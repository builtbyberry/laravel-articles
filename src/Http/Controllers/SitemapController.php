<?php

namespace BuiltByBerry\LaravelArticles\Http\Controllers;

use BuiltByBerry\LaravelArticles\Sitemap\ArticlesSitemapProvider;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class SitemapController extends Controller
{
    public function __construct(private ArticlesSitemapProvider $sitemap) {}

    public function __invoke(): Response
    {
        $xml = $this->renderXml($this->sitemap->entries());

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * @param  list<array{loc: string, lastmod: ?string, changefreq: string, priority: string}>  $entries
     */
    private function renderXml(array $entries): string
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($entries as $entry) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.$this->escape($entry['loc'])."</loc>\n";
            if (! empty($entry['lastmod'])) {
                $xml .= '    <lastmod>'.$this->escape((string) $entry['lastmod'])."</lastmod>\n";
            }
            $xml .= '    <changefreq>'.$this->escape($entry['changefreq'])."</changefreq>\n";
            $xml .= '    <priority>'.$this->escape($entry['priority'])."</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
