<?php
/**
 * Класс работы с блокировками (чтобы в импорте не обрабатывать
 * одну и ту же запись одновременно).
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.util
 */

/**
 * Класс работы с блокировками (чтобы в импорте не обрабатывать
 * одну и ту же запись одновременно).
 *
 * В идеале, проблемы одновременной обработки записей должны решаться транзакциями, но
 * таким макаром мы можем быстро устранить проблему одновременной обработки записей в
 * синхронизации, чтобы запускать импорт в много процессов.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.util
 */
class mnlEntityLockManager
{
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    private $_oConnection;

    /**
     * @param Zend_Db_Adapter_Abstract $oConnection
     */
    public function __construct($oConnection) {
        $this->_oConnection = $oConnection;
    }

    /**
     * Хотеть блокировку для сущности.
     *
     * @param string $sEntityType
     * @param int    $iId
     * @param int    $iTimeout    В секундах. 30 секунда по умолчанию.
     *
     * @return string Идентификатор блокировки?
     */
    public function getLock($sEntityType, $iId, $iTimeout = 30) {
        $this->_oConnection->query(
            'SELECT GET_LOCK(?, ?)',
            array(
                $this->_getLockString($sEntityType, $iId),
                $iTimeout
            )
        );
    }

    /**
     * Не хотеть блокировку для сущности.
     *
     * @param string $sEntityType
     * @param int    $iId
     */
    public function releaseLock($sEntityType, $iId) {
        $this->_oConnection->query(
            'SELECT RELEASE_LOCK(?)',
            $this->_getLockString($sEntityType, $iId)
        );
    }

    private function _getLockString($sEntityType, $iId) {
        return $sEntityType.'_'.$iId;
    }
}
