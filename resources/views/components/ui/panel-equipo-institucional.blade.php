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

<div class="rounded-[2rem] border border-[#C7B5A3] bg-[#E6DDD3]/88 p-5 shadow-[0_16px_38px_rgba(47,62,92,0.12)]">
    <div class="mb-4">
        <span class="text-[11px] font-black uppercase tracking-widest text-terracota">
            Estructura operativa
        </span>
        <h2 class="text-lg font-black text-azul-profundo">Equipo institucional</h2>
        <p class="text-xs font-bold text-azul-profundo/55">Distribución del capital humano de Casa Amandita.</p>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">

        {{-- Personal de Salud --}}
        <div class="rounded-[1.5rem] border border-[#C7B5A3] bg-[#D5C7B9]/50 p-3">
            <div class="mb-2 flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#9B8AC7]/15 text-[#7A68B0]">
                    <i class="ph-fill ph-stethoscope text-base"></i>
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wide text-azul-profundo/55">Personal de salud</p>
                    <p class="text-xl font-black text-azul-profundo">{{ $salud['total'] ?? 0 }}</p>
                </div>
            </div>

            <div class="space-y-1">
                <div class="flex justify-between text-[10px] font-bold">
                    <span class="text-azul-profundo/55">Activos</span>
                    <span class="font-black text-azul-profundo">{{ $salud['activos'] ?? 0 }}</span>
                </div>
                @if($espTot > 0)
                <div class="flex justify-between text-[10px] font-bold">
                    <span class="text-azul-profundo/55">Especialidades</span>
                    <span class="font-black text-[#7A68B0]">{{ $espTot }}</span>
                </div>
                @endif
            </div>

            @if(!empty($salud['especialidades']))
                <div class="mt-2 space-y-1">
                    @foreach(array_slice($salud['especialidades'], 0, 3) as $esp)
                        <div class="flex items-center justify-between rounded-lg bg-[#E6DDD3]/70 px-2 py-1">
                            <span class="truncate text-[10px] font-bold text-azul-profundo/70">{{ $esp['nombre'] }}</span>
                            <span class="ml-1 shrink-0 text-[10px] font-black text-[#7A68B0]">{{ $esp['total'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Personal Administrativo --}}
        <div class="rounded-[1.5rem] border border-[#C7B5A3] bg-[#D5C7B9]/50 p-3">
            <div class="mb-2 flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-terracota/10 text-terracota">
                    <i class="ph-fill ph-briefcase text-base"></i>
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wide text-azul-profundo/55">Personal admin.</p>
                    <p class="text-xl font-black text-azul-profundo">{{ $admin['total'] ?? 0 }}</p>
                </div>
            </div>

            <div class="space-y-1">
                <div class="flex justify-between text-[10px] font-bold">
                    <span class="text-azul-profundo/55">Activos</span>
                    <span class="font-black text-azul-profundo">{{ $admin['activos'] ?? 0 }}</span>
                </div>
            </div>

            @if(!empty($admin['cargos']))
                <div class="mt-2 space-y-1">
                    @foreach(array_slice($admin['cargos'], 0, 3) as $cargo)
                        <div class="flex items-center justify-between rounded-lg bg-[#E6DDD3]/70 px-2 py-1">
                            <span class="truncate text-[10px] font-bold text-azul-profundo/70">{{ $cargo['nombre'] }}</span>
                            <span class="ml-1 shrink-0 text-[10px] font-black text-terracota">{{ $cargo['total'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Voluntarios --}}
        <div class="rounded-[1.5rem] border border-[#C7B5A3] bg-[#D5C7B9]/50 p-3">
            <div class="mb-2 flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#8DA280]/15 text-[#63775B]">
                    <i class="ph-fill ph-hand-heart text-base"></i>
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wide text-azul-profundo/55">Voluntarios</p>
                    <p class="text-xl font-black text-azul-profundo">{{ $vol['total'] ?? 0 }}</p>
                </div>
            </div>

            <div class="space-y-1">
                <div class="flex justify-between text-[10px] font-bold">
                    <span class="text-azul-profundo/55">Activos</span>
                    <span class="font-black text-azul-profundo">{{ $vol['activos'] ?? 0 }}</span>
                </div>
                @if(($vol['asignados'] ?? 0) > 0)
                <div class="flex justify-between text-[10px] font-bold">
                    <span class="text-azul-profundo/55">Con asignación</span>
                    <span class="font-black text-[#63775B]">{{ $vol['asignados'] }}</span>
                </div>
                @endif
            </div>

            @if(!empty($vol['areas']))
                <div class="mt-2 space-y-1">
                    @foreach(array_slice($vol['areas'], 0, 3) as $area)
                        <div class="flex items-center justify-between rounded-lg bg-[#E6DDD3]/70 px-2 py-1">
                            <span class="truncate text-[10px] font-bold text-azul-profundo/70">{{ $area['area'] }}</span>
                            <span class="ml-1 shrink-0 text-[10px] font-black text-[#63775B]">{{ $area['total'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- Red familiar --}}
    <div class="mt-3 rounded-[1.5rem] border border-[#C7B5A3] bg-[#D5C7B9]/40 p-3">
        <div class="mb-2 flex items-center gap-2">
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#F4A261]/15 text-[#D4843A]">
                <i class="ph-fill ph-house-line text-sm"></i>
            </div>
            <span class="text-[11px] font-black uppercase tracking-wide text-azul-profundo/60">Red familiar</span>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-xl bg-[#E6DDD3]/70 px-3 py-2 text-center">
                <p class="text-lg font-black text-azul-profundo">{{ $totalFam }}</p>
                <p class="text-[9px] font-black uppercase tracking-wide text-azul-profundo/45">Familiares</p>
            </div>
            <div class="rounded-xl bg-[#E6DDD3]/70 px-3 py-2 text-center">
                <p class="text-lg font-black text-[#2A9D8F]">{{ $conFam }}</p>
                <p class="text-[9px] font-black uppercase tracking-wide text-azul-profundo/45">Con familiar</p>
            </div>
            <div class="rounded-xl bg-[#E6DDD3]/70 px-3 py-2 text-center {{ $sinFam > 0 ? 'border border-terracota/30' : '' }}">
                <p class="text-lg font-black {{ $sinFam > 0 ? 'text-terracota' : 'text-azul-profundo' }}">{{ $sinFam }}</p>
                <p class="text-[9px] font-black uppercase tracking-wide text-azul-profundo/45">Sin familiar</p>
            </div>
            <div class="rounded-xl bg-[#E6DDD3]/70 px-3 py-2 text-center">
                <p class="text-lg font-black text-azul-profundo">{{ $responsables }}</p>
                <p class="text-[9px] font-black uppercase tracking-wide text-azul-profundo/45">Responsables</p>
            </div>
        </div>

        @if(!empty($parentescos))
            <div class="mt-2 flex flex-wrap gap-1.5">
                @foreach($parentescos as $p)
                    <span class="rm-badge-neutral text-[9px]">
                        {{ $p['parentesco'] }} ({{ $p['total'] }})
                    </span>
                @endforeach
            </div>
        @endif
    </div>

</div>
