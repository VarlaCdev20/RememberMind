<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Simulacion;

use App\Models\ControlCognitivo;
use App\Models\ControlFuncionalIadl;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use MotorExperto\dominio\orion\Entities\Funcionalidad\EvidenciaIadl;
use MotorExperto\dominio\orion\Entities\Hechos\EvidenciaCognitiva;
use MotorExperto\dominio\orion\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\Repositories\Hechos\EvidenciaRepositoryInterface;
use MotorExperto\dominio\orion\Services\Compuertas\EvaluadorConfusoresService;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum as D;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum as H;
use MotorExperto\dominio\orion\ValueObjects\Dcl\CriterioDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\EstadoCriterioDclEnum as C;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoDiferencialEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoIadlEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\OportunidadIadlEnum;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;

/**
 * TÍTULO: Fuente de un caso local ficticio con instantánea tipada.
 * CAPA: Infraestructura de ApoyoDecision.
 * ¿QUÉ HACE? Lee un control y la fuente IADL; separa sus valores de referencias 03A.
 * ¿QUÉ DEVUELVE? Hechos compartidos, revisiones SIM explícitas y entrada C01–C05.
 * RESTRICCIONES: No obtiene A/P ni acredita C03/C04 desde controles o caídas.
 */
final readonly class FuenteCasoSimulado implements EvidenciaRepositoryInterface
{
    public MemoriaTrabajo $memoria;

    public array $entradaDcl;

    public array $procedencias;

    public array $contexto;

    public string $caso;

    public function __construct(ContextoSimulacionLocal $alcance, SolicitudInferenciaDTO $s)
    {
        $alcance->validarVersion($s->versionConocimiento);
        $fisico = $alcance->fisico($s->casoSinteticoId);
        $residente = $alcance->residente($fisico);
        $caso = ContextoSimulacionLocal::CASOS[$residente->numero_documento];
        $this->caso = $caso['caso'];
        $zona = new DateTimeZone(config('app.timezone'));
        $sql = $s->fechaCorte->setTimezone($zona)->format('Y-m-d H:i:s');
        $control = ControlCognitivo::on($alcance->conexion)->where('cod_residente', $fisico)->where('estado', 'VIGENTE')
            ->where('fecha_hora', '<=', $sql)->where('observacion', 'like', '%SIM-SE-20261017-V1:%')
            ->orderByDesc('fecha_hora')->orderByDesc('cod_control_cognitivo')->firstOrFail();
        $iadl = ControlFuncionalIadl::on($alcance->conexion)->where('cod_residente', $fisico)->where('estado', 'REGISTRADO')
            ->where('fecha_hora', '<=', $sql)->where('observacion', 'like', '[SIM-IADL-LOCAL-1:%')
            ->orderByDesc('fecha_hora')->orderByDesc('cod_control_iadl')->firstOrFail();
        if ($alcance->db()->table('controles_funcionales_iadl')->where('cod_control_anterior', $iadl->getKey())->exists()) {
            throw new DomainException('No se acredita una fuente IADL con rectificación posterior.');
        }
        $hechos = $evidencias = $procedencias = $contexto = [];
        $hecho = static function (string $concepto, mixed $valor, string $fuente, string $origen, string $episodio, DateTimeImmutable $fecha) use ($s, &$hechos): HechoExperto {
            $h = new HechoExperto('SIM-H-'.count($hechos), $s->evaluacionId, $s->casoSinteticoId, $episodio, $fuente, $origen,
                $s->versionConocimiento, $concepto, $fecha, $valor);
            $hechos[] = $h;

            return $h;
        };
        $fechaControl = new DateTimeImmutable($control->getRawOriginal('fecha_hora'), $zona);
        $campos = [];
        foreach (PaqueteSimulacionLocal::REGLAS as [$campo, , $dominio]) {
            $campos[$campo] = $dominio;
        }
        foreach ($campos as $campo => $dominio) {
            $valor = $control->getAttribute($campo);
            $origen = 'controles_cognitivos:'.$control->getKey().':'.$campo;
            $episodio = 'CONTROL:'.$control->getKey();
            $hecho('RAW:'.$campo, $valor, 'REFERENCIA_EXTERNA_SINTETICA', $origen, $episodio, $fechaControl);
            $intercambio = is_bool($valor) ? ($valor ? 'SIM_TRUE' : 'SIM_FALSE') : $valor;
            $h = $hecho('SIM-OBS:'.$campo, $intercambio, 'REFERENCIA_EXTERNA_SINTETICA', 'representacion_literal:'.$origen, $episodio, $fechaControl);
            $evidencias[] = new EvidenciaCognitiva($h, D::from($dominio), null,
                $valor === null ? H::DESCONOCIDO : H::HALLAZGO_DESCRIPTIVO, null,
                'Literal copiado del antecedente ficticio; booleanos se intercambian explícitamente como SIM_TRUE/SIM_FALSE. No acredita A/P.');
        }
        // Estas cinco declaraciones están fijadas por el caso 03A, no se calculan desde el control anterior.
        foreach (D::cases() as $i => $dominio) {
            $hallazgo = $caso['dominios'][$i] === 'A' ? H::ALTERACION_OBJETIVA_SIMULADA : H::RENDIMIENTO_ESPERADO_SIMULADO;
            $id = 'SIM-03A-'.$this->caso.'-'.$dominio->value;
            $h = $hecho($dominio->value, $hallazgo->value, 'REFERENCIA_EXTERNA_SINTETICA', $id, $id, $s->fechaCorte);
            $evidencias[] = new EvidenciaCognitiva($h, $dominio, null, $hallazgo, $id,
                'Referencia artificial independiente del caso '.$this->caso.' de la matriz 03A; no es un examen aplicado al residente.',
                ['instrumento' => $id, 'edicion' => 'SIM-03A-v1', 'subprueba' => 'SIM-DOMINIO-COMPLETO',
                    'norma' => 'SIM-SIN-BAREMO-CLINICO', 'idioma' => 'SIM-ES', 'poblacion' => 'SIM-COHORTE', 'revisor' => 'SIM-REVISOR-03A']);
        }
        foreach ($evidencias as $e) {
            $id = 'SIM-PROC-'.$e->hecho->id;
            $procedencias[] = $hecho('VERIFICACION_PROCEDENCIA:'.$e->hecho->id, true, 'REVISION_TECNICA_SINTETICA', $id, $id, $s->fechaCorte);
        }
        foreach (D::cases() as $d) {
            foreach (EvaluadorConfusoresService::CAMPOS_CONTEXTO as $campo) {
                $id = 'SIM-CTX-'.$d->value.'-'.$campo;
                $valor = match ($campo) {
                    'componente_validado', 'condiciones_aplicacion_conocidas', 'confusores_revisados' => true,
                    'invalidacion_necesaria', 'limitacion_no_invalidante', 'basal_disponible' => false,
                };
                $contexto[] = $hecho($d->value.':'.$campo, $valor, 'REVISION_TECNICA_SINTETICA', $id, $id, $s->fechaCorte);
            }
        }
        $pendiente = $caso['c03'] === 'PENDIENTE';
        $esperadoIadl = ['oportunidad' => $pendiente ? 'SIN_OPORTUNIDAD' : 'OBSERVADA',
            'desempeno' => $pendiente ? 'NO_EVALUABLE' : 'INDEPENDIENTE',
            'tipo_ayuda' => $pendiente ? 'NO_APLICA' : 'NINGUNA', 'causa_ayuda' => $pendiente ? 'NO_APLICA' : 'NINGUNA',
            'nivel_previo' => $pendiente ? 'DESCONOCIDO' : 'INDEPENDIENTE'];
        foreach ($esperadoIadl as $campo => $valor) {
            if ($iadl->getRawOriginal($campo) !== $valor) {
                throw new DomainException('Fuente IADL del fixture divergente; no se acreditará independencia: '.$campo);
            }
        }
        $fechaIadl = new DateTimeImmutable($iadl->getRawOriginal('fecha_hora'), $zona);
        foreach ($esperadoIadl as $campo => $valor) {
            $hecho('RAW:IADL:'.$campo, $valor, 'REFERENCIA_EXTERNA_SINTETICA',
                'controles_funcionales_iadl:'.$iadl->getKey().':'.$campo, 'IADL:'.$iadl->getKey(), $fechaIadl);
        }
        $hIadl = $hecho($iadl->actividad, $pendiente ? null : $iadl->desempeno, 'REFERENCIA_IADL_SINTETICA',
            'controles_funcionales_iadl:'.$iadl->getKey(), 'IADL:'.$iadl->getKey(), $fechaIadl);
        $eIadl = new EvidenciaIadl($hIadl, $iadl->actividad, $pendiente ? EstadoIadlEnum::DESCONOCIDO : EstadoIadlEnum::INDEPENDIENTE,
            $pendiente ? OportunidadIadlEnum::SIN_OPORTUNIDAD : OportunidadIadlEnum::REAL_Y_ADECUADA,
            $pendiente ? null : EstadoIadlEnum::INDEPENDIENTE, $pendiente ? 'Sin oportunidad en el escenario ficticio.' : null,
            $pendiente ? null : false, $pendiente ? null : 'SIM-REVISOR-03A');
        $criterios = $soportes = $razones = [];
        $declarados = [C::ACREDITADO, C::from($caso['c02']), C::from($caso['c03']), C::ACREDITADO, C::ACREDITADO];
        foreach (CriterioDclEnum::cases() as $i => $c) {
            $estado = $declarados[$i];
            $criterios[$c->value] = $estado;
            $razones[$c->value] = 'Declaración sintética original '.$this->caso.' / '.$c->value.' de 03A; no se deduce de registros clínicos.';
            $id = 'SIM-03A-'.$this->caso.'-'.$c->value;
            $h = $hecho($c->value, $estado->value, 'REFERENCIA_CRITERIO_SINTETICA', $id, $id, $s->fechaCorte);
            $soportes[$c->value] = $estado === C::PENDIENTE ? [] : [$h];
        }
        $this->memoria = new MemoriaTrabajo($s->evaluacionId, $s->casoSinteticoId, $s->versionConocimiento, $s->fechaCorte, $hechos, $evidencias);
        $this->procedencias = $procedencias;
        $this->contexto = $contexto;
        $this->entradaDcl = ['criterios' => $criterios, 'soportes' => $soportes, 'justificaciones' => $razones,
            'diferencial' => EstadoDiferencialEnum::COMPLETADO_SIN_IMPEDIMENTO, 'independenciaRelativa' => C::from($caso['c03']),
            'evidenciasIadl' => [$eIadl], 'revisorSinteticoId' => $pendiente ? null : 'SIM-REVISOR-03A',
            'justificacionFuncional' => 'Revisión artificial del caso '.$this->caso.'; BADL no acredita IADL y no hay atribución clínica automática.'];
    }

    public function paraEvaluacion(string $evaluacionId, string $residenteId, string $versionConocimiento, DateTimeImmutable $fechaCorte): MemoriaTrabajo
    {
        $m = $this->memoria;
        if ($m->evaluacionId !== $evaluacionId || $m->residenteId !== $residenteId || $m->versionConocimiento !== $versionConocimiento
            || $m->fechaCorte != $fechaCorte) {
            throw new DomainException('La fuente local no pertenece al contexto solicitado.');
        }

        return $m;
    }
}
