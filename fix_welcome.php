<?php
$file = __DIR__ . '/resources/views/welcome.blade.php';
$content = file_get_contents($file);

// 1. Add welcome-page to body
$content = preg_replace(
    '/<body class="([^"]+)"/',
    '<body class="$1 welcome-page"',
    $content
);

// 3. Fix background
// Remove bg-app-institucional, add bg-fondo-layout rm-texture-dots-soft
$content = str_replace('bg-app-institucional', 'bg-fondo-layout rm-texture-dots-soft', $content);

// 4. Fix texts
$content = preg_replace('/\btext-white\b/', 'text-[var(--welcome-titulo)]', $content);
$content = preg_replace('/\btext-black\b/', 'text-[var(--welcome-titulo)]', $content);
$content = preg_replace('/\btext-gray-900\b/', 'text-[var(--welcome-titulo)]', $content);
$content = preg_replace('/\btext-azul-profundo\b/', 'text-[var(--welcome-titulo)]', $content);

$content = preg_replace('/\btext-gray-600\b/', 'text-[var(--welcome-texto)]', $content);
$content = preg_replace('/\btext-gray-700\b/', 'text-[var(--welcome-texto)]', $content);
$content = preg_replace('/\btext-white\/80\b/', 'text-[var(--welcome-texto)]', $content);
$content = preg_replace('/\btext-black\/70\b/', 'text-[var(--welcome-texto)]', $content);

$content = preg_replace('/\btext-gray-400\b/', 'text-[var(--welcome-muted)]', $content);
$content = preg_replace('/\btext-gray-500\b/', 'text-[var(--welcome-muted)]', $content);
$content = preg_replace('/\btext-white\/60\b/', 'text-[var(--welcome-muted)]', $content);

$content = preg_replace('/\btext-texto-principal\b/', 'text-[var(--welcome-texto)]', $content);
$content = preg_replace('/\btext-titulo\b/', 'text-[var(--welcome-titulo)]', $content);
$content = preg_replace('/\btext-parrafo\b/', 'text-[var(--welcome-texto)]', $content);
$content = preg_replace('/\btext-texto-secundario\b/', 'text-[var(--welcome-muted)]', $content);
$content = preg_replace('/\btext-apoyo\b/', 'text-[var(--welcome-muted)]', $content);

// 6. Evolucion Cognitiva Promedio Card
// Change group bg-fondo-card/10 backdrop-blur-md... to welcome styles
// Currently: group bg-fondo-card/10 backdrop-blur-md rounded-[2.5rem] md:rounded-[3rem] p-6 md:p-8 border border-borde shadow-[0_30px_60px_rgba(0,0,0,0.34)] transition-all duration-500 hover:bg-fondo-card/15 hover:shadow-[0_40px_80px_rgba(0,0,0,0.48)] hover:border-borde w-full overflow-hidden
$content = str_replace(
    'group bg-fondo-card/10 backdrop-blur-md rounded-[2.5rem]',
    'group rounded-[2.5rem]',
    $content
);
$content = str_replace(
    'md:rounded-[3rem] p-6 md:p-8 border border-borde shadow-[0_30px_60px_rgba(0,0,0,0.34)] transition-all duration-500 hover:bg-fondo-card/15 hover:shadow-[0_40px_80px_rgba(0,0,0,0.48)] hover:border-borde w-full overflow-hidden',
    'md:rounded-[3rem] p-6 md:p-8 shadow-lg transition-all duration-500 hover:-translate-y-1 w-full overflow-hidden',
    $content
);
// Inyectar el style
$content = preg_replace(
    '/(<div class="group rounded-\[2.5rem\] md:rounded-\[3rem\] p-6 md:p-8 shadow-lg transition-all duration-500 hover:-translate-y-1 w-full overflow-hidden"[^>]*>)/',
    '$1'."\n".'                <style>.welcome-evolucion { background: var(--welcome-card); border: 1px solid var(--welcome-card-border); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); } .welcome-evolucion-inner { background: var(--welcome-card-strong); border: 1px solid var(--welcome-card-border); }</style>',
    $content
);
$content = str_replace('group rounded-[2.5rem]', 'welcome-evolucion group rounded-[2.5rem]', $content);
$content = str_replace('bg-black/20 rounded-3xl p-3 shadow-inner border border-white/5', 'welcome-evolucion-inner rounded-3xl p-3 shadow-inner', $content);

// 7. Buttons
// Acceder al Portal: btn-acento-institucional bg-[var(--welcome-acento)] hover:bg-[var(--welcome-acento-hover)] text-[var(--welcome-boton-texto)]
// Ya tiene btn-acento-institucional, cambiemos las bg en welcome variables. Wait, the TAREA says:
// Botón “Acceder al Portal”: bg-[var(--welcome-acento)] hover:bg-[var(--welcome-acento-hover)] text-[var(--welcome-boton-texto)]
$content = str_replace('btn-acento-institucional', 'bg-[var(--welcome-acento)] hover:bg-[var(--welcome-acento-hover)] text-[var(--welcome-boton-texto)]', $content);
$content = str_replace('btn-primario-institucional', 'bg-[var(--welcome-primario)] hover:bg-[var(--welcome-primario-hover)] text-[var(--welcome-boton-texto)]', $content);
$content = str_replace('btn-secundario-institucional', 'bg-[var(--welcome-card)] hover:bg-[var(--welcome-card-strong)] text-[var(--welcome-texto)] border border-[var(--welcome-card-border)]', $content);

// Theme Toggle Button
// bg-fondo-panel -> bg-[var(--welcome-card)] text-[var(--welcome-texto)]
// hover:bg-fondo-panel -> hover:bg-[var(--welcome-card-strong)]
$content = str_replace(
    'bg-fondo-panel px-3 py-2 text-xs font-black text-[var(--welcome-texto)] border border-borde shadow-sm transition-all hover:bg-fondo-panel hover:text-inverso hover:border-borde',
    'bg-[var(--welcome-card)] px-3 py-2 text-xs font-black text-[var(--welcome-texto)] border border-[var(--welcome-card-border)] shadow-sm transition-all hover:bg-[var(--welcome-card-strong)] hover:text-[var(--welcome-titulo)]',
    $content
);
$content = str_replace(
    'bg-fondo-panel text-[var(--welcome-texto)] border border-borde shadow-sm transition-all hover:bg-fondo-panel hover:text-inverso',
    'bg-[var(--welcome-card)] text-[var(--welcome-texto)] border border-[var(--welcome-card-border)] shadow-sm transition-all hover:bg-[var(--welcome-card-strong)] hover:text-[var(--welcome-titulo)]',
    $content
);
$content = str_replace(
    'bg-fondo-panel border border-borde px-5 py-3 text-base font-black text-[var(--welcome-texto)] transition-all hover:bg-fondo-panel hover:text-inverso hover:border-borde',
    'bg-[var(--welcome-card)] border border-[var(--welcome-card-border)] px-5 py-3 text-base font-black text-[var(--welcome-texto)] transition-all hover:bg-[var(--welcome-card-strong)] hover:text-[var(--welcome-titulo)]',
    $content
);

// Navbar styling
$content = str_replace('bg-fondo-card backdrop-blur-xl shadow-[0_14px_35px_rgba(47,36,31,0.16)] border border-borde', 'bg-[var(--welcome-card)] backdrop-blur-xl border border-[var(--welcome-card-border)] shadow-sm', $content);
$content = str_replace('bg-fondo-card backdrop-blur-md shadow-[0_8px_24px_rgba(47,36,31,0.08)] border border-borde', 'bg-[var(--welcome-card-strong)] backdrop-blur-md border border-[var(--welcome-card-border)] shadow-sm', $content);

// Nav links hover:text-parrafo -> hover:text-[var(--welcome-acento)]
$content = str_replace('hover:text-[var(--welcome-texto)] transition-colors hover-underline group', 'hover:text-[var(--welcome-acento)] transition-colors hover-underline group', $content);

// Indicators
// 98%, +500
// text-inverso -> text-[var(--welcome-titulo)]
$content = str_replace('text-inverso', 'text-[var(--welcome-titulo)]', $content);
$content = str_replace('text-[var(--welcome-titulo)]/80', 'text-[var(--welcome-muted)]', $content);
$content = str_replace('border-borde', 'border-[var(--welcome-card-border)]', $content);

// Arrays HEX colors
$content = str_replace("'#F28B54'", "'var(--color-boton-acento)'", $content);
$content = str_replace("'#006B5E'", "'var(--color-boton-principal)'", $content);
$content = str_replace("'#2EA9C0'", "'var(--color-modulo-salud)'", $content);
$content = str_replace("'#0B4F46'", "'var(--color-modulo-cognitivo)'", $content);

// Also CSS style block HEX colors
$content = str_replace('background-color: #F28B54;', 'background-color: var(--welcome-acento);', $content);

// Backgrounds
$content = str_replace('bg-fondo-panel', 'bg-[var(--welcome-card-strong)]', $content);
$content = str_replace('bg-fondo-app', 'bg-transparent', $content);

// Check other elements that use text-[var(--welcome-texto)] incorrectly, wait, I replaced text-parrafo with text-[var(--welcome-texto)]
// and text-titulo with text-[var(--welcome-titulo)]

file_put_contents($file, $content);
echo "Modificaciones realizadas con éxito.";
