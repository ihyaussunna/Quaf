@extends('layouts.admin', ['title' => 'Stage Call List | QUAF'])

@section('content')
<div class="space-y-6">
    <!-- Screen Header (Hidden on Print) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Stage Call List</h1>
            <p class="text-xs text-slate-500 mt-0.5">Official stage call sheet with code letters and participant signature verification.</p>
        </div>
        <div class="flex items-center gap-2.5">
            @if($selectedProgram)
                <a href="{{ route('greenroom.call-list', ['program' => $selectedProgram->id]) }}" class="px-4 py-2 rounded-xl bg-[#005c94] text-white font-bold text-xs uppercase tracking-wider hover:bg-[#004b78] transition-colors shadow-sm flex items-center gap-1.5 font-sora">
                    <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>ഡിജിറ്റൽ കോൾ ലിസ്റ്റ് (Interactive)</span>
                </a>
                <a href="{{ route('admin.forms.call-list', ['program' => $selectedProgram->id, 'print' => 1]) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-800 text-white font-bold text-xs uppercase tracking-wider hover:bg-slate-700 transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    <span>Open Print View</span>
                </a>
                <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-[#be1e2d] text-white font-bold text-xs uppercase tracking-wider hover:bg-[#a01624] transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Print Call Sheet</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Filter Form (Hidden on Print) -->
    <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs print:hidden">
        <form method="GET" action="{{ route('admin.forms.call-list') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
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
                    Generate Call List
                </button>
            </div>
        </form>
    </div>

    <!-- Printable Official Sheet (Formatted exactly as previous year Quaf model) -->
    @if($selectedProgram)
        <div class="call-list-paper bg-white p-6 sm:p-10 rounded-2xl border border-slate-200 shadow-sm mx-auto print:p-0 print:border-none print:shadow-none print:m-0 print:w-full">
            <!-- Official Centered Logo with Tagline -->
            <div class="text-center mb-2">
                <img src="{{ asset('images/forms-header-logo.svg') }}" alt="QUAF" class="mx-auto header-logo" style="height: 48px; max-height: 48px; width: auto; max-width: 180px; object-fit: contain; display: block; margin: 0 auto;">
            </div>

            <!-- Title -->
            <h2 class="text-center font-bold text-base sm:text-lg tracking-wider uppercase mb-3 text-black">
                CALL LIST
            </h2>

            <!-- Program Info Bar (Single Line) -->
            <div class="flex items-center justify-between text-xs sm:text-sm font-bold text-black mb-2 px-0.5">
                <div>Id: {{ $selectedProgram->code ?? $selectedProgram->id }}</div>
                <div>Program: {{ $selectedProgram->name }}</div>
                <div>Type: {{ ucfirst($selectedProgram->type) }}</div>
                <div>Zone: {{ strtoupper($selectedProgram->eligibility ?? 'ZONE A') }}</div>
            </div>

            <!-- Table (Exact matching columns & styling) -->
            <div class="w-full overflow-x-auto print:overflow-visible">
                <table class="w-full text-black border-collapse border border-black text-xs font-sora">
                    <thead>
                        <tr class="h-10 bg-white">
                            <th class="border border-black px-2 py-1.5 w-[6%] text-center font-bold">No</th>
                            <th class="border border-black px-2 py-1.5 w-[14%] text-center font-bold">Student Id</th>
                            <th class="border border-black px-3 py-1.5 w-[36%] text-left font-bold">Name</th>
                            <th class="border border-black px-2 py-1.5 w-[18%] text-center font-bold">Team</th>
                            <th class="border border-black px-2 py-1.5 w-[13%] text-center font-bold">Code Letter</th>
                            <th class="border border-black px-2 py-1.5 w-[13%] text-center font-bold">Sign</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $entries = $selectedProgram->entries;
                            $totalRows = max(count($entries), 10);
                        @endphp
                        @for($i = 0; $i < $totalRows; $i++)
                            @php
                                $entry = $entries[$i] ?? null;
                                $studentId = '';
                                $studentName = '';
                                $teamName = '';
                                if ($entry) {
                                    $studentId = $entry->student?->chest_number ?? $entry->student?->student_id ?? $entry->chest_number ?? '';
                                    $studentName = $entry->student?->name ?? $entry->group?->name ?? '';
                                    $teamName = $entry->student?->group?->name ?? $entry->group?->name ?? '';
                                }
                            @endphp
                            <tr class="h-11">
                                <td class="border border-black text-center font-semibold">{{ $i + 1 }}</td>
                                <td class="border border-black text-center font-mono font-medium">{{ $studentId }}</td>
                                <td class="border border-black text-left px-3 font-medium capitalize">{{ $studentName }}</td>
                                <td class="border border-black text-center">{{ $teamName }}</td>
                                <td class="border border-black text-center font-bold text-sm">{{ $entry?->code_letter ?? '' }}</td>
                                <td class="border border-black text-center"></td>
                            </tr>
                        @endfor
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
            Please select a competition program above to generate the official Call Sheet.
        </div>
    @endif
</div>

<style>
@page {
    size: landscape;
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
    .call-list-paper {
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
    .call-list-paper img,
    .call-list-paper .header-logo {
        height: 48px !important;
        max-height: 48px !important;
        width: auto !important;
        max-width: 180px !important;
        display: block !important;
        margin: 0 auto 6px auto !important;
        object-fit: contain !important;
    }
}
.call-list-paper img,
.call-list-paper .header-logo {
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
