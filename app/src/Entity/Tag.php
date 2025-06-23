<?php

/**
 * Tag entity.
 */

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Tag.
 */
#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'tags')]
#[ORM\UniqueConstraint(name: 'uq_tags_title', columns: ['title'])]
#[UniqueEntity(fields: ['title'])]
class Tag
{
    /**
     * Primary key.
     *
     * @var int|null unique identifier for each tag
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Title.
     *
     * @var string|null tag title, must be unique
     */
    #[ORM\Column(type: 'string', length: 64, unique: true)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 64)]
    private ?string $title = null;

    /**
     * Created at.
     *
     * @var \DateTimeImmutable|null date and time when the tag was created
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Updated at.
     *
     * @var \DateTimeImmutable|null date and time when the tag was last updated
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Related URLs.
     *
     * @var Collection<int, Url> collection of URLs related to this tag
     */
    #[ORM\ManyToMany(mappedBy: 'tags', targetEntity: Url::class)]
    private Collection $urls;

    /**
     * Constructor.
     *
     * Initializes the collection of related URLs.
     */
    public function __construct()
    {
        $this->urls = new ArrayCollection();
    }

    /**
     * Gets the ID.
     *
     * @return int|null tag identifier
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gets the title.
     *
     * @return string|null tag title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Sets the title.
     *
     * @param string $title tag title
     *
     * @return static returns the current instance for method chaining
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets the creation date.
     *
     * @return \DateTimeImmutable|null tag creation date
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Sets the creation date.
     *
     * @param \DateTimeImmutable $createdAt tag creation date
     *
     * @return static returns the current instance for method chaining
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Gets the update date.
     *
     * @return \DateTimeImmutable|null tag last update date
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Sets the update date.
     *
     * @param \DateTimeImmutable $updatedAt tag last update date
     *
     * @return static returns the current instance for method chaining
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Gets related URLs.
     *
     * @return Collection<int, Url> collection of URLs related to this tag
     */
    public function getUrls(): Collection
    {
        return $this->urls;
    }

    /**
     * Adds a URL to the tag.
     *
     * @param url $url URL to add
     *
     * @return static returns the current instance for method chaining
     */
    public function addUrl(Url $url): static
    {
        if (!$this->urls->contains($url)) {
            $this->urls->add($url);
        }

        return $this;
    }

    /**
     * Removes a URL from the tag.
     *
     * @param url $url URL to remove
     *
     * @return static returns the current instance for method chaining
     */
    public function removeUrl(Url $url): static
    {
        $this->urls->removeElement($url);

        return $this;
    }
}
