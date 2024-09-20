<?php

namespace App\Service\Upload;

use App\Exception\File\MalwareDetectedException;
use App\Exception\File\MaxUploadSizeExceededException;
use App\Security\FileScanner;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadHandlerService
{
    public const int|float MAX_FILESIZE = 10 * 1024 * 1024;
    public const UPLOAD_ACCEPTED_EXTENSIONS = ['jpg', 'jpeg', 'png'];
    public const UPLOAD_ACCEPTED_MIME_TYPES = ['image/jpeg', 'image/png'];

    private ?array $file;

    public function __construct(
        private readonly FilesystemOperator $fileStorage,
        private readonly SluggerInterface $slugger,
        private readonly LoggerInterface $logger,
        private readonly FileScanner $fileScanner,
    ) {
        $this->file = null;
    }

    /**
     * @throws MaxUploadSizeExceededException
     * @throws MalwareDetectedException
     */
    public function uploadFromFile(UploadedFile $file, $newFilename): void
    {
        if (!$this->fileScanner->isClean($file->getPathname())) {
            throw new MalwareDetectedException($file->getClientOriginalName());
        }

        if ($file->getSize() > self::MAX_FILESIZE) {
            throw new MaxUploadSizeExceededException(self::MAX_FILESIZE);
        }
        try {
            $fileResource = fopen($file->getPathname(), 'r');
            $this->fileStorage->writeStream($newFilename, $fileResource);
            fclose($fileResource);
        } catch (FilesystemException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @throws FilesystemException
     */
    public function createTmpFileFromBucket($from, $to): void
    {
        $resourceBucket = $this->fileStorage->read($from);
        $resourceFileSystem = fopen($to, 'w');
        fwrite($resourceFileSystem, $resourceBucket);
        fclose($resourceFileSystem);
    }

    public function setKey(string $key): ?array
    {
        $this->file['key'] = $key;

        return $this->file;
    }

    public function getFile(): ?array
    {
        return $this->file;
    }

    private function isAcceptedPhotoFormat(UploadedFile $file): bool
    {
        return \in_array($file->getMimeType(), self::UPLOAD_ACCEPTED_MIME_TYPES)
                && (
                    \in_array($file->getClientOriginalExtension(), self::UPLOAD_ACCEPTED_EXTENSIONS)
                    || \in_array($file->getExtension(), self::UPLOAD_ACCEPTED_EXTENSIONS)
                    || \in_array($file->guessExtension(), self::UPLOAD_ACCEPTED_EXTENSIONS)
                );
    }

    public function handleUploadFilesRequest(
        ?array $filesPosted,
    ): array {
        $filesToSave = [];
        if (isset($filesPosted)) {
            /** @var UploadedFile $file */
            foreach ($filesPosted as $file) {
                if ($file->getError()) {
                    return [];
                }

                if (!$this->isAcceptedPhotoFormat($file)) {
                    $this->logger->error('Bad format : '.$file->getClientOriginalName());
                    continue;
                }

                $originalFilename = pathinfo($file->getClientOriginalName(), \PATHINFO_FILENAME);
                $title = $originalFilename.'.'.$file->guessExtension();
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
                try {
                    $this->uploadFromFile($file, $newFilename);
                } catch (MaxUploadSizeExceededException|MalwareDetectedException $exception) {
                    $newFilename = '';
                    $this->logger->error($errorMessage = $exception->getMessage());
                }
                if (!empty($newFilename)) {
                    $filesToSave[] = [
                        'file' => $newFilename,
                        'title' => $title,
                        'date' => (new \DateTimeImmutable())->format('d.m.Y'),
                    ];
                }
            }
        }

        return $filesToSave;
    }
}
