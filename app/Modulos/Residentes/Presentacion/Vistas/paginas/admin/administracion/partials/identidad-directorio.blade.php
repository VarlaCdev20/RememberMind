<div class="rm-residents-identity">
    @if($registro->foto)<img src="{{ asset('storage/'.$registro->foto) }}" alt="" loading="lazy" width="52" height="52">@else<span aria-hidden="true">{{ collect(explode(' ', $registro->titulo))->filter()->take(2)->map(fn ($parte) => mb_substr($parte, 0, 1))->implode('') }}</span>@endif
    <div><h3><a wire:navigate href="{{ $enlace(['residente' => $registro->codigo]) }}">{{ $registro->titulo }}</a></h3><p>{{ $registro->codigo }}{{ $registro->documento ? ' · CI '.$registro->documento : '' }}</p></div>
</div>
