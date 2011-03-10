<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.resolving
 */

/**
 * Сервис Резолвера подсистемы синхронизации Manul.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.resolving
 */
interface mnlResolver
{
    public function resolveRemoteId($sType, $iRemoteId);

    public function bindWithRemoteId($sType, $iRemoteId, $iLocalId);

    public function registerLocalId($sType, $iLocalId);
}
