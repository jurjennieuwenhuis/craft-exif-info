<?php

namespace juni\exifinfo;

use Craft;
use craft\base\Element;
use craft\base\Event;
use craft\base\LocalFsInterface;
use craft\base\Plugin as BasePlugin;
use craft\elements\Asset;
use craft\events\DefineHtmlEvent;
use craft\events\DefineMetadataEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\FileHelper;
use craft\services\Fields;
use juni\exifinfo\fields\ExifInfoPanelField;
use juni\exifinfo\plugin\Services;

/**
 * craft-exif-info plugin
 *
 * @method static ExifInfo getInstance()
 */
class ExifInfo extends BasePlugin
{
    use Services;

    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * ExifInfo::$plugin
     *
     * @var ExifInfo
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin's migrations, you'll need to increase its schema version.
     *
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * ExifInfo::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init(): void
    {
        parent::init();

        require __DIR__ . '/../vendor/autoload.php';

        self::$plugin = $this;

        // Load the services
        $this->setPluginComponents();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            // ...
        });

        /**
         * Logging in Craft involves using one of the following methods:
         *
         * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
         * Craft::info(): record a message that conveys some useful information.
         * Craft::warning(): record a warning message that indicates something unexpected has happened.
         * Craft::error(): record a fatal error that should be investigated as soon as possible.
         *
         * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
         *
         * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
         * the category to the method (prefixed with the fully qualified class name) where the constant appears.
         *
         * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
         * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
         *
         * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
         */
        Craft::info(
            Craft::t(
                'juni-exif-info',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================


    // Private Methods
    // =========================================================================


    private function attachEventHandlers(): void
    {
        Event::on(
            Element::class,
            Element::EVENT_DEFINE_META_FIELDS_HTML,
            [$this, 'onDefineMetaFieldsHtml']
        );

//        Event::on(
//            Element::class,
//            Element::EVENT_DEFINE_SIDEBAR_HTML,
//            [$this, 'onDefineMetaFieldsHtml']
//        );

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            static function (RegisterComponentTypesEvent $event) {
                $event->types[] = ExifInfoPanelField::class;
            }
        );
    }

    public function onDefineMetaFieldsHtml(DefineHtmlEvent $event): void
    {
        if (!$event->sender instanceof Asset) {
            return;
        }

        if (null === $filePath = $this->getAssetHelper()->getFilePath($event->sender)) {
            return;
        }

        if (!$this->getAssetHelper()->isJpg($event->sender)) {
            return;
        }

        $exif = $this->getExifReader()->read($filePath);

        $fields = [];

        if ($creationDate = $exif->getCreationDate()) {
            $fields[Craft::t('juni-exif-info', 'Taken on')] = $creationDate->format('Y-m-d H:i:s');
        }

        if (!empty($exif->getGPS())) {
            $fields[Craft::t('juni-exif-info', 'Position')] = $exif->getGoogleMapsLink();
        }

        if ($personInImage = $exif->getPersonInImage()) {
            $fields[Craft::t('juni-exif-info', 'Person in image')] = $personInImage;
        }

        $html = '';

        foreach ($fields as $name => $value) {
            $html .= sprintf('<div class="field"><div class="heading"><label>%s</label></div><div class="input ltr">%s</div></div>', $name, $value);
        }

        $event->html = $html;
    }
}
