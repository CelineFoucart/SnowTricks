<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    /** @var integer|null the image id */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** @var string|null the image file name */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Ce champ ne peut être vide')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Ce champ doit faire au moins 2 caractères',
        maxMessage: 'Ce champ doit faire moins de 255 caractères'
    )]
    private ?string $filename = null;

    /** @var string|null the legend of the image */
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Ce champ doit faire au moins 2 caractères',
        maxMessage: 'Ce champ doit faire moins de 255 caractères'
    )]
    private ?string $legend = null;

    /** @var DateTimeImmutable|null the upload date */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /** @var Trick|null the trick image */
    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trick $trick = null;

    /** @var UploadedFile|null the the uploaded file to move when the entity is created */
    private ?UploadedFile $uploadedFile = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getLegend(): ?string
    {
        return $this->legend;
    }

    public function setLegend(string $legend): self
    {
        $this->legend = $legend;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }

    /**
     * Get the value of uploadedFile.
     */
    public function getUploadedFile(): ?File
    {
        return $this->uploadedFile;
    }

    /**
     * Set the value of uploadedFile.
     *
     * @param ?File $uploadedFile
     */
    public function setUploadedFile(?UploadedFile $uploadedFile): self
    {
        $this->uploadedFile = $uploadedFile;

        return $this;
    }
}
