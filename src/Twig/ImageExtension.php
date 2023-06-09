<?php

namespace App\Twig;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class ImageExtension extends AbstractExtension
{
    public function __construct(
        private string $imageDirectory, private string $avatarDirectory
    ) {
    }
    
    public function getFunctions()
    {
        return [
            new TwigFunction('trick_image', [$this, 'getImageTrickPath']),
        ];
    }

    /**
     * Get the path of a trick image.
     *
     * @return string the path generated
     */
    public function getImageTrickPath(string $imageName): string
    {
        return '/' . $this->imageDirectory . '/' . $imageName;
    }
}