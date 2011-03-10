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
class mnlInhibitorQueueDecorator
{
    /**
     * @var Zend_Queue
     */
    private $_oDelayQueue;

    /**
     * @var Zend_Queue
     */
    private $_oReturnQueue;

    /**
     * @param Zend_Queue $oDelayQueue  Очередь задержки.
     * @param Zend_Queue $oReturnQueue Целевая (оборачиваемая) очередь.
     */
    public function __construct($oDelayQueue, $oReturnQueue) {
        $this->_oDelayQueue  = $oDelayQueue;
        $this->_oReturnQueue = $oReturnQueue;
    }

    /**
     * Отправить пакет с задержкой.
     *
     * @param mixed $mMessage
     *
     * @return bool
     */
    public function send($mMessage) {
        return $this->_oDelayQueue->send(serialize(
            array(
                'ReturnQueueName' => $this->_oReturnQueue->getName(),
                'DelayedMessage'  => $mMessage,
            )
        ));
    }
}
