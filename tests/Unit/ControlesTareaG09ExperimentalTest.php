<?php

declare(strict_types=1);

namespace Tests\Unit;

use DomainException;
use MotorExperto\aplicacion\casos_uso\EjecutarPerfilExperimental;
use MotorExperto\investigacion\dcl\CasoSinteticoCincoDominios as Caso;
use MotorExperto\investigacion\g09\ControlesTareaG09Experimental as Controles;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ControlesTareaG09ExperimentalTest extends TestCase
{
    public static function controlesNoAcreditados(): iterable
    {
        foreach (Controles::CONTROLES as $codigo) {
            yield $codigo.' desconocido' => [$codigo, null, '_DESCONOCIDO'];
            yield $codigo.' negativo' => [$codigo, false, '_NO_ACREDITADO'];
        }
    }

    #[DataProvider('controlesNoAcreditados')]
    public function test_cada_control_desconocido_o_negativo_impide_la_tarea_y_conserva_la_causa(string $codigo, ?bool $valor, string $motivo): void
    {
        $caso = Caso::crear();
        $caso['contexto']['controles_tareas']['SIM-ATE-S01']['atestaciones'][$codigo] = $valor;
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertFalse($r['hechos_instrumentales'][0]['admitida']);
        self::assertContains($codigo.$motivo, $r['hechos_instrumentales'][0]['motivos']);
        self::assertSame($valor, $r['fuentes_examinadas']['g09'][0]['controles_tarea']['atestaciones'][$codigo]);
        self::assertSame('NO_EVALUADA', $r['reglas_examinadas']['g09']['R-ATE-S01-P01']['estado']);
    }

    public function test_no_fabrica_controles_aprobados_si_no_hay_declaracion(): void
    {
        $caso = Caso::crear();
        unset($caso['contexto']['controles_tareas']);
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertCount(0, $r['evidencias_admitidas']['g09']);
        self::assertSame('EV-CA-0', $r['perfil_cinco_dominios']['COG-ATE']['salida']['resultado']['evaluabilidad']);
        self::assertContains('A11_DESCONOCIDO', $r['hechos_instrumentales'][0]['motivos']);
    }

    public function test_confusor_con_efecto_declarado_advierte_o_invalida_sin_causalidad_automatica(): void
    {
        $caso = Caso::crear();
        $confusor = ['codigo' => 'CONF-LEN-01', 'presente' => true, 'efecto' => 'ADVERTENCIA',
            'fuente' => 'SIM-REVISION-AUDICION', 'justificacion' => 'Condición ficticia revisada para esta tarea de laboratorio.'];
        $caso['contexto']['controles_tareas']['SIM-LEN-S01']['confusores'] = [$confusor];
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        $captura = array_values(array_filter($r['fuentes_examinadas']['g09'], static fn ($c) => $c['regla'] === 'R-LEN-S01-P01'))[0];
        self::assertSame('MOD-CL-1', $captura['controles_tarea']['modificador']);
        self::assertStringContainsString('CONF-LEN-01', $r['resumen']['g09']['COG-LEN']['como_se_llego']);
        self::assertSame([$confusor], $r['resumen']['g09']['COG-LEN']['rutas_explicacion'][0]['controles_tarea']['confusores_declarados']);
        self::assertSame('CUMPLE', $r['reglas_examinadas']['g09']['R-LEN-S01-P01']['estado']);
        $caso['contexto']['controles_tareas']['SIM-LEN-S01']['confusores'][0]['efecto'] = 'INVALIDANTE';
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertSame('FUENTE_NO_ADMISIBLE', $r['subcomponentes']['LEN-S01']['motivo_no_emision']);
        self::assertContains('CONF-LEN-01_INVALIDANTE', $r['evidencias_excluidas']['g08'][0]['motivos']);
        $caso['contexto']['controles_tareas']['SIM-LEN-S01']['confusores'][0]['presente'] = null;
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertContains('CONF-LEN-01_PRESENCIA_DESCONOCIDA', $r['evidencias_excluidas']['g08'][0]['motivos']);
    }

    public function test_atestacion_de_texto_se_rechaza(): void
    {
        $control = Caso::crear()['contexto']['controles_tareas']['SIM-ATE-S01'];
        $control['atestaciones']['A01'] = 'true';
        $this->expectException(DomainException::class);
        (new Controles)->revisar('COG-ATE', $control);
    }

    public function test_confusor_de_otro_dominio_se_rechaza(): void
    {
        $control = Caso::crear()['contexto']['controles_tareas']['SIM-ATE-S01'];
        $control['confusores'] = [['codigo' => 'CONF-LEN-01', 'presente' => true, 'efecto' => 'ADVERTENCIA',
            'fuente' => 'SIM-FUENTE', 'justificacion' => 'Declaración ficticia de otro dominio.']];
        $this->expectException(DomainException::class);
        (new Controles)->revisar('COG-ATE', $control);
    }
}
