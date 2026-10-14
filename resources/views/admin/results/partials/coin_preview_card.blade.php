@if(!empty($coinPreview))
<div class="rounded-2xl bg-linear-to-br from-amber-50/80 via-white to-amber-100/40 border border-amber-300 p-6 shadow-xs space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-amber-200/80 pb-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-amber-500 text-slate-950 uppercase tracking-wider">
                    QUAF 09 Coin Distribution Preview
                </span>
                <span class="text-xs font-mono text-slate-500 font-semibold">{{ !empty($coinPreview['is_group']) ? 'Group Program Rule' : 'Individual Program Rule' }}</span>
            </div>
            <h3 class="font-sora font-black text-lg text-slate-900 mt-1">
                {{ $coinPreview['program_name'] }} <span class="text-slate-400 font-normal font-mono">({{ $coinPreview['program_code'] }})</span>
            </h3>
        </div>

        <div class="flex items-center gap-3">
            <div class="px-3.5 py-2 rounded-xl bg-white border border-amber-300 shadow-2xs text-right">
                <span class="block text-[10px] font-mono uppercase text-slate-500 font-semibold">Total Participants</span>
                <span class="text-base font-sora font-bold text-slate-900">{{ $coinPreview['total_eligible_participants'] }}</span>
            </div>
            <div class="px-3.5 py-2 rounded-xl bg-amber-500 text-slate-950 shadow-2xs text-right">
                <span class="block text-[10px] font-mono uppercase font-bold text-slate-900/80">Total Coins Credit</span>
                <span class="text-base font-sora font-black text-slate-950">{{ number_format($coinPreview['total_coins_to_credit']) }}</span>
            </div>
        </div>
    </div>

    @if(!empty($coinPreview['flags']))
        <div class="rounded-xl bg-amber-100/80 border border-amber-300 p-3 space-y-1">
            @foreach($coinPreview['flags'] as $flag)
                <p class="text-xs font-mono font-medium text-amber-900 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                    <span>{{ $flag }}</span>
                </p>
            @endforeach
        </div>
    @endif

    <div class="space-y-4">
        @foreach($coinPreview['placements'] as $placement)
            <div class="rounded-xl bg-white border border-slate-200 shadow-2xs overflow-hidden">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        @if($placement['position'] === 1)
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-400 text-slate-950 uppercase">1st Prize</span>
                        @elseif($placement['position'] === 2)
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-300 text-slate-900 uppercase">2nd Prize</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-900 uppercase">3rd Prize</span>
                        @endif
                        <span class="font-sora font-bold text-sm text-slate-900">{{ $placement['group_name'] }}</span>
                        @if(!empty($placement['chest_number']))
                            <span class="text-xs font-mono text-slate-400 font-semibold">(Chest #{{ $placement['chest_number'] }})</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-3 text-xs font-mono">
                        <span class="px-2 py-0.5 rounded bg-slate-100 font-bold text-slate-800">
                            Grade: {{ $placement['grade'] }}
                        </span>
                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">
                            {{ $placement['coins_per_participant'] }} coins / participant
                        </span>
                        <span class="font-bold text-slate-900">
                            Subtotal: {{ number_format($placement['total_coins']) }} coins
                        </span>
                    </div>
                </div>

                @if(!empty($placement['participants']))
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs font-mono">
                            <thead class="bg-slate-50/50 text-slate-500 uppercase border-b border-slate-100 text-[10px]">
                                <tr>
                                    <th class="px-4 py-2 font-semibold">#</th>
                                    <th class="px-4 py-2 font-semibold">Student ID</th>
                                    <th class="px-4 py-2 font-semibold">Name</th>
                                    <th class="px-4 py-2 font-semibold">Role</th>
                                    <th class="px-4 py-2 font-semibold text-right">Coins to Credit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach($placement['participants'] as $pIdx => $p)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-4 py-2 text-slate-400">{{ $pIdx + 1 }}</td>
                                        <td class="px-4 py-2 font-bold text-slate-800">{{ $p['student_number'] }}</td>
                                        <td class="px-4 py-2 font-medium text-slate-900">{{ $p['name'] }}</td>
                                        <td class="px-4 py-2 text-slate-500 capitalize">{{ $p['role'] }}</td>
                                        <td class="px-4 py-2 font-bold text-emerald-700 text-right">+{{ $p['coins'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-4 py-3 text-xs font-mono text-slate-400 italic">
                        No participants registered for this team entry.
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="pt-2 border-t border-amber-200/60 flex items-center justify-between text-[11px] font-mono text-slate-500">
        <span>Rule: Reward applies individually to registered participating students only. Non-participating group members receive 0 coins.</span>
        <span class="font-semibold text-slate-700">Idempotency Protected</span>
    </div>
</div>
@endif
