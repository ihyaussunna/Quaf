<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Master Competitions Schedule & Event Manual — QUAF</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            .print-hidden-section {
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
                padding: 8mm !important;
                margin: 0 !important;
            }
            tr, .avoid-break {
                page-break-inside: avoid !important;
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
          colCode: true,
          colMalName: true,
          colZone: true,
          colType: true,
          colStage: true,
          colLimit: true,
          colTime: true,
          colDuration: true,
          colWeight: true,
          colEntries: true,
          colRules: true,
          showSignatures: true,
          allProgIds: @json($programs->pluck('id')),
          excludedProgIds: [],
          toggleProg(id) {
              if (this.excludedProgIds.includes(id)) {
                  this.excludedProgIds = this.excludedProgIds.filter(x => x !== id);
              } else {
                  this.excludedProgIds.push(id);
              }
          },
          isProgExcluded(id) {
              return this.excludedProgIds.includes(id);
          },
          includeAllProgs() {
              this.excludedProgIds = [];
          },
          excludeAllProgs() {
              this.excludedProgIds = [...this.allProgIds];
          },
          get selectedCount() {
              return Math.max(0, this.allProgIds.length - this.excludedProgIds.length);
          },
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
                    <h1 class="text-lg font-bold text-slate-900">Competitions Master List Customizer</h1>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Filter competitions by stage or zone, select required sections and print to PDF</p>
            </div>
            
            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.programs.index') }}" class="px-4 py-2 rounded-xl text-xs font-mono font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition-colors">
                    ← Back to Programs
                </a>
                <a href="{{ route('admin.exports.download', 'competitions') }}" class="px-4 py-2 rounded-xl text-xs font-mono font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                    Download CSV
                </a>
                <button @click="printReport()" class="px-5 py-2.5 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#be1e2d] hover:bg-[#a01624] text-white flex items-center gap-2 shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print / Save PDF</span>
                </button>
            </div>
        </div>

        <!-- Filter Selectors -->
        <form method="GET" action="{{ route('admin.print.programs') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Filter by Zone</label>
                <select name="zone" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($zones as $val => $label)
                        <option value="{{ $val }}" {{ $selectedZone == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Filter by Stage Venue</label>
                <select name="stage" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Venues</option>
                    @foreach($stages as $stg)
                        <option value="{{ $stg->id }}" {{ $selectedStageId == $stg->id ? 'selected' : '' }}>{{ $stg->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Type</label>
                <select name="type" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Types (Individual & Group)</option>
                    <option value="individual" {{ $selectedType == 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="group" {{ $selectedType == 'group' ? 'selected' : '' }}>Group</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <div class="w-full">
                    <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Search Event</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search program..."
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                </div>
                <button type="submit" class="px-4 py-2 text-xs font-mono font-bold bg-slate-800 text-white rounded-xl">Go</button>
            </div>
        </form>

        <!-- Column Checkboxes (Interactive Column Toggles) -->
        <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-4 text-xs font-medium text-slate-700">
            <span class="text-xs font-mono font-bold text-slate-500 uppercase">Include Columns:</span>
            
            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colCode" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Code</span>
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
                <span>Type</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colStage" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Stage / Off Stage</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colLimit" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Participant Limit</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colTime" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Scheduled Time</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colDuration" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Duration</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colWeight" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Points Weight</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colEntries" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Registrations</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colRules" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Rules & Criteria</span>
            </label>
        </div>

        <!-- Row Selection & Omission Strip (Hidden on Print) -->
        <div class="pt-3 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2">
                <span class="font-mono font-bold text-slate-700 uppercase">Select Competitions for PDF:</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-mono font-bold bg-amber-100 text-amber-900 border border-amber-300"
                      x-text="selectedCount + ' of ' + allProgIds.length + ' included'"></span>
                <span class="text-slate-400 text-[11px] hidden sm:inline">(Uncheck competitions below to omit from PDF)</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="includeAllProgs()" 
                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-[11px] font-bold transition">
                    Select All
                </button>
                <button type="button" @click="excludeAllProgs()" 
                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-[11px] font-bold transition">
                    Deselect All
                </button>
            </div>
        </div>
    </div>

    <!-- Printable Official Document Container -->
    <div class="print-container max-w-7xl mx-auto bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xl text-slate-900">
        
        <!-- Official Festival Top Masthead Banner -->
        <div class="w-full pb-3 border-b border-slate-300 mb-4 text-center">
            <img src="{{ asset('images/print-pdf-header.svg') }}" alt="Festival Header" class="w-1/2 max-w-[50%] h-auto max-h-12 sm:max-h-14 object-contain block mx-auto" style="max-height: 55px; width: 50%; max-width: 50%;">
        </div>

        <!-- Official Document Header -->
        <div class="flex items-center justify-between pb-4 border-b-2 border-slate-900 gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/dashboard-logo.png') }}" alt="QUAF Logo" class="h-14 w-auto object-contain">
                <div>
                    <h1 class="text-xl font-black font-sora text-slate-900 uppercase tracking-tight">Master Competition Schedule & Event Manual</h1>
                    <p class="text-xs font-semibold text-slate-600">Ihyaussunna Students Union, Markazu Saquafathi Sunniyya</p>
                </div>
            </div>

            <div class="text-right font-mono text-[11px] text-slate-500 space-y-0.5">
                <div class="font-bold text-slate-900 text-xs">EVENT MANUAL</div>
                <div>Date: {{ now()->format('d M Y, h:i A') }}</div>
                <div>Total Competitions: <span class="font-bold text-slate-900">{{ $programs->count() }}</span></div>
                @if($selectedZone)
                    <div class="text-[#be1e2d] font-bold">Zone: {{ $selectedZone }}</div>
                @endif
            </div>
        </div>

        <!-- Programs Table -->
        <div class="mt-4">
            @if($programs->isEmpty())
                <div class="p-8 text-center border border-dashed border-slate-300 rounded-xl text-xs text-slate-500 font-mono">
                    No competitions match the selected filter criteria.
                </div>
            @else
                <table class="w-full text-left text-xs border border-slate-200">
                    <thead class="bg-slate-100 font-mono font-bold text-slate-700 uppercase border-b border-slate-200">
                        <tr>
                            <th class="no-print py-2 px-2 text-center w-12">In PDF</th>
                            <th class="py-2 px-2 text-center w-8">#</th>
                            <th x-show="colCode" class="py-2 px-2.5 w-24">Code</th>
                            <th class="py-2 px-3">Competition Name</th>
                            <th x-show="colMalName" class="py-2 px-3">Malayalam Name</th>
                            <th x-show="colZone" class="py-2 px-2.5">Zone</th>
                            <th x-show="colType" class="py-2 px-2 text-center">Type</th>
                            <th x-show="colStage" class="py-2 px-2.5">Stage / Off Stage</th>
                            <th x-show="colLimit" class="py-2 px-2 text-center w-12">Limit</th>
                            <th x-show="colTime" class="py-2 px-2.5">Scheduled Time</th>
                            <th x-show="colDuration" class="py-2 px-2 text-right">Time</th>
                            <th x-show="colWeight" class="py-2 px-2 text-right">Pts</th>
                            <th x-show="colEntries" class="py-2 px-2 text-center">Reg</th>
                            <th x-show="colRules" class="py-2 px-3 max-w-xs">Guidelines</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-sora">
                        @foreach($programs as $idx => $p)
                            <tr :class="isProgExcluded({{ $p->id }}) ? 'opacity-40 border-dashed bg-slate-100 print-hidden-section ' : ''" class="{{ $idx % 2 === 1 ? 'bg-slate-50/50' : 'bg-white' }} avoid-break">
                                <td class="no-print py-2 px-2 text-center whitespace-nowrap">
                                    <label class="inline-flex items-center cursor-pointer" title="Include/Exclude from PDF">
                                        <input type="checkbox"
                                               :checked="!isProgExcluded({{ $p->id }})"
                                               @change="toggleProg({{ $p->id }})"
                                               class="w-3.5 h-3.5 text-[#be1e2d] rounded border-slate-300 focus:ring-0">
                                    </label>
                                </td>
                                <td class="py-2 px-2 text-center font-mono text-slate-500 text-[11px]">
                                    {{ $idx + 1 }}
                                </td>
                                
                                <td x-show="colCode" class="py-2 px-2.5 font-mono font-bold text-slate-800 text-[11px]">
                                    {{ $p->code }}
                                </td>

                                <td class="py-2 px-3 font-semibold text-slate-900">
                                    {{ $p->name }}
                                </td>

                                <td x-show="colMalName" class="py-2 px-3 text-slate-700">
                                    {{ $p->malayalam_name ?? '—' }}
                                </td>

                                <td x-show="colZone" class="py-2 px-2.5 font-mono text-[11px] text-slate-700">
                                    {{ $p->eligibility ?? 'A Zone' }}
                                </td>

                                <td x-show="colType" class="py-2 px-2 text-center uppercase font-mono text-[10px] font-bold {{ $p->type === 'group' ? 'text-[#005c94]' : 'text-[#009444]' }}">
                                    {{ $p->type }}
                                </td>

                                <td x-show="colStage" class="py-2 px-2.5 text-[11px]">
                                    @if($p->is_stage)
                                        <span class="font-bold text-[#be1e2d]">Stage</span>
                                        @if($p->stage)
                                            <span class="text-slate-500 text-[10px]">({{ $p->stage->name }})</span>
                                        @endif
                                    @else
                                        <span class="text-slate-600">Non Stage</span>
                                    @endif
                                </td>

                                <td x-show="colLimit" class="py-2 px-2 text-center font-mono font-bold text-slate-800 text-[11px]">
                                    {{ $p->participant_count ?? 2 }}
                                </td>

                                <td x-show="colTime" class="py-2 px-2.5 font-mono text-[11px] text-slate-600">
                                    {{ $p->scheduled_time ? $p->scheduled_time->format('h:i A') : 'TBA' }}
                                </td>

                                <td x-show="colDuration" class="py-2 px-2 text-right font-mono text-[11px] text-slate-700">
                                    {{ $p->has_time_limit && $p->duration_minutes ? $p->duration_minutes . 'm' : '—' }}
                                </td>

                                <td x-show="colWeight" class="py-2 px-2 text-right font-mono text-[11px] text-slate-700">
                                    {{ $p->points_weight }}x
                                </td>

                                <td x-show="colEntries" class="py-2 px-2 text-center font-mono font-bold text-slate-800">
                                    {{ $p->entries_count }}
                                </td>

                                <td x-show="colRules" class="py-2 px-3 text-[11px] text-slate-600 leading-tight">
                                    {{ Str::limit($p->rules ?? 'Standard judging criteria apply.', 90) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Official Signatures Block -->
        <div x-show="showSignatures" class="pt-8 mt-8 border-t border-slate-300 avoid-break">
            <div class="grid grid-cols-3 gap-6 text-center text-xs font-mono">
                <div>
                    <div class="border-b border-slate-400 mb-2 h-8"></div>
                    <div class="font-bold text-slate-900">Program Director</div>
                    <div class="text-[10px] text-slate-500">Programming Directorate</div>
                </div>
                <div>
                    <div class="border-b border-slate-400 mb-2 h-8"></div>
                    <div class="font-bold text-slate-900">Stage Coordinator</div>
                    <div class="text-[10px] text-slate-500">Logistics & Venues</div>
                </div>
                <div>
                    <div class="border-b border-slate-400 mb-2 h-8"></div>
                    <div class="font-bold text-slate-900">General Convener</div>
                    <div class="text-[10px] text-slate-500">Ashabul Quaf Committee</div>
                </div>
            </div>
            <div class="text-center text-[10px] text-slate-400 font-mono mt-6">
                Markazu Saquafathi Sunniyya • Ihyaussunna Students Union • Official Competitions Schedule Sheet
            </div>
        </div>

    </div>

</body>
</html>
