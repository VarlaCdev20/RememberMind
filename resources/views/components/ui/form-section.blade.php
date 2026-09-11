@props(['title', 'description' => null, 'columns' => 2])
<fieldset class="rm-form-section">
    <legend class="px-2 text-sm font-black text-titulo">{{ $title }}</legend>
    @if($description)<p class="mb-4 text-xs font-semibold text-apoyo">{{ $description }}</p>@endif
    <div @class(['grid gap-4', 'md:grid-cols-2' => $columns === 2, 'md:grid-cols-3' => $columns === 3])>{{ $slot }}</div>
</fieldset>
