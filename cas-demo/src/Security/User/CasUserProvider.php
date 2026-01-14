<?php

namespace App\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class CasUserProvider implements UserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // À ce stade, CAS a déjà authentifié
        return new CasUser(
            $identifier,
            ['ROLE_USER']
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof CasUser) {
            throw new UnsupportedUserException();
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === CasUser::class;
    }
}
