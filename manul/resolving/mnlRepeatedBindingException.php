<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.resolving
 */

/**
 * Исключение возникает при попытке забиндить один идентификатор ко второму в
 * ситуации, когда такой биндинг уже есть.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.resolving
 */
class mnlRepeatedBindingException
    extends mnlResolverException
{

}
