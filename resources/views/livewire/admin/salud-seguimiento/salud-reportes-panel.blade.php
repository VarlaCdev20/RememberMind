<div class="min-h-screen py-8 font-sans antialiased text-[#2F3E5C]">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        {{-- ENCABEZADO --}}
        <div class="mb-8 flex flex-col justify-between gap-4 border-b border-[#C7B5A3]/50 pb-6 sm:flex-row sm:items-center">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-azul-profundo shadow-sm">
                    <i class="ph-bold ph-chart-pie-slice text-2xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-tight text-azul-profundo sm:text-4xl">
                        Reportes de salud
                    </h1>
                    <p class="mt-1 text-sm font-bold text-azul-profundo/60">
                        Generación de reportes específicos del módulo Salud y Seguimiento.
                    </p>
                </div>
            </div>
        </div>

        {{-- FORMULARIO DE REPORTES --}}
        <div class="rounded-3xl border border-[#C7B5A3]/40 bg-white p-8 shadow-sm mb-8">
            <form wire:submit.prevent="generarVistaPrevia" class="space-y-6">
                
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-xs font-black uppercase text-azul-profundo mb-2">Adulto Mayor</label>
                        <select wire:model="adultoSeleccionado" class="w-full rounded-2xl border-[#C7B5A3]/40 bg-[#F7F5F2] py-3 text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-terracota">
                            <option value="">Seleccione un paciente...</option>
                            <option value="TODOS">Todos los pacientes</option>
                            @foreach($adultos as $adulto)
                                <option value="{{ $adulto->cod_am }}">{{ $adulto->ap_paterno }} {{ $adulto->ap_materno }} {{ $adulto->nombres }} ({{ $adulto->cod_am }})</option>
                            @endforeach
                        </select>
                        @error('adultoSeleccionado') <span class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase text-azul-profundo mb-2">Tipo de Reporte</label>
                        <select wire:model="tipoReporte" class="w-full rounded-2xl border-[#C7B5A3]/40 bg-[#F7F5F2] py-3 text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-terracota">
                            <option value="">Seleccione el tipo...</option>
                            <option value="individual">Reporte individual de salud</option>
                            <option value="ficha">Reporte de ficha médica</option>
                            <option value="medicacion">Reporte de medicación activa</option>
                            <option value="administracion">Reporte de administración de medicación</option>
                            <option value="signos">Reporte de signos vitales</option>
                            <option value="valoracion">Reporte de valoración funcional</option>
                            <option value="alertas">Reporte de alertas</option>
                            <option value="completo">Reporte completo de salud y seguimiento</option>
                        </select>
                        @error('tipoReporte') <span class="text-rose-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2 pt-4 border-t border-[#C7B5A3]/20">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-azul-profundo/60 mb-2">Fecha Inicio (Opcional)</label>
                        <input type="date" wire:model="fechaInicio" class="w-full rounded-2xl border-[#C7B5A3]/40 bg-white py-3 text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-terracota">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase text-azul-profundo/60 mb-2">Fecha Fin (Opcional)</label>
                        <input type="date" wire:model="fechaFin" class="w-full rounded-2xl border-[#C7B5A3]/40 bg-white py-3 text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-terracota">
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-6 mt-4 border-t border-[#C7B5A3]/20">
                    <button type="submit" class="rounded-xl bg-azul-profundo px-6 py-3 text-xs font-black uppercase text-white shadow-md hover:bg-azul-profundo/80 transition-colors flex items-center gap-2">
                        <i class="ph-bold ph-eye"></i> Generar vista previa
                    </button>
                    <button type="button" onclick="window.print()" class="rounded-xl bg-terracota px-6 py-3 text-xs font-black uppercase text-white shadow-md hover:bg-terracota-dark transition-colors flex items-center gap-2">
                        <i class="ph-bold ph-printer"></i> Imprimir / Exportar PDF
                    </button>
                </div>
            </form>
        </div>
        
        {{-- ÁREA DE VISTA PREVIA --}}
        <div class="hidden print:block rounded-3xl border border-[#C7B5A3]/40 bg-white p-8 shadow-sm">
            <div class="text-center mb-8 border-b border-[#C7B5A3]/40 pb-4">
                <h2 class="text-2xl font-black uppercase text-azul-profundo">Casa Amandita</h2>
                <p class="text-sm font-bold text-azul-profundo/60">Reporte Institucional de Salud y Seguimiento</p>
            </div>
            
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <i class="ph-fill ph-file-text text-5xl text-[#C7B5A3]/40 mb-4"></i>
                <h3 class="text-lg font-black text-azul-profundo">Generación de Datos en Espera</h3>
                <p class="text-sm text-azul-profundo/60 mt-1">Selecciona los parámetros y presiona Generar Vista Previa o Imprimir.</p>
            </div>
        </div>
    </div>
</div>
