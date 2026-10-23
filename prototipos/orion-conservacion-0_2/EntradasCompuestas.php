<?php

declare(strict_types=1);

namespace OrionFase02;

use DomainException;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\instrumentos\ExtractorCapturaInstrumental;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\dominio\petersen\FuentePetersenInstitucional;
use MotorExperto\dominio\petersen\ValoracionOriginalPetersen;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional;
use MotorExperto\soporte\conservacion\InstantaneaInstrumental;
use MotorExperto\soporte\conservacion\InstantaneaPetersen;

/** Adaptador de ensayo; no se registra en Laravel ni cambia contratos del motor. */
final class EntradasCompuestas
{
    public static function proyectar(InstantaneaEvaluacionInstitucional $dto, array $fuenteRelease): array
    {
        $d = $dto->contenido;
        if ($d['instrumental']['contenido']['fuente_conceptual'] !== \MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica::ordenar($fuenteRelease)) {
            throw new DomainException('Fuente conceptual distinta del artefacto: no representable por este contrato.');
        }
        $i = $d['instrumental']['contenido'];
        $p = $d['petersen']['contenido'];
        unset($i['salida'], $i['fuente_conceptual'], $p['salida']);
        foreach ($i['capturas']['capturas'] as &$a) {
            self::retirarExtraccion($a);
        }
        unset($a);
        foreach ($p['valoraciones'] as &$v) {
            foreach ($v['soportes'] as &$s) {
                if (isset($s['datos_originales']['captura_original'])) {
                    self::retirarExtraccion($s['datos_originales']['captura_original']);
                }
            }
            unset($s);
        }
        unset($v);

        return ['instrumental' => $i, 'petersen' => $p];
    }

    private static function retirarExtraccion(array &$a): void
    {
        $a['extraccion_adquirida'] = ! array_key_exists('extraccion', $a) ? 'AUSENTE' : ($a['extraccion'] === null ? 'NULL' : 'CALCULADA');
        if ($a['extraccion_adquirida'] === 'CALCULADA') {
            $calculada = (new ExtractorCapturaInstrumental)->extraer($a['perfil_captura_original'], $a, $a['respuestas_originales'], $a['edad_en_aplicacion']);
            // Las capturas canónicas convierten decimales; el replay final coteja la forma canónica completa.
            if (! is_array($calculada)) {
                throw new DomainException('Extracción no reproducible.');
            }
        }
        unset($a['extraccion']);
    }

    private static function restaurarExtraccion(array &$a, bool $canonica): void
    {
        $estado = $a['extraccion_adquirida'];
        unset($a['extraccion_adquirida']);
        if ($canonica || $estado === 'NULL') {
            $a['extraccion'] = null;
        } elseif ($estado === 'CALCULADA') {
            $a['extraccion'] = (new ExtractorCapturaInstrumental)->extraer($a['perfil_captura_original'], $a, $a['respuestas_originales'], $a['edad_en_aplicacion']);
        } elseif ($estado !== 'AUSENTE') {
            throw new DomainException('Estado de extracción incompatible.');
        }
    }

    public static function reproducir(array $entrada, array $fuenteRelease, string $zip, string $huellaRelease): InstantaneaEvaluacionInstitucional
    {
        $i = $entrada['instrumental'];
        $p = $entrada['petersen'];
        $c = $i['contexto'];
        $contexto = new ContextoEjecucion($c['evaluacion'], $c['residente'], $c['version'], CodecInstantanea::leerFecha('corte', (object) $c['fecha_corte']));
        $k = $i['conocimiento'];
        $paquete = self::paquete($k);
        foreach ($i['capturas']['capturas'] as &$a) {
            self::restaurarExtraccion($a, true);
        }
        unset($a);
        $instrumental = InstantaneaInstrumental::crear($paquete, $contexto, $i['capturas'], $i['vinculos'], $c['usuario_ejecucion'],
            $i['cabecera_conocimiento'], $i['contrato'], $fuenteRelease);
        $valoraciones = [];
        foreach ($p['valoraciones'] as $v) {
            foreach ($v['soportes'] as &$s) {
                if (isset($s['datos_originales']['captura_original'])) {
                    self::restaurarExtraccion($s['datos_originales']['captura_original'], false);
                }
            }
            unset($s);
            $valoraciones[] = new ValoracionOriginalPetersen($v['elemento'], $v['estado'], $v['justificacion'], self::fuente($v['origen']),
                array_map(self::fuente(...), $v['soportes']), $v['comprobaciones'], $v['dominios_revisados']);
        }
        $cp = $p['contexto'];
        $contextoPetersen = new ContextoEjecucion($cp['evaluacion'], $cp['residente'], $cp['version'], CodecInstantanea::leerFecha('corte', (object) $cp['fecha_corte']));
        $petersen = InstantaneaPetersen::crear($contextoPetersen, $valoraciones,
            $p['gobierno'] === null ? null : self::paquete($p['gobierno']['conocimiento']), $p['gobierno']['cabecera'] ?? null,
            $p['contexto']['usuario_ejecucion'], $p['adquisicion'], $p['constancias_documentales'], $p['procedencia_valoraciones'], $p['membresias_soportes'],
            $p['soportes_originales'] ?? null);

        return InstantaneaEvaluacionInstitucional::crear($instrumental, $petersen)->vincularRelease($zip, $huellaRelease);
    }

    private static function paquete(array $k): PaqueteConocimiento
    {
        return new PaqueteConocimiento($k['version'], $k['tablas'], $k['contratos_relaciones'], $k['solo_pruebas_tecnicas'], $k['tipo_regla_resultado'], $k['contratos_extraccion']);
    }

    private static function fuente(array $s): FuentePetersenInstitucional
    {
        return new FuentePetersenInstitucional($s['tabla'], $s['registro'], $s['residente'], $s['autor'], new \DateTimeImmutable($s['fecha']),
            $s['episodio'], $s['tipo'], $s['vigente'], $s['datos_originales']);
    }
}
