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
        'series' => 'articles.series',
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
    | Stripped sections
    |--------------------------------------------------------------------------
    |
    | Headings whose section — from the `## <heading>` line to the end of the
    | document — are removed from the RENDERED article only. The source markdown
    | file on disk is never modified. Matching is heading-prefix based and
    | whitespace-insensitive (e.g. "Channel notes" also matches "## Channel
    | notes (internal)"). Set to an empty array to disable stripping entirely.
    |
    */

    'strip_sections' => ['Channel notes'],

    /*
    |--------------------------------------------------------------------------
    | Markdown rendering
    |--------------------------------------------------------------------------
    |
    | Passed straight to the CommonMark environment. Articles are git-native and
    | author-trusted by default, so raw HTML and links pass through. If you ever
    | render untrusted markdown, set `html_input` to 'escape' (or 'strip') and
    | `allow_unsafe_links` to false.
    |
    */

    'markdown' => [
        'html_input' => 'allow',        // 'allow' | 'escape' | 'strip'
        'allow_unsafe_links' => true,   // false blocks javascript: and similar
    ],

    /*
    |--------------------------------------------------------------------------
    | Last-updated resolution
    |--------------------------------------------------------------------------
    |
    | "Last updated" prefers the article's last git commit date, falling back to
    | the file mtime. On hosts without a .git tree or without shell_exec (Vapor,
    | artifact-built containers), set `use_git` to false to skip the shell.
    |
    */

    'last_updated' => [
        'use_git' => true,
        'cache_ttl' => 86400,
    ],

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    */

    'views' => [
        'index' => 'articles.index',
        'show' => 'articles.show',
        'series' => 'articles.series',
    ],

    /*
    |--------------------------------------------------------------------------
    | Series manifests
    |--------------------------------------------------------------------------
    */

    'series' => [
        'path' => '_series',
        'url_prefix' => '/articles/series',
        'sitemap_changefreq' => 'monthly',
        'sitemap_priority' => '0.85',
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
