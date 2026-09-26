@extends('layouts.admin')

@section('title', 'View Group Points - QUAF Fest')

@section('content')
<div class="space-y-6">
    <div class="text-center max-w-xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900">View Group Points</h1>
        <p class="text-xs text-gray-500 mt-1">Live festival points leaderboard by official competition groups</p>
    </div>

    <!-- Stack of group point cards -->
    <div class="max-w-3xl mx-auto space-y-3.5 pt-4">
        @forelse($teams as $index => $group)
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs flex items-center justify-between hover:border-gray-200 transition">
                <div class="flex items-center gap-4 sm:gap-6">
                    <span class="w-8 h-8 rounded-xl font-bold text-sm flex items-center justify-center bg-slate-100 border border-slate-200 text-slate-800">
                        #{{ $group->rank_cache ?: ($index + 1) }}
                    </span>
                    <div class="flex items-center gap-3">
                        <span class="w-3.5 h-3.5 rounded-full border border-slate-300 shadow-xs shrink-0" style="background-color: {{ $group->color_hex }}"></span>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 tracking-wide">
                                {{ $group->name }}
                            </h3>
                            <span class="text-xs text-gray-500 font-mono">{{ $group->code }} &bull; {{ $group->students_count }} delegates</span>
                        </div>
                    </div>
                </div>
                <div>
                    <span class="px-4 py-1.5 rounded-xl font-mono font-bold text-lg shadow-xs border" style="background-color: {{ $group->color_hex }}15; color: {{ $group->color_hex }}; border-color: {{ $group->color_hex }}40;">
                        {{ number_format($group->points_cache ?? 0) }} <span class="text-xs font-normal opacity-80">pts</span>
                    </span>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-gray-400">
                No groups found.
            </div>
        @endforelse
    </div>
</div>
@endsection
