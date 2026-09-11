 <template x-if="modalDocumento">
 <div class="fixed inset-0 z-50 flex items-center justify-center bg-fondo-panel p-4 backdrop-blur-sm" x-transition.opacity>
 <div class="relative w-full max-w-md scale-100 rounded-[24px] bg-fondo-panel shadow-2xl overflow-hidden" @click.stop x-transition>
 <div class="h-1.5 w-full bg-boton-principal"></div>
 
 <div class="px-6 py-5 border-b border-borde flex justify-between items-center bg-fondo-card/50">
 <h3 class="text-lg font-extrabold text-titulo" x-text="isEditingDoc ? 'Editar Metadatos del Documento' : 'Subir Nuevo Documento'"></h3>
 <button @click="cerrarModal()" class="text-apoyo hover:text-red-500 transition">
 <i class="ph-bold ph-x text-xl"></i>
 </button>
 </div>

 <div class="p-6 bg-fondo-card">
 <form :action="isEditingDoc ? '{{ url('admin/adultos-mayores/'.$adulto_mayor->cod_am.'/documentos') }}/' + docData.id : '{{ route('admin.adultos-mayores.documentos.store', $adulto_mayor->cod_am) }}'" method="POST" enctype="multipart/form-data" @submit="validarDocumento">
 @csrf
 <template x-if="isEditingDoc">
 <input type="hidden" name="_method" value="PATCH">
 </template>
 
 {{-- ID del adulto para el store --}}
 <input type="date" name="fecha_subida" x-model="docData.fecha_doc" aria-label="Fecha del documento">
 <input type="hidden" name="cod_am" value="{{ $adulto_mayor->cod_am }}">

 <div class="space-y-4">
 <div>
 <label class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1.5">Título / Nombre del Documento *</label>
 <input type="text" name="nombre" x-model="docData.nom_doc" class="w-full rounded-xl border border-borde bg-fondo-card px-4 py-2.5 text-sm font-bold text-titulo focus:border-borde-fuerte focus:ring-0">
 <p x-show="errors.nom_doc" x-text="errors.nom_doc" class="mt-1 text-xs font-bold text-red-500"></p>
 </div>

 <div>
 <label class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1.5">Clasificación *</label>
 <select name="tipo_documento" x-model="docData.tipo_doc" class="w-full rounded-xl border border-borde bg-fondo-card px-4 py-2.5 text-sm font-bold text-titulo focus:border-borde-fuerte focus:ring-0">
 <option value="">Seleccione tipo de documento</option>
 <option value="IDENTIDAD">Documento de Identidad (CI, Pasaporte)</option>
 <option value="MEDICO">Reporte Médico Ext.</option>
 <option value="LABORATORIO">Análisis / Laboratorio</option>
 <option value="LEGAL">Documento Legal / Sentencia</option>
 <option value="RECETA">Receta Médica Ext.</option>
 <option value="OTRO">Otro documento general</option>
 </select>
 <p x-show="errors.tipo_doc" x-text="errors.tipo_doc" class="mt-1 text-xs font-bold text-red-500"></p>
 </div>

 <div x-show="!isEditingDoc">
 <label class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1.5">Archivo a subir *</label>
 <input type="file" name="archivo" accept=".pdf,image/jpeg,image/png,image/webp" class="block w-full text-sm text-apoyo file:mr-4 file:rounded-xl file:border-0 file:bg-fondo-panel file:px-4 file:py-2.5 file:text-xs file:font-black file:text-titulo hover:file:bg-fondo-panel cursor-pointer">
 <p class="mt-1 text-xs font-bold text-apoyo">Formatos permitidos: PDF, JPG, PNG (Max. 5MB)</p>
 <p x-show="errors.archivo" x-text="errors.archivo" class="mt-1 text-xs font-bold text-red-500"></p>
 </div>

 <div>
 <label class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1.5">Observaciones adicionales</label>
 <textarea name="observaciones" x-model="docData.observaciones" rows="2" class="w-full rounded-xl border border-borde bg-fondo-card px-4 py-2.5 text-sm font-bold text-titulo focus:border-borde-fuerte focus:ring-0" placeholder="Opcional..."></textarea>
 </div>
 </div>

 <div class="mt-6 flex justify-end gap-3 border-t border-borde pt-5">
 <button type="button" @click="cerrarModal()" class="rounded-xl px-4 py-2.5 text-xs font-bold text-apoyo hover:bg-fondo-panel transition">Cancelar</button>
 <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-boton-principal px-5 py-2.5 text-xs font-bold text-inverso shadow-sm hover:bg-fondo-panel transition" :disabled="cargando">
 <span x-show="!cargando" x-text="isEditingDoc ? 'Guardar Cambios' : 'Subir Documento'"></span>
 <span x-show="cargando">Procesando...</span>
 </button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </template>
