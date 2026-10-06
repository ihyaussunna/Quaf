@extends('layouts.public', ['title' => 'QUAF 9.0 — Markaz Cultural Festival 2026'])

@section('content')

<!-- Hero Section (Apple-inspired Festival Presentation) -->
<section class="relative min-h-[55vh] lg:min-h-[65vh] flex items-center justify-center overflow-hidden bg-slate-50 pt-8 sm:pt-14 pb-12 sm:pb-18 border-b border-slate-200/80">
    <!-- Atmospheric Ambient Glows -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[350px] sm:w-[650px] h-[300px] sm:h-[400px] bg-red-500/10 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute bottom-10 left-10 w-72 sm:w-96 h-72 sm:h-96 bg-amber-400/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute top-10 right-10 w-72 sm:w-96 h-72 sm:h-96 bg-blue-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full flex flex-col items-center text-center">
        <!-- Center Artwork Panel -->
        <div class="relative group max-w-lg w-full mb-8">
            <div class="glass-panel rounded-3xl p-6 sm:p-10 border border-slate-200/90 shadow-xl relative overflow-hidden flex flex-col items-center text-center">
                <div class="absolute -top-20 -right-20 w-44 h-44 bg-red-500/10 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-20 -left-20 w-44 h-44 bg-amber-500/15 rounded-full blur-2xl"></div>

                <img src="{{ asset('images/quaf-title-logo.png') }}" 
                     alt="QUAF 9.0" 
                     style="max-height: 260px; object-fit: contain;" 
                     class="w-full max-w-[260px] xs:max-w-[300px] sm:max-w-md h-auto object-contain drop-shadow-sm transition-transform duration-500 group-hover:scale-105">

                <!-- Badge Pills inside Hero Card -->
                <div class="grid grid-cols-3 gap-2.5 w-full mt-6 pt-6 border-t border-slate-200/80">
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-center">
                        <span class="font-sora font-black text-slate-900 text-base sm:text-lg block">{{ $stats['programs'] ?? 144 }}</span>
                        <span class="text-[10px] font-mono text-slate-500 uppercase tracking-tight">Programs</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-center">
                        <span class="font-sora font-black text-[#be1e2d] text-base sm:text-lg block">{{ $stats['groups'] ?? 5 }}</span>
                        <span class="text-[10px] font-mono text-slate-500 uppercase tracking-tight">Groups</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-center">
                        <span class="font-sora font-black text-[#009444] text-base sm:text-lg block">{{ $stats['stages'] ?? 8 }}</span>
                        <span class="text-[10px] font-mono text-slate-500 uppercase tracking-tight">Venues & Stages</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Primary Action CTAs -->
        <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4">
            <a href="{{ route('results.index') }}" class="px-7 py-3.5 rounded-xl font-bold text-xs sm:text-sm uppercase tracking-wider bg-gradient-to-r from-[#be1e2d] to-[#991522] text-white hover:brightness-110 shadow-md shadow-red-600/20 transition-all transform hover:-translate-y-0.5 text-center">
                View Live Results
            </a>
            <a href="{{ route('schedule.index') }}" class="px-7 py-3.5 rounded-xl font-semibold text-xs sm:text-sm tracking-wider bg-white border border-slate-300 hover:border-[#be1e2d] text-slate-800 transition-all transform hover:-translate-y-0.5 shadow-2xs text-center">
                Explore Schedule
            </a>
            <a href="{{ route('brochure.index') }}" class="px-5 py-3.5 rounded-xl font-semibold text-xs sm:text-sm tracking-wider bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 transition-all text-center">
                Brochure
            </a>
        </div>
    </div>
</section>

<!-- Quick Festival Metrics Strip (Section 14) -->
<section class="py-8 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="glass-panel p-4 rounded-2xl text-center shadow-2xs">
                <span class="text-[10px] font-mono uppercase text-slate-500 tracking-wider font-semibold block mb-0.5">Competitions</span>
                <span class="text-2xl sm:text-3xl font-rockwell font-bold text-slate-900">{{ number_format($stats['programs'] ?? 144) }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5">120+ Official Events</span>
            </div>
            <div class="glass-panel p-4 rounded-2xl text-center shadow-2xs">
                <span class="text-[10px] font-mono uppercase text-slate-500 tracking-wider font-semibold block mb-0.5">Registered Students</span>
                <span class="text-2xl sm:text-3xl font-rockwell font-bold text-[#be1e2d]">{{ number_format($stats['students'] ?? 640) }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5">Verified Delegates</span>
            </div>
            <div class="glass-panel p-4 rounded-2xl text-center shadow-2xs">
                <span class="text-[10px] font-mono uppercase text-slate-500 tracking-wider font-semibold block mb-0.5">Academic Groups</span>
                <span class="text-2xl sm:text-3xl font-rockwell font-bold text-[#2e3192]">{{ $stats['groups'] ?? 5 }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5">Official Houses</span>
            </div>
            <div class="glass-panel p-4 rounded-2xl text-center shadow-2xs">
                <span class="text-[10px] font-mono uppercase text-slate-500 tracking-wider font-semibold block mb-0.5">Active Stages</span>
                <span class="text-2xl sm:text-3xl font-rockwell font-bold text-[#009444]">{{ $stats['stages'] ?? 4 }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5">Grand Arenas</span>
            </div>
            <div class="glass-panel p-4 rounded-2xl text-center shadow-2xs col-span-2 md:col-span-1">
                <span class="text-[10px] font-mono uppercase text-slate-500 tracking-wider font-semibold block mb-0.5">Festival Divisions</span>
                <span class="text-2xl sm:text-3xl font-rockwell font-bold text-amber-600">{{ $stats['zones'] ?? 4 }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5">A, B, C & Mix Zones</span>
            </div>
        </div>
    </div>
</section>

<!-- Live Standings / Festival Standings (Section 15) -->
<section id="groups" class="py-14 sm:py-20 bg-white border-b border-slate-200 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">ACADEMIC GROUPS</span>
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1">Festival Standings</h2>
            </div>
            <div class="flex items-center gap-3">
                <p class="text-xs sm:text-sm text-slate-600 max-w-md">
                    Points are aggregated dynamically in real-time as official verdicts are published and verified.
                </p>
                <a href="{{ route('groups.index') }}" class="text-xs font-bold text-[#be1e2d] hover:underline whitespace-nowrap shrink-0">
                    All Groups →
                </a>
            </div>
        </div>

        <!-- 5 Official Groups Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-6">
            @foreach($groups as $group)
                @php
                    $isFirst = $loop->iteration === 1;
                    $isSecond = $loop->iteration === 2;
                    $isThird = $loop->iteration === 3;
                @endphp
                <a href="{{ route('groups.show', $group->id) }}" class="group relative rounded-2xl bg-white border-2 border-slate-200/90 hover:border-slate-300 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg flex flex-col justify-between"
                   style="border-top-color: {{ $group->color_hex }}; border-top-width: 4px;">
                    
                    <!-- Ambient Tint on Hover -->
                    <div class="absolute -right-10 -top-10 w-28 h-28 rounded-full opacity-10 group-hover:opacity-20 blur-xl transition-opacity duration-500"
                         style="background-color: {{ $group->color_hex }}"></div>

                    <div>
                        <!-- Header with Rank Badge & Code -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full shadow-2xs" style="background-color: {{ $group->color_hex }}"></span>
                                <span class="font-mono text-xs text-slate-700 font-bold uppercase tracking-wider">{{ $group->code }}</span>
                            </div>
                            
                            <!-- Rank Badge with Podium Styling -->
                            @if($isFirst)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                                    #1 LEADER
                                </span>
                            @elseif($isSecond)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                    #2
                                </span>
                            @elseif($isThird)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    #3
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-mono font-semibold bg-slate-100 text-slate-600">
                                    #{{ $group->rank_cache ?: $loop->iteration }}
                                </span>
                            @endif
                        </div>

                        <!-- Group Name -->
                        <h3 class="text-xl font-sora font-black text-slate-900 mb-1 group-hover:text-[#be1e2d] transition-colors">
                            {{ $group->name }}
                        </h3>
                        <p class="text-xs text-slate-500 mb-5 flex items-center gap-1.5 truncate">
                            <span class="truncate">Captain: {{ $group->manager_name ?: ($group->leader?->name ?? 'House Leadership') }}</span>
                        </p>
                    </div>

                    <!-- Points Metric in Rockwell Font -->
                    <div class="pt-4 border-t border-slate-100 flex items-baseline justify-between mt-auto">
                        <span class="text-xs font-mono text-slate-500 uppercase font-semibold">Tally</span>
                        <div class="text-3xl font-rockwell font-bold tracking-tight" style="color: {{ $group->color_hex }}">
                            {{ number_format($group->points_cache) }} <span class="text-xs font-mono text-slate-500 uppercase font-normal">PTS</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>



<!-- Festival Journal & News (Section 24) -->
@if($latestNews->isNotEmpty())
<section class="py-14 sm:py-20 bg-slate-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">FESTIVAL JOURNAL</span>
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1">Latest News & Dispatches</h2>
            </div>
            <a href="{{ route('news.index') }}" class="text-xs sm:text-sm font-bold text-[#be1e2d] hover:underline flex items-center gap-1">
                <span>View All Articles</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach($latestNews as $article)
                <a href="{{ route('news.show', $article->slug) }}" class="group flex flex-col rounded-2xl bg-white border border-slate-200 hover:border-slate-300 overflow-hidden transition-all duration-300 shadow-2xs hover:shadow-md">
                    <div class="aspect-video w-full overflow-hidden bg-slate-100 relative">
                        @if($article->cover_image)
                            <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-400 font-mono text-xs">
                                QUAF Press Desk
                            </div>
                        @endif
                        <div class="absolute top-3 left-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-mono uppercase tracking-wider bg-white/90 backdrop-blur-md text-[#be1e2d] border border-red-200 font-bold shadow-2xs">
                                {{ $article->category }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <span class="text-xs font-mono text-slate-400 block mb-2">{{ $article->published_at?->format('M d, Y') ?? 'Recent' }}</span>
                            <h3 class="text-lg font-sora font-bold text-slate-900 group-hover:text-[#be1e2d] transition-colors leading-snug mb-2">
                                {{ $article->title }}
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 line-clamp-2 leading-relaxed">
                                {{ $article->excerpt }}
                            </p>
                        </div>
                        <span class="text-xs font-bold text-[#be1e2d] mt-4 flex items-center gap-1">
                            Read Dispatch →
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Visual Gallery Preview (Section 23) -->
@if($galleryPreview->isNotEmpty())
<section class="py-14 sm:py-20 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-mono font-bold tracking-widest text-[#be1e2d] uppercase">MOMENTS OF SPLENDOR</span>
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-sora font-black text-slate-900 mt-1">Festival Gallery</h2>
            </div>
            <a href="{{ route('gallery.index') }}" class="text-xs sm:text-sm font-bold text-[#be1e2d] hover:underline flex items-center gap-1">
                <span>View Full Photo Archive</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($galleryPreview as $photo)
                <div class="group relative rounded-2xl overflow-hidden aspect-[4/3] bg-slate-100 shadow-2xs">
                    <img src="{{ $photo->image_path }}" alt="{{ $photo->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                        <span class="text-xs sm:text-sm font-medium text-white truncate">{{ $photo->title }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
