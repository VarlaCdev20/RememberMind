<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Servicios;

use App\Models\Instrumento;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use MotorExperto\dominio\instrumentos\CircuitoInstrumentalInicial;

/** Resuelve identidades exactas del catálogo instalado; no completa PK ni versiones por semejanza. */
final class PerfilesInstrumentalesIniciales
{
    public function definicion(Instrumento $instrumento, iterable $preguntas): ?array
    {
        foreach ((new CircuitoInstrumentalInicial)->definiciones() as $metodo => $d) {
            if ($instrumento->codigo !== $d['codigo']) {
                continue;
            }
            if ($instrumento->version !== $d['version'] || $instrumento->nombre !== $d['nombre']
                || $instrumento->tipo !== $d['tipo'] || (string) $instrumento->puntaje_maximo !== '30.00') {
                throw new DomainException('La identidad del instrumento inicial cambió; revise su catálogo antes de capturar.');
            }
            $filas = collect($preguntas);
            $porCodigo = $filas->keyBy('codigo');
            if ($filas->count() !== $porCodigo->count() || $porCodigo->count() !== count($d['campos'])) {
                throw new DomainException('El catálogo inicial contiene campos ausentes o adicionales.');
            }
            $selectores = $campos = [];
            $orden = 0;
            foreach ($d['campos'] as $nombre => $meta) {
                $p = $porCodigo->get($nombre);
                $tipo = $meta['tipo'] === 'LOGICO' ? 'LOGICO' : 'NUMERO';
                $maximo = $meta['puntuable'] ? number_format($meta['maximo'], 2, '.', '') : null;
                if (! $p || $p->cod_instrumento !== $instrumento->getKey() || $p->estado !== 'ACTIVA'
                    || $p->tipo_respuesta !== $tipo || $p->enunciado !== $meta['label']
                    || ($p->puntaje_maximo === null ? null : number_format((float) $p->puntaje_maximo, 2, '.', '')) !== $maximo
                    || (int) $p->orden !== ++$orden || $p->opciones->isNotEmpty()) {
                    throw new DomainException('Un campo inicial no coincide con el contrato de su edición.');
                }
                $selectores[$nombre] = ['cod_pregunta' => $p->getKey(), 'campo' => $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero'];
                $campos[$p->getKey()] = ['nombre' => $nombre, ...$meta];
            }

            return $d + ['metodo' => $metodo, 'campos_por_pk' => $campos, 'perfil' => ['id' => 'INICIAL-'.$d['codigo'].'-1',
                'metodo' => $metodo, 'cod_instrumento' => $instrumento->getKey(), 'codigo_instrumento' => $d['codigo'],
                'version_instrumento' => $d['version'], 'identidad_instrumento' => $d['identidad'], 'selectores' => $selectores]];
        }

        return null;
    }

    /** La carga por lote evita una consulta por aplicación/pregunta. No se guarda configuración en la BDD. */
    public function resolver(Collection $instrumentos): array
    {
        $perfiles = config('sistema_experto.perfiles_instrumentales', []);
        $codigos = array_column((new CircuitoInstrumentalInicial)->definiciones(), 'codigo');
        $iniciales = $instrumentos->filter(fn ($i) => in_array($i->codigo, $codigos, true))->unique(fn ($i) => $i->getKey());
        $iniciales->loadMissing('preguntas.opciones');
        foreach ($iniciales as $instrumento) {
            if ($instrumento->estado !== 'ACTIVO') {
                continue;
            }
            $perfil = $this->definicion($instrumento, $instrumento->preguntas)['perfil'];
            if (collect($perfiles)->contains(fn ($p) => ($p['cod_instrumento'] ?? null) === $instrumento->getKey())) {
                throw new DomainException('Existe más de un vínculo para el instrumento inicial.');
            }
            $perfiles[] = $perfil;
        }

        return $perfiles;
    }

    public function puntuacion(array $definicion, array $respuestas): array
    {
        $valores = [];
        foreach ($respuestas as $r) {
            if (isset($r['puntaje'])) {
                throw ValidationException::withMessages(['respuestas' => 'Introduce la puntuación en su campo original; el cálculo del perfil no admite una segunda puntuación manual.']);
            }
            $valores[$r['cod_pregunta']] = $r['valor_logico'] ?? $r['valor_numero'] ?? null;
        }
        $r = $this->previsualizar($definicion, $valores);
        if ($r['invalidos'] !== []) {
            throw ValidationException::withMessages(['respuestas' => 'Revisa los enteros, rangos y coherencia de las puntuaciones del protocolo.']);
        }

        return $r;
    }

    /** Inventario de catálogo, sin aplicaciones, residentes ni configuración privada. */
    public function estado(): array
    {
        $codigos = array_column((new CircuitoInstrumentalInicial)->definiciones(), 'codigo');
        $estado = [];
        foreach (Instrumento::query()->whereIn('codigo', $codigos)->where('estado', 'ACTIVO')->with('preguntas.opciones')->get() as $i) {
            $d = $this->definicion($i, $i->preguntas);
            $estado[$d['familia']] = ['codigo' => $d['codigo'], 'edicion' => $d['version'], 'metodo' => $d['metodo'],
                'perfil' => $d['perfil']['id'], 'campos_vinculados' => count($d['campos'])];
        }

        return $estado;
    }

    /** Previsualización sin persistencia: el writer vuelve a cotejar catálogo y originales. */
    public function previsualizar(array $definicion, array $valores): array
    {
        $captura = $definicion['identidad'] + ['protocolo' => $definicion['metodo']];
        foreach ($definicion['campos_por_pk'] as $pk => $meta) {
            $v = $valores[$pk] ?? null;
            if ($v === '' || $v === null) {
                $v = null;
            } elseif ($meta['tipo'] === 'LOGICO') {
                $v = match (true) {
                    in_array($v, [true, 1, '1'], true) => true,
                    in_array($v, [false, 0, '0'], true) => false,
                    default => $v,
                };
            } elseif ((is_int($v) || is_float($v) || is_string($v)) && is_numeric($v) && is_finite((float) $v)
                && (float) $v >= 0 && (float) $v <= 99999999 && floor((float) $v) === (float) $v) {
                $v = (int) $v;
            }
            $captura[$meta['nombre']] = $v;
        }

        return (new CircuitoInstrumentalInicial)->calcular($definicion['metodo'], $captura)['resumen_inicial'];
    }
}
