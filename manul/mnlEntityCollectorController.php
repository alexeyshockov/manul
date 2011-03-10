<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */

/**
 * Событийный сборщик.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */
class mnlEntityCollectorController
    extends mnlCollectorController
{
    /**
     * Производит формирование пакета для указанной сущности (аналогично сбору)
     * Учитывается глобальная переменная, сигнализирующая о том, что в настоящий момент
     * происходит импорт. Защищаемся от каскадных вызовов при сохранении в импорте.
     *
     * @param string $sEvent
     * @param object $oEntity
     */
    public function collectEntity($sEvent, $oEntity) {
        $oComponent = $this->_oComponentFactory->getActiveComponentByClass(get_class($oEntity));
        if (!$oComponent) {
            mnlRegistry::get('logger')->info(
                'Active component for class '.get_class($oEntity).' not found.'
            );

            return;
        }

        $aCollectors = $oComponent->getCollectors();

        if (empty($aCollectors[$sEvent]['event_handler'])) {
            mnlRegistry::get('logger')->info(
                'Component for class '.get_class($oEntity).' does not support event collecting.'
            );

            return;
        }

        $aEntity = call_user_func($aCollectors[$sEvent]['event_handler'], $oEntity);

        if (!$aEntity) {
            // Сборщик как-бы говорит нам, что запись по ему одному ведомым причинам
            // отправлять на синхронизацию не нужно (к примеру, если мы не синхронизируем
            // неопубликованные программы).

            mnlRegistry::get('logger')->info(
                'Collector says, that entity does not need to be collected...'
            );

            return;
        }

        $this->_send($sEvent, $oComponent, $aEntity);
    }
}
