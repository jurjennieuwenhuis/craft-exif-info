<?php

declare(strict_types=1);

namespace juni\exifinfo\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\elements\Asset;
use craft\helpers\Html;
use juni\exifinfo\assets\field\FieldAsset;
use juni\exifinfo\ExifInfo;

final class ExifInfoPanelField extends Field
{
    // Properties
    // =========================================================================


    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('juni-exif-info', 'Exif Info Panel');
    }

    public static function phpType(): string
    {
        return 'string|null';
    }

    // Public Methods
    // =========================================================================

    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        // Get the exif data from the corresponding asset.

        if (!$element instanceof Asset) {
            return '';
        }

        if (null === $filePath = ExifInfo::$plugin->getAssetHelper()->getFilePath($element)) {
            return '';
        }

        if (!ExifInfo::$plugin->getAssetHelper()->supportsExif($element)) {
            return '';
        }

        $exif = ExifInfo::$plugin->getExifReader()->read($filePath);


        $view = Craft::$app->getView();
        $view->registerAssetBundle(FieldAsset::class);

        // Get our id and namespace
        $id = Html::id($this->handle);
        $nsId = $view->namespaceInputId($id);

        // Render the input template
        return $view->renderTemplate(
            'juni-exif-info/_components/fields/_input',
            [
                'name' => $this->handle,
                'data' => $exif->getFormattedData(),
                'rawData' => $exif->getFormattedRawData(),
                'id' => $id,
                'namespaceId' => $nsId,
            ]
        );
    }

    // Private Methods
    // =========================================================================

}
