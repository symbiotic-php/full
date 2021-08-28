<?php

namespace Symbiotic\Packages;


interface PackagesLoaderInterface
{
    public function load(PackagesRepositoryInterface $repository);
}
