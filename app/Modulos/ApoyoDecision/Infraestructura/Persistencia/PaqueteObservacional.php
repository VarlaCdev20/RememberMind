<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Compartido\Infraestructura\Persistencia\CatalogoCodigos;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoObservacional;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoVersionado;
use DomainException;
use Illuminate\Support\Facades\DB;
use MotorExperto\dominio\conocimiento\CompiladorObservacional;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorObservaciones as Motor;

/** Conserva el paquete literal aprobado; cada versión es inmutable y no habilita Petersen. */
final class PaqueteObservacional implements ConocimientoObservacional
{
    /** Carga idempotente dentro de la transacción de la acción autorizada. */
    public function preparar(User $autor): array
    {
        if (! DB::table('versiones_modelo_experto')->where('codigo_version', Motor::VERSION)->exists()) {
            app(CodigosEntidad::class)->serializar('versiones_modelo_experto', fn () => $this->construir($autor));
        }

        return $this->vigente() ?? throw new DomainException('No hay conocimiento observacional vigente.');
    }

    /** Selección por cadena real, nunca por la fecha de una versión ajena. Sin escrituras de catálogo. */
    public function vigente(): ?array
    {
        return $this->seleccionarVigente(0);
    }

    private function seleccionarVigente(int $intento): ?array
    {
        $versiones = DB::table('versiones_modelo_experto')->get()->keyBy('cod_version_modelo');
        $raices = $versiones->where('codigo_version', Motor::VERSION);
        if ($raices->isEmpty()) {
            return null;
        }
        if ($raices->count() !== 1) {
            throw new DomainException('Raíz observacional ambigua.');
        }
        $actual = $raices->sole();
        if ($actual->cod_version_anterior !== null) {
            throw new DomainException('La raíz observacional no puede tener antecesora.');
        }
        $visitadas = [];
        while (true) {
            if (isset($visitadas[$actual->cod_version_modelo])) {
                throw new DomainException('Ciclo en el conocimiento observacional.');
            }
            $visitadas[$actual->cod_version_modelo] = true;
            $hijas = $versiones->where('cod_version_anterior', $actual->cod_version_modelo);
            if ($hijas->isEmpty()) {
                break;
            }
            if ($hijas->count() !== 1 || $actual->fecha_hora_retiro === null) {
                throw new DomainException('Sucesión observacional ambigua o no cerrada.');
            }
            $actual = $hijas->sole();
        }
        // El gestor y el ejecutor serializan sobre la misma versión, antes de leer reglas/revisiones.
        $retiradaAntesDelBloqueo = $actual->fecha_hora_retiro;
        $actual = DB::table('versiones_modelo_experto')->where('cod_version_modelo', $actual->cod_version_modelo)->lockForUpdate()->sole();
        if ($retiradaAntesDelBloqueo === null && $actual->fecha_hora_retiro !== null && $intento < 3) {
            return $this->seleccionarVigente($intento + 1);
        }
        if ($actual->estado !== 'ACTIVO' || $actual->fecha_hora_retiro !== null
            || ($actual->fecha_hora_vigencia !== null && new \DateTimeImmutable($actual->fecha_hora_vigencia) > now())) {
            throw new DomainException('La versión observacional no está disponible para nuevas ejecuciones.');
        }

        return $this->historico($actual->cod_version_modelo, null);
    }

    public function historico(string $version, ?array $ejecutadas): array
    {
        $actual = DB::table('versiones_modelo_experto')->where('cod_version_modelo', $version)->sole();
        $codigo = $actual->codigo_version;
        $visitadas = [];
        while ($actual->codigo_version !== Motor::VERSION) {
            if (isset($visitadas[$actual->cod_version_modelo]) || $actual->cod_version_anterior === null) {
                throw new DomainException('La versión no pertenece a la familia observacional.');
            }
            $visitadas[$actual->cod_version_modelo] = true;
            $actual = DB::table('versiones_modelo_experto')->where('cod_version_modelo', $actual->cod_version_anterior)->sole();
        }
        if ($actual->cod_version_anterior !== null) {
            throw new DomainException('La raíz observacional tiene una antecesora incompatible.');
        }

        return (new CompiladorObservacional)->compilar(app(ConocimientoVersionado::class)->instantanea($version), $version, $codigo, $ejecutadas);
    }

    public function verificar(string $version): array
    {
        $paquete = $this->construir(null);
        if ($paquete['version'] !== $version) {
            throw new DomainException('La versión observacional conservada no coincide con su contrato.');
        }

        return $paquete;
    }

    private function construir(?User $autor): array
    {
        $sello = ['estado' => 'ACTIVO', 'fecha_hora_creacion' => now()->format('Y-m-d H:i:s'),
            'cod_usuario_creacion' => $autor?->getKey(), 'observacion' => 'Observación literal autorizada; sin baremos, diagnóstico ni clasificación DCL.'];
        $crear = $autor !== null;
        $version = $this->fila('versiones_modelo_experto', ['codigo_version' => Motor::VERSION], [
            'nombre' => 'Hallazgos observacionales del seguimiento', 'descripcion' => 'Tres implicaciones booleanas de la propuesta funcional v0.1.',
            'fecha_hora_vigencia' => null, 'fecha_hora_retiro' => null, 'motivo_cambio' => 'Primera versión literal solicitada por la propietaria.', 'cod_version_anterior' => null] + $sello, $crear);
        $base = ['cod_version_modelo' => $version] + $sello;
        $entrada = $this->fila('dominios_valores_expertos', ['cod_version_modelo' => $version, 'codigo_dominio' => 'OBS-LOGICO'],
            ['nombre' => 'Indicador registrado', 'tipo_dominio' => 'CATEGORICO', 'descripcion' => 'Sí o no literal. NULL permanece desconocido.'] + $sello, $crear);
        $salida = $this->fila('dominios_valores_expertos', ['cod_version_modelo' => $version, 'codigo_dominio' => 'OBS-HALLAZGO'],
            ['nombre' => 'Hecho observacional documentado', 'tipo_dominio' => 'CATEGORICO', 'descripcion' => 'No acredita alteración objetiva ni DCL.'] + $sello, $crear);
        $valores = [];
        foreach (['SI', 'NO'] as $valor) {
            $valores[$valor] = $this->fila('valores_semanticos', ['cod_dominio_valores' => $entrada, 'codigo_valor' => $valor],
                ['nombre' => $valor, 'definicion_semantica' => 'Literal lógico registrado.', 'estado_aprobacion' => 'APROBADO'] + $sello, $crear);
        }
        $fuente = $this->fila('fuentes_datos_expertas', ['cod_version_modelo' => $version, 'codigo_fuente' => 'OBS-CONTROL'],
            ['nombre' => 'Control cognitivo del seguimiento', 'tipo_fuente' => 'OPERATIVA', 'tabla_raiz' => 'controles_cognitivos',
                'campo_pk_raiz' => 'cod_control_cognitivo', 'campo_residente' => 'cod_residente', 'campo_temporal' => 'fecha_hora',
                'campo_personal' => 'cod_personal', 'campo_estado' => 'estado', 'clave_adaptador' => 'OBS_LITERAL'] + $sello, $crear);
        $reglas = [];
        foreach (Motor::REGLAS as $codigo => $r) {
            $criterio = $this->fila('nodos_semanticos', ['cod_version_modelo' => $version, 'codigo_semantico' => $codigo],
                ['nombre' => $r['nombre'], 'tipo_nodo' => 'CRITERIO', 'definicion' => $r['hallazgo']] + $sello, $crear);
            $nodo = $this->fila('nodos_semanticos', ['cod_version_modelo' => $version, 'codigo_semantico' => 'OBS:'.$r['campo']],
                ['nombre' => $r['nombre'].' registrada', 'tipo_nodo' => 'VARIABLE', 'definicion' => 'Valor booleano literal, sin interpretación instrumental.'] + $sello, $crear);
            $variable = $this->fila('variables_expertas', ['cod_version_modelo' => $version, 'cod_nodo_semantico' => $nodo],
                ['cod_nodo_propietario_primario' => $criterio, 'cod_dominio_valores' => $entrada, 'tipo_semantico' => 'OBSERVACIONAL', 'papel_inferencial' => 'DESCRIPTIVO'] + $sello, $crear);
            $mapeo = $this->fila('mapeos_variables_fuente', ['cod_version_modelo' => $version, 'cod_variable_experta' => $variable, 'cod_fuente_dato_experta' => $fuente],
                ['tipo_extraccion' => 'CAMPO_DIRECTO', 'campo_valor' => $r['campo'], 'clave_selector' => null] + $sello, $crear);
            foreach (['true' => 'SI', 'false' => 'NO'] as $literal => $valor) {
                $this->fila('mapeos_valores_fuente', ['cod_mapeo_variable_fuente' => $mapeo, 'tipo_valor_fuente' => 'bool', 'valor_fuente_exacto' => $literal],
                    ['cod_valor_semantico' => $valores[$valor], 'estado_aprobacion' => 'APROBADO', 'fecha_hora_vigencia_desde' => null, 'fecha_hora_vigencia_hasta' => null] + $sello, $crear);
            }
            $dominio = $this->fila('criterios_dominios_resultado', ['cod_nodo_criterio' => $criterio], ['cod_dominio_valores' => $salida] + $sello, $crear);
            $valorResultado = $this->fila('valores_semanticos', ['cod_dominio_valores' => $salida, 'codigo_valor' => $codigo],
                ['nombre' => $r['nombre'], 'definicion_semantica' => $r['hallazgo'], 'estado_aprobacion' => 'APROBADO'] + $sello, $crear);
            $regla = $this->fila('reglas_expertas', ['cod_version_modelo' => $version, 'codigo_regla' => $codigo],
                ['nombre' => $r['nombre'], 'tipo_regla' => 'OBSERVACIONAL', 'descripcion' => 'SI '.$r['campo'].' = true ENTONCES '.$r['hallazgo']] + $sello, $crear);
            $condicion = $this->fila('condiciones_regla_experta', ['cod_regla_experta' => $regla], ['cod_variable_experta' => $variable, 'cod_valor_semantico' => $valores['SI']], $crear);
            $this->fila('consecuencias_regla_experta', ['cod_regla_experta' => $regla], ['cod_criterio_dominio_resultado' => $dominio, 'cod_valor_semantico' => $valorResultado], $crear);
            $reglas[$codigo] = compact('criterio', 'mapeo', 'regla', 'condicion', 'valorResultado') + $r;
        }

        return compact('version', 'reglas', 'valores');
    }

    private function fila(string $tabla, array $clave, array $datos, bool $crear): string
    {
        $pk = CatalogoCodigos::clave($tabla);
        $filas = DB::table($tabla)->where($clave)->get();
        if ($filas->count() > 1) {
            throw new DomainException('Identidad observacional ambigua.');
        }
        if ($fila = $filas->first()) {
            foreach ($datos as $campo => $valor) {
                if (! in_array($campo, ['cod_usuario_creacion', 'fecha_hora_creacion'], true)
                    && ! ($tabla === 'versiones_modelo_experto' && $campo === 'fecha_hora_retiro') && $valor !== $fila->$campo) {
                    throw new DomainException('Conocimiento observacional alterado; requiere otra versión.');
                }
            }

            return $fila->$pk;
        }
        if (! $crear) {
            throw new DomainException('Conocimiento observacional incompleto.');
        }
        $id = app(CodigosEntidad::class)->siguiente($tabla);
        DB::table($tabla)->insert([$pk => $id] + $clave + $datos);

        return $id;
    }
}
