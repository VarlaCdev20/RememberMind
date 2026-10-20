<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\ReglaExperta;
use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoVersionado;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\IdentidadesConocimiento;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratosConocimiento;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use MotorExperto\dominio\conocimiento\CompiladorObservacional;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\conocimiento\PertenenciaVariableCognitiva;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorReglas;
use MotorExperto\dominio\conocimiento\versiones\PaquetePetersenInstitucional;

/** Gobierno institucional del conocimiento. Ninguna aprobación se inventa o se exige al crear. */
final class GestionarReglasExpertas
{
    /** Prueba declarativa sin residente, persistencia de resultados ni aprobación automática. */
    public function probar(User $actor, string $regla, array $entradas): array
    {
        return DB::transaction(function () use ($actor, $regla, $entradas): array {
            $this->actor($actor, 'gestionarReglas');
            $r = ReglaExperta::query()->whereKey($regla)->sole();
            $tablas = app(ConocimientoVersionado::class)->instantanea($r->cod_version_modelo);
            $condiciones = array_values(array_filter($tablas['condiciones_regla_experta'], fn ($c) => $c['cod_regla_experta'] === $regla));
            $variables = array_column($tablas['variables_expertas'], null, 'cod_variable_experta');
            $valores = array_column($tablas['valores_semanticos'], null, 'cod_valor_semantico');
            if (array_diff(array_keys($entradas), array_column($condiciones, 'cod_variable_experta'))) {
                throw new DomainException('Las entradas deben pertenecer a las condiciones de esta regla.');
            }
            $hechos = [];
            foreach ($entradas as $variable => $valor) {
                if ($valor === '' || $valor === null) {
                    continue;
                }
                if (! is_string($valor) || ! isset($valores[$valor], $variables[$variable])
                    || $valores[$valor]['cod_dominio_valores'] !== $variables[$variable]['cod_dominio_valores']) {
                    throw new DomainException('Entrada incompatible con el dominio de la variable.');
                }
                $hechos[] = ['id' => 'ENTRADA-'.(count($hechos) + 1), 'variable' => $variable, 'valor' => $valor];
            }
            $evaluadas = (new EvaluadorReglas)->evaluarCondiciones($condiciones, $hechos);
            if ($r->tipo_regla === 'OBSERVACIONAL') {
                foreach ($evaluadas as &$condicion) {
                    if (! isset($entradas[$condicion['variable']]) || $entradas[$condicion['variable']] === '') {
                        $condicion['estado'] = 'NO_EVALUABLE';
                    }
                }
                unset($condicion);
            }
            $estados = array_column($evaluadas, 'estado');
            $estado = in_array('NO_CUMPLE', $estados, true) ? 'NO_CUMPLE'
                : (in_array('NO_EVALUABLE', $estados, true) ? 'NO_EVALUABLE' : 'CUMPLE');

            return ['regla' => $regla, 'version' => $r->cod_version_modelo, 'condiciones' => $evaluadas,
                'cumple' => $estado === 'CUMPLE', 'estado' => $estado, 'persistido' => false];
        });
    }

    public function crearRevision(User $actor, string $anterior, string $codigo, string $motivo, array $nueva = [], array $retiradas = [], ?string $sustituye = null): string
    {
        if (! preg_match('/^ORION-[A-Z0-9-]{1,24}$/D', $codigo) || mb_strlen(trim($motivo)) < 10 || mb_strlen($motivo) > 5000
            || ! array_is_list($retiradas) || count($retiradas) !== count(array_unique($retiradas))) {
            throw new DomainException('Indique código ORION, motivo de al menos diez caracteres y retiros no duplicados.');
        }

        return app(CodigosEntidad::class)->serializar('versiones_modelo_experto', function () use ($actor, $anterior, $codigo, $motivo, $nueva, $retiradas, $sustituye): string {
            $actor = $this->actor($actor, 'gestionarReglas');
            $version = VersionModeloExperto::query()->whereKey($anterior)->lockForUpdate()->sole();
            $this->institucional($version);
            if ($version->fecha_hora_retiro !== null) {
                throw new DomainException('Seleccione una versión no retirada para crear su sucesora.');
            }
            $ya = VersionModeloExperto::query()->where('codigo_version', $codigo)->first();
            if ($ya) {
                throw new DomainException('Ese código de versión ya existe; no se sobrescribe.');
            }
            $tablas = app(ConocimientoVersionado::class)->instantanea($anterior);
            $petersen = app(ContratosConocimiento::class)->esFamiliaPetersen($anterior);
            if ($petersen && $nueva !== []) {
                throw new DomainException('Petersen conserva las cuatro definiciones adoptadas. Puede probar, revisar, archivar o crear una sucesora; otra semántica requiere decisión institucional.');
            }
            $reglas = array_column($tablas['reglas_expertas'], null, 'cod_regla_experta');
            if ($sustituye !== null) {
                if ($nueva === [] || ! isset($reglas[$sustituye])) {
                    throw new DomainException('La sustitución requiere una regla anterior de esta versión y una definición nueva.');
                }
                $retiradas = array_values(array_unique([...$retiradas, $sustituye]));
            }
            if (array_diff($retiradas, array_keys($reglas))) {
                throw new DomainException('Una regla archivada pertenece a otra versión.');
            }
            $fecha = now()->format('Y-m-d H:i:s');
            $id = app(CodigosEntidad::class)->siguiente('versiones_modelo_experto');
            $previasPorCodigo = array_column($tablas['reglas_expertas'], 'cod_regla_experta', 'codigo_regla');
            foreach ($tablas as $table => &$rows) {
                foreach ($rows as &$row) {
                    if (array_key_exists('cod_version_modelo', $row)) {
                        $row['cod_version_modelo'] = $id;
                    }
                    if (array_key_exists('cod_usuario_creacion', $row)) {
                        $row['cod_usuario_creacion'] = $actor->getKey();
                    }
                    if (array_key_exists('fecha_hora_creacion', $row)) {
                        $row['fecha_hora_creacion'] = $fecha;
                    }
                    if ($table === 'reglas_expertas') {
                        if (in_array($row['cod_regla_experta'], $retiradas, true) || ($row['estado_revision'] ?? null) === 'RECHAZADA') {
                            $row['estado'] = 'INACTIVO';
                        }
                        $row = $this->pendiente($row);
                    }
                }
                unset($row);
            }
            unset($rows);
            if ($nueva !== []) {
                $this->agregar($tablas, $nueva, $id, $actor->getKey(), $fecha);
            }
            $observacional = count(array_filter($tablas['fuentes_datos_expertas'], fn ($f) => $f['clave_adaptador'] === 'OBS_LITERAL')) > 0;
            if ($observacional && $nueva !== []) {
                foreach ($tablas['reglas_expertas'] as &$reglaNueva) {
                    if ($reglaNueva['cod_regla_experta'] === 'TMP-NEW-RULE') {
                        $reglaNueva['tipo_regla'] = 'OBSERVACIONAL';
                    }
                }
                unset($reglaNueva);
            }
            $header = ['cod_version_modelo' => $id, 'codigo_version' => $codigo,
                'nombre' => 'Conocimiento del sistema experto', 'descripcion' => 'Revisión institucional; decisiones profesionales pendientes.',
                'estado' => 'ACTIVO', 'fecha_hora_creacion' => $fecha, 'fecha_hora_vigencia' => $fecha,
                'fecha_hora_retiro' => null, 'motivo_cambio' => trim($motivo), 'cod_usuario_creacion' => $actor->getKey(),
                'cod_version_anterior' => $anterior, 'observacion' => 'Creación autorizada sin precargar aprobación profesional.'];
            $traducidas = app(IdentidadesConocimiento::class)->traducir(['versiones_modelo_experto' => [$header]] + $tablas, $id, $id, false);
            if ($observacional) {
                (new CompiladorObservacional)->compilar($traducidas, $id, $codigo);
            } elseif ($nueva !== []) {
                $contrato = app(ContratosConocimiento::class)->porVersion($anterior);
                $p = new PaqueteConocimiento($id, array_intersect_key($traducidas, PaqueteConocimiento::CLAVES),
                    $contrato['relaciones'], false, $contrato['tipo_regla_resultado'], $contrato['extracciones']);
                $reglaNueva = array_values(array_filter($p->tabla('reglas_expertas'), static fn ($r) => $r['codigo_regla'] === $nueva['codigo']))[0];
                if ($reglaNueva['tipo_regla'] !== $p->tipoReglaResultado) {
                    throw new DomainException('El tipo de la nueva regla no corresponde al contrato de la familia.');
                }
                foreach ($p->tabla('consecuencias_regla_experta') as $salida) {
                    if ($salida['cod_regla_experta'] !== $reglaNueva['cod_regla_experta']) {
                        continue;
                    }
                    $criterio = $p->fila('criterios_dominios_resultado', $salida['cod_criterio_dominio_resultado'])['cod_nodo_criterio'];
                    foreach ($p->tabla('condiciones_regla_experta') as $condicion) {
                        if ($condicion['cod_regla_experta'] === $reglaNueva['cod_regla_experta'] && ! $p->variableParticipa($condicion['cod_variable_experta'], $criterio)) {
                            throw new DomainException('La nueva regla carece de participación inferencial habilitante.');
                        }
                    }
                }
            }
            if ($petersen) {
                $c = PaquetePetersenInstitucional::contratos();
                (new PaquetePetersenInstitucional)->validar(new PaqueteConocimiento($id,
                    array_intersect_key($traducidas, PaqueteConocimiento::CLAVES), $c['relaciones'], false, $c['tipo_regla_resultado'], []));
            }
            foreach ($traducidas['reglas_expertas'] as &$rule) {
                // Esta FK apunta a la historia existente, nunca a la PK regenerada de la copia.
                $rule['cod_regla_anterior'] = $previasPorCodigo[$rule['codigo_regla']] ?? $sustituye;
                if (! isset($previasPorCodigo[$rule['codigo_regla']]) && $sustituye === null) {
                    $rule['cod_regla_anterior'] = null;
                }
            }
            unset($rule);
            foreach ($traducidas as $table => $rows) {
                if ($rows !== []) {
                    DB::table($table)->insert($rows);
                }
            }
            // Una sucesora publicada retira la anterior de futuras ejecuciones; conserva su contenido.
            DB::table('versiones_modelo_experto')->where('cod_version_modelo', $anterior)->update(['fecha_hora_retiro' => $fecha]);
            $audit = activity('conocimiento_experto')->causedBy($actor)->event('conocimiento_sucedido')
                ->withProperties(['version' => $id, 'anterior' => $anterior, 'reglas_archivadas' => $retiradas,
                    'regla_sustituida' => $sustituye, 'revision_profesional' => 'PENDIENTE'])
                ->log('Nueva revisión de conocimiento conservada sin sobrescribir la anterior.');
            if (! $audit) {
                throw new DomainException('No se confirmó la trazabilidad de la revisión.');
            }

            return $id;
        });
    }

    public function revisar(User $actor, string $regla, string $decision, string $justificacion): void
    {
        if (! in_array($decision, ['APROBADA', 'RECHAZADA'], true) || trim($justificacion) === '' || mb_strlen($justificacion) > 5000) {
            throw new DomainException('Seleccione aprobación o rechazo e indique su justificación.');
        }
        DB::transaction(function () use ($actor, $regla, $decision, $justificacion): void {
            $actor = $this->actor($actor, 'revisarReglas');
            $versionId = ReglaExperta::query()->whereKey($regla)->sole()->cod_version_modelo;
            $version = VersionModeloExperto::query()->whereKey($versionId)->lockForUpdate()->sole();
            $r = ReglaExperta::query()->whereKey($regla)->lockForUpdate()->sole();
            $this->institucional($version);
            if ($version->fecha_hora_retiro !== null || $r->estado !== 'ACTIVO') {
                throw new DomainException('La regla retirada permanece consultable; revise su sucesora vigente.');
            }
            if (in_array($r->estado_revision, ['APROBADA', 'RECHAZADA'], true)) {
                if ($r->estado_revision === $decision && $r->cod_usuario_revision === $actor->getKey()
                    && $r->justificacion_revision === trim($justificacion)) {
                    return;
                }
                throw new DomainException('La decisión original se conserva. Registre una nueva revisión para rectificarla.');
            }
            // Excepción de gobernanza aprobada, limitada a la primera decisión; no cambia condiciones ni consecuencias.
            $changed = DB::table('reglas_expertas')->where('cod_regla_experta', $regla)
                ->where(fn ($q) => $q->whereNull('estado_revision')->orWhere('estado_revision', 'PENDIENTE'))
                ->update(['estado_revision' => $decision, 'cod_usuario_revision' => $actor->getKey(),
                    'fecha_hora_revision' => now()->format('Y-m-d H:i:s'), 'justificacion_revision' => trim($justificacion)]);
            if ($changed !== 1) {
                throw new DomainException('La revisión cambió durante la operación; recargue la regla.');
            }
            $audit = activity('conocimiento_experto')->causedBy($actor)->performedOn($r)->event('regla_revisada')
                ->withProperties(['version' => $version->getKey(), 'decision' => $decision])
                ->log('Decisión profesional de conocimiento registrada sin cambiar la definición de la regla.');
            if (! $audit) {
                throw new DomainException('No se confirmó la auditoría de la decisión.');
            }
        });
    }

    private function actor(User $actor, string $ability): User
    {
        $actor = User::query()->whereKey($actor->getKey())->lockForUpdate()->sole();
        Gate::forUser($actor)->authorize($ability, VersionModeloExperto::class);

        return $actor;
    }

    private function institucional(VersionModeloExperto $v): void
    {
        if (preg_match('/SIM|TEST|EXPERIMENTAL/i', $v->codigo_version)) {
            throw new DomainException('Los paquetes de laboratorio mantienen su gestor y contrato propios.');
        }
    }

    private function pendiente(array $row): array
    {
        return array_replace($row, ['cod_regla_anterior' => null, 'estado_revision' => 'PENDIENTE',
            'cod_usuario_revision' => null, 'fecha_hora_revision' => null, 'justificacion_revision' => null]);
    }

    private function agregar(array &$t, array $n, string $version, string $actor, string $fecha): void
    {
        $fields = ['codigo', 'nombre', 'descripcion', 'fundamento', 'condiciones', 'criterio_dominio', 'valor_consecuencia'];
        if (array_diff(array_keys($n), $fields) || array_diff($fields, array_keys($n))) {
            throw new DomainException('La definición requiere código, nombre, descripción, fundamento, condiciones y consecuencia.');
        }
        foreach (array_diff($fields, ['condiciones']) as $field) {
            if (! is_string($n[$field]) || trim($n[$field]) === '') {
                throw new DomainException('Definición incompleta: '.$field);
            }
        }
        if (! preg_match('/^[A-Z0-9-]{1,50}$/D', $n['codigo']) || mb_strlen($n['nombre']) > 160
            || ! is_array($n['condiciones']) || ! array_is_list($n['condiciones']) || $n['condiciones'] === [] || count($n['condiciones']) > 20) {
            throw new DomainException('Código, nombre o condiciones fuera de contrato.');
        }
        $variables = array_column($t['variables_expertas'], null, 'cod_variable_experta');
        $values = array_column($t['valores_semanticos'], null, 'cod_valor_semantico');
        $criteria = array_column($t['criterios_dominios_resultado'], null, 'cod_criterio_dominio_resultado');
        $domain = $criteria[$n['criterio_dominio']] ?? throw new DomainException('Criterio de otra versión.');
        $consequence = $values[$n['valor_consecuencia']] ?? throw new DomainException('Consecuencia de otra versión.');
        $domains = array_column($t['dominios_valores_expertos'], null, 'cod_dominio_valores');
        $nodes = array_column($t['nodos_semanticos'], null, 'cod_nodo_semantico');
        if ($domain['cod_dominio_valores'] !== $consequence['cod_dominio_valores']
            || $consequence['codigo_valor'] === 'HALLAZGOS_MIXTOS'
            || $consequence['estado'] !== 'ACTIVO' || $consequence['estado_aprobacion'] !== 'APROBADO'
            || $domain['estado'] !== 'ACTIVO' || ($domains[$domain['cod_dominio_valores']]['estado'] ?? null) !== 'ACTIVO'
            || ($nodes[$domain['cod_nodo_criterio']]['tipo_nodo'] ?? null) !== 'CRITERIO'
            || ($nodes[$domain['cod_nodo_criterio']]['estado'] ?? null) !== 'ACTIVO') {
            throw new DomainException('Consecuencia incompatible con el criterio.');
        }
        foreach ($t['reglas_expertas'] as $r) {
            if ($r['codigo_regla'] === $n['codigo']) {
                throw new DomainException('El código ya existe en la sucesora; use un código de regla nuevo.');
            }
        }
        $id = 'TMP-NEW-RULE';
        $t['reglas_expertas'][] = $this->pendiente(['cod_regla_experta' => $id, 'cod_version_modelo' => $version,
            'codigo_regla' => $n['codigo'], 'nombre' => trim($n['nombre']), 'tipo_regla' => 'RESULTADO',
            'descripcion' => trim($n['descripcion']), 'estado' => 'ACTIVO', 'fecha_hora_creacion' => $fecha,
            'cod_usuario_creacion' => $actor, 'observacion' => trim($n['fundamento'])]);
        $seen = [];
        foreach ($n['condiciones'] as $i => $c) {
            if (! is_array($c) || array_diff(array_keys($c), ['variable', 'valor']) || count($c) !== 2
                || ! is_string($c['variable'] ?? null) || ! is_string($c['valor'] ?? null)) {
                throw new DomainException('Condición mal formada.');
            }
            $v = $variables[$c['variable']] ?? throw new DomainException('Variable de otra versión.');
            $value = $values[$c['valor']] ?? throw new DomainException('Valor de otra versión.');
            if ($v['cod_dominio_valores'] !== $value['cod_dominio_valores'] || isset($seen[$c['variable']])
                || $v['estado'] !== 'ACTIVO' || $value['estado'] !== 'ACTIVO' || $value['estado_aprobacion'] !== 'APROBADO'
                || ($domains[$value['cod_dominio_valores']]['estado'] ?? null) !== 'ACTIVO'
                || ($nodes[$v['cod_nodo_semantico']]['tipo_nodo'] ?? null) !== 'VARIABLE'
                || ($nodes[$v['cod_nodo_semantico']]['estado'] ?? null) !== 'ACTIVO') {
                throw new DomainException('Condición incompatible o variable repetida.');
            }
            (new PertenenciaVariableCognitiva)->verificar($t, $c['variable'], $domain['cod_nodo_criterio']);
            $seen[$c['variable']] = true;
            $t['condiciones_regla_experta'][] = ['cod_condicion_regla' => 'TMP-C-'.($i + 1), 'cod_regla_experta' => $id,
                'cod_variable_experta' => $c['variable'], 'cod_valor_semantico' => $c['valor']];
        }
        $t['consecuencias_regla_experta'][] = ['cod_consecuencia_regla' => 'TMP-K-NEW', 'cod_regla_experta' => $id,
            'cod_criterio_dominio_resultado' => $n['criterio_dominio'], 'cod_valor_semantico' => $n['valor_consecuencia']];
    }
}
