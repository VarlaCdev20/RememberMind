<?php

declare(strict_types=1);

namespace Tests\Support;

use DateTimeImmutable;
use MotorExperto\dominio\instrumentos\NormalizadorHvltBolivia2015 as Hvlt;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\dominio\petersen\EvaluarPetersenInstitucional as E;
use MotorExperto\dominio\petersen\FuentePetersenInstitucional as F;
use MotorExperto\dominio\petersen\ValoracionOriginalPetersen as V;

/** Juicios y brutos de ingeniería, sin reactivos protegidos ni datos reales. */
trait ConstruyeCasoPetersen
{
    private function contexto(): ContextoEjecucion
    {
        return new ContextoEjecucion('EVAL-TEST', 'RES-TEST', 'VERSION-TEST', new DateTimeImmutable('2026-10-19T12:00:00.123456-04:00'));
    }

    private function fuente(string $id, string $tipo, array $datos = [], ?string $episodio = null,
        string $fecha = '2026-10-15T09:00:00.000000-04:00', bool $vigente = true, string $residente = 'RES-TEST'): F
    {
        $tabla = match ($tipo) {
            'MEDICION', 'VALORACION_ORIGINAL' => 'aplicaciones_instrumento', 'IADL' => 'controles_funcionales_iadl',
            'HISTORIA_PREVIA', 'CORROBORACION' => 'controles_cognitivos', default => 'valoraciones_psicologicas',
        };

        return new F($tabla, $id, $residente, 'AUTOR-TEST', new DateTimeImmutable($fecha), $episodio ?? 'EP-'.$id, $tipo, $vigente, $datos);
    }

    private function medicion(string $dominio): F
    {
        $metodo = match ($dominio) {
            'COG-MEM' => Hvlt::METODO, 'COG-ATE' => 'BTA-BO-2015-NL', 'COG-EJE' => 'TMT-B-BO-2015',
            'COG-LEN' => 'BNT-BO-2015-60', 'COG-VIS' => 'ROCF-BO-2015-COPIA-A',
        };
        $meta = (new RegistroMetodosInstrumentales)->metodos()[$metodo];
        $a = ['cod_aplicacion' => 'MED-'.$dominio, 'cod_residente' => 'RES-TEST', 'cod_personal' => 'AUTOR-TEST',
            'cod_instrumento' => 'INST-'.$dominio, 'codigo_instrumento' => 'TEST-'.$dominio, 'version_instrumento' => 'TEST-1',
            'fecha_hora' => '2026-10-15T09:00:00.000000-04:00', 'edad_en_aplicacion' => 70, 'estado_original' => 'COMPLETA',
            'instrumento_actual' => ['tipo' => 'COGNITIVO'], 'motivos' => [], 'respuestas_originales' => []];
        $valores = ['ensayo_1' => 6, 'ensayo_2' => 7, 'ensayo_3' => 8, 'recuerdo_diferido' => 4,
            'demora_minutos' => 22, 'escolaridad_anios' => 4, 'forma_n_aciertos' => 6, 'forma_l_aciertos' => 7,
            'segundos_parte_b' => 180.5, 'errores_observados' => 0, 'aciertos_espontaneos' => 40,
            'aciertos_clave_semantica' => 2, 'puntaje_copia' => 22.5, 'elementos_revisados' => 18];
        $selectores = [];
        foreach ($meta['entradas'] as $entrada => $tipo) {
            $campo = $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero';
            $id = 'P-'.count($selectores);
            $selectores[$entrada] = ['cod_pregunta' => $id, 'campo' => $campo];
            $a['respuestas_originales'][] = ['cod_respuesta' => 'R-'.$id, 'cod_aplicacion' => $a['cod_aplicacion'], 'cod_pregunta' => $id,
                'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO', 'coherente' => true,
                $campo => $tipo === 'LOGICO' ? true : ($valores[$entrada] ?? throw new \LogicException('Falta bruto del caso sintético.'))];
        }
        $a['perfil_captura_original'] = ['id' => 'PERFIL-'.$dominio, 'cod_instrumento' => $a['cod_instrumento'],
            'codigo_instrumento' => $a['codigo_instrumento'], 'version_instrumento' => 'TEST-1', 'metodo' => $metodo,
            'identidad_instrumento' => $meta['identidad'], 'selectores' => $selectores];

        $atencion = 'AT-'.$dominio;
        $a['aplicacion_actual'] = ['cod_atencion' => $atencion];
        $a['atencion_original'] = ['cod_atencion' => $atencion, 'cod_residente' => 'RES-TEST'];

        return $this->fuente($a['cod_aplicacion'], 'MEDICION', ['dominio' => $dominio, 'captura_original' => $a], 'ATENCION:'.$atencion);
    }

    private function caso(array $alterados = ['COG-MEM']): array
    {
        $v = [];
        foreach (E::DOMINIOS as $dominio) {
            $v[] = new V($dominio, in_array($dominio, $alterados, true) ? 'A' : 'P', 'Interpretación original del dominio, caso sintético.',
                $this->fuente('REV-'.$dominio, 'VALORACION_ORIGINAL'), [$this->medicion($dominio)],
                ['alcance_dominio' => true, 'contexto_interpretado' => true]);
        }
        $v[] = new V('C01', 'S', 'Cambio previo y corroboración de episodios originales independientes.', $this->fuente('REV-C01', 'VALORACION_ORIGINAL'),
            [$this->fuente('PREVIO', 'HISTORIA_PREVIA', ['descripcion' => 'Funcionamiento previo documentado.'], fecha: '2026-10-10T09:00:00-04:00'),
                $this->fuente('CORROB', 'CORROBORACION', ['descripcion' => 'Cambio corroborado en otro episodio.'])],
            ['cambio_respecto_previo' => true, 'corroboracion_independiente' => true]);
        $v[] = new V('C03', 'S', 'Valoración original integrada de independencia relativa.', $this->fuente('REV-C03', 'VALORACION_ORIGINAL'),
            [$this->fuente('IADL-1', 'IADL', ['actividad' => 'MEDICACION', 'oportunidad' => 'OBSERVADA', 'desempeno' => 'INDEPENDIENTE',
                'tipo_ayuda' => 'NINGUNA', 'causa_ayuda' => 'NINGUNA', 'nivel_previo' => 'INDEPENDIENTE', 'fuente' => 'OBSERVACION_DIRECTA'])],
            ['revision_integrada' => true, 'oportunidad_adecuada' => true, 'atribucion_revisada' => true]);
        $v[] = new V('C04', 'S', 'Revisión diferencial original sin impedimento actual.', $this->fuente('REV-C04', 'VALORACION_ORIGINAL'),
            [$this->fuente('DIF-1', 'DIFERENCIAL', ['descripcion' => 'Factores pertinentes considerados explícitamente.'])],
            ['revision_explicita' => true, 'factores_pertinentes_revisados' => true, 'impedimento_no_resuelto' => false]);
        $v[] = new V('C05', 'S', 'Condiciones y exclusiones revisadas; perfil suficiente.', $this->fuente('REV-C05', 'VALORACION_ORIGINAL'),
            [$this->fuente('CAL-1', 'CALIDAD', ['descripcion' => 'Suficiencia y condiciones del conjunto revisadas.'])],
            ['suficiencia_conjunto' => true, 'condiciones_revisadas' => true, 'exclusiones_revisadas' => true],
            array_combine(E::DOMINIOS, array_map(fn ($d) => $this->fuente('REV-'.$d, 'VALORACION_ORIGINAL')->identidad(), E::DOMINIOS)));

        return $v;
    }
}
