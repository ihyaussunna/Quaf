<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $mode === 'program_wise' ? 'Program-Wise' : 'Group-Wise' }} Entries Registry — QUAF</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
                padding: 6mm !important;
                margin: 0 !important;
            }
            .entry-group-block, .avoid-break, tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            thead {
                display: table-header-group;
            }
        }
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sora antialiased min-h-screen py-6 px-3 sm:px-6"
      x-data="{
          mode: '{{ $mode }}',
          colChestNo: true,
          colMalName: true,
          colZone: true,
          colType: true,
          colStatus: true,
          colClass: true,
          colSign: true,
          showSignatures: true,
          printReport() {
              window.print();
          }
      }">

    <!-- Top Customization Control Bar (Hidden on Print) -->
    <div class="no-print max-w-7xl mx-auto mb-6 bg-white border border-slate-300 rounded-2xl p-4 sm:p-5 shadow-lg space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-[#be1e2d] text-white">PRINT & PDF EXPORT</span>
                    <h1 class="text-lg font-bold text-slate-900">
                        {{ $mode === 'program_wise' ? 'Program-Wise' : 'Group-Wise' }} Entries Roster
                    </h1>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Filter entries, toggle columns, switch between Group-Wise and Program-Wise, and export to PDF</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2.5">
                @if(request()->routeIs('program-committee.*'))
                    <a href="{{ route('program-committee.team-entries.index') }}" class="px-4 py-2 rounded-xl text-xs font-mono font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition-colors">
                        Back to Team Entries
                    </a>
                @else
                    <a href="{{ route('admin.exports.index') }}" class="px-4 py-2 rounded-xl text-xs font-mono font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition-colors">
                        Back to Exports
                    </a>
                @endif
                <a href="{{ route('admin.exports.download', 'entries') }}" class="px-4 py-2 rounded-xl text-xs font-mono font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                    Download CSV
                </a>
                <button @click="printReport()" class="px-5 py-2.5 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#be1e2d] hover:bg-[#a01624] text-white flex items-center gap-2 shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print / Save PDF</span>
                </button>
            </div>
        </div>

        <!-- Mode Switcher Tab Buttons -->
        <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-xl w-fit border border-slate-200">
            <a href="{{ request()->fullUrlWithQuery(['mode' => 'group_wise']) }}"
               class="px-4 py-1.5 rounded-lg text-xs font-mono font-bold transition-all {{ $mode === 'group_wise' ? 'bg-[#be1e2d] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Group-Wise Entries
            </a>
            <a href="{{ request()->fullUrlWithQuery(['mode' => 'program_wise']) }}"
               class="px-4 py-1.5 rounded-lg text-xs font-mono font-bold transition-all {{ $mode === 'program_wise' ? 'bg-[#be1e2d] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Program-Wise Entries
            </a>
        </div>

        <!-- Filter Selectors -->
        <form method="GET" action="{{ url()->current() }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3">
            <input type="hidden" name="mode" value="{{ $mode }}">

            <!-- Group Filter -->
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Filter by Group</label>
                <select name="group" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Groups</option>
                    @foreach($groups as $grp)
                        <option value="{{ $grp->id }}" {{ $selectedGroupId == $grp->id ? 'selected' : '' }}>
                            {{ $grp->name }} ({{ $grp->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Program Filter -->
            <div class="sm:col-span-2">
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Filter by Program</label>
                <select name="program" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Programs</option>
                    @foreach($allPrograms as $prg)
                        <option value="{{ $prg->id }}" {{ $selectedProgramId == $prg->id ? 'selected' : '' }}>
                            {{ $prg->code }} - {{ $prg->name }} ({{ ucfirst($prg->type) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Zone Filter -->
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Zone</label>
                <select name="zone" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($zones as $val => $label)
                        <option value="{{ $val }}" {{ $selectedZone == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Type Filter -->
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Type</label>
                <select name="type" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Types</option>
                    <option value="individual" {{ $selectedType === 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="group" {{ $selectedType === 'group' ? 'selected' : '' }}>Group</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Statuses</option>
                    <option value="verified" {{ $selectedStatus === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ $selectedStatus === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                </select>
            </div>

            <!-- Search Filter -->
            <div class="sm:col-span-4">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by student name, ID, chest number, or program..."
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-mono text-xs font-bold transition">
                    Filter
                </button>
                @if($selectedGroupId || $selectedProgramId || $selectedZone || $selectedType || $selectedStatus || $search)
                    <a href="{{ url()->current() }}?mode={{ $mode }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-mono text-xs font-bold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- Column Toggles (Interactive Checkboxes) -->
        <div class="pt-3 border-t border-slate-200 flex flex-wrap items-center gap-4 text-xs font-mono text-slate-700">
            <span class="font-bold text-slate-400 uppercase text-[11px]">Toggle Columns:</span>
            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colChestNo" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Chest Number</span>
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colMalName" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Malayalam Name</span>
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colZone" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Zone</span>
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colType" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Type Badge</span>
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colClass" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Class Level</span>
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colStatus" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Status Badge</span>
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colSign" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Signature / Remarks</span>
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="showSignatures" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Authority Seals</span>
            </label>
        </div>
    </div>

    <!-- Printable Official Document Container -->
    <div class="print-container max-w-7xl mx-auto bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xl text-slate-900">
        
        <!-- Official Document Header -->
        <div class="flex items-center justify-between pb-4 border-b-2 border-slate-900 gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF Logo" class="h-14 w-auto object-contain">
                <div>
                    <h1 class="text-xl font-black font-sora text-slate-900 uppercase tracking-tight">
                        {{ $mode === 'program_wise' ? 'Official Program-Wise Entries Registry' : 'Official Group-Wise Entries Registry' }}
                    </h1>
                    <p class="text-xs font-semibold text-slate-600">Ihyaussunna Students Union, Markazu Saquafathi Sunniyya</p>
                </div>
            </div>

            <div class="text-right font-mono text-[11px] text-slate-500 space-y-0.5">
                <div class="font-bold text-slate-900 text-xs">
                    {{ $mode === 'program_wise' ? 'PROGRAM ENTRIES' : 'GROUP ENTRIES' }}
                </div>
                <div>Date: {{ now()->format('d M Y, h:i A') }}</div>
                <div>Total Entries: <span class="font-bold text-slate-900">{{ $totalEntries }}</span></div>
                @if($selectedZone)
                    <div class="text-[#be1e2d] font-bold">Zone: {{ $selectedZone }}</div>
                @endif
                @if($selectedType)
                    <div class="text-purple-700 font-bold">Type: {{ ucfirst($selectedType) }}</div>
                @endif
            </div>
        </div>

        <!-- Quick KPI Stats Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 py-3 border-b border-slate-200 text-xs font-mono">
            <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block text-[10px] uppercase font-bold">Total Entries</span>
                <span class="text-base font-bold text-slate-900">{{ $totalEntries }}</span>
            </div>
            <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200">
                <span class="text-slate-500 block text-[10px] uppercase font-bold">Groups Represented</span>
                <span class="text-base font-bold text-slate-900">{{ $groupedData->count() }} {{ $mode === 'group_wise' ? 'Groups' : 'Programs' }}</span>
            </div>
            <div class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200">
                <span class="text-emerald-700 block text-[10px] uppercase font-bold">Verified Entries</span>
                <span class="text-base font-bold text-emerald-800">{{ $verifiedCount }}</span>
            </div>
            <div class="p-2.5 rounded-lg bg-amber-50 border border-amber-200">
                <span class="text-amber-700 block text-[10px] uppercase font-bold">Pending Entries</span>
                <span class="text-base font-bold text-amber-800">{{ $pendingCount }}</span>
            </div>
        </div>

        <!-- Entries Content Body -->
        <div class="mt-6 space-y-8">
            @if($groupedData->isEmpty())
                <div class="p-8 text-center border border-dashed border-slate-300 rounded-xl text-xs text-slate-500 font-mono">
                    No registered entries match the selected filter criteria.
                </div>
            @elseif($mode === 'group_wise')
                <!-- GROUP-WISE LAYOUT -->
                @foreach($groupedData as $groupId => $groupEntries)
                    @php
                        $group = $groups->firstWhere('id', $groupId) ?? $groupEntries->first()?->group;
                    @endphp
                    <div class="entry-group-block avoid-break border border-slate-300 rounded-xl overflow-hidden">
                        <!-- Group Header Banner -->
                        <div class="bg-slate-900 text-white px-4 py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full inline-block" style="background-color: {{ $group->color_hex ?? '#be1e2d' }}"></span>
                                <h2 class="text-sm font-bold font-sora tracking-wide uppercase">
                                    {{ $group->name ?? 'Unassigned Group' }}
                                </h2>
                                @if($group?->code)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-white/20 text-white">
                                        {{ $group->code }}
                                    </span>
                                @endif
                                @if($group?->manager_name)
                                    <span class="text-[11px] text-slate-300 font-normal">
                                        Leader: {{ $group->manager_name }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs font-mono text-amber-400 font-bold">
                                {{ $groupEntries->count() }} Entries
                            </div>
                        </div>

                        <!-- Group Entries Table -->
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-100 font-mono font-bold text-slate-700 uppercase border-b border-slate-300 text-[11px]">
                                <tr>
                                    <th class="py-2 px-2 text-center w-8">#</th>
                                    <th class="py-2 px-3">Competition Program</th>
                                    <th x-show="colType" class="py-2 px-2.5 text-center w-24">Type</th>
                                    <th x-show="colZone" class="py-2 px-2.5 w-24">Zone</th>
                                    <th x-show="colChestNo" class="py-2 px-2.5 w-24 font-mono">Chest No</th>
                                    <th class="py-2 px-3">Candidate / Team Participants</th>
                                    <th x-show="colStatus" class="py-2 px-2.5 text-center w-24">Status</th>
                                    <th x-show="colSign" class="py-2 px-3 text-center w-28">Sign / Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($groupEntries as $idx => $entry)
                                    @php
                                        $prog = $entry->program;
                                        $isGroupProg = ($prog?->type ?? 'individual') === 'group';
                                    @endphp
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2 px-2 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                                        <td class="py-2 px-3">
                                            <span class="font-bold text-slate-900 block">
                                                <span class="font-mono text-slate-600 mr-1">[{{ $prog?->code }}]</span>{{ $prog?->name }}
                                            </span>
                                            @if($prog?->malayalam_name)
                                                <span x-show="colMalName" class="text-[11px] text-slate-500 font-malayalam block mt-0.5">{{ $prog->malayalam_name }}</span>
                                            @endif
                                        </td>
                                        <td x-show="colType" class="py-2 px-2.5 text-center whitespace-nowrap">
                                            @if($isGroupProg)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                                    Group
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                                    Individual
                                                </span>
                                            @endif
                                        </td>
                                        <td x-show="colZone" class="py-2 px-2.5 font-mono text-slate-600 text-[11px] whitespace-nowrap">
                                            {{ $prog?->zone?->name ?? $prog?->eligibility ?? 'A Zone' }}
                                        </td>
                                        <td x-show="colChestNo" class="py-2 px-2.5 font-mono font-bold text-slate-900 whitespace-nowrap">
                                            {{ $entry->chest_number ?: ($entry->code_letter ?: '—') }}
                                        </td>
                                        <td class="py-2 px-3">
                                            @if($isGroupProg)
                                                @if($entry->participants->isNotEmpty())
                                                    <div class="space-y-1">
                                                        <span class="font-bold text-slate-900 text-[11px] block">
                                                            Team Entry ({{ $entry->participants->count() }} members):
                                                        </span>
                                                        <div class="text-[10px] text-slate-600 font-mono leading-tight">
                                                            {{ $entry->participants->pluck('name')->implode(', ') }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="italic text-slate-500 text-xs">Group Team (Roster not specified)</span>
                                                @endif
                                            @else
                                                @if($entry->student)
                                                    <span class="font-bold text-slate-900 block">{{ $entry->student->name }}</span>
                                                    <span class="text-[10px] text-slate-500 font-mono block">
                                                        ID: {{ $entry->student->student_id }}
                                                        @if($entry->student->class_level)
                                                            <span x-show="colClass">• Class: {{ $entry->student->class_level }}</span>
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 italic">No candidate linked</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td x-show="colStatus" class="py-2 px-2.5 text-center whitespace-nowrap">
                                            @if($entry->status === 'verified' || $entry->status === 'confirmed')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                    Verified
                                                </span>
                                            @elseif($entry->status === 'pending')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                                    Pending
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 capitalize">
                                                    {{ $entry->status }}
                                                </span>
                                            @endif
                                        </td>
                                        <td x-show="colSign" class="py-2 px-3 text-center border-l border-slate-200">
                                            <div class="h-6 border-b border-dashed border-slate-300"></div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @else
                <!-- PROGRAM-WISE LAYOUT -->
                @foreach($groupedData as $progId => $progEntries)
                    @php
                        $prog = $allPrograms->firstWhere('id', $progId) ?? $progEntries->first()?->program;
                        $isGroupProg = ($prog?->type ?? 'individual') === 'group';
                    @endphp
                    <div class="entry-group-block avoid-break border border-slate-300 rounded-xl overflow-hidden">
                        <!-- Program Header Banner -->
                        <div class="bg-slate-900 text-white px-4 py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-amber-400 text-slate-950">
                                    {{ $prog->code }}
                                </span>
                                <div>
                                    <h2 class="text-sm font-bold font-sora tracking-wide uppercase inline">
                                        {{ $prog->name }}
                                    </h2>
                                    @if($prog->malayalam_name)
                                        <span x-show="colMalName" class="text-xs font-malayalam text-amber-200 ml-2">
                                            {{ $prog->malayalam_name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-xs font-mono">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $isGroupProg ? 'bg-purple-900 text-purple-200 border border-purple-700' : 'bg-blue-900 text-blue-200 border border-blue-700' }}">
                                    {{ ucfirst($prog->type ?? 'individual') }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-200 border border-slate-700">
                                    {{ $prog->zone?->name ?? $prog->eligibility ?? 'A Zone' }}
                                </span>
                                @if($prog->stage)
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-200 border border-slate-700">
                                        {{ $prog->stage->name }}
                                    </span>
                                @endif
                                <span class="text-amber-400 font-bold ml-2">
                                    {{ $progEntries->count() }} {{ $isGroupProg ? 'Teams' : 'Candidates' }}
                                </span>
                            </div>
                        </div>

                        <!-- Program Entries Table -->
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-100 font-mono font-bold text-slate-700 uppercase border-b border-slate-300 text-[11px]">
                                <tr>
                                    <th class="py-2 px-2 text-center w-8">#</th>
                                    <th class="py-2 px-3 w-48">Group / Team</th>
                                    <th x-show="colChestNo" class="py-2 px-2.5 w-24 font-mono text-center">Chest No</th>
                                    <th class="py-2 px-3">Participant(s)</th>
                                    <th x-show="colStatus" class="py-2 px-2.5 text-center w-24">Status</th>
                                    <th x-show="colSign" class="py-2 px-3 text-center w-32">Stage Call / Sign</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($progEntries as $idx => $entry)
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2 px-2 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                                        <td class="py-2 px-3">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2.5 h-2.5 rounded-full inline-block shrink-0" style="background-color: {{ $entry->group?->color_hex ?? '#be1e2d' }}"></span>
                                                <div>
                                                    <span class="font-bold text-slate-900 block">{{ $entry->group?->name ?? 'N/A' }}</span>
                                                    <span class="text-[10px] font-mono text-slate-500 block">{{ $entry->group?->code }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td x-show="colChestNo" class="py-2 px-2.5 text-center font-mono font-bold text-slate-900 whitespace-nowrap">
                                            {{ $entry->chest_number ?: ($entry->code_letter ?: '—') }}
                                        </td>
                                        <td class="py-2 px-3">
                                            @if($isGroupProg)
                                                @if($entry->participants->isNotEmpty())
                                                    <div class="space-y-0.5">
                                                        <span class="font-bold text-slate-900 text-[11px] block">
                                                            Team Roster ({{ $entry->participants->count() }} members):
                                                        </span>
                                                        <div class="text-[10px] text-slate-600 font-mono leading-tight">
                                                            {{ $entry->participants->pluck('name')->implode(', ') }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="italic text-slate-500 text-xs">Group Team (Roster not specified)</span>
                                                @endif
                                            @else
                                                @if($entry->student)
                                                    <span class="font-bold text-slate-900 block">{{ $entry->student->name }}</span>
                                                    <span class="text-[10px] text-slate-500 font-mono block">
                                                        ID: {{ $entry->student->student_id }}
                                                        @if($entry->student->class_level)
                                                            <span x-show="colClass">• Class: {{ $entry->student->class_level }}</span>
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 italic">No candidate linked</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td x-show="colStatus" class="py-2 px-2.5 text-center whitespace-nowrap">
                                            @if($entry->status === 'verified' || $entry->status === 'confirmed')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                    Verified
                                                </span>
                                            @elseif($entry->status === 'pending')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                                    Pending
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 capitalize">
                                                    {{ $entry->status }}
                                                </span>
                                            @endif
                                        </td>
                                        <td x-show="colSign" class="py-2 px-3 text-center border-l border-slate-200">
                                            <div class="h-6 border-b border-dashed border-slate-300"></div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Official Signatures Strip -->
        <div x-show="showSignatures" class="avoid-break mt-12 pt-8 border-t-2 border-slate-900 grid grid-cols-3 gap-6 text-center font-mono">
            <div>
                <div class="h-10 mb-2 border-b border-dashed border-slate-300"></div>
                <div class="text-xs font-bold text-slate-900 uppercase">Program Committee Convener</div>
                <div class="text-[10px] text-slate-500">Verification & Scheduling Desk</div>
            </div>
            <div>
                <div class="h-10 mb-2 border-b border-dashed border-slate-300"></div>
                <div class="text-xs font-bold text-slate-900 uppercase">Registration Desk In-Charge</div>
                <div class="text-[10px] text-slate-500">Candidate Quota Validation</div>
            </div>
            <div>
                <div class="h-10 mb-2 border-b border-dashed border-slate-300"></div>
                <div class="text-xs font-bold text-slate-900 uppercase">General Convener</div>
                <div class="text-[10px] text-slate-500">QUAF Sahityotsav Secretariat</div>
            </div>
        </div>

        <!-- Official Print Footer -->
        <div class="mt-8 pt-3 border-t border-slate-200 flex items-center justify-between text-[10px] font-mono text-slate-400">
            <div>QUAF Fest Management Engine • Official Competition Roster</div>
            <div>Generated on {{ now()->format('d M Y, h:i:s A') }}</div>
        </div>
    </div>

</body>
</html>
