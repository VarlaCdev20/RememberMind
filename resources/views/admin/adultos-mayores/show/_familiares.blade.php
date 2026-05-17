{{-- TAB FAMILIARES --}}
            
<section
    x-show="tab === 'familiares'"
    x-transition.opacity.duration.250ms
    class="space-y-4"
>
    {{-- Encabezado de sección --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
        <div class="flex flex-col gap-4 border-b border-[#D5C7B9] px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[#8EA17D]">
                    Red de apoyo
                </span>

                <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">
                    Familiares vinculados
                </h2>

                <p class="mt-1 text-xs font-bold leading-5 text-[#2F3E5C]/55">
                    Administra los contactos responsables, familiares de referencia y vínculos importantes del adulto mayor.
                </p>
            </div>

            <button type="button"
                    @click="abrir('familiar')"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#8EA17D] px-4 py-2.5 text-xs font-black text-white shadow-[0_10px_20px_rgba(142,161,125,0.18)] transition hover:-translate-y-0.5 hover:bg-[#7C916A] active:scale-[0.98]">
                <i class="ph-bold ph-user-plus"></i>
                Vincular familiar
            </button>
        </div>

        {{-- Métricas rápidas --}}
        <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Total vinculados
                </p>
                <p class="mt-2 text-2xl font-black text-[#617453]">
                    {{ $totalFamiliares }}
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Responsable
                </p>
                <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                    {{ $totalFamiliares > 0 ? 'Por verificar' : 'No asignado' }}
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Estado de red
                </p>
                <p class="mt-2 text-sm font-black {{ $totalFamiliares > 0 ? 'text-[#617453]' : 'text-[#D96F58]' }}">
                    {{ $totalFamiliares > 0 ? 'Con apoyo registrado' : 'Sin red registrada' }}
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Acción sugerida
                </p>
                <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                    {{ $totalFamiliares > 0 ? 'Actualizar contactos' : 'Vincular familiar' }}
                </p>
            </div>
        </div>
    </section>

    {{-- Tabla de familiares --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#2F3E5C]">
                <thead class="bg-[#D5C7B9]/50 text-[10px] uppercase tracking-widest text-[#2F3E5C]/70">
                    <tr>
                        <th class="px-6 py-4">Familiar / Contacto</th>
                        <th class="px-6 py-4">Parentesco</th>
                        <th class="px-6 py-4">Contacto</th>
                        <th class="px-6 py-4">Responsable</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#D5C7B9]/40">
                    @forelse($familiaresActivos as $familiar)
                        @php
                            $familiarObj = is_object($familiar) ? $familiar : null;
                            $codFamiliar = optional($familiarObj)->cod_fam;
                            $nombreFamiliar = trim(
                                (optional($familiarObj)->nombres ?? optional($familiarObj)->nombre ?? '') . ' ' .
                                (optional($familiarObj)->ap_paterno ?? '') . ' ' .
                                (optional($familiarObj)->ap_materno ?? '')
                            );
                            $parentescoFamiliar = optional($familiarObj)->parentesco
                                ?? optional($familiarObj)->parentesco_vinculo
                                ?? optional(optional($familiarObj)->pivot)->parentesco_vinculo
                                ?? 'No definido';
                            $telefonoFamiliar = optional($familiarObj)->telefono
                                ?? optional($familiarObj)->celular
                                ?? 'No registrado';
                            $esResponsable = optional($familiarObj)->es_responsable
                                ?? optional(optional($familiarObj)->pivot)->es_responsable
                                ?? false;
                            $estadoVinc = strtoupper(optional(optional($familiarObj)->pivot)->estado ?? 'ACTIVO');
                        @endphp
                        <tr class="group transition hover:bg-[#F2EBE3]/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#8EA17D]/15 text-sm font-black text-[#617453]">
                                        {{ strtoupper(substr($nombreFamiliar ?: 'F', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-black text-[#2F3E5C]">{{ $nombreFamiliar ?: 'Familiar' }}</p>
                                        <p class="text-[10px] font-bold text-[#2F3E5C]/50 uppercase">ID: {{ $codFamiliar }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-[#2F3E5C]/70">{{ $parentescoFamiliar }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-black text-[#2F3E5C]">{{ $telefonoFamiliar }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($esResponsable)
                                    <span class="inline-flex items-center rounded-full bg-[#E27D60]/15 px-2.5 py-0.5 text-[9px] font-black uppercase text-[#D96F58]">
                                        Sí
                                    </span>
                                @else
                                    <span class="text-[9px] font-bold text-[#2F3E5C]/30 uppercase">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[9px] font-black uppercase {{ $estadoVinc === 'ACTIVO' ? 'bg-[#8EA17D]/15 text-[#617453]' : 'bg-[#9A7B60]/15 text-[#7A604B]' }}">
                                    {{ $estadoVinc }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-1.5">
                                    <button type="button" @click="abrir('familiar', @js($familiar), false, true)" title="Ver detalle" class="rounded-lg bg-[#2F3E5C]/10 p-2 text-[#2F3E5C] transition hover:bg-[#2F3E5C] hover:text-white">
                                        <i class="ph-bold ph-eye"></i>
                                    </button>
                                    <button type="button" @click="abrir('familiar', @js($familiar), true, false)" title="Editar" class="rounded-lg bg-[#2F3E5C]/10 p-2 text-[#2F3E5C] transition hover:bg-[#2F3E5C] hover:text-white">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                    </button>
                                    @if($estadoVinc === 'ACTIVO')
                                        <form action="{{ route('admin.adultos-mayores.familiares.destroy', [$idAdulto, $codFamiliar]) }}" method="POST" onsubmit="confirmarAccion(event, 'Desactivar vínculo familiar', 'El vínculo con este familiar se marcará como inactivo. Se conservará el registro histórico.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Desactivar vínculo" class="rounded-lg bg-terracota/10 p-2 text-terracota transition hover:bg-terracota hover:text-white">
                                                <i class="ph-bold ph-user-minus"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.adultos-mayores.familiares.restore', [$idAdulto, $codFamiliar]) }}" method="POST" onsubmit="confirmarAccion(event, 'Restaurar vínculo', 'El familiar volverá a estar vinculado activamente al expediente.')">
                                            @csrf @method('PATCH')
                                            <button type="submit" title="Restaurar vínculo" class="rounded-lg bg-[#8EA17D]/10 p-2 text-[#8EA17D] transition hover:bg-[#8EA17D] hover:text-white">
                                                <i class="ph-bold ph-arrow-counter-clockwise"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#8EA17D]/10 text-[#617453]">
                                    <i class="ph-bold ph-heart text-2xl"></i>
                                </div>
                                <h4 class="mt-4 text-sm font-black text-[#2F3E5C]">Sin familiares vinculados</h4>
                                <p class="mx-auto mt-2 max-w-xs text-xs font-bold text-[#2F3E5C]/50">
                                    Registra los contactos responsables para fortalecer el seguimiento institucional.
                                </p>
                                <button type="button" @click="abrir('familiar')" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#8EA17D] px-4 py-2 text-xs font-black text-white transition hover:bg-[#7C916A]">
                                    <i class="ph-bold ph-plus"></i> Vincular primer familiar
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Vínculos Familiares Desactivados --}}
        @if(count($familiaresInactivos) > 0)
        <div class="mt-8 border-t border-[#D5C7B9]/30 pt-6">
            <div class="mb-4 flex items-center justify-between">
                <h4 class="text-xs font-black uppercase tracking-widest text-terracota/60 flex items-center gap-2">
                    <i class="ph-bold ph-user-minus"></i> Vínculos Familiares Desactivados
                </h4>
                <span class="rounded-full bg-terracota/10 px-2 py-0.5 text-[9px] font-black text-terracota uppercase tracking-tighter">Historial Institucional</span>
            </div>
            <div class="overflow-hidden rounded-2xl border border-[#D5C7B9]/50 opacity-60 grayscale-[50%] transition-all hover:grayscale-0 hover:opacity-100 bg-white/30 backdrop-blur-sm shadow-sm">
                <table class="w-full text-left text-sm text-[#2F3E5C]">
                    <thead class="bg-[#D5C7B9]/20 text-[9px] uppercase tracking-widest text-[#2F3E5C]/50">
                        <tr>
                            <th class="px-6 py-3">Familiar</th>
                            <th class="px-6 py-3">Parentesco</th>
                            <th class="px-6 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D5C7B9]/20">
                        @foreach($familiaresInactivos as $finac)
                            @php
                                $nombreFinac = (optional($finac)->nombres ?? optional($finac)->nombre ?? '') . ' ' . (optional($finac)->ap_paterno ?? '');
                            @endphp
                            <tr class="hover:bg-[#F2EBE3]/40 transition">
                                <td class="px-6 py-3 font-bold text-[#2F3E5C]/70 text-xs">{{ $nombreFinac }}</td>
                                <td class="px-6 py-3 text-xs text-[#2F3E5C]/60">{{ optional($finac->pivot)->parentesco_vinculo }}</td>
                                <td class="px-6 py-3 text-right">
                                    <form action="{{ route('admin.adultos-mayores.familiares.restore', [$adulto->cod_am, $finac->cod_fam]) }}" method="POST" onsubmit="confirmarAccion(event, 'Restaurar vínculo', 'El familiar volverá a estar vinculado activamente al expediente.')">
                                        @csrf @method('PATCH')
                                        <button type="submit" title="Restaurar vínculo" class="rounded-lg bg-[#8EA17D]/10 p-1.5 text-[#8EA17D] hover:bg-[#8EA17D] hover:text-white transition">
                                            <i class="ph-bold ph-arrow-counter-clockwise"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </section>
</section>

           