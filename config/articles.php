<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content path
    |--------------------------------------------------------------------------
    |
    | Root directory containing article folders. Each article lives at
    | {content_path}/{slug}/article.md with YAML frontmatter.
    |
    */

    'content_path' => base_path('articles'),

    /*
    |--------------------------------------------------------------------------
    | URL prefix
    |--------------------------------------------------------------------------
    */

    'url_prefix' => '/articles',

    /*
    |--------------------------------------------------------------------------
    | Route names
    |--------------------------------------------------------------------------
    */

    'route_names' => [
        'index' => 'articles',
        'show' => 'articles.show',
        'feed' => 'articles.feed',
        'sitemap' => 'articles.sitemap',
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */

    'routes' => [
        'enabled' => true,
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Discovery statuses
    |--------------------------------------------------------------------------
    */

    'discovery' => [
        'index' => ['ready', 'published'],
        'feed' => ['published'],
        'sitemap' => ['published'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    */

    'views' => [
        'index' => 'articles.index',
        'show' => 'articles.show',
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO defaults
    |--------------------------------------------------------------------------
    */

    'seo' => [
        'canonical_host' => env('APP_URL', 'http://localhost'),
        'site_name' => env('APP_NAME', 'Laravel'),
        'index_title' => 'Articles',
        'index_description' => 'Long-form articles.',
        'author' => [
            'name' => 'Author',
            'url' => null,
        ],
        'publisher' => [
            'name' => env('APP_NAME', 'Laravel'),
            'url' => env('APP_URL', 'http://localhost'),
            'logo' => '/favicon.svg',
        ],
        'default_og_image' => '/images/og/site-default.png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Atom feed
    |--------------------------------------------------------------------------
    */

    'feed' => [
        'enabled' => true,
        'path' => '/feed.xml',
        'title' => 'Articles',
        'subtitle' => 'Long-form articles.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap
    |--------------------------------------------------------------------------
    */

    'sitemap' => [
        'enabled' => true,
        'path' => '/sitemap.xml',
        'index_changefreq' => 'weekly',
        'index_priority' => '0.9',
        'article_changefreq' => 'monthly',
        'article_priority' => '0.8',
    ],

    /*
    |--------------------------------------------------------------------------
    | GitHub edit link (optional)
    |--------------------------------------------------------------------------
    */

    'github_edit_base' => null,

    /*
    |--------------------------------------------------------------------------
    | OG image generation
    |--------------------------------------------------------------------------
    */

    'og' => [
        'view' => 'laravel-articles::og.article-card',
        'output_dir' => 'images/og/articles',
        'site_default_output' => 'images/og/site-default.png',
        'kind_labels' => [
            'evergreen-explainer' => 'Explainer',
            'recipe-narrative' => 'Recipe Story',
            'announcement' => 'Announcement',
            'opinion' => 'Opinion',
        ],
    ],

];
