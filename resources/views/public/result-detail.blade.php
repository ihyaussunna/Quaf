@extends('layouts.public', ['title' => $program->name . ' — Results | QUAF Season 09'])

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16">
    <div class="mb-6 sm:mb-8">
        <a href="{{ route('results.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline flex items-center gap-1 mb-3 sm:mb-4 font-semibold">
            ← Back to All Results
        </a>
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-2">
            <span class="px-2.5 py-1 rounded bg-amber-50 border border-amber-200 text-[#f3bd2e] font-mono text-xs font-bold">{{ $program->code }}</span>
            <span class="text-slate-500 text-xs font-mono">{{ $program->eligibility ?? 'A Zone' }} • {{ ucfirst($program->type) }}</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-2xl sm:text-4xl lg:text-5xl font-serif font-black text-slate-900 leading-tight">
                {{ $program->name }}
            </h1>
            @if($result->poster_image)
                <a href="{{ route('media.results.public-poster', $result) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shrink-0 shadow-xs transition-colors">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>View Official Poster</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Podium Winners Banner (Light Theme & Mobile Optimized) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-8 sm:mb-12">
        <!-- 1st Place -->
        <div class="md:order-2 rounded-2xl bg-gradient-to-b from-amber-50 to-white border-2 border-[#f3bd2e] p-6 sm:p-8 text-center relative overflow-hidden shadow-md">
            <div class="w-12 h-12 rounded-full bg-[#f3bd2e] text-white font-serif font-black text-xl flex items-center justify-center mx-auto mb-3 sm:mb-4 shadow-sm">1</div>
            <span class="text-xs font-mono font-bold tracking-widest text-[#f3bd2e] uppercase block mb-1">CHAMPION</span>
            <h3 class="text-xl sm:text-2xl font-serif font-black text-slate-900 mb-2">
                {{ $result->firstEntry?->student?->name ?? 'Team ' . $result->firstEntry?->group?->name }}
            </h3>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-xs font-mono text-slate-700 mb-3 sm:mb-4">
                <span>Chest #{{ $result->firstEntry?->chest_number }}</span>
                <span>•</span>
                <span class="font-bold text-[#f3bd2e]">{{ $result->firstEntry?->group?->name }}</span>
            </div>
            @if($result->firstEntry?->certificate)
                <div>
                    <a href="{{ route('verify.certificate', $result->firstEntry->certificate->certificate_number) }}" class="text-xs font-mono text-[#f3bd2e] hover:underline font-semibold">
                        View Certificate #{{ $result->firstEntry->certificate->certificate_number }} →
                    </a>
                </div>
            @endif
        </div>

        <!-- 2nd Place -->
        @if($result->secondEntry)
            <div class="md:order-1 rounded-2xl bg-white border border-slate-200 p-5 sm:p-6 text-center shadow-xs">
                <div class="w-10 h-10 rounded-full bg-slate-300 text-slate-800 font-serif font-black text-lg flex items-center justify-center mx-auto mb-3 sm:mb-4">2</div>
                <span class="text-xs font-mono font-bold tracking-widest text-slate-500 uppercase block mb-1">SECOND PLACE</span>
                <h3 class="text-lg sm:text-xl font-serif font-bold text-slate-900 mb-2">
                    {{ $result->secondEntry?->student?->name ?? 'Team ' . $result->secondEntry?->group?->name }}
                </h3>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-xs font-mono text-slate-700 mb-2">
                    <span>Chest #{{ $result->secondEntry?->chest_number }}</span>
                    <span>•</span>
                    <span class="font-bold">{{ $result->secondEntry?->group?->name }}</span>
                </div>
            </div>
        @endif

        <!-- 3rd Place -->
        @if($result->thirdEntry)
            <div class="md:order-3 rounded-2xl bg-white border border-slate-200 p-5 sm:p-6 text-center shadow-xs">
                <div class="w-10 h-10 rounded-full bg-amber-700 text-white font-serif font-black text-lg flex items-center justify-center mx-auto mb-3 sm:mb-4">3</div>
                <span class="text-xs font-mono font-bold tracking-widest text-slate-500 uppercase block mb-1">THIRD PLACE</span>
                <h3 class="text-lg sm:text-xl font-serif font-bold text-slate-900 mb-2">
                    {{ $result->thirdEntry?->student?->name ?? 'Team ' . $result->thirdEntry?->group?->name }}
                </h3>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-xs font-mono text-slate-700 mb-2">
                    <span>Chest #{{ $result->thirdEntry?->chest_number }}</span>
                    <span>•</span>
                    <span class="font-bold">{{ $result->thirdEntry?->group?->name }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Complete Participant Roster & Scores (Light Theme) -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-xs mb-8 sm:mb-12">
        <div class="px-5 sm:px-6 py-3.5 sm:py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
            <h3 class="font-serif font-bold text-base sm:text-lg text-slate-900">All Contestants</h3>
            <span class="text-xs font-mono text-slate-500">{{ $program->entries->count() }} Registered</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm min-w-[500px]">
                <thead class="bg-slate-100 text-slate-600 font-mono text-xs uppercase">
                    <tr>
                        <th class="px-5 sm:px-6 py-3">Chest #</th>
                        <th class="px-5 sm:px-6 py-3">Participant / Team</th>
                        <th class="px-5 sm:px-6 py-3">House</th>
                        <th class="px-5 sm:px-6 py-3">Evaluation Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @foreach($program->entries as $entry)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 sm:px-6 py-3.5 sm:py-4 font-mono font-bold text-[#f3bd2e]">{{ $entry->chest_number }}</td>
                            <td class="px-5 sm:px-6 py-3.5 sm:py-4 font-medium text-slate-900">
                                {{ $entry->student?->name ?? 'Team ' . $entry->group?->name }}
                            </td>
                            <td class="px-5 sm:px-6 py-3.5 sm:py-4">
                                <span class="px-2 py-0.5 rounded text-xs font-mono font-semibold" style="background-color: {{ $entry->group->color_hex }}20; color: {{ $entry->group->color_hex }}">
                                    {{ $entry->group->name }}
                                </span>
                            </td>
                            <td class="px-5 sm:px-6 py-3.5 sm:py-4 font-mono text-xs text-slate-600">
                                @if($entry->id === $result->first_entry_id)
                                    <span class="px-2.5 py-1 rounded bg-amber-100 text-[#be1e2d] font-bold">1st Place</span>
                                @elseif($entry->id === $result->second_entry_id)
                                    <span class="px-2.5 py-1 rounded bg-slate-200 text-slate-800 font-bold">2nd Place</span>
                                @elseif($entry->id === $result->third_entry_id)
                                    <span class="px-2.5 py-1 rounded bg-amber-50 text-amber-800 font-bold">3rd Place</span>
                                @else
                                    <span class="text-slate-400">Participant</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
