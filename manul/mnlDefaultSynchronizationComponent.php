<?php
/**
 * Компонент синхронизации.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */

/**
 * Компонент синхронизации.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */
class mnlDefaultSynchronizationComponent
    implements mnlComponent
{
    /**
     * @var array
     */
    protected $_aCollectors;

    /**
     * @var string
     */
    protected $_sEntityType;

    protected $_oManifest;

    /**
     * @var mnlEntityManifestValidator
     */
    protected $_oManifestValidator;

    private $_cClassResolver;

    private $_cPreviousCollectionTimestampGetter;

    private $_cPreviousCollectionTimestampSetter;

    /**
     * Карта типов событий на интерфейсы, которые их реализуют.
     *
     * @var array
     */
    private $_aCollectableEvents = array(
        'update' => array(
            'handler'       => array(
                'interface' => 'mnlCollector',
                'method'    => 'collect',
            ),
            'event_handler' => array(
                'interface' => 'mnlEntityCollector',
                'method'    => 'collectEntity',
            )
        ),
        'delete' => array(
            'handler'       => array(
                'interface' => 'mnlDeletionCollector',
                'method'    => 'collectDeletions',
            ),
            'event_handler' => array(
                'interface' => 'mnlEntityDeletionCollector',
                'method'    => 'collectEntityDeletion',
            )
        )
    );

    // TODO Есть мнение, что всю эту прокидку из фабрики сюда можно прооптимизировать...
    public function __construct(
        $cClassResolver,
        $sEntityType,
        $cPreviousCollectionTimestampGetter,
        $cPreviousCollectionTimestampSetter
    ) {
        $this->_cClassResolver = $cClassResolver;
        $this->_sEntityType    = $sEntityType;

        $this->_cPreviousCollectionTimestampGetter = $cPreviousCollectionTimestampGetter;
        $this->_cPreviousCollectionTimestampSetter = $cPreviousCollectionTimestampSetter;
    }

    /**
     * Check environment for component.
     *
     * @throws mnlEnvironmentException
     */
    public function checkEnvironment() {
        // Implement (run checks from importer and collector)? For what?..
    }

    /**
     * Мотивация вынесения в метод - чтобы удобнее можно было навешивать "декораторы" (переопределять
     * для каждой стороны)...
     */
    protected function _getCollector() {
        $sClass = call_user_func($this->_cClassResolver, $this->_sEntityType, 'collector');

        $oCollector = new $sClass();

        // Проверяем среду испольнения. Если что-то не так, пробрасываем исключение наверх.
        if (is_callable(array($oCollector, 'checkEnvironment'))) {
            mnlRegistry::get('logger')->debug('Checking environment for '.$this->_sEntityType.' component...');

            $oCollector->checkEnvironment();
        }

        return $oCollector;
    }

    /**
     * Получить экземпляр коллекторов по событиям.
     *
     * @return array
     */
    public function getCollectors() {
        if (is_null($this->_aCollectors)) {
            $oCollector = $this->_getCollector();

            foreach ($this->_aCollectableEvents as $sEvent => $aHandlers) {
                $this->_aCollectors[$sEvent] = array(
                    'collector' => $oCollector,
                );

                foreach ($aHandlers as $sHandler => $aHandler) {
                    if (in_array($aHandler['interface'], class_implements($oCollector))) {
                        $this->_aCollectors[$sEvent][$sHandler] = array($oCollector, $aHandler['method']);
                    }
                }
            }
        }

        return $this->_aCollectors;
    }

    /**
     * Возвращаем импортёр для сущности, если компонент поддерживает обновление и создание элементов.
     *
     * @param int $iId
     *
     * @return mnlEntityImporter|null
     */
    public function getEntityImporter($iId = null) {
        $sClass = call_user_func($this->_cClassResolver, $this->_sEntityType, 'importer');

        if (
            !in_array(
                'mnlEntityImporter',
                class_implements($sClass)
            )
        ) {
            return null;
        }

        $oImporter = new $sClass($iId);

        // Проверяем среду испольнения. Если что-то не так, пробрасываем исключение наверх.
        if (is_callable(array($oImporter, 'checkEnvironment'))) {
            mnlRegistry::get('logger')->debug('Checking environment for '.$this->_sEntityType.' component...');

            $oImporter->checkEnvironment();
        }

        return $oImporter;
    }

    /**
     * Возвращаем импортёр, если компонент поддерживает обработку удаления.
     *
     * @return mnlEntityDeletionImporter|null
     */
    public function getEntityDeletionImporter() {
        $sClass = call_user_func($this->_cClassResolver, $this->_sEntityType, 'importer');

        if (
            in_array(
                'mnlEntityDeletionImporter',
                class_implements($sClass)
            )
        ) {
            return null;
        }

        $oImporter = new $sClass();

        // Проверяем среду испольнения. Если что-то не так, пробрасываем исключение наверх.
        if (is_callable(array($oImporter, 'checkEnvironment'))) {
            mnlRegistry::get('logger')->debug('Checking environment for '.$this->_sEntityType.' component...');

            $oImporter->checkEnvironment();
        }

        return $oImporter;
    }

    /**
     * Получить манивест.
     *
     * @return array
     */
    public function getManifest() {
        if (is_null($this->_oManifest)) {
            $sClass = call_user_func($this->_cClassResolver, $this->_sEntityType, 'manifest');

            $this->_oManifest = new $sClass();
        }

        return $this->_oManifest->getManifest();
    }

    /**
     * Получить экземпляр валидатора манифеста.
     *
     * @return mnlEntityManifestValidator
     */
    public function getManifestValidator() {
        if (is_null($this->_oManifestValidator)) {
            $this->_oManifestValidator = new mnlEntityManifestValidator(
                $this->getManifest(),
                $this->_sEntityType
            );
        }

        return $this->_oManifestValidator;
    }

    /**
     * Получить строку с названием типа сущности.
     *
     * @return string
     */
    public function getEntityType() {
        return $this->_sEntityType;
    }

    /**
     * Получить значение метки времени последнего сбора.
     *
     * @return int
     */
    public function getPreviousCollectionTimestamp() {
        return call_user_func(
            $this->_cPreviousCollectionTimestampGetter,
            $this->_sEntityType
        );
    }

    /**
     * Установить значение метки времени последнего сбора.
     *
     * @param int $iPreviousCollectionTimestamp
     */
    public function setPreviousCollectionTimestamp($iPreviousCollectionTimestamp) {
        call_user_func(
            $this->_cPreviousCollectionTimestampSetter,
            $this->_sEntityType,
            $iPreviousCollectionTimestamp
        );
    }
}
