<?php

/**
 * User checker.
 */

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserChecker.
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator Translator
     */
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Checks if the user is blocked before authentication.
     *
     * @param UserInterface $user User
     *
     * @throws CustomUserMessageAccountStatusException If the user is blocked
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getIsBlocked()) {
            throw new CustomUserMessageAccountStatusException($this->translator->trans('message.accountBlocked'));
        }
    }

    /**
     * Checks the user after authentication.
     *
     * @param UserInterface $user User
     */
    public function checkPostAuth(UserInterface $user): void
    {
    }
}
