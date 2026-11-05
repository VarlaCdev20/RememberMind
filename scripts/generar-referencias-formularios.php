<?php

// Exportación estática de referencias históricas. Nunca ejecuta Blade histórico.
$root = dirname(__DIR__);
$sources = [
    ['adulto', 'ea7cca83', 'resources/views/pages/adultos-mayores/create.blade.php', 6],
    ['personal-pagina', 'ea7cca83', 'resources/views/pages/usuarios/create.blade.php', 6],
    ['personal-modal', '155a1516', 'app/Modulos/Identidad/Presentacion/Vistas/livewire/identidad/personal-institucional-form.blade.php', 7],
];
$output = "{{-- Referencias estáticas generadas; no ejecutar código histórico. --}}\n";
foreach ($sources as [$key, $revision, $path, $count]) {
    $process = proc_open(['git', 'show', "$revision:$path"], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root);
    if (! is_resource($process)) throw new RuntimeException('No se pudo leer Git.');
    $source = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    if (proc_close($process) !== 0) throw new RuntimeException($error);
    preg_match_all('~(?:\{\{--|<!--)\s*Paso\s+(\d+):.*?(?:--\}\}|-->)~is', $source, $matches, PREG_OFFSET_CAPTURE);
    $starts = [];
    foreach ($matches[1] as $index => [$number]) {
        $number = (int) $number;
        if ($number >= 1 && $number <= $count && ! isset($starts[$number])) $starts[$number] = $matches[0][$index][1];
    }
    if (count($starts) !== $count) throw new RuntimeException("Apartados incompletos: $key");
    for ($step = 1; $step <= $count; $step++) {
        $html = substr($source, $starts[$step], ($starts[$step + 1] ?? strlen($source)) - $starts[$step]);
        if ($step === $count) {
            $footer = match ($key) {
                'adulto' => '<section class="rm-surface-glass p-4 mt-6">',
                'personal-pagina' => '{{-- NAVEGACIÓN DE FORMULARIO --}}',
                'personal-modal' => '<div class="flex items-center justify-between pt-3 mt-2',
            };
            $html = explode($footer, $html, 2)[0];
        }
        if ($key === 'personal-modal' && $step === $count) $html = explode("\n@else", $html, 2)[0];
        $html = preg_replace('~@php\b.*?@endphp|@error\s*\(.*?\).*?@enderror~s', '', $html);
        $html = preg_replace('~\{\{--.*?--\}\}|<!--.*?-->|<script\b.*?</script>|\{!!.*?!!\}~is', '', $html);
        $html = preg_replace('~\{\{.*?\}\}~s', '', $html);
        // Las directivas restantes se eliminan como texto; no se evalúan condiciones ni bucles.
        $html = preg_replace('~@[a-zA-Z_][a-zA-Z_0-9]*(?:[ \t]*\([^\r\n]*\))?~', '', $html);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div id="export-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors(); libxml_use_internal_errors($previous);
        $allowed = ['div','section','p','span','strong','small','h2','h3','h4','h5','label','input','select','option','textarea','button','i','a','fieldset','legend','dl','dt','dd','ul','ol','li','table','thead','tbody','tr','td','th','br'];
        $nodes = iterator_to_array($dom->getElementsByTagName('*'));
        foreach (array_reverse($nodes) as $node) {
            if ($node->hasAttribute('x-show') && str_contains($node->getAttribute('x-show'), 'errors')) { $node->parentNode?->removeChild($node); continue; }
            if (! in_array($node->tagName, $allowed, true)) { $node->parentNode?->removeChild($node); continue; }
            foreach (iterator_to_array($node->attributes) as $attribute) {
                if (! in_array($attribute->name, ['class','type','placeholder','rows','cols','for','id'], true)) $node->removeAttribute($attribute->name);
            }
            if ($node->hasAttribute('class')) {
                $classes = array_filter(explode(' ', $node->getAttribute('class')), fn ($class) => preg_match('~^[a-zA-Z0-9_:/!\[\]#%.(),-]+$~', $class));
                $node->setAttribute('class', implode(' ', $classes));
            }
            foreach (['id', 'for'] as $attribute) if ($node->hasAttribute($attribute)) $node->setAttribute($attribute, "ref-$key-$step-".preg_replace('/[^a-zA-Z0-9_-]/', '', $node->getAttribute($attribute)));
            if (in_array($node->tagName, ['input','select','textarea','button'], true)) {
                $node->setAttribute('disabled', 'disabled');
                if ($node->tagName === 'button') $node->setAttribute('type', 'button');
            }
            if ($node->tagName === 'i') $node->setAttribute('aria-hidden', 'true');
        }
        $export = $dom->saveHTML();
        $export = preg_replace('~<\?xml.*?\?>~s', '', $export);
        $export = str_replace('@', '&#64;', $export);
        $export = preg_replace('/[ \t]+$/m', '', $export);
        if (preg_match('~\{\{|\{!!|<\?php|wire:|x-data|x-on:|@click|<script~i', $export)) throw new RuntimeException("Contenido activo en $key/$step");
        $output .= '<section x-show="reference === \''.$key.'\' && step === '.$step.'" x-cloak data-reference-step="'.$key.'-'.$step.'">'.$export."</section>\n";
    }
}
$target = $root.'/app/Modulos/Identidad/Presentacion/Vistas/referencias/formularios-historicos.blade.php';
if (! is_dir(dirname($target))) mkdir(dirname($target), 0777, true);
file_put_contents($target, $output);
echo "19 apartados estáticos exportados.\n";
