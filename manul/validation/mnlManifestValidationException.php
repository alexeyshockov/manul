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
class mnlManifestValidationException
    extends mnlValidationException
{
    private $_aManifest;

    public function __construct($aManifest, $aValidationMessages) {
        $this->_aManifest = $aManifest;

        parent::__construct($aValidationMessages);
    }

    public function getManifest() {
        return $this->_aManifest;
    }

    // FIXME __toString() - плюсануть к нему запись манифеста!
}
