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
interface mnlDeletionCollector
{
    /**
     * @param int $iRecentCollectTime
     *
     * @return array|Iterator
     */
    public function collectDeletions($iRecentCollectTime);
}
