@extends('layouts.admin', ['title' => 'Media Critic Topics (Q9-183)'])

@section('content')
<div class="space-y-8">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-amber-50 text-[#be1e2d] border border-[#be1e2d]/30">
                    Q9-183
                </span>
                <span class="text-xs font-mono text-slate-500">Stage Competition &bull; Media Critic</span>
            </div>
            <h1 class="text-3xl font-sora font-black text-slate-900">
                Media Critic Topic Allocation
            </h1>
            <p class="text-xs font-mono text-slate-500 mt-0.5">
                Manage official topics, review team allocations, and reset lottery picks.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('topic-picker.index') }}" target="_blank"
                class="px-4 py-2.5 rounded-xl text-xs font-mono font-bold bg-amber-500 hover:bg-amber-600 text-slate-950 flex items-center gap-1.5 shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Open Live Topic Picker</span>
            </a>
            <button onclick="window.print()"
                class="px-4 py-2.5 rounded-xl text-xs font-mono font-bold bg-slate-900 hover:bg-slate-800 text-white flex items-center gap-1.5 shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Judges Sheet</span>
            </button>
            <form method="POST" action="{{ route('admin.media-critic-topics.reset-all') }}"
                onsubmit="return confirm('ശ്രദ്ധിക്കുക: എല്ലാ ടീമുകളുടെയും വിഷയങ്ങൾ റീസെറ്റ് ചെയ്യപ്പെടും. തുടരണോ? (Are you sure you want to reset all topic allocations?)');">
                @csrf
                <button type="submit"
                    class="px-4 py-2.5 rounded-xl text-xs font-mono font-bold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition">
                    Reset All Picks
                </button>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <span class="text-xs font-mono text-slate-500 uppercase tracking-wider block mb-1">Total Topics</span>
            <div class="text-3xl font-sora font-black text-slate-900">{{ $topics->count() }}</div>
            <span class="text-[11px] font-mono text-slate-400 mt-1 block">5 official competition topics</span>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <span class="text-xs font-mono text-slate-500 uppercase tracking-wider block mb-1">Allocated / Locked</span>
            <div class="text-3xl font-sora font-black text-emerald-600">{{ $claimedCount }}</div>
            <span class="text-[11px] font-mono text-slate-400 mt-1 block">Picked by team leaders</span>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <span class="text-xs font-mono text-slate-500 uppercase tracking-wider block mb-1">Remaining Available</span>
            <div class="text-3xl font-sora font-black text-amber-600">{{ $remainingCount }}</div>
            <span class="text-[11px] font-mono text-slate-400 mt-1 block">Open for lottery selection</span>
        </div>
    </div>

    <!-- Topics Table -->
    <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-sora font-bold text-base text-slate-900">Official Topic Allocation Table</h2>
                <p class="text-xs font-mono text-slate-500">Real-time status of cards and allotted groups</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5 font-semibold">Card / Code</th>
                        <th class="px-6 py-3.5 font-semibold">Competition Topic (മലയാളം)</th>
                        <th class="px-6 py-3.5 font-semibold">Assigned Team</th>
                        <th class="px-6 py-3.5 font-semibold">Picked At / Method</th>
                        <th class="px-6 py-3.5 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($topics as $topic)
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $topic->isClaimed() ? 'bg-amber-50/20' : '' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-xl bg-slate-900 text-amber-400 font-black text-sm flex items-center justify-center shadow-xs">
                                        {{ $topic->code_letter }}
                                    </span>
                                    <div>
                                        <div class="font-bold text-slate-900">Card {{ $topic->code_letter }}</div>
                                        <span class="text-[10px] text-slate-400">#{{ $topic->topic_number }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-900 font-malayalam max-w-lg leading-relaxed">
                                    {{ $topic->title }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($topic->isClaimed() && $topic->group)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold font-sora text-slate-900" style="background-color: {{ $topic->group->color_hex ?? '#fef3c7' }}25; border: 1px solid {{ $topic->group->color_hex ?? '#dfa528' }};">
                                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $topic->group->color_hex ?? '#dfa528' }};"></span>
                                        <span>{{ $topic->group->name }}</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                        Available (ലഭ്യമാണ്)
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($topic->isClaimed())
                                    <div class="font-bold text-slate-800">
                                        {{ $topic->picked_at?->format('d M, h:i A') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 uppercase">
                                        Method: {{ $topic->selection_method }} &bull; {{ $topic->pickedBy?->name ?? 'Leader' }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Not picked yet</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($topic->isClaimed())
                                        <form method="POST" action="{{ route('admin.media-critic-topics.reset', $topic) }}"
                                            onsubmit="return confirm('Are you sure you want to reset this topic pick?');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold text-rose-600 hover:bg-rose-50 border border-rose-200 transition">
                                                Reset Pick
                                            </button>
                                        </form>
                                    @else
                                        <!-- Quick Manual Assign Dropdown -->
                                        <form method="POST" action="{{ route('admin.media-critic-topics.assign') }}" class="inline-flex items-center gap-1">
                                            @csrf
                                            <input type="hidden" name="topic_id" value="{{ $topic->id }}">
                                            <select name="group_id" required class="bg-slate-50 border border-slate-200 rounded-lg text-xs px-2 py-1 text-slate-700">
                                                <option value="">Assign Team...</option>
                                                @foreach($groups as $g)
                                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs">
                                                Assign
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                No topics configured.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Printable Official Sheet for Judges (Hidden on Screen, Visible on Print) -->
    <div class="hidden print:block p-8 bg-white text-black font-serif">
        <div class="text-center pb-4 border-b-2 border-black mb-6">
            <h1 class="text-2xl font-bold uppercase tracking-wider">QUAF Fest</h1>
            <h2 class="text-xl font-bold mt-1">Media Critic Competition (Q9-183) &mdash; Official Topic Allocation</h2>
            <p class="text-xs text-gray-600 mt-1 font-mono">Generated on {{ now()->format('d M Y, h:i A') }}</p>
        </div>

        <table class="w-full text-left text-sm border-collapse border border-black mb-8">
            <thead>
                <tr class="bg-gray-100 border-b border-black">
                    <th class="border border-black px-4 py-2 font-bold">SL</th>
                    <th class="border border-black px-4 py-2 font-bold">Card</th>
                    <th class="border border-black px-4 py-2 font-bold">Team Name</th>
                    <th class="border border-black px-4 py-2 font-bold">Allocated Topic (മലയാളം)</th>
                    <th class="border border-black px-4 py-2 font-bold">Timestamp</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topics as $idx => $t)
                    <tr class="border-b border-black">
                        <td class="border border-black px-4 py-2 text-center">{{ $idx + 1 }}</td>
                        <td class="border border-black px-4 py-2 font-bold text-center">Card {{ $t->code_letter }}</td>
                        <td class="border border-black px-4 py-2 font-bold">{{ $t->group?->name ?? 'NOT ALLOCATED' }}</td>
                        <td class="border border-black px-4 py-2 font-malayalam">{{ $t->title }}</td>
                        <td class="border border-black px-4 py-2 font-mono text-xs">{{ $t->picked_at?->format('d M, h:i A') ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="grid grid-cols-3 gap-8 pt-12 text-center text-xs font-mono mt-12">
            <div>
                <div class="border-b border-black mb-1 h-12"></div>
                <strong>Stage Coordinator</strong>
            </div>
            <div>
                <div class="border-b border-black mb-1 h-12"></div>
                <strong>Chief Judge</strong>
            </div>
            <div>
                <div class="border-b border-black mb-1 h-12"></div>
                <strong>Program Samithi Convenor</strong>
            </div>
        </div>
    </div>
</div>
@endsection
