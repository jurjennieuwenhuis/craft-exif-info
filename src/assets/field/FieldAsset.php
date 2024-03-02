<?php
declare(strict_types=1);

namespace juni\exifinfo\assets\field;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

final class FieldAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/dist';

    public $depends = [
        CpAsset::class,
    ];

    public $css = [
        'exif-info-panel.css',
    ];

    public $js = [];
}
