<?php

namespace craft\ckeditor\helpers;

class Toolbar
{
    private static array $_items = [
        ['button' => 'heading', 'configOption' => 'heading'],
        ['button' => 'style', 'configOption' => 'style'],
        ['button' => 'alignment', 'configOption' => 'alignment'],
        'bold',
        'italic',
        'underline',
        'strikethrough',
        'subscript',
        'superscript',
        'code',
        'link',
        // 'anchor',
        'textPartLanguage',
        ['button' => 'fontSize', 'configOption' => 'fontSize'],
        'fontFamily',
        'fontColor',
        'fontBackgroundColor',
        'insertImage',
        'mediaEmbed',
        'htmlEmbed',
        'blockQuote',
        'insertTable',
        'codeBlock',
        'bulletedList',
        'numberedList',
        'todoList',
        ['outdent', 'indent'],
        'horizontalLine',
        'pageBreak',
        'removeFormat',
        'selectAll',
        'findAndReplace',
        ['undo', 'redo'],
        'sourceEditing',
        'createEntry',
    ];

    public static function items(): array
    {
        return self::normalizeToolbarItems(self::$_items);
    }

    private static function normalizeToolbarItem($item): array
    {
        if (is_string($item)) {
            $item = ['button' => $item];
        }

        if (array_is_list($item)) {
            $item = collect($item)->map(fn($item) => ['button' => $item])->toArray();
        }

        return [$item];
    }

    public static function normalizeToolbarItems($items): array
    {
        return collect($items)
            ->map(fn($item) => self::normalizeToolbarItem($item))
            ->toArray();
    }

}
