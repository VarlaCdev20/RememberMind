@props(['label', 'count' => null, 'active' => false, 'action'])
<button type="button" wire:click="{{ $action }}" aria-pressed="{{ $active ? 'true' : 'false' }}" {{ $attributes->class(['rm-filter-chip', 'rm-resident-directory__filter-chip', 'is-active' => $active]) }}>{{ $label }}{{ $count !== null ? ' ('.$count.')' : '' }}</button>
