<?php

/**
 * Url service.
 */

namespace App\Service;

use App\Repository\UrlRepository;
use App\Entity\Tag;
use App\Entity\Url;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class UrlService.
 */
class UrlService implements UrlServiceInterface
{
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
     * Constructor.
     *
     * @param UrlRepository      $urlRepository Url repository
     * @param PaginatorInterface $paginator     Paginator
     */
    public function __construct(private readonly UrlRepository $urlRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int  $page   Page number
     * @param User $author Author
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->urlRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['url.id', 'url.createdAt', 'url.updatedAt'],
                'defaultSortFieldName' => 'url.updatedAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    public function getPaginatedListByTag(Tag $tag, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->urlRepository->queryByTag($tag),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Url $url Url entity
     */
    public function save(Url $url): void
    {
        if (empty($url->getShortCode())) {
            do {
                $generated = bin2hex(random_bytes(4));
                $existing = $this->urlRepository->findOneBy(['shortCode' => $generated]);
            } while ($existing !== null);

            $url->setShortCode($generated);
        }

        $this->urlRepository->save($url);
    }

    /**
     * Delete entity.
     *
     * @param Url $url Url entity
     */
    public function delete(Url $url): void
    {
        $this->urlRepository->delete($url);
    }
}
