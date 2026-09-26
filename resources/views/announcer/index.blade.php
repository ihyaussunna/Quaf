@extends('layouts.app')

@section('title', 'Announcer Desk - QUAF Fest')

@section('content')
<div class="min-h-screen bg-[#edf3f8] py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto space-y-8">
        <!-- Title Banner matching screenshot -->
        <div class="text-center">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Program Results</h1>
            <p class="text-xs text-gray-500 mt-1">Live stage results broadcast console for announcers</p>
        </div>

        <!-- Flash messages -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-xs">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($results->isEmpty())
            <!-- Empty state matching screenshot -->
            <div class="bg-white rounded-3xl p-16 text-center border border-gray-100 shadow-sm max-w-3xl mx-auto">
                <p class="text-base text-gray-700 font-medium">
                    No programs available for announcement.
                </p>
            </div>
        @else
            <!-- Results list -->
            <div class="space-y-5">
                @foreach($results as $res)
                    @php
                        $isDelivered = in_array($res->status, ['send', 'delivered']);
                    @endphp
                    <div class="bg-white rounded-3xl p-6 sm:p-8 border {{ $isDelivered ? 'border-amber-300 ring-2 ring-amber-100 shadow-md' : 'border-gray-100 shadow-sm' }} transition">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-5">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $isDelivered ? 'bg-amber-100 text-amber-800 animate-pulse' : 'bg-blue-50 text-[#005c94]' }}">
                                        {{ $isDelivered ? 'READY FOR ANNOUNCEMENT' : 'ANNOUNCED' }}
                                    </span>
                                    <span class="text-xs text-gray-500 font-semibold">{{ $res->program?->eligibility ?? 'A Zone' }} • {{ $res->program?->is_stage ? 'Stage' : 'Non-stage' }}</span>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 capitalize">{{ $res->program?->name }}</h3>
                                <p class="text-xs text-gray-400 font-mono mt-0.5">Program ID: {{ $res->program?->code ?: $res->program?->id }}</p>
                            </div>

                            @if($isDelivered)
                                <form method="POST" action="{{ route('announcer.announced', $res->id) }}">
                                    @csrf
                                    <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-brand-orange text-white rounded-2xl text-sm font-bold hover:bg-orange-600 transition shadow-sm flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                        Mark as Announced
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-gray-100 text-gray-600">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Announced on Stage
                                </span>
                            @endif
                        </div>

                        <!-- Winners Podium Details -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-5">
                            <!-- 1st Place -->
                            <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-amber-800">1st Place</span>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded bg-amber-200 text-amber-900">Rank 1</span>
                                </div>
                                <div class="font-bold text-gray-900 text-base capitalize">
                                    {{ $res->firstEntry?->student?->name ?: ($res->firstEntry ? 'Chest #'.$res->firstEntry->chest_number : 'None') }}
                                </div>
                                <div class="text-xs text-gray-600 mt-1">
                                    Chest: #{{ $res->firstEntry?->chest_number ?? '-' }} • Team: <strong class="text-gray-800">{{ $res->firstEntry?->group?->name ?? $res->firstEntry?->student?->group?->name ?? '-' }}</strong>
                                </div>
                            </div>

                            <!-- 2nd Place -->
                            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-gray-700">2nd Place</span>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded bg-gray-200 text-gray-800">Rank 2</span>
                                </div>
                                <div class="font-bold text-gray-900 text-base capitalize">
                                    {{ $res->secondEntry?->student?->name ?: ($res->secondEntry ? 'Chest #'.$res->secondEntry->chest_number : 'None') }}
                                </div>
                                <div class="text-xs text-gray-600 mt-1">
                                    Chest: #{{ $res->secondEntry?->chest_number ?? '-' }} • Team: <strong class="text-gray-800">{{ $res->secondEntry?->group?->name ?? $res->secondEntry?->student?->group?->name ?? '-' }}</strong>
                                </div>
                            </div>

                            <!-- 3rd Place -->
                            <div class="p-4 rounded-2xl bg-red-50/80 border border-red-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-[#be1e2d]">3rd Place</span>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded bg-red-100 text-[#be1e2d]">Rank 3</span>
                                </div>
                                <div class="font-bold text-gray-900 text-base capitalize">
                                    {{ $res->thirdEntry?->student?->name ?: ($res->thirdEntry ? 'Chest #'.$res->thirdEntry->chest_number : 'None') }}
                                </div>
                                <div class="text-xs text-gray-600 mt-1">
                                    Chest: #{{ $res->thirdEntry?->chest_number ?? '-' }} • Team: <strong class="text-gray-800">{{ $res->thirdEntry?->group?->name ?? $res->thirdEntry?->student?->group?->name ?? '-' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
