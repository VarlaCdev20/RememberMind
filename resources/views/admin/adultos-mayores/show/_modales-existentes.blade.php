<template x-if="modal">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-[#2F3E5C]/45 p-4 backdrop-blur-sm" x-transition.opacity>
                <div class="relative w-full max-w-4xl rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3] shadow-[0_24px_60px_rgba(47,62,92,0.35)]">
                    <div class="flex items-center justify-between border-b border-[#C7B5A3] p-5">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-widest text-[#E27D60]">
                                Acción rápida · {{ $nombreCompleto ?: 'Adulto mayor' }}
                            </p>
                            <h2 class="text-xl font-black text-[#2F3E5C]"
                                x-text="{
                                    familiar: (isViewing ? 'Ver ' : (isEditing ? 'Editar ' : 'Vincular ')) + 'familiar',
                                    observacion: (isViewing ? 'Ver ' : (isEditing ? 'Editar ' : 'Registrar ')) + 'observación',
                                    atencion: (isViewing ? 'Ver ' : (isEditing ? 'Editar ' : 'Registrar ')) + 'atención',
                                    evaluacion: (isViewing ? 'Ver ' : (isEditing ? 'Editar ' : 'Registrar ')) + 'evaluación cognitiva',
                                    actividad: (isViewing ? 'Ver ' : (isEditing ? 'Editar ' : 'Registrar ')) + 'actividad',
                                    documento: (isViewing ? 'Ver ' : (isEditing ? 'Editar ' : 'Subir ')) + 'documento',
                                    voluntario: (isViewing ? 'Ver ' : (isEditing ? 'Editar ' : 'Asignar ')) + 'voluntario'
                                }[modal]">
                            </h2>
                        </div>

                        <button type="button"
                                @click="cerrar()"
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#D5C7B9] text-[#2F3E5C] transition hover:bg-[#2F3E5C] hover:text-white active:scale-95">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </div>

                    <div class="max-h-[72vh] overflow-y-auto p-5">
                        {{-- Mensajes de Error de Validación --}}
                        @if($errors->any())
                            <div class="mb-4 rounded-xl border border-terracota/30 bg-terracota/10 p-4">
                                <p class="mb-2 text-xs font-black uppercase tracking-widest text-terracota">
                                    <i class="ph-bold ph-warning-circle mr-1"></i> Errores de validación
                                </p>
                                <ul class="list-inside list-disc text-xs font-bold text-terracota/80">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif


                        {{-- Familiar --}}
                        <form id="form-familiar" method="POST" :action="isEditing ? `{{ route('admin.adultos-mayores.familiares.update', [$idAdulto, 'ID']) }}`.replace('ID', recordData.cod_fam) : `{{ route('admin.adultos-mayores.familiares.store', $idAdulto) }}`" x-show="modal === 'familiar'" class="grid gap-4 md:grid-cols-2" onsubmit="procesarFormulario(event)">
                            @csrf
                            <input type="hidden" name="_method" :value="isEditing ? 'PATCH' : 'POST'">
                            <input type="hidden" name="estado" value="ACTIVO">
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Nombre completo del Familiar</label>
                                <input name="nombre_nuevo" :value="recordData.usuario?.name || recordData.nombres || ''" :disabled="isViewing" required class="w-full rounded-xl border {{ $errors->has('nombre_nuevo') ? 'border-terracota bg-terracota/5' : 'border-[#C7B5A3] bg-[#D5C7B9]/70' }} px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]" placeholder="Ej. Juan Pérez">
                                @error('nombre_nuevo') <span class="mt-1 block text-xs font-black text-terracota">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Correo Electrónico (Opcional)</label>
                                <input name="email_nuevo" type="email" :value="recordData.usuario?.email || recordData.email || ''" :disabled="isViewing" class="w-full rounded-xl border {{ $errors->has('email_nuevo') ? 'border-terracota bg-terracota/5' : 'border-[#C7B5A3] bg-[#D5C7B9]/70' }} px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]" placeholder="ejemplo@correo.com">
                                @error('email_nuevo') <span class="mt-1 block text-xs font-black text-terracota">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Parentesco</label>
                                <select name="parentesco_vinculo" :value="recordData.pivot?.parentesco_vinculo || recordData.parentesco_vinculo || ''" :disabled="isViewing" required class="w-full rounded-xl border {{ $errors->has('parentesco_vinculo') ? 'border-terracota bg-terracota/5' : 'border-[#C7B5A3] bg-[#D5C7B9]/70' }} px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                                    <option value="">Seleccione parentesco</option>
                                    <option value="Hijo/a">Hijo/a</option>
                                    <option value="Conyuge">Cónyuge</option>
                                    <option value="Hermano/a">Hermano/a</option>
                                    <option value="Nieto/a">Nieto/a</option>
                                    <option value="Sobrino/a">Sobrino/a</option>
                                    <option value="Tutor Legal">Tutor Legal</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                @error('parentesco_vinculo') <span class="mt-1 block text-xs font-black text-terracota">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Nivel de Responsabilidad</label>
                                <select name="es_responsable" :value="recordData.pivot?.es_responsable ? '1' : '0'" :disabled="isViewing" required class="w-full rounded-xl border {{ $errors->has('es_responsable') ? 'border-terracota bg-terracota/5' : 'border-[#C7B5A3] bg-[#D5C7B9]/70' }} px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                                    <option value="0">Contacto referencial</option>
                                    <option value="1">Responsable principal (Firma autorizado)</option>
                                </select>
                                @error('es_responsable') <span class="mt-1 block text-xs font-black text-terracota">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Observaciones del vínculo / Horarios de visita</label>
                                <textarea name="observaciones" :value="recordData.pivot?.observaciones || recordData.observaciones || ''" :disabled="isViewing" class="w-full rounded-xl border {{ $errors->has('observaciones') ? 'border-terracota bg-terracota/5' : 'border-[#C7B5A3] bg-[#D5C7B9]/70' }} px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]" rows="3" placeholder="Restricciones de visita, observaciones médicas que el familiar deba conocer, etc."></textarea>
                                @error('observaciones') <span class="mt-1 block text-xs font-black text-terracota">{{ $message }}</span> @enderror
                            </div>
                            <!-- Botón removido por estar duplicado en el footer del modal -->
                        </form>

                        {{-- Observación --}}
                        <form id="form-observacion" method="POST" :action="isEditing ? `{{ route('admin.adultos-mayores.observaciones.update', [$idAdulto, 'ID']) }}`.replace('ID', recordData.cod_obs_adul) : `{{ route('admin.adultos-mayores.observaciones.store', $idAdulto) }}`" x-show="modal === 'observacion'" class="grid gap-4 md:grid-cols-2" onsubmit="procesarFormulario(event)">
                            @csrf
                            <input type="hidden" name="_method" :value="isEditing ? 'PATCH' : 'POST'">
                            <div>
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Fecha de Observación</label>
                                <input type="date" name="fecha" :value="recordData.fecha?.split(' ')[0] || '{{ now()->format('Y-m-d') }}'" :disabled="isViewing" required class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Tipo de Seguimiento</label>
                                <select name="tipo_obs" :value="recordData.tipo_obs || ''" :disabled="isViewing" required class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                                    <option value="">Clasificación</option>
                                    <option value="Conductual">Comportamiento / Conductual</option>
                                    <option value="Emocional">Estado de ánimo / Emocional</option>
                                    <option value="Cognitiva">Estado Cognitivo</option>
                                    <option value="Salud General">Salud General / Física</option>
                                    <option value="Alimentación">Nutrición / Alimentación</option>
                                    <option value="Administrativa">Administrativa / Pagos</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Estado Institucional (Se actualizará en la ficha)</label>
                                <select name="cod_est_adul" :value="recordData.cod_est_adul || '{{ $adulto->cod_est_adul }}'" :disabled="isViewing" required class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                                    <option value="">Confirmar Estado Actual</option>
                                    @php /** @var object $estadoObj */ @endphp
                                    @foreach($estadosAdulto ?? [] as $estadoObj)
                                        <option value="{{ $estadoObj->cod_est_adul }}" @selected($adulto->cod_est_adul == $estadoObj->cod_est_adul)>{{ $estadoObj->estado }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Descripción Detallada</label>
                                <textarea name="descripcion" :value="recordData.descripcion || ''" :disabled="isViewing" required class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]" rows="4" placeholder="Describa de manera profesional el incidente, síntoma o evento observado en el turno..."></textarea>
                            </div>
                            <!-- Botón removido por estar duplicado en el footer del modal -->
                        </form>

                        {{-- Atención --}}
                        <form
                            id="form-atencion"
                            method="POST"
                            :action="isEditing ? `{{ route('admin.adultos-mayores.atenciones.update', [$idAdulto, 'ID']) }}`.replace('ID', recordData.cod_aten_adul) : `{{ route('admin.adultos-mayores.atenciones.store', $idAdulto) }}`"
                            x-show="modal === 'atencion'"
                            class="space-y-4"
                            onsubmit="procesarFormulario(event)"
                        >
                            @csrf
                            <input type="hidden" name="_method" :value="isEditing ? 'PATCH' : 'POST'">

                            {{-- Descripción del formulario --}}
                            <div class="rounded-xl bg-[#D5C7B9]/60 border border-[#C7B5A3] p-3 text-xs font-bold leading-5 text-azul-profundo/70">
                                <i class="ph-fill ph-stethoscope mr-1 text-terracota"></i>
                                Registra una atención institucional realizada al adulto mayor. Este registro forma parte del seguimiento histórico clínico y administrativo.
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                {{-- Fecha --}}
                                <div>
                                    <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">
                                        Fecha de Atención *
                                    </label>
                                    <input
                                        type="date"
                                        name="fecha"
                                        :value="recordData.fecha?.split(' ')[0] || '{{ now()->format('Y-m-d') }}'"
                                        :disabled="isViewing"
                                        class="w-full rounded-xl border {{ $errors->has('fecha') ? 'border-terracota bg-terracota/5' : 'border-[#C7B5A3]' }} bg-[#D5C7B9]/70 px-4 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:bg-[#E6DDD3] focus:ring-4 focus:ring-[#E27D60]/10"
                                    >
                                    @error('fecha')
                                        <span class="mt-1 flex items-center gap-1 text-xs font-black text-terracota">
                                            <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                {{-- Hora --}}
                                <div>
                                    <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">
                                        Hora de Atención *
                                    </label>
                                    <input
                                        type="time"
                                        name="hora"
                                        :value="recordData.hora || '{{ now()->format('H:i') }}'"
                                        :disabled="isViewing"
                                        class="w-full rounded-xl border {{ $errors->has('hora') ? 'border-terracota bg-terracota/5' : 'border-[#C7B5A3]' }} bg-[#D5C7B9]/70 px-4 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:bg-[#E6DDD3] focus:ring-4 focus:ring-[#E27D60]/10"
                                    >
                                    @error('hora')
                                        <span class="mt-1 flex items-center gap-1 text-xs font-black text-terracota">
                                            <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                {{-- Tipo de Atención --}}
                                <div>
                                    <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">
                                        Tipo de Atención *
                                    </label>
                                    @php
                                        $tiposAtencionesLista = isset($tiposAtenciones) && is_iterable($tiposAtenciones)
                                            ? collect($tiposAtenciones)
                                            : collect();
                                    @endphp

                                    @if($tiposAtencionesLista->isEmpty())
                                        <div class="rounded-xl border border-[#D9A27C]/40 bg-[#D9A27C]/10 p-3 text-xs font-bold text-[#B07446]">
                                            <i class="ph-bold ph-warning-circle mr-1"></i>
                                            No existen tipos de atención registrados. Contacte al administrador del sistema.
                                        </div>
                                    @else
                                        <select
                                            name="cod_tipo_aten"
                                            :value="recordData.cod_tipo_aten || ''"
                                            :disabled="isViewing"
                                            class="w-full rounded-xl border {{ $errors->has('cod_tipo_aten') ? 'border-terracota bg-terracota/5' : 'border-[#C7B5A3]' }} bg-[#D5C7B9]/70 px-4 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:bg-[#E6DDD3] focus:ring-4 focus:ring-[#E27D60]/10"
                                        >
                                            <option value="">Seleccionar tipo...</option>
                                            @foreach($tiposAtencionesLista as $tipo)
                                                <option
                                                    value="{{ $tipo->cod_tipo_aten }}"
                                                    @selected(old('cod_tipo_aten') == $tipo->cod_tipo_aten)
                                                >
                                                    {{ $tipo->tipo }}
                                                    @if($tipo->descripcion) — {{ Str::limit($tipo->descripcion, 40) }} @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('cod_tipo_aten')
                                        <span class="mt-1 flex items-center gap-1 text-xs font-black text-terracota">
                                            <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                {{-- Estado --}}
                                <div>
                                    <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">
                                        Estado *
                                    </label>
                                    <select
                                        name="estado"
                                        :value="recordData.estado || ''"
                                        :disabled="isViewing"
                                        class="w-full rounded-xl border {{ $errors->has('estado') ? 'border-terracota bg-terracota/5' : 'border-[#C7B5A3]' }} bg-[#D5C7B9]/70 px-4 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:bg-[#E6DDD3] focus:ring-4 focus:ring-[#E27D60]/10"
                                    >
                                        <option value="">Seleccionar estado...</option>
                                        <option value="PENDIENTE"  @selected(old('estado') === 'PENDIENTE')>Pendiente</option>
                                        <option value="REALIZADA"  @selected(old('estado') === 'REALIZADA')>Realizada</option>
                                        <option value="FINALIZADA" @selected(old('estado') === 'FINALIZADA')>Finalizada</option>
                                        <option value="CANCELADA"  @selected(old('estado') === 'CANCELADA')>Cancelada</option>
                                    </select>
                                    @error('estado')
                                        <span class="mt-1 flex items-center gap-1 text-xs font-black text-terracota">
                                            <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                {{-- Observaciones / Notas --}}
                                <div
                                    class="md:col-span-2"
                                    x-data="{ obs: '{{ old('obs') }}' }"
                                >
                                    <div class="mb-1 flex items-center justify-between">
                                        <label class="block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60">
                                            Observaciones Clínicas
                                        </label>
                                        <span class="text-[10px] font-bold text-[#2F3E5C]/45" x-text="obs.length + '/1000'"></span>
                                    </div>
                                    <textarea
                                        name="obs"
                                        rows="4"
                                        x-model="obs"
                                        :value="recordData.obs || ''"
                                        :disabled="isViewing"
                                        maxlength="1000"
                                        placeholder="Diagnóstico preventivo, tratamiento indicado, medicación administrada, próximos controles..."
                                        class="w-full resize-none rounded-xl border {{ $errors->has('obs') ? 'border-terracota bg-terracota/5' : 'border-[#C7B5A3]' }} bg-[#D5C7B9]/70 px-4 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none transition placeholder:text-[#2F3E5C]/35 focus:border-[#E27D60] focus:bg-[#E6DDD3] focus:ring-4 focus:ring-[#E27D60]/10"
                                    ></textarea>
                                    @error('obs')
                                        <span class="mt-1 flex items-center gap-1 text-xs font-black text-terracota">
                                            <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                                        </span>
                                    @enderror
                                    <p class="mt-1 text-[10px] font-bold text-[#2F3E5C]/45">
                                        Campo opcional. Use vocabulario clínico claro y objetivo.
                                    </p>
                                </div>
                            </div>

                            {{-- Botones de acción --}}
                            <!-- Footer interno removido por estar duplicado con el footer principal del modal -->
                        </form>

                        {{-- Evaluación Cognitiva --}}
                        <form id="form-evaluacion" method="POST" action="{{ route('admin.adultos-mayores.evaluaciones.store', $idAdulto) }}" x-show="modal === 'evaluacion'" class="grid gap-4 md:grid-cols-2" onsubmit="procesarFormulario(event)">
                            @csrf
                            <input type="hidden" name="_method" :value="isEditing ? 'PATCH' : 'POST'">
                            <div>
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Tipo de Evaluación</label>
                                <select name="cod_tipo_eval" :value="recordData.cod_tipo_eval || ''" :disabled="isViewing || isEditing" required class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                                    <option value="">Seleccione prueba</option>
                                    @foreach($tiposEvaluaciones as $tipo)
                                        <option value="{{ $tipo->cod_tipo_eval }}">{{ $tipo->nombre }} (Máx: {{ $tipo->puntaje_maximo }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Puntaje Obtenido (0-30)</label>
                                <input type="number" name="puntaje_total" :value="recordData.puntaje_total || ''" :disabled="isViewing || isEditing" min="0" max="30" step="1" required class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]" placeholder="Ej. 26">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Fecha de Aplicación</label>
                                <input type="date" name="fecha_eval" :value="recordData.fecha_eval?.split(' ')[0] || '{{ now()->format('Y-m-d') }}'" :disabled="isViewing || isEditing" required class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Observaciones y Hallazgos</label>
                                <textarea name="observaciones" :value="recordData.observaciones || ''" :disabled="isViewing || isEditing" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]" rows="4" placeholder="Detalle fallos en subpruebas específicas (ej. memoria, orientación, dibujo)..."></textarea>
                            </div>
                        </form>

                        {{-- Actividad --}}
                        <form id="form-actividad" method="POST" :action="isEditing ? `{{ route('admin.adultos-mayores.actividades.update', [$idAdulto, 'ID']) }}`.replace('ID', recordData.cod_act_adul) : `{{ route('admin.adultos-mayores.actividades.store', $idAdulto) }}`" x-show="modal === 'actividad'" class="grid gap-4 md:grid-cols-2" onsubmit="procesarFormulario(event)">
                            @csrf
                            <input type="hidden" name="_method" :value="isEditing ? 'PATCH' : 'POST'">
                            <div>
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Fecha y Hora</label>
                                <div class="flex gap-2">
                                    <input type="date" name="fecha" :value="recordData.fecha?.split(' ')[0] || '{{ now()->format('Y-m-d') }}'" :disabled="isViewing || isEditing" required class="w-2/3 rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                                    <input type="time" name="hora" :value="recordData.hora || '{{ now()->format('H:i') }}'" :disabled="isViewing || isEditing" required class="w-1/3 rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Área Terapéutica / Actividad</label>
                                <select name="cod_tipo_act" :value="recordData.cod_tipo_act || ''" :disabled="isViewing || isEditing" required class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                                    <option value="">Seleccione terapia</option>
                                    @foreach($tiposActividades as $tipoAct)
                                        <option value="{{ $tipoAct->cod_tipo_act }}">{{ $tipoAct->tipo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Estado de Participación</label>
                                <select name="estado" :value="recordData.estado || 'REALIZADA'" :disabled="isViewing" required class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                                    <option value="REALIZADA">Realizada</option>
                                    <option value="PENDIENTE">Pendiente</option>
                                    <option value="CANCELADA">Cancelada</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Desempeño y Logros (Observaciones)</label>
                                <textarea name="obs" :value="recordData.obs || ''" :disabled="isViewing" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]" rows="3" placeholder="Describa el comportamiento durante la sesión, avances cognitivos o motores..."></textarea>
                            </div>
                        </form>

                        {{-- Documento --}}
                        <form id="form-documento" method="POST" :action="isEditing ? `{{ route('admin.adultos-mayores.documentos.update', [$idAdulto, 'ID']) }}`.replace('ID', recordData.cod_doc_am) : `{{ route('admin.adultos-mayores.documentos.store', $idAdulto) }}`" enctype="multipart/form-data" x-show="modal === 'documento'" class="grid gap-4 md:grid-cols-2" onsubmit="procesarFormulario(event)">
                            @csrf
                            <input type="hidden" name="_method" :value="isEditing ? 'PATCH' : 'POST'">
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Título del Documento</label>
                                <input name="nom_doc" :value="recordData.nom_doc || ''" :disabled="isViewing" required class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]" placeholder="Ej. Resultados de laboratorio completo">
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Categoría del Expediente</label>
                                <select name="tipo_doc" :value="recordData.tipo_doc || ''" :disabled="isViewing" required class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                                    <option value="">Seleccione categoría</option>
                                    <option value="Historial Médico">Historial Médico / Epicrisis</option>
                                    <option value="Exámenes">Exámenes y Laboratorios</option>
                                    <option value="Recetas">Recetas Médicas</option>
                                    <option value="Administrativo">Contrato Administrativo</option>
                                    <option value="Legal">Documentación Legal / CI</option>
                                    <option value="Consentimiento">Consentimiento Informado</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Fecha del Documento</label>
                                <input type="date" name="fecha_doc" :value="recordData.fecha_doc?.split(' ')[0] || '{{ now()->format('Y-m-d') }}'" :disabled="isViewing" required class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                            </div>
                            <div class="md:col-span-2" x-show="!isEditing && !isViewing">
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Archivo (PDF, JPG, PNG)</label>
                                <input type="file" name="archivo" :required="!isEditing && !isViewing" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none file:mr-4 file:rounded-lg file:border-0 file:bg-[#2F3E5C] file:px-3 file:py-1.5 file:text-xs file:font-black file:text-white hover:file:bg-[#5B5F97]">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Observaciones adicionales (Opcional)</label>
                                <textarea name="observaciones" :value="recordData.observaciones || ''" :disabled="isViewing" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]" rows="2" placeholder="Ej. El familiar entregó los originales al administrador..."></textarea>
                            </div>
                            <!-- Botón removido por estar duplicado en el footer del modal -->
                        </form>

                        {{-- Voluntario --}}
                        <form method="POST" action="#" x-show="modal === 'voluntario'" class="grid gap-4 md:grid-cols-2">
                            @csrf
                            <div class="md:col-span-2">
                                <p class="text-xs text-red-500 font-bold mb-2"><i class="ph-bold ph-warning"></i> En desarrollo: Módulo de voluntarios no conectado.</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Seleccionar Voluntario / Padrino</label>
                                <select name="voluntario_id" disabled class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none opacity-50">
                                    <option>Sin voluntarios registrados</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Tipo de Asignación</label>
                                <select name="tipo_asignacion" disabled class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none opacity-50">
                                    <option>Acompañamiento</option>
                                    <option>Apoyo Económico</option>
                                </select>
                            </div>
                            <div class="md:col-span-2 mt-2">
                                <button type="button" disabled class="w-full rounded-xl bg-[#C7B5A3] px-4 py-3 text-sm font-black text-white cursor-not-allowed">Asignar Próximamente</button>
                            </div>
                        </form>
                    </div>

                    <div class="flex flex-col-reverse gap-2 border-t border-[#C7B5A3] p-5 sm:flex-row sm:justify-end">
                        <button type="button"
                                @click="cerrar()"
                                class="rounded-xl bg-[#D5C7B9] px-4 py-2.5 text-xs font-black text-[#2F3E5C] transition hover:bg-[#9A7B60] hover:text-white active:scale-95">
                            Cancelar
                        </button>

                        {{-- Botones Dinámicos de Guardado --}}
                        <button type="submit" form="form-familiar" x-show="modal === 'familiar' && !isViewing" class="rounded-xl bg-[#E27D60] px-4 py-2.5 text-xs font-black text-white transition hover:bg-[#D96F58] active:scale-95" x-text="isEditing ? 'Actualizar Familiar' : 'Vincular Familiar'"></button>
                        <button type="submit" form="form-observacion" x-show="modal === 'observacion' && !isViewing" class="rounded-xl bg-[#E27D60] px-4 py-2.5 text-xs font-black text-white transition hover:bg-[#D96F58] active:scale-95" x-text="isEditing ? 'Actualizar Observación' : 'Guardar Observación'"></button>
                        <button type="submit" form="form-atencion" x-show="modal === 'atencion' && !isViewing" class="rounded-xl bg-[#E27D60] px-4 py-2.5 text-xs font-black text-white transition hover:bg-[#D96F58] active:scale-95" x-text="isEditing ? 'Actualizar Atención' : 'Guardar Atención'"></button>
                        <button type="submit" form="form-evaluacion" x-show="modal === 'evaluacion' && !isViewing" class="rounded-xl bg-[#E27D60] px-4 py-2.5 text-xs font-black text-white transition hover:bg-[#D96F58] active:scale-95">Guardar Evaluación</button>
                        <button type="submit" form="form-actividad" x-show="modal === 'actividad' && !isViewing" class="rounded-xl bg-[#E27D60] px-4 py-2.5 text-xs font-black text-white transition hover:bg-[#D96F58] active:scale-95" x-text="isEditing ? 'Actualizar Actividad' : 'Guardar Actividad'"></button>
                        <button type="submit" form="form-documento" x-show="modal === 'documento' && !isViewing" class="rounded-xl bg-[#E27D60] px-4 py-2.5 text-xs font-black text-white transition hover:bg-[#D96F58] active:scale-95" x-text="isEditing ? 'Actualizar Metadatos' : 'Subir Documento'"></button>
                    </div>
                </div>
            </div>
        </template>
    