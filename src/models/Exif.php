<?php
declare(strict_types=1);

namespace juni\exifinfo\models;

use Craft;
use PHPExif\Exif as BaseExif;

final class Exif extends BaseExif
{
    public const GOOGLE_MAPS_URL = 'https://www.google.com/maps/place/%s';

    public const PERSON_IN_PICTURE = 'XMP-iptcExt:PersonInImage';

    public static function wrap(BaseExif $exif): self
    {
        return new self($exif);
    }

    public function __construct(BaseExif $exif)
    {
        parent::__construct($exif->getData());
        $this->setRawData($exif->getRawData());
    }

    public function getGPS(): string
    {
        $gps = parent::getGPS();

        if (is_array($gps)) {
            return implode(',', $gps);
        }

        return (string) $gps;
    }

    public function getGoogleMapsLink(): string
    {
        $coords = $this->escape($this->getGPS());

        $url = sprintf(self::GOOGLE_MAPS_URL, urlencode($coords));
        $linkText = Craft::t('juni-exif-info', 'Show in Google Maps');
        return sprintf('<a href="%s" target="_blank">%s</a>', $url, $linkText);
    }

    public function getPersonInImage(): string|null
    {
        if ($personInPicture = $this->rawData[self::PERSON_IN_PICTURE] ?? null) {
            return $this->escape($personInPicture);
        }

        return null;
    }

    public function getFormattedData(): array
    {
        return $this->formatData($this->data);
    }

    public function getFormattedRawData(): array
    {
        return $this->formatData($this->rawData);
    }

    public function formatData(array $data): array
    {
        $formatted = [];

        foreach ($data as $name => $value) {

            $name = $this->escape(ucfirst($name));

            if (is_array($value)) {
                $formatted[$name] = $this->escape(implode(', ', $value));
            } else {

                if ($value instanceof \DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }

                $formatted[$name] = $this->escape($value);
            }
        }

        return $formatted;
    }

    private function escape(mixed $value): array|string
    {
        if (is_array($value)) {
            return array_map('htmlentities', $value);
        }

        return htmlentities((string) $value);
    }
}
