<?php

/**
 * Url repository.
 */

namespace App\Repository;

use App\Entity\Url;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class UrlRepository.
 *
 * @extends ServiceEntityRepository<Url>
 */
class UrlRepository extends ServiceEntityRepository
{
    /**
     * Items per page.
     *
     * Constant defining the number of items displayed per page in paginated results.
     *
     * @constant int
     */
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Url::class);
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->createQueryBuilder('url')
            ->select(
                'partial url.{id, originalUrl, shortCode, createdAt, updatedAt, clickCount, isPublished, blockedUntil}',
                'partial tag.{id, title}'
            )
            ->leftJoin('url.tags', 'tag')
            ->where('url.isPublished = true');
    }

    /**
     * Query by tag.
     *
     * @param Tag $tag Tag entity
     *
     * @return QueryBuilder Query builder
     */
    public function queryByTag(Tag $tag): QueryBuilder
    {
        return $this->createQueryBuilder('url')
            ->leftJoin('url.tags', 'filter_tag')
            ->leftJoin('url.tags', 'all_tags')
            ->addSelect('partial all_tags.{id, title}')
            ->where('filter_tag = :tag')
            ->andWhere('url.isPublished = true')
            ->setParameter('tag', $tag);
    }

    /**
     * Count URLs created by a specific email today.
     *
     * @param string $email Author's email
     *
     * @return int Count of Url records created today by the specified email
     */
    public function countByEmailToday(string $email): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.authorEmail = :email')
            ->andWhere('u.createdAt >= :today')
            ->setParameter('email', $email)
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Save entity.
     *
     * @param Url $url Url entity
     */
    public function save(Url $url): void
    {
        $this->getEntityManager()->persist($url);
        $this->getEntityManager()->flush();
    }

    /**
     * Delete entity.
     *
     * @param Url $url Url entity
     */
    public function delete(Url $url): void
    {
        $this->getEntityManager()->remove($url);
        $this->getEntityManager()->flush();
    }

    /**
     * Find URLs that are temporarily blocked.
     *
     * @return Url[] Array of Url entities that are temporarily blocked
     */
    public function findTemporarilyBlocked(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.blockedUntil IS NOT NULL')
            ->andWhere('u.blockedUntil < :maxDate')
            ->setParameter('maxDate', new \DateTimeImmutable('9999-12-31 23:59:59'))
            ->getQuery()
            ->getResult();
    }

    /**
     * Query all unpublished URLs.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAllUnpublished(): QueryBuilder
    {
        return $this->createQueryBuilder('url')
            ->select(
                'partial url.{id, originalUrl, shortCode, createdAt, updatedAt, clickCount, isPublished, blockedUntil}',
                'partial tag.{id, title}'
            )
            ->leftJoin('url.tags', 'tag')
            ->where('url.isPublished = false');
    }

    /**
     * Query latest URLs.
     *
     * @param int $limit Number of URLs to return
     *
     * @return QueryBuilder Query builder
     */
    public function queryLatestUrls(int $limit = 10): QueryBuilder
    {
        return $this->createQueryBuilder('url')
            ->select(
                'partial url.{id, originalUrl, shortCode, createdAt, updatedAt, clickCount, isPublished, blockedUntil}',
                'partial tag.{id, title}'
            )
            ->leftJoin('url.tags', 'tag')
            ->orderBy('url.createdAt', 'DESC')
            ->where('url.isPublished = true')
            ->setMaxResults($limit);
    }

    /**
     * Query most popular URLs.
     *
     * @param int $limit Number of URLs to return
     *
     * @return QueryBuilder Query builder
     */
    public function queryMostPopularUrls(int $limit = 10): QueryBuilder
    {
        return $this->createQueryBuilder('url')
            ->select(
                'partial url.{id, originalUrl, shortCode, createdAt, updatedAt, clickCount, isPublished, blockedUntil}',
                'partial tag.{id, title}'
            )
            ->leftJoin('url.tags', 'tag')
            ->orderBy('url.clickCount', 'DESC')
            ->where('url.isPublished = true')
            ->setMaxResults($limit);
    }

    /**
     * Query most popular URLs for chart.
     *
     * @param int $limit Number of URLs to return
     *
     * @return Url[] Array of most popular URLs
     */
    public function queryMostPopularUrlsForChart(int $limit = 10): array
    {
        return $this->createQueryBuilder('url')
            ->select('partial url.{id, shortCode, clickCount}')
            ->where('url.isPublished = true')
            ->orderBy('url.clickCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Search URLs by query.
     *
     * @param string $query Search query
     *
     * @return QueryBuilder Query builder
     */
    public function searchByQuery(string $query): QueryBuilder
    {
        return $this->createQueryBuilder('url')
            ->select(
                'partial url.{id, originalUrl, shortCode, createdAt, updatedAt, clickCount, isPublished, blockedUntil}',
                'partial tag.{id, title}'
            )
            ->leftJoin('url.tags', 'tag')
            ->where('url.originalUrl LIKE :query')
            ->orWhere('tag.title LIKE :query')
            ->orWhere('url.shortCode LIKE :query')
            ->setParameter('query', '%'.$query.'%');
    }
}
