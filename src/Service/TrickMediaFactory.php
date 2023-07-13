<?php

namespace App\Service;

use App\Entity\Trick;
use Symfony\Component\Form\FormInterface;

final class TrickMediaFactory
{
    private Trick $trick;

    /**
     * @param ImageUploader $imageUploader
     */
    public function __construct(private ImageUploader $imageUploader)
    {
        
    }

    /**
     * Defines the featured image if the user doesn't ask for its removing.
     * 
     * @param FormInterface $form
     * 
     * @return self
     */
    public function setFeaturedImageFile(FormInterface $form): self
    {
        $featuredImageFile = $form->get('featuredImageFile')->getData();
        $deleteFeaturedImage = (bool) $form->get('deleteFeaturedImage')->getData();

        if ($featuredImageFile && !$deleteFeaturedImage) {
            $newFileName = $this->imageUploader->move($featuredImageFile);

            if (null !== $this->trick->getFeaturedImage()) {
                $this->imageUploader->remove($this->trick->getFeaturedImage());
            }

            $this->trick->setFeaturedImage($newFileName);
        }

        if ($deleteFeaturedImage) {
            $status = $this->imageUploader->remove($this->trick->getFeaturedImage());

            if ($status) {
                $this->trick->setFeaturedImage(null);
            }
        }

        return $this;
    }

    /**
     * Defines the image gallery of a trick.
     * 
     * @param FormInterface $form
     * 
     * @return self
     */
    public function setGallery(FormInterface $form): self
    {
        $images = $form->get('images')->getData();
        foreach ($images as $image) {
            if (null === $image->getId()) {
                $filename = $this->imageUploader->move($image->getUploadedFile());
                $image->setFilename($filename)->setCreatedAt(new \DateTimeImmutable())->setTrick($this->trick);
            } elseif (null !== $image->getUploadedFile()) {
                $this->imageUploader->remove($image->getFilename());
                $filename = $this->imageUploader->move($image->getUploadedFile());
                $image->setFilename($filename);
            }
        }

        return $this;
    }

    /**
     * Defines the video gallery of a trick.
     * 
     * @param FormInterface $form
     * 
     * @return self
     */
    public function setVideoGallery(FormInterface $form): self
    {
        $videos = $form->get('videos')->getData();
        foreach ($videos as $video) {
            $video->setTrick($this->trick);
        }

        return $this;
    }

    /**
     * Get the value of trick
     */
    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    /**
     * Set the value of trick
     *
     * @param Trick $trick
     *
     * @return self
     */
    public function setTrick(Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }
}
