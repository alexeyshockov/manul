<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */

/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */
class mnlCollectorPacketBuilder
{
    /**
     * @var mnlResolver
     */
    private $_oResolver;

    /**
     * @param mnlResolver $oResolver
     */
    public function __construct($oResolver) {
        $this->_oResolver = $oResolver;
    }

    /**
     * Упаковать сущность в пакет
     * На входе - данные об одной записи от сборщика, на выходе - пакет.
     *
     * @param mnlComponent $oComponent
     * @param array        $aEntity
     *
     * @return array
     */
    public function buildPacket($oComponent, $aEntity) {
        $sEntityType = $oComponent->getEntityType();

        $this->_oResolver->registerLocalId($sEntityType, $aEntity['Id']);

        $aPacket = array(
            'EntityType'    => $sEntityType,
            'Entity'        => $aEntity,
        );

        return $aPacket;
    }
}
