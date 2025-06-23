<?php

/**
 * Url service.
 */

namespace App\Service;

use App\Repository\UrlRepository;
use App\Entity\Tag;
use App\Entity\Url;
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
     * @param int $page Page number
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
                'sortFieldAllowList' => ['url.id', 'url.createdAt', 'url.updatedAt', 'url.clickCount'],
                'defaultSortFieldName' => 'url.createdAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Get paginated list of unpublished URLs.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedListUnpublished(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->urlRepository->queryAllUnpublished(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['url.id', 'url.createdAt', 'url.updatedAt', 'url.clickCount'],
                'defaultSortFieldName' => 'url.createdAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Get latest N URLs.
     *
     * @param int $limit Number of URLs to return
     * @param int $page  Page number
     *
     * @return PaginationInterface Paginated list of URLs
     */
    public function getLatestUrls(int $limit, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->urlRepository->queryLatestUrls($limit),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Get N most popular URLs.
     *
     * @param int $limit Number of URLs to return
     * @param int $page  Page number
     *
     * @return PaginationInterface Paginated list of URLs
     */
    public function getMostPopularUrls(int $limit, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->urlRepository->queryMostPopularUrls($limit),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Get most popular URLs for chart.
     *
     * @param int $limit Number of URLs to return
     *
     * @return Url[] Array of most popular URLs
     */
    public function getMostPopularUrlsForChart(int $limit): array
    {
        return $this->urlRepository->queryMostPopularUrlsForChart($limit);
    }

    /**
     * Get paginated list of URLs by tag.
     *
     * @param Tag $tag  Tag entity
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list of URLs
     */
    public function getPaginatedListByTag(Tag $tag, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->urlRepository->queryByTag($tag),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['url.id', 'url.createdAt', 'url.updatedAt', 'url.clickCount'],
                'defaultSortFieldName' => 'url.createdAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Get paginated list of URLs by search query.
     *
     * @param string $query Search query
     * @param int    $page  Page number
     *
     * @return PaginationInterface Paginated list of URLs
     */
    public function getPaginatedListSearchByQuery(string $query, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->urlRepository->searchByQuery($query),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['url.id', 'url.createdAt', 'url.updatedAt', 'url.clickCount'],
                'defaultSortFieldName' => 'url.createdAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Save entity.
     *
     * @param Url $url Url entity
     */
    public function save(Url $url): void
    {
        if (in_array($url->getShortCode(), [null, '', '0'], true)) {
            do {
                $generated = bin2hex(random_bytes(4));
                $existing = $this->urlRepository->findOneBy(['shortCode' => $generated]);
            } while (null !== $existing);

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

    /**
     * Check if URL can be created for the given email.
     *
     * @param string $email Email address
     *
     * @return bool True if URL can be created, false otherwise
     */
    public function canCreateForEmail(string $email): bool
    {
        return $this->urlRepository->countByEmailToday($email) < 10;
    }

    /**
     * Block a URL.
     *
     * @param Url                     $url          The URL to block
     * @param bool                    $permanent    Whether the block is permanent
     * @param \DateTimeInterface|null $blockedUntil The date until which the URL is blocked, null for permanent
     */
    public function blockUrl(Url $url, bool $permanent, ?\DateTimeInterface $blockedUntil): void
    {
        if ($permanent) {
            $url->setBlockedUntil(new \DateTimeImmutable('9999-12-31 23:59:59'));
        } else {
            $url->setBlockedUntil($blockedUntil);
        }

        $url->setIsBlocked(true);
        $this->urlRepository->save($url);
    }

    /**
     * Unblock a URL.
     *
     * @param Url $url The URL to unblock
     */
    public function unblockUrl(Url $url): void
    {
        $url->setBlockedUntil(null);
        $url->setIsBlocked(false);
        $this->urlRepository->save($url);
    }

    /**
     * Check and update the block status of a URL.
     *
     * @param Url $url The URL to check and update
     */
    public function checkAndUpdateBlockStatus(Url $url): void
    {
        $now = new \DateTimeImmutable();

        if ($url->getBlockedUntil() instanceof \DateTimeImmutable && $url->getBlockedUntil() <= $now) {
            $url->setIsBlocked(false);
            $url->setBlockedUntil(null);
            $this->urlRepository->save($url);
        }
    }
}
