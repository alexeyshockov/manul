<?php
/**
 * Extended log formatter.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.util
 */

/**
 * Extended log formatter.
 *
 * Typical use case:
 *
 * <pre>
 * $oSimpleFormatter = new Zend_Log_Formatter_Simple(
 *  Zend_Log_Formatter_Simple::DEFAULT_FORMAT.' %exception%'
 * );
 *
 * $oFormatter = new mnlLogFormatterDecorator(
 *  $oSimpleFormatter,
 *  array(
 *      'exception' => create_function('$oException', 'return var_export($oException, true);'),
 *  )
 * );
 *
 * // Create logger and writer with $oFormatter...
 *
 * $oLogger->log(
 *  'Test message.',
 *  Zend_Log::INFO,
 *  array(
 *      'exception' => $oException,
 *  )
 * );
 * <pre>
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.util
 */
// TODO Таки подумать над названием?
class mnlLogFormatterDecorator
    implements Zend_Log_Formatter_Interface
{
    /**
     * @var Zend_Log_Formatter_Interface
     */
    private $_oFormatter;

    /**
     * @var array
     */
    private $_aProcessors;

    /**
     * @param Zend_Log_Formatter_Interface $oFormatter
     * @param array                        $aProcessors Опциональный обработчик в строку для каждого доп. параметра.
     */
    public function __construct(
        $oFormatter,
        $aProcessors = array()
    ) {
        $this->_oFormatter  = $oFormatter;
        $this->_aProcessors = $aProcessors;
    }

    public function format($aEvent) {
        // Идём именно по списку обработчиков, чтобы, если даже значения для доп. поля нет,
        // поставить вместо него пустую строку (иначе он просто не заменится и так и останется
        // в записи журнала).
        foreach ($this->_aProcessors as $sName => $cProcessor) {
            if (array_key_exists($sName, $aEvent)) {
                $aEvent[$sName] = call_user_func(
                    $cProcessor,
                    $aEvent[$sName]
                );
            } else {
                $aEvent[$sName] = '';
            }
        }

        return $this->_oFormatter->format($aEvent);
    }
}
