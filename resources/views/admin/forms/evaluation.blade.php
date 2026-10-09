@extends('layouts.admin', ['title' => 'Judge Evaluation Sheet | QUAF'])

@section('content')
<div class="space-y-6">
    <!-- Screen Header (Hidden on Print) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Judge Evaluation Sheet</h1>
            <p class="text-xs text-slate-500 mt-0.5">Printable judging evaluation form with strict code letter anonymity.</p>
        </div>
        <div class="flex items-center gap-2.5">
            @if($selectedProgram)
                <a href="{{ route('admin.forms.evaluation', ['program' => $selectedProgram->id, 'print' => 1]) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-800 text-white font-bold text-xs uppercase tracking-wider hover:bg-slate-700 transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    <span>Open Print View</span>
                </a>
                <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-[#be1e2d] text-white font-bold text-xs uppercase tracking-wider hover:bg-[#a01624] transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Print Evaluation Sheet</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Filter Form (Hidden on Print) -->
    <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs print:hidden">
        <form method="GET" action="{{ route('admin.forms.evaluation') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Select Zone</label>
                <select name="zone" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($zones as $val => $label)
                        <option value="{{ $val }}" {{ ($selectedZone ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Select Program</label>
                <select name="program" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">-- Choose Competition Program --</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ $selectedProgramId == $prog->id ? 'selected' : '' }}>
                            {{ $prog->name }} — ID: {{ $prog->code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-[#be1e2d] hover:bg-[#a01624] text-white font-bold text-xs uppercase tracking-wider transition-colors shadow-sm">
                    Generate Form
                </button>
            </div>
        </form>
    </div>

    <!-- Printable Official Sheet (Formatted exactly as previous year Quaf model) -->
    @if($selectedProgram)
        <div class="evaluation-paper bg-white p-6 sm:p-10 rounded-2xl border border-slate-200 shadow-sm mx-auto print:p-0 print:border-none print:shadow-none print:m-0 print:w-full">
            <!-- Official Centered Logo with Tagline -->
            <div class="text-center mb-2">
                <img src="{{ asset('images/forms-header-logo.svg') }}" alt="QUAF" class="mx-auto header-logo" style="height: 48px; max-height: 48px; width: auto; max-width: 180px; object-fit: contain; display: block; margin: 0 auto;">
            </div>

            <!-- Title -->
            <h2 class="text-center font-bold text-base sm:text-lg tracking-wider mb-3 text-black">
                Evaluation Sheet
            </h2>

            <!-- Program Info Bar (Single Line) -->
            <div class="flex items-center justify-between text-xs sm:text-sm font-bold text-black mb-2 px-0.5">
                <div>Id: {{ $selectedProgram->code ?? $selectedProgram->id }}</div>
                <div>Program: {{ $selectedProgram->name }}</div>
                <div>Type: {{ ucfirst($selectedProgram->type) }}</div>
                <div>Zone: {{ strtoupper($selectedProgram->eligibility ?? 'ZONE A') }}</div>
            </div>

            <!-- Table (Code Letter, Criteria columns, Out of 100) -->
            <div class="w-full overflow-x-auto print:overflow-visible">
                <table class="w-full text-black border-collapse border border-black text-xs font-sora">
                    <thead>
                        <tr class="h-10 bg-white">
                            <th class="border border-black px-2 py-1.5 w-[14%] text-center font-bold">Code Letter</th>
                            @php
                                $criteria = $selectedProgram->scoringCriteria ?? collect();
                            @endphp
                            @if($criteria->count() > 0)
                                @foreach($criteria as $crit)
                                    <th class="border border-black px-2 py-1.5 text-center font-bold">
                                        {{ $crit->criterion_name }}
                                        @if($crit->max_marks)
                                            <span class="block text-[10px] font-normal">({{ $crit->max_marks }})</span>
                                        @endif
                                    </th>
                                @endforeach
                                @for($c = $criteria->count(); $c < 4; $c++)
                                    <th class="border border-black px-2 py-1.5 w-[14%] text-center"></th>
                                @endfor
                            @else
                                <th class="border border-black px-2 py-1.5 w-[14%] text-center"></th>
                                <th class="border border-black px-2 py-1.5 w-[14%] text-center"></th>
                                <th class="border border-black px-2 py-1.5 w-[14%] text-center"></th>
                                <th class="border border-black px-2 py-1.5 w-[14%] text-center"></th>
                            @endif
                            <th class="border border-black px-2 py-1.5 w-[16%] text-center font-bold">Out of 100</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $assignedCodes = $selectedProgram->entries
                                ->where('status', 'verified')
                                ->filter(function($entry) use ($selectedProgram) {
                                    if ($selectedProgram->is_call_list_locked) {
                                        return $entry->attendance_status === 'present' && !empty($entry->code_letter);
                                    }
                                    return $entry->attendance_status !== 'absent';
                                })
                                ->pluck('code_letter')
                                ->filter()
                                ->sort()
                                ->values()
                                ->toArray();

                            $codeLetters = !empty($assignedCodes) ? $assignedCodes : ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
                        @endphp
                        @foreach($codeLetters as $letter)
                            <tr class="h-11">
                                <td class="border border-black text-center font-bold text-sm">{{ $letter }}</td>
                                @if($criteria->count() > 0)
                                    @foreach($criteria as $crit)
                                        <td class="border border-black"></td>
                                    @endforeach
                                    @for($c = $criteria->count(); $c < 4; $c++)
                                        <td class="border border-black"></td>
                                    @endfor
                                @else
                                    <td class="border border-black"></td>
                                    <td class="border border-black"></td>
                                    <td class="border border-black"></td>
                                    <td class="border border-black"></td>
                                @endif
                                <td class="border border-black"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($selectedProgramId)
        <div class="p-8 text-center bg-white rounded-2xl border border-slate-200 text-slate-400 text-xs print:hidden">
            Program not found.
        </div>
    @else
        <div class="p-12 text-center bg-white rounded-2xl border border-slate-200 text-slate-400 text-xs print:hidden">
            Please select a competition program above to generate the Judge Evaluation Sheet.
        </div>
    @endif
</div>

<style>
@page {
    size: A4 portrait;
    margin: 8mm 12mm;
}
@media print {
    body, html {
        background: #ffffff !important;
        color: #000000 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    aside, header, #sidebar, .print\:hidden, [x-show="settingsDrawerOpen"], button, nav {
        display: none !important;
    }
    .evaluation-paper {
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        box-shadow: none !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    table {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 1.5px solid #000000 !important;
    }
    th, td {
        border: 1.5px solid #000000 !important;
        color: #000000 !important;
    }
    .evaluation-paper img,
    .evaluation-paper .header-logo {
        height: 48px !important;
        max-height: 48px !important;
        width: auto !important;
        max-width: 180px !important;
        display: block !important;
        margin: 0 auto 6px auto !important;
        object-fit: contain !important;
    }
}
.evaluation-paper img,
.evaluation-paper .header-logo {
    height: 48px !important;
    max-height: 48px !important;
    width: auto !important;
    max-width: 180px !important;
    display: block !important;
    margin: 0 auto 8px auto !important;
    object-fit: contain !important;
}
</style>
@endsection
