<div class="rm-staff-documents__actions rm-document-actions">
    <button type="button" class="rm-btn-secondary" wire:click="verArchivo(@js($documento->getKey()))" wire:loading.attr="disabled" wire:target="verArchivo" data-documento-trigger="{{ $documento->getKey() }}" aria-haspopup="dialog"><i class="ph-bold ph-eye" aria-hidden="true"></i>Ver archivo<span class="sr-only"> {{ $documento->nombre }} de {{ $titular->nombres }}</span></button>
    <a class="rm-staff-documents__open" href="{{ route('admin.personal.documento.archivo', $documento) }}" target="_blank" rel="noopener noreferrer" aria-label="Abrir original de {{ $documento->nombre }} en otra pestaña"><i class="ph-bold ph-arrow-square-out" aria-hidden="true"></i><span>Abrir original</span></a>
</div>
