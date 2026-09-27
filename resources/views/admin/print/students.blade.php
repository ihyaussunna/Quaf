<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Participant Delegate Registry — QUAF</title>
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
<body class="bg-slate-100 text-slate-900 font-sans antialiased min-h-screen py-6 px-3 sm:px-6"
      x-data="{
          colChestNo: true,
          colGroup: true,
          colCategory: true,
          colClass: true,
          colContact: true,
          colPrograms: true,
          colPoints: true,
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
                    <h1 class="text-lg font-bold text-slate-900">Participants Roster Customizer</h1>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Filter by group or zone, select required columns, and export to PDF</p>
            </div>
            
            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.students.index') }}" class="px-4 py-2 rounded-xl text-xs font-mono font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition-colors">
                    ← Back to Participants
                </a>
                <a href="{{ route('admin.exports.download', 'participants') }}" class="px-4 py-2 rounded-xl text-xs font-mono font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                    Download CSV
                </a>
                <button @click="printReport()" class="px-5 py-2.5 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#be1e2d] hover:bg-[#a01624] text-white flex items-center gap-2 shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print / Save PDF</span>
                </button>
            </div>
        </div>

        <!-- Filter Selectors -->
        <form method="GET" action="{{ route('admin.print.students') }}" class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-3">
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

            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Filter by Zone</label>
                <select name="category" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
                    <option value="">All Zones</option>
                    @foreach($categories as $val => $label)
                        <option value="{{ $val }}" {{ $selectedCategory == $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-mono uppercase font-bold text-slate-500 mb-1">Search Participant</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search name or ID..."
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-[#be1e2d]">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 text-xs font-mono font-bold bg-slate-800 text-white rounded-xl">Apply</button>
                @if($selectedGroupId || $selectedCategory || $search)
                    <a href="{{ route('admin.print.students') }}" class="px-3 py-2 text-xs font-mono text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- Column Checkboxes (Interactive Column Toggles) -->
        <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-4 text-xs font-medium text-slate-700">
            <span class="text-xs font-mono font-bold text-slate-500 uppercase">Include Columns:</span>
            
            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colChestNo" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Student ID / Chest #</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colGroup" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Group / Team</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colCategory" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Zone</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colClass" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Class</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colContact" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Contact</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colPrograms" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Enrolled Programs</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colPoints" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Total Points</span>
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="colSign" class="rounded border-slate-300 text-[#be1e2d] focus:ring-0">
                <span>Attendance / Sign Box</span>
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
                    <h1 class="text-xl font-black font-serif text-slate-900 uppercase tracking-tight">Participant Delegate Registry & Roll Sheet</h1>
                    <p class="text-xs font-semibold text-slate-600">Ihyaussunna Students Union, Jamia Markaz</p>
                </div>
            </div>

            <div class="text-right font-mono text-[11px] text-slate-500 space-y-0.5">
                <div class="font-bold text-slate-900 text-xs">DELEGATE ROSTER</div>
                <div>Date: {{ now()->format('d M Y, h:i A') }}</div>
                <div>Total Delegates: <span class="font-bold text-slate-900">{{ $students->count() }}</span></div>
                @if($selectedGroupId)
                    @php $currentGroup = $groups->firstWhere('id', $selectedGroupId); @endphp
                    <div class="font-bold" style="color: {{ $currentGroup?->color_hex ?? '#be1e2d' }}">
                        Group: {{ $currentGroup?->name }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Participants Table -->
        <div class="mt-4">
            @if($students->isEmpty())
                <div class="p-8 text-center border border-dashed border-slate-300 rounded-xl text-xs text-slate-500 font-mono">
                    No participants match the selected filter criteria.
                </div>
            @else
                <table class="w-full text-left text-xs border border-slate-200">
                    <thead class="bg-slate-100 font-mono font-bold text-slate-700 uppercase border-b border-slate-200">
                        <tr>
                            <th class="py-2 px-2.5 w-10 text-center">#</th>
                            <th x-show="colChestNo" class="py-2 px-2.5">ID / Chest #</th>
                            <th class="py-2 px-3">Participant Name</th>
                            <th x-show="colGroup" class="py-2 px-2.5">Group</th>
                            <th x-show="colCategory" class="py-2 px-2.5">Zone</th>
                            <th x-show="colClass" class="py-2 px-2.5">Class</th>
                            <th x-show="colContact" class="py-2 px-2.5">Contact</th>
                            <th x-show="colPrograms" class="py-2 px-3">Enrolled Competitions</th>
                            <th x-show="colPoints" class="py-2 px-2.5 text-right">Points</th>
                            <th x-show="colSign" class="py-2 px-3 text-center w-24">Signature</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-sans">
                        @foreach($students as $idx => $st)
                            <tr class="{{ $idx % 2 === 1 ? 'bg-slate-50/50' : 'bg-white' }} avoid-break">
                                <td class="py-2 px-2.5 text-center font-mono text-slate-500 text-[11px]">
                                    {{ $idx + 1 }}
                                </td>
                                
                                <td x-show="colChestNo" class="py-2 px-2.5 font-mono font-bold text-slate-800 text-[11px]">
                                    {{ $st->student_id }}
                                </td>

                                <td class="py-2 px-3 font-semibold text-slate-900">
                                    {{ $st->name }}
                                </td>

                                <td x-show="colGroup" class="py-2 px-2.5">
                                    <span class="inline-flex items-center gap-1.5 font-medium">
                                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $st->group?->color_hex ?? '#be1e2d' }}"></span>
                                        <span class="text-[11px]">{{ $st->group?->name ?? '—' }}</span>
                                    </span>
                                </td>

                                <td x-show="colCategory" class="py-2 px-2.5 font-mono text-[11px] text-slate-700">
                                    {{ $st->category }}
                                </td>

                                <td x-show="colClass" class="py-2 px-2.5 text-slate-600 text-[11px]">
                                    {{ $st->class_level ?? '—' }}
                                </td>

                                <td x-show="colContact" class="py-2 px-2.5 font-mono text-slate-600 text-[11px]">
                                    {{ $st->contact ?? '—' }}
                                </td>

                                <td x-show="colPrograms" class="py-2 px-3">
                                    @if($st->entries && $st->entries->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($st->entries as $entry)
                                                <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-[10px] font-mono text-slate-700">
                                                    {{ $entry->program?->code }}: {{ Str::limit($entry->program?->name, 18) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[11px] italic">Not registered</span>
                                    @endif
                                </td>

                                <td x-show="colPoints" class="py-2 px-2.5 text-right font-mono font-bold text-slate-900">
                                    {{ $st->points_cache }}
                                </td>

                                <td x-show="colSign" class="py-2 px-3 border-l border-slate-200">
                                    <div class="h-6 border-b border-dashed border-slate-300"></div>
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
                    <div class="font-bold text-slate-900">Desk Officer / Registrar</div>
                    <div class="text-[10px] text-slate-500">Registration Committee</div>
                </div>
                <div>
                    <div class="border-b border-slate-400 mb-2 h-8"></div>
                    <div class="font-bold text-slate-900">Group Leader / Captain</div>
                    <div class="text-[10px] text-slate-500">Official Verification</div>
                </div>
                <div>
                    <div class="border-b border-slate-400 mb-2 h-8"></div>
                    <div class="font-bold text-slate-900">General Convener</div>
                    <div class="text-[10px] text-slate-500">Ashabul Quaf Committee</div>
                </div>
            </div>
            <div class="text-center text-[10px] text-slate-400 font-mono mt-6">
                Markazu Saquafathi Sunniyya • Ihyaussunna Students Union • Official Participant Enrollment List
            </div>
        </div>

    </div>

</body>
</html>
