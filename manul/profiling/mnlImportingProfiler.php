<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.profiling
 */

/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.profiling
 */
// FIXME Вообще, данную тему с профайлеров нужно хорошо отрефакторить. И сам профайлер, и
// эта его настройка, создавались на очень скорую руку. Это бы дело унифицировать, нормально
// разбить по классикам...
class mnlImportingProfiler
{
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    private $_oConnection;

    /**
     * @var string
     */
    private $_sSystem;

    /**
     * @var string
     */
    private $_aStartPoints;

    /**
     * @var string
     */
    private $_aPacketTimes;

    /**
     * @var string
     */
    private $_aPacketProfile;

    private $_bEnabled;

    /**
     * @param string                   $sSystem     System name.
     * @param Zend_Db_Adapter_Abstract $oConnection
     * @param bool                     $bEnabled
     */
    public function __construct($sSystem, $oConnection, $bEnabled = true) {
        $this->_sSystem     = $sSystem;
        $this->_oConnection = $oConnection;
        $this->_bEnabled    = $bEnabled;

        $this->_clearProfile();
    }

    private function _clearProfile() {
        $this->_aStartPoints   = array(
            'processing'                  => null,
            'resolving_local_id'          => null,
            'resolving_dependencies'      => null,
            'creating_importer'           => null,
            'filling'                     => null,
            'saving'                      => null,
            'binding'                     => null,
            'hash_checking'               => null,
        );
        $this->_aPacketProfile = array(
            'pid'                         => posix_getpid(),
            'system'                      => $this->_sSystem,
            'start_time'                  => null,
            'entity_type'                 => null,
            'entity_remote_id'            => null,
            'entity_local_id'             => null,
        );
        $this->_aPacketTimes   = array(
            'processing_time'             => null,
            'resolving_local_id_time'     => null,
            'resolving_dependencies_time' => null,
            'creating_importer_time'      => null,
            'filling_time'                => null,
            'saving_time'                 => null,
            'binding_time'                => null,
            'hash_checking_time'          => null,
        );
    }

    public function startProcessing($sEntityType, $iRemoteId) {
        $this->_aPacketProfile['entity_type']      = $sEntityType;
        $this->_aPacketProfile['entity_remote_id'] = $iRemoteId;

        // Время начала обработки пакета.
        $this->_aPacketProfile['start_time'] = time();

        $this->_aStartPoints['processing'] = microtime(true);
    }

    public function finishProcessing() {
        $this->_aPacketTimes['processing_time'] = microtime(true) - $this->_aStartPoints['processing'];

        $this->_flush();

        $this->_clearProfile();
    }

    public function startResolvingLocalId() {
        $this->_aStartPoints['resolving_local_id'] = microtime(true);
    }

    public function finishResolvingLocalId($iLocalId) {
        $this->_aPacketTimes['resolving_local_id_time'] = microtime(true) - $this->_aStartPoints['resolving_local_id'];

        $this->_aPacketTimes['entity_local_id'] = $iLocalId;
    }

    public function startResolvingDependencies() {
        $this->_aStartPoints['resolving_dependencies'] = microtime(true);
    }

    public function finishResolvingDependencies() {
        $this->_aPacketTimes['resolving_dependencies_time'] = microtime(true) - $this->_aStartPoints['resolving_dependencies'];
    }

    public function startCreatingImporter() {
        $this->_aStartPoints['creating_importer'] = microtime(true);
    }

    public function finishCreatingImporter() {
        $this->_aPacketTimes['creating_importer_time'] = microtime(true) - $this->_aStartPoints['creating_importer'];
    }

    public function startFilling() {
        $this->_aStartPoints['filling'] = microtime(true);
    }

    public function finishFilling() {
        $this->_aPacketTimes['filling_time'] = microtime(true) - $this->_aStartPoints['filling'];
    }

    public function startSaving() {
        $this->_aStartPoints['saving'] = microtime(true);
    }

    public function finishSaving($iLocalId) {
        $this->_aPacketTimes['saving_time'] = microtime(true) - $this->_aStartPoints['saving'];

        if (!$this->_aPacketTimes['entity_local_id']) {
            $this->_aPacketTimes['entity_local_id'] = $iLocalId;
        }
    }

    public function startBindingLocalId() {
        $this->_aStartPoints['binding'] = microtime(true);
    }

    public function finishBindingLocalId() {
        $this->_aPacketTimes['binding_time'] = microtime(true) - $this->_aStartPoints['binding'];
    }

    /**
     * @todo Выделить в отдельный класс-декоратор.
     */
    // TODO Выделить в декоратор профилирование проверки хешей (там же выбирается запись, а
    // это дополнительное большое время).
    public function startHashChecking() {
        $this->_aStartPoints['hash_checking'] = microtime(true);
    }

    /**
     * @todo Выделить в отдельный класс-декоратор.
     */
    public function finishHashChecking() {
        $this->_aPacketTimes['hash_checking_time'] = microtime(true) - $this->_aStartPoints['hash_checking'];
    }

    /**
     * Форматируем чило с плавающей точкой в целое, если оно вообще представлено.
     *
     * @param null|float $iTime
     *
     * @return null|int
     */
    private function _formatTime($fTime) {
        if (!is_null($fTime)) {
            return round($fTime, 3) * 1000;
        }
    }

    private function _flush() {
        if (!$this->_bEnabled) {
            return;
        }

        $aData = array_merge(
            $this->_aPacketProfile,
            array_map(array($this, '_formatTime'), $this->_aPacketTimes)
        );

        $aData['start_time'] = new Zend_Db_Expr('FROM_UNIXTIME('.$aData['start_time'].')');

        // INSERT DELAYED не поддерживается (да и не играет роли) в InnoDB :(
        $this->_oConnection->insert(
            'manul_profiling',
            $aData
        );
    }
}
