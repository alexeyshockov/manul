<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.core
 */

/**
 * Контроллер унифицированного импорта.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.core
 */
class mnlMessageProcessor
{
    /**
     * @var callback
     */
    private $_cFailureBroker;

    /**
     * @var mnlComponentFactory
     */
    private $_oComponentFactory;

    /**
     * @var mnlImportingProfiler
     */
    private $_oProfiler;

    /**
     * @var array
     */
    private $_aPacketProcessors;

    /**
     * @param mnlComponentFactory $oComponentFactory
     * @param Zend_Queue          $oQueue
     * @param callback            $cFailureBroker
     */
    public function __construct(
        $oComponentFactory,
        $cFailureBroker
    ) {
        $this->_oComponentFactory = $oComponentFactory;
        $this->_cFailureBroker    = $cFailureBroker;

        // Вынести как-то получше в Service Locator?..
        $this->_oProfiler = mnlRegistry::get('importing_profiler');
    }

    public function registerPacketProcessor($sEventType, $oPacketProcessor) {
        $this->_aPacketProcessors[$sEventType] = $oPacketProcessor;
    }

    public function processMessage($oMessage) {
        // Запоминаем время начала обработки, чтобы потом вычислить время этой самой обработки пакета.
        // TODO Заменить на использование профилировщика.
        $fStartTime = microtime(true);

        $aMessage = $oMessage->toArray();

        $aPacket = unserialize($aMessage['body']);

        // Если так получилось, что пакет в очереди кто-то поправил, сломалось
        // что... В общем, не восстанавливается он обратно.
        if (
            !is_array($aPacket)
            || !array_key_exists('EventType', $aPacket)
            || !array_key_exists('EntityType', $aPacket)
            || !array_key_exists('Entity', $aPacket)
        ) {
            mnlRegistry::get('logger')->log(
                'Broken message or incorrect packet format!',
                Zend_Log::ERR,
                array(
                    // К сожалению, просто message - это сам текст для записи в журнал.
                    'queue_message' => $aMessage
                )
            );
        } else {
            $this->_oProfiler->startProcessing($aPacket['EntityType'], $aPacket['Entity']['Id']);

            $oComponent = $this->_oComponentFactory->getComponentByType($aPacket['EntityType']);
            if (!$oComponent) {
                mnlRegistry::get('logger')->log(
                    'Сomponent for :'.$aPacket['EntityType'].' not found.',
                    Zend_Log::ERR,
                    array(
                        'packet' => $aPacket
                    )
                );
            } else {
                mnlRegistry::get('logger')->log(
                    ':'.$aPacket['EntityType'].'[RemoteId='.$aPacket['Entity']['Id'].'] received.',
                    Zend_Log::INFO
                );

                if (empty($this->_aPacketProcessors[$aPacket['EventType']])) {
                    // FIXME Обработчика для данного события нет, рапортуем.
                } else {
                    try {
                        $iLocalEntityId = $this->_aPacketProcessors[$aPacket['EventType']]->processPacket(
                            $oComponent,
                            $aPacket
                        );

                        // Считаем время обработки...
                        // TODO Заменить на использование профилировщика.
                        $fProcessingTime = microtime(true) - $fStartTime;

                        if ($iLocalEntityId) {
                            mnlRegistry::get('logger')->log(
                                ':'.$aPacket['EntityType'].'[LocalId='.$iLocalEntityId.', RemoteId='.$aPacket['Entity']['Id'].'] processed successfully. Processing time: '.sprintf('%.3f', $fProcessingTime).'.',
                                Zend_Log::INFO
                            );
                        }
                    } catch (mnlException $oException) {
                        // Пишем в лог и отправляем в очередь сбоя, обработка сообщений из
                        // основной очереди продолжается (ошибка при обработке отдельного пакета).
                        mnlRegistry::get('logger')->log(
                            'Something wrong, going to failure broker.',
                            Zend_Log::ERR,
                            array(
                                'packet'    => $aPacket,
                                'exception' => $oException
                            )
                        );

                        call_user_func(
                            $this->_cFailureBroker,
                            $aPacket,
                            $oException
                        );
                    }
                }
            }

            // Сейчас точка работает только при нормальной обработке пакета, а так же при исключительной
            // ситуации уровня пакета. Делать запись профиля даже при ошибке уровня всей синхронизации...
            // Нет большого смысла.
            $this->_oProfiler->finishProcessing();
        }
    }
}
