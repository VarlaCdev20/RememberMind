@props([
    'cabeceras' => [],
    'vacio' => 'No hay registros disponibles en este momento.',
    'iconoVacio' => 'ph-folder-open',
])

<div {{ $attributes->merge(['class' => 'enf-table-container shadow-sm']) }}>
    <table class="enf-table">
        @if(count($cabeceras) > 0 || isset($head))
            <thead>
                <tr>
                    @if(isset($head))
                        {{ $head }}
                    @else
                        @foreach($cabeceras as $cabecera)
                            <th>{{ $cabecera }}</th>
                        @endforeach
                    @endif
                </tr>
            </thead>
        @endif

        <tbody>
            @if(trim($slot) === '')
                <tr>
                    <td colspan="{{ max(count($cabeceras), 1) }}" class="text-center py-10">
                        <div class="flex flex-col items-center justify-center text-[#6F7B8F]">
                            <i class="ph-light {{ $iconoVacio }} text-3xl mb-2 opacity-60"></i>
                            <p class="text-xs">{{ $vacio }}</p>
                        </div>
                    </td>
                </tr>
            @else
                {{ $slot }}
            @endif
        </tbody>
    </table>

    @if(isset($pagination))
        <div class="p-3 border-t border-[#E3D6C8] bg-[#F4ECE3]/30">
            {{ $pagination }}
        </div>
    @endif
</div>
