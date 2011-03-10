<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.failurebroker
 */

/**
 * Для каждой очереди есть своя очередь сбоев (это к тому, откуда взялся первый параметр), т.е.
 * это не случай шаблона Return Address ({@link http://www.eaipatterns.com/ReturnAddress.html}).
 * И есть какой-то обработчик фатальных ошибок.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.failurebroker
 */
class mnlUnresolvedDependencyFailureHandler
{
    /**
     * @var Zend_Queue
     */
    private $_oDalayQueue;

    /**
     * @var mnlFailureProcessor
     */
    private $_cErrorHandler;

    private $_sCycleCountHeader = 'UnresolvedDependencyCycleCount';

    private $_iCycleCountMaximum;

    /**
     * @param mnlFailureProcessor $cDefaultErrorHandler
     * @param Zend_Queue          $oQueue               Оригинальная очередь, откуда пакетики.
     * @param int                 $iCount
     */
    public function __construct($cDefaultErrorHandler, $oDelayQueue, $iCycleCountMaximum = 10) {
        $this->_oDalayQueue        = $oDelayQueue;
        $this->_cErrorHandler      = $cDefaultErrorHandler;
        $this->_iCycleCountMaximum = $iCycleCountMaximum;
    }

    /**
     * @param array     $aFailedPacket Пакет с информацией о сбое.
     * @param Exception $oException
     */
    public function handle($aFailedPacket, $oException) {
        $iCycleCount = $aFailedPacket[$this->_sCycleCountHeader] =
            (array_key_exists($this->_sCycleCountHeader, $aFailedPacket)
                ? $aFailedPacket[$this->_sCycleCountHeader] + 1
                : 1
            );

        if ($iCycleCount <= $this->_iCycleCountMaximum) {
            $this->_oDalayQueue->send(
                serialize($aFailedPacket)
            );
        } else {
            // Сбой - уже не сбой, а самая настоящая ошибка. Пишем его в очередь ошибок.
            call_user_func(
                $this->_cErrorHandler,
                $aFailedPacket,
                $oException
            );
        }
    }
}
