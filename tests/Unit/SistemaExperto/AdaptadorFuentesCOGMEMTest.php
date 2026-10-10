<?php

namespace Tests\Unit\SistemaExperto;

use App\Backend\Modulos\SistemaExperto\Adaptadores\AdaptadorFuentesCOGMEM;
use App\Backend\Modulos\SistemaExperto\Conocimiento\EjemploTecnicoCOGMEM as F;
use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteConocimiento;
use App\Backend\Modulos\SistemaExperto\MemoriaTrabajo\MemoriaTrabajo;
use App\Backend\Modulos\SistemaExperto\Servicios\MotorExperto;
use App\Models\ControlCognitivo;
use App\Models\Personal;
use DomainException;
use PHPUnit\Framework\TestCase;

class AdaptadorFuentesCOGMEMTest extends TestCase
{
    private function booleano(string $variable, bool $valor): PaqueteConocimiento
    {
        $t = F::tablas();
        foreach ($t['mapeos_valores_fuente'] as &$m) {
            if ($m['cod_mapeo_variable_fuente'] === $variable && $m['cod_valor_semantico'] === 'VAL_B') {
                $m['tipo_valor_fuente'] = 'boolean';
                $m['valor_fuente_exacto'] = $valor ? 'true' : 'false';
            }
        }
        unset($m);

        return F::paquete($t);
    }

    public function test_false_mapeado_explicitamente_no_demuestra_preservacion(): void
    {
        $p = $this->booleano('MAP_COR', false);
        $m = new MemoriaTrabajo(F::contexto());
        $e = (new AdaptadorFuentesCOGMEM)->adaptar($p, $m, F::fuente(false, 'repite_preguntas', contexto: ['contexto_mnesico_verificado' => true]), 'MAP_COR');
        $this->assertSame('VAL_B', $e->valor);
        $this->assertFalse($e->utilizable());
        $this->assertSame('NO_ADMISIBLE', $e->admisibilidad);
    }

    public function test_true_corroborativo_requiere_contexto_mnesico(): void
    {
        $p = $this->booleano('MAP_COR', true);
        $m = new MemoriaTrabajo(F::contexto());
        $e = (new AdaptadorFuentesCOGMEM)->adaptar($p, $m, F::fuente(true, 'repite_preguntas'), 'MAP_COR');
        $this->assertFalse($e->utilizable());
        $this->assertContains('CONTEXTO_CORROBORATIVO_NO_VERIFICADO', $e->motivos);
    }

    public function test_olvida_indicaciones_conserva_una_evidencia_multidominio(): void
    {
        $p = $this->booleano('MAP_MULTI', true);
        $m = new MemoriaTrabajo(F::contexto());
        $e = (new AdaptadorFuentesCOGMEM)->adaptar($p, $m, F::fuente(true, 'olvida_indicaciones', contexto: [
            'recepcion_comprension_verificada' => true, 'dificultad_retencion_recuperacion_verificada' => true]), 'MAP_MULTI');
        $this->assertTrue($e->utilizable());
        $this->assertNull($p->fila('variables_expertas', 'VAR_MULTI')['cod_nodo_propietario_primario']);
        $m->vincular('MEM', $e->id, 'MULTIDOMINIO');
        $m->vincular('ATE_TEST', $e->id, 'MULTIDOMINIO');
        $this->assertCount(1, $m->todas());
    }

    public function test_cambio_no_explicitamente_capturado_no_prueba_estabilidad(): void
    {
        $p = $this->booleano('MAP_META', false);
        $m = new MemoriaTrabajo(F::contexto());
        $e = (new AdaptadorFuentesCOGMEM)->adaptar($p, $m, F::fuente(false, 'cambio_cognitivo'), 'MAP_META');
        $this->assertFalse($e->utilizable());
        $this->assertContains('SEMANTICA_DE_CAPTURA_DE_CAMBIO_NO_VERIFICADA', $e->motivos);
    }

    public function test_metaevidencia_longitudinal_no_participa_en_mem_por_defecto(): void
    {
        $p = $this->booleano('MAP_META', true);
        $m = new MemoriaTrabajo(F::contexto());
        $e = (new AdaptadorFuentesCOGMEM)->adaptar($p, $m, F::fuente(true, 'cambio_cognitivo', contexto: ['captura_cambio_explicita_verificada' => true]), 'MAP_META');
        $this->assertTrue($e->utilizable());
        $this->assertFalse($p->variableParticipa('VAR_META', 'MEM'));
        $m->vincular('MEM', $e->id, 'META');
        $this->expectException(DomainException::class);
        (new MotorExperto)->evaluarCOGMEM($p, $m, 'MEM', F::contextoClinico());
    }

    public function test_narrativa_no_emite_hechos_incluso_con_un_literal_que_coincide(): void
    {
        $t = F::tablas();
        $t['mapeos_variables_fuente'][0]['campo_valor'] = 'observacion';
        $p = F::paquete($t);
        $m = new MemoriaTrabajo(F::contexto());
        $e = (new AdaptadorFuentesCOGMEM)->adaptar($p, $m, F::fuente('literal_A', 'observacion'), 'MAP_OBS');
        $this->assertFalse($e->utilizable());
        $this->assertContains('CAMPO_SIN_ATRIBUCION_AUTORIZADA', $e->motivos);
    }

    public function test_lector_eloquent_conserva_bruto_y_rechaza_control_anulado_sin_bdd(): void
    {
        $control = new ControlCognitivo;
        $control->setDateFormat('Y-m-d H:i:s');
        $control->setRawAttributes(['cod_control_cognitivo' => 'CTRL_TEST', 'cod_residente' => 'RES_TEST', 'cod_personal' => 'PER_TEST',
            'fecha_hora' => '2026-10-08 11:00:00', 'memoria_reciente' => 'literal_A', 'repite_preguntas' => 1, 'estado' => 'ANULADO'], true);
        $control->exists = true;
        $personal = new Personal;
        $personal->cod_personal = 'PER_TEST';
        $personal->exists = true;
        $control->setRelation('personal', $personal);
        $m = new MemoriaTrabajo(F::contexto());
        $emitidas = (new AdaptadorFuentesCOGMEM)->extraerControl($control, F::paquete(), $m);
        $obs = array_values(array_filter($emitidas, fn ($e) => $e->variable === 'VAR_OBS'))[0];
        $cor = array_values(array_filter($emitidas, fn ($e) => $e->variable === 'VAR_COR'))[0];
        $this->assertSame('literal_A', $obs->fuente->valorOriginal);
        $this->assertSame(1, $cor->fuente->valorOriginal);
        $this->assertTrue($cor->fuente->valorTipado);
        $this->assertSame('int', $cor->fuente->tipoOriginal);
        $this->assertFalse($obs->utilizable());
        $this->assertContains('ESTADO_OPERACIONAL_NO_UTILIZABLE', $obs->motivos);
    }

    public function test_selector_que_no_se_usa_no_oculta_equivalencia_de_mapeos(): void
    {
        $t = F::tablas();
        $duplicado = $t['mapeos_variables_fuente'][0];
        $duplicado['cod_mapeo_variable_fuente'] = 'MAP_OBS_DUP';
        $duplicado['clave_selector'] = 'IGNORADO';
        $t['mapeos_variables_fuente'][] = $duplicado;
        $this->expectException(DomainException::class);
        F::paquete($t);
    }

    public function test_soportes_con_prerrequisito_comun_y_hecho_independiente_no_se_descartan(): void
    {
        $t = F::tablas();
        $t['condiciones_regla_experta'][1]['cod_valor_semantico'] = 'VAL_A';
        $t['condiciones_regla_experta'][] = ['cod_condicion_regla' => 'COND_OBS', 'cod_regla_experta' => 'RULE_B', 'cod_variable_experta' => 'VAR_OBS', 'cod_valor_semantico' => 'VAL_B'];
        $p = F::paquete($t);
        $m = new MemoriaTrabajo(F::contexto());
        $a = new AdaptadorFuentesCOGMEM;
        $ins = $a->adaptar($p, $m, F::fuente(), 'MAP_INS');
        $m->vincular('MEM', $ins->id, 'INSTRUMENTAL');
        $obs = $a->adaptar($p, $m, F::fuente('literal_B', 'memoria_reciente', 'CTRL_OBS'), 'MAP_OBS');
        $m->vincular('MEM', $obs->id, 'OBSERVACIONAL');
        $r = (new MotorExperto)->evaluarCOGMEM($p, $m, 'MEM', F::contextoClinico());
        $this->assertSame('HALLAZGOS_MIXTOS', $r['resultado']['codigo']);
        $this->assertSame('RES_TEST', $r['traza']['residente']);
        $this->assertSame(F::contexto()->fechaCorte->format(DATE_ATOM), $r['traza']['fecha_corte']);
    }
}
