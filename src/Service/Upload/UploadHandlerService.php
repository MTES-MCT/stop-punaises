<?php

namespace App\Service\Upload;

use App\Exception\File\MaxUploadSizeExceededException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadHandlerService
{
    public const MAX_FILESIZE = 10 * 1024 * 1024;

    private $file;

    public function __construct(
        private FilesystemOperator $fileStorage,
        private ParameterBagInterface $parameterBag,
        private SluggerInterface $slugger,
        private LoggerInterface $logger,
    ) {
        $this->file = null;
    }

    public function toTempFolder(UploadedFile $file): self|array
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), \PATHINFO_FILENAME);
        $titre = $originalFilename.'.'.$file->guessExtension();
        // this is needed to safely include the file name as part of the URL
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        if ($file->getSize() > self::MAX_FILESIZE) {
            throw new MaxUploadSizeExceededException(self::MAX_FILESIZE);
        }
        try {
            $file->move(
                $this->parameterBag->get('uploads_tmp_dir'),
                $newFilename
            );
        } catch (FileException $e) {
            $this->logger->error($e->getMessage());

            return ['error' => 'Erreur lors du téléversement.', 'message' => $e->getMessage(), 'status' => 500];
        }
        $this->file = ['file' => $newFilename, 'titre' => $titre];

        return $this;
    }

    public function uploadFromFilename(string $filename): string
    {
        $tmpFilepath = $this->parameterBag->get('uploads_tmp_dir').$filename;

        try {
            $resourceFile = fopen($tmpFilepath, 'r');
            $this->fileStorage->writeStream($filename, $resourceFile);
            fclose($resourceFile);
        } catch (FilesystemException $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $filename;
    }

    public function uploadFromFile(UploadedFile $file, $newFilename): void
    {
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

    public function createTmpFileFromBucket($from, $to): void
    {
        $resourceBucket = $this->fileStorage->read($from);
        $resourceFileSytem = fopen($to, 'w');
        fwrite($resourceFileSytem, $resourceBucket);
        fclose($resourceFileSytem);
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

    public function handleUploadFilesRequest(
        ?array $filesPosted,
    ): array {
        $filesToSave = [];
        if (isset($filesPosted) && \is_array($filesPosted)) {
            /** @var UploadedFile $file */
            foreach ($filesPosted as $file) {
                if ($file->getError()) {
                    return [];
                }
                $originalFilename = pathinfo($file->getClientOriginalName(), \PATHINFO_FILENAME);
                $title = $originalFilename.'.'.$file->guessExtension();
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
                try {
                    $this->uploadFromFile($file, $newFilename);
                } catch (FilesystemException $exception) {
                    $newFilename = '';
                    $this->logger->error($exception->getMessage());
                } catch (MaxUploadSizeExceededException $exception) {
                    $newFilename = '';
                    $this->logger->error($exception->getMessage());
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
