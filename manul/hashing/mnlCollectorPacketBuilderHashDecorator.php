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
class mnlCollectorPacketBuilderHashDecorator
{
    private $_oPacketBuilder;

    private $_oHasher;

    /**
     * @param mnlEntityHasher           $oHasher
     * @param mnlCollectorPacketBuilder $oPacketBuilder
     */
    public function __construct($oHasher, $oPacketBuilder) {
        $this->_oPacketBuilder = $oPacketBuilder;
        $this->_oHasher        = $oHasher;
    }

    /**
     * Формирует пакет средствами дочернего PacketBuilder-а и добавляет к пакету секцию с хешами.
     *
     * @param mnlComponent $oComponent
     * @param array        $aEntity
     *
     * @return array
     */
    public function buildPacket($oComponent, $aEntity) {
        return $this->_buildHashes(
            $oComponent,
            $this->_oPacketBuilder->buildPacket(
                $oComponent,
                $aEntity
            )
        );
    }

    private function _buildHashes($oComponent, $aPacket) {
        return array_merge(
            $aPacket,
            array(
                'Hashes' => $this->_oHasher->getHashes($oComponent->getManifest(), $aPacket['Entity']),
            )
        );
    }
}
