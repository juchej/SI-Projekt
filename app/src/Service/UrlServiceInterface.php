<?php

/**
 * Url service interface.
 */

namespace App\Service;

use App\Entity\Url;
use App\Entity\Tag;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UrlServiceInterface.
 */
interface UrlServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedListUnpublished(int $page): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Url $url Url entity
     */
    public function save(Url $url): void;

    /**
     * Returns top N most clicked URLs.
     *
     * @param int $limit Number of URLs to return
     * @param int $page  Page number
     */
    public function getMostPopularUrls(int $limit, int $page): PaginationInterface;

    /**
     * Returns top N most clicked URLs for chart.
     *
     * @param int $limit Number of URLs to return
     *
     * @return Url[] Array of most popular URLs
     */
    public function getMostPopularUrlsForChart(int $limit): array;

    /**
     * Returns latest N URLs.
     *
     * @param int $limit Number of URLs to return
     * @param int $page  Page number
     */
    public function getLatestUrls(int $limit, int $page): PaginationInterface;

    /**
     * Delete entity.
     *
     * @param Url $url Url entity
     */
    public function delete(Url $url): void;

    /**
     * Check if URL can be created for the given email.
     *
     * @param string $email Email address
     *
     * @return bool True if URL can be created, false otherwise
     */
    public function canCreateForEmail(string $email): bool;

    /**
     * Get paginated list of URLs by tag.
     *
     * @param Tag $tag  Tag entity
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list of URLs
     */
    public function getPaginatedListByTag(Tag $tag, int $page): PaginationInterface;

    /**
     * Get paginated list of URLs by search query.
     *
     * @param string $query Search query
     * @param int    $page  Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list of URLs matching the search query
     */
    public function getPaginatedListSearchByQuery(string $query, int $page): PaginationInterface;

    /**
     * Block a URL.
     *
     * @param Url                     $url          Url to block
     * @param bool                    $permanent    Whether the block is permanent
     * @param \DateTimeInterface|null $blockedUntil Optional date until which the URL is blocked
     */
    public function blockUrl(Url $url, bool $permanent, ?\DateTimeInterface $blockedUntil): void;

    /**
     * Unblock a URL.
     *
     * @param Url $url Url to unblock
     */
    public function unblockUrl(Url $url): void;

    /**
     * Check and update the block status of a URL.
     *
     * @param Url $url The URL to check and update
     */
    public function checkAndUpdateBlockStatus(Url $url): void;
}
