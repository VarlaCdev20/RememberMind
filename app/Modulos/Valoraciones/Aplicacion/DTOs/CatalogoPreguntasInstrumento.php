<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Aplicacion\DTOs;

use App\Models\Instrumento;

/** Proyección estable del catálogo que se confirmó; la huella no sustituye una instantánea histórica. */
final class CatalogoPreguntasInstrumento
{
    public static function proyectar(Instrumento $instrumento, iterable $preguntas): array
    {
        $definicion = array_intersect_key($instrumento->getRawOriginal(), array_flip(['cod_instrumento', 'codigo', 'nombre', 'tipo', 'version', 'estado', 'puntaje_maximo']));
        $filas = collect($preguntas)->sortBy([['orden', 'asc'], ['cod_pregunta', 'asc']])->map(fn ($p) => [
            'codigo' => $p->getKey(), 'codigo_catalogo' => $p->codigo, 'enunciado' => $p->enunciado,
            'dominio' => $p->dominio, 'orden' => $p->orden, 'estado' => $p->estado,
            'tipo' => $p->tipo_respuesta, 'puntaje_maximo' => $p->getRawOriginal('puntaje_maximo'),
            'campo' => match ($p->tipo_respuesta) {
                'NUMERO', 'NUMERICO', 'NUMERICA' => 'valor_numero', 'LOGICO', 'BOOLEANO' => 'valor_logico',
                'OPCION', 'OPCION_UNICA' => 'cod_opcion', 'TEXTO' => 'valor_texto', default => null,
            }, 'opciones' => $p->opciones->sortBy([['orden', 'asc'], ['cod_opcion', 'asc']])
                ->map(fn ($o) => ['codigo' => $o->getKey(), 'nombre' => $o->nombre, 'valor' => $o->valor,
                    'orden' => $o->orden, 'estado' => $o->estado, 'puntaje' => $o->getRawOriginal('puntaje')])->values()->all(),
        ])->values()->all();

        return ['instrumento' => $definicion, 'preguntas' => $filas,
            'huella' => hash('sha256', json_encode([$definicion, $filas], JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION))];
    }
}
