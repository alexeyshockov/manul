<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.util
 */

/**
 * Декоратор итератора с обработкой элементов внутри итератора функцией обратного вызова.
 * Перед возврещением каждого элемента из итератора происходит вызов фунции, задаваемой в конструкторе.
 * Функция должна получать в качестве единственного параметра элемент из итератора, а также
 * возвращать некое значение обратно в итератор, которое итератор и вернет в точку своего вызова.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.util
 */
class mnlIteratorCallbackDecorator
    extends IteratorIterator
{
    private $_cCallback;

    /**
     * @param Iterator $oIterator
     * @param callback $cCallback
     */
    public function __construct($oIterator, $cCallback) {
        $this->_cCallback = $cCallback;
        parent::__construct($oIterator);
    }

    public function current() {
        return call_user_func(
            $this->_cCallback,
            $this->getInnerIterator()->current()
        );
    }
}
