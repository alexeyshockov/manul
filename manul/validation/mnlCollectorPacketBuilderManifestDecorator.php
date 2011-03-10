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
class mnlCollectorPacketBuilderManifestDecorator
{
    private $_oPacketBuilder;

    public function __construct($oPacketBuilder) {
        $this->_oPacketBuilder = $oPacketBuilder;
    }

    public function buildPacket($oComponent, $aEntity) {
        $oManifestValidator = $oComponent->getManifestValidator();
        $aEntityManifest    = $oComponent->getManifest();

        $aEntity = $this->_castTypes($aEntityManifest, $aEntity);

        if (!$oManifestValidator->isValid($aEntity)) {
            throw new mnlManifestValidationException(
                $aEntityManifest,
                $oManifestValidator->getMessages()
            );
        }

        return $this->_oPacketBuilder->buildPacket(
            $oComponent,
            $this->_excludeUnneccessaryAttributes($aEntityManifest, $aEntity)
        );
    }

    private function _excludeUnneccessaryAttributes($aEntityManifest, $aEntity) {
        foreach(array_keys($aEntity) as $sAttrubuteName) {
            if (!array_key_exists($sAttrubuteName, $aEntityManifest)) {
                unset($aEntity[$sAttrubuteName]);
            }
        }

        return $aEntity;
    }

    /**
     * Принудительно приводим типы в соответствие с манифестом (если они заданы). Для упрощения жизни компонентам
     * очень полезное.
     *
     * @param array $aManifest
     * @param array $aEntity
     *
     * @return array
     */
    private function _castTypes($aManifest, $aEntity) {
        foreach($aManifest as $sAttributeName => $aAttributeManifest) {
            // Принудительно подгоняем тип, если он есть в манифесте. Можно использовать gettype, но разработчики не
            // рекомендуют (http://php.net/manual/en/function.gettype.php).
            if (
                // Работаем, только если атрибует действительно есть у собранной сущности (чтобы вдруг
                // не добавить чего лишнего).
                isset($aEntity[$sAttributeName])
                &&
                $aAttributeManifest['Type']
                &&
                in_array(
                    $aAttributeManifest['Type'],
                    array('int', 'float', 'string', 'array', 'bool')
                )
            ) {
                settype($aEntity[$sAttributeName], $aAttributeManifest['Type']);
            }
        }

        return $aEntity;
    }
}
