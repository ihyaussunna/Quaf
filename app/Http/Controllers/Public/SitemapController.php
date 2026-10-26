<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [
            ['loc' => route('home.view'), 'priority' => '1.0', 'changefreq' => 'hourly'],
            ['loc' => route('results.index'), 'priority' => '0.9', 'changefreq' => 'always'],
            ['loc' => route('schedule.index'), 'priority' => '0.9', 'changefreq' => 'hourly'],
            ['loc' => route('groups.index'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => route('qf.directory'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => route('dp.index'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => route('news.index'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => route('gallery.index'), 'priority' => '0.7', 'changefreq' => 'daily'],
            ['loc' => route('videos.index'), 'priority' => '0.7', 'changefreq' => 'daily'],
            ['loc' => route('media.index'), 'priority' => '0.7', 'changefreq' => 'daily'],
            ['loc' => route('brochure.index'), 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => route('about'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => route('contact'), 'priority' => '0.6', 'changefreq' => 'monthly'],
        ];

        // Add published news articles dynamically
        try {
            $articles = News::where('status', 'published')
                ->orWhereNotNull('published_at')
                ->latest('updated_at')
                ->get(['slug', 'updated_at']);

            foreach ($articles as $article) {
                if (! empty($article->slug)) {
                    $urls[] = [
                        'loc' => route('news.show', $article->slug),
                        'priority' => '0.7',
                        'changefreq' => 'weekly',
                        'lastmod' => $article->updated_at?->toAtomString() ?? now()->toAtomString(),
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Silently handle if news cannot be retrieved
        }

        $now = now()->toAtomString();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ($urls as $u) {
            $lastmod = $u['lastmod'] ?? $now;
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($u['loc'], ENT_XML1).'</loc>'."\n";
            $xml .= '    <lastmod>'.$lastmod.'</lastmod>'."\n";
            $xml .= '    <changefreq>'.$u['changefreq'].'</changefreq>'."\n";
            $xml .= '    <priority>'.$u['priority'].'</priority>'."\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
