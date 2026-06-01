<section class="rm-card rounded-[2rem] p-5 backdrop-blur-xl">
 <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
 <div>
 <span class="text-[11px] font-bold uppercase tracking-widest text-boton-acento">
 Estadísticas generales
 </span>
 <h2 class="text-xl font-extrabold text-titulo md:text-2xl">
 Indicadores institucionales
 </h2>
 </div>
 <p class="max-w-md text-xs font-bold leading-5 text-meta">
 Distribución de residentes por estado y composición del equipo institucional.
 </p>
 </div>

 <div class="grid gap-4 xl:grid-cols-2">
 <x-ui.grafico-dashboard
 id="graficoAdultosPorEstado"
 titulo="Adultos mayores por estado"
 subtitulo="Distribución de residentes"
 icono="ph-users-three"
 color="azul"
 />

 <x-ui.grafico-dashboard
 id="graficoEquipoInstitucional"
 titulo="Composición del equipo"
 subtitulo="Personal de salud, admin y voluntarios"
 icono="ph-buildings"
 color="marron"
 />
 </div>
</section>
