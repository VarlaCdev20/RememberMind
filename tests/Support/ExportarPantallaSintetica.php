<?php

declare(strict_types=1);

namespace Tests\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use LogicException;

/** Lectura estática de la vista Blade real. No crea rutas de acceso a la aplicación. */
final class ExportarPantallaSintetica
{
    public static function guardar(string $directorio, string $caso, string $html): void
    {
        if (! in_array($caso, ['S01', 'S04', 'S07', 'S10'], true) || ! is_dir($directorio)
            || ! str_contains($html, 'Caso sintético') || ! str_contains($html, 'Perfil compatible con posible DCL')) {
            throw new LogicException('La exportación requiere una pantalla sintética calculada y un destino existente.');
        }
        $dom = new DOMDocument;
        $previo = libxml_use_internal_errors(true);
        try {
            $dom->loadHTML('<?xml encoding="UTF-8"><div id="export-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previo);
        }
        $xpath = new DOMXPath($dom);
        foreach ($xpath->query('//script | //iframe | //form') as $nodo) {
            $nodo->parentNode->removeChild($nodo);
        }
        foreach ($xpath->query('//*') as $nodo) {
            if (! $nodo instanceof DOMElement) {
                continue;
            }
            // La copia estática no contiene una sesión, snapshots Livewire ni acciones de mutación.
            foreach (iterator_to_array($nodo->attributes) as $atributo) {
                if (str_starts_with($atributo->name, 'wire:') || str_starts_with($atributo->name, 'x-')
                    || str_starts_with($atributo->name, '@') || str_starts_with($atributo->name, 'on')) {
                    if ($atributo->name === 'x-show' || $atributo->name === 'x-cloak') {
                        $nodo->setAttribute('hidden', 'hidden');
                    }
                    $nodo->removeAttribute($atributo->name);
                }
            }
            if ($nodo->tagName === 'button') {
                $nodo->setAttribute('disabled', 'disabled');
                $nodo->setAttribute('title', 'Copia de lectura. Las acciones se comprueban en el test Livewire.');
            }
            if ($nodo->tagName === 'a' && ! str_starts_with($nodo->getAttribute('href'), '#')) {
                $nodo->removeAttribute('href');
                $nodo->setAttribute('aria-disabled', 'true');
            }
        }
        $raiz = $dom->getElementById('export-root');
        if ($raiz === null) {
            throw new LogicException('No se pudo reconstruir la vista exportada.');
        }
        $cuerpo = $dom->saveHTML($raiz);
        $manifest = json_decode(file_get_contents(base_path('public/build/manifest.json')), true, flags: JSON_THROW_ON_ERROR);
        $css = $manifest['resources/frontend/styles/app.css']['file'] ?? null;
        if ($css === null) {
            foreach ($manifest as $entrada) {
                if (($entrada['isEntry'] ?? false) && str_ends_with($entrada['file'] ?? '', '.css')) {
                    $css = $entrada['file'];
                    break;
                }
            }
        }
        if (! is_string($css) || ! preg_match('~^assets/[A-Za-z0-9_.-]+\.css$~D', $css) || ! is_file(public_path('build/'.$css))) {
            throw new LogicException('Se necesita el CSS construido del Design System para exportar la vista.');
        }
        $nav = '';
        foreach (['S01' => 'Amnésico único', 'S04' => 'Amnésico multidominio', 'S07' => 'No amnésico único', 'S10' => 'No amnésico multidominio'] as $id => $nombre) {
            $nav .= '<a class="rm-btn rm-btn-secondary" href="/'.$id.'.html"'.($id === $caso ? ' aria-current="page"' : '').'>'.$nombre.'</a> ';
        }
        $documento = '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            .'<meta http-equiv="Content-Security-Policy" content="default-src &apos;none&apos;; style-src &apos;self&apos; &apos;unsafe-inline&apos;; img-src &apos;self&apos; data:; font-src &apos;self&apos;; base-uri &apos;none&apos;; form-action &apos;none&apos;">'
            .'<title>Caso ficticio · Perfil cognitivo calculado</title><link rel="stylesheet" href="/'.$css.'">'
            .'<style>[hidden]{display:none!important}body{margin:0;background:var(--rm-bg,#f5f1eb);color:var(--rm-text-primary,#514c46)}'
            .'header,main{max-width:1440px;margin:auto;padding:20px}header{border-bottom:2px solid var(--rm-warning,#b88731)}'
            .'header h1{font-size:26px;font-weight:800}header p{margin:8px 0}nav{display:flex;flex-wrap:wrap;gap:8px;margin-top:16px}'
            .'[aria-current=page]{outline:2px solid var(--rm-primary,#6c806b)}button:disabled{cursor:default}a:not([href]){cursor:default}'
            .'</style></head><body><header><strong>DATOS SINTÉTICOS · CASO FICTICIO</strong><h1>Perfil orientativo calculado por el motor</h1>'
            .'<p>Esta pantalla corresponde a un residente ficticio de la cohorte de pruebas. No es el expediente de Elena ni una valoración de una persona real.</p>'
            .'<p>Vista Blade original, generada después de ejecutar el motor con fuentes SQL de prueba. Copia de lectura; no guarda ni permite firmar.</p><nav aria-label="Otros perfiles de prueba">'.$nav.'</nav></header>'
            .'<main>'.$cuerpo.'</main></body></html>';
        if (file_put_contents($directorio.'/'.$caso.'.html', $documento) === false) {
            throw new LogicException('No se pudo conservar la pantalla sintética.');
        }
    }
}
