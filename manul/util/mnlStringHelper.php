<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.util
 */

/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.util
 */
class mnlStringHelper
{
    /**
     * Decode string from UTF-8 to CP1251.
     *
     * @param string|array $mData
     *
     * @return string
     */
    public static function utf8toCp1251($mData) {
        if (is_array($mData)) {
            return array_map(array(__CLASS__, __FUNCTION__), $mData);
        }

        return (is_string($mData) ? iconv('UTF-8', 'CP1251', $mData) : $mData);
    }

    /**
     * Encode CP1251 string to UTF-8.
     *
     * @param string|array $mData
     *
     * @return string
     */
    public static function cp1251toUtf8($mData) {
        if (is_array($mData)) {
            return array_map(array(__CLASS__, __FUNCTION__), $mData);
        }

        return (is_string($mData) ? iconv('CP1251', 'UTF-8', $mData) : $mData);
    }
}
