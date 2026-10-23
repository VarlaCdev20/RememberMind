<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Simulacion;

use App\Models\Instrumento;
use App\Models\Residente;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;

/** Bindings explícitos del lote COH30: no identifica pruebas por similitud ni altera reglas. */
final class CohorteCognitivaLocal
{
    public const VERSION_LOTE = 'COHORTE-INFERENCIA-30-V1';

    public const MARCA_ADMISION = self::VERSION_LOTE.' · DATOS SINTÉTICOS, USO EXCLUSIVO DE PRUEBAS.';

    public const METODOS = ['COG-MEM' => 'HVLT-R-BO-2015-F5', 'COG-ATE' => 'BTA-BO-2015-NL',
        'COG-EJE' => 'TMT-B-BO-2015', 'COG-LEN' => 'BNT-BO-2015-60', 'COG-VIS' => 'ROCF-BO-2015-COPIA-A'];

    public static function habilitada(): bool
    {
        if (! app()->environment('local') || config('sistema_experto.simulacion_local.habilitada') !== true) {
            return false;
        }
        $db = DB::connection();
        if (config('sistema_experto.simulacion_local.base_permitida') !== $db->getDatabaseName()) {
            return false;
        }

        return ($db->getDriverName() === 'pgsql' && in_array($db->getConfig('host'), ['127.0.0.1', 'localhost', '::1'], true))
            || ($db->getDriverName() === 'sqlite' && $db->getDatabaseName() === ':memory:');
    }

    public static function esResidente(Residente $residente): bool
    {
        return preg_match('/^COH30-S(?:0[1-9]|[12][0-9]|30)$/D', (string) $residente->numero_documento) === 1
            && $residente->admisiones()->where('observacion', self::MARCA_ADMISION)->exists();
    }

    /** La pertenencia y búsqueda del lote no dependen del nombre visible del residente. */
    public static function limitarLote(Builder $query): Builder
    {
        if (! self::habilitada()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('numero_documento', array_map(fn ($i) => sprintf('COH30-S%02d', $i), range(1, 30)))
            ->whereHas('admisiones', fn ($q) => $q->where('observacion', self::MARCA_ADMISION));
    }

    /** Sólo lee instrumentos/preguntas nominales del lote; adquisición conserva sus Policies y validaciones. */
    public function perfiles(Residente $residente): array
    {
        if (! self::habilitada() || ! self::esResidente($residente)) {
            return [];
        }
        $instrumentos = Instrumento::query()->whereIn('codigo', array_map(fn ($d) => 'COH30-'.$d, array_keys(self::METODOS)))
            ->with(['preguntas' => fn ($q) => $q->orderBy('orden')])->get()->keyBy('codigo');
        $registro = (new RegistroMetodosInstrumentales)->metodos();
        $perfiles = [];
        foreach (self::METODOS as $dominio => $metodo) {
            $i = $instrumentos->get('COH30-'.$dominio);
            $meta = $registro[$metodo];
            if (! $i || $i->version !== 'TEST-1' || $i->tipo !== 'COGNITIVO' || $i->estado !== 'ACTIVO'
                || $i->preguntas->count() !== count($meta['entradas'])) {
                throw new DomainException('La captura de entrenamiento no coincide con el catálogo versionado del lote.');
            }
            $selectores = [];
            foreach ($meta['entradas'] as $campo => $tipo) {
                $orden = count($selectores);
                $p = $i->preguntas->get($orden);
                $tipoPregunta = $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO';
                if (! $p || $p->codigo !== 'ENTRADA-'.$orden || (int) $p->orden !== $orden + 1
                    || $p->enunciado !== 'Dato sintético: '.$campo || $p->tipo_respuesta !== $tipoPregunta || $p->estado !== 'ACTIVA') {
                    throw new DomainException('Las preguntas de entrenamiento cambiaron: revise su identidad, orden y tipo antes de interpretar.');
                }
                $selectores[$campo] = ['cod_pregunta' => $p->getKey(), 'campo' => $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero'];
            }
            $perfiles[] = ['id' => 'COH30-'.$dominio, 'cod_instrumento' => $i->getKey(), 'codigo_instrumento' => $i->codigo,
                'version_instrumento' => 'TEST-1', 'metodo' => $metodo, 'identidad_instrumento' => $meta['identidad'], 'selectores' => $selectores];
        }

        return $perfiles;
    }
}
