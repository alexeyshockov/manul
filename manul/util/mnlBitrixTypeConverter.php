<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.entities
 */

/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.util
 */
class mnlBitrixTypeConverter
{
    public static function bool2YN($bBool) {
        return ($bBool ? 'Y' : 'N');
    }

    public static function yn2bool($sYN) {
        return ('Y' == $sYN);
    }

    public static function datetime2timestamp($sDatetime) {
        // TODO http://dev.1c-bitrix.ru/api_help/main/functions/date/maketimestamp.php
        return strtotime(
            ConvertDateTime(
                $sDatetime,
                'YYYY-MM-DD HH:MI:SS' // Такой формат всегда поймет strtotime.
            )
        );
    }

    public static function timestamp2datetime($iTimestamp) {
        return ConvertTimeStamp($iTimestamp, "FULL");
    }
}
