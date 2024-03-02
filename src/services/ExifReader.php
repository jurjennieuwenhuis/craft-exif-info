<?php
declare(strict_types=1);

namespace juni\exifinfo\services;

use yii\base\Component;
use juni\exifinfo\models\Exif;
use PHPExif\Adapter\Exiftool;
use PHPExif\Reader\Reader;

final class ExifReader extends Component
{
    public string $exifToolPath = '';

    public function read(string $imagePath): Exif
    {
        $adapter = new Exiftool([
            'toolPath'  => $this->exifToolPath,
        ]);

        $exif = (new Reader($adapter))->read($imagePath);

        return Exif::wrap($exif);
    }
}
