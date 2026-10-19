<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Simulacion;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\LecturaSimulacionExperta;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\LenguajeResultadosExperto as L;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarPatronExperimental;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\InformeExpertoDTO;
use MotorExperto\dominio\orion\Entities\Dcl\PerfilRequisitosDcl;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\AlmacenInstantaneas;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use DateTimeZone;
use DomainException;
use Illuminate\Support\Facades\Gate;
use ValueError;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Consulta histórica experimental.
 * TÍTULO: Lectura íntegra de instantáneas locales.
 * CAPA: Infraestructura de ApoyoDecision.
 * ¿PARA QUÉ SIRVE? Conecta la pantalla existente con la ejecución realmente conservada.
 * ¿QUÉ INFORMACIÓN RECIBE? Cuenta, residente, evaluación y área autorizados.
 * ¿QUÉ HACE? Reconstruye y comprueba memoria, perfil, DCL y decisiones guardadas.
 * ¿QUÉ DEVUELVE? DTO plano de simulación o ausencia fuera del lote habilitado.
 * ¿QUÉ NO DEBE HACER? No escribe, consulta fuentes actuales ni vuelve a ejecutar el motor.
 */
final class LectorSimulacionLocal implements LecturaSimulacionExperta
{
    public function consultar(User $usuario, Residente $residente, ?string $id, string $criterio, int $pagina = 1): ?array
    {
        if (! config('sistema_experto.simulacion_local.habilitada') || ! app()->environment(['local', 'testing'])
            || ! isset(ContextoSimulacionLocal::CASOS[$residente->numero_documento])) {
            return null;
        }
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        abort_unless(isset(L::CRITERIOS[$criterio]), 422);
        $scope = new ContextoSimulacionLocal(config('database.default'));
        $db = $scope->db();
        $alias = $scope->alias($residente->getKey());
        $version = $db->table('versiones_modelo_experto')->where('codigo_version', ContextoSimulacionLocal::VERSION)
            ->value('cod_version_modelo');
        if ($version === null) {
            return null;
        }
        $scope->validarVersion($version);
        $base = EvaluacionExperta::query()->where('cod_residente', $residente->getKey())->where('cod_version_modelo', $version)
            ->where('origen_activacion', 'PRUEBA_TECNICA')->where('estado_ejecucion', 'PRUEBA_FINALIZADA')
            ->where('version_conservacion', 'ORION-SNAP-2');
        if ($id === null && ! (clone $base)->exists()) {
            return null;
        }
        if ($id !== null && ! (clone $base)->whereKey($id)->exists()) {
            // Una evaluación ajena nunca cae a una lectura sin alcance.
            if ($db->table('evaluaciones_expertas')->where('cod_evaluacion_experta', $id)->where('cod_version_modelo', $version)->exists()) {
                abort(404);
            }

            return null;
        }
        $historial = (clone $base)->orderByDesc('fecha_hora_inicio')->orderByDesc('cod_evaluacion_experta')
            ->with('personalSolicitante')->paginate(8, ['*'], 'historia', $pagina);
        $seleccion = $id === null ? (clone $base)->orderByDesc('fecha_hora_inicio')->orderByDesc('cod_evaluacion_experta')->firstOrFail()
            : (clone $base)->whereKey($id)->firstOrFail();
        $perfil = [];
        foreach (L::CRITERIOS as $codigo => $etiqueta) {
            $perfil[$codigo] = $etiqueta + ['criterio_registrado' => true, 'evaluabilidad_codigo' => null,
                'estado' => 'Simulación sin resultado verificable', 'activacion' => 'Simulación local', 'evaluabilidad' => null,
                'modificador' => null, 'motivo' => null, 'resultado' => null, 'interpretacion' => 'No se publica una salida sin verificar su conservación.',
                'evidencias' => [], 'reglas' => [], 'integridad' => [], 'traza' => null, 'autorizado' => false];
        }
        $sim = ['caso' => ContextoSimulacionLocal::CASOS[$residente->numero_documento]['caso'], 'patron' => null,
            'advertencia' => InformeExpertoDTO::ADVERTENCIA_EXPERIMENTAL,
            'motivos' => [], 'criterios' => [], 'hechos' => [], 'integridad' => [], 'conservacion_completa' => false];
        try {
            $almacen = new AlmacenInstantaneas($scope->conexion, null, new DateTimeZone(config('app.timezone')), $scope);
            $cab = $almacen->cabecera($seleccion->getKey());
            $corte = CodecInstantanea::leerFecha('fecha_corte', $cab);
            $sim['fecha_corte'] = $corte->format('d/m/Y H:i:s.uP');
            $dcl = $almacen->obtenerClasificacion($cab->id_evaluacion_dominio, $alias, $version, $corte, PerfilRequisitosDcl::VERSION_CONTRATO)
                ?? throw new DomainException('Clasificación local no conservada.');
            $vector = $dcl->perfil->cognicion;
            $criterios = $db->table('evaluacion_criterios as c')->join('nodos_semanticos as n', 'n.cod_nodo_semantico', '=', 'c.cod_nodo_criterio')
                ->where('c.cod_evaluacion_experta', $seleccion->getKey())->where('c.tipo_registro', 'DOMINIO')
                ->get(['c.cod_evaluacion_criterio', 'n.codigo_semantico'])->keyBy('codigo_semantico');
            foreach ($vector->resultados as $codigo => $r) {
                $c = $criterios->get('SNAP:DOMINIO:'.$codigo) ?? throw new DomainException('Dominio local sin registro original.');
                $traza = (new TrazaSimulacionLocal($scope))->leer($c->cod_evaluacion_criterio);
                if (in_array($codigo, ['COG-MEM', 'COG-ATE', 'COG-LEN'], true) && $traza === null) {
                    throw new DomainException('Decisiones originales de reglas no conservadas.');
                }
                $nombreEstado = match ($r->estado->value) {
                    'A' => 'Alteración simulada', 'P' => 'Rendimiento esperado simulado', 'U' => 'Información insuficiente',
                    'I' => 'No interpretable', 'X' => 'Contradicción',
                };
                $perfil[$codigo] = array_replace($perfil[$codigo], ['evaluabilidad_codigo' => $r->evaluabilidad->value,
                    'estado' => $nombreEstado, 'evaluabilidad' => 'Integración experimental conservada',
                    'resultado' => ['codigo' => $r->estado->value, 'titulo' => $nombreEstado], 'interpretacion' => $r->justificacion,
                    'reglas' => $traza['reglas'] ?? [], 'traza' => $traza]);
                foreach ($r->evidencias as $e) {
                    $perfil[$codigo]['evidencias'][$e->hecho->id] = ['id' => $e->hecho->id, 'nombre' => $e->hecho->concepto,
                        'fuente' => $e->hecho->fuenteId, 'valor' => $e->hecho->valor,
                        'origen' => $e->hecho->origenId, 'fecha' => $e->hecho->fecha->format('d/m/Y H:i:s.uP'),
                        'referencia' => $e->referenciaSintetica,
                        'papel' => 'Soporte de simulación'];
                }
            }
            foreach ($dcl->perfil->criterios as $codigo => $estado) {
                $sim['criterios'][] = ['codigo' => $codigo, 'estado' => $estado->value,
                    'razon' => $dcl->perfil->justificaciones[$codigo],
                    'soportes' => array_map(fn ($h) => $h->origenId, $dcl->perfil->soportes[$codigo])];
            }
            foreach ($vector->memoria->hechos as $h) {
                if (str_starts_with($h->concepto, 'RAW:')) {
                    $sim['hechos'][] = ['concepto' => $h->concepto, 'valor' => $h->valor, 'origen' => $h->origenId,
                        'fecha' => $h->fecha->format('d/m/Y H:i:s.uP')];
                }
            }
            $sim['patron'] = $dcl->patronCandidato?->value;
            $sim['motivos'] = array_map(fn ($m) => $m->value, $dcl->motivos);
            $sim['explicacion'] = $dcl->justificacion;
            $sim['conservacion_completa'] = true;
            $sim['narrativa'] = (new PresentarPatronExperimental)->presentar($dcl);
        } catch (DomainException|ValueError|\DateInvalidTimeZoneException $e) {
            report($e);
            $sim['integridad'] = ['La instantánea o sus decisiones no superan la verificación de integridad. No se publica el resultado.'];
            // No conserva una conclusión parcial si falla otro dominio, DCL o traza.
            foreach ($perfil as &$area) {
                $area['resultado'] = null;
                $area['evidencias'] = $area['reglas'] = [];
                $area['integridad'] = $sim['integridad'];
                $area['traza'] = null;
            }
            unset($area);
            $sim['patron'] = null;
            $sim['criterios'] = $sim['hechos'] = [];
        }
        $historial->through(fn ($h) => ['id' => $h->getKey(), 'fecha' => $h->fecha_hora_inicio->format('d/m/Y H:i'),
            'version' => ContextoSimulacionLocal::VERSION,
            'criterios' => [['nombre' => 'Alcance', 'estado' => 'Simulación local conservada', 'evaluabilidad' => 'Datos artificiales; sin publicación clínica']]]);

        return ['residente' => ['codigo' => $residente->getKey(), 'nombre' => $residente->nombre_completo, 'edad' => $residente->fecha_nacimiento?->age],
            'evaluacion' => ['id' => $seleccion->getKey(), 'fecha' => $seleccion->fecha_hora_inicio->format('d/m/Y H:i'),
                'version' => ContextoSimulacionLocal::VERSION, 'estado' => $seleccion->estado_ejecucion,
                'solicitante' => $seleccion->personalSolicitante ? trim(implode(' ', [$seleccion->personalSolicitante->nombres,
                    $seleccion->personalSolicitante->apellido_paterno, $seleccion->personalSolicitante->apellido_materno])) : null],
            'perfil' => $perfil, 'detalle' => $perfil[$criterio], 'historial' => $historial, 'prueba_tecnica' => true,
            'simulacion_local' => $sim, 'informe_tecnico' => null, 'conocimiento_documental' => null, 'preparacion_memoria' => null,
            'registros_cognitivos' => []];
    }
}
