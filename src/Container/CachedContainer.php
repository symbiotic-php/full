<?php

declare(strict_types=1);

namespace Symbiotic\Container;


class CachedContainer extends Container implements CachedContainerInterface
{
    use CachedContainerTrait;
}