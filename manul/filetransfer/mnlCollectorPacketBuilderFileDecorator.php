<?php
/**
 * Упаковщик файлов в сообщение для передачи по очереди.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.filetransfer
 */

/**
 * Упаковщик файлов в сообщение для передачи по очереди.
 *
 * @todo Делать проверку параметра MySQL-сервера на максимальный объём строки запроса и писать
 * предупреждение, если нужно (только один раз за запуск, естественно).
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.filetransfer
 */
class mnlCollectorPacketBuilderFileDecorator
{
    private $_oPacketBuilder;

    /**
     * @param mnlCollectorPacketBuilder $oPacketBuilder
     */
    public function __construct($oPacketBuilder) {
        $this->_oPacketBuilder = $oPacketBuilder;
    }

    /**
     * @param mnlComponent $oComponent
     * @param array        $aEntity
     *
     * @return array
     */
    public function buildPacket($oComponent, $aEntity) {
        $aManifest = $oComponent->getManifest();
        foreach ($aManifest as $sAttributeName => $aAttributeManifest) {
            if ($aAttributeManifest['IsFile']) {
                $sFilePath = $aEntity[$sAttributeName];

                if (is_null($sFilePath)) {
                    // Поле может быть не обязательным, тогда отсутствие значения - норма. Кстати, можно
                    // подумать про обязательность полей в манифесте...

                    continue;
                }

                if (!file_exists($sFilePath)) {
                    throw new mnlException('File not found {'.$sFilePath.'}.');
                }

                $aEntity[$sAttributeName] = base64_encode(file_get_contents($sFilePath));
            }
        }
        return $this->_oPacketBuilder->buildPacket($oComponent, $aEntity);
    }
}
