<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ImageUploader
{
    private ?string $error = null;

    public function __construct(
        private SluggerInterface $slugger,
        private string $imageDirectory,
        private string $avatarDirectory
    ) {
    }

    /**
     * Move an uploaded file to a directory in the server.
     *
     * @param  UploadedFile   $file   the file to move
     * @param  string         $type   the file type, an image or an avatar
     * @return string|null    null in case of failure or the new file name in case of success
     */
    public function move(UploadedFile $file, string $type = 'image'): ?string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $path = ($type === 'avatar') ? $this->avatarDirectory : $this->imageDirectory;
            $file->move($path, $newFilename);
            $this->error = null;

            return $newFilename;
        } catch (FileException $th) {
            $this->error = $th->getMessage();

            return null;
        }
    }

    /**
     * Get the value of error
     *
     * @return ?string
     */
    public function getError(): ?string
    {
        return $this->error;
    }
}
