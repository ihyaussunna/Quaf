@extends('layouts.leader', ['title' => 'House Roster: ' . $group->name])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif font-black text-slate-900">House Students Roster</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">All registered participants belonging to {{ $group->name }}.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs font-mono text-[#f3bd2e] font-bold shadow-xs">
                Total: {{ $students->total() }} Students
            </span>
        </div>
    </div>

    <!-- Students Table (Light Theme) -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Chest #</th>
                        <th class="px-6 py-4">Student ID</th>
                        <th class="px-6 py-4">Full Name</th>
                        <th class="px-6 py-4">Zone</th>
                        <th class="px-6 py-4">Enrolled Programs</th>
                        <th class="px-6 py-4 text-right">Verification</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse($students as $student)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-[#f3bd2e]">#{{ $student->chest_number ?? '---' }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $student->student_id }}</td>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $student->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-semibold">
                                    {{ $student->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1 max-w-md">
                                    @forelse($student->entries as $entry)
                                        <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 border border-slate-200 text-slate-700 font-medium">
                                            {{ $entry->program->name }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 text-[11px]">No entries yet</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('verify.student', $student->qr_token) }}" target="_blank" class="text-[#f3bd2e] font-semibold hover:underline">
                                    View Digital Pass &nearr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">No students registered in this house.</td>
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
