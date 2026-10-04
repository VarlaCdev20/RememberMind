@props(['icon', 'title', 'value', 'detail' => null, 'tone' => 'calm'])
<article class="rm-resident-summary__current-card rm-resident-summary__current-card--{{ $tone }}">
    <div class="rm-resident-summary__card-label"><i class="ph-bold {{ $icon }}" aria-hidden="true"></i><span>{{ $title }}</span></div>
    <strong>{{ $value }}</strong>
    @if($detail)<p>{{ $detail }}</p>@endif
</article>
