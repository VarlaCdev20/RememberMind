<ol class="rm-pre-timeline">
 @forelse($eventos as [$titulo, $fecha, $descripcion])
  <li><span aria-hidden="true"></span><div><strong>{{ $titulo }}</strong><time datetime="{{ $fecha->toIso8601String() }}">{{ $fecha->translatedFormat('d M Y · H:i') }}</time><p>{{ $descripcion }}</p></div></li>
 @empty
  <li><div><p>No hay eventos fechados registrados.</p></div></li>
 @endforelse
</ol>
