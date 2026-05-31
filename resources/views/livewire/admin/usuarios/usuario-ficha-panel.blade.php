<div class="space-y-6">

    {{-- ENCABEZADO DE LA FICHA CON FOTO Y AVANCE DOCUMENTAL (GLASSMORPHISM PREMIM) --}}
    <header class="relative overflow-hidden rounded-[2.5rem] border border-[#C7B5A3]/50 bg-[#E6DDD3]/80 p-6 md:p-8 shadow-[0_16px_38px_rgba(47,62,92,0.06)] backdrop-blur-xl">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col items-center gap-5 text-center sm:flex-row sm:text-left">
                <div class="relative">
                    @if($usuario->foto_de_perfil)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($usuario->foto_de_perfil) }}" 
                             alt="{{ $usuario->name }}" 
                             class="h-28 w-28 rounded-[2rem] object-cover ring-4 ring-white shadow-xl">
                    @elseif($usuario->profile_photo_url)
                        <img src="{{ $usuario->profile_photo_url }}" 
                             alt="{{ $usuario->name }}" 
                             class="h-28 w-28 rounded-[2rem] object-cover ring-4 ring-white shadow-xl">
                    @else
                        <div class="flex h-28 w-28 items-center justify-center rounded-[2rem] bg-[#2F3E5C] text-4xl font-black text-white ring-4 ring-white shadow-xl">
                            {{ strtoupper(substr($usuario->nombres, 0, 1)) }}{{ strtoupper(substr($usuario->ap_paterno ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                    <span class="absolute -bottom-1 -right-1 h-6 w-6 rounded-full border-4 border-[#F8F3ED] {{ $usuario->estado === 'ACTIVO' ? 'bg-[#8DA280]' : 'bg-[#967B66]' }}"></span>
                </div>
                
                <div>
                    <span class="text-[9px] font-black uppercase tracking-[0.3em] text-[#E27D60]">
                        Expediente digital
                    </span>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-[#2F3E5C] sm:text-3xl leading-none">
                        {{ $usuario->nombres }} <span class="text-[#E27D60]">{{ $usuario->ap_paterno }} {{ $usuario->ap_materno }}</span>
                    </h1>
                    
                    <div class="mt-3 flex flex-wrap justify-center gap-2 sm:justify-start">
                        <span class="inline-flex items-center gap-1 rounded-full bg-[#2F3E5C]/10 px-3.5 py-1 text-[10px] font-black uppercase text-[#2F3E5C]">
                            <i class="ph-bold ph-shield-star"></i> {{ $nombre_rol }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full px-3.5 py-1 text-[10px] font-black {{ $usuario->acceso_sistema === 'HABILITADO' ? 'bg-[#8DA280]/20 text-[#63775B]' : 'bg-[#E27D60]/20 text-[#E27D60]' }}">
                            <i class="ph-bold {{ $usuario->acceso_sistema === 'HABILITADO' ? 'ph-lock-open' : 'ph-lock' }}"></i> ACCESO {{ $usuario->acceso_sistema }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Medidor de Avance Documental Circular/Barra Premium --}}
            <div class="w-full max-w-xs rounded-2xl bg-white/45 p-4 border border-[#C7B5A3]/40 shadow-sm shrink-0 self-center lg:self-auto">
                <div class="flex items-center justify-between text-xs font-black uppercase tracking-wider text-[#2F3E5C] mb-2">
                    <span>Avance Documental</span>
                    <span class="text-[#E27D60] font-extrabold">{{ $avance_documental['porcentaje_avance'] }}%</span>
                </div>
                <div class="h-3 w-full rounded-full bg-[#C7B5A3]/30 overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-[#E27D60] to-[#8DA280] transition-all duration-1000 shadow-md"
                         style="width: {{ $avance_documental['porcentaje_avance'] }}%"></div>
                </div>
                <p class="mt-2 text-[9px] font-black text-[#2F3E5C]/55 uppercase tracking-wide text-center">
                    {{ $avance_documental['validados'] }} de {{ $avance_documental['total_requeridos'] }} obligatorios validados.
                </p>
            </div>
        </div>
    </header>

    {{-- BARRA NAVEGACIÓN DE PESTAÑAS --}}
    <nav class="flex overflow-x-auto rounded-[1.5rem] bg-[#E6DDD3]/50 p-2 shadow-[0_10px_24px_rgba(47,62,92,0.04)] backdrop-blur-md custom-scrollbar">
        <div class="flex space-x-1.5 min-w-max">
            @php
                $tabsDef = [
                    'datos_personales' => ['i' => 'ph-user', 'l' => 'Datos Personales'],
                    'perfil_institucional' => ['i' => 'ph-briefcase', 'l' => 'Perfil'],
                    'documentacion' => ['i' => 'ph-folder-open', 'l' => 'Documentación'],
                    'horarios' => ['i' => 'ph-calendar-check', 'l' => 'Horarios'],
                    'acceso_seguridad' => ['i' => 'ph-shield-check', 'l' => 'Seguridad'],
                    'historial' => ['i' => 'ph-clock-counter-clockwise', 'l' => 'Historial'],
                    'reportes' => ['i' => 'ph-file-pdf', 'l' => 'Reportes']
                ];
            @endphp

            @foreach($tabsDef as $t => $info)
                <button type="button"
                        wire:click="setTab('{{ $t }}')"
                        class="flex h-10 items-center gap-2 rounded-xl px-4 text-xs font-black uppercase tracking-wider transition-all duration-300 active:scale-95
                        {{ $activeTab === $t 
                            ? 'bg-[#2F3E5C] text-white shadow-md shadow-[#2F3E5C]/15' 
                            : 'text-[#2F3E5C]/65 hover:bg-white/40 hover:text-[#2F3E5C]' }}">
                    <i class="ph-bold {{ $info['i'] }} text-base"></i>
                    {{ $info['l'] }}
                </button>
            @endforeach
        </div>
    </nav>

    {{-- CARGADORES E INDICADORES DE CARGA GLOBAL --}}
    <div wire:loading wire:target="setTab,restablecerPassword,toggleAcceso,generarExpedientePdf,generarExpedienteExcel,validarDoc" class="w-full flex items-center justify-center py-12 rounded-[2rem] bg-[#E6DDD3]/30 border border-[#C7B5A3]/25 backdrop-blur-sm">
        <div class="flex items-center gap-3 rounded-full bg-white px-6 py-4 shadow-xl">
            <i class="ph-bold ph-circle-notch animate-spin text-2xl text-[#E27D60]"></i>
            <span class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]">
                Sincronizando información...
            </span>
        </div>
    </div>

    {{-- SECCIONES ACTIVAS --}}
    <div wire:loading.remove>

        {{-- TAB 1: DATOS PERSONALES --}}
        @if($activeTab === 'datos_personales')
        <div class="grid gap-6 md:grid-cols-3 animate-in fade-in duration-300">
            <div class="md:col-span-2 space-y-6">
                <section class="rounded-[2rem] border border-[#C7B5A3]/45 bg-white/70 p-6 shadow-sm">
                    <h3 class="mb-5 flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#E27D60]">
                        <i class="ph-bold ph-identification-card text-lg"></i> Datos de Identidad
                    </h3>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/40">Nombres completos</p>
                            <p class="mt-1 font-bold text-[#2F3E5C] uppercase">{{ $usuario->nombres }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/40">Apellidos</p>
                            <p class="mt-1 font-bold text-[#2F3E5C] uppercase">{{ $usuario->ap_paterno }} {{ $usuario->ap_materno }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/40">Documento de Identidad</p>
                            <p class="mt-1 font-bold text-[#2F3E5C] uppercase">
                                {{ $usuario->tipo_documento ?? 'CI' }} {{ $usuario->numero_documento }}
                                @if($usuario->expedido) <span class="text-[#E27D60] font-black">({{ $usuario->expedido }})</span> @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/40">Nacionalidad / Emisor</p>
                            <p class="mt-1 font-bold text-[#2F3E5C]">{{ $usuario->pais_documento ?? 'Bolivia' }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/40">Fecha de Nacimiento</p>
                            <p class="mt-1 font-bold text-[#2F3E5C]">
                                {{ $usuario->fecha_nacimiento ? $usuario->fecha_nacimiento->format('d/m/Y') : 'No registrado' }}
                                @if($usuario->fecha_nacimiento)
                                    <span class="ml-1 text-[9px] font-black bg-[#E27D60]/10 text-[#E27D60] px-2 py-0.5 rounded-md uppercase">
                                        {{ $usuario->fecha_nacimiento->age }} años
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/40">Género</p>
                            <p class="mt-1 font-bold text-[#2F3E5C] uppercase">{{ $usuario->genero ?? 'No especificado' }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-[#C7B5A3]/45 bg-white/70 p-6 shadow-sm">
                    <h3 class="mb-5 flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#E27D60]">
                        <i class="ph-bold ph-phone-call text-lg"></i> Información de Contacto
                    </h3>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/40">Correo Electrónico</p>
                            <p class="mt-1 font-bold text-[#2F3E5C] lowercase break-all">{{ $usuario->correo }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/40">Número de Celular</p>
                            <p class="mt-1 font-bold text-[#2F3E5C]">
                                <span class="text-[#E27D60] font-black">{{ $usuario->codigo_telefono }}</span> {{ $usuario->telefono ?? 'Sin registrar' }}
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-[2rem] border border-[#C7B5A3]/45 bg-[#E6DDD3]/50 p-6 shadow-sm">
                    <h3 class="mb-4 text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Metadatos de Registro</h3>
                    <div class="space-y-3.5">
                        <div class="flex justify-between items-center text-xs border-b border-[#C7B5A3]/25 pb-2">
                            <span class="font-bold text-[#2F3E5C]/45">Creado:</span>
                            <span class="font-bold text-[#2F3E5C]">{{ $usuario->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs border-b border-[#C7B5A3]/25 pb-2">
                            <span class="font-bold text-[#2F3E5C]/45">Actualizado:</span>
                            <span class="font-bold text-[#2F3E5C]">{{ $usuario->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-bold text-[#2F3E5C]/45">Estado:</span>
                            <span class="rounded bg-[#8DA280]/20 text-[#63775B] px-2 py-0.5 text-[9px] font-black uppercase tracking-widest">{{ $usuario->estado }}</span>
                        </div>
                    </div>
                </section>

                @if($usuario->observaciones)
                <section class="rounded-[2rem] border border-[#E27D60]/20 bg-[#FDF1ED]/80 p-6 shadow-sm">
                    <h3 class="mb-2 text-xs font-black uppercase tracking-widest text-[#E27D60]">Observaciones Generales</h3>
                    <p class="text-xs font-bold text-[#2F3E5C]/70 uppercase leading-relaxed italic">
                        {{ $usuario->observaciones }}
                    </p>
                </section>
                @endif
            </div>
        </div>
        @endif

        {{-- TAB 2: PERFIL INSTITUCIONAL --}}
        @if($activeTab === 'perfil_institucional')
        <div class="grid gap-6 md:grid-cols-2 animate-in fade-in duration-300">
            <section class="rounded-[2rem] border border-[#C7B5A3]/45 bg-white/70 p-6 shadow-sm space-y-5">
                <h3 class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#E27D60]">
                    <i class="ph-bold ph-buildings text-lg"></i> Asignación Física & Área
                </h3>
                
                <div class="rounded-2xl border border-[#C7B5A3]/40 bg-[#FAF7F3] p-5 flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#2F3E5C]/10 text-[#2F3E5C] shadow-inner">
                        <i class="ph-bold ph-tree-structure text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/45">Área Operativa Principal</p>
                        <p class="mt-1 text-base font-black text-[#2F3E5C] uppercase">{{ $area }}</p>
                        <p class="mt-1 text-[10px] font-bold text-[#2F3E5C]/50 leading-relaxed">
                            Organiza y supervisa las tareas realizadas por el personal asignado a este sector.
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-[2rem] border border-[#C7B5A3]/45 bg-white/70 p-6 shadow-sm space-y-5">
                <h3 class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#E27D60]">
                    <i class="ph-bold ph-certificate text-lg"></i> Especialidad / Función
                </h3>

                <div class="rounded-2xl border border-[#C7B5A3]/40 bg-[#FAF7F3] p-5 flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#E27D60]/10 text-[#E27D60] shadow-inner">
                        <i class="ph-bold ph-award text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/45">Cargo de Desempeño</p>
                        
                        @if($usuario->hasRole('personal_salud'))
                            <p class="mt-1 text-base font-black text-[#2F3E5C] uppercase truncate">{{ $usuario->personalSalud?->especialidad?->nombre ?? 'Sin Especialidad' }}</p>
                            @if($usuario->personalSalud?->fecha_ing)
                                <p class="mt-1 text-[10px] font-bold text-[#2F3E5C]/45">
                                    Ingresó en: {{ $usuario->personalSalud->fecha_ing instanceof \Carbon\Carbon ? $usuario->personalSalud->fecha_ing->format('d/m/Y') : \Carbon\Carbon::parse($usuario->personalSalud->fecha_ing)->format('d/m/Y') }}
                                </p>
                            @endif
                        @elseif($usuario->hasRole('personal_admin'))
                            <p class="mt-1 text-base font-black text-[#2F3E5C] uppercase truncate">{{ $usuario->personalAdmin?->cargoAdmin?->nombre ?? ($usuario->personalAdmin?->cargo ?? 'Sin cargo') }}</p>
                            @if($usuario->personalAdmin?->fecha_ingreso)
                                <p class="mt-1 text-[10px] font-bold text-[#2F3E5C]/45">
                                    Ingresó en: {{ $usuario->personalAdmin->fecha_ingreso instanceof \Carbon\Carbon ? $usuario->personalAdmin->fecha_ingreso->format('d/m/Y') : \Carbon\Carbon::parse($usuario->personalAdmin->fecha_ingreso)->format('d/m/Y') }}
                                </p>
                            @endif
                        @else
                            <p class="mt-1 text-base font-black text-[#2F3E5C] uppercase">{{ $nombre_rol }}</p>
                            <p class="mt-1 text-[10px] font-bold text-[#2F3E5C]/45 leading-relaxed">
                                Vinculado institucionalmente bajo el rol correspondiente en CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.
                            </p>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Sección de Adultos Mayores Vinculados para el Rol Familiar --}}
            @if($usuario->hasRole('familiar'))
            <section class="md:col-span-2 rounded-[2rem] border border-[#C7B5A3]/45 bg-white/70 p-6 shadow-sm space-y-5">
                <h3 class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#E27D60]">
                    <i class="ph-bold ph-link text-lg"></i> Adulto(s) Mayor(es) Vinculado(s)
                </h3>
                
                @php
                    $familiar = $usuario->familiares->first();
                    $vinculos = $familiar ? $familiar->adultosMayores : collect();
                @endphp

                @if($vinculos->isEmpty())
                    <div class="rounded-2xl border border-dashed border-[#C7B5A3] bg-white/40 p-10 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#2F3E5C]/10 text-[#2F3E5C]">
                            <i class="ph-bold ph-link-break text-2xl"></i>
                        </div>
                        <h4 class="mt-4 text-sm font-black text-[#2F3E5C]">Sin vinculaciones activas</h4>
                        <p class="mt-1 text-xs font-bold text-[#2F3E5C]/45">
                            Este familiar no tiene adultos mayores vinculados en el expediente digital.
                        </p>
                    </div>
                @else
                    <div class="overflow-hidden rounded-2xl border border-[#C7B5A3]/40 bg-white shadow-sm">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#F4EEE7] text-[9px] uppercase tracking-widest text-[#2F3E5C]/50 border-b border-[#C7B5A3]/45">
                                <tr>
                                    <th class="px-5 py-3.5 font-black">Adulto Mayor</th>
                                    <th class="px-5 py-3.5 font-black">Parentesco / Vínculo</th>
                                    <th class="px-5 py-3.5 text-center font-black">Responsable Principal</th>
                                    <th class="px-5 py-3.5 font-black">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E7DDD1]">
                                @foreach($vinculos as $v)
                                    <tr class="hover:bg-[#FAF7F3]">
                                        <td class="px-5 py-4">
                                            <p class="font-black text-[#2F3E5C] uppercase">{{ $v->ap_paterno }} {{ $v->ap_materno }} {{ $v->nombres }}</p>
                                            <p class="text-[10px] text-[#2F3E5C]/50 font-bold mt-0.5">{{ $v->cod_am }}</p>
                                        </td>
                                        <td class="px-5 py-4 font-black">
                                            <span class="px-2.5 py-0.5 rounded bg-[#C7B5A3]/25 text-[#2F3E5C] text-[9px] font-black uppercase">
                                                {{ $v->pivot->parentesco_vinculo ?? 'Familiar' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-center">
                                            @if($v->pivot->es_responsable)
                                                <span class="rounded bg-[#8DA280]/20 text-[#63775B] px-2.5 py-0.5 text-[9px] font-black uppercase tracking-wider">SÍ</span>
                                            @else
                                                <span class="rounded bg-[#C7B5A3]/20 text-[#2F3E5C]/60 px-2.5 py-0.5 text-[9px] font-black uppercase tracking-wider">NO</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 font-bold text-[#2F3E5C]/60 leading-relaxed max-w-xs truncate">
                                            {{ $v->pivot->observaciones ?: 'Sin observaciones específicas' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
            @endif
        </div>
        @endif

        {{-- TAB 3: DOCUMENTACIÓN --}}
        @if($activeTab === 'documentacion')
        <div class="space-y-6 animate-in fade-in duration-300">
            <section class="rounded-[2rem] border border-[#C7B5A3]/45 bg-white/70 p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
                    <div>
                        <h3 class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#E27D60]">
                            <i class="ph-bold ph-folder-lock text-lg"></i> Expediente Documental Requerido
                        </h3>
                        <p class="mt-1 text-xs text-[#2F3E5C]/55 font-bold">
                            Listado completo de requisitos y credenciales obligatorias para su rol en CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded bg-[#8DA280]/20 text-[#63775B] px-3 py-1 text-[10px] font-black uppercase tracking-wider">
                            Obligatorios: {{ $avance_documental['total_requeridos'] }}
                        </span>
                        <span class="rounded bg-[#FAF7F3] border border-[#C7B5A3]/40 text-[#2F3E5C] px-3 py-1 text-[10px] font-black uppercase tracking-wider">
                            Opcionales: {{ $avance_documental['total_opcionales'] }}
                        </span>
                        @can('usuarios.reportes.pdf')
                        <a href="{{ route('admin.usuarios.documentacion.pdf', $usuario) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 rounded-xl bg-[#2F3E5C] text-white px-3.5 py-1.5 text-[10px] font-black uppercase tracking-wider transition hover:bg-[#E27D60] active:scale-95 shadow-sm">
                            <i class="ph-bold ph-printer"></i> Imprimir Checklist
                        </a>
                        @endcan
                    </div>
                </div>

                {{-- Tabla de Documentos --}}
                <div class="overflow-hidden rounded-2xl border border-[#C7B5A3]/40 bg-white shadow-sm">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#F4EEE7] text-[9px] uppercase tracking-widest text-[#2F3E5C]/50 border-b border-[#C7B5A3]/45">
                            <tr>
                                <th class="px-5 py-3.5 font-black w-[40%]">Documento / Tipo</th>
                                <th class="px-5 py-3.5 font-black w-[15%]">Obligatorio</th>
                                <th class="px-5 py-3.5 font-black w-[15%]">Estado</th>
                                <th class="px-5 py-3.5 text-center font-black w-[30%]">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E7DDD1]">
                            @forelse($checklist as $item)
                                <tr class="hover:bg-[#FAF7F3]">
                                    <td class="px-5 py-4">
                                        <p class="font-black text-[#2F3E5C] uppercase">{{ $item['nombre'] }}</p>
                                        <p class="text-[10px] text-[#2F3E5C]/50 leading-relaxed mt-0.5 font-bold">{{ $item['descripcion'] }}</p>
                                    </td>
                                    <td class="px-5 py-4 font-black">
                                        @if($item['obligatorio'])
                                            <span class="text-[#E27D60] bg-[#E27D60]/10 px-2 py-0.5 rounded text-[9px]">SÍ</span>
                                        @else
                                            <span class="text-[#2F3E5C]/45 bg-[#2F3E5C]/5 px-2 py-0.5 rounded text-[9px]">NO</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @php
                                            $pillColor = match($item['estado']) {
                                                'VALIDADO' => 'bg-[#8DA280]/20 text-[#63775B]',
                                                'CARGADO', 'PENDIENTE_VALIDACION' => 'bg-[#7C83B8]/20 text-[#5E6599]',
                                                'OBSERVADO' => 'bg-[#E27D60]/20 text-[#E27D60]',
                                                'VENCIDO' => 'bg-[#967B66]/20 text-[#7B624F]',
                                                default => 'bg-[#C7B5A3]/20 text-[#7C7168]'
                                            };
                                        @endphp
                                        <span class="rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-wider {{ $pillColor }}">
                                            {{ $item['estado'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                            @if(!$item['cargado'])
                                                @can('documentos_usuarios.subir')
                                                <button type="button"
                                                        wire:click="abrirSubida('{{ $item['cod_tipo_doc'] }}')"
                                                        class="inline-flex items-center gap-1 rounded-full bg-[#E27D60] px-3.5 py-1.5 text-[9px] font-black uppercase tracking-wider text-white shadow-sm hover:shadow active:scale-95">
                                                    <i class="ph-bold ph-upload-simple"></i> Subir
                                                </button>
                                                @endcan
                                            @else
                                                @can('documentos_usuarios.descargar')
                                                <button type="button"
                                                        wire:click="descargarDoc('{{ $item['documento']->cod_doc_usu }}')"
                                                        class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#2F3E5C]/10 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition shadow-sm active:scale-90"
                                                        title="Descargar documento">
                                                    <i class="ph-bold ph-download-simple"></i>
                                                </button>
                                                @endcan

                                                {{-- Reemplazar si está observado o vencido --}}
                                                @if(in_array($item['estado'], ['OBSERVADO', 'VENCIDO']))
                                                    @can('documentos_usuarios.reemplazar')
                                                    <button type="button"
                                                            wire:click="abrirSubida('{{ $item['cod_tipo_doc'] }}')"
                                                            class="inline-flex items-center gap-1 rounded-full bg-[#E27D60] px-3.5 py-1.5 text-[9px] font-black uppercase tracking-wider text-white shadow-sm hover:bg-[#d86c50] active:scale-95"
                                                            title="Reemplazar por nueva versión">
                                                        <i class="ph-bold ph-arrows-counter-clockwise"></i> Reemplazar
                                                    </button>
                                                    @endcan
                                                @endif

                                                {{-- Validadores autorizados --}}
                                                @if($item['estado'] === 'CARGADO')
                                                    @can('documentos_usuarios.validar')
                                                    <button type="button"
                                                            wire:click="validarDoc('{{ $item['documento']->cod_doc_usu }}')"
                                                            wire:confirm="¿Está seguro de validar este documento?"
                                                            class="inline-flex items-center gap-1 rounded-full bg-[#8DA280] px-3.5 py-1.5 text-[9px] font-black uppercase tracking-wider text-white shadow-sm hover:bg-[#6e8262] active:scale-95"
                                                            title="Validar documento">
                                                        <i class="ph-bold ph-check"></i> Validar
                                                    </button>
                                                    @endcan

                                                    @can('documentos_usuarios.observar')
                                                    <button type="button"
                                                            wire:click="abrirObservarDoc('{{ $item['documento']->cod_doc_usu }}')"
                                                            class="inline-flex items-center gap-1 rounded-full bg-[#D5C7B9] border border-[#C7B5A3]/65 px-3.5 py-1.5 text-[9px] font-black uppercase tracking-wider text-[#2F3E5C] shadow-sm hover:bg-[#E27D60] hover:text-white active:scale-95"
                                                            title="Observar documento">
                                                        <i class="ph-bold ph-warning"></i> Observar
                                                    </button>
                                                    @endcan
                                                @endif

                                                @can('documentos_usuarios.anular')
                                                @if($item['estado'] !== 'ANULADO')
                                                    <button type="button"
                                                            wire:click="abrirAnularDoc('{{ $item['documento']->cod_doc_usu }}')"
                                                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-red-100 text-red-700 hover:bg-red-700 hover:text-white transition shadow-sm active:scale-90"
                                                            title="Anular documento">
                                                        <i class="ph-bold ph-trash"></i>
                                                    </button>
                                                @endif
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-xs font-bold text-[#2F3E5C]/45">
                                        No se requieren documentos para este perfil.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        @endif

        {{-- TAB 4: HORARIOS Y ASIGNACIONES --}}
        @if($activeTab === 'horarios')
        <div class="space-y-6 animate-in fade-in duration-300">
            <section class="rounded-[2rem] border border-[#C7B5A3]/45 bg-white/70 p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-5">
                    <h3 class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#E27D60]">
                        <i class="ph-bold ph-calendar-check text-lg"></i> Asignación de Turno Activa
                    </h3>
                    @can('usuarios.reportes.pdf')
                    <a href="{{ route('admin.usuarios.horarios.pdf', $usuario) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 rounded-xl bg-[#2F3E5C] text-white px-3.5 py-1.5 text-[10px] font-black uppercase tracking-wider transition hover:bg-[#E27D60] active:scale-95 shadow-sm">
                        <i class="ph-bold ph-printer"></i> Imprimir Horarios
                    </a>
                    @endcan
                </div>

                @if($horarios)
                    <div class="rounded-2xl border border-[#C7B5A3]/45 bg-[#FAF7F3] p-5 grid gap-6 md:grid-cols-3">
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/45">Turno Asignado</p>
                            <p class="mt-1 text-base font-black text-[#2F3E5C] uppercase">{{ $horarios->turno?->nombre }}</p>
                            <p class="mt-1 text-[10px] font-bold text-[#2F3E5C]/45">
                                Horario: {{ substr($horarios->turno?->hora_inicio, 0, 5) }} a {{ substr($horarios->turno?->hora_fin, 0, 5) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/45">Sector / Área</p>
                            <p class="mt-1 text-base font-black text-[#2F3E5C] uppercase">{{ $horarios->area?->nombre }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/45">Días Laborales</p>
                            <div class="mt-1.5 flex flex-wrap gap-1">
                                @foreach($horarios->dias_semana ?? [] as $d)
                                    <span class="rounded bg-[#2F3E5C] px-2 py-0.5 text-[8px] font-black uppercase text-white shadow-sm">
                                        {{ $d }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-[#C7B5A3] bg-white/40 p-10 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#2F3E5C]/10 text-[#2F3E5C]">
                            <i class="ph-bold ph-calendar-x text-2xl"></i>
                        </div>
                        <h4 class="mt-4 text-sm font-black text-[#2F3E5C]">Sin asignación horaria activa</h4>
                        <p class="mt-1 text-xs font-bold text-[#2F3E5C]/45">
                            Este usuario no tiene un cronograma de turnos planificado actualmente.
                        </p>
                    </div>
                @endif
            </section>

            <section class="rounded-[2rem] border border-[#C7B5A3]/45 bg-white/70 p-6 shadow-sm">
                <h3 class="mb-5 flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">
                    <i class="ph-bold ph-history text-lg"></i> Historial de Asignaciones
                </h3>

                <div class="overflow-hidden rounded-2xl border border-[#C7B5A3]/40 bg-white shadow-sm">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#F4EEE7] text-[9px] uppercase tracking-widest text-[#2F3E5C]/50 border-b border-[#C7B5A3]/45">
                            <tr>
                                <th class="px-5 py-3.5 font-black">Turno / Horario</th>
                                <th class="px-5 py-3.5 font-black">Área Asignada</th>
                                <th class="px-5 py-3.5 font-black">Rango Fecha</th>
                                <th class="px-5 py-3.5 font-black">Estado</th>
                                <th class="px-5 py-3.5 font-black">Registrado Por</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E7DDD1]">
                            @forelse($historial_horarios as $h)
                                <tr class="hover:bg-[#FAF7F3]">
                                    <td class="px-5 py-4">
                                        <p class="font-black text-[#2F3E5C] uppercase">{{ $h->turno?->nombre }}</p>
                                        <p class="text-[10px] text-[#2F3E5C]/50 font-bold">
                                            {{ substr($h->turno?->hora_inicio, 0, 5) }} - {{ substr($h->turno?->hora_fin, 0, 5) }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4 font-black text-[#2F3E5C] uppercase">
                                        {{ $h->area?->nombre }}
                                    </td>
                                    <td class="px-5 py-4 font-bold text-[#2F3E5C]/60">
                                        {{ $h->fecha_inicio ? $h->fecha_inicio->format('d/m/Y') : '---' }}
                                        <i class="ph-bold ph-arrow-right mx-1 text-[#E27D60] text-[10px]"></i>
                                        {{ $h->fecha_fin ? $h->fecha_fin->format('d/m/Y') : 'Vigente' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        @php
                                            $hPill = match($h->estado) {
                                                'ACTIVA' => 'bg-[#8DA280]/20 text-[#63775B]',
                                                'FINALIZADA' => 'bg-[#C7B5A3]/25 text-[#7C7168]',
                                                default => 'bg-red-100 text-red-700'
                                            };
                                        @endphp
                                        <span class="rounded px-2.5 py-0.5 text-[9px] font-black uppercase {{ $hPill }}">
                                            {{ $h->estado }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 font-bold text-[#2F3E5C]/50">
                                        {{ $h->creador?->name ?? 'Sistema' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-6 text-center text-[#2F3E5C]/45 font-bold">
                                        No hay registros de turnos anteriores.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        @endif

        {{-- TAB 5: ACCESO Y SEGURIDAD --}}
        @if($activeTab === 'acceso_seguridad')
        <div class="grid gap-6 md:grid-cols-3 animate-in fade-in duration-300">
            <div class="md:col-span-2 space-y-6">
                <section class="rounded-[2rem] border border-[#C7B5A3]/45 bg-white/70 p-6 shadow-sm space-y-5">
                    <h3 class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#E27D60]">
                        <i class="ph-bold ph-key text-lg"></i> Credenciales & Privilegios de Acceso
                    </h3>

                    <div class="grid gap-4 sm:grid-cols-1">
                        <div class="rounded-2xl border border-[#C7B5A3]/40 bg-[#FAF7F3] p-5 text-center flex flex-col justify-between max-w-md mx-auto w-full">
                            <div>
                                <i class="ph-bold {{ $usuario->acceso_sistema === 'HABILITADO' ? 'ph-user-check text-[#63775B]' : 'ph-user-focus text-[#E27D60]' }} text-3xl"></i>
                                <h4 class="mt-3 text-sm font-black text-[#2F3E5C] uppercase">Bloqueo de Cuenta</h4>
                                <p class="mt-1.5 text-[10px] font-bold text-[#2F3E5C]/50 leading-relaxed">
                                    Impide o reactiva de inmediato el inicio de sesión y uso del portal RememberMind.
                                </p>
                            </div>
                            @can('usuarios.acceso.bloquear')
                            @if($usuario->cod_usu !== auth()->id())
                                <button type="button"
                                        wire:click="toggleAcceso"
                                        wire:confirm="¿Está seguro de cambiar el estado de acceso del usuario?"
                                        class="mt-4 inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-[10px] font-black uppercase text-white shadow-md transition hover:-translate-y-0.5 active:translate-y-0
                                        {{ $usuario->acceso_sistema === 'HABILITADO' ? 'bg-[#E27D60] shadow-[#E27D60]/10 hover:bg-[#d86c50]' : 'bg-[#8DA280] shadow-[#8DA280]/10 hover:bg-[#7d9271]' }}">
                                    <i class="ph-bold {{ $usuario->acceso_sistema === 'HABILITADO' ? 'ph-user-minus' : 'ph-user-plus' }} text-sm"></i>
                                    {{ $usuario->acceso_sistema === 'HABILITADO' ? 'Bloquear Acceso' : 'Habilitar Acceso' }}
                                </button>
                            @endif
                            @endcan
                        </div>
                    </div>
                </section>
            </div>

            <section class="rounded-[2rem] border border-[#C7B5A3]/45 bg-[#E6DDD3]/50 p-6 shadow-sm space-y-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Recomendaciones de Seguridad</h3>
                <ul class="space-y-3 text-[10px] font-bold text-[#2F3E5C]/60 leading-relaxed">
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#E27D60]"></span>
                        <span>Las claves temporales expiran al primer ingreso forzando el cambio.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#E27D60]"></span>
                        <span>El bloqueo de cuenta suspende sesiones web vigentes al instante.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-0.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#E27D60]"></span>
                        <span>Todas las actividades críticas quedan registradas en la bitácora con fines de auditoría.</span>
                    </li>
                </ul>
            </section>
        </div>
        @endif

        {{-- TAB 6: HISTORIAL DE ACTIVIDAD --}}
        @if($activeTab === 'historial')
        <div class="space-y-6 animate-in fade-in duration-300">
            {{-- Filtros del historial --}}
            <section class="rounded-[1.5rem] border border-[#C7B5A3]/45 bg-white/70 p-4 shadow-sm">
                <div class="grid items-end gap-3 sm:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/50">Acción</label>
                        <select wire:model.live="filtroAccion"
                                class="h-9 w-full rounded-xl border border-[#C7B5A3]/65 bg-[#FAF7F3] px-3 text-xs font-bold text-[#2F3E5C]">
                            <option value="">Todos los eventos</option>
                            <option value="registro">Registros</option>
                            <option value="edicion">Ediciones</option>
                            <option value="documentacion">Documentación</option>
                            <option value="seguridad">Seguridad</option>
                            <option value="reportes">Reportes</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/50">Desde</label>
                        <input type="date"
                               wire:model.live="filtroFechaDesde"
                               class="h-9 w-full rounded-xl border border-[#C7B5A3]/65 bg-[#FAF7F3] px-3 text-xs font-bold text-[#2F3E5C]">
                    </div>
                    <div>
                        <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/50">Hasta</label>
                        <input type="date"
                               wire:model.live="filtroFechaHasta"
                               class="h-9 w-full rounded-xl border border-[#C7B5A3]/65 bg-[#FAF7F3] px-3 text-xs font-bold text-[#2F3E5C]">
                    </div>
                    <div class="flex gap-2">
                        <button type="button"
                                wire:click="$reset('filtroAccion', 'filtroFechaDesde', 'filtroFechaHasta')"
                                class="h-9 flex-1 rounded-xl bg-[#C7B5A3]/30 text-[#2F3E5C] text-[10px] font-black uppercase tracking-wider transition hover:bg-[#E27D60] hover:text-white active:scale-95 shadow-sm">
                            Restablecer
                        </button>
                    </div>
                </div>
            </section>

            <section class="rounded-[2rem] border border-[#C7B5A3]/45 bg-white/70 p-6 shadow-sm space-y-6">
                <h3 class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">
                    <i class="ph-bold ph-clock-counter-clockwise text-lg"></i> Auditoría de Eventos del Usuario
                </h3>

                {{-- Timeline visual --}}
                @if($historial_actividad->count() > 0)
                    <div class="relative pl-6 border-l-2 border-[#C7B5A3]/50 space-y-6 ml-3">
                        @foreach($historial_actividad as $act)
                            <div class="relative">
                                {{-- Indicador circular --}}
                                @php
                                    $circleColor = match($act->event) {
                                        'registro' => 'bg-[#8DA280]',
                                        'documentacion' => 'bg-[#7C83B8]',
                                        'seguridad' => 'bg-[#E27D60]',
                                        'reportes' => 'bg-[#967B66]',
                                        default => 'bg-[#2F3E5C]'
                                    };
                                @endphp
                                <span class="absolute -left-[31px] top-1.5 flex h-4.5 w-4.5 rounded-full {{ $circleColor }} border-4 border-white shadow-sm"></span>

                                <div class="rounded-2xl border border-[#C7B5A3]/30 bg-[#FAF7F3]/80 p-4 shadow-sm max-w-3xl">
                                    <div class="flex items-center justify-between gap-4 mb-1">
                                        <span class="rounded bg-[#2F3E5C]/10 px-2 py-0.5 text-[8px] font-black uppercase tracking-wider text-[#2F3E5C]">
                                            {{ $act->event }}
                                        </span>
                                        <span class="text-[9px] font-bold text-[#2F3E5C]/45">
                                            {{ $act->created_at->format('d/m/Y H:i:s') }}
                                        </span>
                                    </div>
                                    <p class="text-xs font-bold text-[#2F3E5C]/80 leading-relaxed">
                                        {{ $act->description }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Paginación --}}
                    @if($historial_actividad->hasPages())
                        <div class="mt-4">
                            {{ $historial_actividad->links() }}
                        </div>
                    @endif
                @else
                    <div class="py-8 text-center text-xs font-bold text-[#2F3E5C]/45">
                        No se encontraron registros de auditoría bajo los filtros seleccionados.
                    </div>
                @endif
            </section>
        </div>
        @endif

        {{-- TAB 7: REPORTES --}}
        @if($activeTab === 'reportes')
        <div class="grid gap-6 md:grid-cols-2 animate-in fade-in duration-300">
            <section class="rounded-[2rem] border border-[#C7B5A3]/45 bg-white/70 p-6 shadow-sm flex flex-col justify-between h-56">
                <div>
                    <h3 class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#E27D60]">
                        <i class="ph-bold ph-file-pdf text-lg"></i> Dossier de Expediente (PDF)
                    </h3>
                    <p class="mt-2 text-xs font-bold text-[#2F3E5C]/55 leading-relaxed">
                        Genera un reporte institucional detallado listo para imprimir, conteniendo los datos de identidad, perfiles asignados y el estatus actual de toda la documentación.
                    </p>
                </div>
                @can('usuarios.reportes.pdf')
                <a href="{{ route('admin.usuarios.ficha.pdf', $usuario) }}" target="_blank"
                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2F3E5C] py-3 text-xs font-black uppercase text-white shadow-md shadow-[#2F3E5C]/15 transition hover:-translate-y-0.5 active:translate-y-0 text-center">
                    <i class="ph-bold ph-printer text-base"></i> Descargar Reporte PDF
                </a>
                @endcan
            </section>

            <section class="rounded-[2rem] border border-[#C7B5A3]/45 bg-white/70 p-6 shadow-sm flex flex-col justify-between h-56">
                <div>
                    <h3 class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#E27D60]">
                        <i class="ph-bold ph-file-xls text-lg"></i> Ficha Analítica de Auditoría (Excel)
                    </h3>
                    <p class="mt-2 text-xs font-bold text-[#2F3E5C]/55 leading-relaxed">
                        Exporta una hoja de cálculo estructurada con todos los metadatos de auditoría del usuario, el historial de asignaciones de turnos y las marcas de tiempo de control documental.
                    </p>
                </div>
                @can('usuarios.reportes.excel')
                <button type="button"
                        wire:click="generarExpedienteExcel"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#8DA280] py-3 text-xs font-black uppercase text-white shadow-md shadow-[#8DA280]/15 transition hover:-translate-y-0.5 active:translate-y-0">
                    <i class="ph-bold ph-microsoft-excel-logo text-base"></i> Exportar Ficha Excel
                </button>
                @endcan
            </section>
        </div>
        @endif

    </div>

    {{-- MODALES AUXILIARES DE DOCUMENTACIÓN --}}

    {{-- MODAL 1: SUBIDA / REEMPLAZO DE ARCHIVO --}}
    @if($mostrarSubidaModal && $selectedTipoDoc)
    <div class="fixed inset-0 z-[2147483640] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm px-4">
        <div class="relative w-full max-w-lg rounded-[2rem] border border-[#C7B5A3]/30 bg-[#E6DDD3] p-6 shadow-[0_20px_50px_rgba(0,0,0,0.4)] animate-in zoom-in duration-300">
            <header class="flex items-center justify-between border-b border-[#C7B5A3]/30 pb-3 mb-4">
                <h4 class="text-sm font-black uppercase text-[#2F3E5C] tracking-wide">Cargar Credencial</h4>
                <button type="button" wire:click="$set('mostrarSubidaModal', false)" class="text-[#2F3E5C]/60 hover:text-red-600">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </header>

            <form wire:submit.prevent="subirArchivo" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60 mb-1">Tipo de Requisito</label>
                    <p class="text-xs font-black text-[#2F3E5C] uppercase">{{ $selectedTipoDoc->nombre }}</p>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60 mb-1">Seleccionar Archivo (PDF, JPG, PNG) *</label>
                    <input type="file"
                           wire:model="archivoSubida"
                           class="w-full text-xs font-bold text-[#2F3E5C] bg-white p-3 rounded-xl border border-[#C7B5A3] outline-none">
                    @error('archivoSubida') <span class="text-[9px] font-black text-[#E27D60] uppercase mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60 mb-1">Fecha de Emisión</label>
                        <input type="date"
                               wire:model="fechaEmision"
                               class="w-full h-10 rounded-xl border border-[#C7B5A3] bg-white px-3 text-xs font-bold text-[#2F3E5C]">
                        @error('fechaEmision') <span class="text-[9px] font-black text-[#E27D60] uppercase mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60 mb-1">Vencimiento</label>
                        <input type="date"
                               wire:model="fechaVencimiento"
                               class="w-full h-10 rounded-xl border border-[#C7B5A3] bg-white px-3 text-xs font-bold text-[#2F3E5C]"
                               {{ $selectedTipoDoc->requiere_vencimiento ? 'required' : '' }}>
                        @error('fechaVencimiento') <span class="text-[9px] font-black text-[#E27D60] uppercase mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60 mb-1">Observaciones</label>
                    <textarea wire:model="observacionesArchivo"
                              rows="2"
                              class="w-full rounded-xl border border-[#C7B5A3] bg-white p-3 text-xs font-bold text-[#2F3E5C] outline-none"
                              placeholder="Indique algún detalle relevante..."></textarea>
                </div>

                <div class="border-t border-[#C7B5A3]/30 pt-4 flex items-center justify-end gap-2">
                    <button type="button"
                            wire:click="$set('mostrarSubidaModal', false)"
                            class="rounded-xl border border-[#C7B5A3] bg-white px-4 py-2 text-[10px] font-black uppercase text-[#2F3E5C] transition hover:bg-red-500 hover:text-white">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="rounded-xl bg-[#2F3E5C] px-6 py-2.5 text-[10px] font-black uppercase text-white shadow-md shadow-[#2F3E5C]/15 transition hover:bg-[#E27D60]">
                        <span wire:loading.remove wire:target="archivoSubida">Confirmar Carga</span>
                        <span wire:loading wire:target="archivoSubida"><i class="ph-bold ph-spinner animate-spin"></i> Subiendo...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL 2: OBSERVAR DOCUMENTO --}}
    @if($mostrarObservacionModal)
    <div class="fixed inset-0 z-[2147483640] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm px-4">
        <div class="relative w-full max-w-md rounded-[2rem] border border-[#C7B5A3]/30 bg-[#E6DDD3] p-6 shadow-[0_20px_50px_rgba(0,0,0,0.4)] animate-in zoom-in duration-300">
            <header class="flex items-center justify-between border-b border-[#C7B5A3]/30 pb-3 mb-4">
                <h4 class="text-sm font-black uppercase text-[#2F3E5C] tracking-wide">Observar Credencial</h4>
                <button type="button" wire:click="$set('mostrarObservacionModal', false)" class="text-[#2F3E5C]/60 hover:text-red-600">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </header>

            <form wire:submit.prevent="observarDoc" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60 mb-1">Motivo de la Observación / Rechazo *</label>
                    <textarea wire:model="motivoRechazo"
                              rows="4"
                              class="w-full rounded-xl border border-[#C7B5A3] bg-white p-3 text-xs font-bold text-[#2F3E5C] outline-none"
                              placeholder="Ej. El archivo no es legible o le falta firma..."></textarea>
                    @error('motivoRechazo') <span class="text-[9px] font-black text-[#E27D60] uppercase mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="border-t border-[#C7B5A3]/30 pt-4 flex items-center justify-end gap-2">
                    <button type="button"
                            wire:click="$set('mostrarObservacionModal', false)"
                            class="rounded-xl border border-[#C7B5A3] bg-white px-4 py-2 text-[10px] font-black uppercase text-[#2F3E5C]">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="rounded-xl bg-[#E27D60] px-6 py-2.5 text-[10px] font-black uppercase text-white shadow-md transition hover:bg-[#2F3E5C]">
                        Rechazar Requisito
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL 3: ANULAR DOCUMENTO --}}
    @if($mostrarAnulacionModal)
    <div class="fixed inset-0 z-[2147483640] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm px-4">
        <div class="relative w-full max-w-md rounded-[2rem] border border-[#C7B5A3]/30 bg-[#E6DDD3] p-6 shadow-[0_20px_50px_rgba(0,0,0,0.4)] animate-in zoom-in duration-300">
            <header class="flex items-center justify-between border-b border-[#C7B5A3]/30 pb-3 mb-4">
                <h4 class="text-sm font-black uppercase text-[#2F3E5C] tracking-wide">Anular Credencial</h4>
                <button type="button" wire:click="$set('mostrarAnulacionModal', false)" class="text-[#2F3E5C]/60 hover:text-red-600">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </header>

            <form wire:submit.prevent="anularDoc" class="space-y-4">
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl text-red-700 text-xs font-bold leading-relaxed mb-4">
                    ¡Atención! La anulación archivará de forma permanente e inválida el documento del expediente.
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60 mb-1">Motivo de Anulación *</label>
                    <textarea wire:model="motivoRechazo"
                              rows="4"
                              class="w-full rounded-xl border border-[#C7B5A3] bg-white p-3 text-xs font-bold text-[#2F3E5C] outline-none"
                              placeholder="Ej. Credencial expirada definitivamente o revocada..."></textarea>
                    @error('motivoRechazo') <span class="text-[9px] font-black text-[#E27D60] uppercase mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="border-t border-[#C7B5A3]/30 pt-4 flex items-center justify-end gap-2">
                    <button type="button"
                            wire:click="$set('mostrarAnulacionModal', false)"
                            class="rounded-xl border border-[#C7B5A3] bg-white px-4 py-2 text-[10px] font-black uppercase text-[#2F3E5C]">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="rounded-xl bg-red-700 px-6 py-2.5 text-[10px] font-black uppercase text-white shadow-md transition hover:bg-[#2F3E5C]">
                        Confirmar Anulación
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
