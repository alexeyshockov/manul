<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.validation
 */

/**
 * Интерфейс валидатора правил.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.validation
 */
interface mnlEntityDomainValidator
{
    /**
     * Проверить, является ли сущность, загруженная в валидатор, валидной.
     */
    public function isValid();

    /**
     * Получить сообщения об ошибках валидации.
     */
    public function getMessages();

    /**
     * Получить сообщения об ошибках валидации склеенные в одну строку.
     */
    public function getMessagesAsString();
}
