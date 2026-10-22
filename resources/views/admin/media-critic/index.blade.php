@extends('layouts.admin', ['title' => 'മീഡിയ ക്രിറ്റിക് വിഷയം — അലോക്കേഷൻ (Q9-183)'])

@section('content')
<div class="space-y-8" id="media-critic-admin-container">
    <!-- Top Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                <span class="px-2.5 py-0.5 rounded-md text-xs font-mono font-bold bg-[#be1e2d]/10 text-[#be1e2d] border border-[#be1e2d]/30">
                    Q9-183
                </span>
                <span class="text-xs font-mono font-semibold text-slate-500 uppercase tracking-wide">
                    Stage Competition &bull; Media Critic
                </span>
                <span id="live-indicator" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live Sync Active</span>
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-sora font-black text-slate-900 tracking-tight">
                മീഡിയ ക്രിറ്റിക് വിഷയം &mdash; ടീം അലോക്കേഷൻ
            </h1>
            <p class="text-xs font-mono text-slate-500 mt-1">
                Media Critic Topic Allocation Dashboard &bull; തത്സമയ വിവരങ്ങൾ താഴെ കാണാം
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" onclick="window.location.reload()"
                class="px-3.5 py-2 rounded-xl text-xs font-mono font-bold bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 flex items-center gap-1.5 shadow-xs transition">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Refresh Now</span>
            </button>
            <a href="{{ route('topic-picker.index') }}" target="_blank"
                class="px-4 py-2 rounded-xl text-xs font-mono font-bold bg-amber-500 hover:bg-amber-600 text-slate-950 flex items-center gap-1.5 shadow-xs transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Open Spin Wheel</span>
            </a>
            <button onclick="window.print()"
                class="px-4 py-2 rounded-xl text-xs font-mono font-bold bg-slate-900 hover:bg-slate-800 text-white flex items-center gap-1.5 shadow-xs transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Judges Sheet</span>
            </button>
            <form method="POST" action="{{ route('topic-picker.admin.reset-all') }}"
                onsubmit="return confirm('ശ്രദ്ധിക്കുക: എല്ലാ ടീമുകളുടെയും വിഷയങ്ങൾ റീസെറ്റ് ചെയ്യപ്പെടും. തുടരണോ? (Are you sure you want to reset all topic allocations?)');">
                @csrf
                <button type="submit"
                    class="px-3.5 py-2 rounded-xl text-xs font-mono font-bold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition">
                    Reset All Picks
                </button>
            </form>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <span class="text-xs font-mono text-slate-500 uppercase tracking-wider block mb-1">Total Teams</span>
            <div class="text-3xl font-sora font-black text-slate-900">{{ $teamAllocations->count() }}</div>
            <span class="text-[11px] font-mono text-slate-400 mt-1 block">5 Official Festival Teams</span>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <span class="text-xs font-mono text-slate-500 uppercase tracking-wider block mb-1">Topics Selected</span>
            <div class="text-3xl font-sora font-black text-emerald-600" id="stat-claimed-count">{{ $claimedCount }}</div>
            <span class="text-[11px] font-mono text-slate-400 mt-1 block">Completed lottery spins</span>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <span class="text-xs font-mono text-slate-500 uppercase tracking-wider block mb-1">Pending Selection</span>
            <div class="text-3xl font-sora font-black text-amber-600" id="stat-remaining-count">{{ $remainingCount }}</div>
            <span class="text-[11px] font-mono text-slate-400 mt-1 block">Waiting for spin wheel</span>
        </div>
    </div>

    <!-- PRIMARY SECTION: 5-TEAM ALLOCATION BREAKDOWN -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-sora font-bold text-slate-900 flex items-center gap-2">
                    <span>ഓരോ ടീമും തിരഞ്ഞെടുത്ത വിഷയം</span>
                    <span class="text-xs font-mono font-normal text-slate-500">(Team-wise Allocations)</span>
                </h2>
                <p class="text-xs font-mono text-slate-500">
                    5 ടീമുകൾക്ക് ലഭിച്ച വിഷയങ്ങൾ താഴെ പ്രത്യേകം കാണാം
                </p>
            </div>
            <span class="text-xs font-mono text-slate-400 hidden sm:inline">
                Auto-updates when teams spin on stage
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5" id="team-allocations-grid">
            @foreach($teamAllocations as $alloc)
                @php
                    $group = $alloc->group;
                    $topic = $alloc->topic;
                    $hasSelected = $alloc->has_selected;
                    $groupColor = $group->color_hex ?? '#be1e2d';
                @endphp
                <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden flex flex-col justify-between transition-all hover:shadow-md"
                     style="border-top: 4px solid {{ $groupColor }};"
                     data-group-id="{{ $group->id }}">
                    <!-- Team Card Header -->
                    <div class="p-5 pb-3">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-2xs" style="background-color: {{ $groupColor }};"></span>
                                <div>
                                    <h3 class="font-sora font-black text-base text-slate-900 leading-tight">
                                        {{ $group->name }}
                                    </h3>
                                    <span class="text-[11px] font-mono text-slate-400 uppercase tracking-wider">
                                        Code: {{ $group->code }}
                                    </span>
                                </div>
                            </div>

                            @if($hasSelected)
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>തിരഞ്ഞെടുത്തു</span>
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-mono font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    <span>കാത്തിരിക്കുന്നു</span>
                                </span>
                            @endif
                        </div>

                        <!-- Topic Box -->
                        @if($hasSelected && $topic)
                            <div class="p-4 rounded-xl bg-gradient-to-br from-amber-50/70 to-white border border-amber-200/80 my-2">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-mono font-black bg-amber-500 text-slate-950">
                                        <span>Card {{ $topic->code_letter }}</span>
                                    </span>
                                    <span class="text-[11px] font-mono text-slate-500">
                                        വിഷയം #{{ $topic->topic_number }}
                                    </span>
                                </div>
                                <div class="text-sm sm:text-base font-bold text-slate-900 font-malayalam leading-relaxed">
                                    {{ $topic->title }}
                                </div>
                                <div class="mt-3 pt-2.5 border-t border-amber-200/50 flex flex-wrap items-center justify-between gap-2 text-[11px] font-mono text-slate-500">
                                    <span>Time: {{ $topic->picked_at?->format('h:i A') ?? '-' }}</span>
                                    <span class="uppercase">Method: {{ $topic->selection_method }}</span>
                                </div>
                            </div>
                        @else
                            <div class="py-6 px-4 rounded-xl bg-slate-50/70 border-2 border-dashed border-slate-200 text-center my-2">
                                <p class="text-sm font-malayalam font-bold text-slate-600 mb-1">
                                    വിഷയം തിരഞ്ഞെടുത്തിട്ടില്ല
                                </p>
                                <p class="text-xs font-mono text-slate-400">
                                    ടീം Spin Wheel കറക്കുന്നതോടെ ഇവിടെ തത്സമയം കാണാം
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Team Card Footer / Admin Actions -->
                    <div class="px-5 py-3 bg-slate-50/60 border-t border-slate-100 flex items-center justify-between gap-2">
                        @if($hasSelected && $topic)
                            <form method="POST" action="{{ route('topic-picker.admin.reset', $topic->id) }}"
                                  onsubmit="return confirm('{{ $group->name }} ടീമിന്റെ വിഷയം റീസെറ്റ് ചെയ്യണോ? (Reset this team pick?)');"
                                  class="w-full">
                                @csrf
                                <button type="submit"
                                    class="w-full py-1.5 px-3 rounded-lg text-xs font-mono font-bold text-rose-600 hover:text-rose-700 hover:bg-rose-50 border border-rose-200 transition text-center">
                                    Reset This Team (റീസെറ്റ്)
                                </button>
                            </form>
                        @else
                            <!-- Quick Manual Assign Dropdown -->
                            <form method="POST" action="{{ route('topic-picker.admin.assign') }}" class="w-full flex items-center gap-1.5">
                                @csrf
                                <input type="hidden" name="group_id" value="{{ $group->id }}">
                                <select name="topic_id" required class="flex-1 bg-white border border-slate-200 rounded-lg text-xs px-2 py-1.5 text-slate-700 font-mono">
                                    <option value="">Assign Topic...</option>
                                    @foreach($availableTopics as $avail)
                                        <option value="{{ $avail->id }}">Card {{ $avail->code_letter }} (#{{ $avail->topic_number }})</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-950 font-mono font-bold text-xs shrink-0">
                                    Assign
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- SECONDARY SECTION: OFFICIAL TOPICS TABLE -->
    <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="font-sora font-bold text-base text-slate-900">Official Topic Allocation Table</h2>
                <p class="text-xs font-mono text-slate-500">All 5 competition topics and their assigned teams</p>
            </div>
            <span class="text-xs font-mono text-slate-400">
                5 Topics &bull; Single Pick Mutual Lock
            </span>
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
                <tbody class="divide-y divide-slate-100 text-slate-700" id="topics-table-body">
                    @forelse($topics as $topic)
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $topic->isClaimed() ? 'bg-amber-50/20' : '' }}" data-topic-id="{{ $topic->id }}">
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
                                        <form method="POST" action="{{ route('topic-picker.admin.reset', $topic->id) }}"
                                            onsubmit="return confirm('Are you sure you want to reset this topic pick?');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold text-rose-600 hover:bg-rose-50 border border-rose-200 transition">
                                                Reset Pick
                                            </button>
                                        </form>
                                    @else
                                        <!-- Quick Manual Assign Dropdown -->
                                        <form method="POST" action="{{ route('topic-picker.admin.assign') }}" class="inline-flex items-center gap-1">
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
                    <th class="border border-black px-4 py-2 font-bold text-center">SL</th>
                    <th class="border border-black px-4 py-2 font-bold">Team Name</th>
                    <th class="border border-black px-4 py-2 font-bold text-center">Code</th>
                    <th class="border border-black px-4 py-2 font-bold text-center">Card</th>
                    <th class="border border-black px-4 py-2 font-bold">Allocated Topic (മലയാളം)</th>
                    <th class="border border-black px-4 py-2 font-bold text-center">Timestamp</th>
                </tr>
            </thead>
            <tbody>
                @foreach($teamAllocations as $idx => $item)
                    <tr class="border-b border-black">
                        <td class="border border-black px-4 py-2 text-center font-mono">{{ $idx + 1 }}</td>
                        <td class="border border-black px-4 py-2 font-bold">{{ $item->group->name }}</td>
                        <td class="border border-black px-4 py-2 font-mono text-center">{{ $item->group->code }}</td>
                        <td class="border border-black px-4 py-2 font-bold text-center">
                            {{ $item->topic ? 'Card '.$item->topic->code_letter : 'PENDING' }}
                        </td>
                        <td class="border border-black px-4 py-2 font-malayalam">
                            {{ $item->topic?->title ?? 'Not selected yet (തീരുമാനിച്ചിട്ടില്ല)' }}
                        </td>
                        <td class="border border-black px-4 py-2 font-mono text-xs text-center">
                            {{ $item->topic?->picked_at?->format('h:i A') ?? '-' }}
                        </td>
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

<script>
// Auto-refresh polling every 5 seconds to reflect live stage spins
(function() {
    let lastClaimedCount = {{ $claimedCount }};
    const pollUrl = '{{ route('topic-picker.admin') }}';

    setInterval(function() {
        fetch(pollUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                // If the claimed count changed, automatically reload the page to refresh cards and forms
                if (data.claimed_count !== lastClaimedCount) {
                    lastClaimedCount = data.claimed_count;
                    window.location.reload();
                }
            }
        })
        .catch(err => {
            // Silently ignore transient network glitches during polling
        });
    }, 4000);
})();
</script>
@endsection