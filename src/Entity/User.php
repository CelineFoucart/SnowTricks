<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['username', 'email'], message: 'Un compte utilise déjà cette valeur')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /** @var int|null the user id */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** @var string|null the user name */
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Length(
        min: 1,
        max: 180,
        minMessage: 'Le pseudo doit faire au moins 1 caractères',
        maxMessage: 'Le pseudo doit faire moins de 180 caractères'
    )]
    #[Assert\NotBlank]
    private ?string $username = null;

    /** the user roles, for example ROLE_USER or ROLE_ADMIN */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string|null The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /** @var string|null the user email */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email(message: "Ce champ n'est pas un email valide")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: 'Ce champ doit faire au moins 5 caractères',
        maxMessage: 'Ce champ doit faire moins de 255 caractères'
    )]
    private ?string $email = null;

    /** @var DateTimeImmutable|null the user cration date */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /** @var string|null the user avatar file name */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    /** @var bool|null the user active status */
    #[ORM\Column]
    private ?bool $isActive = null;

    /** @var Collection the user reset passwords */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ResetPassword::class, orphanRemoval: true)]
    private Collection $resetPasswords;

    /** @var Collection the tricks the user has created */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Trick::class)]
    private Collection $tricks;

    /** @var Collection the user comments */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Comment::class)]
    private Collection $comments;

    public function __construct()
    {
        $this->resetPasswords = new ArrayCollection();
        $this->tricks = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Define the value of username.
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, ResetPassword>
     */
    public function getResetPasswords(): Collection
    {
        return $this->resetPasswords;
    }

    public function addResetPassword(ResetPassword $resetPassword): self
    {
        if (!$this->resetPasswords->contains($resetPassword)) {
            $this->resetPasswords->add($resetPassword);
            $resetPassword->setUser($this);
        }

        return $this;
    }

    public function removeResetPassword(ResetPassword $resetPassword): self
    {
        if ($this->resetPasswords->removeElement($resetPassword)) {
            // set the owning side to null (unless already changed)
            if ($resetPassword->getUser() === $this) {
                $resetPassword->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Trick>
     */
    public function getTricks(): Collection
    {
        return $this->tricks;
    }

    public function addTrick(Trick $trick): self
    {
        if (!$this->tricks->contains($trick)) {
            $this->tricks->add($trick);
            $trick->setAuthor($this);
        }

        return $this;
    }

    public function removeTrick(Trick $trick): self
    {
        if ($this->tricks->removeElement($trick)) {
            // set the owning side to null (unless already changed)
            if ($trick->getAuthor() === $this) {
                $trick->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }
}
