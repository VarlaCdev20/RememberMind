<section class="rm-wound__gallery" aria-label="Fotografías clínicas">
    <h4 class="rm-section-title">Fotografías clínicas</h4>
    @forelse($galeriaHerida->groupBy('grupo') as $grupo => $fotografiasGrupo)
        <h5>{{ $grupo }} · {{ $fotografiasGrupo->count() }}</h5>
        <div class="rm-wound__thumbs">
        @foreach($fotografiasGrupo as $foto)
            <button type="button" class="rm-wound__thumbnail" @click="viewGallery(@js($galeriaHerida), {{ $galeriaHerida->search(fn ($item) => $item['codigo'] === $foto['codigo']) }}, $event)" aria-label="Ver fotografía clínica del {{ $foto['fecha'] }}"><img src="{{ $foto['url'] }}" alt="Fotografía clínica de herida, {{ $foto['fecha'] }}" loading="lazy" width="80" height="80"><span>{{ $foto['fecha'] }}</span><span>{{ $foto['profesional'] ?: 'Profesional no registrado' }}</span></button>
        @endforeach
        </div>
    @empty <p>Sin fotografías clínicas registradas para esta herida.</p> @endforelse
    @if($galeriaHerida->isNotEmpty())<button type="button" class="rm-btn-secondary" @click="viewGallery(@js($galeriaHerida), 0, $event)">Ver galería completa · {{ $galeriaHerida->count() }}</button>@endif
    @if($galeriaHerida->count() >= 2)<button type="button" class="rm-btn-secondary" @click="compare(@js($galeriaHerida), $event)">Comparar fotografías</button>@endif
</section>
