@props(['id'])

<details {{ $attributes->class(['rm-captura__guia']) }} id="{{ $id }}">
    <summary><i class="ph-bold ph-notebook" aria-hidden="true"></i> Guía para documentar el contexto</summary>
    <p class="rm-captura__ayuda">Incluye solo información disponible y pertinente para tu valoración. Distingue el relato del residente, el del informante y lo observado directamente.</p>
    <dl class="rm-captura__guia-grid">
        <div><dt>Cambio y evolución</dt><dd>Qué cambió respecto al nivel previo, desde cuándo y quién aporta la información.</dd></div>
        <div><dt>Vida cotidiana</dt><dd>Dificultades, apoyos y oportunidades reales para realizar las actividades.</dd></div>
        <div><dt>Ánimo y conducta</dt><dd>Cambios relevantes descritos u observados, con sus circunstancias.</dd></div>
        <div><dt>Condiciones de evaluación</dt><dd>Idioma, escolaridad y dificultades sensoriales o motoras conocidas que puedan influir.</dd></div>
    </dl>
    <p class="rm-captura__ayuda">Si no se conoce un dato, indícalo. Esta guía organiza el relato; no es una prueba ni genera una puntuación.</p>
    <p class="rm-captura__fuentes">Fundamento: <a href="https://alz-journals.onlinelibrary.wiley.com/doi/10.1002/alz.14333" target="_blank" rel="noopener noreferrer">Alzheimer’s Association, DETeCD-ADRD</a> y <a href="https://www.nice.org.uk/guidance/ng97/chapter/Recommendations" target="_blank" rel="noopener noreferrer">NICE NG97</a>.</p>
</details>
