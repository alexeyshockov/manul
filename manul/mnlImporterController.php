<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */

/**
 * Контроллер унифицированного импорта.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */
class mnlImporterController
{
    /**
     * Packets to process in one run.
     *
     * @var int
     */
    private $_iPacketsToProcess;

    /**
     * @var Zend_Queue
     */
    private $_oQueue;

    /**
     * @var mnlMessageProcessor
     */
    private $_oMessageProcessor;

    /**
     * @param Zend_Queue $oQueue
     * @param int        $iMessagesToProcess
     */
    public function __construct(
        $oQueue,
        $iPacketsToProcess = 1000
    ) {
        $this->_oQueue            = $oQueue;
        $this->_iPacketsToProcess = $iPacketsToProcess;
    }

    public function registerMessageProcessor($oMessageProcessor) {
        $this->_oMessageProcessor = $oMessageProcessor;
    }

    /**
     * Получаем последовательно по одному сообщению из очереди, вытаскиваем пакет и начинаем его обработку.
     *
     * Метод следит за глобальной переменной, сигнализирующей о необходимости останова во время
     * выполнения скрипта (если из консоли).
     *
     * @return int Exit status процесса импорта.
     */
    public function import($bRepeatMode = false, $iRepeatInterval = 5) {
        $iExitCode          = 0;
        $iProcessedMessages = 0;

        while (true) {
            // $GLOBALS['bStop'] - флаг, который может установиться при сигнале SIGINT,
            // SIGTERM и т.д., когда процесс, в котором запущен импорт, завершают. При
            // помощи своего обработчика (см. скрипты запуска) эти сигналы можно перехватить
            // и завершиться только после полной отработки очередного шага.
            while (!$GLOBALS['bStop']) {
                try {
                    $aMessages = $this->_oQueue->receive(1);
                } catch (Zend_Queue_Exception $oException) {
                    // "SQLSTATE[40001]: Serialization failure: 1213 Deadlock found when trying
                    // to get lock; try restarting transaction" - в таком случае стоит просто попробовать
                    // снова ;)
                    // TODO Кстати, странно, что такая ошибка не обрабатывается на уровне драйвера системы обмена
                    // сообщениями. Не пользовательская ведь ошибка, не?
                    if (40001 == $oException->getCode()) {
                        // TODO Вообще, можно сделать счётчик попыток и определить максимальное количество. Оно
                        // нужно?
                        mnlRegistry::get('logger')->warn(
                            'Deadlock found when receiving message!'
                        );

                        continue;
                    }

                    // Пробрасываем, если не можем обработать сами.
                    throw $oException;
                }

                if (count($aMessages) == 0) {
                    mnlRegistry::get('logger')->log(
                        'Queue is empty.',
                        Zend_Log::INFO
                    );

                    break;
                }

                // TODO Треш. Но работает.
                foreach ($aMessages as $oMessage) {

                }

                $this->_oMessageProcessor->processMessage($oMessage);

                $this->_oQueue->deleteMessage($oMessage);

                $iProcessedMessages++;

                if ($iProcessedMessages >= $this->_iPacketsToProcess) {
                    // Если  достигнут лимит пакетов, то останавливаемся. Остатки очереди запроцессятся в других процессах.
                    // Такой механизм должен решить проблему нехватки памяти.
                    mnlRegistry::get('logger')->log(
                        'Packets per process limit reached.',
                        Zend_Log::INFO
                    );

                    // Exit status для продолжения обработки.
                    // TODO Magic number detected. Вынести бы.
                    $iExitCode = 254;

                    break 2;
                }
            }

            if ($GLOBALS['bStop']) {
                break;
            }

            if (!$bRepeatMode) {
                break;
            }

            sleep($iRepeatInterval);
        }

        mnlRegistry::get('logger')->log(
            'Messages processed: '.$iProcessedMessages.'.',
            Zend_Log::INFO
        );

        if ($GLOBALS['bStop']) {
            $GLOBALS['bStopAllowed']    = true;
            $iExitCode                  = 0;
        }

        return $iExitCode;
    }
}
