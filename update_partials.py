import os

show_blade_path = r'c:\laragon\www\RememberMind_F1\resources\views\admin\adultos-mayores\show.blade.php'
with open(show_blade_path, 'r', encoding='utf-8') as f:
    show_content = f.read()

# Update the folders array and the x-show contents in show.blade.php
new_carpetas = """ @php
 $carpetas = [
 ['id' => 'resumen', 'icon' => 'ph-identification-card', 'label' => 'Resumen', 'status' => 'Completo'],
 ['id' => 'familiares', 'icon' => 'ph-users-three', 'label' => 'Familia y red de apoyo', 'status' => $totalFamiliares.' vinculados'],
 ['id' => 'seguimiento', 'icon' => 'ph-clipboard-text', 'label' => 'Seguimiento institucional', 'status' => ($totalObservaciones + $totalAtenciones).' registros'],
 ['id' => 'salud', 'icon' => 'ph-heartbeat', 'label' => 'Resumen clínico', 'status' => $fichasMedicas->isNotEmpty() ? 'Con datos' : 'Sin datos'],
 ['id' => 'evaluaciones', 'icon' => 'ph-brain', 'label' => 'Evaluaciones geriátricas', 'status' => $totalEvaluaciones > 0 ? $totalEvaluaciones.' pruebas' : 'Sin pruebas'],
 ['id' => 'historial', 'icon' => 'ph-clock-counter-clockwise', 'label' => 'Historial individual', 'status' => 'Disponible'],
 ['id' => 'preventivo', 'icon' => 'ph-shield-check', 'label' => 'Resultados preventivos', 'status' => 'Pendiente'],
 ['id' => 'documentos', 'icon' => 'ph-folder-open', 'label' => 'Documentos', 'status' => $totalDocumentos.' archivos'],
 ['id' => 'reportes', 'icon' => 'ph-file-pdf', 'label' => 'Reportes', 'status' => 'Disponibles'],
 ];
 @endphp"""

# Find the old carpetas array
import re
show_content = re.sub(r'@php\s+\$carpetas = \[.*?\];\s+@endphp', new_carpetas, show_content, flags=re.DOTALL)

# Default tab
show_content = show_content.replace("carpetaActiva: 'identificacion',", "carpetaActiva: 'resumen',")

# Update content includes
new_includes = """ <div class="flex-1 min-w-0">
 <div x-show="carpetaActiva === 'resumen'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._resumen')
 </div>
 
 <div x-show="carpetaActiva === 'familiares'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._familiares')
 </div>

 <div x-show="carpetaActiva === 'seguimiento'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._seguimiento-observaciones')
 </div>

 <div x-show="carpetaActiva === 'salud'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._salud-medica')
 </div>

 <div x-show="carpetaActiva === 'evaluaciones'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._evaluaciones-cognitivas')
 </div>

 <div x-show="carpetaActiva === 'historial'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._historial-individual')
 </div>

 <div x-show="carpetaActiva === 'preventivo'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._resultados-preventivos')
 </div>

 <div x-show="carpetaActiva === 'documentos'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._documentos')
 </div>

 <div x-show="carpetaActiva === 'reportes'" x-transition style="display: none;">
 @include('admin.adultos-mayores.show._reportes')
 </div>
 </div>"""

show_content = re.sub(r'<div class="flex-1 min-w-0">.*?</div>\s+</div>', new_includes + "\n </div>", show_content, flags=re.DOTALL)

# Ensure "tab === '...'" in individual files works by matching it to "carpetaActiva === '...'". Wait!
# The partials use `x-show="tab === 'evaluaciones'"`
# But `show.blade.php` uses `carpetaActiva`. If the partials expect `tab`, we must change `carpetaActiva` to `tab` in show.blade.php OR just update the partials to remove their `x-show` since the parent `show.blade.php` already wraps them in `x-show="carpetaActiva === ..."`.
# Let's remove the wrapper `x-show` in the partials if they exist, or just change `carpetaActiva` to `tab` in `show.blade.php`.
show_content = show_content.replace('carpetaActiva', 'tab')

with open(show_blade_path, 'w', encoding='utf-8') as f:
    f.write(show_content)


# Create _historial-individual.blade.php
historial_path = r'c:\laragon\www\RememberMind_F1\resources\views\admin\adultos-mayores\show\_historial-individual.blade.php'
with open(historial_path, 'w', encoding='utf-8') as f:
    f.write('''<section class="space-y-6">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-borde pb-5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-fondo-panel text-parrafo">
                <i class="ph-fill ph-clock-counter-clockwise text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-titulo">Historial individual</h2>
                <p class="text-sm font-semibold text-apoyo">Línea de tiempo del adulto mayor.</p>
            </div>
        </div>
    </div>

    <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm" x-data="{ filtroHistorial: 'todo' }">
        <div class="mb-6 flex flex-wrap gap-2">
            <template x-for="f in ['todo', 'salud', 'seguimiento', 'evaluaciones', 'actividades', 'documentos', 'estados']">
                <button type="button" @click="filtroHistorial = f"
                    class="rounded-lg border px-3 py-1.5 text-xs font-bold uppercase tracking-wider transition-colors"
                    :class="filtroHistorial === f ? 'bg-boton-acento text-inverso border-borde-focus' : 'bg-fondo-panel text-apoyo border-borde hover:bg-fondo-card'">
                    <span x-text="f"></span>
                </button>
            </template>
        </div>

        <div class="relative border-l-2 border-borde-suave ml-3 space-y-6">
            {{-- Fechas de ingreso --}}
            <div class="relative pl-6">
                <div class="absolute -left-[9px] top-1 h-4 w-4 rounded-full bg-emerald-500 ring-4 ring-fondo-card"></div>
                <p class="text-xs font-bold uppercase tracking-wide text-apoyo">{{ $adulto->fecha_ing ? $adulto->fecha_ing->format('d/m/Y') : 'N/D' }}</p>
                <div class="mt-2 rounded-xl bg-fondo-panel p-4 border border-borde-suave">
                    <h4 class="font-bold text-titulo">Ingreso a la institución</h4>
                    <p class="text-sm text-apoyo">Registro inicial en el sistema.</p>
                </div>
            </div>

            {{-- Observaciones y Bitacora (Ejemplo timeline mixto) --}}
            @foreach($observacionesActivas->take(5) as $obs)
            <div class="relative pl-6" x-show="filtroHistorial === 'todo' || filtroHistorial === 'seguimiento'">
                <div class="absolute -left-[9px] top-1 h-4 w-4 rounded-full bg-blue-500 ring-4 ring-fondo-card"></div>
                <p class="text-xs font-bold uppercase tracking-wide text-apoyo">{{ $obs->fecha->format('d/m/Y') }}</p>
                <div class="mt-2 rounded-xl bg-fondo-panel p-4 border border-borde-suave">
                    <h4 class="font-bold text-titulo">Observación Registrada</h4>
                    <p class="text-sm text-apoyo">{{ Str::limit($obs->observacion, 100) }}</p>
                </div>
            </div>
            @endforeach

            @foreach($evaluacionesActivas->take(5) as $eval)
            <div class="relative pl-6" x-show="filtroHistorial === 'todo' || filtroHistorial === 'evaluaciones'">
                <div class="absolute -left-[9px] top-1 h-4 w-4 rounded-full bg-indigo-500 ring-4 ring-fondo-card"></div>
                <p class="text-xs font-bold uppercase tracking-wide text-apoyo">{{ $eval->fecha_eval->format('d/m/Y') }}</p>
                <div class="mt-2 rounded-xl bg-fondo-panel p-4 border border-borde-suave">
                    <h4 class="font-bold text-titulo">Evaluación Geriátrica: {{ $eval->tipoEvaluacion->nombre ?? 'Prueba' }}</h4>
                    <p class="text-sm text-apoyo">Puntaje: {{ $eval->puntaje_total }} pts. Riesgo: {{ $eval->nivel_riesgo }}</p>
                </div>
            </div>
            @endforeach
            
            @foreach($signosVitales->take(3) as $sv)
            <div class="relative pl-6" x-show="filtroHistorial === 'todo' || filtroHistorial === 'salud'">
                <div class="absolute -left-[9px] top-1 h-4 w-4 rounded-full bg-red-500 ring-4 ring-fondo-card"></div>
                <p class="text-xs font-bold uppercase tracking-wide text-apoyo">{{ $sv->fecha->format('d/m/Y') }}</p>
                <div class="mt-2 rounded-xl bg-fondo-panel p-4 border border-borde-suave">
                    <h4 class="font-bold text-titulo">Signos Vitales Actualizados</h4>
                    <p class="text-sm text-apoyo">PA: {{ $sv->presion_arterial }}. FC: {{ $sv->frecuencia_cardiaca }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>''')


# Create _resultados-preventivos.blade.php
preventivo_path = r'c:\laragon\www\RememberMind_F1\resources\views\admin\adultos-mayores\show\_resultados-preventivos.blade.php'
with open(preventivo_path, 'w', encoding='utf-8') as f:
    f.write('''<section class="space-y-6">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-borde pb-5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-fondo-panel text-parrafo">
                <i class="ph-fill ph-shield-check text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-titulo">Resultados preventivos</h2>
                <p class="text-sm font-semibold text-apoyo">Sistema de alertas tempranas.</p>
            </div>
        </div>
    </div>

    <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-8 text-center shadow-sm relative overflow-hidden">
        <div class="absolute inset-0 bg-fondo-bloqueado/30 backdrop-blur-sm z-10 flex flex-col items-center justify-center rounded-[24px]">
            <i class="ph-bold ph-lock-key text-5xl text-meta mb-4"></i>
            <h3 class="text-2xl font-black text-titulo">Módulo en Desarrollo</h3>
            <p class="text-sm font-bold text-apoyo mt-2 max-w-md">El motor de cálculo de riesgo preventivo es un aporte futuro. Actualmente se encuentra en etapa de diseño.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 opacity-30 pointer-events-none">
            <div class="border border-borde rounded-xl p-6 bg-fondo-panel">
                <p class="text-[10px] font-bold uppercase text-apoyo">Nivel de Riesgo</p>
                <p class="text-3xl font-black text-amber-600 mt-2">Medio</p>
            </div>
            <div class="border border-borde rounded-xl p-6 bg-fondo-panel">
                <p class="text-[10px] font-bold uppercase text-apoyo">Alerta Preventiva</p>
                <p class="text-xl font-bold text-titulo mt-2">Riesgo de Caídas</p>
            </div>
            <div class="border border-borde rounded-xl p-6 bg-fondo-panel">
                <p class="text-[10px] font-bold uppercase text-apoyo">Acción Sugerida</p>
                <p class="text-sm font-bold text-titulo mt-2">Asistir en traslados y revisar valoración funcional.</p>
            </div>
        </div>
    </div>
</section>''')


# Fix evaluacion-cognitiva to Evaluaciones geriátricas
evaluaciones_path = r'c:\laragon\www\RememberMind_F1\resources\views\admin\adultos-mayores\show\_evaluaciones-cognitivas.blade.php'
with open(evaluaciones_path, 'r', encoding='utf-8') as f:
    eval_content = f.read()

eval_content = eval_content.replace('Resumen Cognitivo', 'Evaluaciones geriátricas')
eval_content = eval_content.replace('Evolución (Promedio)', 'Evolución Cognitiva')
eval_content = eval_content.replace('<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">', '''
    <!-- Áreas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="rounded-xl border border-borde-suave bg-fondo-panel p-5">
            <h3 class="font-bold text-titulo flex items-center gap-2 mb-2"><i class="ph-bold ph-brain text-boton-acento"></i> Área Cognitiva</h3>
            <p class="text-sm text-apoyo">Evaluaciones actuales registradas en el sistema (MoCA, MMSE).</p>
        </div>
        <div class="rounded-xl border border-borde-suave bg-fondo-panel p-5">
            <h3 class="font-bold text-titulo flex items-center gap-2 mb-2"><i class="ph-bold ph-wheelchair text-boton-acento"></i> Área Funcional</h3>
            <p class="text-sm text-apoyo">La valoración funcional actual se registra en el módulo de salud.</p>
        </div>
        <div class="rounded-xl border border-borde-suave bg-fondo-panel p-5 opacity-60">
            <h3 class="font-bold text-titulo flex items-center gap-2 mb-2"><i class="ph-bold ph-heart text-meta"></i> Área Afectiva</h3>
            <p class="text-sm text-apoyo">Futuro módulo para evaluación afectiva.</p>
        </div>
        <div class="rounded-xl border border-borde-suave bg-fondo-panel p-5 opacity-60">
            <h3 class="font-bold text-titulo flex items-center gap-2 mb-2"><i class="ph-bold ph-apple-logo text-meta"></i> Área Nutricional</h3>
            <p class="text-sm text-apoyo">Futuro módulo para evaluación nutricional.</p>
        </div>
    </div>
    
    <h3 class="text-lg font-bold text-titulo mt-6 mb-4">Métricas Cognitivas</h3>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">''')

# Let's remove any local wrapper of x-show="tab === 'evaluaciones'" if we want, or keep it. It's fine since it will just be double checked.
with open(evaluaciones_path, 'w', encoding='utf-8') as f:
    f.write(eval_content)


# Fix _salud-medica.blade.php
salud_path = r'c:\laragon\www\RememberMind_F1\resources\views\admin\adultos-mayores\show\_salud-medica.blade.php'
with open(salud_path, 'r', encoding='utf-8') as f:
    salud_content = f.read()

salud_content = salud_content.replace('Resumen de Salud y Seguimiento', 'Resumen clínico')
salud_content = salud_content.replace('Resumen de Ficha Médica', 'Ficha Médica')
salud_content = salud_content.replace('Resumen de Signos Vitales', 'Signos Vitales y Mediciones')
salud_content = salud_content.replace('Resumen de Medicación', 'Medicación')
salud_content = salud_content.replace('Resumen de Administración de Medicación', 'Administración de medicación')
salud_content = salud_content.replace('Resumen Geriátrico', 'Valoración Funcional')

with open(salud_path, 'w', encoding='utf-8') as f:
    f.write(salud_content)

# Fix _seguimiento-observaciones to "Seguimiento institucional"
seguimiento_path = r'c:\laragon\www\RememberMind_F1\resources\views\admin\adultos-mayores\show\_seguimiento-observaciones.blade.php'
if os.path.exists(seguimiento_path):
    with open(seguimiento_path, 'r', encoding='utf-8') as f:
        seg_content = f.read()
    seg_content = seg_content.replace('Seguimiento y Observaciones', 'Seguimiento institucional')
    with open(seguimiento_path, 'w', encoding='utf-8') as f:
        f.write(seg_content)

# Fix _familiares to "Familia y red de apoyo"
familia_path = r'c:\laragon\www\RememberMind_F1\resources\views\admin\adultos-mayores\show\_familiares.blade.php'
if os.path.exists(familia_path):
    with open(familia_path, 'r', encoding='utf-8') as f:
        fam_content = f.read()
    fam_content = fam_content.replace('Directorio de Familiares', 'Familia y red de apoyo')
    fam_content = fam_content.replace('Familiares y Contactos de Emergencia', 'Familia y red de apoyo')
    with open(familia_path, 'w', encoding='utf-8') as f:
        f.write(fam_content)

print("Updates applied successfully.")
