<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.core
 */

require_once 'Zend/Registry.php';

/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.core
 */
// TODO Придумать что-то более вменяемое?..
class mnlRegistry
    extends Zend_Registry
{
    protected static function init() {
        self::setInstance(new self());
    }
}
