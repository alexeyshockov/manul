<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.transaction
 */

/**
 * Транзакционный декоратор для обработчика пакета.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.transaction
 */
interface mnlTransactionManager
{
    public function beginTransaction();

    public function commit();

    public function rollBack();
}
