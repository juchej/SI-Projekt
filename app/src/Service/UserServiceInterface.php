<?php

/**
 * User service interface.
 */

namespace App\Service;

use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UserServiceInterface.
 */
interface UserServiceInterface
{
    /**
     * Save a user.
     *
     * @param User $user User to save
     */
    public function save(User $user): void;

    /**
     * Delete a user.
     *
     * @param User $user User to delete
     */
    public function delete(User $user): void;

    /**
     * Get all users.
     */
    public function getAll(): array;

    /**
     * Block a user.
     *
     * @param User $user User to block
     */
    public function blockUser(User $user): void;

    /**
     * Unblock a user.
     *
     * @param User $user User to unblock
     */
    public function unblockUser(User $user): void;

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedUsers(int $page = 1): PaginationInterface;

    /**
     * Change user password by admin.
     *
     * @param User   $user          User whose password is being changed
     * @param string $plainPassword Plain text password to set
     */
    public function changePassword(User $user, string $plainPassword): void;
}
