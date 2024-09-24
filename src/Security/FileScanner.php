<?php

namespace App\Security;

use Sineflow\ClamAV\Exception\FileScanException;
use Sineflow\ClamAV\Exception\SocketException;
use Sineflow\ClamAV\Scanner;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Uid\Uuid;

readonly class FileScanner
{
    public function __construct(
        private Scanner $scanner,
        private ParameterBagInterface $parameterBag,
        #[Autowire(env: 'CLAMAV_SCAN_ENABLE')]
        private bool $clamavScanEnable,
    ) {
    }

    /**
     * @throws FileScanException
     * @throws SocketException
     */
    public function isClean(string $filePath, ?bool $copy = true): bool
    {
        if (!$this->clamavScanEnable) {
            return true;
        }
        if ($copy) {
            $copiedFilepath = $this->parameterBag->get('uploads_tmp_dir').'clamav_'.Uuid::v4();
            file_put_contents($copiedFilepath, file_get_contents($filePath));
        } else {
            $copiedFilepath = $filePath;
        }

        $scannedFile = $this->scanner->scan($copiedFilepath);

        return $scannedFile->isClean();
    }
}
