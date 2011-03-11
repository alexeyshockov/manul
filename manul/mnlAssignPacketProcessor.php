<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */

/**
 * Обработчик пакета с идентификатором созданного элемента во внешней системе.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */
class mnlAssignPacketProcessor
    implements mnlPacketProcessor
{
    /**
     * @var mnlResolver
     */
    private $_oResolver;

    /**
     * @param mnlResolver          $oResolver
     * @param mnlEntityLockManager $oLockManager
     */
    public function __construct($oResolver) {
        $this->_oResolver = $oResolver;
    }

    /**
     * @param mnlComponent $oComponent
     * @param array        $aPacket
     *
     * @return int Идентификатор обработанной записи.
     */
    public function processPacket($oComponent, $aPacket) {
        $sEntityType        = $oComponent->getEntityType();

        $aEntity = $aPacket['Entity'];

        $iRemoteId  = $aEntity['Id'];
        $iLocalId   = $this->_oResolver->resolveRemoteId($sEntityType, $aEntity['Id']);

        $oEntityImporter = $oComponent->getEntityImporter($iLocalId);

        if (!($oEntityImporter instanceof mnlConnectableEntityImporter)) {
            mnlRegistry::get('logger')->log(
                $aPacket['EntityType'].' does not support remote identifier assigning.',
                Zend_Log::INFO
            );

            return $iLocalId;
        }

        mnlRegistry::get('logger')->log(
            'Assigning remote identifier to :'.$aPacket['EntityType'].'[LocalId='.(is_null($iLocalId) ? 'NULL' : $iLocalId).', RemoteId='.$aPacket['Entity']['Id'].']...',
            Zend_Log::INFO
        );

        $oEntityImporter->fillRemoteId($iRemoteId);

        return $oEntityImporter->saveEntity();
    }
}
