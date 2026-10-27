@props(['item'])
@if($item['conflicto'] ?? false)
    <div role="note">
        <p>{{ $item['advertencia'] }}</p>
        <ul>
            @foreach($item['documentos_candidatos'] as $candidato)
                <li>{{ $candidato->nombre }} · {{ ucfirst(mb_strtolower(str_replace('_', ' ', $candidato->estado))) }}</li>
            @endforeach
        </ul>
    </div>
@endif
