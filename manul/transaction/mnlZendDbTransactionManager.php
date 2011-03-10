<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.transaction
 */

/**
 * Транзакционный декоратор для обработчика пакета.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.transaction
 */
class mnlZendDbTransactionManager
    implements mnlTransactionManager
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

    public function beginTransaction() {
        $this->_oConnection->beginTransaction();
    }

    public function commit() {
        $this->_oConnection->commit();
    }

    public function rollBack() {
        $this->_oConnection->rollBack();
    }
}
