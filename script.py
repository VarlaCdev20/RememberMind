import os

# 1. Update gestion-institucional/index.blade.php
gestion_path = r'c:\laragon\www\RememberMind_F1\resources\views\admin\gestion-institucional\index.blade.php'
gestion_content = """<x-sistema-layout>
    <div class="relative mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="text-2xl font-black text-titulo">Gestión Institucional</h2>
            <p class="text-sm font-medium text-apoyo">Configuración central del Centro Geriátrico y organigrama.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Áreas Institucionales (Módulo Real) --}}
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-card">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ph-bold ph-buildings text-4xl text-boton-acento"></i>
                        <h3 class="font-bold text-lg text-titulo">Áreas institucionales</h3>
                    </div>
                    <p class="text-sm text-apoyo mb-6">Gestión de espacios, áreas internas y unidades operativas.</p>
                </div>
                <a href="{{ route('admin.areas-institucionales.index') }}" class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-fondo-hover px-4 py-2 text-sm font-bold text-boton-acento transition-colors hover:bg-boton-acento hover:text-white">
                    Ir a áreas institucionales <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>

            {{-- Turnos y Asignaciones (Módulo Real) --}}
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-card">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ph-bold ph-clock text-4xl text-boton-acento"></i>
                        <h3 class="font-bold text-lg text-titulo">Turnos y asignaciones</h3>
                    </div>
                    <p class="text-sm text-apoyo mb-6">Horarios, responsables, asignaciones y organización operativa.</p>
                </div>
                <a href="{{ route('admin.turnos-asignaciones.index') }}" class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-fondo-hover px-4 py-2 text-sm font-bold text-boton-acento transition-colors hover:bg-boton-acento hover:text-white">
                    Ir a turnos y asignaciones <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>

            {{-- Cargos administrativos --}}
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-card opacity-80">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ph-bold ph-briefcase text-4xl text-meta"></i>
                        <h3 class="font-bold text-lg text-titulo">Cargos administrativos</h3>
                    </div>
                    <p class="text-sm text-apoyo mb-6">El modelo de cargos ya existe. Gestión en desarrollo.</p>
                </div>
                <div class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-fondo-bloqueado px-4 py-2 text-sm font-bold text-apoyo cursor-not-allowed">
                    <i class="ph-bold ph-lock"></i> Próximamente
                </div>
            </div>

            {{-- Personal administrativo --}}
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-card opacity-80">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ph-bold ph-users text-4xl text-meta"></i>
                        <h3 class="font-bold text-lg text-titulo">Personal administrativo</h3>
                    </div>
                    <p class="text-sm text-apoyo mb-6">Se gestiona directamente desde Usuarios mediante el rol administrativo.</p>
                </div>
                <a href="{{ route('admin.usuarios.index') }}" class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-fondo-hover px-4 py-2 text-sm font-bold text-boton-acento transition-colors hover:bg-boton-acento hover:text-white">
                    Ir a Usuarios <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>

            {{-- Personal de salud --}}
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-card opacity-80">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ph-bold ph-user-circle-plus text-4xl text-meta"></i>
                        <h3 class="font-bold text-lg text-titulo">Personal de salud</h3>
                    </div>
                    <p class="text-sm text-apoyo mb-6">Se gestiona desde Usuarios mediante el rol de personal de salud.</p>
                </div>
                <a href="{{ route('admin.usuarios.index') }}" class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-fondo-hover px-4 py-2 text-sm font-bold text-boton-acento transition-colors hover:bg-boton-acento hover:text-white">
                    Ir a Usuarios <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>

            {{-- Especialidades --}}
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-card opacity-80">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ph-bold ph-certificate text-4xl text-meta"></i>
                        <h3 class="font-bold text-lg text-titulo">Especialidades</h3>
                    </div>
                    <p class="text-sm text-apoyo mb-6">El catálogo de especialidades médicas. Gestión en desarrollo.</p>
                </div>
                <div class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-fondo-bloqueado px-4 py-2 text-sm font-bold text-apoyo cursor-not-allowed">
                    <i class="ph-bold ph-lock"></i> Próximamente
                </div>
            </div>

            {{-- Estados y Catálogos (Placeholder) --}}
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-card opacity-70">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ph-bold ph-list-dashes text-4xl text-meta"></i>
                        <h3 class="font-bold text-lg text-titulo">Estados y catálogos</h3>
                    </div>
                    <p class="text-sm text-apoyo mb-6">Estados del adulto mayor, tipos de observación y evaluación.</p>
                </div>
                <div class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-fondo-bloqueado px-4 py-2 text-sm font-bold text-apoyo cursor-not-allowed">
                    <i class="ph-bold ph-lock"></i> Próximamente
                </div>
            </div>
        </div>
    </div>
</x-sistema-layout>"""
with open(gestion_path, 'w', encoding='utf-8') as f:
    f.write(gestion_content)


# 2. Update barra-lateral-sistema.blade.php
sidebar_path = r'c:\laragon\www\RememberMind_F1\resources\views\components\layout\barra-lateral-sistema.blade.php'
with open(sidebar_path, 'r', encoding='utf-8') as f:
    sidebar_content = f.read()

import re
# Fix active_routes for Adultos Mayores
old_adultos = """                    auth()->user()->can('adultos.ver') ? [
                        'label' => 'Registro integral', 
                        'route' => 'admin.adultos-mayores.index',
                        'active_routes' => ['admin.adultos-mayores.index', 'admin.adultos-mayores.create']
                    ] : null,
                    auth()->user()->can('adultos.ver') ? [
                        'label' => 'Ficha del adulto mayor', 
                        'route' => 'admin.adultos-mayores.index',
                        'active_routes' => ['admin.adultos-mayores.show', 'admin.adultos-mayores.edit']
                    ] : null,"""

new_adultos = """                    auth()->user()->can('adultos.ver') ? [
                        'label' => 'Registro integral', 
                        'route' => 'admin.adultos-mayores.index',
                        'active_routes' => ['admin.adultos-mayores.index', 'admin.adultos-mayores.create']
                    ] : null,
                    auth()->user()->can('adultos.ver') ? [
                        'label' => 'Ficha del adulto mayor', 
                        'route' => 'admin.adultos-mayores.index',
                        'active_routes' => ['admin.adultos-mayores.show', 'admin.adultos-mayores.edit']
                    ] : null,"""
# Already looks correct, but let's ensure Activities and Voluntariado uses real routes

sidebar_content = sidebar_content.replace(
    "['label' => 'Programación', 'route' => 'admin.actividades.programacion-placeholder']",
    "['label' => 'Programación', 'route' => 'admin.actividades.index']"
)

# And evaluations for salud-seguimiento
sidebar_content = sidebar_content.replace(
    "['label' => 'Evaluaciones geriátricas', 'route' => 'admin.salud-seguimiento.evaluaciones-geriatricas']",
    "['label' => 'Evaluaciones geriátricas', 'route' => 'admin.salud-seguimiento.evaluaciones-geriatricas.index']"
)
# Wait, evaluations-geriatricas inside salud-seguimiento has real route: admin.salud-seguimiento.evaluaciones-geriatricas.index or similar?
# Let's check from previous audit: Route::get('/evaluaciones-geriatricas', \App\Livewire\Admin\SaludSeguimiento\SaludSeguimientoListPanel::class)->name('evaluaciones-geriatricas.index');
# So yes, admin.salud-seguimiento.evaluaciones-geriatricas.index is the real one.

# What about reportes?
sidebar_content = sidebar_content.replace(
    "['label' => 'Reporte individual', 'route' => 'admin.reportes.individual']",
    "['label' => 'Reporte individual', 'route' => 'admin.reportes.adultos.preview']" # There is a real route for this.
)
# We don't want to change too much if they don't explicitly say the exact route name, but they said "admin.reportes.individual o ruta real existente".
# Actually, the user says "Si alguna ruta todavía no existe para exportaciones, dejar como próximamente".

with open(sidebar_path, 'w', encoding='utf-8') as f:
    f.write(sidebar_content)

# 3. Create placeholder views for Familia y Social
familia_social_dir = r'c:\laragon\www\RememberMind_F1\resources\views\admin\familia-social'
os.makedirs(familia_social_dir, exist_ok=True)

for view_name, title, desc in [
    ('ficha-social.blade.php', 'Ficha Social Global', 'El panel global de Ficha Social sirve para consulta y acceso rápido. La red de apoyo real y los datos detallados se gestionan desde la pestaña Familiares dentro del expediente individual de cada adulto mayor.'),
    ('red-apoyo.blade.php', 'Red de Apoyo Institucional', 'La red de apoyo ya se gestiona desde el módulo de familiares dentro del expediente de cada adulto mayor. Este panel servirá como orientador global.'),
    ('visitas.blade.php', 'Registro de Visitas', 'Las visitas de familiares e invitados se registrarán aquí globalmente para tener control de acceso a Casa Amandita.'),
]:
    with open(os.path.join(familia_social_dir, view_name), 'w', encoding='utf-8') as f:
        f.write(f'''<x-sistema-layout>
    <div class="relative mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-titulo">{title}</h2>
                <p class="text-sm font-medium text-apoyo">Panel de consulta global.</p>
            </div>
            <a href="{{{{ route('admin.adultos-mayores.index') }}}}" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-boton-principal px-5 py-2.5 text-sm font-bold text-boton-principalTexto transition-all hover:bg-boton-principalHover active:scale-95">
                <i class="ph-bold ph-users-four text-lg"></i>
                Ir a Registro Integral
            </a>
        </div>

        <div class="rounded-[24px] border border-borde bg-fondo-card p-8 text-center shadow-sm">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-fondo-card-calido">
                <i class="ph-bold ph-info text-4xl text-boton-acento"></i>
            </div>
            <h3 class="mb-2 text-xl font-bold text-titulo">Información Importante</h3>
            <p class="mb-6 text-sm text-apoyo max-w-2xl mx-auto leading-relaxed">
                {desc}
            </p>
        </div>
    </div>
</x-sistema-layout>''')


# 4. Create placeholder views for Salud Seguimiento
salud_dir = r'c:\laragon\www\RememberMind_F1\resources\views\admin\salud-seguimiento'
os.makedirs(salud_dir, exist_ok=True)

for view_name, title, desc in [
    ('resumen-clinico.blade.php', 'Resumen Clínico Global', 'La Ficha Médica, Medicación, Signos Vitales y Valoración Funcional se registran directamente en el Expediente del Adulto Mayor. Este panel sirve para consulta y control global.'),
    ('seguimiento-institucional.blade.php', 'Seguimiento Institucional', 'Control global de la salud en la institución. Los registros individuales están en cada ficha.'),
    ('resultados-preventivos.blade.php', 'Resultados Preventivos', 'Los resultados preventivos y de alerta son un aporte futuro (en desarrollo). Por ahora, las alertas pueden consultarse individualmente en el expediente.'),
]:
    with open(os.path.join(salud_dir, view_name), 'w', encoding='utf-8') as f:
        f.write(f'''<x-sistema-layout>
    <div class="relative mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-titulo">{title}</h2>
                <p class="text-sm font-medium text-apoyo">Panel global de enfermería y seguimiento.</p>
            </div>
            <a href="{{{{ route('admin.adultos-mayores.index') }}}}" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-boton-principal px-5 py-2.5 text-sm font-bold text-boton-principalTexto transition-all hover:bg-boton-principalHover active:scale-95">
                <i class="ph-bold ph-folder-open text-lg"></i>
                Ir a Expedientes
            </a>
        </div>

        <div class="rounded-[24px] border border-borde bg-fondo-card p-8 text-center shadow-sm">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-fondo-card-calido">
                <i class="ph-bold ph-stethoscope text-4xl text-boton-acento"></i>
            </div>
            <h3 class="mb-2 text-xl font-bold text-titulo">Aviso Arquitectónico</h3>
            <p class="mb-6 text-sm text-apoyo max-w-2xl mx-auto leading-relaxed">
                {desc}
            </p>
        </div>
    </div>
</x-sistema-layout>''')

# 5. Create audit doc
audit_dir = r'c:\laragon\www\RememberMind_F1\docs\auditorias'
os.makedirs(audit_dir, exist_ok=True)
audit_path = os.path.join(audit_dir, 'restructuracion-modulos-remembermind.md')
with open(audit_path, 'w', encoding='utf-8') as f:
    f.write('''# Reestructuración de Módulos - RememberMind

**Fecha:** 01/06/2026
**Objetivo:** Conectar lo funcional, eliminar duplicidad y establecer convenciones arquitectónicas claras sin duplicar CRUD ni romper el Expediente del Adulto Mayor.

## 1. Módulos Reales Detectados y Respetados
- **Usuarios & Autenticación:** `admin.usuarios.*`
- **Bitácora:** `admin.bitacora.index`
- **Expediente del Adulto Mayor:** `admin.adultos-mayores.*` con todas sus relaciones (`familiares`, `medicacion`, `signos-vitales`, etc.).

## 2. Decisiones Arquitectónicas Aplicadas

### A. Gestión Institucional
Se convirtió el placeholder de Gestión Institucional en un panel puente ("Hub").
- Conecta a los módulos reales de **Áreas Institucionales** (`admin.areas-institucionales.index`) y **Turnos/Asignaciones** (`admin.turnos-asignaciones.index`).
- El Personal de Salud y Personal Administrativo se enlaza de vuelta al módulo global de **Usuarios**, ya que son roles del sistema, evitando duplicar tablas o CRUDs.
- Cargos y Especialidades permanecen en estado "Próximamente", pero referenciando la existencia del modelo en la base de datos.

### B. Familia y Social
Se implementaron paneles informativos globales (`ficha-social`, `red-apoyo`). Se ha documentado estrictamente en la interfaz que la creación y gestión real de familiares ocurre DENTRO del Expediente Individual del Adulto Mayor. No se duplicará el CRUD de familiares aquí.

### C. Salud y Evaluación Geriátrica
Se mantuvieron los paneles globales de consulta, pero se establecieron advertencias en la UI indicando que la **Ficha Médica, Medicación y Signos Vitales** se registran desde la ficha clínica de cada residente.
- **Resultados Preventivos:** Permanece como placeholder seguro. Se confirma que es un motor futuro.

### D. Voluntariado
**Convención Oficial:** El módulo debe llamarse y enlazarse bajo `Voluntariado`.
- Se han conectado las rutas reales `.index` (`admin.voluntariado.voluntarios.index`, `admin.voluntariado.asignaciones.index`, `admin.voluntariado.asistencia.index`).
- **Advertencia de limpieza:** Existe una dualidad entre la carpeta física `admin/voluntarios/` y el prefijo de ruta `voluntariado`. La carpeta oficial que se consolidará en el futuro será `admin/voluntariado/`, pendiente a limpieza destructiva posterior.

### E. Actividades
El menú lateral ahora utiliza las rutas reales del módulo Livewire:
- `admin.actividades.index` (reemplazando el placeholder de programación)
- `admin.actividades.participacion`
- `admin.actividades.reportes`

## 3. Placeholders y Rutas Duplicadas Corregidas
- Las rutas `Route::view` que duplicaban módulos Livewire funcionales en `web.php` han sido renombradas con sufijos como `-placeholder` (ej. `programacion-placeholder`) o comentadas para evitar choques en el Name Router de Laravel.

## 4. Pendientes Backend
- Conversión de `AdultoMayorController@show` a componentes Livewire de carga asíncrona para mejorar la velocidad (Lazy Load).
- Implementación del motor de cálculo de riesgo (Resultados Preventivos).
- Refactorización de la carpeta física de Voluntarios.
''')
