{{-- Solo recibe proyecciones autorizadas sin respuestas instrumentales. --}}
<dl class="rm-lab-data">
    @forelse($datos as $campo => $valor)
        <div>
            <dt>{{ is_int($campo) ? 'Elemento '.($campo + 1) : ucfirst(str_replace('_', ' ', $campo)) }}</dt>
            <dd>
                @if(is_array($valor))
                    @if($valor === []) <span class="text-rm-secondary">Sin elementos registrados.</span>
                    @else @include('livewire.superadministrador.sistema-experto.datos', ['datos' => $valor]) @endif
                @else
                    {{ \App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarResultadoLaboratorio::texto($valor) }}
                @endif
            </dd>
        </div>
    @empty
        <div><dd class="text-rm-secondary">Sin datos disponibles en este contrato.</dd></div>
    @endforelse
</dl>
