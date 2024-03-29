<?php
declare(strict_types=1);

namespace juni\exifinfo\services;

use craft\base\LocalFsInterface;
use craft\elements\Asset;
use craft\helpers\FileHelper;

final class AssetHelper
{
    public function getFilePath(Asset|null $asset): string|null
    {
        $filePath = null;

        if (null === $asset) {
            return $filePath;
        }

        $volume = $asset->getVolume();
        $fs = $volume->getFs();

        if ($fs instanceof LocalFsInterface) {
            $filePath = FileHelper::normalizePath(
                $fs->getRootPath() . DIRECTORY_SEPARATOR .
                $volume->getSubpath() . $asset->getPath());
        }

        return $filePath;
    }

    public function isJpg(Asset $asset): bool
    {
        return $asset->getMimeType() === 'image/jpeg';
    }

    public function isMp4(Asset $asset): bool
    {
        return $asset->getMimeType() === 'video/mp4';
    }

    public function supportsExif(Asset $asset): bool
    {
        if (
            $this->isJpg($asset) ||
            $this->isMp4($asset)
        ) {
            return true;
        }

        return false;
    }
}
