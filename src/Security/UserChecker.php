<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    /**
     * @var string The message to return in case of error
     */
    private string $message = 'Vous devez confirmer votre compte pour pouvoir vous connecter.';

    /**
     * {@inheritDoc}
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->getIsActive()) {
            // The message passed to this exception is meant to be displayed to the user
            throw new CustomUserMessageAccountStatusException($this->message);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->getIsActive()) {
            // the message passed to this exception is meant to be displayed to the user
            throw new CustomUserMessageAccountStatusException($this->message);
        }
    }
}
