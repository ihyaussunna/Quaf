@extends('layouts.admin', ['title' => 'Student 360°: ' . $student->name])

@section('content')
<div class="space-y-8 max-w-6xl mx-auto">
    <div>
        <a href="{{ route('admin.students.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to Students</a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-serif font-black text-slate-900">Student 360° Profile</h1>
                <p class="text-xs font-mono text-slate-500 mt-1">Comprehensive festival dossier for {{ $student->name }} ({{ $student->student_id }}).</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.idcards.show', $student) }}" class="px-4 py-2 rounded-xl bg-white border border-slate-300 text-xs font-mono text-slate-700 hover:bg-slate-50 shadow-sm font-semibold">
                    🪪 Print ID Card
                </a>
                <a href="{{ route('admin.students.edit', $student) }}" class="px-4 py-2 rounded-xl bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase tracking-wider hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                    Edit Student
                </a>
            </div>
        </div>
    </div>

    <!-- 360 Profile Hero Card -->
    <div class="rounded-3xl bg-white border border-slate-200 p-8 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 items-center">
            <!-- Photo & QR Code -->
            <div class="flex flex-col items-center text-center space-y-4">
                <div class="w-36 h-36 rounded-2xl overflow-hidden bg-slate-100 border-2 border-[#f3bd2e]/40 shadow-md">
                    @if($student->photo_path)
                        <img src="{{ asset('storage/' . $student->photo_path) }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                    @elseif($student->photo_url)
                        <img src="{{ $student->photo_url }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center font-serif text-5xl font-bold text-[#f3bd2e]">
                            {{ substr($student->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div class="w-28 h-28 bg-white p-2 rounded-xl border border-slate-200 shadow-inner flex items-center justify-center">
                    <img src="{{ $qrCodeSvg }}" alt="QR Code" class="w-full h-full object-contain">
                </div>
                <span class="text-[10px] font-mono text-slate-500">Scan to Verify</span>
            </div>

            <!-- Details (3 Cols) -->
            <div class="md:col-span-3 space-y-6">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-amber-50 text-[#f3bd2e] border border-[#f3bd2e]/30">
                            {{ $student->student_id }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold" style="background-color: {{ $student->group->color_hex }}15; color: {{ $student->group->color_hex }}">
                            Group {{ $student->group->name }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded text-xs font-mono bg-slate-100 text-slate-700 border border-slate-200">
                            {{ $student->category }}
                        </span>
                    </div>
                    <h2 class="text-3xl font-serif font-black text-slate-900">{{ $student->name }}</h2>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs font-mono">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] text-slate-500 uppercase block">Class</span>
                        <span class="text-slate-900 font-medium">{{ $student->class_level ?? '—' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] text-slate-500 uppercase block">Gender</span>
                        <span class="text-slate-900 font-medium">{{ $student->gender }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] text-slate-500 uppercase block">Contact</span>
                        <span class="text-slate-900 font-medium">{{ $student->contact ?? '—' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] text-slate-500 uppercase block">Registered Programs</span>
                        <span class="text-slate-900 font-bold">{{ $student->entries->count() }} Entries</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] text-slate-500 uppercase block">Total Points Won</span>
                        <span class="text-[#f3bd2e] font-serif font-black text-base">{{ $student->points_cache }} pts</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] text-slate-500 uppercase block">Certificates</span>
                        <span class="text-slate-900 font-bold">{{ $student->certificates->count() }} Awards</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Registered Programs List -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-serif font-bold text-lg text-slate-900">Registered Program Entries</h3>
            <span class="text-xs font-mono text-slate-500">{{ $student->entries->count() }} Entries</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Chest #</th>
                        <th class="px-6 py-3 font-semibold">Program</th>
                        <th class="px-6 py-3 font-semibold">Zone</th>
                        <th class="px-6 py-3 font-semibold">Stage</th>
                        <th class="px-6 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($student->entries as $entry)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-3 font-bold text-[#f3bd2e]">{{ $entry->chest_number }}</td>
                            <td class="px-6 py-3 font-medium text-slate-900">{{ $entry->program->name }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $entry->program->eligibility ?? 'A Zone' }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $entry->program->stage?->name ?? 'TBD' }}</td>
                            <td class="px-6 py-3">
                                @if($entry->status === 'verified')
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold">VERIFIED</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-bold">PENDING</span>
                                @endif
                                @if($entry->conflict_flag)
                                    <span class="px-2 py-0.5 rounded bg-red-50 text-red-600 border border-red-200 font-bold ml-2">CONFLICT!</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">No program entries registered for this student yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
