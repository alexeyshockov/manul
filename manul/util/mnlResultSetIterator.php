<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.util
 */

/**
 * Господа, дамы! Если требуется произвести доп. работу с данными, которые
 * будет отдавать итератор, просто оберните его в {@see mnlIteratorCallbackDecorator} :)
 *
 * P.S. По результатам изучения PDO и Zend_Db с Максимом узналось, что statement PDO'шный очень
 * неплохо поддерживает интерфейс итератора, а вот Zend_Db_Statement - ничерта. Из этого вывод - если
 * есть уверенность, что используется PDO, в такой вот собственный итератор можно не оборачивать.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.util
 */
class mnlResultSetIterator
    implements Iterator
{
    /**
     * @var Zend_Db_Statement_Interface
     */
    private $_oResultSet;

    /**
     * Current record.
     *
     * @var array
     */
    private $_aRecord;

    /**
     * @param Zend_Db_Statement_Interface $oResultSet
     */
    public function __construct($oResultSet) {
        $this->_oResultSet  = $oResultSet;

        // Инициализируем первый элемент...
        $this->next();
    }

    public function rewind(){
        // Ситуаций, где бы мог потребовться сброс итератора к
        // началу, пока не предвидится... Unsupported, в общем.
    }

    public function current() {
        return $this->_aRecord;
    }

    public function key() {
        // TODO Пока не нужно, но можно возвращать какой-нибудь идентификатор строки...
    }

    public function next() {
        $this->_aRecord = $this->_oResultSet->fetch();

        // Есть мнение, что нужно закрывать курсор (closeCursor?), когда закончили всю выборку...

        return $this->current();
    }

    public function valid() {
        return (bool)$this->current();
    }
}
