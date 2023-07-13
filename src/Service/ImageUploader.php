<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageUploader
{
    private ?string $error = null;

    /**
     * @param SluggerInterface $slugger
     * @param string $imageDirectory
     * @param string $avatarDirectory
     */
    public function __construct(
        private SluggerInterface $slugger,
        private string $imageDirectory,
        private string $avatarDirectory
    ) {
    }

    /**
     * Move an uploaded file to a directory in the server.
     *
     * @param UploadedFile $file the file to move
     * @param string       $type the file type, an image or an avatar
     *
     * @return string|null null in case of failure or the new file name in case of success
     */
    public function move(UploadedFile $file, string $type = 'image'): ?string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $path = ('avatar' === $type) ? $this->avatarDirectory : $this->imageDirectory;
            $file->move($path, $newFilename);
            $this->error = null;

            return $newFilename;
        } catch (FileException $th) {
            $this->error = $th->getMessage();

            return null;
        }
    }

    public function remove(string $filename, string $type = 'image'): bool
    {
        $path = ('avatar' === $type) ? $this->avatarDirectory : $this->imageDirectory;

        $fileSystem = new Filesystem();
        $pathFile = $path.DIRECTORY_SEPARATOR.$filename;

        try {
            if ($fileSystem->exists($pathFile)) {
                $fileSystem->remove($path.DIRECTORY_SEPARATOR.$filename);

                return true;
            } else {
                return false;
            }
        } catch (\Exception $th) {
            $this->error = $th->getMessage();

            return false;
        }
    }

    /**
     * Get the value of error.
     */
    public function getError(): ?string
    {
        return $this->error;
    }
}
