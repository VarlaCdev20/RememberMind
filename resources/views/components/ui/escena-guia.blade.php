@props(['paso' => 0])
{{-- Ilustración original de orientación. No representa tareas ni materiales de una prueba. --}}
<svg viewBox="0 0 240 180" width="240" height="180" class="rm-guia-escena" aria-hidden="true" focusable="false">
    <rect x="12" y="12" width="216" height="154" rx="28" class="rm-guia-escena__fondo"/>
    <path d="M29 152H211" class="rm-guia-escena__trazo"/>
    @if($paso === 0)
        <rect x="91" y="29" width="58" height="38" rx="10" class="rm-guia-escena__papel"/>
        <path d="M104 42H135 M104 52H126" class="rm-guia-escena__trazo"/>
        <circle cx="58" cy="72" r="16" class="rm-guia-escena__acento"/>
        <path d="M38 76Q34 40 58 43Q79 44 79 76 M36 145V111Q36 91 58 91Q80 91 80 111V145" class="rm-guia-escena__persona"/>
        <circle cx="182" cy="72" r="16" class="rm-guia-escena__acento"/>
        <path d="M166 63Q182 42 198 63 M160 145V111Q160 91 182 91Q204 91 204 111V145" class="rm-guia-escena__persona"/>
        <path d="M78 109L104 121 M162 109L137 121" class="rm-guia-escena__trazo"/>
        <rect x="88" y="118" width="64" height="8" rx="4" class="rm-guia-escena__papel"/>
        <path d="M99 126V150 M141 126V150" class="rm-guia-escena__trazo"/>
    @elseif($paso === 1)
        <path d="M44 53Q84 35 119 53Q153 35 196 53V132Q156 117 119 134Q83 117 44 132Z" class="rm-guia-escena__papel"/>
        <path d="M119 54V133 M60 67L98 65 M60 80L98 78 M60 93L88 91 M139 66L179 68 M139 80L179 82 M139 94L166 96" class="rm-guia-escena__trazo"/>
        <path d="M154 47V21L164 26L174 21V48" class="rm-guia-escena__acento"/>
        <circle cx="186" cy="130" r="19" class="rm-guia-escena__persona"/>
        <path d="M176 130L183 137L196 123" class="rm-guia-escena__trazo"/>
    @elseif($paso === 2)
        <rect x="58" y="28" width="118" height="122" rx="12" class="rm-guia-escena__papel"/>
        <rect x="92" y="21" width="50" height="16" rx="6" class="rm-guia-escena__persona"/>
        <path d="M77 56H155 M77 82H142 M77 108H128" class="rm-guia-escena__trazo"/>
        <rect x="77" y="63" width="78" height="9" rx="4" class="rm-guia-escena__fondo"/>
        <rect x="77" y="89" width="65" height="9" rx="4" class="rm-guia-escena__fondo"/>
        <path d="M151 139L184 86L194 92L162 145L148 153Z" class="rm-guia-escena__acento"/>
        <path d="M184 86L194 92 M151 139L162 145" class="rm-guia-escena__trazo"/>
    @else
        <rect x="40" y="27" width="133" height="118" rx="14" class="rm-guia-escena__papel"/>
        <path d="M40 53H173 M56 40H60 M68 40H72" class="rm-guia-escena__trazo"/>
        <rect x="57" y="68" width="17" height="17" rx="4" class="rm-guia-escena__persona"/>
        <rect x="57" y="99" width="17" height="17" rx="4" class="rm-guia-escena__persona"/>
        <path d="M86 75H151 M86 106H133 M60 76L64 80L71 71 M60 107L64 111L71 102" class="rm-guia-escena__trazo"/>
        <circle cx="177" cy="130" r="28" class="rm-guia-escena__acento"/>
        <path d="M163 130L173 140L192 120" class="rm-guia-escena__trazo"/>
    @endif
</svg>
