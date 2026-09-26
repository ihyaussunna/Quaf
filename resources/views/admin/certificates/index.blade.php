@extends('layouts.admin', ['title' => 'Issued Certificates'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif font-black text-slate-900">Digital Certificates</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">Official merit and participation certificates with unique serial numbers and QR authentication.</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.certificates.index') }}" class="rounded-2xl bg-white border border-slate-200 p-4 grid grid-cols-1 sm:grid-cols-3 gap-4 shadow-sm">
        <div>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search certificate # or student..."
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>
        <div>
            <select name="program" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                <option value="">All Programs</option>
                @foreach($programs as $p)
                    <option value="{{ $p->id }}" {{ $programId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="flex-1 py-2.5 bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase rounded-xl hover:brightness-110 shadow-md shadow-[#f3bd2e]/20">Filter</button>
            <a href="{{ route('admin.certificates.index') }}" class="px-3 py-2.5 bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200 rounded-xl text-xs font-mono font-semibold">Reset</a>
        </div>
    </form>

    <!-- Certificates Table -->
    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Certificate Serial</th>
                        <th class="px-6 py-4 font-semibold">Participant</th>
                        <th class="px-6 py-4 font-semibold">Group</th>
                        <th class="px-6 py-4 font-semibold">Program</th>
                        <th class="px-6 py-4 font-semibold">Placement</th>
                        <th class="px-6 py-4 font-semibold">Issued On</th>
                        <th class="px-6 py-4 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($certificates as $cert)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4 font-bold text-[#f3bd2e]">{{ $cert->certificate_number }}</td>
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $cert->student->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold" style="background-color: {{ $cert->student->group->color_hex }}15; color: {{ $cert->student->group->color_hex }}">
                                    {{ $cert->student->group->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $cert->program->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded bg-amber-50 border border-amber-200 text-[#f3bd2e] font-bold">
                                    {{ $cert->position }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ $cert->issued_at?->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.certificates.show', $cert) }}" class="text-[#f3bd2e] hover:underline font-semibold">Print View</a>
                                <span class="text-slate-300">|</span>
                                <a href="{{ route('verify.certificate', $cert->certificate_number) }}" target="_blank" class="text-slate-500 hover:text-slate-900">Verify URL</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">No certificates issued yet. Certificates are issued automatically upon publishing results.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $certificates->links() }}
    </div>
</div>
@endsection
