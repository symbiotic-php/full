<?php

namespace Symbiotic\Auth;


interface UserInterface
{
    const GROUP_GUEST = 0;

    const GROUP_MANAGER = 1;
    //
    const GROUP_ADMIN = 69;

    /**
     * @return int
     */
    public function getAccessGroup(): int;

    /**
     * @return string
     */
    public function getFullName():string;


}