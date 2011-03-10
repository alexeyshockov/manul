<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.logging
 */

/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.logging
 */
class mnlLogFormatter
{
    /**
     * @param Exception $oException
     *
     * @return string
     */
    public static function formatException($oException, $iLevel = 1) {
        $sString =
            PHP_EOL.str_repeat("\t", $iLevel).get_class($oException)." in ".$oException->getFile()." at line ".$oException->getLine().":".
            PHP_EOL.str_repeat("\t", $iLevel)."\t".join(PHP_EOL.str_repeat("\t", $iLevel)."\t", split(PHP_EOL, $oException->getTraceAsString()));
        if ($oException->getMessage()) {
            $sString .= PHP_EOL.str_repeat("\t", $iLevel)."\t".'Message: '.$oException->getMessage();
        }

        if (
            $oException instanceof mnlManifestValidationException
            || $oException instanceof mnlDomainValidationException
        ) {
            $sString .= PHP_EOL.str_repeat("\t", $iLevel)."\t".'Messages:'.PHP_EOL.self::_toString($oException->getValiadtionMessages(), $iLevel + 2);
        }

        if (is_callable(array($oException, 'getPrevious')) && $oException->getPrevious()) {
            $sString .= self::formatException($oException->getPrevious(), $iLevel + 1);
        }

        return $sString;
    }

    private static function _toString($mElement, $iIndent) {
        $sResult = '';
        if (is_array($mElement)) {
            $bFirst = true;
            foreach ($mElement as $sKey => $mValue) {
                if ($bFirst) {
                    $bFirst = false;
                } else {
                    $sResult .= PHP_EOL;
                }

                $sResult .= str_repeat("\t", $iIndent);

                $sResult .= (is_string($sKey) ? $sKey.': ' : '');
                if (is_array($mValue)) {
                    $sResult .= PHP_EOL.self::_toString($mValue, $iIndent + 1);
                } else {
                    if (is_string($mValue) && (strlen($mValue) > 100)) {
                        $sResult .= var_export(substr($mValue, 0, 100), true).' (truncated to 100 characters)';
                    } else {
                        $sResult .= var_export($mValue, true);
                    }
                }
            }
        } else {
            if (is_string($mValue) && (strlen($mValue) > 100)) {
                $sResult .= var_export(substr($mValue, 0, 100), true).' (truncated to 100 characters)';
            } else {
                $sResult .= var_export($mValue, true);
            }
        }

        return $sResult;
    }

    /**
     * @param array $aPacket
     *
     * @return string
     */
    public static function formatPacket($aPacket) {
        return PHP_EOL."\t"."Packet: ".PHP_EOL.self::_toString($aPacket, 2);
    }
}
