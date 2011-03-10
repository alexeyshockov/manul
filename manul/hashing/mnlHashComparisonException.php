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
class mnlHashComparisonException
    extends mnlException
{
    private $_aUpdatedEntityHashes;

    private $_aPacket;

    public function __construct($aUpdatedEntityHashes, $aPacket) {
        $this->_aUpdatedEntityHashes = $aUpdatedEntityHashes;
        $this->_aPacket              = $aPacket;

        parent::__construct($this->_formatMessage());
    }

    public function getPacket() {
        return $this->_aPacket;
    }

    public function getImportingEntityHashes() {
        return $this->_aPacket['Hashes'];
    }

    public function getUpdatedEntityHashes() {
        return $this->_aUpdatedEntityHashes;
    }

    private function _formatMessage() {
        $aImportingEntityHashes = $this->getImportingEntityHashes();
        $aUpdatedEntityHashes   = $this->getUpdatedEntityHashes();

        $sMessage = '';
        foreach($aImportingEntityHashes as $sAttributeName => $sAttributeValueHash) {
            if ($sAttributeValueHash != $aUpdatedEntityHashes[$sAttributeName]) {
                $sMessage .= 'Unequal attribute: '.$this->_aPacket['EntityType'].':'.$sAttributeName.PHP_EOL;
            }
        }
        return trim($sMessage);
    }
}
