<?php

namespace Dissonance\Packages;


interface PackagesLoaderInterface
{
    public function load(PackagesRepositoryInterface $repository);
}
