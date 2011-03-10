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
interface mnlCollector
{
    /**
     * @param int $iRecentCollectTime
     *
     * @return array|Iterator
     */
    // TODO Rename to collectRecentChanges.
    public function collect($iRecentCollectTime);

    public function collectById($iId);
}
