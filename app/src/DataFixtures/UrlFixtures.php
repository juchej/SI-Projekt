<?php

/**
 * Url fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\Url;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

/**
 * Class UrlFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class UrlFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: TagFixtures::class}
     */
    public function getDependencies(): array
    {
        return [TagFixtures::class, UserFixtures::class];
    }
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }

        $this->createMany(100, 'url', function (int $i) {
            $url = new Url();
            $url->setOriginalUrl($this->faker->url); // zamiana sentence -> url
            $url->setShortCode(substr($this->faker->unique()->lexify('??????'), 0, 6));
            $url->setClickCount($this->faker->numberBetween(0, 100));
            $url->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $url->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $url->setIsPublished(true);
            $url->setIsBlocked(false);

            // przypisz losowe tagi (1–3)
            $tagCount = $this->faker->numberBetween(1, 3);
            for ($j = 0; $j < $tagCount; $j++) {
                /** @var Tag $tag */
                $tag = $this->getRandomReference('tag', Tag::class);
                $url->addTag($tag);
            }

            /** @var User $author */
            $author = $this->getRandomReference('user', User::class);
            $url->setAuthor($author);

            return $url;
        });
    }
}
