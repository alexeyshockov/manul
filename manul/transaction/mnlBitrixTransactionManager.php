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
class mnlBitrixTransactionManager
    implements mnlTransactionManager
{
    /**
     * @var CDatabase
     */
    private $_oConnection;

    /**
     * @param CDatabase $oConnection
     */
    public function __construct($oConnection) {
        $this->_oConnection = $oConnection;
    }

    public function beginTransaction() {
        $this->_oConnection->StartTransaction();
    }

    public function commit() {
        $this->_oConnection->Commit();
    }

    public function rollBack() {
        $this->_oConnection->Rollback();
    }
}
