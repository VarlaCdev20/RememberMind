<?php

declare(strict_types=1);

namespace OrionFase02;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\ArchivoConocimientoOrion;
use DomainException;
use MotorExperto\dominio\conocimiento\PerfilConceptualCognitivo;
use MotorExperto\dominio\conocimiento\versiones\PaqueteCognitivoInstitucional;
use MotorExperto\dominio\conocimiento\versiones\PaquetePetersenInstitucional;
use MotorExperto\dominio\instrumentos\ExtractorCapturaInstrumental;
use MotorExperto\dominio\petersen\FuentePetersenInstitucional as F;
use MotorExperto\dominio\petersen\ValoracionOriginalPetersen as V;
use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica as C;
use MotorExperto\soporte\conservacion\CodecInstantanea as Codec;
use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional as Compuesta;
use MotorExperto\soporte\conservacion\InstantaneaInstrumental;
use MotorExperto\soporte\conservacion\InstantaneaPetersen;
use MotorExperto\soporte\conservacion\ReleaseMotorInstrumental;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Support\ConstruyeCasoPetersen;
use ZipArchive;

/** Contrato compuesto sin Laravel ni SQL durante el replay; datos exclusivamente sintéticos. */
final class CasoCanonico
{
    use ConstruyeCasoPetersen;

    public function crear(array $alterados = ['COG-MEM'], bool $sinC03 = false, bool $sinGobierno = false, ?callable $variar = null, string $identidad = 'EVAL-TEST'): Compuesta
    {
        $c = $this->contexto();
        $c = new \MotorExperto\dominio\objetos_valor\ContextoEjecucion($identidad, $c->residente, $c->version, $c->fechaCorte);
        $v = $this->caso($alterados);
        $capturas = [];
        foreach ($v as $i => $valoracion) {
            if (! str_starts_with($valoracion->elemento, 'COG-')) {
                continue;
            }
            $s = $valoracion->soportes[0];
            $a = $s->datos['captura_original'] + ['vinculos_preguntas_actuales' => [], 'preguntas_declarativas_originales' => []];
            $a['nombre_instrumento'] = 'Instrumento sintético '.$valoracion->elemento;
            $a['autor'] = 'Profesional sintético';
            $a['fuente_edad'] = ['tabla' => 'residentes', 'campo' => 'fecha_nacimiento', 'registro' => 'RES-TEST',
                'fecha_nacimiento' => '1956-01-01', 'fecha_aplicacion' => $a['fecha_hora']];
            $a['procedencia'] = ['cod_usuario_registro' => 'USUARIO-TEST', 'registrado_en' => null,
                'cod_documento_clinico' => null, 'localizador_fuente' => '', 'cod_aplicacion_anterior' => null];
            $a['documento_original'] = null;
            $a['instrumento_actual'] += ['cod_instrumento' => $a['cod_instrumento'], 'codigo' => $a['codigo_instrumento'],
                'nombre' => $a['nombre_instrumento'], 'version' => 'TEST-1', 'descripcion' => null, 'puntaje_maximo' => '100.00', 'estado' => 'ACTIVO'];
            $a['aplicacion_actual'] += ['cod_aplicacion' => $a['cod_aplicacion'], 'cod_residente' => 'RES-TEST',
                'cod_personal' => 'AUTOR-TEST', 'cod_instrumento' => $a['cod_instrumento'], 'fecha_hora' => '2026-10-15 09:00:00',
                'puntaje_total' => null, 'puntaje_maximo' => '100.00', 'clasificacion' => null, 'interpretacion' => '',
                'estado' => 'COMPLETA', 'observacion' => null, 'cod_usuario_registro' => 'USUARIO-TEST', 'registrado_en' => null,
                'cod_documento_clinico' => null, 'localizador_fuente' => '', 'cod_aplicacion_anterior' => null];
            foreach ($a['respuestas_originales'] as &$r) {
                $r += ['cod_opcion' => null, 'valor_numero' => null, 'valor_logico' => null, 'valor_texto' => null,
                    'puntaje' => '0.00', 'observacion' => '', 'codigo_pregunta' => $r['cod_pregunta'], 'pregunta' => 'Reactivo de ingeniería',
                    'dominio_original' => $valoracion->elemento, 'estado_pregunta_actual' => 'ACTIVA',
                    'pregunta_actual' => ['cod_pregunta' => $r['cod_pregunta'], 'cod_instrumento' => $a['cod_instrumento'],
                        'codigo' => $r['cod_pregunta'], 'enunciado' => 'Reactivo sintético', 'dominio' => $valoracion->elemento,
                        'tipo_respuesta' => $r['tipo_respuesta'], 'puntaje_maximo' => '100.00', 'orden' => 0, 'estado' => 'ACTIVA'], 'opcion_actual' => null];
            }
            unset($r);
            $sinRespuesta = ['cod_pregunta' => 'SIN-RESPUESTA', 'cod_instrumento' => $a['cod_instrumento'], 'codigo' => 'SIN-R',
                'enunciado' => 'Pregunta sin respuesta', 'dominio' => $valoracion->elemento, 'tipo_respuesta' => 'LOGICO',
                'puntaje_maximo' => null, 'orden' => 99, 'estado' => 'ACTIVA'];
            $a['preguntas_declarativas_originales']['SIN-RESPUESTA'] = $sinRespuesta;
            $a['vinculos_preguntas_actuales']['SIN-RESPUESTA'] = ['selector' => ['cod_pregunta' => 'SIN-RESPUESTA', 'campo' => 'valor_logico'], 'pertenece_al_instrumento' => true,
                'tiene_respuesta' => false, 'pregunta_actual' => $sinRespuesta];
            $a['extraccion'] = (new ExtractorCapturaInstrumental)->extraer($a['perfil_captura_original'], $a, $a['respuestas_originales'], $a['edad_en_aplicacion']);
            $capturas[] = $a;
            $v[$i] = new V($valoracion->elemento, $valoracion->estado, $valoracion->justificacion, $valoracion->origen,
                [new F($s->tabla, $s->registro, $s->residente, $s->autor, $s->fecha, $s->episodio, $s->tipo, $s->vigente,
                    ['dominio' => $valoracion->elemento, 'captura_original' => $a])], $valoracion->comprobaciones);
        }
        if ($sinC03) {
            $v = array_values(array_filter($v, fn ($j) => $j->elemento !== 'C03'));
        }
        $fuente = (new ArchivoConocimientoOrion)->leer();
        $p = (new PaqueteCognitivoInstitucional)->construir((new PerfilConceptualCognitivo)->construir($fuente), $c->version, 'AUTOR-TEST', '2026-10-19 10:00:00');
        $g = $sinGobierno ? null : (new PaquetePetersenInstitucional)->construir('GOBIERNO-TEST', 'AUTOR-TEST', '2026-10-19 10:00:00');
        $cabecera = $sinGobierno ? null : ['cod_version_modelo' => $g->version, 'codigo_version' => PaquetePetersenInstitucional::CODIGO,
            'nombre' => 'Gobierno sintético', 'descripcion' => PaquetePetersenInstitucional::FUNDAMENTO, 'estado' => 'ACTIVO',
            'fecha_hora_creacion' => '2026-10-19T10:00:00.000000-04:00', 'fecha_hora_vigencia' => '2026-10-19T10:00:00.000000-04:00',
            'fecha_hora_retiro' => null, 'motivo_cambio' => 'Ensayo de conservación', 'cod_version_anterior' => null,
            'cod_usuario_creacion' => 'AUTOR-TEST', 'observacion' => PaquetePetersenInstitucional::FUNDAMENTO];
        $procedencia = array_map(fn ($j) => ['cod_valoracion_petersen' => $j->origen->registro, 'cod_residente' => $c->residente,
            'cod_personal' => $j->origen->autor, 'cod_usuario_registro' => 'USUARIO-TEST', 'registrado_en' => '2026-10-19T10:00:00.000000-04:00',
            'fecha_hora' => $j->origen->fecha->format('Y-m-d\TH:i:s.uP'), 'elemento' => $j->elemento, 'tipo_registro' => 'REGISTRO',
            'cod_valoracion_anterior' => null, 'cod_documento_clinico' => null, 'localizador_fuente' => null], $v);
        $membresias = [];
        foreach ($v as $indice => $j) {
            $cantidad = count($j->soportes) + count($j->dominiosRevisados);
            $membresias[] = ['valoracion' => $j->origen->registro,
                'soportes' => $cantidad === 0 ? [] : array_map(fn ($n) => 'SV-'.$indice.'-'.$n, range(1, $cantidad))];
        }
        $adquisicion = ['valoraciones_al_corte' => count($v), 'exclusiones' => [], 'fuentes_originales_mutables' => true, 'conservacion_completa' => false];
        $documentos = [];
        $vinculos = [];
        if ($variar !== null) {
            $variar($capturas, $v, $p, $c, $procedencia, $membresias, $adquisicion, $documentos, $vinculos);
        }
        $i = InstantaneaInstrumental::crear($p, $c, ['disponible' => true, 'fecha_corte' => $c->fechaCorte->format('Y-m-d\TH:i:s.uP'),
            'capturas' => $capturas], $vinculos, 'USUARIO-TEST', ['cod_version_modelo' => $c->version], 'INSTRUMENTAL-DECLARATIVO-3', $fuente);
        $petersen = InstantaneaPetersen::crear($c, $v, $g, $cabecera, 'USUARIO-TEST', $adquisicion, $documentos,
            procedencia: $procedencia, membresias: $membresias);

        return Compuesta::crear($i, $petersen);
    }

}
