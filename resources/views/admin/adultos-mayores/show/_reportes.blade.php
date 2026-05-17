{{-- TAB REPORTES DE EVOLUCIÓN --}}
<section
    x-cloak
    x-show="tab === 'reportes'"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    class="space-y-6"
>
    @livewire('admin.adultos-mayores.reportes.reportes-adulto-panel', ['adultoMayor' => $adulto])
</section>
