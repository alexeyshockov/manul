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
interface mnlEntityCollector
{
    /**
     * @param object $oEntity
     *
     * @return array|null Массив (аналогичный формату обычного collect()), либо null, если запись не нужно собирать.
     */
    public function collectEntity($oEntity);
}
