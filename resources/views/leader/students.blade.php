@extends('layouts.leader', ['title' => 'Group Roster: ' . $group->name])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif font-black text-slate-900">Group Students Roster</h1>
            <p class="text-xs font-sans text-slate-500 mt-1">All registered participants belonging to {{ $group->name }}.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs font-sans text-slate-700 font-bold shadow-xs">
                Total: <span class="font-mono text-slate-900 font-bold">{{ $students->total() }}</span> Students
            </span>
        </div>
    </div>

    <!-- Students Table (Light Theme) -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sans">
                <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-200 font-semibold text-[11px] tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Chest Number</th>
                        <th class="px-6 py-4">Full Name</th>
                        <th class="px-6 py-4">Zone</th>
                        <th class="px-6 py-4">Enrolled Programs</th>
                        <th class="px-6 py-4 text-right">Verification</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse($students as $student)
                        @php
                            $chestNo = ltrim((string)($student->chest_number ?: $student->student_id), '#');
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900 font-mono text-sm">{{ $chestNo ?: '---' }}</td>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $student->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-semibold">
                                    {{ $student->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5 max-w-md">
                                    @forelse($student->entries as $entry)
                                        <span class="px-2.5 py-1 rounded-lg text-[11px] bg-slate-100 border border-slate-200 text-slate-700 font-medium">
                                            {{ $entry->program->name }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 text-xs">No entries yet</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('verify.student', $student->qr_token) }}" target="_blank" class="text-[#005c94] font-semibold hover:underline">
                                    View Digital Pass &nearr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">No students registered in this group.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $students->links() }}
    </div>
</div>
@endsection
