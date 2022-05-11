<?php

namespace Symbiotic\Auth;


interface ResultInterface
{
    /**
     * @return array|object|string
     */
    public function getIdentity();

    /**
     * Успешна ли авторизация
     * @return bool
     */
    public function isValid(): bool;
}