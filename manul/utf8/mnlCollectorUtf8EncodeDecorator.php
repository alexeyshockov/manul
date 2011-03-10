<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.utf8
 */

/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.utf8
 */
class mnlCollectorUtf8EncodeDecorator
    implements mnlCollector
{
    private $_oCollector;

    private $_cUtf8Encoder;

    public function __construct($oCollector) {
        $this->_oCollector = $oCollector;

        $this->_cUtf8Encoder = create_function('$aEntity', 'return array_map(array("mnlStringHelper", "cp1251toUtf8"), $aEntity);');
    }

    /**
     * @return Iterator
     */
    public function collect($iLastCollectTime) {
        $mEntities = $this->_oCollector->collect($iLastCollectTime);

        return new mnlIteratorCallbackDecorator(
            (is_array($mEntities) ? new ArrayIterator($mEntities) : $mEntities),
            $this->_cUtf8Encoder
        );
    }

    public function collectById($iId) {
        return call_user_func(
            $this->_cUtf8Encoder,
            $this->_oCollector->collectById($iId)
        );
    }
}
