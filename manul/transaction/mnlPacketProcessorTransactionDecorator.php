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
class mnlPacketProcessorTransactionDecorator
    implements mnlPacketProcessor
{
    private $_oPacketProcessor;

    private $_aTransactionManagers;

    public function __construct($aTransactionManagers, $oPacketProcessor) {
        $this->_aTransactionManagers = $aTransactionManagers;
        $this->_oPacketProcessor     = $oPacketProcessor;
    }

    /**
     * @param mnlComponent $oComponent
     * @param array        $aPacket
     *
     * @return int
     */
    public function processPacket($oComponent, $aPacket) {
        foreach ($this->_aTransactionManagers as $oTransactionManager) {
            $oTransactionManager->beginTransaction();
        }

        try {
            $iImportedEntityId = $this->_oPacketProcessor->processPacket($oComponent, $aPacket);

            foreach ($this->_aTransactionManagers as $oTransactionManager) {
                $oTransactionManager->commit();
            }

            return $iImportedEntityId;
        } catch (Exception $oException) {
            foreach ($this->_aTransactionManagers as $oTransactionManager) {
                $oTransactionManager->rollBack();
            }

            throw $oException;
        }
    }
}
