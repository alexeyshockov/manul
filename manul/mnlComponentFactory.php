<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Roman Alyakritskiy <rompomtoy@gmail.com>
 *
 * @package manul.core
 */

/**
 * Класс-фабрика по работе с экземплярами компонентов. Участвующие в синхронизации типы
 * сущностей читаются из конфига. Возможно получение отдельного экземпляра компонента
 * для отдельного типа сущности.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Roman Alyakritskiy <rompomtoy@gmail.com>
 *
 * @package manul.core
 */
class mnlComponentFactory
{
    private $_cClassResolver;

    private $_cPreviousCollectionTimestampGetter;

    private $_cPreviousCollectionTimestampSetter;

    private $_aComponents = array();

    /**
     * @param string $sPrefix
     * @param array  $aComponents
     */
    public function __construct(
        $cClassResolver,
        $cPreviousCollectionTimestampGetter,
        $cPreviousCollectionTimestampSetter,
        $aComponents
    ) {
        $this->_cClassResolver = $cClassResolver;

        $this->_cPreviousCollectionTimestampGetter = $cPreviousCollectionTimestampGetter;
        $this->_cPreviousCollectionTimestampSetter = $cPreviousCollectionTimestampSetter;

        $this->_aComponents = $aComponents;

        // И сортируем в нужном порядке.
        uasort($this->_aComponents, array($this, '_compareComponentsByWeight'));

        array_walk($this->_aComponents, array($this, '_createComponent'));
    }

    private function _createComponent(&$aComponent) {
        $aComponent['component'] = new mnlDefaultSynchronizationComponent(
            $this->_cClassResolver,
            $aComponent['entity_type'],
            $this->_cPreviousCollectionTimestampGetter,
            $this->_cPreviousCollectionTimestampSetter
        );
    }

    /**
     * Получить экземпляры компонентов для участвующих в синхронизации типов сущностей,
     * отсортированных по весу.
     *
     * @return array
     */
    public function getActiveComponents() {

        $aActiveComponents = array_filter($this->_aComponents, array($this, '_filterComponentsByActivity'));

        $aComponents = array();
        foreach ($aActiveComponents as $aComponent) {
            $aComponents[] = $aComponent['component'];
        }

        return $aComponents;
    }

    /**
     * Получить компонент для определенного типа сущности по его названию.
     *
     * @param string $sEntityType
     *
     * @return mnlComponent
     */
    public function getComponentByType($sEntityType) {
        return (array_key_exists($sEntityType, $this->_aComponents) ? $this->_aComponents[$sEntityType]['component'] : null);
    }

    /**
     * Получить компонент для определенного типа сущности по названию класса.
     *
     * @param string $sClassName Название класса для типа сущности.
     *
     * @return mnlComponent
     */
    public function getComponentByClass($sClassName) {
        $aComponent = current(
            array_filter(
                $this->_aComponents,
                create_function('$aComponent', 'return ($aComponent["entity_class"] == "'.$sClassName.'");')
            )
        );

        return ($aComponent ? $aComponent['component'] : null);
    }

    /**
     * Получить компонент для определенного типа сущности, участвующего в синхронизации,
     * по названию класса.
     *
     * @param string $sClassName Название класса для типа сущности.
     *
     * @return mnlComponent|null
     */
    public function getActiveComponentByClass($sClassName) {
        $oComponent = $this->getComponentByClass($sClassName);

        return ($oComponent && $this->_aComponents[$oComponent->getEntityType()]['active']) ? $oComponent : null;
    }

    private function _filterComponentsByActivity($aComponent) {
        return $aComponent['active'];
    }

    private function _compareComponentsByWeight($aComponent1, $aComponent2) {
        return ($aComponent1['weight'] < $aComponent2['weight']);
    }
}
