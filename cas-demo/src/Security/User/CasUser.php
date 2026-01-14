<?php

namespace App\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class CasUser implements UserInterface
{
    private string $userIdentifier;
    private array $roles;
    private array $attributes;

    public function __construct(
        string $userIdentifier,
        array $roles = ['ROLE_USER'],
        array $attributes = []
    ) {
        $this->userIdentifier = $userIdentifier;
        $this->roles = $roles;
        $this->attributes = $attributes;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    public function eraseCredentials(): void
    {
        // Rien à faire (CAS)
    }

    // ---- Attributs CAS ----

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }
}
