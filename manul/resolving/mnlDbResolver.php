<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.resolving
 */

/**
 * Сервис Резолвера подсистемы синхронизации Manul. Функционал класса позволяет установливать отображение
 * идентификаторов сущностей в одной системе на идентификаторы тех же сущностей в другой системе и обратно.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.resolving
 */
class mnlDbResolver
    implements mnlResolver
{
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_oConnection;

    /**
     * @var string
     */
    protected $_sTableName;

    /**
     * State, типа.
     *
     * @var array
     */
    protected $_aContext;

    /**
     * @param Zend_Db_Adapter_Abstract $oConnection Соединение к БД, где находится таблица резольвера.
     * @param string                   $sTableName  Имя таблицы резольвера.
     * @param array                    $aContext    Соответствие колонок в таблице.
     */
    public function __construct($oConnection, $sTableName = 'manul_mapping', $aContext) {
        $this->_oConnection = $oConnection;
        $this->_sTableName  = $sTableName;
        $this->_aContext    = $aContext;
    }

    public function resolveRemoteId($sType, $iRemoteId) {
        $iResult = $this->_oConnection->fetchOne(
            'SELECT '.$this->_aContext['local_field_name'].'
             FROM '.$this->_sTableName.'
             WHERE type = ?
             AND '.$this->_aContext['remote_field_name'].' = ?',
            array($sType, $iRemoteId)
        );

        return ($iResult) ? $iResult : null;
    }

    public function bindWithRemoteId($sType, $iRemoteId, $iLocalId) {
        // Вся магия - в "...AND '.$this->_aContext['remote_field_name'].' IS NULL". С этим
        // условием обновление будет, если только до этого соответствия не было.
        $bAlreadyExist = !(bool)$this->_oConnection->update(
            $this->_sTableName,
            array($this->_aContext['local_field_name'] => $iLocalId),
            "type = '".$sType."'
             AND ".$this->_aContext['remote_field_name']." = ".$iRemoteId."
             AND ".$this->_aContext['local_field_name']." IS NULL"
        );

        if ($bAlreadyExist) {
            $bExists = (bool)$this->_oConnection->fetchOne(
                "SELECT COUNT(*) AS cnt
                 FROM ".$this->_sTableName."
                 WHERE type='".$sType."'
                 AND ".$this->_aContext['remote_field_name']." = ".$iRemoteId
            );
            if (!$bExists) {
                throw new mnlResolverException('Trying to bind with unknown '.$this->_aContext['local_field_name'].' = '.$iRemoteId.' for '.$sType.'.');
            } else {
                throw new mnlRepeatedBindingException();
            }
        }
    }

    public function registerLocalId($sType, $iLocalId) {
        $this->_oConnection->query(
            'INSERT INTO '.$this->_sTableName.'(type, '.$this->_aContext['local_field_name'].')
                SELECT ?, ? FROM (SELECT "type", "id") data WHERE NOT EXISTS(
                    SELECT * FROM '.$this->_sTableName.'
                        WHERE
                            type = ?
                            AND '.$this->_aContext['local_field_name'].' = ?
                ) LIMIT 1',
            array($sType, $iLocalId, $sType, $iLocalId)
        );
    }
}
