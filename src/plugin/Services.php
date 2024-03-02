<?php

namespace juni\exifinfo\plugin;

use juni\exifinfo\services\AssetHelper;
use juni\exifinfo\services\ExifReader;

/**
 * Trait Services
 *
 * @property ExifReader $exifReader the exif reader service
 * @property AssetHelper $assetHelper The asset helper service
 */
trait Services
{
    public function getExifReader(): ExifReader
    {
        return $this->get('exif.reader');
    }

    public function getAssetHelper(): AssetHelper
    {
        return $this->get('asset.helper');
    }

    private function setPluginComponents(): void
    {
        $this->setComponents([
            'exif.reader' => [
                'class' => ExifReader::class,
                'exifToolPath' => dirname(__DIR__, 2) . '/bin/exiftool/exiftool',
            ],
            'asset.helper' => [
                'class' => AssetHelper::class,
            ]
        ]);
    }
}
