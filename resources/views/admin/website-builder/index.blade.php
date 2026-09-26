@extends('layouts.admin', ['title' => 'Website Builder | FestFloww'])

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif font-black text-slate-900">Website Builder & Content</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">Customize public landing page content, live stream feeds, announcements, and contact information.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase hover:brightness-110 shadow-xs flex items-center gap-1.5">
                <span>View Public Site</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.website-builder.update') }}" class="space-y-8">
        @csrf

        <!-- 1. Hero Section Content -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-5">
            <div class="border-b border-slate-200 pb-3">
                <h2 class="font-serif font-bold text-lg text-slate-900 flex items-center gap-2">
                    <span>🌟</span> Hero Section & Branding
                </h2>
                <p class="text-xs font-mono text-slate-500">Main headline, subtitle, and organizing bodies featured at the top of the homepage.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-mono font-bold uppercase text-slate-600 mb-1">Festival Main Title</label>
                    <input type="text" name="hero_title" value="{{ old('hero_title', $settings['hero_title']) }}"
                           class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-mono font-bold uppercase text-slate-600 mb-1">Tagline / Subtitle</label>
                    <input type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $settings['hero_subtitle']) }}"
                           class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white text-slate-900">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-mono font-bold uppercase text-slate-600 mb-1">Organizing Body / Attribution</label>
                    <input type="text" name="hero_tagline" value="{{ old('hero_tagline', $settings['hero_tagline']) }}"
                           class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white text-slate-900">
                </div>
            </div>
        </div>

        <!-- 2. Live Broadcast & Announcements -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-5">
            <div class="border-b border-slate-200 pb-3">
                <h2 class="font-serif font-bold text-lg text-slate-900 flex items-center gap-2">
                    <span>🔴</span> Live Broadcast & Flash News
                </h2>
                <p class="text-xs font-mono text-slate-500">YouTube live stream link and real-time announcement marquee ticker.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-mono font-bold uppercase text-slate-600 mb-1">Announcement Ticker Text</label>
                    <input type="text" name="announcement_ticker" value="{{ old('announcement_ticker', $settings['announcement_ticker']) }}"
                           placeholder="Flash announcement displayed at the top of the portal..."
                           class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white text-slate-900">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-mono font-bold uppercase text-slate-600 mb-1">Live Stream Embed URL (YouTube)</label>
                    <input type="url" name="live_stream_url" value="{{ old('live_stream_url', $settings['live_stream_url']) }}"
                           placeholder="https://www.youtube.com/embed/..."
                           class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white text-slate-900">
                </div>
            </div>
        </div>

        <!-- 3. About Section -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-5">
            <div class="border-b border-slate-200 pb-3">
                <h2 class="font-serif font-bold text-lg text-slate-900 flex items-center gap-2">
                    <span>📖</span> About Section
                </h2>
                <p class="text-xs font-mono text-slate-500">Tell visitors about the festival background, heritage, and vision.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-mono font-bold uppercase text-slate-600 mb-1">Section Heading</label>
                    <input type="text" name="about_heading" value="{{ old('about_heading', $settings['about_heading']) }}"
                           class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-mono font-bold uppercase text-slate-600 mb-1">About Text / Description</label>
                    <textarea name="about_text" rows="4"
                              class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white text-slate-900">{{ old('about_text', $settings['about_text']) }}</textarea>
                </div>
            </div>
        </div>

        <!-- 4. Contact & Social Channels -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-5">
            <div class="border-b border-slate-200 pb-3">
                <h2 class="font-serif font-bold text-lg text-slate-900 flex items-center gap-2">
                    <span>📱</span> Contact & Social Media Channels
                </h2>
                <p class="text-xs font-mono text-slate-500">Helpline phone number, official email, and links for social media handles.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-mono font-bold uppercase text-slate-600 mb-1">Helpline Phone Number</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}"
                           class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-mono font-bold uppercase text-slate-600 mb-1">Official Email Address</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}"
                           class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-mono font-bold uppercase text-slate-600 mb-1">WhatsApp Channel URL</label>
                    <input type="url" name="whatsapp_channel" value="{{ old('whatsapp_channel', $settings['whatsapp_channel']) }}"
                           placeholder="https://whatsapp.com/channel/..."
                           class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-mono font-bold uppercase text-slate-600 mb-1">Instagram Profile URL</label>
                    <input type="url" name="instagram_url" value="{{ old('instagram_url', $settings['instagram_url']) }}"
                           placeholder="https://instagram.com/..."
                           class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-mono font-bold uppercase text-slate-600 mb-1">YouTube Channel URL</label>
                    <input type="url" name="youtube_url" value="{{ old('youtube_url', $settings['youtube_url']) }}"
                           placeholder="https://youtube.com/@..."
                           class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-mono font-bold uppercase text-slate-600 mb-1">Show Live Results Banner</label>
                    <select name="show_live_banner" class="w-full px-3 py-2 text-xs font-mono rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-[#f3bd2e] focus:bg-white text-slate-900">
                        <option value="1" {{ $settings['show_live_banner'] === '1' ? 'selected' : '' }}>Enabled (Show Banner)</option>
                        <option value="0" {{ $settings['show_live_banner'] === '0' ? 'selected' : '' }}>Disabled (Hide Banner)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 rounded-xl bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase tracking-wider hover:brightness-110 shadow-sm">
                💾 Save Website Configuration
            </button>
        </div>
    </form>
</div>
@endsection
