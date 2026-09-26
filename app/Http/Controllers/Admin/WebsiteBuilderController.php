<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FestivalSetting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteBuilderController extends Controller
{
    public function index(): View
    {
        $settings = [
            'hero_title' => FestivalSetting::get('hero_title', 'QUAF — Season 09'),
            'hero_subtitle' => FestivalSetting::get('hero_subtitle', 'The Grand Cultural Conclave of Talents'),
            'hero_tagline' => FestivalSetting::get('hero_tagline', 'Ihyaussunna Students Union • Markazu Saquafathi Sunniyya'),
            'announcement_ticker' => FestivalSetting::get('announcement_ticker', 'Welcome to QUAF 09 — Live Results and Stage Updates Streaming Now!'),
            'live_stream_url' => FestivalSetting::get('live_stream_url', 'https://www.youtube.com/embed/live_stream?channel=markaz'),
            'about_heading' => FestivalSetting::get('about_heading', 'About QUAF Season 09'),
            'about_text' => FestivalSetting::get('about_text', 'QUAF is the premier cultural and literary festival organized by Ihyaussunna Students Union, celebrating creativity, arts, and intellectual excellence across multiple competitive categories.'),
            'contact_phone' => FestivalSetting::get('contact_phone', '+91 98470 12345'),
            'contact_email' => FestivalSetting::get('contact_email', 'festival@markaz.in'),
            'instagram_url' => FestivalSetting::get('instagram_url', 'https://instagram.com/quaf_markaz'),
            'youtube_url' => FestivalSetting::get('youtube_url', 'https://youtube.com/@markazlive'),
            'whatsapp_channel' => FestivalSetting::get('whatsapp_channel', 'https://whatsapp.com/channel/quaf09'),
            'show_live_banner' => FestivalSetting::get('show_live_banner', '1'),
            'show_schedule_cta' => FestivalSetting::get('show_schedule_cta', '1'),
            'accent_color' => FestivalSetting::get('accent_color', 'emerald'),
        ];

        return view('admin.website-builder.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $fields = [
            'hero_title',
            'hero_subtitle',
            'hero_tagline',
            'announcement_ticker',
            'live_stream_url',
            'about_heading',
            'about_text',
            'contact_phone',
            'contact_email',
            'instagram_url',
            'youtube_url',
            'whatsapp_channel',
            'show_live_banner',
            'show_schedule_cta',
            'accent_color',
        ];

        foreach ($fields as $field) {
            $value = $request->input($field, '');
            FestivalSetting::set($field, (string) $value);
        }

        AuditLogger::log('update_website_builder', null, null, $request->only($fields));

        return back()->with('success', 'Website configuration updated successfully! Changes are live on the public portal.');
    }
}
