<?php
/**
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.inhibitor
 */

/**
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.inhibitor
 */
class mnlInhibitor
{
    /**
     * Очередь с сообщениями, пересылку которых в другую очередь надо затормозить.
     *
     * @var Zend_Queue
     */
    private $_oDelayQueue;

    /**
     * Минимальный период торможения пакета перед пересылкой из очереди удержания в целевую очередь
     * Минимальность обусловлена тем, что в инфраструктуре очередей гарантировать
     * точную по времени (секунда в секунду) задержку невозможно.
     *
     * @var int
     */
    private $_iDefaultDelayTimeout;

    /**
     * Коллбэк, который который вернет объект очереди по ее имени.
     *
     * @var callback
     */
    private $_cQueueGetter;

    public function __construct($oDalayQueue, $cQueueGetter, $iDelayTimeout = 60) {
        $this->_oDelayQueue          = $oDalayQueue;
        $this->_cQueueGetter         = $cQueueGetter;
        $this->_iDefaultDelayTimeout = $iDelayTimeout;
    }

    public function run() {
        while($aMessages = $this->_oDelayQueue->receive(1)) {
            if (count($aMessages) == 0) {
                mnlRegistry::get('logger')->log(
                    'Delay queue is empty.',
                    Zend_Log::INFO
                );

                break;
            }

            mnlRegistry::get('logger')->log(
                'Trying to re-send packet from delay queue.',
                Zend_Log::INFO
            );

            // TODO Треш. Но работает.
            foreach ($aMessages as $oMessage) {}

            if ((time() - $oMessage->created) > $this->_iDefaultDelayTimeout) {
                $aDelayPacket = unserialize($oMessage->body);

                $oQueue = call_user_func($this->_cQueueGetter, $aDelayPacket['ReturnQueueName']);
                $oQueue->send($aDelayPacket['DelayedMessage']);

                mnlRegistry::get('logger')->log(
                    'Packet is re-sent from delay to '.$oQueue->getName().' queue',
                    Zend_Log::INFO
                );

                $oQueue->deleteMessage($oMessage);
            } else {
                mnlRegistry::get('logger')->log(
                    'Waiting packet.',
                    Zend_Log::INFO
                );
            }
        }
    }
}
