<?php

declare(strict_types=1);

namespace Tests\Unit;

use DomainException;
use MotorExperto\aplicacion\casos_uso\EjecutarPerfilExperimental;
use MotorExperto\investigacion\dcl\CasoSinteticoCincoDominios as Caso;
use MotorExperto\investigacion\g09\EvaluadorG09Experimental;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PerfilExperimentalTest extends TestCase
{
    public static function patrones(): array
    {
        return [['PET-01', ['A', 'P', 'P', 'P', 'P']], ['PET-02', ['A', 'A', 'P', 'P', 'P']],
            ['PET-03', ['P', 'P', 'A', 'P', 'P']], ['PET-04', ['P', 'A', 'A', 'P', 'P']]];
    }

    #[DataProvider('patrones')]
    public function test_fuentes_hasta_patron_y_replay_son_reproducibles(string $patron, array $estados): void
    {
        $caso = Caso::crear(array_combine(['COG-MEM', 'COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'], $estados));
        $antes = $caso;
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertSame($antes, $caso);
        self::assertSame($patron, $r['perfil_experimental']['clasificacion_dcl']['patron_candidato']);
        self::assertCount(5, $r['perfil_experimental']['perfil']);
        self::assertSame($r, (new EjecutarPerfilExperimental)->ejecutar(...$r['instantanea_entrada']));
        self::assertSame('U', $r['perfil_experimental']['perfil']['COG-MEM']['estado_dcl']);
        self::assertFalse($r['persistido']);
    }

    public function test_sin_referencias_no_hay_patron_y_sin_soporte_no_es_normalidad(): void
    {
        $caso = Caso::crear(respuesta: 'SIN_HALLAZGO_SIMULADO');
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertNull($r['perfil_experimental']['clasificacion_dcl']['patron_candidato']);
        self::assertSame('EVALUABLE_SIN_SOPORTE', $r['perfil_experimental']['perfil']['COG-ATE']['salida']['resultado']['estado_tecnico']);
        self::assertSame($caso['contexto']['evaluacion'], $r['perfil_experimental']['perfil']['COG-ATE']['salida']['cotejo_nucleo_d130']['contexto_cotejo']['evaluacion']);
    }

    public function test_contradiccion_y_confusor_llegan_por_el_recorrido_integrado(): void
    {
        $caso = Caso::crear();
        $caso['contexto']['contextos_dominios'] = ['COG-ATE' => ['contradiccion_documentada' => true], 'COG-LEN' => ['confusor_invalidante' => true]];
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertSame('EVIDENCIA_CONTRADICTORIA', $r['perfil_experimental']['perfil']['COG-ATE']['salida']['resultado']['estado_tecnico']);
        self::assertSame('SIN_EVIDENCIA_ADMISIBLE', $r['perfil_experimental']['perfil']['COG-LEN']['salida']['resultado']['estado_tecnico']);
        self::assertSame([], $r['perfil_experimental']['perfil']['COG-ATE']['salida']['resultado']['consecuencias_candidatas']);
    }

    public function test_condicion_desconocida_se_conserva_como_null_en_la_traza(): void
    {
        $caso = Caso::crear();
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        $fuente = $r['perfil_experimental']['perfil']['COG-ATE']['salida']['resultado']['evidencias_admitidas'][0];
        $fuente['dificultad_especifica_test_only'] = null;
        $entrada = $caso['contexto'] + ['criterio' => 'COG-ATE', 'evidencias' => [$fuente]];
        $salida = (new EvaluadorG09Experimental)->evaluar($entrada);
        self::assertNull($salida['traza'][0]['condiciones']['C1']);
        self::assertSame('NO_EVALUABLE', $salida['traza'][0]['estados_condiciones']['C1']);
        self::assertFalse($salida['traza'][0]['cumple']);
    }

    public function test_nulo_no_hereda_contexto_positivo_de_otro_dominio(): void
    {
        $caso = Caso::crear();
        $caso['contexto']['contextos_dominios']['COG-ATE'] = ['contexto_suficiente' => null];
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertSame('EV-CA-1', $r['perfil_experimental']['perfil']['COG-ATE']['salida']['resultado']['evaluabilidad']);
    }

    public function test_no_acepta_identificadores_operativos_ni_tipos_mal_formados(): void
    {
        $caso = Caso::crear();
        $caso['capturas']['aplicaciones_instrumento'][0]['cod_residente'] = 'COD_RES_000001';
        $this->expectException(DomainException::class);
        (new EjecutarPerfilExperimental)->ejecutar(...$caso);
    }

    public function test_no_convierte_un_contenedor_de_capturas_invalido_en_fuentes_ausentes(): void
    {
        foreach ([null, false, 'sin filas', ['cod_instrumento' => 'SIM-TAREA']] as $filas) {
            $caso = Caso::crear();
            $caso['capturas']['opciones_pregunta'] = $filas;
            try {
                (new EjecutarPerfilExperimental)->ejecutar(...$caso);
                self::fail('Se aceptó una colección de capturas inválida.');
            } catch (DomainException $e) {
                self::assertStringContainsString('Colección de capturas', $e->getMessage());
            }
        }
    }

    public function test_no_ignora_contextos_de_memoria_ni_nombres_de_dominio_erroneos(): void
    {
        foreach (['COG-MEM', 'COG-ATT'] as $dominio) {
            $caso = Caso::crear();
            $caso['contexto']['contextos_dominios'][$dominio] = ['confusor_invalidante' => true];
            try {
                (new EjecutarPerfilExperimental)->ejecutar(...$caso);
                self::fail('Se ignoró un contexto no soportado.');
            } catch (DomainException $e) {
                self::assertStringContainsString('Contexto ajeno', $e->getMessage());
            }
        }
    }

    public function test_null_estructural_no_se_convierte_en_contexto_general_positivo(): void
    {
        foreach ([null, ['COG-ATE' => null]] as $contextos) {
            $caso = Caso::crear();
            $caso['contexto']['contextos_dominios'] = $contextos;
            try {
                (new EjecutarPerfilExperimental)->ejecutar(...$caso);
                self::fail('Se aceptó un contexto estructural NULL.');
            } catch (DomainException $e) {
                self::assertStringContainsString('Contexto', $e->getMessage());
            }
        }
    }

    public function test_memoria_utiliza_su_compuerta_declarada_y_no_el_contexto_de_g09(): void
    {
        $caso = Caso::crear();
        $caso['contexto']['contexto_memoria']['confusores_revisados'] = null;
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertNull($r['perfil_experimental']['perfil']['COG-MEM']['salida']['resultado']);
        self::assertSame('EV-CM-1', $r['perfil_experimental']['perfil']['COG-MEM']['salida']['evaluabilidad']['estado']);
        $caso['contexto']['contexto_memoria']['invalidacion_necesaria'] = true;
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertSame('EV-CM-0', $r['perfil_experimental']['perfil']['COG-MEM']['salida']['evaluabilidad']['estado']);
    }

    public function test_evaluador_directo_rechaza_procedencia_mal_tipada_y_fecha_imposible(): void
    {
        $caso = Caso::crear();
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        $fuente = $r['perfil_experimental']['perfil']['COG-ATE']['salida']['resultado']['evidencias_admitidas'][0];
        foreach ([['fecha' => '2026-02-30'], ['episodio' => false], ['fuente' => []]] as $alteracion) {
            $entrada = $caso['contexto'] + ['criterio' => 'COG-ATE', 'evidencias' => [array_replace($fuente, $alteracion)]];
            $salida = (new EvaluadorG09Experimental)->evaluar($entrada);
            self::assertSame([], $salida['consecuencias_candidatas']);
            self::assertCount(1, $salida['excluidas']);
        }
        $entrada['residente_ficticio'] = 'COD_RES_000030';
        $this->expectException(DomainException::class);
        (new EvaluadorG09Experimental)->evaluar($entrada);
    }

    public function test_comparacion_longitudinal_forma_parte_del_contrato_y_del_replay(): void
    {
        $caso = Caso::crear();
        $fuente = ['uso' => 'TEST_ONLY', 'caso' => 'SINTETICO-01', 'componente' => 'ATE-S01', 'instrumento' => 'SIM-TAREA',
            'edicion' => 'SIM-01', 'norma' => 'SIM-NORMA', 'idioma' => 'SIM-ES', 'poblacion' => 'SIM-POBLACION',
            'version_mapeo' => 'SIM-MAP', 'tipo_valor' => 'int', 'valor' => 3, 'fecha' => '2026-10-15T09:00:00-04:00',
            'episodio' => 'SIM-EP-1', 'oportunidad_verificada' => true, 'estado' => 'VIGENTE'];
        $caso['comparaciones'] = [['anterior' => $fuente, 'actual' => array_replace($fuente,
            ['episodio' => 'SIM-EP-2', 'fecha' => '2026-10-16T09:00:00-04:00', 'valor' => 2])]];
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertSame('CAMBIO_SENALADO_NO_CORROBORADO', $r['comparaciones_longitudinales'][0]['estado']);
        self::assertNull($r['comparaciones_longitudinales'][0]['diferencia_interpretada']);
        self::assertSame($r, (new EjecutarPerfilExperimental)->ejecutar(...$r['instantanea_entrada']));
    }
}
