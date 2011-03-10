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
class mnlEntityRuleValidator
    implements mnlEntityDomainValidator
{
    /**
     * @var mixed
     */
    private $_mEntity;

    /**
     * @var array
     */
    private $_aRules;

    /**
     * @param mixed $mEntity
     * @param array $aRules
     */
    public function __construct($mEntity, $aRules) {
        $this->_mEntity = $mEntity;
        $this->_aRules = $aRules;

        // Сразу и проверяем, т.к. все составляющие у нас есть. Чтобы объект не
        // находился в "неопределённом" состоянии.
        $this->isValid();
    }

    public function isValid() {
        $bIsValid = true;
        foreach ($this->_aRules as $sRuleName => $aRule) {
            $oValidator = $aRule['validator'];
            $mValue     = ($aRule['value_getter'] ? call_user_func($aRule['value_getter'], $this->_mEntity) : $this->_mEntity);

            if (!$oValidator->isValid($mValue)) {
                $bIsValid = false;
            }
        }

        return $bIsValid;
    }

    public function getMessages() {
        $aMessages = array();

        foreach ($this->_aRules as $sRuleName => $aRule) {
            $aValidatorMessages = $aRule['validator']->getMessages();
            if (!empty($aValidatorMessages)) {
                $aMessages[$sRuleName] = $aRule['validator']->getMessages();
            }
        }

        return $aMessages;
    }

    public function getMessagesAsString() {
        return $this->joinMessages(PHP_EOL, $this->getMessages());
    }

    private function joinMessages($sGlue, $aMessages) {
        foreach ($aMessages as $mMessage) {
            if (is_array($mMessage)) {
                $sJoinedMessages[] = $this->joinMessages($sGlue, $mMessage);
            } else {
                $sJoinedMessages[] = $mMessage;
            }
        }
        return implode($sGlue, $sJoinedMessages);
    }

}
