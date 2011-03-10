<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */

/**
 * Контроллер унифицированного сбора. Запускает либо сбор всех изменений, либо сбор
 * отдельной сущности по типу сущности и идентификатору.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.core
 */
class mnlCollectorController
{
    /**
     * @var Zend_Queue
     */
    protected $_oQueue;

    /**
     * @var mnlComponentFactory
     */
    protected $_oComponentFactory;

    /**
     * @var array
     */
    protected $_aPacketBuilders;

    /**
     * @param mnlComponentFactory $oComponentFactory
     * @param Zend_Queue          $oQueue
     */
    public function __construct($oComponentFactory, $oQueue) {
        $this->_oComponentFactory = $oComponentFactory;
        $this->_oQueue            = $oQueue;
    }

    /**
     * @param string                    $sEvent
     * @param mnlCollectorPacketBuilder $oPacketBuilder
     */
    public function registePacketBuilder($sEvent, $oPacketBuilder) {
        $this->_aPacketBuilders[$sEvent] = $oPacketBuilder;
    }

    /**
     * Здесь мы явно привязываемся к {@link mnlCollector}.
     *
     * @param string $sEntityType
     * @param int    $iId
     */
    private function _collectById($sEntityType, $iId) {
        $sEvent     = 'update';
        $oComponent = $this->_oComponentFactory->getComponentByType($sEntityType);

        if (!$oComponent) {
            mnlRegistry::get('logger')->log(
                $sEntityType.' component not found!',
                Zend_Log::WARN
            );

            return;
        }

        $aCollectors = $oComponent->getCollectors();

        if (empty($aCollectors[$sEvent])) {
            // TODO Рапортуем, что не поддерживается функциональность компонентом для данной сущности.

            return;
        }

        $oCollector = $aCollectors[$sEvent]['collector'];

        // TODO Event in message...
        mnlRegistry::get('logger')->log(
            'Collecting :'.$sEntityType.'[LocalId='.$iId.'].',
            Zend_Log::INFO
        );

        // mnlException ловить, в общем, смысла никакого нет, как в других
        // местах, т.к. тут он уже ни на что не повляет.
        $this->_send(
            $sEvent,
            $oComponent,
            $oCollector->collectById($iId)
        );
    }

    /**
     * Сбор отдельного типа сущности по обычным правилам (по последней метке времени).
     *
     * @todo А нужно ли по обычным правилам? Возможно, без учёта времени?
     *
     * @param string $sEntityType
     */
    private function _collectByType($sEntityType) {
        $this->_collectByComponent(
            $this->_oComponentFactory->getComponentByType($sEntityType)
        );
    }

    private function _collectByComponent($oComponent) {
        $iRecentCollectTime = $oComponent->getPreviousCollectionTimestamp();

        mnlRegistry::get('logger')->log(
            'Collecting '.$oComponent->getEntityType().' entities (previously checked on '.date(DATE_ISO8601, $iRecentCollectTime).').',
            Zend_Log::INFO
        );

        $aCollectors = $oComponent->getCollectors();

        $iCollectTime = time();

        // FIXME Разобраться с сообщениями, вкрячить туда тип события.
        foreach ($aCollectors as $sEvent => $aCollector) {
            if (empty($aCollectors[$sEvent]['handler'])) {
                // TODO "Not supported" to log?

                continue;
            }

            $aEntities = call_user_func($aCollector['handler'], $iRecentCollectTime);

            $iProcessedEntities = 0;

            foreach ($aEntities as $aEntitу) {
                $this->_send($sEvent, $oComponent, $aEntitу);

                $iProcessedEntities++;
            }

            mnlRegistry::get('logger')->log(
                $iProcessedEntities.' changed entities collected.',
                Zend_Log::INFO
            );
        }

        // Выставляем дату только тогда, когда весь процесс прошёл нормально.
        // TODO Возможно, выставлять для каждого типа события?
        $oComponent->setPreviousCollectionTimestamp($iCollectTime);
    }

    private function _collect() {
        $aComponents = $this->_oComponentFactory->getActiveComponents();

        mnlRegistry::get('logger')->log(
            count($aComponents).' active components found.',
            Zend_Log::INFO
        );

        foreach ($aComponents as $oComponent) {
            try {
                $this->_collectByComponent($oComponent);
            } catch (mnlException $oException) {
                // Пишем в лог, работа коллектора продолжается (ошибка в работе компонента отдельной сущности).
                mnlRegistry::get('logger')->log(
                    'Something wrong with current component, collecting others.',
                    Zend_Log::ERR,
                    array(
                        'exception' => $oException
                    )
                );
            }
        }
    }

    /**
     * Произвести сбор. Если необходимо произвести сбор сущности определенного типа с
     * определенным идентификатором, указываем параметры. Если параметры не заполнять, будет
     * производиться бор всех последних изменений
     *
     * @param string    $sEntityType
     * @param int       $iId
     */
    public function collect($sEntityType = null, $iId = null) {
        if ($sEntityType && $iId) {
            $this->_collectById($sEntityType, $iId);
        } else if ($sEntityType) {
            $this->_collectByType($sEntityType);
        } else {
            $this->_collect();
        }
    }

    protected function _send($sEvent, $oComponent, $aEntity) {
        // Если вообще сброщик пакета для данного типа сообытия?
        if (empty($this->_aPacketBuilders[$sEvent])) {
            // Нет. Делать, значит, нечего.

            // TODO Бибикать об этом деле в журнал.

            return;
        }
        if (!$aEntity) {
            // Сборщик как-бы говорит нам, что запись по ему одному ведомым причинам
            // отправлять на синхронизацию не нужно (к примеру, если мы не синхронизируем
            // неопубликованные программы).

            mnlRegistry::get('logger')->info(
                'Collector says, that entity does not need to be collected...'
            );

            return;
        }

        $this->_oQueue->send(
            serialize(
                $aPacket = array_merge(
                    $this->_aPacketBuilders[$sEvent]->buildPacket(
                        $oComponent,
                        $aEntity
                    ),
                    array('EventType' => $sEvent)
                )
            )
        );

        // FIXME Поправить с учётом типа события.
        mnlRegistry::get('logger')->log(
            ':'.$oComponent->getEntityType().'[LocalId='.$aEntity['Id'].'] collected and sent to queue.',
            Zend_Log::INFO,
            array(
                'packet' => $aPacket,
            )
        );
    }
}
