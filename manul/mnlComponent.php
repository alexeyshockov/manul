<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */

/**
 * Интерфейс компонента синхронизации.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */
interface mnlComponent
{
    /**
     * Получить экземпляры коллекторов по событиям.
     *
     * Формат результата:
     * <pre>
     * array(
     *  'update'    => array(
     *      'handler'   => callback,
     *      'collector' => object,
     *  ),
     *  'delete'    => array(
     *      ...
     *  ),
     * );
     * </pre>
     *
     * @return array
     */
    public function getCollectors();

    /**
     * Получить экземпляр импортера для сущности с указанным идентификатором.
     *
     * @param int $iId
     *
     * @return mnlEntityImporter|null
     */
    public function getEntityImporter($iId = null);

    /**
     * @return mnlEntityDeletionImporter|null
     */
    public function getEntityDeletionImporter();

    /**
     * Получить манивест.
     *
     * @return array
     */
    public function getManifest();

    /**
     * Получить экземпляр валидатора манифеста.
     *
     * @return mnlEntityManifestValidator
     */
    public function getManifestValidator();

    /**
     * Получить строку с названием типа сущности.
     *
     * @return string
     */
    public function getEntityType();

    /**
     * Получить значение метки времени последнего сбора.
     *
     * @return int
     */
    public function getPreviousCollectionTimestamp();

    /**
     * Установить значение метки времени последнего сбора.
     *
     * @param int $iPreviousCollectionTimestamp
     */
    public function setPreviousCollectionTimestamp($iPreviousCollectionTimestamp);
}
