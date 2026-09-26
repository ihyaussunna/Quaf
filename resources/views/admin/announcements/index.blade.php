@extends('layouts.admin', ['title' => 'Festival Announcements'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-serif font-black text-slate-900">Announcements</h1>
            <p class="text-xs font-mono text-slate-500 mt-1">Broadcast urgent stage calls, jury notices, and group alerts.</p>
        </div>
        <a href="{{ route('admin.announcements.create') }}" class="px-4 py-2.5 rounded-xl bg-[#f3bd2e] text-white font-mono font-bold text-xs uppercase tracking-wider hover:brightness-110 shadow-lg shadow-[#f3bd2e]/20">
            + Broadcast Notice
        </a>
    </div>

    <div class="space-y-4">
        @forelse($announcements as $ann)
            <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 hover:border-slate-300 transition-all">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        @if($ann->priority === 'urgent')
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-red-600 text-white uppercase animate-pulse shadow-sm">URGENT</span>
                        @elseif($ann->priority === 'important')
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-50 text-amber-700 border border-amber-300 uppercase">IMPORTANT</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-600 uppercase border border-slate-200">NORMAL</span>
                        @endif

                        <span class="text-xs font-mono text-slate-500">Target: {{ $ann->target_role ?? 'Everyone' }}</span>
                        @if($ann->targetStage)
                            <span class="text-xs font-mono text-[#f3bd2e] font-semibold">[{{ $ann->targetStage->code }}]</span>
                        @endif
                        @if($ann->targetGroup)
                            <span class="text-xs font-mono text-slate-700 font-semibold">[Group {{ $ann->targetGroup->code }}]</span>
                        @endif
                    </div>

                    <h3 class="text-xl font-serif font-bold text-slate-900 mb-2">{{ $ann->title }}</h3>
                    <p class="text-sm text-slate-600 font-normal max-w-3xl leading-relaxed">{{ $ann->message }}</p>
                </div>

                <div class="flex items-center gap-3 self-end md:self-center font-mono text-xs">
                    <a href="{{ route('admin.announcements.edit', $ann) }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">Edit</a>
                    <form method="POST" action="{{ route('admin.announcements.destroy', $ann) }}" onsubmit="return confirm('Delete announcement?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 text-red-600 hover:underline">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-white rounded-2xl border border-slate-200 font-mono text-sm text-slate-400 shadow-sm">
                No active announcements currently posted.
            </div>
        @endforelse
    </div>

    <div>
        {{ $announcements->links() }}
    </div>
</div>
@endsection
