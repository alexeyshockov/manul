<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.util
 */

/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.util
 */
class mnlBitrixDatabaseReconnectDecorator
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

    /**
     * Переподключаемся, если нужно...
     *
     * @throws Exception
     *
     * @param string    $sQuery
     * @param bool      $bIgnoreErrors
     * @param int       $iErrorPosition
     */
    // Вопрос только в том, что делать с транзакциями? Но так-то хер с ними, никто не
    // будет их так долго пялить в такое соединение.
    public function Query($sQuery, $bIgnoreErrors = false, $iErrorPosition = "") {
        try {
            return $this->_oConnection->Query($sQuery, $bIgnoreErrors, $iErrorPosition);
        } catch (Exception $oException) {
            // 2006 - MySQL server has gone away (connection timeout).
            if ($oException->getCode() == 2006) {
                $this->_oConnection->Disconnect();
                $this->_oConnection->DoConnect();

                // Заново!
                return $this->_oConnection->Query($sQuery, $bIgnoreErrors, $iErrorPosition);
            }

            throw $oException;
        }
    }

    public function __call($sMethodName, $aArguments) {
        if (!is_callable(array($this->_oConnection, $sMethodName))) {
            throw new BadMethodCallException();
        }

        return call_user_func_array(array($this->_oConnection, $sMethodName), $aArguments);
    }

    public function __set($name, $value) {
        $this->_oConnection->$name = $value;
    }

    public function __get($name) {
        return $this->_oConnection->$name;
    }
}
