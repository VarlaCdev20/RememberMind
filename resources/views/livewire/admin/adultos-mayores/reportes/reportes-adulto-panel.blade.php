<div>
    <div class="mb-6 rounded-[24px] border border-[#CBBBAA] bg-[#F2EBE3]/85 p-5 shadow-sm flex flex-col md:flex-row gap-4 items-end justify-between">
        <div class="flex-1">
            <h3 class="text-lg font-black text-[#2F3E5C] mb-1">Centro de Reportes y Evolución</h3>
            <p class="text-xs font-semibold text-[#2F3E5C]/60">Selecciona el periodo para filtrar los gráficos y la información de los reportes.</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div>
                <label class="block text-[10px] font-black uppercase text-[#2F3E5C]/60 mb-1">Desde</label>
                <input type="date" wire:model.live="fecha_inicio" class="rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-2 text-sm font-bold text-[#2F3E5C] outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase text-[#2F3E5C]/60 mb-1">Hasta</label>
                <input type="date" wire:model.live="fecha_fin" class="rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-2 text-sm font-bold text-[#2F3E5C] outline-none">
            </div>
        </div>
    </div>

    {{-- Error de fechas --}}
    @if($fecha_inicio && $fecha_fin && $fecha_inicio > $fecha_fin)
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-bold text-red-600">
            <i class="ph-bold ph-warning-circle"></i> La fecha de inicio no puede ser mayor a la fecha de fin.
        </div>
    @endif

    {{-- Cards de Reportes --}}
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 mb-8">
        @php
            $q = "?start_date={$fecha_inicio}&end_date={$fecha_fin}";
            $reportes = [
                ['icono' => 'ph-files', 'titulo' => 'Reporte Individual Integral', 'desc' => 'Consolidado administrativo, social y médico', 'color' => '#2F3E5C', 'bg' => 'bg-[#2F3E5C]/10', 'url' => route('admin.adultos-mayores.reporte-individual', $adultoMayor->cod_am)],
                ['icono' => 'ph-stethoscope', 'titulo' => 'Reporte Médico', 'desc' => 'Historial de atenciones, ficha médica', 'color' => '#E27D60', 'bg' => 'bg-[#E27D60]/10', 'url' => route('admin.adultos-mayores.reportes.especifico', [$adultoMayor->cod_am, 'medico']) . $q],
                ['icono' => 'ph-pill', 'titulo' => 'Reporte de Medicación', 'desc' => 'Tratamientos y tomas registradas', 'color' => '#D9A27C', 'bg' => 'bg-[#D9A27C]/20', 'url' => route('admin.adultos-mayores.reportes.especifico', [$adultoMayor->cod_am, 'medicacion']) . $q],
                ['icono' => 'ph-heartbeat', 'titulo' => 'Reporte Signos Vitales', 'desc' => 'Registro y evolución de SV', 'color' => '#C45F4B', 'bg' => 'bg-[#C45F4B]/10', 'url' => route('admin.adultos-mayores.reportes.especifico', [$adultoMayor->cod_am, 'signos']) . $q],
                ['icono' => 'ph-person-arms-spread', 'titulo' => 'Valoración Funcional Institucional', 'desc' => 'Niveles de dependencia registrados', 'color' => '#8EA17D', 'bg' => 'bg-[#8EA17D]/15', 'url' => route('admin.adultos-mayores.reportes.especifico', [$adultoMayor->cod_am, 'funcional']) . $q],
                ['icono' => 'ph-brain', 'titulo' => 'Evaluaciones Cognitivas', 'desc' => 'Puntajes de tamizaje e interpretación', 'color' => '#5B5F97', 'bg' => 'bg-[#5B5F97]/15', 'url' => route('admin.adultos-mayores.reportes.especifico', [$adultoMayor->cod_am, 'cognitivo']) . $q],
            ];
        @endphp

        @foreach($reportes as $rep)
            <div class="rounded-2xl border border-[#CBBBAA] bg-[#E7DDD2]/90 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $rep['bg'] }}" style="color: {{ $rep['color'] }}">
                        <i class="ph-fill {{ $rep['icono'] }} text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-[#2F3E5C]">{{ $rep['titulo'] }}</h4>
                        <p class="text-[10px] font-bold text-[#2F3E5C]/60 mb-3">{{ $rep['desc'] }}</p>
                        <div class="flex gap-2">
                            <a href="{{ $rep['url'] }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg bg-white/50 px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-[#2F3E5C] transition hover:bg-white border border-[#D5C7B9]">
                                <i class="ph-bold ph-eye"></i> Visualizar
                            </a>
                            <a href="{{ $rep['url'] }}&format=pdf" class="inline-flex items-center gap-1 rounded-lg bg-[#2F3E5C] px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-white transition hover:bg-[#1F2E4C]">
                                <i class="ph-bold ph-file-pdf"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Gráficos de Evolución --}}
    <h3 class="text-sm font-black uppercase tracking-widest text-[#2F3E5C]/60 mb-4 border-b border-[#D5C7B9]/50 pb-2">
        <i class="ph-bold ph-trend-up"></i> Gráficos de Evolución Histórica
    </h3>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-[#CBBBAA] bg-[#E7DDD2]/90 p-4 shadow-sm"
             x-data="{ chart: null }"
             x-init="
                chart = new Chart($refs.canvasSignos, {
                    type: 'line',
                    data: {
                        labels: @js($chartSignos['labels']),
                        datasets: [
                            { label: 'Frecuencia Cardíaca', data: @js($chartSignos['fc']), borderColor: '#D96F58', backgroundColor: 'rgba(217,111,88,0.1)', tension: 0.3, fill: true },
                            { label: 'Saturación O2', data: @js($chartSignos['sat']), borderColor: '#5B5F97', backgroundColor: 'transparent', tension: 0.3 },
                            { label: 'Temperatura', data: @js($chartSignos['temp']), borderColor: '#8EA17D', backgroundColor: 'transparent', tension: 0.3 }
                        ]
                    },
                    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 10, family: 'Outfit' } } } } }
                });
                $watch('$wire.chartSignos', value => {
                    chart.data.labels = value.labels;
                    chart.data.datasets[0].data = value.fc;
                    chart.data.datasets[1].data = value.sat;
                    chart.data.datasets[2].data = value.temp;
                    chart.update();
                });
             "
        >
            <h4 class="text-xs font-black text-[#2F3E5C] mb-3">Evolución de Signos Vitales</h4>
            <div class="relative h-64 w-full">
                <canvas x-ref="canvasSignos"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-[#CBBBAA] bg-[#E7DDD2]/90 p-4 shadow-sm"
             x-data="{ chart: null }"
             x-init="
                chart = new Chart($refs.canvasCognitivo, {
                    type: 'bar',
                    data: {
                        labels: @js($chartCognitivo['labels']),
                        datasets: [
                            { label: 'Puntaje Obtenido', data: @js($chartCognitivo['puntajes']), backgroundColor: '#5B5F97', borderRadius: 6 }
                        ]
                    },
                    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 10, family: 'Outfit' } } } }, scales: { y: { beginAtZero: true } } }
                });
                $watch('$wire.chartCognitivo', value => {
                    chart.data.labels = value.labels;
                    chart.data.datasets[0].data = value.puntajes;
                    chart.update();
                });
             "
        >
            <h4 class="text-xs font-black text-[#2F3E5C] mb-3">Evolución de Evaluaciones Cognitivas</h4>
            <div class="relative h-64 w-full">
                <canvas x-ref="canvasCognitivo"></canvas>
            </div>
        </div>
    </div>
</div>
