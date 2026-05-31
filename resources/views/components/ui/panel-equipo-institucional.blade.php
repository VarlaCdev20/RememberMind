@props(['equipo' => [], 'redFamiliar' => []])

@php
$salud  = $equipo['personal_salud'] ?? [];
$admin  = $equipo['personal_admin'] ?? [];
$vol    = $equipo['voluntarios'] ?? [];
$espTot = $equipo['especialidades_total'] ?? 0;

$totalFam    = $redFamiliar['totalFamiliares']    ?? 0;
$conFam      = $redFamiliar['adultosConFamiliar'] ?? 0;
$sinFam      = $redFamiliar['adultosSinFamiliar'] ?? 0;
$responsables = $redFamiliar['responsables']      ?? 0;
$parentescos = $redFamiliar['parentescos']        ?? [];
@endphp

<div class="rm-card rounded-[2rem] p-5">
    <div class="mb-4">
        <span class="text-[11px] font-black uppercase tracking-widest text-boton-acento">
            Estructura operativa
        </span>
        <h2 class="text-lg font-black text-titulo">Equipo institucional</h2>
        <p class="text-xs font-bold text-meta">Distribución del capital humano de CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.</p>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">

        {{-- Personal de Salud --}}
        <div class="rounded-[1.5rem] border border-borde bg-fondo-hover p-3">
            <div class="mb-2 flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-modulo-cognitivoFondo text-modulo-cognitivoTexto">
                    <i class="ph-fill ph-stethoscope text-base"></i>
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wide text-meta">Personal de salud</p>
                    <p class="text-xl font-black text-titulo">{{ $salud['total'] ?? 0 }}</p>
                </div>
            </div>

            <div class="space-y-1">
                <div class="flex justify-between text-[10px] font-bold">
                    <span class="text-meta">Activos</span>
                    <span class="font-black text-titulo">{{ $salud['activos'] ?? 0 }}</span>
                </div>
                @if($espTot > 0)
                <div class="flex justify-between text-[10px] font-bold">
                    <span class="text-meta">Especialidades</span>
                    <span class="font-black text-modulo-cognitivoTexto">{{ $espTot }}</span>
                </div>
                @endif
            </div>

            @if(!empty($salud['especialidades']))
                <div class="mt-2 space-y-1">
                    @foreach(array_slice($salud['especialidades'], 0, 3) as $esp)
                        <div class="flex items-center justify-between rounded-lg bg-fondo-card px-2 py-1">
                            <span class="truncate text-[10px] font-bold text-apoyo">{{ $esp['nombre'] }}</span>
                            <span class="ml-1 shrink-0 text-[10px] font-black text-modulo-cognitivoTexto">{{ $esp['total'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Personal Administrativo --}}
        <div class="rounded-[1.5rem] border border-borde bg-fondo-hover p-3">
            <div class="mb-2 flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-advertencia-bg text-boton-acento">
                    <i class="ph-fill ph-briefcase text-base"></i>
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wide text-meta">Personal admin.</p>
                    <p class="text-xl font-black text-titulo">{{ $admin['total'] ?? 0 }}</p>
                </div>
            </div>

            <div class="space-y-1">
                <div class="flex justify-between text-[10px] font-bold">
                    <span class="text-meta">Activos</span>
                    <span class="font-black text-titulo">{{ $admin['activos'] ?? 0 }}</span>
                </div>
            </div>

            @if(!empty($admin['cargos']))
                <div class="mt-2 space-y-1">
                    @foreach(array_slice($admin['cargos'], 0, 3) as $cargo)
                        <div class="flex items-center justify-between rounded-lg bg-fondo-card px-2 py-1">
                            <span class="truncate text-[10px] font-bold text-apoyo">{{ $cargo['nombre'] }}</span>
                            <span class="ml-1 shrink-0 text-[10px] font-black text-boton-acento">{{ $cargo['total'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Voluntarios --}}
        <div class="rounded-[1.5rem] border border-borde bg-fondo-hover p-3">
            <div class="mb-2 flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-modulo-voluntariosFondo text-modulo-voluntariosTexto">
                    <i class="ph-fill ph-hand-heart text-base"></i>
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wide text-meta">Voluntarios</p>
                    <p class="text-xl font-black text-titulo">{{ $vol['total'] ?? 0 }}</p>
                </div>
            </div>

            <div class="space-y-1">
                <div class="flex justify-between text-[10px] font-bold">
                    <span class="text-meta">Activos</span>
                    <span class="font-black text-titulo">{{ $vol['activos'] ?? 0 }}</span>
                </div>
                @if(($vol['asignados'] ?? 0) > 0)
                <div class="flex justify-between text-[10px] font-bold">
                    <span class="text-meta">Con asignación</span>
                    <span class="font-black text-modulo-voluntariosTexto">{{ $vol['asignados'] }}</span>
                </div>
                @endif
            </div>

            @if(!empty($vol['areas']))
                <div class="mt-2 space-y-1">
                    @foreach(array_slice($vol['areas'], 0, 3) as $area)
                        <div class="flex items-center justify-between rounded-lg bg-fondo-card px-2 py-1">
                            <span class="truncate text-[10px] font-bold text-apoyo">{{ $area['area'] }}</span>
                            <span class="ml-1 shrink-0 text-[10px] font-black text-modulo-voluntariosTexto">{{ $area['total'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- Red familiar --}}
    <div class="mt-3 rounded-[1.5rem] border border-borde bg-fondo-hover p-3">
        <div class="mb-2 flex items-center gap-2">
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-estado-advertencia-bg text-estado-advertencia-texto">
                <i class="ph-fill ph-house-line text-sm"></i>
            </div>
            <span class="text-xs font-black uppercase tracking-wide text-meta">Red familiar</span>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-xl bg-fondo-card px-3 py-2 text-center">
                <p class="text-lg font-black text-titulo">{{ $totalFam }}</p>
                <p class="text-[10px] font-black uppercase tracking-wide text-meta">Familiares</p>
            </div>
            <div class="rounded-xl bg-fondo-card px-3 py-2 text-center">
                <p class="text-lg font-black text-modulo-salud">{{ $conFam }}</p>
                <p class="text-[10px] font-black uppercase tracking-wide text-meta">Con familiar</p>
            </div>
            <div class="rounded-xl bg-fondo-card px-3 py-2 text-center {{ $sinFam > 0 ? 'border border-boton-acento' : '' }}">
                <p class="text-lg font-black {{ $sinFam > 0 ? 'text-boton-acento' : 'text-titulo' }}">{{ $sinFam }}</p>
                <p class="text-[10px] font-black uppercase tracking-wide text-meta">Sin familiar</p>
            </div>
            <div class="rounded-xl bg-fondo-card px-3 py-2 text-center">
                <p class="text-lg font-black text-titulo">{{ $responsables }}</p>
                <p class="text-[10px] font-black uppercase tracking-wide text-meta">Responsables</p>
            </div>
        </div>

        @if(!empty($parentescos))
            <div class="mt-2 flex flex-wrap gap-1.5">
                @foreach($parentescos as $p)
                    <span class="rm-badge-neutral text-xs">
                        {{ $p['parentesco'] }} ({{ $p['total'] }})
                    </span>
                @endforeach
            </div>
        @endif
    </div>

</div>
