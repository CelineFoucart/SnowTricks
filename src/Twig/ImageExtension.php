<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageExtension extends AbstractExtension
{
    public function __construct(
        private string $imageDirectory, private string $avatarDirectory
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('trick_image', [$this, 'getImageTrickPath']),
            new TwigFunction('avatar_image', [$this, 'getImageAvatarPath']),
        ];
    }

    /**
     * Get the path of a trick image.
     *
     * @return string the path generated
     */
    public function getImageTrickPath(?string $imageName): string
    {
        if (null === $imageName) {
            return '';
        }

        return '/'.$this->imageDirectory.'/'.$imageName;
    }

    /**
     * Get the path of a trick image.
     *
     * @return string the path generated
     */
    public function getImageAvatarPath(string $imageName): string
    {
        return '/'.$this->avatarDirectory.'/'.$imageName;
    }
}
