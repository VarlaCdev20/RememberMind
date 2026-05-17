{{-- TAB SALUD MÉDICA --}}
<section
    x-show="tab === 'salud'"
    style="display: none;"
    x-transition.opacity.duration.250ms
    class="space-y-6"
>
    <!-- Los componentes Livewire renderizan sus propias tarjetas (listas) y sus modales -->
    <livewire:admin.adultos-mayores.salud.ficha-medica-adulto-modal :cod_am="$adulto->cod_am" />
    <livewire:admin.adultos-mayores.salud.medicacion-adulto-modal :cod_am="$adulto->cod_am" />
    <livewire:admin.adultos-mayores.salud.signos-vitales-adulto-modal :cod_am="$adulto->cod_am" />
    <livewire:admin.adultos-mayores.salud.valoracion-funcional-adulto-modal :cod_am="$adulto->cod_am" />
    <livewire:admin.adultos-mayores.salud.historial-estado-adulto-panel :cod_am="$adulto->cod_am" />
    
    <!-- Este modal es llamado globalmente -->
    <livewire:admin.adultos-mayores.salud.administracion-medicacion-modal :cod_am="$adulto->cod_am" />
</section>

            