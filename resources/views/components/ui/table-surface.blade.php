<div {{ $attributes->class(['rm-table-surface']) }} data-rm-ui="table-surface">
    {{ $slot }}
    @if(isset($footer)){{ $footer }}@endif
</div>
