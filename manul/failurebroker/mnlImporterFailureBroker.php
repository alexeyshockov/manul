<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.failurebroker
 */

/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.failurebroker
 */
class mnlImporterFailureBroker
{
    /**
     * @var callback
     */
    private $_cUnresolvedDependencyHandler;

    /**
     * @var callback
     */
    private $_cDefaultErrorHandler;

    /**
     * @param callback $cDefaultErrorHandler
     * @param callback $cUnresolvedDependencyHandler
     */
    public function __construct($cDefaultErrorHandler, $cUnresolvedDependencyHandler) {
        $this->_cDefaultErrorHandler         = $cDefaultErrorHandler;
        $this->_cUnresolvedDependencyHandler = $cUnresolvedDependencyHandler;
    }

    /**
     * @param array     $aFailedPacket
     * @param Exception $oException
     */
    public function handle($aFailedPacket, $oException) {
        try {
            throw $oException;
        } catch (mnlUnresolvedDependencyException $oException) {
            call_user_func(
                $this->_cUnresolvedDependencyHandler,
                $aFailedPacket,
                $oException
            );
        } catch (Exception $oException) {
            call_user_func(
                $this->_cDefaultErrorHandler,
                $aFailedPacket,
                $oException
            );
        }
    }
}
