<?php

namespace Dissonance\Core\Support;


/**
 * Class Collection
 * @package Dissonance\Core\Support
 * @see  https://laravel.com/docs/5.8/collections
 */
class Collection implements
    \ArrayAccess,
    ArrayableInterface,
    JsonableInterface,
    \Countable,
    \IteratorAggregate,
    \JsonSerializable
{
    use CollectionTrait;
}
