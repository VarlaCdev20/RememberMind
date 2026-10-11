@props(['type'])
<svg viewBox="0 0 76 42" aria-hidden="true" focusable="false" class="rm-eliminacion__bristol-svg" fill="currentColor">
    @if($type === 1)
        <ellipse cx="17" cy="16" rx="7" ry="6"/><ellipse cx="35" cy="23" rx="7" ry="6"/><ellipse cx="54" cy="16" rx="7" ry="6"/>
    @elseif($type === 2)
        <path d="M10 25Q8 12 19 13Q26 5 35 12Q46 7 51 16Q67 12 66 25Q57 37 47 31Q36 38 29 30Q15 35 10 25Z"/>
        <path d="M25 15L29 26M43 14L47 29" stroke="var(--rm-surface-main, #F7F3EF)" stroke-width="2" fill="none"/>
    @elseif($type === 3)
        <rect x="8" y="11" width="60" height="23" rx="12"/><path d="M23 12L28 20L24 28M43 12L39 20L45 31" stroke="var(--rm-surface-main, #F7F3EF)" stroke-width="2" fill="none"/>
    @elseif($type === 4)
        <path d="M12 28C2 17 17 7 28 12S48 28 60 17C71 7 77 23 64 31S40 34 29 25S19 36 12 28Z"/>
    @elseif($type === 5)
        <rect x="7" y="10" width="20" height="14" rx="7"/><rect x="31" y="20" width="18" height="13" rx="6"/><rect x="52" y="9" width="18" height="15" rx="7"/>
    @elseif($type === 6)
        <path d="M10 28L15 16L25 19L32 9L42 17L52 13L66 24L60 32L44 30L34 35L24 29Z"/>
    @else
        <path d="M9 22Q18 12 28 20T49 19T68 22L68 30Q58 35 49 29T29 30T9 30Z"/><path d="M17 9Q22 15 17 16Q12 15 17 9M57 6Q63 14 57 15Q52 14 57 6"/>
    @endif
</svg>
