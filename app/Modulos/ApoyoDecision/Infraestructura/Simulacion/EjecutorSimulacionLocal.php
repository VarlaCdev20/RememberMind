<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Simulacion;

use App\Compartido\Infraestructura\Persistencia\GeneradorCodigo;
use App\Models\ControlFuncionalIadl;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\SistemaExperto\Application\DTOs\SolicitudInferenciaDTO;
use App\SistemaExperto\Application\UseCases\AnalizarCasoExperimentalUseCase;
use App\SistemaExperto\Application\UseCases\ClasificarDclUseCase;
use App\SistemaExperto\Application\UseCases\EjecutarInferenciaUseCase;
use App\SistemaExperto\Application\UseCases\ExtraerHechosUseCase;
use App\SistemaExperto\Application\UseCases\GenerarInformeUseCase;
use App\SistemaExperto\Application\UseCases\PrepararMemoriaTrabajoUseCase;
use App\SistemaExperto\Domain\Entities\Dcl\ResultadoClasificacionDcl;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum as D;
use App\SistemaExperto\Infrastructure\Persistence\Conservacion\AlmacenInstantaneas;
use App\SistemaExperto\Infrastructure\Persistence\Mappers\MapeadorRedSemantica;
use App\SistemaExperto\Infrastructure\Persistence\Mappers\MapeadorReglas;
use App\SistemaExperto\Infrastructure\Persistence\Repositories\RedSemanticaEloquentRepository;
use App\SistemaExperto\Infrastructure\Persistence\Repositories\ReglaExpertaEloquentRepository;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Ejecución experimental y conservación.
 * TÍTULO: Coordinación local de la cohorte ficticia.
 * CAPA: Infraestructura de ApoyoDecision.
 * ¿PARA QUÉ SIRVE? Compone los cinco casos de uso existentes con puertos SQL reales.
 * ¿QUÉ INFORMACIÓN RECIBE? Responsables explícitos y fecha de corte original.
 * ¿QUÉ HACE? Valida competencia, admisión y alcance; conserva todo en una transacción.
 * ¿QUÉ DEVUELVE? Identidades, patrones o abstenciones realmente guardados.
 * ¿QUÉ NO DEBE HACER? No se ejecuta desde consultas, eventos clínicos o tareas programadas.
 */
final readonly class EjecutorSimulacionLocal
{
    public function __construct(private ContextoSimulacionLocal $contexto) {}

    /**
     * @param  string  $autorId  Cuenta activa con permiso de auditoría para la carga técnica.
     * @param  string  $enfermeroId  Personal activo y competente, asignado a toda la cohorte.
     * @param  DateTimeImmutable  $corte  Límite temporal explícito, sin sustituir fechas fuente.
     * @return list<array> Resultados confirmados; el reintento idéntico no consume códigos.
     *
     * @throws DomainException Por alcance, fuente divergente o invariantes incompatibles.
     * @throws \RuntimeException Por destino cerrado o fallo SQL; se revierte el lote completo.
     */
    public function ejecutar(string $autorId, string $enfermeroId, DateTimeImmutable $corte): array
    {
        $db = $this->contexto->db();
        if ($this->contexto->conexion !== config('database.default') || $corte > new DateTimeImmutable('now')) {
            throw new DomainException('Destino diferente o fecha de corte futura.');
        }

        return (new GeneradorCodigo)->serializar('versiones_modelo_experto', function () use ($db, $autorId, $enfermeroId, $corte): array {
            $autor = User::on($this->contexto->conexion)->with('roles')->whereKey($autorId)->lockForUpdate()->firstOrFail();
            $enfermero = Personal::on($this->contexto->conexion)->with('usuario.roles')->whereKey($enfermeroId)->lockForUpdate()->firstOrFail();
            if ($enfermero->estado !== 'ACTIVO' || $enfermero->usuario?->estado !== 'ACTIVO'
                || ! $enfermero->usuario->hasRole('ENFERMEROS') || $enfermero->profesion !== 'ENFERMERIA') {
                throw new DomainException('Responsable de Enfermería explícito sin competencia o cuenta activa.');
            }
            $residentes = Residente::on($this->contexto->conexion)->whereIn('numero_documento', array_keys(ContextoSimulacionLocal::CASOS))
                ->orderBy('cod_residente')->lockForUpdate()->get();
            if ($residentes->count() !== 5) {
                throw new DomainException('La cohorte requiere los cinco residentes ficticios originales.');
            }
            foreach ($residentes as $residente) {
                $this->contexto->residente($residente->getKey());
                $admision = $residente->admisiones()->min('fecha_hora_admision');
                if ($admision === null || new DateTimeImmutable($admision, new DateTimeZone(config('app.timezone'))) > $corte->modify('-22 days')) {
                    throw new DomainException('La cohorte requiere admisión previa a todo el período IADL solicitado.');
                }
                if (! $residente->asignacionesJornada()->where('cod_personal', $enfermeroId)->where('estado', 'ACTIVA')
                    ->whereHas('jornada', fn ($q) => $q->whereDate('fecha_jornada', now()->toDateString()))->exists()) {
                    throw new DomainException('La cohorte no pertenece al alcance actual del enfermero explícito.');
                }
            }
            $version = (new PaqueteSimulacionLocal($this->contexto))->preparar($autor);
            $reglas = new ReglaExpertaEloquentRepository(new MapeadorReglas(PaqueteSimulacionLocal::TIPO_REGLA), $this->contexto->conexion, $this->contexto);
            $redes = new RedSemanticaEloquentRepository(new MapeadorRedSemantica, $this->contexto->conexion, $this->contexto);
            $red = $redes->porVersion($version);
            $habilitaciones = array_fill_keys(array_keys($red->relaciones), true);
            $adscripcion = [];
            foreach ($reglas->porVersion($version) as $regla) {
                $adscripcion[$regla->id] = D::from($regla->consecuencia['concepto']);
            }
            $zona = new DateTimeZone(config('app.timezone'));
            $almacen = new AlmacenInstantaneas($this->contexto->conexion, null, $zona, $this->contexto);
            $salidas = [];
            foreach ($residentes as $residente) {
                $this->prepararIadl($residente, $enfermero, $corte);
                $alias = $this->contexto->alias($residente->getKey());
                $id = 'SIM-LOCAL-'.substr($residente->getKey(), 8).'-'.$corte->setTimezone(new DateTimeZone('UTC'))->format('YmdHis');
                $solicitud = new SolicitudInferenciaDTO('SIM-SOL-'.$id, $id, $alias, $corte, $version);
                $fuente = new FuenteCasoSimulado($this->contexto, $solicitud);
                $caso = new AnalizarCasoExperimentalUseCase(new ExtraerHechosUseCase($fuente), new PrepararMemoriaTrabajoUseCase,
                    new EjecutarInferenciaUseCase($reglas, $redes), new ClasificarDclUseCase, new GenerarInformeUseCase);
                $informe = $caso->ejecutar($solicitud, $fuente->entradaDcl, $adscripcion,
                    PaqueteSimulacionLocal::contratoRed($version), $habilitaciones, $fuente->procedencias, $fuente->contexto);
                $cumplidas = array_merge(...array_values($informe->inferencia->reglasPorDominio));
                $dcl = $informe->dcl->resultado;
                $dcl = new ResultadoClasificacionDcl($dcl->perfil, $dcl->patronCandidato, $dcl->motivos, $dcl->justificacion, $cumplidas);
                $previa = $almacen->cabecera($id);
                $almacen->guardar($informe->inferencia->perfilCognitivo);
                $cab = $almacen->cabecera($id);
                $personal = $enfermero->getKey();
                if ($previa !== null && $cab->cod_personal_solicitante !== $personal) {
                    throw new DomainException('El solicitante del reintento no coincide con el original.');
                }
                if ($previa === null) {
                    $db->table('evaluaciones_expertas')->where('cod_evaluacion_experta', $cab->cod_evaluacion_experta)->update([
                        'cod_personal_solicitante' => $personal, 'motivo_activacion' => 'SIMULACIÓN LOCAL '.$fuente->caso.'; antecedentes ficticios y referencias 03A independientes.']);
                }
                $almacen->guardarClasificacion($dcl);
                (new TrazaSimulacionLocal($this->contexto))->guardar($cab->cod_evaluacion_experta, $informe->inferencia);
                if ($previa === null) {
                    activity('Desarrollo')->causedBy($autor)->withProperties(['evaluacion' => $cab->cod_evaluacion_experta,
                        'residente' => $residente->getKey(), 'caso' => $fuente->caso, 'version' => ContextoSimulacionLocal::VERSION])
                        ->log('Ejecución local ficticia conservada; sin diagnóstico ni activación clínica.');
                }
                $salidas[] = ['residente' => $residente->getKey(), 'nombre' => $residente->nombre_completo, 'caso' => $fuente->caso,
                    'evaluacion' => $cab->cod_evaluacion_experta, 'nueva' => $previa === null,
                    'patron' => $dcl->patronCandidato?->value, 'motivos' => array_map(fn ($m) => $m->value, $dcl->motivos)];
            }

            return $salidas;
        });
    }

    /** Observaciones IADL ficticias explícitas; el caso 03A acredita C03 por separado. */
    private function prepararIadl(Residente $residente, Personal $enfermero, DateTimeImmutable $corte): void
    {
        $caso = ContextoSimulacionLocal::CASOS[$residente->numero_documento];
        $pendiente = $caso['c03'] === 'PENDIENTE';
        foreach ([22, 15, 8, 1] as $dias) {
            $fecha = $corte->modify('-'.$dias.' days')->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i:s');
            foreach (ControlFuncionalIadl::ACTIVIDADES as $actividad) {
                $marca = '[SIM-IADL-LOCAL-1:'.$caso['caso'].':'.$actividad.':'.$fecha.']';
                $datos = ['cod_residente' => $residente->getKey(), 'cod_personal' => $enfermero->getKey(),
                    'cod_usuario_registro' => $enfermero->cod_usuario, 'cod_atencion' => null, 'cod_control_anterior' => null,
                    'fecha_hora' => $fecha, 'actividad' => $actividad, 'oportunidad' => $pendiente ? 'SIN_OPORTUNIDAD' : 'OBSERVADA',
                    'desempeno' => $pendiente ? 'NO_EVALUABLE' : 'INDEPENDIENTE', 'tipo_ayuda' => $pendiente ? 'NO_APLICA' : 'NINGUNA',
                    'causa_ayuda' => $pendiente ? 'NO_APLICA' : 'NINGUNA', 'nivel_previo' => $pendiente ? 'DESCONOCIDO' : 'INDEPENDIENTE',
                    'fuente' => 'OBSERVACION_DIRECTA', 'estado' => 'REGISTRADO',
                    'observacion' => $marca.' SIMULACIÓN explícita. No es medicación administrada ni acredita por sí sola C03.'];
                $existente = $this->contexto->db()->table('controles_funcionales_iadl')->where('observacion', $datos['observacion'])->first();
                if ($existente !== null) {
                    if ($this->contexto->db()->table('controles_funcionales_iadl')->where('cod_control_anterior', $existente->cod_control_iadl)->exists()) {
                        throw new DomainException('La fuente IADL del lote tiene una sucesora; requiere un caso explícito nuevo.');
                    }
                    foreach ($datos as $campo => $valor) {
                        if ($valor !== $existente->$campo) {
                            throw new DomainException('Observación IADL del lote alterada: '.$campo);
                        }
                    }

                    continue;
                }
                ControlFuncionalIadl::on($this->contexto->conexion)->create($datos + ['registrado_en' => now()]);
            }
        }
    }
}
