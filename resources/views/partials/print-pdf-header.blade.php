<div class="print-official-header w-full mb-4 {{ $class ?? '' }}">
    <div class="w-full pb-2 text-center">
        <img src="{{ asset('images/print-pdf-header.svg') }}"
             alt="Markaz Cultural Festival"
             class="w-full h-auto max-h-24 sm:max-h-28 object-contain block mx-auto"
             style="max-height: 110px;">
    </div>

    @if(!empty($title) || !empty($group) || !empty($subtitle) || !empty($filterText) || !empty($extraMeta))
        <div class="flex items-end justify-between border-t border-b-2 border-slate-900 py-2 mt-1 gap-4 text-xs font-mono">
            <div class="min-w-0">
                @if(!empty($title))
                    <h1 class="text-sm sm:text-base font-black font-sora text-slate-900 uppercase tracking-tight leading-tight">{{ $title }}</h1>
                @endif
                @if(!empty($subtitle))
                    <p class="text-[11px] text-slate-600 font-sans mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>

            <div class="text-right text-[11px] text-slate-600 space-y-0.5 shrink-0 font-mono">
                @if(!empty($group))
                    <div class="font-bold text-slate-900 text-xs font-sora">{{ $group->name }} ({{ $group->code }})</div>
                @endif
                <div>Date: {{ $date ?? now()->format('d M Y, h:i A') }}</div>
                @if(!empty($filterText))
                    <div class="text-slate-800 font-bold">{{ $filterText }}</div>
                @endif
                @if(!empty($extraMeta) && is_array($extraMeta))
                    @foreach($extraMeta as $metaLabel => $metaValue)
                        <div>{{ $metaLabel }}: <span class="font-bold text-slate-900">{{ $metaValue }}</span></div>
                    @endforeach
                @endif
            </div>
        </div>
    @else
        <div class="border-b-2 border-slate-900 mt-1"></div>
    @endif
</div>
