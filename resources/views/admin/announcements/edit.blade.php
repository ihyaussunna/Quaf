@extends('layouts.admin', ['title' => 'Edit Announcement'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.announcements.index') }}" class="text-xs font-mono text-[#f3bd2e] hover:underline mb-2 block font-semibold">← Back to Announcements</a>
        <h1 class="text-3xl font-sora font-black text-slate-900">Edit Announcement</h1>
    </div>

    <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" class="rounded-2xl bg-white border border-slate-200 p-8 space-y-6 shadow-sm">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Notice Headline</label>
            <input type="text" name="title" value="{{ old('title', $announcement->title) }}" required
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Priority Level</label>
                <select name="priority" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="normal" {{ old('priority', $announcement->priority) == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="important" {{ old('priority', $announcement->priority) == 'important' ? 'selected' : '' }}>Important</option>
                    <option value="urgent" {{ old('priority', $announcement->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Target Audience</label>
                <select name="target_role" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">
                    <option value="all" {{ old('target_role', $announcement->target_role) == 'all' ? 'selected' : '' }}>Everyone / Public</option>
                    <option value="student" {{ old('target_role', $announcement->target_role) == 'student' ? 'selected' : '' }}>Students</option>
                    <option value="leader" {{ old('target_role', $announcement->target_role) == 'leader' ? 'selected' : '' }}>Group Leaders</option>
                    <option value="judge" {{ old('target_role', $announcement->target_role) == 'judge' ? 'selected' : '' }}>Judges / Jury</option>
                    <option value="green_room" {{ old('target_role', $announcement->target_role) == 'green_room' ? 'selected' : '' }}>Green Room Backstage</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-mono uppercase text-slate-600 mb-1.5 font-bold">Announcement Body</label>
            <textarea name="message" rows="4" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:border-[#f3bd2e] focus:bg-white transition-colors">{{ old('message', $announcement->message) }}</textarea>
        </div>

        <div class="pt-4 flex items-center justify-between border-t border-slate-100">
            <button type="submit" form="delete-form" class="text-xs font-mono text-red-600 hover:underline">
                Delete Announcement
            </button>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.announcements.index') }}" class="px-5 py-3 rounded-xl text-xs font-mono text-slate-500 hover:text-slate-800">Cancel</a>
                <button type="submit" class="px-6 py-3 rounded-xl text-xs font-mono font-bold uppercase tracking-wider bg-[#f3bd2e] text-white hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
                    Update Notice
                </button>
            </div>
        </div>
    </form>

    <form id="delete-form" method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete announcement?');" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
