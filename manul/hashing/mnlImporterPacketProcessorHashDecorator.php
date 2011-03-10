<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.hashes
 */

/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.hashes
 */
class mnlImporterPacketProcessorHashDecorator
    implements mnlPacketProcessor
{
    /**
     * @var mnlImporterPacketProcessor
     */
    private $_oPacketProcessor;

    /**
     * @var mnlCollectorPacketBuilder
     */
    private $_oPacketBuilder;

    /**
     * @var mnlImportingProfiler
     */
    private $_oProfiler;

    public function __construct($oPacketBuilder, $oPacketProcessor) {
        $this->_oPacketProcessor    = $oPacketProcessor;
        $this->_oPacketBuilder      = $oPacketBuilder;

        // Вынести как-то получше в Service Locator?..
        $this->_oProfiler = mnlRegistry::get('importing_profiler');
    }

    /**
     * @param mnlComponent $oComponent
     * @param array        $aImportingPacket
     */
    public function processPacket($oComponent, $aImportingPacket) {
        $iImportedEntityId = $this->_oPacketProcessor->processPacket($oComponent, $aImportingPacket);

        $this->_oProfiler->startHashChecking();

        // Только если что-то реально было обработано.
        if ($iImportedEntityId) {
            $aCollectors    = $oComponent->getCollectors();
            // TODO Тип события можно вынести, к примеру, в конструктор, чтобы унифицировать декоратор. Другое
            // дело, что collectById есть только для update... Нет, унифицировать полностью не нужно. Но тип
            // события вынести нужно :)
            $oCollector = $aCollectors['update']['collector'];

            $aImportedPacket = $this->_oPacketBuilder->buildPacket(
                $oComponent,
                $oCollector->collectById($iImportedEntityId)
            );

            if (!$this->_areHashesEqual(
                    $aImportedPacket['Hashes'],
                    $aImportingPacket['Hashes']
                )
            ) {
                // Какое-то нехорошее дублирование.
                $this->_oProfiler->finishHashChecking();

                // TODO Полностью передавать импортированный пакет.
                throw new mnlHashComparisonException($aImportedPacket['Hashes'], $aImportingPacket);
            }
        }

        $this->_oProfiler->finishHashChecking();

        return $iImportedEntityId;
    }

    private function _areHashesEqual($aImportedEntityHashes, $aImportingEntityHashes) {
        return !(bool)count(array_diff_assoc($aImportedEntityHashes, $aImportingEntityHashes));
    }
}
