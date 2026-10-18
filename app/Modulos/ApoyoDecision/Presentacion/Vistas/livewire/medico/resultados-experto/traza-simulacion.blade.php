<details class="rm-expert-supplement" @if($abierto ?? false) open @endif>
    <summary>Decisiones de reglas conservadas</summary>
    <div class="space-y-3 mt-3 text-sm">
        @forelse($detalle['reglas'] as $regla)
            <div class="break-words"><p class="font-semibold">{{ $regla['codigo'] }} · {{ $regla['estado'] }}</p><p>{{ $regla['nombre'] }}</p>
                @foreach($regla['condiciones'] as [$codigoCondicion, $estadoCondicion])<p>{{ $codigoCondicion }} · {{ $estadoCondicion }}</p>@endforeach
            </div>
        @empty
            <p>No hay reglas descriptivas configuradas para esta área. Su referencia de dominio es independiente y artificial.</p>
        @endforelse
        <p>Estas reglas describen coincidencias de los antecedentes ficticios. No acreditan alteración objetiva, independencia funcional ni criterios Petersen.</p>
    </div>
</details>
