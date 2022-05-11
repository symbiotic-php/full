<?php

namespace Symbiotic\Auth;


class AuthResult implements ResultInterface
{

    protected ?UserInterface $user;

    protected string $error = '';

    public function __construct(UserInterface $user = null)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param string $error
     * @return $this
     */
    public function setError(string $error): self
    {
        $this->error = $error;
        return $this;
    }

    public function isValid(): bool
    {
        return ($this->user instanceof UserInterface);
    }

    /**
     * @return UserInterface|null
     */
    public function getIdentity(): ?UserInterface
    {
        return $this->user;
    }

}