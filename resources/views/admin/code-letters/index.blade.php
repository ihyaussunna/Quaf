@extends('layouts.admin')

@section('title', 'Code Letters Handler - QUAF Fest')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Code Letters Handler</h1>
            <p class="text-sm text-gray-500 mt-1">Assign and manage secret code letters for participants</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleAssignMode(false)" id="btnViewMode" class="px-4 py-2 bg-brand-orange text-white rounded-xl text-sm font-semibold shadow-sm hover:bg-orange-600 transition">
                View Code Letters
            </button>
            <button type="button" onclick="toggleAssignMode(true)" id="btnEditMode" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 transition">
                Assign / Edit Code Letters
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs">
        <form method="GET" action="{{ route('admin.code-letters.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Zone</label>
                <select name="zone" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                    <option value="">-- All Zones --</option>
                    @foreach($zones as $zKey => $zVal)
                        @php
                            $zoneName = is_object($zVal) ? $zVal->name : (is_string($zVal) ? $zVal : $zKey);
                            $zoneValue = is_object($zVal) ? $zVal->name : (is_string($zKey) && !is_numeric($zKey) ? $zKey : $zVal);
                        @endphp
                        <option value="{{ $zoneValue }}" {{ ($selectedZone ?? '') == $zoneValue ? 'selected' : '' }}>{{ $zoneName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-5">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Program</label>
                <select name="program" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                    <option value="">-- Select Program --</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ (string)$selectedProgramId === (string)$prog->id ? 'selected' : '' }}>{{ $prog->name }} (ID: {{ $prog->code ?: $prog->id }})</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3">
                <button type="submit" class="w-full bg-brand-orange text-white py-2.5 px-4 rounded-xl text-sm font-semibold hover:bg-orange-600 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    View Code Letters
                </button>
            </div>
        </form>
    </div>

    @if($selectedProgram)
        <!-- Program Details Banner -->
        <div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-2xl border border-orange-100 p-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-brand-orange/10 text-brand-orange flex items-center justify-center font-bold text-sm">
                    {{ $selectedProgram->code ?: '#'.$selectedProgram->id }}
                </span>
                <div>
                    <h3 class="font-bold text-gray-900">{{ $selectedProgram->name }}</h3>
                    <p class="text-xs text-gray-500">{{ $selectedProgram->eligibility ?? 'A Zone' }} • {{ $selectedProgram->is_stage ? 'Stage' : 'Non-stage' }} • {{ ucfirst($selectedProgram->type ?? 'Individual') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.code-letters.auto-assign', $selectedProgram->id) }}" onsubmit="return confirm('Randomly shuffle and assign code letters A-Z?')">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 bg-[#005c94] text-white rounded-xl text-xs font-semibold hover:bg-[#004b78] transition flex items-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Auto Shuffle (A-Z)
                    </button>
                </form>
                <a href="{{ route('admin.forms.call-list', ['program_id' => $selectedProgram->id]) }}" target="_blank" class="px-3.5 py-2 bg-gray-100 text-gray-700 rounded-xl text-xs font-semibold hover:bg-gray-200 transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Call List
                </a>
            </div>
        </div>

        <!-- View Mode Table -->
        <div id="viewModeCard" class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50/75 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-5 py-3.5">Student ID</th>
                            <th class="px-5 py-3.5">Student Name</th>
                            <th class="px-5 py-3.5">Student Class</th>
                            <th class="px-5 py-3.5">Student Team</th>
                            <th class="px-5 py-3.5 text-center">Code Letter</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($entries as $entry)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-5 py-3.5 font-medium text-gray-900">
                                    #{{ $entry->student?->student_id ?: $entry->chest_number }}
                                </td>
                                <td class="px-5 py-3.5 font-medium text-gray-900">
                                    {{ $entry->student?->name ?: 'Chest #'.$entry->chest_number }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">
                                    {{ $entry->student?->class_level ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">
                                    {{ $entry->group?->name ?? $entry->student?->group?->name ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if($entry->code_letter)
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-700 font-bold text-sm border border-blue-200">
                                            {{ $entry->code_letter }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Not assigned</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-gray-400">
                                    No participants registered yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit Mode Form -->
        <div id="editModeCard" class="hidden bg-white rounded-2xl border border-gray-100 shadow-xs p-6">
            <form method="POST" action="{{ route('admin.code-letters.save', $selectedProgram->id) }}">
                @csrf
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900">Assign / Edit Code Letters Manually</h3>
                        <p class="text-xs text-gray-500">Enter code letters (e.g., A, B, C...) for each participant</p>
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-brand-orange text-white rounded-xl text-sm font-semibold hover:bg-orange-600 transition shadow-sm">
                        Save Code Letters
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($entries as $entry)
                        <div class="border border-gray-200 rounded-xl p-3 flex items-center justify-between bg-gray-50/50">
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $entry->student?->name ?: 'Chest #'.$entry->chest_number }}</p>
                                <p class="text-xs text-gray-500">ID: #{{ $entry->student?->student_id ?: $entry->chest_number }} • {{ $entry->group?->name ?? $entry->student?->group?->name ?? 'Team' }}</p>
                            </div>
                            <div class="w-16">
                                <input type="text" name="letters[{{ $entry->id }}]" value="{{ $entry->code_letter }}" maxlength="5" class="w-full text-center uppercase font-bold text-lg bg-white border border-gray-300 rounded-lg py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-brand-orange text-white rounded-xl text-sm font-semibold hover:bg-orange-600 transition shadow-sm">
                        Save Code Letters
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-red-50 text-brand-orange mx-auto flex items-center justify-center mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
            <h3 class="text-base font-bold text-gray-900">Select a zone and program to list students</h3>
            <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Please select a zone and program from the dropdown above.</p>
        </div>
    @endif
</div>

<script>
function toggleAssignMode(isEdit) {
    const viewCard = document.getElementById('viewModeCard');
    const editCard = document.getElementById('editModeCard');
    const btnView = document.getElementById('btnViewMode');
    const btnEdit = document.getElementById('btnEditMode');

    if (!viewCard || !editCard) return;

    if (isEdit) {
        viewCard.classList.add('hidden');
        editCard.classList.remove('hidden');
        btnEdit.className = 'px-4 py-2 bg-brand-orange text-white rounded-xl text-sm font-semibold shadow-sm hover:bg-orange-600 transition';
        btnView.className = 'px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 transition';
    } else {
        viewCard.classList.remove('hidden');
        editCard.classList.add('hidden');
        btnView.className = 'px-4 py-2 bg-brand-orange text-white rounded-xl text-sm font-semibold shadow-sm hover:bg-orange-600 transition';
        btnEdit.className = 'px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 transition';
    }
}
</script>
@endsection
