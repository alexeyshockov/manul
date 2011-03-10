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
class mnlValidationException
    extends mnlException
{
    private $_aValidationMessages;

    public function __construct($aValidationMessages = array()) {
        $this->_aValidationMessages = $aValidationMessages;

        parent::__construct();
    }

    public function getValiadtionMessages() {
        return $this->_aValidationMessages;
    }

    // FIXME __toString() - плюсануть запись сообщений!
}
