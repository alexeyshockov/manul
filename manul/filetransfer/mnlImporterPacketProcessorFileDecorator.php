<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.filetransfer
 */

/**
 * Распакощик файлов из сообщения.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.filetransfer
 */
class mnlImporterPacketProcessorFileDecorator
    implements mnlPacketProcessor
{
    private $_oPacketProcessor;

    public function __construct($oPacketProcessor) {
        $this->_oPacketProcessor = $oPacketProcessor;
    }

    /**
     * @param mnlComponent $oComponent
     * @param array        $aPacket
     *
     * @return int
     */
    public function processPacket($oComponent, $aPacket) {
        $aManifest = $oComponent->getManifest();

        foreach ($aManifest as $sAttributeName => $aAttributeManifest) {
            if ($aAttributeManifest['IsFile']) {
                if (is_null($aPacket['Entity'][$sAttributeName])) {
                    // Поле может быть не обязательным, тогда отсутствие значения - норма.

                    continue;
                }

                $sFile = base64_decode($aPacket['Entity'][$sAttributeName]);

                if (false === $sFile) {
                    throw new mnlException('Unable to decode file from base64.');
                }

                $sTemporaryFile = tempnam(
                    sys_get_temp_dir(),
                    $aPacket['EntityType'].'_'.$aPacket['Entity']['Id'].'_'
                );

                // Не забыть удалить после обработки ;) См. далее по коду.
                file_put_contents($sTemporaryFile, $sFile);

                // Записываем путь к файлу на локальной машине...
                $aPacket['Entity'][$sAttributeName] = $sTemporaryFile;
            }
        }

        $iImportedEntity = $this->_oPacketProcessor->processPacket($oComponent, $aPacket);

        // И не забываем удалить файл, чтобы не захламлял систему.
        unlink($sTemporaryFile);

        return $iImportedEntity;
    }
}
