<?php

namespace App\Modulos\Reportes\Presentacion\Reportes;

/** Presentación de los mismos conteos autorizados; no consulta ni interpreta datos clínicos. */
final class GraficasGerencia
{
    public const TIPOS = ['auto', 'dona', 'torta', 'radar', 'barras'];

    // Solo categorías disjuntas. Totales y subconjuntos superpuestos nunca son porciones.
    private const PARTICIONES = [
        'Trabajadores registrados' => ['Trabajadores activos', 'Trabajadores inactivos'],
        'Archivos laborales registrados' => ['Archivos validados', 'Archivos observados', 'Archivos cargados', 'Archivos pendientes de revisión'],
        'Áreas registradas' => ['Áreas activas'],
        'Residentes registrados' => ['Residentes admitidos'],
        'Personas de contacto registradas' => ['Contactos activos'],
        'Fotografías registradas' => ['Fotografías activas', 'Fotografías archivadas'],
        'Avisos documentales' => ['Avisos con plazo vencido'],
    ];

    /** Paleta de impresión de los tokens care, clinical, cognitive, rehab y reference. */
    public const COLORES = ['#78826E', '#7D94A8', '#9B83BF', '#6FB89A', '#B6ACA2'];

    public function series(array $reporte): array
    {
        $resultado = [];
        foreach (collect($reporte['filas'])->groupBy('unidad') as $unidad => $filas) {
            $serie = $filas->map(fn ($fila) => ['etiqueta' => $fila['indicador'], 'valor' => $fila['valor']])->values()->all();
            $composicion = $unidad === 'camas' && $filas->every(fn ($fila) => in_array($fila['indicador'], ['Camas ocupadas', 'Camas disponibles', 'Camas no habilitadas'], true));
            $nota = 'Conteos en '.$unidad.'. Escala común desde cero.';
            foreach (self::PARTICIONES as $etiquetaTotal => $categorias) {
                $total = $filas->firstWhere('indicador', $etiquetaTotal);
                $partes = $filas->whereIn('indicador', $categorias);
                if (! $total || $partes->isEmpty() || $partes->count() + 1 !== $filas->count()) {
                    continue;
                }
                if (! $this->valido($total['valor']) || ! $partes->every(fn ($fila) => $this->valido($fila['valor'])) || $partes->sum('valor') > $total['valor']) {
                    break;
                }
                $serie = $partes->map(fn ($fila) => ['etiqueta' => $fila['indicador'], 'valor' => $fila['valor']])->values()->all();
                $resto = $total['valor'] - $partes->sum('valor');
                if ($resto > 0 || count($serie) === 1) {
                    $serie[] = ['etiqueta' => 'Resto de registros', 'valor' => $resto];
                }
                $composicion = true;
                $nota = 'Base: '.$etiquetaTotal.' ('.$total['valor'].'). El resto es el total menos las categorías mostradas.';
                break;
            }
            $completa = collect($serie)->every(fn ($punto) => $this->valido($punto['valor']));
            $tipos = ['barras'];
            if ($composicion && $completa && count($serie) >= 2) {
                $tipos = [...$tipos, 'dona', 'torta'];
                if (str_starts_with($nota, 'Conteos')) {
                    $nota = 'Distribución de las categorías seleccionadas. Base: '.array_sum(array_column($serie, 'valor')).' '.$unidad.'.';
                }
            }
            if ($completa && count($serie) >= 3) {
                $tipos[] = 'radar';
            }
            $preferido = in_array('radar', $tipos, true) && $unidad === 'archivos' ? 'radar'
                : (in_array('torta', $tipos, true) && $unidad === 'fotografías' ? 'torta'
                    : (in_array('dona', $tipos, true) ? 'dona' : 'barras'));
            $resultado[] = ['id' => 'serie-'.count($resultado), 'titulo' => 'Indicadores en '.$unidad, 'unidad' => $unidad,
                'serie' => $serie, 'tipos' => $tipos, 'recomendado' => $preferido, 'nota' => $nota,
                'composicion' => $composicion, 'completa' => $completa,
                'total' => $completa && $composicion ? array_sum(array_column($serie, 'valor')) : null,
                'maximo' => max([0, ...array_filter(array_column($serie, 'valor'), fn ($valor) => $this->valido($valor))])];
        }

        return $resultado;
    }

    public function tipo(array $grafica, string $preferido): string
    {
        return in_array($preferido, $grafica['tipos'], true) ? $preferido
            : ($preferido === 'auto' ? $grafica['recomendado'] : 'barras');
    }

    public function paraInicio(array $reporte): array
    {
        $ocupadas = $reporte['ocupadas'];
        $camas = $reporte['camas'];
        $avisos = $reporte['avisos_documentales'];

        return array_map(function (array $grafica) use ($reporte, $ocupadas, $camas, $avisos): array {
            $grafica['resumen'] = match ($grafica['unidad']) {
                'personas' => [
                    'titulo' => 'Personal activo', 'icono' => 'ph-identification-badge',
                    'valor' => $reporte['personal'], 'unidad' => $reporte['personal'] === 1 ? 'trabajador' : 'trabajadores', 'solo_cifra' => true,
                ],
                'camas' => [
                    'titulo' => 'Ocupación de camas', 'icono' => 'ph-bed',
                    'valor' => $camas, 'unidad' => $camas === 1 ? 'cama registrada' : 'camas registradas', 'solo_cifra' => ! $camas || ! $grafica['completa'],
                    'porcentaje' => $camas > 0 && $grafica['completa'] && $ocupadas !== null
                        ? round($ocupadas / $camas * 100, 1) : null,
                ],
                'avisos' => [
                    'titulo' => 'Avisos documentales', 'icono' => 'ph-bell',
                    'valor' => $avisos, 'unidad' => $avisos === 1 ? 'aviso documental' : 'avisos documentales',
                    'solo_cifra' => ! $avisos || ! $grafica['completa'],
                ],
            };
            if ($grafica['unidad'] === 'camas') {
                $grafica['tonos'] = ['care', 'clinical', 'reference'];
            }
            if ($grafica['unidad'] === 'avisos' && $grafica['composicion']) {
                $grafica['tonos'] = ['alert', 'reference'];
                $grafica['serie'][1]['etiqueta'] = 'Otros avisos documentales';
            }

            return $grafica;
        }, $this->series($reporte));
    }

    /** SVG local para DomPDF; sin JavaScript, recursos remotos ni etiquetas de entrada. */
    public function imagen(array $grafica, string $tipo): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="240" viewBox="0 0 300 240">';
        $valores = array_column($grafica['serie'], 'valor');
        if (in_array($tipo, ['dona', 'torta'], true)) {
            $total = array_sum($valores);
            if ($total === 0 || $total === 0.0) {
                $svg .= '<circle cx="150" cy="120" r="86" fill="none" stroke="#E8E1DA" stroke-width="28"/>';
            } else {
                $angulo = -M_PI / 2;
                foreach ($valores as $indice => $valor) {
                    if ($valor <= 0) {
                        continue;
                    }
                    $fin = $angulo + $valor / $total * M_PI * 2;
                    // Dos arcos también representan correctamente una categoría del 100 %.
                    $medio = ($angulo + $fin) / 2;
                    $p = fn ($radio, $a) => round(150 + cos($a) * $radio, 3).' '.round(120 + sin($a) * $radio, 3);
                    $camino = 'M '.$p(96, $angulo).' A 96 96 0 0 1 '.$p(96, $medio).' A 96 96 0 0 1 '.$p(96, $fin);
                    $camino .= $tipo === 'dona' ? ' L '.$p(58, $fin).' A 58 58 0 0 0 '.$p(58, $medio).' A 58 58 0 0 0 '.$p(58, $angulo).' Z' : ' L 150 120 Z';
                    $svg .= '<path d="'.$camino.'" fill="'.self::COLORES[$indice % count(self::COLORES)].'" fill-opacity="0.82" stroke="#F7F3EF" stroke-width="2"/>';
                    $angulo = $fin;
                }
            }
        } elseif ($tipo === 'radar') {
            $n = count($valores);
            $p = fn ($i, $ratio) => round(150 + sin($i * 2 * M_PI / $n) * 87 * $ratio, 3).','.round(120 - cos($i * 2 * M_PI / $n) * 87 * $ratio, 3);
            foreach ([0.25, 0.5, 0.75, 1] as $ratio) {
                $svg .= '<polygon points="'.implode(' ', array_map(fn ($i) => $p($i, $ratio), range(0, $n - 1))).'" fill="none" stroke="#CCC5BF" stroke-width="1"/>';
            }
            foreach ($valores as $i => $valor) {
                [$x, $y] = explode(',', $p($i, 1));
                [$lx, $ly] = explode(',', $p($i, 1.18));
                $svg .= '<line x1="150" y1="120" x2="'.$x.'" y2="'.$y.'" stroke="#CCC5BF"/>';
                $svg .= '<text x="'.$lx.'" y="'.($ly + 4).'" text-anchor="middle" font-family="DejaVu Sans" font-size="12" fill="#403B37">'.($i + 1).'</text>';
            }
            $puntos = array_map(fn ($i) => $p($i, $grafica['maximo'] > 0 ? $valores[$i] / $grafica['maximo'] : 0), range(0, $n - 1));
            $svg .= '<polygon points="'.implode(' ', $puntos).'" fill="#78826E" fill-opacity="0.3" stroke="#5F7567" stroke-width="2"/>';
            foreach ($puntos as $punto) {
                [$x, $y] = explode(',', $punto);
                $svg .= '<circle cx="'.$x.'" cy="'.$y.'" r="3" fill="#5F7567"/>';
            }
        }

        return 'data:image/svg+xml;base64,'.base64_encode($svg.'</svg>');
    }

    private function valido(mixed $valor): bool
    {
        return (is_int($valor) || is_float($valor)) && is_finite((float) $valor) && $valor >= 0;
    }
}
