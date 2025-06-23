<?php

/**
 * User service.
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserService.
 */
class UserService implements UserServiceInterface
{
    /**
     * Constructor.
     *
     * @param UserRepository              $userRepository User repository
     * @param EntityManagerInterface      $entityManager  Entity manager
     * @param PaginatorInterface          $paginator      Paginator
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     */
    public function __construct(private readonly UserRepository $userRepository, private readonly EntityManagerInterface $entityManager, private readonly PaginatorInterface $paginator, private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    /**
     * Save a user.
     *
     * @param User $user User to save
     */
    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Delete a user.
     *
     * @param User $user User to delete
     */
    public function delete(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    /**
     * Get all users.
     *
     * @return User[] Array of User entities
     */
    public function getAll(): array
    {
        return $this->userRepository->findAll();
    }

    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedUsers(int $page = 1): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->userRepository->findAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['user.id'],
                'defaultSortFieldName' => 'user.id',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Block a user.
     *
     * @param User $user User to block
     */
    public function blockUser(User $user): void
    {
        $user->setIsBlocked(true);
        $this->save($user);
    }

    /**
     * Unblock a user.
     *
     * @param User $user User to unblock
     */
    public function unblockUser(User $user): void
    {
        $user->setIsBlocked(false);
        $this->save($user);
    }

    /**
     * Change user password by admin.
     *
     * @param User   $user          User to change password for
     * @param string $plainPassword New plain password
     */
    public function changePassword(User $user, string $plainPassword): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $this->entityManager->flush();
    }
}
