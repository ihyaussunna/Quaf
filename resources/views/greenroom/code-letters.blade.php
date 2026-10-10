@extends('layouts.app')

@section('title', 'View Code Letters - Green Room')

@section('content')
<div class="min-h-screen bg-[#f4f7fc] py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto space-y-6">
        <!-- Top Header matching screenshot -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">View Code Letters</h1>
                <p class="text-sm text-gray-500 mt-1">View assigned code letters for students in a selected program</p>
            </div>
        </div>

        <!-- Filter Card matching screenshot -->
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <form method="GET" action="{{ route('greenroom.code-letters') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Zone</label>
                    <select name="zone" onchange="this.form.submit()" class="w-full bg-slate-50 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5b4df2]/20">
                        <option value="">-- All Zones --</option>
                        @foreach($zones as $zKey => $zVal)
                            @php
                                $zoneName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                                $zoneValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                            @endphp
                            <option value="{{ $zoneValue }}" {{ ($selectedZone ?? '') === $zoneValue ? 'selected' : '' }}>{{ $zoneName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-6">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Program</label>
                    <select name="program" class="w-full bg-slate-50 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5b4df2]/20">
                        <option value="">-- Select Program --</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" {{ (string)$selectedProgramId === (string)$prog->id ? 'selected' : '' }}>{{ $prog->name }} — ID: {{ $prog->code ?: $prog->id }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <button type="submit" class="w-full bg-[#5243e8] hover:bg-[#4335cf] text-white py-2.5 px-4 rounded-xl text-sm font-bold transition shadow-sm shadow-[#5243e8]/20 flex items-center justify-center gap-2 cursor-pointer">
                        View Code Letters
                    </button>
                </div>
            </form>
        </div>

        @if($selectedProgram)
            <!-- Table Card matching screenshot -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-bold text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-4">STUDENT</th>
                                <th class="px-6 py-4">ID</th>
                                <th class="px-6 py-4">CLASS</th>
                                <th class="px-6 py-4">CODE</th>
                                <th class="px-6 py-4 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($entries as $entry)
                                @php
                                    $studentName = $entry->student?->name ?: 'Chest #'.$entry->chest_number;
                                    $initials = collect(explode(' ', $studentName))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    <!-- Student avatar with initials + name -->
                                    <td class="px-6 py-4 flex items-center gap-3.5">
                                        <div class="w-10 h-10 rounded-full bg-blue-50 text-[#005c94] font-bold text-xs flex items-center justify-center flex-shrink-0">
                                            {{ $initials ?: 'ST' }}
                                        </div>
                                        <span class="font-bold text-gray-900 capitalize text-sm">{{ $studentName }}</span>
                                    </td>
                                    <!-- Student ID -->
                                    <td class="px-6 py-4 text-gray-600 font-medium text-sm">
                                        #{{ $entry->student?->student_id ?: $entry->chest_number }}
                                    </td>
                                    <!-- Class -->
                                    <td class="px-6 py-4 text-gray-600 font-medium text-sm">
                                        {{ $entry->student?->class_level ?? '-' }}
                                    </td>
                                    <!-- Team Name (Code in screenshot) -->
                                    <td class="px-6 py-4 text-gray-600 font-medium text-sm">
                                        {{ $entry->group?->name ?? $entry->student?->group?->name ?? '-' }}
                                    </td>
                                    <!-- Code letter green badge matching screenshot -->
                                    <td class="px-6 py-4 text-center">
                                        @if($entry->code_letter)
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-50 text-emerald-700 font-extrabold text-sm shadow-xs">
                                                {{ $entry->code_letter }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                        No participants registered for this program.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
