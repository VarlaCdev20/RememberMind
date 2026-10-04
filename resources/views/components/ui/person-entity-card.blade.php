@props(['entity' => 'resident'])
<article {{ $attributes->class(['rm-person-entity-card']) }} data-entity="{{ $entity }}">{{ $slot }}</article>
