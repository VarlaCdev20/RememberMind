<span class="rm-document-thumb {{ $imagenDocumento ? 'is-image' : '' }}" aria-hidden="true" x-data="{ falloImagen:false }">
    @if($imagenDocumento)
        <img src="{{ route('admin.personal.documento.archivo', $documento) }}" alt="" loading="lazy" decoding="async" x-show="!falloImagen" x-on:error="falloImagen=true">
        <i class="ph-bold ph-image-broken" x-show="falloImagen" x-cloak></i>
    @else
        <span class="rm-document-thumb__paper"><i class="ph-bold {{ $documento->extension === 'pdf' ? 'ph-file-pdf' : 'ph-file-text' }}"></i><small>{{ mb_strtoupper($documento->extension) ?: 'DOC' }}</small></span>
    @endif
</span>
