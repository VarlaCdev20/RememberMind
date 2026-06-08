import re

with open('resources/views/livewire/admin/admisiones/preadmision-wizard.blade.php', 'r') as f:
    content = f.read()

# 1. New Wrapper and Header
new_header_and_stepper = """<div class="fixed inset-0 z-[100] flex items-center justify-center bg-fondo/80 p-4 backdrop-blur-md">
    <div x-data="{ isDirty: false }" x-on:input="isDirty = true" x-on:change="isDirty = true" class="max-w-5xl mx-auto w-full max-h-[85vh] flex flex-col p-3 md:p-4 bg-white rounded-xl shadow-xl overflow-hidden relative">
        <!-- Header Compacto -->
        <div class="flex items-start justify-between mb-3 border-b border-borde/50 pb-2">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento shadow-inner border border-boton-acento/20">
                    <i class="ph-fill ph-file-plus text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-titulo leading-tight">Registrar preadmision</h3>
                    <p class="text-xs font-semibold text-apoyo mt-0.5">Registra la solicitud inicial y prepara el caso para valoracion</p>
                </div>
            </div>
            <a href="{{ route('admin.admisiones.preadmisiones') }}" class="w-6 h-6 flex items-center justify-center rounded-full bg-fondo text-apoyo hover:bg-estado-peligroBg hover:text-estado-peligro transition-colors">
                <i class="ph-bold ph-x text-lg"></i>
            </a>
        </div>

        @php
            $pasoActualsLista = [
                1 => 'Identidad',
                2 => 'Direccion',
                3 => 'Familiar',
                4 => 'Caso',
                5 => 'Documentos',
                6 => 'Asignacion'
            ];
            
            // Simular errores para el stepper
            $pasosErrores = [
                1 => $errors->has('nombres') || $errors->has('ap_paterno') || $errors->has('ap_materno') || $errors->has('ci') || $errors->has('expedicion_ci') || $errors->has('fecha_nac') || $errors->has('genero') || $errors->has('estado_civil') || $errors->has('telefono') || $errors->has('celular'),
                2 => $errors->has('departamento_residencia') || $errors->has('ciudad_municipio') || $errors->has('zona') || $errors->has('calle') || $errors->has('direccion_referencia'),
                3 => $errors->has('familiar_nombres') || $errors->has('familiar_ap_paterno') || $errors->has('familiar_ap_materno') || $errors->has('familiar_ci') || $errors->has('familiar_parentesco') || $errors->has('familiar_celular') || $errors->has('familiar_correo') || $errors->has('familiar_direccion'),
                4 => $errors->has('motivo_ingreso') || $errors->has('procedencia_ingreso') || $errors->has('tipo_ingreso') || $errors->has('permanencia') || $errors->has('prioridad') || $errors->has('descripcion_caso'),
                5 => $errors->has('doc_ci_adulto') || $errors->has('doc_ci_familiar') || $errors->has('doc_solicitud_ingreso'),
                6 => $errors->has('enfermero_id')
            ];
        @endphp

        <!-- Stepper Compacto -->
        <div class="mb-3">
            <div class="relative flex items-center justify-between w-full pb-2">
                <!-- Linea de fondo -->
                <div class="absolute left-4 right-4 top-3 transform -translate-y-1/2 h-[3px] bg-borde/40 rounded-full z-0"></div>
                <!-- Linea de progreso -->
                <div class="absolute left-4 top-3 transform -translate-y-1/2 h-[3px] bg-boton-acento rounded-full z-0 transition-all duration-500 ease-out shadow-[0_0_8px_rgba(63,125,90,0.4)]" style="width: calc({{ (($paso - 1) / 5) * 100 }}% - 2rem)"></div>
                
                @foreach($pasoActualsLista as $num => $nombre)
                    <div class="relative z-10 flex flex-col items-center group">
                        @php
                            $hasError = $pasosErrores[$num] ?? false;
                            $isCompleted = $paso > $num;
                            $isActive = $paso == $num;
                        @endphp
                        
                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-[9px] transition-all duration-300 {{ 
                            $hasError 
                                ? 'bg-estado-peligroBg text-estado-peligro border-2 border-estado-peligro' 
                                : ($isActive 
                                    ? 'bg-boton-acento text-white ring-4 ring-boton-acento/20 scale-105 shadow-md' 
                                    : ($isCompleted 
                                        ? 'bg-boton-acento text-white hover:bg-boton-acentoHover' 
                                        : 'bg-white text-apoyo/40 border-2 border-borde/60 hover:border-apoyo/30'))
                        }}">
                            @if($hasError)
                                <i class="ph-bold ph-warning text-sm"></i>
                            @elseif($isCompleted)
                                <i class="ph-bold ph-check text-sm"></i>
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        
                        <span class="absolute top-7 text-[9px] font-bold uppercase tracking-wider whitespace-nowrap transition-colors duration-300 {{ 
                            $hasError 
                                ? 'text-estado-peligro' 
                                : ($isActive 
                                    ? 'text-boton-acento font-black scale-105 origin-top' 
                                    : ($isCompleted 
                                        ? 'text-titulo/70' 
                                        : 'text-apoyo/40')) 
                        }} hidden sm:block">{{ $nombre }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Form Body -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden pr-2 space-y-4 pb-2 custom-scrollbar">"""

# We need to replace everything from the start up to the start of `@if ($paso === 1)`
content = re.sub(r'<div class="fixed inset-0.*?@if \(\$paso === 1\)', new_header_and_stepper + '\n        @if ($paso === 1)', content, flags=re.DOTALL)

# Now, we need to change the sections inside each step to match the compact design.
# Original: <div class="space-y-5">\n                <div>\n                    <h2 class="text-lg font-bold text-titulo">Identificacion y datos personales</h2>\n                    <p class="text-sm text-apoyo">Datos minimos de la persona solicitante antes de su admision oficial.</p>\n                </div>
# We want: 
# <div class="space-y-3 animate-fade-in">
#     <h4 class="text-sm font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">
#         <i class="ph-bold ph-user text-boton-acento"></i> 1. Identificacion y datos personales
#     </h4>

content = content.replace(
    '<div class="space-y-5">\n                <div>\n                    <h2 class="text-lg font-bold text-titulo">Identificacion y datos personales</h2>\n                    <p class="text-sm text-apoyo">Datos minimos de la persona solicitante antes de su admision oficial.</p>\n                </div>',
    '<div class="space-y-3 animate-fade-in">\n                <h4 class="text-sm font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">\n                    <i class="ph-bold ph-user text-boton-acento"></i> 1. Identificacion y datos personales\n                </h4>'
)

content = content.replace(
    '<div class="space-y-5">\n                <div>\n                    <h2 class="text-lg font-bold text-titulo">Direccion</h2>\n                    <p class="text-sm text-apoyo">Ubicacion de referencia para contacto y continuidad del caso.</p>\n                </div>',
    '<div class="space-y-3 animate-fade-in">\n                <h4 class="text-sm font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">\n                    <i class="ph-bold ph-map-pin text-boton-acento"></i> 2. Direccion de referencia\n                </h4>'
)

content = content.replace(
    '<div class="space-y-5">\n                <div>\n                    <h2 class="text-lg font-bold text-titulo">Familiar responsable</h2>\n                    <p class="text-sm text-apoyo">Persona que responde por la solicitud inicial y firma la documentacion.</p>\n                </div>',
    '<div class="space-y-3 animate-fade-in">\n                <h4 class="text-sm font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">\n                    <i class="ph-bold ph-users text-boton-acento"></i> 3. Familiar responsable\n                </h4>'
)

content = content.replace(
    '<div class="space-y-5">\n                <div>\n                    <h2 class="text-lg font-bold text-titulo">Datos del caso</h2>\n                    <p class="text-sm text-apoyo">Motivo, procedencia y prioridad de la solicitud de ingreso.</p>\n                </div>',
    '<div class="space-y-3 animate-fade-in">\n                <h4 class="text-sm font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">\n                    <i class="ph-bold ph-file-text text-boton-acento"></i> 4. Datos del caso\n                </h4>'
)

content = content.replace(
    '<div class="space-y-5">\n                <div>\n                    <h2 class="text-lg font-bold text-titulo">Documentos iniciales e institucionales</h2>\n                    <p class="text-sm text-apoyo">Carga los documentos recibidos. Los documentos institucionales quedan registrados como generados por el sistema.</p>\n                </div>',
    '<div class="space-y-3 animate-fade-in">\n                <h4 class="text-sm font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">\n                    <i class="ph-bold ph-folder-open text-boton-acento"></i> 5. Documentos iniciales e institucionales\n                </h4>'
)

content = content.replace(
    '<div class="space-y-5">\n                <div>\n                    <h2 class="text-lg font-bold text-titulo">Asignacion y confirmacion</h2>\n                    <p class="text-sm text-apoyo">Selecciona enfermeria para continuar con valoracion inicial. El estado final sera preadmision asignada.</p>\n                </div>',
    '<div class="space-y-3 animate-fade-in">\n                <h4 class="text-sm font-bold text-titulo border-b border-borde pb-2 flex items-center gap-2">\n                    <i class="ph-bold ph-check-circle text-boton-acento"></i> 6. Asignacion y confirmacion\n                </h4>'
)

# And now the footer buttons.
# Original: </section>\n\n    <section class="flex items-center justify-between rounded-b-2xl bg-fondo-card p-4">
# We want: </div>\n\n        <!-- Botonera -->\n        <div class="mt-4 pt-3 border-t border-borde flex items-center justify-between">
content = re.sub(
    r'    </section>\n\n    <section class="flex items-center justify-between rounded-b-2xl bg-fondo-card p-4">',
    '        </div>\n\n        <!-- Botonera -->\n        <div class="mt-4 pt-3 border-t border-borde flex items-center justify-between">',
    content,
    flags=re.DOTALL
)

# Fix the end tags.
# Original: </section>\n\n    @script ... </div>\n</div>
# We want: </div>\n\n    @script ... </div>\n</div>
content = content.replace('    </section>\n\n    @script', '        </div>\n\n    @script')

# Adjust input padding from rounded-lg to rounded-xl where appropriate
# The user wants styling similar to personal-institucional, so we can make inputs rounded-xl
content = content.replace('rounded-lg border', 'rounded-xl border')
content = content.replace('gap-4 md:grid-cols-3', 'gap-3 md:grid-cols-3')
content = content.replace('gap-4 md:grid-cols-2', 'gap-3 md:grid-cols-2')

# Label text formatting: text-xs font-semibold uppercase tracking-wider
content = re.sub(
    r'<label class="mb-1 block text-xs font-semibold text-apoyo">',
    '<label class="block text-[10px] font-bold text-apoyo uppercase tracking-wider mb-0.5">',
    content
)

with open('resources/views/livewire/admin/admisiones/preadmision-wizard.blade.php', 'w') as f:
    f.write(content)
