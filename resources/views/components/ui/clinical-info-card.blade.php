@props(['icon', 'title', 'value', 'detail' => null])
<article class="rm-resident-summary__info-card">
    <div class="rm-resident-summary__card-label"><i class="ph-bold {{ $icon }}" aria-hidden="true"></i><span>{{ $title }}</span></div>
    <strong>{{ $value }}</strong>
    @if($detail)<p>{{ $detail }}</p>@endif
</article>
