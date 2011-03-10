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
class mnlEntityImporterUtf8DecodeDecorator
    implements mnlEntityImporter
{
    private $_oImporter;

    public function __construct($oImporter)
    {
        $this->_oImporter = $oImporter;
    }

    private function _decodeEntity($aEntity) {
        return array_map(array('mnlStringHelper', 'utf8toCp1251'), $aEntity);
    }

    public function fillEntity($aEntity)
    {
        $this->_oImporter->fillEntity($this->_decodeEntity($aEntity));
    }

    public function getEntityDomainValidator()
    {
        return $this->_oImporter->getEntityDomainValidator();
    }

    public function getEntityLastModifiedDate()
    {
        return $this->_oImporter->getEntityLastModifiedDate();
    }

    public function saveEntity()
    {
        return $this->_oImporter->saveEntity();
    }
}
