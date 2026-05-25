<div class="space-y-6">
    <section class="overflow-hidden rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/78 shadow-sm backdrop-blur-xl">
        <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
        <div class="p-5">
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-[#E27D60]">Documentación institucional</span>
            <h2 class="mt-1 text-xl font-black tracking-tight text-[#2F3E5C]">Reportes de salud</h2>
            <p class="mt-1 max-w-2xl text-xs font-bold leading-relaxed text-[#2F3E5C]/62">
                Genere reportes clínico-asistenciales con selección de adulto mayor, rango de fechas y tipo de informe.
            </p>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['key' => 'completo', 'tag' => 'General', 'title' => 'Censo de salud', 'text' => 'Resumen institucional de todos los expedientes.', 'icon' => 'ph-files', 'color' => 'text-[#2F3E5C]', 'bg' => 'bg-[#2F3E5C]/8'],
            ['key' => 'alertas', 'tag' => 'Control', 'title' => 'Alertas preventivas', 'text' => 'Pendientes críticos y preventivos del módulo.', 'icon' => 'ph-warning-circle', 'color' => 'text-[#E27D60]', 'bg' => 'bg-[#E27D60]/10'],
            ['key' => 'medicacion', 'tag' => 'Farmacia', 'title' => 'Tratamientos', 'text' => 'Medicaciones activas e historial de control.', 'icon' => 'ph-pill', 'color' => 'text-[#63775B]', 'bg' => 'bg-[#8DA280]/14'],
            ['key' => 'signos', 'tag' => 'Diario', 'title' => 'Signos vitales', 'text' => 'Controles fisiológicos recientes y evolución.', 'icon' => 'ph-activity', 'color' => 'text-[#9A6A2F]', 'bg' => 'bg-[#D9A05B]/12'],
        ] as $card)
            <button type="button" wire:click="$set('tipoReporte', '{{ $card['key'] }}')" class="group rounded-[1.45rem] border p-5 text-left shadow-sm backdrop-blur-xl transition hover:-translate-y-1 hover:shadow-[0_16px_34px_rgba(47,62,92,0.12)] {{ $tipoReporte === $card['key'] ? 'border-[#E27D60]/45 bg-[#F3ECE4]/90' : 'border-[#C7B5A3]/60 bg-[#E6DDD3]/55' }}">
                <div class="flex items-start justify-between gap-3">
                    <span class="rounded-full px-2.5 py-1 text-[8px] font-black uppercase tracking-wider {{ $card['bg'] }} {{ $card['color'] }}">{{ $card['tag'] }}</span>
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl {{ $card['bg'] }} {{ $card['color'] }} transition group-hover:scale-105">
                        <i class="ph-bold {{ $card['icon'] }} text-xl"></i>
                    </span>
                </div>
                <h3 class="mt-4 text-base font-black text-[#2F3E5C]">{{ $card['title'] }}</h3>
                <p class="mt-1 text-[11px] font-bold leading-relaxed text-[#2F3E5C]/58">{{ $card['text'] }}</p>
            </button>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 shadow-sm backdrop-blur-xl">
        <div class="grid lg:grid-cols-[240px_1fr]">
            <aside class="border-b border-[#C7B5A3]/45 bg-[#D5C7B9]/48 p-6 lg:border-b-0 lg:border-r">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-[#C7B5A3]/55 bg-[#E6DDD3]/70 text-[#E27D60] shadow-sm">
                    <i class="ph-bold ph-printer text-3xl"></i>
                </div>
                <h3 class="mt-4 text-lg font-black text-[#2F3E5C]">Generador</h3>
                <p class="mt-1 text-xs font-bold leading-relaxed text-[#2F3E5C]/60">
                    Configure parámetros antes de generar la vista previa o imprimir el documento.
                </p>
                <div class="mt-5 rounded-2xl border border-[#C7B5A3]/40 bg-[#F3ECE4]/55 p-3">
                    <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/45">Estado</p>
                    <p class="mt-1 text-xs font-black text-[#63775B]">Disponible para vista previa</p>
                </div>
            </aside>

            <div class="p-5 sm:p-7">
                <form wire:submit.prevent="generarVistaPrevia" class="space-y-5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Adulto mayor</label>
                            <select wire:model="adultoSeleccionado" class="w-full rounded-xl border border-[#C7B5A3]/65 bg-[#E6DDD3]/70 px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                                <option value="">Seleccione un paciente...</option>
                                <option value="TODOS">Todos los pacientes activos</option>
                                @foreach($adultos as $adulto)
                                    <option value="{{ $adulto->cod_am }}">{{ $adulto->ap_paterno }} {{ $adulto->ap_materno }} {{ $adulto->nombres }}</option>
                                @endforeach
                            </select>
                            @error('adultoSeleccionado') <span class="mt-1 flex items-center text-[10px] font-black text-[#E27D60]"><i class="ph-bold ph-warning mr-1"></i>{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Tipo de reporte</label>
                            <select wire:model="tipoReporte" class="w-full rounded-xl border border-[#C7B5A3]/65 bg-[#E6DDD3]/70 px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                                <option value="">Seleccione el tipo...</option>
                                <option value="individual">Ficha de salud individual</option>
                                <option value="medicacion">Control de tratamientos</option>
                                <option value="signos">Historial de signos vitales</option>
                                <option value="valoracion">Matriz de valoración funcional</option>
                                <option value="alertas">Reporte de alertas críticas</option>
                                <option value="completo">Censo completo de salud</option>
                            </select>
                            @error('tipoReporte') <span class="mt-1 flex items-center text-[10px] font-black text-[#E27D60]"><i class="ph-bold ph-warning mr-1"></i>{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 border-t border-[#C7B5A3]/35 pt-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Fecha inicio</label>
                            <input type="date" wire:model="fechaInicio" class="w-full rounded-xl border border-[#C7B5A3]/65 bg-[#E6DDD3]/70 px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Fecha fin</label>
                            <input type="date" wire:model="fechaFin" class="w-full rounded-xl border border-[#C7B5A3]/65 bg-[#E6DDD3]/70 px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-2 border-t border-[#C7B5A3]/35 pt-5 sm:flex-row sm:justify-end">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#C7B5A3]/70 bg-[#D5C7B9]/65 px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C] transition hover:bg-[#C7B5A3]/75 active:scale-95">
                            <i class="ph-bold ph-eye text-sm"></i>
                            Vista previa
                        </button>
                        <button type="button" onclick="window.print()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2F3E5C] px-5 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-[0_8px_18px_rgba(47,62,92,0.18)] transition hover:-translate-y-0.5 hover:bg-[#5B5F97] active:scale-95">
                            <i class="ph-bold ph-download-simple text-sm"></i>
                            Generar documento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="hidden print:block rounded-2xl border border-[#C7B5A3]/40 bg-white p-8 shadow-sm">
        <div class="mb-8 border-b border-[#C7B5A3]/40 pb-5 text-center">
            <h2 class="text-2xl font-black uppercase tracking-tight text-[#2F3E5C]">Casa Amandita</h2>
            <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-[#E27D60]">Reporte institucional de salud</p>
        </div>
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-[#C7B5A3]/60 bg-[#F7F5F2] py-12 text-center">
            <i class="ph-bold ph-file-pdf mb-4 text-4xl text-[#C7B5A3]"></i>
            <h3 class="text-base font-black text-[#2F3E5C]">Documento en espera</h3>
            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/60">Configure los parámetros y genere la vista previa.</p>
        </div>
    </section>
</div>
