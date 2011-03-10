<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */

/**
 * Обработчик пакета при импорте.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */
class mnlImporterPacketProcessor
    implements mnlPacketProcessor
{
    /**
     * @var mnlResolver
     */
    private $_oResolver;

    /**
     * Callback для сравнения дат (на разных сторонах сравнение немного отличается).
     *
     * @var callback
     */
    private $_cNeedImportDateStrategy;

    /**
     * @var mnlEntityLockManager
     */
    private $_oLockManager;

    /**
     * @var mnlImportingProfiler
     */
    private $_oProfiler;

    /**
     * @param mnlResolver          $oResolver
     * @param callback             $cNeedImportDateStrategy
     * @param mnlEntityLockManager $oLockManager
     */
    public function __construct($oResolver, $cNeedImportDateStrategy, $oLockManager) {
        $this->_oResolver               = $oResolver;
        $this->_cNeedImportDateStrategy = $cNeedImportDateStrategy;
        $this->_oLockManager            = $oLockManager;

        // Вынести как-то получше в Service Locator?..
        $this->_oProfiler = mnlRegistry::get('importing_profiler');
    }

    /**
     * @param mnlComponent $oComponent
     * @param array        $aPacket
     *
     * @return int Идентификатор обработанной записи, либо null, если ничего не
     *             было реально обработано (если, к примеру, не прошла проверка по
     *             датам и не нужно было ничего обрабатывать).
     */
    // TODO Вынести работу с блокировками в отдельный декоратор.
    public function processPacket($oComponent, $aPacket) {
        $sEntityType     = $oComponent->getEntityType();
        $aEntityManifest = $oComponent->getManifest();

        $aEntity = $aPacket['Entity'];

        $this->_oProfiler->startResolvingLocalId();

        $iRemoteId = $aEntity['Id'];
        $iLocalId  = $this->_oResolver->resolveLocalId($sEntityType, $aEntity['Id']);

        $this->_oProfiler->finishResolvingLocalId($iLocalId);

        mnlRegistry::get('logger')->log(
            'Importing :'.$aPacket['EntityType'].'[LocalId='.(is_null($iLocalId) ? 'NULL' : $iLocalId).', RemoteId='.$aPacket['Entity']['Id'].']...',
            Zend_Log::INFO
        );

        $this->_oProfiler->startResolvingDependencies();

        $aEntity = $this->_resolveRelations($aEntityManifest, $aEntity);

        $this->_oProfiler->finishResolvingDependencies();

        $this->_oProfiler->startCreatingImporter();

        $oEntityImporter = $oComponent->getEntityImporter($iLocalId);

        $this->_oProfiler->finishCreatingImporter();

        // Eсли Резольвер указывает на то, что сущность новая ($iLocalId == NULL), можно
        // не проверять даты на предмет конфликта.
        if ($iLocalId && !$this->_needImport($oEntityImporter, $aEntity)) {
            mnlRegistry::get('logger')->log(
                'Imported entity ('.date(DATE_ISO8601, $aEntity['ModifiedDate']).') is older then current ('.date(DATE_ISO8601, $oEntityImporter->getEntityLastModifiedDate()).'), do not import.',
                Zend_Log::WARN,
                array(
                    'packet' => $aPacket
                )
            );

            return;
        }

        // Вырезаем идентификатор из записи, чтобы он не смущал народ. Ибо он уже передался при создании.
        $aImportingEntity = $aEntity;
        unset($aImportingEntity['Id']);

        $this->_oProfiler->startFilling();

        $oEntityImporter->fillEntity($aImportingEntity);

        if ($oEntityImporter instanceof mnlConnectableEntityImporter) {
            $oEntityImporter->fillRemoteId($iRemoteId);
        }

        $this->_oProfiler->finishFilling();

        $oDomainValidator = $oEntityImporter->getEntityDomainValidator();
        if (!$oDomainValidator->isValid()) {
            throw new mnlDomainValidationException(
                $oDomainValidator->getMessages()
            );
        }

        if ($iLocalId) {
            mnlRegistry::get('logger')->log(
                'Getting lock for :'.$sEntityType.'[LocalId='.(is_null($iLocalId) ? 'NULL' : $iLocalId).']...',
                Zend_Log::DEBUG
            );

            // Получаем блокировку на работу с локальной сущностью.
            $this->_oLockManager->getLock($sEntityType, $iLocalId);
        }

        try {
            $this->_oProfiler->startSaving();

            $iImportedEntityId = $oEntityImporter->saveEntity();

            $this->_oProfiler->finishSaving($iImportedEntityId);

            $this->_oProfiler->startBindingLocalId();

            // Если ранее не было ID сущности в резолвере, то надо забиндить.
            if (!$iLocalId) {
                // TODO А если импортёр написан неправильно, не возвращает идентификатор даже тогда, когда вставил новую
                // запись? Она не попадёт в Резольвер, получается. Нужно бы проверить, что идентификатор возвращается
                // всегда. И писать ошибку, если он не возвращается?
                $this->_oResolver->bindWithRemoteId($sEntityType, $aEntity['Id'], $iImportedEntityId);
            }

            $this->_oProfiler->finishBindingLocalId();

            // Возвращаем блокировку.
            if ($iLocalId) {
                $this->_oLockManager->releaseLock($sEntityType, $iLocalId);
            }

            return $iImportedEntityId;
        } catch (Exception $oException) {
            // Возвращаем блокировку в любом случае.
            // TODO Как-то в одном месте это делать?..
            if ($iLocalId) {
                $this->_oLockManager->releaseLock($sEntityType, $iLocalId);
            }

            throw $oException;
        }
    }

    private function _resolveRelations($aEntityManifest, $aEntity) {
        foreach($aEntityManifest as $sAttributeName => $aAttributeManifest) {
            if (
                ($sRelatedEntityType = $aAttributeManifest['RelatedEntityType'])
                &&
                // Осуществляем преобразование, если только значение
                // ненулевое (необязательные свойства привязки).
                // TODO А если свойство привязки обязательное?..
                $aEntity[$sAttributeName]
            ) {
                // Отношение может быть как *-к-одному (тип атрибута - int), так и *-ко-многим (тип
                // свойства - array).
                // TODO Хорошо бы делать проверку, что это либо массив, либо число...
                $aRemoteIds = $aEntity[$sAttributeName];
                if ('int' == $aAttributeManifest['Type']) {
                    $aRemoteIds = array($aRemoteIds);
                }

                $aLocalIds = array();
                foreach ($aRemoteIds as $iRemoteId) {
                    $iLocalId = $this->_oResolver->resolveLocalId(
                        $sRelatedEntityType, $iRemoteId
                    );

                    if (is_null($iLocalId)) {
                        throw new mnlUnresolvedDependencyException(
                            'Unable resolve '.$sRelatedEntityType.' with '.$iRemoteId.' identifier for '.$sAttributeName.' attribute.'
                        );
                    }

                    $aLocalIds[] = $iLocalId;
                }

                $aEntity[$sAttributeName] = $aLocalIds;
                if ('int' == $aAttributeManifest['Type']) {
                    $aEntity[$sAttributeName] = array_shift($aEntity[$sAttributeName]);
                }
            }
        }

        return $aEntity;
    }

    private function _needImport($oEntityImporter, $aImportingEntity) {
        return call_user_func(
            $this->_cNeedImportDateStrategy,
            $iCurrentTimestamp = $oEntityImporter->getEntityLastModifiedDate(),
            $iPacketTimestamp  = $aImportingEntity['ModifiedDate']
        );
    }
}
