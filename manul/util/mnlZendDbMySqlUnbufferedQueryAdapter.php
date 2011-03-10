<?php
/**
 * Unbuffered query adapter, adds unbuffered functionality to Zend_Db_Adapter_Abstract.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.util
 */

/**
 * Unbuffered query adapter, adds unbuffered functionality to Zend_Db_Adapter_Abstract.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.util
 */
class mnlZendDbMySqlUnbufferedQueryAdapter
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
     * @param string|Zend_Db_Select $mSql
     * @param mixed $mBind
     *
     * @return Zend_Db_Statement
     */
    public function unbufferedQuery($mSql, $mBind = array()) {
        $bUseBuffer = $this->_oConnection->getConnection()->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY);

        $this->_oConnection->getConnection()->setAttribute(
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,
            false
        );

        $oResultSet = $this->_oConnection->query($mSql, $mBind);

        // Reset to original.
        $this->_oConnection->getConnection()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $bUseBuffer);

        return $oResultSet;
    }

    public function __call($sMethod, $aParams) {
        if (!is_callable(array($this->_oConnection, $sMethod))) {
            throw new BadMethodCallException();
        }

        return call_user_func_array(
            array($this->_oConnection, $sMethod),
            $aParams
        );
    }
}
