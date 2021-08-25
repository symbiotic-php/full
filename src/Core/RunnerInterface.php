<?php

namespace Dissonance\Core;


interface RunnerInterface
{
    public function isHandle(): bool;

    public function run(): void;
}
