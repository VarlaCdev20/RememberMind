<section class="dash-anim rounded-[2rem] border border-[#C7B5A3] bg-[#E6DDD3]/88 p-5 shadow-[0_16px_38px_rgba(47,62,92,0.12)] backdrop-blur-xl">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <span class="text-[11px] font-black uppercase tracking-widest text-terracota">
                Estadísticas generales
            </span>

            <h2 class="text-xl font-black text-azul-profundo md:text-2xl">
                Indicadores administrativos
            </h2>
        </div>

        <p class="max-w-md text-xs font-bold leading-5 text-azul-profundo/55">
            Lectura visual de usuarios, actividad mensual, registros y seguimiento institucional.
        </p>
    </div>

    <div class="grid gap-4 xl:grid-cols-4">
        <div class="xl:col-span-2">
            <x-ui.grafico-dashboard
                id="graficoUsuariosRol"
                titulo="Usuarios por rol"
                subtitulo="Distribución de accesos"
                icono="ph-chart-bar"
                color="terracota"
            />
        </div>

        <x-ui.grafico-dashboard
            id="graficoActividadMensual"
            titulo="Actividad mensual"
            subtitulo="Movimiento del sistema"
            icono="ph-chart-line-up"
            color="verde"
        />

        <x-ui.grafico-dashboard
    id="graficoCumplimientoAdmin"
    titulo="Cumplimiento administrativo"
    subtitulo="Estado de carga y vinculación de datos clave"
    icono="ph-clipboard-text"
    color="azul"
/>
    </div>
</section>