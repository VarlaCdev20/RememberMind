@hasanyrole('SUPERADMINISTRADOR|PSICOLOGO/A')
@can('instrumentos.ver')
@can('aplicaciones_instrumento.ver')
@can('viewAny', \App\Models\Residente::class)
<x-ui.section-card title="Explorar instrumentos y guías" subtitle="Accede a las cuatro áreas de valoración desde esta pantalla" icon="ph-books">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="text-sm text-[var(--rm-text-secondary)] max-w-2xl">Consulta el catálogo, la biblioteca de investigación y las guías de registro. Las aplicaciones y la valoración profesional conservan su recorrido y autorización.</p>
        <x-ui.avatar-guia estado="explicacion" mensaje="Elige un área para encontrar sus instrumentos y ayudas. La valoración funcional general continúa debajo." />
    </div>
    <nav class="rm-valoraciones-areas mt-4" aria-label="Explorar instrumentos por área">
        <a href="{{ route('admin.psicologia.evaluacion.cognitiva') }}"><i class="ph-bold ph-brain" aria-hidden="true"></i>Cognitiva</a>
        <a href="{{ route('admin.psicologia.evaluacion.afectiva') }}"><i class="ph-bold ph-heart" aria-hidden="true"></i>Afectiva</a>
        <a href="{{ route('admin.psicologia.evaluacion.funcionamiento') }}"><i class="ph-bold ph-person-simple-walk" aria-hidden="true"></i>Funcionamiento</a>
        <a href="{{ route('admin.psicologia.evaluacion.entorno') }}"><i class="ph-bold ph-users-four" aria-hidden="true"></i>Entorno y red social</a>
    </nav>
</x-ui.section-card>
@endcan
@endcan
@endcan
@endhasanyrole
