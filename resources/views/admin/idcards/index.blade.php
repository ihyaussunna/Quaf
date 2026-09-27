@extends('layouts.admin', ['title' => 'Student QR ID Cards'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-sora font-black text-slate-900">Student QR ID Badges</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">Official participant festival credentials with encrypted QR verification tokens.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.idcards.chest-slips', ['group' => $groupId, 'category' => $category]) }}" target="_blank" class="px-4 py-2.5 bg-white border border-slate-300 text-slate-800 font-mono font-bold text-xs uppercase rounded-xl hover:bg-slate-50 flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4 text-[#f3bd2e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Chest Number Slips (FestPro)</span>
            </a>
            <a href="{{ route('admin.idcards.print', ['group' => $groupId]) }}" target="_blank" class="px-5 py-2.5 bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase rounded-xl hover:brightness-110 flex items-center gap-2 shadow-lg shadow-[#f3bd2e]/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Batch Print Badges {{ $groupId ? '(Filtered)' : '(All)' }}</span>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.idcards.index') }}" class="rounded-2xl bg-white border border-slate-200 p-4 grid grid-cols-1 sm:grid-cols-4 gap-4 shadow-sm">
        <div>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name or ID..."
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>
        <div>
            <select name="group" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="">All Groups</option>
                @foreach($groups as $g)
                    <option value="{{ $g->id }}" {{ $groupId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="category" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="">All Zones</option>
                @foreach($zones as $val => $label)
                    <option value="{{ $val }}" {{ $category == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="flex-1 py-2.5 bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase rounded-xl hover:brightness-110 shadow-md shadow-[#f3bd2e]/20">Filter</button>
            <a href="{{ route('admin.idcards.index') }}" class="px-3 py-2.5 bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200 rounded-xl text-xs font-mono font-semibold">Reset</a>
        </div>
    </form>

    <!-- Student Badges Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($students as $student)
            <div class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-[#f3bd2e]/40 transition-all group flex flex-col justify-between shadow-sm hover:shadow-md">
                <div class="flex items-start gap-4">
                    <div class="w-16 h-16 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center font-sora text-2xl font-bold text-[#f3bd2e] overflow-hidden flex-shrink-0">
                        @if($student->photo_path)
                            <img src="{{ asset('storage/' . $student->photo_path) }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                        @else
                            {{ substr($student->name, 0, 1) }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold" style="background-color: {{ $student->group->color_hex }}15; color: {{ $student->group->color_hex }}">
                                {{ $student->group->name }}
                            </span>
                            <span class="text-[10px] font-mono text-slate-500">{{ $student->category }}</span>
                        </div>
                        <h3 class="text-base font-sora font-bold text-slate-900 truncate mt-1 group-hover:text-[#f3bd2e] transition-colors">
                            {{ $student->name }}
                        </h3>
                        <p class="text-xs font-mono text-slate-500">ID: {{ $student->student_id }}</p>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] font-mono text-slate-600">Chest: <strong class="text-slate-900">{{ $student->chest_number ?? 'N/A' }}</strong></span>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.idcards.show', $student) }}" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-[#f3bd2e] border border-[#f3bd2e]/30 rounded-lg text-xs font-mono font-semibold">
                            View Badge &rarr;
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-400 font-mono text-xs bg-white rounded-2xl border border-slate-200 shadow-sm">
                No students found matching your filter criteria.
            </div>
        @endforelse
    </div>

    <div>
        {{ $students->links() }}
    </div>
</div>
@endsection
