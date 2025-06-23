<?php

/**
 * Tag voter.
 */

namespace App\Security\Voter;

use App\Entity\Tag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class TagVoter.
 */
final class TagVoter extends Voter
{
    /**
     * Delete permission.
     *
     * @const string
     */
    public const DELETE = 'TAG_DELETE';

    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'TAG_EDIT';

    /**
     * View permission.
     *
     * @const string
     */
    public const VIEW = 'TAG_VIEW';

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
        return in_array($attribute, [self::DELETE, self::EDIT, self::VIEW])
            && $subject instanceof Tag;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }
        if (!$subject instanceof Tag) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::VIEW => $this->canView($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if user can delete tag.
     *
     * @param Tag           $tag  Tag entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canDelete(Tag $tag, UserInterface $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    /**
     * Checks if user can edit tag.
     *
     * @param Tag           $tag  Tag entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canEdit(Tag $tag, UserInterface $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    /**
     * Checks if user can view tag.
     *
     * @param Tag           $tag  Tag entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canView(Tag $tag, UserInterface $user): bool
    {
        return true;
    }
}
