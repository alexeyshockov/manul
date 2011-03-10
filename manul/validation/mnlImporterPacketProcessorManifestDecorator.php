<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.validation
 */

/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.validation
 */
class mnlImporterPacketProcessorManifestDecorator
    implements mnlPacketProcessor
{
    private $_oPacketProcessor;

    public function __construct($oPacketProcessor)
    {
        $this->_oPacketProcessor = $oPacketProcessor;
    }

    public function processPacket($oComponent, $aPacket)
    {
        $oManifestValidator = $oComponent->getManifestValidator();
        if (!$oManifestValidator->isValid($aPacket['Entity'])) {
            throw new mnlManifestValidationException(
                $oComponent->getManifest(),
                $oManifestValidator->getMessages()
            );
        }

        return $this->_oPacketProcessor->processPacket($oComponent, $aPacket);
    }
}
