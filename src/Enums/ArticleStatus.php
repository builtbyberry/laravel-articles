<?php

namespace BuiltByBerry\LaravelArticles\Enums;

enum ArticleStatus: string
{
    case Draft = 'draft';
    case Ready = 'ready';
    case Published = 'published';
    case Archived = 'archived';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
