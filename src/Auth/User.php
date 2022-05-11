<?php

namespace Symbiotic\Auth;


class User implements UserInterface
{
    /**
     * @var int
     */
    protected int $accessGroup = 0;

    /**
     * @var string
     */
    protected string $fullName = '';

    /**
     * @var int|null
     */
    protected ?int $id;

    public function __construct(int $accessGroup = self::GROUP_GUEST, string $fullName = '', int $id = null)
    {
        $this->accessGroup = $accessGroup;
        $this->fullName = $fullName;
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccessGroup(): int
    {
        return $this->accessGroup;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

}