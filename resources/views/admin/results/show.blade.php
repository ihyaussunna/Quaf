@extends('layouts.admin', ['title' => 'Result Verdict: ' . $result->program->name])

@section('content')
<div class="max-w-5xl mx-auto space-y-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.results.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">&larr; Back to Results</a>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-amber-50 text-[#f3bd2e] border border-[#f3bd2e]/30">{{ $result->program->code }}</span>
                <span class="text-xs font-mono text-slate-500">{{ $result->program->eligibility ?? 'A Zone' }} &bull; {{ ucfirst($result->program->type) }}</span>
            </div>
            <h1 class="text-3xl font-sora font-black text-slate-900">{{ $result->program->name }}</h1>
            @if($result->program->malayalam_name)
                <p class="text-sm font-ml text-slate-500 mt-0.5">{{ $result->program->malayalam_name }}</p>
            @endif
        </div>

        <div class="flex items-center gap-3">
            @if($result->status === 'published')
                <form method="POST" action="{{ route('admin.results.unpublish', $result) }}" onsubmit="return confirm('Do you want to hide/draft this published result? It will be removed from public view and points will be recalculated.')">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-amber-500 text-slate-950 hover:bg-amber-400 shadow-md">
                        Hide / Revert to Draft
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.results.publish', $result) }}">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-emerald-600 text-white hover:bg-emerald-700 shadow-md">
                        Publish Verdict & Distribute Rewards
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.results.edit', $result) }}" class="px-4 py-2.5 rounded-xl text-xs font-mono font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200">
                Edit Verdict
            </a>
        </div>
    </div>

    <!-- Podium Winners Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- 1st Place -->
        <div class="p-5 rounded-2xl bg-white border-2 border-amber-300 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-amber-400 text-slate-950 uppercase">1st Place</span>
                <span class="text-xs font-mono font-bold text-amber-700">Champion</span>
            </div>
            @if($result->firstEntry)
                <div class="font-sora font-bold text-base text-slate-900">
                    {{ $result->firstEntry->student?->name ?? $result->firstEntry->group?->name }}
                </div>
                <div class="text-xs font-mono text-slate-500 mt-1">
                    {{ $result->firstEntry->group?->name }} &bull; Chest #{{ $result->firstEntry->chest_number }}
                </div>
                @if($result->first_grade)
                    <div class="mt-2 text-xs font-mono font-bold text-emerald-700">
                        Declared Grade: {{ $result->first_grade }}
                    </div>
                @endif
            @else
                <div class="text-xs font-mono text-slate-400 italic">Not Assigned</div>
            @endif
        </div>

        <!-- 2nd Place -->
        <div class="p-5 rounded-2xl bg-white border border-slate-300 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-slate-200 text-slate-800 uppercase">2nd Place</span>
                <span class="text-xs font-mono font-bold text-slate-600">Runner Up</span>
            </div>
            @if($result->secondEntry)
                <div class="font-sora font-bold text-base text-slate-900">
                    {{ $result->secondEntry->student?->name ?? $result->secondEntry->group?->name }}
                </div>
                <div class="text-xs font-mono text-slate-500 mt-1">
                    {{ $result->secondEntry->group?->name }} &bull; Chest #{{ $result->secondEntry->chest_number }}
                </div>
                @if($result->second_grade)
                    <div class="mt-2 text-xs font-mono font-bold text-emerald-700">
                        Declared Grade: {{ $result->second_grade }}
                    </div>
                @endif
            @else
                <div class="text-xs font-mono text-slate-400 italic">Not Assigned</div>
            @endif
        </div>

        <!-- 3rd Place -->
        <div class="p-5 rounded-2xl bg-white border border-amber-200 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-amber-100 text-amber-900 uppercase">3rd Place</span>
                <span class="text-xs font-mono font-bold text-amber-800">2nd Runner Up</span>
            </div>
            @if($result->thirdEntry)
                <div class="font-sora font-bold text-base text-slate-900">
                    {{ $result->thirdEntry->student?->name ?? $result->thirdEntry->group?->name }}
                </div>
                <div class="text-xs font-mono text-slate-500 mt-1">
                    {{ $result->thirdEntry->group?->name }} &bull; Chest #{{ $result->thirdEntry->chest_number }}
                </div>
                @if($result->third_grade)
                    <div class="mt-2 text-xs font-mono font-bold text-emerald-700">
                        Declared Grade: {{ $result->third_grade }}
                    </div>
                @endif
            @else
                <div class="text-xs font-mono text-slate-400 italic">Not Assigned</div>
            @endif
        </div>
    </div>

    <!-- Coin Preview for Group Programs -->
    @if(!empty($coinPreview))
        @include('admin.results.partials.coin_preview_card', ['coinPreview' => $coinPreview])
    @endif
</div>
@endsection
