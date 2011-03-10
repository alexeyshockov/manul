<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.core
 */

/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.core
 */
interface mnlPacketProcessor
{
    /**
     * @param mnlComponent $oComponent
     * @param array        $aPacket
     *
     * @return int Идентификатор обработанной записи.
     */
    public function processPacket($oComponent, $aPacket);
}
