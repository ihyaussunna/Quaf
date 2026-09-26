@extends('layouts.admin', ['title' => 'Broadcast Announcement'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.announcements.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to Announcements</a>
        <h1 class="text-3xl font-serif font-black text-slate-900">Broadcast Announcement</h1>
    </div>

    <form method="POST" action="{{ route('admin.announcements.store') }}" class="rounded-2xl bg-white border border-slate-200 p-8 space-y-6 shadow-sm">
        @csrf

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Notice Headline</label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Schedule Update for Stage 03"
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Priority Level</label>
                <select name="priority" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="important" {{ old('priority') == 'important' ? 'selected' : '' }}>Important</option>
                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent (Top Marquee Alert)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Target Audience</label>
                <select name="target_role" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="all">Everyone / Public</option>
                    <option value="student">Students</option>
                    <option value="leader">House Leaders</option>
                    <option value="judge">Judges / Jury</option>
                    <option value="green_room">Green Room Backstage</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Specific Stage (Optional)</label>
                <select name="target_stage_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="">-- All Stages --</option>
                    @foreach($stages as $stg)
                        <option value="{{ $stg->id }}" {{ old('target_stage_id') == $stg->id ? 'selected' : '' }}>{{ $stg->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Specific House (Optional)</label>
                <select name="target_group_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="">-- All Houses --</option>
                    @foreach($groups as $grp)
                        <option value="{{ $grp->id }}" {{ old('target_group_id') == $grp->id ? 'selected' : '' }}>{{ $grp->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Announcement Body</label>
            <textarea name="message" rows="4" required placeholder="Write the full announcement text..."
                      class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">{{ old('message') }}</textarea>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
            <a href="{{ route('admin.announcements.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
            <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#f3bd2e] text-white hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                Broadcast Notice
            </button>
        </div>
    </form>
</div>
@endsection
