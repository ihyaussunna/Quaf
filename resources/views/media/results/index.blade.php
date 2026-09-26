@extends('layouts.media')

@section('title', 'Results & Poster Studio | QUAF 09 Media')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-900 text-white">
                    Result Studio
                </span>
                <span class="text-xs text-slate-500 font-mono">Stage Announcer & Media Sync</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-2 font-sans">
                Result Publishing & Poster Studio
            </h1>
            <p class="text-xs text-slate-600 mt-1 max-w-2xl">
                Once announced by stage coordinators, results appear here. Create and publish official podium posters (1st, 2nd, and 3rd winners only) to the public festival portal.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('media.results.templates') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors">
                Manage Poster Templates
            </a>
            <a href="{{ route('announcer.index') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors">
                View Announcer Desk
            </a>
        </div>
    </div>

    <!-- Stat Badges -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Announced Awaiting Poster -->
        <a href="{{ route('media.results.index', ['tab' => 'announced']) }}" 
           class="p-5 rounded-2xl border transition-all {{ $tab === 'announced' ? 'bg-amber-50 border-amber-300 ring-2 ring-amber-400' : 'bg-white border-slate-200 hover:border-slate-300' }}">
            <div class="flex items-center justify-between text-amber-700 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider">Awaiting Poster</span>
                @if($stats['announced_count'] > 0)
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                @endif
            </div>
            <div class="text-3xl font-black text-slate-900">{{ $stats['announced_count'] }}</div>
            <div class="text-[11px] text-amber-800 font-medium mt-1">Announced by stage, awaiting poster design</div>
        </a>

        <!-- Published Posters -->
        <a href="{{ route('media.results.index', ['tab' => 'published']) }}" 
           class="p-5 rounded-2xl border transition-all {{ $tab === 'published' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-400' : 'bg-white border-slate-200 hover:border-slate-300' }}">
            <div class="flex items-center justify-between text-emerald-700 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider">Published Posters</span>
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div class="text-3xl font-black text-slate-900">{{ $stats['published_count'] }}</div>
            <div class="text-[11px] text-emerald-800 font-medium mt-1">Official results published with poster</div>
        </a>

        <!-- Total Results -->
        <a href="{{ route('media.results.index', ['tab' => 'all']) }}" 
           class="p-5 rounded-2xl border transition-all {{ $tab === 'all' ? 'bg-slate-100 border-slate-300 ring-2 ring-slate-400' : 'bg-white border-slate-200 hover:border-slate-300' }}">
            <div class="flex items-center justify-between text-slate-500 mb-1">
                <span class="text-xs font-bold uppercase tracking-wider">All Festival Results</span>
                <span class="text-xs font-mono font-bold">{{ $stats['total_count'] }}</span>
            </div>
            <div class="text-3xl font-black text-slate-900">{{ $stats['total_count'] }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-1">All officially declared competition results</div>
        </a>
    </div>

    <!-- Search & Tabs -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('media.results.index', ['tab' => 'announced']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === 'announced' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                New from Announcer
                @if($stats['announced_count'] > 0)
                    <span class="ml-1.5 px-1.5 py-0.5 rounded-full bg-white text-amber-800 text-[10px]">{{ $stats['announced_count'] }}</span>
                @endif
            </a>
            <a href="{{ route('media.results.index', ['tab' => 'published']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === 'published' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Published Posters
            </a>
            <a href="{{ route('media.results.index', ['tab' => 'all']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors {{ $tab === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                All Results
            </a>
        </div>

        <form method="GET" action="{{ route('media.results.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="relative w-full md:w-64">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search program, code..."
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-[#be1e2d]">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <button type="submit" class="px-3.5 py-2 bg-slate-900 text-white font-bold text-xs rounded-xl hover:bg-slate-800 transition-colors">
                Search
            </button>
            @if($search)
                <a href="{{ route('media.results.index', ['tab' => $tab]) }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 text-xs font-medium rounded-xl hover:bg-slate-100 transition-colors">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Results Cards Grid -->
    @if($results->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($results as $res)
                @php
                    $prog = $res->program;
                    $winners = $res->getPosterWinners();
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div>
                        <!-- Header Bar of Card -->
                        <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded font-mono text-[11px] font-bold bg-slate-900 text-white">
                                    {{ $prog->code ?? 'PROG' }}
                                </span>
                                <span class="text-[11px] font-bold text-slate-600 uppercase">
                                    {{ $prog->eligibility ?? 'General' }}
                                </span>
                            </div>

                            @if($res->is_media_published)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    Poster Published
                                </span>
                            @elseif($res->status === 'announced')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 animate-pulse">
                                    Ready for Poster
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                                    {{ ucfirst($res->status) }}
                                </span>
                            @endif
                        </div>

                        <!-- Content Info -->
                        <div class="p-5 space-y-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 leading-snug">{{ $prog->name }}</h3>
                                @if($prog->malayalam_name)
                                    <div class="text-xs text-slate-500 font-ml mt-0.5">{{ $prog->malayalam_name }}</div>
                                @endif
                            </div>

                            <!-- Podium Winners (Only 1st, 2nd, 3rd) -->
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Podium Winners</div>
                                
                                <!-- 1st Place -->
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ asset('images/medals/first.png') }}" alt="1st" class="w-5 h-5 object-contain shrink-0">
                                    @if(!empty($winners['first']))
                                        <div class="truncate">
                                            <span class="font-bold text-slate-900">{{ $winners['first'][0]['name'] }}</span>
                                            <span class="text-slate-500 text-[11px]">({{ $winners['first'][0]['unit'] }})</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Not declared</span>
                                    @endif
                                </div>

                                <!-- 2nd Place -->
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ asset('images/medals/second.png') }}" alt="2nd" class="w-5 h-5 object-contain shrink-0">
                                    @if(!empty($winners['second']))
                                        <div class="truncate">
                                            <span class="font-bold text-slate-900">{{ $winners['second'][0]['name'] }}</span>
                                            <span class="text-slate-500 text-[11px]">({{ $winners['second'][0]['unit'] }})</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Not declared</span>
                                    @endif
                                </div>

                                <!-- 3rd Place -->
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ asset('images/medals/third.png') }}" alt="3rd" class="w-5 h-5 object-contain shrink-0">
                                    @if(!empty($winners['third']))
                                        <div class="truncate">
                                            <span class="font-bold text-slate-900">{{ $winners['third'][0]['name'] }}</span>
                                            <span class="text-slate-500 text-[11px]">({{ $winners['third'][0]['unit'] }})</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Not declared</span>
                                    @endif
                                </div>
                            </div>

                            @if($res->poster_image)
                                <div class="relative rounded-xl overflow-hidden aspect-video bg-slate-900 border border-slate-200 group">
                                    <img src="{{ $res->poster_image }}" alt="Generated Poster" class="w-full h-full object-cover">
                                    <a href="{{ route('media.results.public-poster', $res) }}" target="_blank" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold">
                                        View Poster Fullscreen
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="p-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between gap-2">
                        <a href="{{ route('media.results.studio', $res) }}" class="flex-1 text-center py-2 px-3 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs transition-colors shadow-xs">
                            {{ $res->poster_image ? 'Edit Poster Settings' : 'Create & Design Poster' }}
                        </a>

                        @if($res->poster_image)
                            <a href="{{ $res->poster_image }}" download="QUAF09_Result_{{ $prog->code }}.png" class="p-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 transition-colors" title="Download Poster Image">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </a>
                            <a href="{{ route('media.results.public-poster', $res) }}" target="_blank" class="p-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 transition-colors" title="Share Page">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($results->hasPages())
            <div class="p-4 bg-white rounded-xl border border-slate-200">
                {{ $results->links() }}
            </div>
        @endif
    @else
        <div class="bg-white p-12 rounded-2xl border border-slate-200 text-center space-y-3">
            <svg class="w-12 h-12 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <div class="font-bold text-slate-700 text-sm">No results found</div>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                When stage announcers declare results and mark them announced, they will appear here in real-time.
            </p>
        </div>
    @endif
</div>
@endsection
