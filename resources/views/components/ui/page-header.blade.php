@props(['titulo', 'subtitulo' => null, 'breadcrumb' => []])

<header class="rm-page-header">
    <div class="rm-page-title-group">
        @if(count($breadcrumb))
            <nav aria-label="Ruta de navegación" class="rm-breadcrumb">
                <ol>
                    @foreach($breadcrumb as $paso)
                        <li>
                            @if(isset($paso['url']))<a href="{{ $paso['url'] }}">{{ $paso['label'] }}</a>
                            @else<span aria-current="page">{{ $paso['label'] }}</span>@endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif
        <h1 class="rm-page-title">{{ $titulo }}</h1>
        @if($subtitulo)<p class="rm-page-subtitle">{{ $subtitulo }}</p>@endif
    </div>
    @if($slot->isNotEmpty())<div class="rm-page-actions">{{ $slot }}</div>@endif
</header>
