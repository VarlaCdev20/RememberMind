import sys

with open('resources/views/livewire/admin/personal-institucional/partials/personal-institucional-form.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Merge 5 and 6
start_5 = content.find('@elseif($pasoActual == 5)')
start_6 = content.find('@elseif($pasoActual == 6)')
start_7 = content.find('@elseif($pasoActual == 7)')
start_8 = content.find('@elseif($pasoActual == 8)')

if start_5 == -1 or start_6 == -1 or start_7 == -1 or start_8 == -1:
    print("Could not find step markers")
    sys.exit(1)

# Step 5 text
step_5_6 = """            @elseif($pasoActual == 5)
                <!-- Paso 5: Clasificación Institucional -->
                <div class="space-y-6">
                    <h4 class="text-md font-bold text-titulo border-b border-borde pb-2"><i class="ph-bold ph-briefcase"></i> 5. Clasificación Institucional</h4>
                    <p class="text-sm text-apoyo mb-4">El área institucional y el perfil operativo se derivan del rol seleccionado.</p>

                    @php($clasificacion = $clasificacion_derivada)

                    @if($clasificacion)
                        <div class="p-4 {{ $clasificacion['tipo_personal'] === 'salud' ? 'bg-estado-infoBg border-estado-infoBorde' : 'bg-estado-advertenciaBg border-estado-advertenciaBorde' }} border rounded-xl space-y-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <h5 class="text-xs font-bold uppercase tracking-wider {{ $clasificacion['tipo_personal'] === 'salud' ? 'text-estado-info' : 'text-estado-advertencia' }}">Clasificación automática</h5>
                                    <p class="text-sm font-bold text-titulo">{{ $clasificacion['rol_sistema'] }}</p>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-black text-titulo border border-borde">
                                    <i class="ph-bold ph-check-circle"></i> Derivado
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="rounded-xl border border-borde bg-white p-3">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-apoyo">Tipo</span>
                                    <span class="text-sm font-black text-titulo">{{ $clasificacion['tipo_label'] }}</span>
                                </div>
                                <div class="rounded-xl border border-borde bg-white p-3">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-apoyo">Rol operativo</span>
                                    <span class="text-sm font-black text-titulo">{{ $clasificacion['rol_label'] }}</span>
                                </div>
                                <div class="rounded-xl border border-borde bg-white p-3">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-apoyo">Área</span>
                                    <span class="text-sm font-black text-titulo">{{ $clasificacion['area_nombre'] }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-estado-peligroBg border border-estado-peligro/30 rounded-xl text-estado-peligro text-sm font-bold">
                            <i class="ph-bold ph-warning-circle"></i> Seleccione un rol institucional válido en el paso anterior.
                        </div>
                    @endif
                    @error('roles_seleccionados') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                    
                    <!-- Datos Laborales -->
                    <div class="mt-8 pt-6 border-t border-borde">
                        <h5 class="text-md font-bold text-titulo mb-4">Información Laboral / Operativa</h5>
                        
                        @if($tipo_personal === 'salud')
                            <div class="space-y-4">
                                <div class="p-4 bg-boton-acento/5 border border-boton-acento/20 rounded-2xl flex items-center gap-3 mb-2">
                                    <i class="ph-bold ph-shield-check text-boton-acento text-xl"></i>
                                    <div>
                                        <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Perfil Clínico / Salud</h5>
                                        <p class="text-xs text-apoyo">Complete los datos de la especialidad, experiencia y matrícula profesional.</p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Especialidad Médica / de Salud</label>
                                        <select wire:model="cod_esp" class="w-full rounded-xl {{ $errors->has('cod_esp') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm">
                                            <option value="">Seleccione Especialidad...</option>
                                            @foreach($especialidades_list as $esp)
                                                <option value="{{ $esp->cod_esp }}">{{ $esp->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('cod_esp') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Años de Experiencia <span class="text-estado-peligro">*</span></label>
                                        <input type="number" wire:model.blur="anios_exp" class="w-full rounded-xl {{ $errors->has('anios_exp') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm" placeholder="Ej: 5">
                                        @error('anios_exp') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Matrícula Profesional / Respaldo</label>
                                        <input type="text" wire:model.blur="matricula_prof" class="w-full rounded-xl {{ $errors->has('matricula_prof') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm uppercase" placeholder="Ej: MP-98765-LP">
                                        @error('matricula_prof') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Universidad / Institución de Formación</label>
                                        <input type="text" wire:model.blur="institucion_formacion" class="w-full rounded-xl {{ $errors->has('institucion_formacion') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm uppercase" placeholder="Ej: UMSA">
                                        @error('institucion_formacion') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    @if(($clasificacion_derivada['rol_operativo'] ?? '') === 'ENFERMERO')
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Subtipo de Enfermería <span class="text-estado-peligro">*</span></label>
                                            <select wire:model="subtipo_enfermeria" class="w-full rounded-xl {{ $errors->has('subtipo_enfermeria') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm">
                                                <option value="">Seleccione Subtipo...</option>
                                                <option value="GENERAL_ADMISION">Enfermero General de Admisión</option>
                                                <option value="ESPECIALIZADO_TURNO">Enfermero Especializado de Turno</option>
                                            </select>
                                            @error('subtipo_enfermeria') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif($tipo_personal === 'admin')
                            <div class="space-y-4">
                                <div class="p-4 bg-yellow-500/5 border border-yellow-500/20 rounded-2xl flex items-center gap-3 mb-2">
                                    <i class="ph-bold ph-shield-star text-yellow-600 text-xl"></i>
                                    <div>
                                        <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Perfil Administrativo</h5>
                                        <p class="text-xs text-apoyo">Complete los detalles de su cargo y funciones administrativas.</p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Cargo Administrativo Asignado</label>
                                        <select wire:model="cod_cargo_admin" class="w-full rounded-xl {{ $errors->has('cod_cargo_admin') ? 'border-estado-peligro focus:ring-estado-peligro focus:border-estado-peligro' : 'border-input-borde focus:ring-input-ringFocus focus:border-input-bordeFocus' }} bg-input-bg text-sm">
                                            <option value="">Seleccione Cargo...</option>
                                            @foreach($cargos_list as $crg)
                                                <option value="{{ $crg->cod_cargo_admin }}">{{ $crg->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('cod_cargo_admin') <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

"""

step_6_7 = """            @elseif($pasoActual == 6 || $pasoActual == 7)
                <!-- Paso 6 y 7: Documentación -->
                <div class="space-y-6">
                    <div class="flex justify-between items-center border-b border-borde pb-2">
                        <h4 class="text-md font-black text-titulo flex items-center gap-2">
                            <i class="ph-bold ph-folder-open text-boton-acento"></i> 
                            {{ $pasoActual == 6 ? '6. Documentación del Ingresante' : '7. Documentación Institucional' }}
                        </h4>
                        <div class="flex items-center gap-2 text-xs text-apoyo">
                            <span class="px-2.5 py-1 rounded-full bg-estado-advertenciaBg text-estado-advertencia font-bold border border-estado-advertenciaBorde">
                                Plazo de regularización: 48 horas
                            </span>
                        </div>
                    </div>

                    <div class="space-y-6">
                        @php
                            $docsAgrupados = collect($this->documentos_configurados)->groupBy('tipo');
                        @endphp

                        @if($pasoActual == 6)
                            <!-- BLOQUE 1: Documentación del Ingresante -->
                            <div class="space-y-4 bg-fondo/35 p-5 rounded-[1.5rem] border border-borde/60">
                                <div class="flex items-center gap-2 pb-2 border-b border-borde">
                                    <i class="ph-bold ph-identification-card text-lg text-boton-acento"></i>
                                    <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Archivos Personales / Respaldo</h5>
                                </div>
                                
                                <div class="space-y-3">
                                    @if(isset($docsAgrupados['ingresante']))
                                        @foreach($docsAgrupados['ingresante'] as $doc)
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border rounded-xl bg-white shadow-sm gap-4 transition-all hover:border-boton-acento/40 {{ ($doc['obligatorio'] && !isset($archivos_temporales[$doc['id']]) && !isset($estado_documentos[$doc['id']])) ? 'border-estado-peligro/30 bg-estado-peligroBg/10' : 'border-borde' }}">
                                                <div class="flex items-start gap-3 flex-1">
                                                    <div class="w-10 h-10 rounded-full {{ isset($archivos_temporales[$doc['id']]) || (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'VALIDADO') ? 'bg-estado-exitoBg text-estado-exito' : (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'OBSERVADO' ? 'bg-estado-peligroBg text-estado-peligro' : 'bg-fondo text-apoyo') }} flex items-center justify-center shrink-0 border border-borde/40">
                                                        <i class="ph-fill {{ isset($archivos_temporales[$doc['id']]) || (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'VALIDADO') ? 'ph-check-circle' : (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'OBSERVADO' ? 'ph-warning-circle' : 'ph-file-text') }} text-xl"></i>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <h6 class="text-sm font-bold text-titulo flex items-center flex-wrap gap-2">
                                                            {{ $doc['nombre'] }}
                                                            @if($doc['obligatorio'])
                                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-estado-peligroBg text-estado-peligro border border-estado-peligro/20 uppercase tracking-wider">Obligatorio</span>
                                                            @else
                                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertenciaBorde uppercase tracking-wider">Pendiente (48h)</span>
                                                            @endif
                                                        </h6>
                                                        <p class="text-[10px] text-apoyo">{{ $doc['desc'] }}</p>
                                                        
                                                        @if(isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'OBSERVADO')
                                                            <div class="mt-2 p-2 bg-estado-peligroBg rounded text-xs text-estado-peligro border border-estado-peligro/20">
                                                                <span class="font-bold">Observación:</span> {{ $observacion_documentos[$doc['id']] }}
                                                            </div>
                                                        @endif
                                                        
                                                        @error("archivos_temporales." . $doc['id'])
                                                            <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-2 shrink-0">
                                                    @if(isset($archivos_temporales[$doc['id']]))
                                                        <button type="button" wire:click="removerDocumento('{{ $doc['id'] }}')" class="px-3 py-1.5 text-xs font-bold text-estado-peligro border border-estado-peligro rounded-lg hover:bg-estado-peligroBg transition-colors" title="Eliminar archivo">
                                                            <i class="ph-bold ph-trash"></i> Eliminar
                                                        </button>
                                                    @else
                                                        <div x-data="{ observar: false }" class="flex items-center gap-2 relative">
                                                            @if(!isset($estado_documentos[$doc['id']]) || $estado_documentos[$doc['id']] !== 'OBSERVADO')
                                                                <label class="cursor-pointer px-4 py-1.5 text-xs font-bold text-boton-acento border border-boton-acento rounded-lg hover:bg-boton-acento/10 transition-colors">
                                                                    <i class="ph-bold ph-upload-simple"></i> Subir Archivo
                                                                    <input type="file" wire:model="archivos_temporales.{{ $doc['id'] }}" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                                                </label>
                                                                <button type="button" @click="observar = !observar" class="px-3 py-1.5 text-xs font-bold text-apoyo border border-borde rounded-lg hover:bg-fondo-hover transition-colors" title="Marcar como observado si el documento físico es incorrecto">
                                                                    <i class="ph-bold ph-eye"></i> Observar
                                                                </button>
                                                            @else
                                                                <button type="button" wire:click="removerDocumento('{{ $doc['id'] }}')" class="px-3 py-1.5 text-xs font-bold text-apoyo border border-borde rounded-lg hover:bg-fondo-hover transition-colors">
                                                                    Deshacer Observación
                                                                </button>
                                                            @endif
                                                            
                                                            <div x-show="observar" x-cloak class="absolute z-10 bg-white p-3 border border-borde rounded-xl shadow-lg mt-10 right-0 w-64" @click.away="observar = false">
                                                                <label class="block text-[10px] font-bold text-apoyo uppercase tracking-wider mb-1">Motivo de la Observación</label>
                                                                <textarea id="obs_{{ $doc['id'] }}" rows="2" class="w-full rounded-lg border-input-borde bg-input-bg text-xs focus:ring-input-ringFocus focus:border-input-bordeFocus mb-2"></textarea>
                                                                <div class="flex justify-end gap-2">
                                                                    <button type="button" @click="observar = false" class="text-xs text-apoyo font-bold px-2 py-1">Cancelar</button>
                                                                    <button type="button" @click="$wire.observarDocumento('{{ $doc['id'] }}', document.getElementById('obs_{{ $doc['id'] }}').value); observar = false" class="text-xs text-white bg-estado-peligro font-bold px-3 py-1 rounded-lg">Guardar Obs.</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($pasoActual == 7)
                            <!-- BLOQUE 2: Documentación Institucional -->
                            <div class="space-y-4 bg-fondo/35 p-5 rounded-[1.5rem] border border-borde/60">
                                <div class="flex items-center gap-2 pb-2 border-b border-borde">
                                    <i class="ph-bold ph-briefcase text-lg text-boton-acento"></i>
                                    <h5 class="text-xs font-bold text-titulo uppercase tracking-wider">Documentación Institucional</h5>
                                </div>
                                
                                <div class="space-y-3">
                                    @if(isset($docsAgrupados['institucional']))
                                        @foreach($docsAgrupados['institucional'] as $doc)
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border rounded-xl bg-white shadow-sm gap-4 transition-all hover:border-boton-acento/40 {{ ($doc['obligatorio'] && !isset($archivos_temporales[$doc['id']]) && !isset($estado_documentos[$doc['id']])) ? 'border-estado-peligro/30 bg-estado-peligroBg/10' : 'border-borde' }}">
                                                <div class="flex items-start gap-3 flex-1">
                                                    <div class="w-10 h-10 rounded-full {{ isset($archivos_temporales[$doc['id']]) || (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'VALIDADO') ? 'bg-estado-exitoBg text-estado-exito' : (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'OBSERVADO' ? 'bg-estado-peligroBg text-estado-peligro' : 'bg-fondo text-apoyo') }} flex items-center justify-center shrink-0 border border-borde/40">
                                                        <i class="ph-fill {{ isset($archivos_temporales[$doc['id']]) || (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'VALIDADO') ? 'ph-check-circle' : (isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'OBSERVADO' ? 'ph-warning-circle' : 'ph-file-text') }} text-xl"></i>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <h6 class="text-sm font-bold text-titulo flex items-center flex-wrap gap-2">
                                                            {{ $doc['nombre'] }}
                                                            @if($doc['obligatorio'])
                                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-estado-peligroBg text-estado-peligro border border-estado-peligro/20 uppercase tracking-wider">Obligatorio</span>
                                                            @else
                                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertenciaBorde uppercase tracking-wider">Pendiente (48h)</span>
                                                            @endif
                                                        </h6>
                                                        <p class="text-[10px] text-apoyo">{{ $doc['desc'] }}</p>
                                                        
                                                        @if(isset($estado_documentos[$doc['id']]) && $estado_documentos[$doc['id']] === 'OBSERVADO')
                                                            <div class="mt-2 p-2 bg-estado-peligroBg rounded text-xs text-estado-peligro border border-estado-peligro/20">
                                                                <span class="font-bold">Observación:</span> {{ $observacion_documentos[$doc['id']] }}
                                                            </div>
                                                        @endif
                                                        
                                                        @error("archivos_temporales." . $doc['id'])
                                                            <span class="text-xs text-estado-peligro block mt-1">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-2 shrink-0">
                                                    @if(isset($archivos_temporales[$doc['id']]))
                                                        <button type="button" wire:click="removerDocumento('{{ $doc['id'] }}')" class="px-3 py-1.5 text-xs font-bold text-estado-peligro border border-estado-peligro rounded-lg hover:bg-estado-peligroBg transition-colors" title="Eliminar archivo">
                                                            <i class="ph-bold ph-trash"></i> Eliminar
                                                        </button>
                                                    @else
                                                        <div x-data="{ observar: false }" class="flex items-center gap-2 relative">
                                                            @if(!isset($estado_documentos[$doc['id']]) || $estado_documentos[$doc['id']] !== 'OBSERVADO')
                                                                <label class="cursor-pointer px-4 py-1.5 text-xs font-bold text-boton-acento border border-boton-acento rounded-lg hover:bg-boton-acento/10 transition-colors">
                                                                    <i class="ph-bold ph-upload-simple"></i> Subir Archivo
                                                                    <input type="file" wire:model="archivos_temporales.{{ $doc['id'] }}" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                                                </label>
                                                                <button type="button" @click="observar = !observar" class="px-3 py-1.5 text-xs font-bold text-apoyo border border-borde rounded-lg hover:bg-fondo-hover transition-colors" title="Marcar como observado si el documento físico es incorrecto">
                                                                    <i class="ph-bold ph-eye"></i> Observar
                                                                </button>
                                                            @else
                                                                <button type="button" wire:click="removerDocumento('{{ $doc['id'] }}')" class="px-3 py-1.5 text-xs font-bold text-apoyo border border-borde rounded-lg hover:bg-fondo-hover transition-colors">
                                                                    Deshacer Observación
                                                                </button>
                                                            @endif
                                                            
                                                            <div x-show="observar" x-cloak class="absolute z-10 bg-white p-3 border border-borde rounded-xl shadow-lg mt-10 right-0 w-64" @click.away="observar = false">
                                                                <label class="block text-[10px] font-bold text-apoyo uppercase tracking-wider mb-1">Motivo de la Observación</label>
                                                                <textarea id="obs_{{ $doc['id'] }}" rows="2" class="w-full rounded-lg border-input-borde bg-input-bg text-xs focus:ring-input-ringFocus focus:border-input-bordeFocus mb-2"></textarea>
                                                                <div class="flex justify-end gap-2">
                                                                    <button type="button" @click="observar = false" class="text-xs text-apoyo font-bold px-2 py-1">Cancelar</button>
                                                                    <button type="button" @click="$wire.observarDocumento('{{ $doc['id'] }}', document.getElementById('obs_{{ $doc['id'] }}').value); observar = false" class="text-xs text-white bg-estado-peligro font-bold px-3 py-1 rounded-lg">Guardar Obs.</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif

                        @error('documentos_generales')
                            <div class="p-3 bg-estado-peligroBg text-estado-peligro border border-estado-peligro/30 rounded-xl text-sm font-bold mt-4 flex items-center gap-2">
                                <i class="ph-bold ph-warning"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

"""

new_content = content[:start_5] + step_5_6 + step_6_7 + content[start_8:]

with open('resources/views/livewire/admin/personal-institucional/partials/personal-institucional-form.blade.php', 'w', encoding='utf-8') as f:
    f.write(new_content)

print("Done")
