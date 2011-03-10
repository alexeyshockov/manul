<?php
/**
 * Тупо отправка в табличку, которая уже разбирается отдельно.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.failurebroker
 */

/**
 * Тупо отправка в табличку, которая уже разбирается отдельно.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.failurebroker
 */
class mnlDefaultFailureHandler
{
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    private $_oConnection;

    private $_sTableName;

    private $_sQueueName;

    /**
     * @param Zend_Db_Adapter_Abstract  $oConnection
     * @param string                    $sTableName
     * @param string                    $sQueueName
     */
    public function __construct($oConnection, $sQueueName) {
        $this->_oConnection = $oConnection;
        // TODO Вынести как-то в опциональные параметры? В настройки?
        $this->_sTableName  = 'manul_errors';
        $this->_sQueueName  = $sQueueName;
    }

    public function handle($aFailedPacket, $oException) {
        $oPreviousException = $oException->getPrevious();
        $sMessage           = $oException->getMessage();
        if ($oPreviousException) {
            $sMessage .= ($sMessage ? ' ' : '').$oPreviousException->getMessage();
        }
        $this->_oConnection->insert(
            $this->_sTableName,
            array(
                // Ну FB у нас только импорте.
                'action'                => 'import',
                'queue'                 => $this->_sQueueName,
                'entity_type'           => $aFailedPacket['EntityType'],
                'packet'                => serialize($aFailedPacket),
                'exception_type'        => get_class($oException),
                'exception_description' => $sMessage ? $sMessage : null,
                'exception_stack_trace' => $oException->getTraceAsString(),
            )
        );
    }
}
