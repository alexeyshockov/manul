<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.validation
 */

require_once 'Zend/Validate/Interface.php';

/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.validation
 */
class mnlEntityManifestValidator
    implements Zend_Validate_Interface
{
    /**
     * @var array
     */
    private $_aManifest;

    /**
     * @var array
     */
    private $_aMessages;

    /**
     * @var string
     */
    private $_sEntityType;

    /**
     * @param array $aManifest
     * @param string $sEntityType
     */
    public function __construct($aManifest, $sEntityType) {
        $this->_aManifest   = $aManifest;
        $this->_sEntityType = $sEntityType;

        $this->_aMessages   = array();
    }

    public function isValid($aEntity) {
        $bIsValid = true;

        foreach($this->_aManifest as $sAttributeName => $aAttributeManifest) {
            // Все ли атрибуты из манифеста на месте?
            if (!array_key_exists($sAttributeName, $aEntity)) {
                $bIsValid = false;

                $this->_aMessages[] = 'Missed attribute: :'.$this->_sEntityType.'/'.$sAttributeName.'.';
            }

            // Проверяем тип, если он есть в манифесте. Можно использовать gettype, но разработчики не
            // рекомендуют (http://php.net/manual/en/function.gettype.php).
            if (
                // Не делаем проверку отсутствующих элементов (если его нет, что проверять?). Проверку
                // на отсутствие см. выше.
                isset($aEntity[$sAttributeName])
                &&
                $aAttributeManifest['Type']
                &&
                in_array(
                    $aAttributeManifest['Type'],
                    array('int', 'float', 'string', 'bool')
                )
            ) {
                // FIXME А если null? Может же быть отсутствие значения? Это же нормально должно быть... :(
                if (!call_user_func('is_'.$aAttributeManifest['Type'], $aEntity[$sAttributeName])) {
                    $bIsValid = false;

                    $this->_aMessages[] = 'Attribute value type is not '.$aAttributeManifest['Type'].': :'.$this->_sEntityType.'/'.$sAttributeName.'.';
                }
            }

            // И кодировку, если это строка.
            // TODO Сделать рекурсивную проверку в случае массива (пока массивов нигде нет...).
            if (
                    is_string($aEntity[$sAttributeName])
                    &&
                    !(
                        mb_detect_encoding($aEntity[$sAttributeName]) == 'UTF-8'
                        ||
                        mb_detect_encoding($aEntity[$sAttributeName]) == 'ASCII'
                    )
            ) {
                $bIsValid = false;

                $this->_aMessages[] = 'Attribute value is not in UTF-8: :'.$this->_sEntityType.'/'.$sAttributeName.'.';
            }
        }

        return $bIsValid;
    }

    public function getMessages() {
        return $this->_aMessages;
    }
}