<?php

namespace App\EventSubscriber;

use App\Entity\Image;
use App\Entity\Trick;
use App\Service\ImageUploader;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Events;

class ImageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ImageUploader $imageUploader
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postRemove,
        ];
    }

    /**
     * Deletes a image file of a removed entity Image.
     * 
     * @param PostRemoveEventArgs $args
     */
    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Image) {
            $this->imageUploader->remove($entity->getFilename());
        } elseif ($entity instanceof Trick) {
            foreach ($entity->getImages() as $image) {
                $this->imageUploader->remove($image->getFilename());
            }
        }
    }
}
