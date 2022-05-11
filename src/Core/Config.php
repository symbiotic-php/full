<?php

namespace Symbiotic\Core;

use Symbiotic\Core\Support\Collection;



class Config extends Collection {}

/*
 * Todo: Нужно протестировать насколько быстрее, возможно и не нужно тут коллекцию использовать,
 *  если никто не пользуется array dot {@see Symbiotic\Core\Support\Arr::get()} доступом.
 *
use Symbiotic\Container\ArrayAccessTrait;
use Symbiotic\Container\BaseContainerInterface;
use Symbiotic\Container\ItemsContainerTrait;
use Symbiotic\Container\MagicAccessTrait;

class Config implements BaseContainerInterface,\ArrayAccess
{
  use ItemsContainerTrait;
  use ArrayAccessTrait;
  use MagicAccessTrait;

  public function __construct(array $items = [])
  {
      $this->items = $items;
  }
}*/