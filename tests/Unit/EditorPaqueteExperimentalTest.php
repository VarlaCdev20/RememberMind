<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTimeImmutable;
use DomainException;
use MotorExperto\adaptadores\entrada\AdaptadorHechosCOGMEM;
use MotorExperto\adaptadores\entrada\EjemploTecnicoCOGMEM as Ejemplo;
use MotorExperto\dominio\conocimiento\EditorPaqueteExperimental;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorReglas;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\dominio\objetos_valor\EvidenciaExperta;
use MotorExperto\dominio\objetos_valor\FuenteBrutaCOGMEM;
use PHPUnit\Framework\TestCase;

final class EditorPaqueteExperimentalTest extends TestCase
{
    public function test_reglas_pendientes_se_evaluan_y_rechazadas_se_excluyen(): void
    {
        $t = Ejemplo::tablas();
        $t['reglas_expertas'][0]['estado_revision'] = 'PENDIENTE';
        $t['reglas_expertas'][1]['estado_revision'] = 'RECHAZADA';
        $p = Ejemplo::paquete($t);
        $r = (new EvaluadorReglas)->evaluar($p, new MemoriaTrabajo(Ejemplo::contexto()), 'MEM');
        self::assertCount(1, $r);
        self::assertSame('RULE_A', $r[0]['regla']);
        self::assertSame('NO_CUMPLE', $r[0]['estado']);
    }

    public function test_una_regla_creada_por_datos_cambia_la_ejecucion_sin_editar_el_motor(): void
    {
        $p = Ejemplo::paquete();
        $nueva = ['id' => 'SIM-REGLA', 'codigo' => 'SIM-NUEVA', 'nombre' => 'Prueba', 'descripcion' => 'AND declarativo sintético',
            'fundamento' => 'Prueba de ingeniería', 'condiciones' => [['variable' => 'VAR_INS', 'valor' => 'VAL_A']],
            'criterio_dominio' => 'CR_DOM', 'valor_consecuencia' => 'RES_D'];
        $revision = (new EditorPaqueteExperimental)->revisar($p, 'VER_NUEVA', [$nueva]);
        $m = new MemoriaTrabajo(new ContextoEjecucion('SIM-EVAL', 'SINTETICO-01', 'VER_NUEVA', new DateTimeImmutable('2026-10-17')));
        $fuente = new FuenteBrutaCOGMEM('FU_INST', 'aplicaciones_instrumento', 'COMP_TEST', 'SIM-APP', 'SINTETICO-01',
            new DateTimeImmutable('2026-10-16'), 'literal_A', 'literal_A', 'SIM-AUTOR', true, true, contexto: ['componente_instrumental_verificado' => true]);
        $e = (new AdaptadorHechosCOGMEM)->adaptar($revision, $m, $fuente, 'MAP_INS');
        $m->vincular('MEM', $e->id, 'INSTRUMENTAL');
        $r = (new EvaluadorReglas)->evaluar($revision, $m, 'MEM');
        self::assertSame('CUMPLE', array_values(array_filter($r, fn ($r) => $r['regla'] === 'SIM-REGLA'))[0]['estado']);
        self::assertCount(2, $p->tabla('reglas_expertas'));
        self::assertCount(3, $revision->tabla('reglas_expertas'));
        $retirada = (new EditorPaqueteExperimental)->revisar($revision, 'VER_SUCESORA', retiradas: ['SIM-REGLA']);
        self::assertSame('INACTIVO', $retirada->fila('reglas_expertas', 'SIM-REGLA')['estado']);
        self::assertSame('ACTIVO', $revision->fila('reglas_expertas', 'SIM-REGLA')['estado']);
    }

    public function test_condicion_de_otra_variable_o_valor_no_se_admite(): void
    {
        $this->expectException(DomainException::class);
        (new EditorPaqueteExperimental)->revisar(Ejemplo::paquete(), 'VER_NUEVA', retiradas: ['NO_EXISTE']);
    }

    public function test_sucesora_se_compara_con_los_mismos_casos_positivos_negativos_y_desconocidos(): void
    {
        $p = Ejemplo::paquete();
        $editor = new EditorPaqueteExperimental;
        $nueva = ['id' => 'SIM-REGLA', 'codigo' => 'SIM-NUEVA', 'nombre' => 'Prueba', 'descripcion' => 'AND declarativo sintético',
            'fundamento' => 'Prueba de ingeniería', 'condiciones' => [['variable' => 'VAR_INS', 'valor' => 'VAL_A']],
            'criterio_dominio' => 'CR_DOM', 'valor_consecuencia' => 'RES_D'];
        $sucesora = $editor->revisar($p, 'VER_SUCESORA', [$nueva]);
        $casos = [];
        foreach (['literal_A', 'literal_B', null] as $i => $valor) {
            $casos[] = ['anterior' => $this->memoriaPara($p, 'SIM-CASO-'.$i, $valor),
                'sucesora' => $this->memoriaPara($sucesora, 'SIM-CASO-'.$i, $valor),
                'criterio_anterior' => 'MEM', 'criterio_sucesora' => 'MEM'];
        }
        $r = $editor->compararImpacto($p, $sucesora, $casos);
        self::assertCount(3, $r['casos']);
        self::assertArrayHasKey('SIM-NUEVA', $r['diferencias_conocimiento']);
        foreach (['CUMPLE', 'NO_CUMPLE', 'NO_CUMPLE'] as $i => $esperado) {
            $salida = $r['casos']['SIM-CASO-'.$i];
            self::assertCount(2, $salida['anterior']);
            self::assertCount(3, $salida['sucesora']);
            self::assertSame($esperado, array_values(array_filter($salida['sucesora'], static fn ($r) => $r['regla'] === 'SIM-REGLA'))[0]['estado']);
        }
        self::assertFalse($r['persistido']);
    }

    public function test_comparacion_de_impacto_rechaza_fuentes_alteradas_entre_revisiones(): void
    {
        $p = Ejemplo::paquete();
        $otro = (new EditorPaqueteExperimental)->revisar($p, 'VER_SUCESORA');
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('mismas fuentes originales');
        (new EditorPaqueteExperimental)->compararImpacto($p, $otro, [
            ['anterior' => $this->memoriaPara($p, 'SIM-CASO', 'literal_A'),
                'sucesora' => $this->memoriaPara($otro, 'SIM-CASO', 'literal_B'),
                'criterio_anterior' => 'MEM', 'criterio_sucesora' => 'MEM'],
        ]);
    }

    public function test_comparacion_rechaza_evidencia_manipulada_aunque_el_bruto_original_sea_identico(): void
    {
        $p = Ejemplo::paquete();
        $otro = (new EditorPaqueteExperimental)->revisar($p, 'VER_SUCESORA');
        foreach (['valor', 'admisibilidad'] as $campo) {
            $original = $this->memoriaPara($p, 'SIM-CASO', 'literal_A');
            $correcta = $this->memoriaPara($otro, 'SIM-CASO', 'literal_A');
            $e = $correcta->paraCriterio('MEM')[0];
            $manipulada = new MemoriaTrabajo($correcta->contexto);
            $manipulada->incorporar(new EvidenciaExperta($e->id, $e->ejecucion, $e->fuente, $e->mapeoVariable, $e->mapeoValor,
                $e->variable, $campo === 'valor' ? 'VAL_B' : $e->valor, $e->representacion,
                $campo === 'admisibilidad' ? 'NO_ADMISIBLE' : $e->admisibilidad, $e->motivos));
            $manipulada->vincular('MEM', $e->id, 'INSTRUMENTAL');
            try {
                (new EditorPaqueteExperimental)->compararImpacto($p, $otro, [
                    ['anterior' => $original, 'sucesora' => $manipulada, 'criterio_anterior' => 'MEM', 'criterio_sucesora' => 'MEM'],
                ]);
                self::fail('Se atribuyó al conocimiento una evidencia manipulada.');
            } catch (DomainException $error) {
                self::assertStringContainsString('Evidencia incompatible con su fuente y mapeo', $error->getMessage());
            }
        }
    }

    private function memoriaPara(PaqueteConocimiento $p, string $caso, ?string $valor): MemoriaTrabajo
    {
        $m = new MemoriaTrabajo(new ContextoEjecucion($caso, 'SINTETICO-01', $p->version, new DateTimeImmutable('2026-10-17')));
        $fuente = new FuenteBrutaCOGMEM('FU_INST', 'aplicaciones_instrumento', 'COMP_TEST', 'SIM-APP', 'SINTETICO-01',
            new DateTimeImmutable('2026-10-16'), $valor, $valor, 'SIM-AUTOR', true, true,
            contexto: ['componente_instrumental_verificado' => true]);
        $e = (new AdaptadorHechosCOGMEM)->adaptar($p, $m, $fuente, 'MAP_INS');
        $m->vincular('MEM', $e->id, 'INSTRUMENTAL');

        return $m;
    }

    public function test_comparacion_detecta_cambio_de_tipo_que_modifica_elegibilidad(): void
    {
        $p = Ejemplo::paquete();
        $t = [];
        foreach (PaqueteConocimiento::CLAVES as $tabla => $pk) {
            $t[$tabla] = $p->tabla($tabla);
        }
        $t['reglas_expertas'][0]['tipo_regla'] = 'OTRO_TIPO_TECNICO';
        $otro = new PaqueteConocimiento($p->version, $t, $p->contratosRelaciones, true, $p->tipoReglaResultado, $p->contratosExtraccion);
        self::assertNotSame([], (new EditorPaqueteExperimental)->comparar($p, $otro));
    }

    public function test_comparacion_no_confunde_codigos_con_delimitadores(): void
    {
        $p = Ejemplo::paquete();
        $t = [];
        foreach (PaqueteConocimiento::CLAVES as $tabla => $pk) {
            $t[$tabla] = $p->tabla($tabla);
        }
        foreach ($t['nodos_semanticos'] as &$fila) {
            if ($fila['cod_nodo_semantico'] === 'INS') {
                $fila['codigo_semantico'] = 'A:B';
            }
        }
        unset($fila);
        foreach ($t['valores_semanticos'] as &$fila) {
            if ($fila['cod_valor_semantico'] === 'VAL_A') {
                $fila['codigo_valor'] = 'C';
            }
        }
        unset($fila);
        $a = new PaqueteConocimiento($p->version, $t, $p->contratosRelaciones, true, $p->tipoReglaResultado, $p->contratosExtraccion);
        foreach ($t['nodos_semanticos'] as &$fila) {
            if ($fila['cod_nodo_semantico'] === 'INS') {
                $fila['codigo_semantico'] = 'A';
            }
        }
        unset($fila);
        foreach ($t['valores_semanticos'] as &$fila) {
            if ($fila['cod_valor_semantico'] === 'VAL_A') {
                $fila['codigo_valor'] = 'B:C';
            }
        }
        unset($fila);
        $b = new PaqueteConocimiento($p->version, $t, $p->contratosRelaciones, true, $p->tipoReglaResultado, $p->contratosExtraccion);
        self::assertNotSame([], (new EditorPaqueteExperimental)->comparar($a, $b));
    }
}
