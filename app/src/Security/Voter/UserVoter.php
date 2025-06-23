<?php

/**
 * User voter.
 */

namespace App\Security\Voter;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserVoter.
 */
class UserVoter extends Voter
{
    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'USER_EDIT';

    /**
     * Block permission.
     *
     * @const string
     */
    public const BLOCK = 'USER_BLOCK';

    /**
     * View permission.
     *
     * @const string
     */
    public const VIEW = 'USER_VIEW';

    /**
     * Constructor.
     *
     * @param UserRepository $userRepository User repository
     */
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::BLOCK, self::VIEW])
            && $subject instanceof User;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof UserInterface) {
            return false;
        }

        /** @var User $targetUser */
        $targetUser = $subject;

        if (self::VIEW === $attribute) {
            return in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
        }

        // only admins can edit or block users
        if (!in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            return false;
        }

        if (self::BLOCK === $attribute) {
            if ($currentUser === $targetUser) {
                return false;
            }

            if (in_array('ROLE_ADMIN', $subject->getRoles(), true)) {
                return false;
            }
        }

        return true;
    }
}
