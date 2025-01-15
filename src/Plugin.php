<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license GPL-3.0-or-later
 */

namespace craft\ckeditor;

use Craft;
use craft\base\Element;
use craft\ckeditor\web\assets\BaseCkeditorPackageAsset;
use craft\ckeditor\web\assets\ckeditor\CkeditorAsset;
use craft\elements\NestedElementManager;
use craft\events\AssetBundleEvent;
use craft\events\ModelEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Fields;
use craft\web\UrlManager;
use craft\web\View;
use yii\base\Event;

/**
 * CKEditor plugin.
 *
 * @method static Plugin getInstance()
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @property-read CkeConfigs $ckeConfigs
 */
class Plugin extends \craft\base\Plugin
{
    public static function config(): array
    {
        return [
            'components' => [
                'ckeConfigs' => CkeConfigs::class,
            ],
        ];
    }

    /**
     * Registers an asset bundle for a CKEditor package.
     *
     * @param string $name The asset bundle class name. The asset bundle should extend
     * [[\craft\ckeditor\web\assets\BaseCkeditorPackageAsset]].
     * @since 3.5.0
     */
    public static function registerCkeditorPackage(string $name): void
    {
        self::$ckeditorPackages[$name] = true;
    }

    private static array $ckeditorPackages = [];

    public static array $pluginButtonMap = [
        [
            'plugins' => ['Alignment'],
            'buttons' => ['alignment']
        ],
        // ['plugins' => ['Anchor'], 'buttons' => ['anchor']],
        [
            'plugins' => [
                'AutoImage',
                // 'CraftImageInsertUI',
                'Image',
                'ImageCaption',
                'ImageStyle',
                'ImageToolbar',
                'ImageTransform',
                'ImageEditor',
                'LinkImage',
            ],
            'buttons' => ['insertImage']
        ],
        [
            'plugins' => ['AutoLink', 'CraftLinkUI', 'LinkEditing', 'LinkImage'],
            'buttons' => ['link']
        ],
        [
            'plugins' => ['BlockQuote'],
            'buttons' => ['blockQuote']
        ],
        [
            'plugins' => ['Bold'],
            'buttons' => ['bold']
        ],
        [
            'plugins' => ['Code'],
            'buttons' => ['code']
        ],
        [
            'plugins' => ['CodeBlock'],
            'buttons' => ['codeBlock']
        ],
        [
            'plugins' => ['List', 'ListProperties'],
            'buttons' => ['bulletedList', 'numberedList']
        ],
        [
            'plugins' => ['Font'],
            'buttons' => ['fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor']
        ],
        [
            'plugins' => ['FindAndReplace'],
            'buttons' => ['findAndReplace']
        ],
        [
            'plugins' => ['Heading'],
            'buttons' => ['heading']
        ],
        [
            'plugins' => ['HorizontalLine'],
            'buttons' => ['horizontalLine']
        ],
        [
            'plugins' => ['HtmlEmbed'],
            'buttons' => ['htmlEmbed']
        ],
        [
            'plugins' => ['Indent', 'IndentBlock'],
            'buttons' => ['outdent', 'indent']
        ],
        [
            'plugins' => ['Italic'],
            'buttons' => ['italic']
        ],
        [
            'plugins' => ['MediaEmbed', 'MediaEmbedToolbar'],
            'buttons' => ['mediaEmbed']
        ],
        [
            'plugins' => ['PageBreak'],
            'buttons' => ['pageBreak']
        ],
        [
            'plugins' => ['RemoveFormat'],
            'buttons' => ['removeFormat']
        ],
        [
            'plugins' => ['SourceEditing'],
            'buttons' => ['sourceEditing']
        ],
        [
            'plugins' => ['Strikethrough'],
            'buttons' => ['strikethrough']
        ],
        [
            'plugins' => ['Style'],
            'buttons' => ['style']
        ],
        [
            'plugins' => ['Subscript'],
            'buttons' => ['subscript']
        ],
        [
            'plugins' => ['Superscript'],
            'buttons' => ['superscript']
        ],
        [
            'plugins' => [
                'Table',
                'TableCaption',
                'TableCellProperties',
                'TableProperties',
                'TableToolbar',
                'TableUI',
            ],
            'buttons' => ['insertTable']
        ],
        [
            'plugins' => ['TextPartLanguage'],
            'buttons' => ['textPartLanguage']
        ],
        [
            'plugins' => ['TodoList'],
            'buttons' => ['todoList']
        ],
        [
            'plugins' => ['Underline'],
            'buttons' => ['underline']
        ],
        [
            'plugins' => ['CraftEntries'],
            'buttons' => ['createEntry']
        ]
    ];

    public static array $ckeditorPlugins = [
        'ckeditor5' => [
            'Paragraph',
            'SelectAll',
            'Clipboard',
            'Alignment',
            // 'Anchor',
            'AutoImage',
            'AutoLink',
            'Autoformat',
            'BlockQuote',
            'Bold',
            'Code',
            'CodeBlock',
            'List',
            'ListProperties',
            'Essentials',
            'FindAndReplace',
            'Font',
            'GeneralHtmlSupport',
            'Heading',
            'HorizontalLine',
            'HtmlComment',
            'HtmlEmbed',
            'Image',
            'ImageCaption',
            'ImageStyle',
            'ImageToolbar',
            'Indent',
            'IndentBlock',
            'Italic',
            'LinkEditing',
            'LinkImage',
            'MediaEmbed',
            'MediaEmbedToolbar',
            'PageBreak',
            'PasteFromOffice',
            'RemoveFormat',
            'SourceEditing',
            'Strikethrough',
            'Style',
            'Subscript',
            'Superscript',
            'Table',
            'TableCaption',
            'TableCellProperties',
            'TableProperties',
            'TableToolbar',
            'TableUI',
            'TextPartLanguage',
            'TodoList',
            'Underline',
            'WordCount',
        ],
        '@craftcms/ckeditor' => [
            'CraftImageInsertUI',
            'ImageTransform',
            'ImageEditor',
            'CraftLinkUI',
            'CraftEntries',
        ]
    ];

    public string $schemaVersion = '3.0.0.0';
    public bool $hasCpSettings = true;


    public function init()
    {
        parent::init();

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = Field::class;
        });

        Event::on(Fields::class, Fields::EVENT_REGISTER_NESTED_ENTRY_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = Field::class;
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules += [
                'settings/ckeditor' => 'ckeditor/cke-configs/index',
                'settings/ckeditor/new' => 'ckeditor/cke-configs/edit',
                'settings/ckeditor/<uid:{uid}>' => 'ckeditor/cke-configs/edit',
            ];
        });

        Event::on(View::class, View::EVENT_AFTER_REGISTER_ASSET_BUNDLE, function(AssetBundleEvent $event) {
            if ($event->bundle instanceof CkeditorAsset) {
                /** @var View $view */
                $view = $event->sender;
                foreach (array_keys(self::$ckeditorPackages) as $name) {
                    $bundle = $view->registerAssetBundle($name);
                    if ($bundle instanceof BaseCkeditorPackageAsset) {
                        $bundle->registerPackage($view);
                    }
                }
            }
        });

        Event::on(Element::class, Element::EVENT_AFTER_PROPAGATE, function(ModelEvent $event) {
            /** @var Element $element */
            $element = $event->sender;
            foreach ($this->entryManagers($element) as $entryManager) {
                $entryManager->maintainNestedElements($element, $event->isNew);
            }
        });

        Event::on(Element::class, Element::EVENT_BEFORE_DELETE, function(ModelEvent $event) {
            /** @var Element $element */
            $element = $event->sender;
            foreach ($this->entryManagers($element) as $entryManager) {
                // Delete any entries that primarily belong to this element
                $entryManager->deleteNestedElements($element, $element->hardDelete);
            }
        });

        Event::on(Element::class, Element::EVENT_AFTER_RESTORE, function(Event $event) {
            /** @var Element $element */
            $element = $event->sender;
            foreach ($this->entryManagers($element) as $entryManager) {
                $entryManager->restoreNestedElements($element);
            }
        });
    }

    /**
     * @param Element $element
     * @return NestedElementManager[]
     */
    private function entryManagers(Element $element): array
    {
        $entryManagers = [];
        $customFields = $element->getFieldLayout()?->getCustomFields() ?? [];
        foreach ($customFields as $field) {
            if ($field instanceof Field && !isset($entryManagers[$field->id])) {
                $entryManagers[$field->id] = Field::entryManager($field);
            }
        }
        return array_values($entryManagers);
    }

    public function getCkeConfigs(): CkeConfigs
    {
        return $this->get('ckeConfigs');
    }

    public function getSettingsResponse(): mixed
    {
        return Craft::$app->controller->redirect('settings/ckeditor');
    }
}
