<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Flujo;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\InformeExpertoDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso\AnalizarCasoExperimentalUseCase;
use App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso\ClasificarDclUseCase;
use App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso\EjecutarInferenciaUseCase;
use App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso\ExtraerHechosUseCase;
use App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso\GenerarInformeUseCase;
use App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso\PrepararMemoriaTrabajoUseCase;
use MotorExperto\dominio\orion\ValueObjects\Dcl\EstadoCriterioDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoDiferencialEnum;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorRedSemantica;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\RedSemanticaEloquentRepository;
use Illuminate\Support\Facades\DB;
use Tests\Feature\SistemaExperto\Infrastructure\Adapters\AdaptadoresTestCase;

/**
 * TÍTULO: Recorrido experimental con repositorios, mappers y Domain reales.
 * CAPA: Infrastructure (prueba de integración).
 * ¿PARA QUÉ SIRVE? Demuestra adquisición SQL y los cinco casos de uso sin mocks.
 * ¿QUÉ INFORMACIÓN RECIBE? Un control ficticio y una versión documental en base desechable.
 * ¿QUÉ HACE? Ejecuta extracción, memoria, inferencia, DCL e informe sin acreditar ausencias.
 * ¿QUÉ DEVUELVE? Cinco U y abstención explicada con la procedencia consultada.
 * ¿QUÉ NO DEBE HACER? No persiste una instantánea incompleta ni activa el circuito asistencial.
 */
final class AnalizarCasoExperimentalFeatureTest extends AdaptadoresTestCase
{
    /** Una observación existente sin revisiones suficientes no se convierte en A/P ni en PET. */
    public function test_flujo_real_con_fuente_descriptiva_se_abstiene_y_no_escribe(): void
    {
        $this->prepararControl(['memoria_reciente' => null]);
        $red = new RedSemanticaEloquentRepository(new MapeadorRedSemantica, $this->conexionPruebas());
        $coordinador = new AnalizarCasoExperimentalUseCase(
            new ExtraerHechosUseCase($this->repositorioAdaptado()), new PrepararMemoriaTrabajoUseCase,
            new EjecutarInferenciaUseCase($this->reglas(), $red), new ClasificarDclUseCase, new GenerarInformeUseCase,
        );
        $criterios = $razones = [];
        for ($i = 1; $i <= 5; $i++) {
            $criterios['DCL-C0'.$i] = EstadoCriterioDclEnum::PENDIENTE;
            $razones['DCL-C0'.$i] = 'No existe declaración independiente acreditada.';
        }
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        $informe = $coordinador->ejecutar(
            new SolicitudInferenciaDTO('SIM-SOLICITUD', 'SIM-EVAL', 'SINTETICO-RES', self::corte(), 'SIM-V1'),
            ['criterios' => $criterios, 'soportes' => [], 'justificaciones' => $razones,
                'diferencial' => EstadoDiferencialEnum::PENDIENTE, 'independenciaRelativa' => EstadoCriterioDclEnum::PENDIENTE,
                'evidenciasIadl' => [], 'revisorSinteticoId' => null, 'justificacionFuncional' => 'Sin revisión IADL.'],
            [],
        );
        self::assertNull($informe->inferencia->perfilCognitivo->memoria->hechos['SIM-EVID']->valor);
        foreach ($informe->inferencia->dominios as $resultado) {
            self::assertSame('U', $resultado->estado->value);
        }
        self::assertTrue($informe->dcl->abstencion);
        self::assertSame('ABSTENCION_INFORMACION_INSUFICIENTE', $informe->dcl->motivos[0]->value);
        self::assertStringContainsString('registro=SIM-CTRL', $informe->fundamento);
        self::assertStringContainsString('PROCEDENCIA_DESCONOCIDA', $informe->fundamento);
        self::assertSame(InformeExpertoDTO::ADVERTENCIA_EXPERIMENTAL, $informe->advertencia);
        $this->exigirSoloLecturas();
    }
}
