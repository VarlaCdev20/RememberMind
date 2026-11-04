<section class="rm-clinical-form__section rm-wound__photo-section" aria-labelledby="fotografias-titulo">
    <header class="rm-wound__section-heading"><span class="rm-wound__section-number" aria-hidden="true" x-text="nueva ? '2' : '3'"></span><div><h4 id="fotografias-titulo" x-text="nueva ? 'Fotografías de la herida' : 'Evidencia fotográfica'">Evidencia fotográfica</h4><p x-text="nueva ? 'Añade evidencia visual inicial para documentar la evolución.' : 'Añade imágenes de esta curación para documentar su evolución.'"></p></div><span class="rm-wound__optional">Opcional</span></header>
    <div class="rm-wound__file-control">
        <input id="herida-fotografias" x-ref="woundFileInput" class="rm-wound__file-input" type="file" multiple accept="image/jpeg,image/png,image/webp" @change="selectFiles($event)" :disabled="uploading" :tabindex="nueva ? 0 : -1" aria-describedby="fotografias-restricciones fotografias-privacidad fotografias-error">
        <div class="rm-wound__upload">
            <span class="rm-wound__upload-icon" aria-hidden="true"><i class="ph-bold ph-camera-plus"></i></span>
            <div class="rm-wound__upload-copy"><label for="herida-fotografias">Añadir fotografías clínicas</label><p id="fotografias-restricciones">JPG, PNG o WEBP · máximo {{ config('fotografias_clinicas.max_kb') / 1024 }} MB por imagen<br>Hasta {{ config('fotografias_clinicas.max_archivos') }} imágenes por registro</p></div>
            <button type="button" class="rm-btn-primary" @click="$refs.woundFileInput.click()" :disabled="uploading" aria-controls="herida-fotografias"><i class="ph-bold ph-plus" aria-hidden="true"></i> Seleccionar fotografías</button>
        </div>
    </div>
    <p id="fotografias-privacidad" class="rm-wound__privacy"><i class="ph-bold ph-lock-key" aria-hidden="true"></i> Archivo clínico privado. Solo personal autorizado puede visualizarlo.</p>
    <div x-show="uploading" x-cloak role="status" class="rm-wound__upload-status"><progress max="100" :value="progress" aria-label="Progreso de carga"></progress><p>Subiendo… <span x-text="progress"></span>%</p></div>
    <div x-show="uploadError" x-cloak role="alert" class="rm-wound__upload-error"><p x-text="uploadError"></p><button type="button" class="rm-btn-secondary" @click="sendFiles()">Reintentar</button><button type="button" class="rm-btn-secondary" @click="pendingFiles = []; uploadError = ''">Quitar selección fallida</button></div>
    <div id="fotografias-error" role="alert">@error('fotografias')<p class="rm-error">{{ $message }}</p>@enderror @error('fotografias.*')<p class="rm-error">{{ $message }}</p>@enderror @error('loteFotografias')<p class="rm-error">{{ $message }}</p>@enderror</div>
    <div class="rm-wound__previews" x-show="previews.length" x-cloak>
        <template x-for="photo in previews" :key="photo.url">
            <article class="rm-wound__preview">
                <div class="rm-wound__preview-image"><img :src="photo.url" alt="Fotografía seleccionada, todavía sin registrar" width="240" height="180"><button type="button" class="rm-wound__remove" :aria-label="'Quitar fotografía ' + (photo.index + 1)" @click="removePhoto(photo)" :disabled="uploading"><i class="ph-bold ph-x" aria-hidden="true"></i></button><div class="rm-wound__preview-caption"><strong x-text="'Foto ' + (photo.index + 1)"></strong><span x-text="photo.size"></span></div></div>
                <span class="rm-wound__pending">Pendiente de registro</span><span class="rm-wound__file-name" x-text="photo.name"></span>
                <label :for="'foto-descripcion-' + photo.index">Descripción opcional</label><textarea :id="'foto-descripcion-' + photo.index" maxlength="{{ config('fotografias_clinicas.descripcion_max') }}" rows="2" class="rm-textarea" :value="$wire.descripcionesFotos[photo.index] ?? ''" @input="$wire.$set('descripcionesFotos.' + photo.index, $event.target.value, false); clinicalFormDirty = true"></textarea>
                <div class="rm-wound__replacement"><input :id="'foto-reemplazar-' + photo.index" class="rm-wound__file-input" type="file" accept="image/jpeg,image/png,image/webp" @change="replacePhoto(photo, $event)" :disabled="uploading"><label class="rm-btn-secondary" :for="'foto-reemplazar-' + photo.index">Reemplazar fotografía</label></div>
            </article>
        </template>
        <button x-show="previews.length < {{ config('fotografias_clinicas.max_archivos') }}" type="button" class="rm-wound__add-photo" @click="$refs.woundFileInput.click()" :disabled="uploading"><i class="ph-bold ph-plus" aria-hidden="true"></i><span>Añadir fotografía</span></button>
    </div>
</section>
