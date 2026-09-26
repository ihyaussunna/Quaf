@extends('layouts.admin', ['title' => 'Register Judge'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.judges.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to Judges</a>
        <h1 class="text-3xl font-serif font-black text-slate-900">Register Judge</h1>
        <p class="text-xs font-mono text-slate-500 mt-1">Creates a judge profile and login credentials for evaluating assigned programs.</p>
    </div>

    <form method="POST" action="{{ route('admin.judges.store') }}" class="rounded-2xl bg-white border border-slate-200 p-8 space-y-6 shadow-sm">
        @csrf

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Judge Full Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Dr. Anas Al-Azhari"
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Login Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="judge@quaf.fest"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Login Password</label>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Designation / Title</label>
                <input type="text" name="designation" value="{{ old('designation') }}" placeholder="e.g. Professor of Arabic"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Specialization</label>
                <input type="text" name="specialization" value="{{ old('specialization') }}" placeholder="e.g. Vocal Music / Elocution"
                       class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
            </div>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Contact Number</label>
            <input type="text" name="contact" value="{{ old('contact') }}" placeholder="+91 ..."
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Assign Programs to Judge</label>
            <div class="max-h-48 overflow-y-auto space-y-2 p-3 rounded-xl bg-slate-50 border border-slate-300 text-xs font-mono">
                @foreach($programs as $prog)
                    <label class="flex items-center gap-2 text-slate-700 hover:text-slate-900 cursor-pointer">
                        <input type="checkbox" name="program_ids[]" value="{{ $prog->id }}"
                               class="rounded border-slate-300 text-[#f3bd2e] focus:ring-0">
                        <span>[{{ $prog->code }}] {{ $prog->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
            <a href="{{ route('admin.judges.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
            <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#f3bd2e] text-white hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                Save Judge Profile
            </button>
        </div>
    </form>
</div>
@endsection
