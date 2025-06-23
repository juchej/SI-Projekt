<?php

/**
 * Url entity.
 */

namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Url.
 */
#[ORM\Entity(repositoryClass: UrlRepository::class)]
#[ORM\Table(name: 'urls')]
#[UniqueEntity('shortCode')]
class Url implements \Stringable
{
    /**
     * Primary key.
     *
     * @var int|null unique identifier for the URL entity
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Original URL.
     *
     * @var string|null the original URL to be shortened
     */
    #[ORM\Column(length: 2048)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private ?string $originalUrl = null;

    /**
     * Short code.
     *
     * @var string|null a unique short code representing the URL
     */
    #[ORM\Column(length: 32, unique: true)]
    #[Assert\Length(max: 32)]
    private ?string $shortCode = null;

    /**
     * Created at.
     *
     * @var \DateTimeImmutable|null the date and time when the URL was created
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Updated at.
     *
     * @var \DateTimeImmutable|null the date and time when the URL was last updated
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Click count.
     *
     * @var int|null the number of times the URL has been clicked
     */
    #[ORM\Column]
    private ?int $clickCount = 0;

    /**
     * Is published.
     *
     * @var bool|null indicates whether the URL is published
     */
    #[ORM\Column]
    private ?bool $isPublished = true;

    /**
     * Is blocked.
     *
     * @var bool|null indicates whether the URL is blocked
     */
    #[ORM\Column]
    private ?bool $isBlocked = false;

    /**
     * Tags.
     *
     * @var Collection<int, Tag> a collection of tags associated with the URL
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'urls', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(name: 'url_tag')]
    private Collection $tags;

    /**
     * Author.
     *
     * @var User|null the user who created the URL
     */
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\Type(User::class)]
    private ?User $author = null;

    /**
     * Author email.
     *
     * @var string|null the email address of the user who created the URL
     */
    #[ORM\Column(type: 'string', length: 180, nullable: true)]
    #[Assert\Email]
    private ?string $authorEmail = null;

    /**
     * Blocked until.
     *
     * @var \DateTimeImmutable|null the date and time until which the URL is blocked
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $blockedUntil = null;

    /**
     * Constructor.
     *
     * Initializes the collection of tags.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * Gets the ID.
     *
     * @return int|null the unique identifier of the URL
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gets the original URL.
     *
     * @return string|null the original URL
     */
    public function getOriginalUrl(): ?string
    {
        return $this->originalUrl;
    }

    /**
     * Sets the original URL.
     *
     * @param string $originalUrl the original URL to set
     *
     * @return static returns the current instance for method chaining
     */
    public function setOriginalUrl(string $originalUrl): static
    {
        $this->originalUrl = $originalUrl;

        return $this;
    }

    /**
     * Gets the short code.
     *
     * @return string|null the short code of the URL
     */
    public function getShortCode(): ?string
    {
        return $this->shortCode;
    }

    /**
     * Sets the short code.
     *
     * @param string $shortCode the short code to set
     *
     * @return static returns the current instance for method chaining
     */
    public function setShortCode(string $shortCode): static
    {
        $this->shortCode = $shortCode;

        return $this;
    }

    /**
     * Gets the creation date.
     *
     * @return \DateTimeImmutable|null the creation date of the URL
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Sets the creation date.
     *
     * @param \DateTimeImmutable $createdAt the creation date to set
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
     * @return \DateTimeImmutable|null the last update date of the URL
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Sets the update date.
     *
     * @param \DateTimeImmutable $updatedAt the update date to set
     *
     * @return static returns the current instance for method chaining
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Gets the click count.
     *
     * @return int|null the number of clicks on the URL
     */
    public function getClickCount(): ?int
    {
        return $this->clickCount;
    }

    /**
     * Sets the click count.
     *
     * @param int $clickCount the click count to set
     *
     * @return static returns the current instance for method chaining
     */
    public function setClickCount(int $clickCount): static
    {
        $this->clickCount = $clickCount;

        return $this;
    }

    /**
     * Checks if the URL is published.
     *
     * @return bool|null true if the URL is published, false otherwise
     */
    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    /**
     * Sets the published status.
     *
     * @param bool $isPublished the published status to set
     *
     * @return static returns the current instance for method chaining
     */
    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * Checks if the URL is blocked.
     *
     * @return bool|null true if the URL is blocked, false otherwise
     */
    public function isBlocked(): ?bool
    {
        if ($this->blockedUntil instanceof \DateTimeImmutable) {
            return $this->blockedUntil > new \DateTimeImmutable();
        }

        return true === $this->isBlocked;
    }

    /**
     * Sets the blocked status.
     *
     * @param bool $isBlocked the blocked status to set
     *
     * @return static returns the current instance for method chaining
     */
    public function setIsBlocked(bool $isBlocked): static
    {
        $this->isBlocked = $isBlocked;

        return $this;
    }

    /**
     * Gets the tags.
     *
     * @return Collection<int, Tag> the collection of tags associated with the URL
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Adds a tag to the URL.
     *
     * @param Tag $tag the tag to add
     *
     * @return static returns the current instance for method chaining
     */
    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addUrl($this);
        }

        return $this;
    }

    /**
     * Removes a tag from the URL.
     *
     * @param Tag $tag the tag to remove
     *
     * @return static returns the current instance for method chaining
     */
    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeUrl($this);
        }

        return $this;
    }

    /**
     * Converts the URL entity to a string.
     *
     * @return string the string representation of the URL
     */
    public function __toString(): string
    {
        return $this->getShortCode() ?? $this->getOriginalUrl() ?? '[url]';
    }

    /**
     * Gets the author.
     *
     * @return User|null the user who created the URL
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Sets the author.
     *
     * @param User|null $author the user to set as the author
     *
     * @return static returns the current instance for method chaining
     */
    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Gets the author's email.
     *
     * @return string|null the email address of the author
     */
    public function getAuthorEmail(): ?string
    {
        return $this->authorEmail;
    }

    /**
     * Sets the author's email.
     *
     * @param string|null $authorEmail the email address to set
     *
     * @return static returns the current instance for method chaining
     */
    public function setAuthorEmail(?string $authorEmail): static
    {
        $this->authorEmail = $authorEmail;

        return $this;
    }

    /**
     * Gets the date until which the URL is blocked.
     *
     * @return \DateTimeImmutable|null the date until which the URL is blocked
     */
    public function getBlockedUntil(): ?\DateTimeImmutable
    {
        return $this->blockedUntil;
    }

    /**
     * Sets the date until which the URL is blocked.
     *
     * @param \DateTimeImmutable|null $blockedUntil the date to set
     *
     * @return static returns the current instance for method chaining
     */
    public function setBlockedUntil(?\DateTimeImmutable $blockedUntil): static
    {
        $this->blockedUntil = $blockedUntil;

        return $this;
    }
}
