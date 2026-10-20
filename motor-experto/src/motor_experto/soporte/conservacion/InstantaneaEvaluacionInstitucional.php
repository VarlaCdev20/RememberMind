<?php

declare(strict_types=1);

namespace MotorExperto\soporte\conservacion;

use DomainException;

/** Preparación compuesta en memoria. La conservación relacional y la retención del release siguen pendientes. */
final readonly class InstantaneaEvaluacionInstitucional
{
    public const CONTRATO = 'EVALUACION-INSTITUCIONAL-1';

    private function __construct(public array $contenido, public string $huella) {}

    public static function crear(InstantaneaInstrumental $instrumental, InstantaneaPetersen $petersen): self
    {
        $d = ['contrato' => self::CONTRATO, 'instrumental' => ['contenido' => $instrumental->contenido, 'huella' => $instrumental->huella],
            'petersen' => ['contenido' => $petersen->contenido, 'huella' => $petersen->huella], 'release' => null,
            'persistido' => false, 'release_retenido' => false, 'ejecutor' => (new ReleaseMotorInstrumental)->identidadEjecutorActual()];

        return self::verificar($d, CodecInstantanea::huella(CapturaInstrumentalCanonica::ordenar($d)));
    }

    /** Verifica el artefacto real antes de vincular su manifiesto; esta operación no lo retiene ni lo almacena. */
    public function vincularRelease(string $ruta, string $huellaManifiesto): self
    {
        $manifiesto = (new ReleaseMotorInstrumental)->verificarEjecutorActual($ruta, $huellaManifiesto);
        if ($this->contenido['ejecutor'] !== ReleaseMotorInstrumental::identidadEjecutor($manifiesto['archivos'])) {
            throw new DomainException('El release difiere del ejecutor capturado al preparar la evaluación.');
        }
        $d = $this->contenido;
        $d['release'] = $manifiesto;

        return self::verificar($d, CodecInstantanea::huella(CapturaInstrumentalCanonica::ordenar($d)));
    }

    public static function verificar(array $d, string $huella): self
    {
        $d = CapturaInstrumentalCanonica::ordenar($d);
        $campos = ['contrato', 'instrumental', 'petersen', 'release', 'persistido', 'release_retenido', 'ejecutor'];
        if (array_diff(array_keys($d), $campos) || array_diff($campos, array_keys($d))
            || ($d['contrato'] ?? null) !== self::CONTRATO || ($d['persistido'] ?? null) !== false || ($d['release_retenido'] ?? null) !== false
            || ! hash_equals(CodecInstantanea::huella($d), $huella)) {
            throw new DomainException('Contrato o huella de evaluación institucional incompatible.');
        }
        if (! is_array($d['ejecutor']) || array_keys($d['ejecutor']) !== ['cantidad_archivos', 'huella_src']
            || ! is_int($d['ejecutor']['cantidad_archivos']) || $d['ejecutor']['cantidad_archivos'] < 1
            || ! is_string($d['ejecutor']['huella_src']) || ! preg_match('/^[a-f0-9]{64}$/D', $d['ejecutor']['huella_src'])) {
            throw new DomainException('La preparación requiere la identidad del ejecutor capturada al crearla.');
        }
        foreach (['instrumental', 'petersen'] as $rama) {
            if (! is_array($d[$rama]) || array_keys($d[$rama]) !== ['contenido', 'huella']) {
                throw new DomainException('La evaluación necesita ambas preparaciones completas.');
            }
        }
        $i = InstantaneaInstrumental::verificar($d['instrumental']['contenido'], $d['instrumental']['huella']);
        $p = InstantaneaPetersen::verificar($d['petersen']['contenido'], $d['petersen']['huella']);
        if ($i->contenido['contrato'] !== 'INSTRUMENTAL-DECLARATIVO-3') {
            throw new DomainException('La preparación compuesta exige el contrato instrumental completo V3.');
        }
        foreach (['residente', 'version', 'usuario_ejecucion', 'fecha_corte'] as $campo) {
            if ($i->contenido['contexto'][$campo] !== $p->contenido['contexto'][$campo]) {
                throw new DomainException('Las ramas pertenecen a otro residente, corte, actor o versión instrumental.');
            }
        }
        $mediciones = [];
        foreach ($i->contenido['capturas']['capturas'] as $captura) {
            if (isset($mediciones[$captura['cod_aplicacion']])) {
                throw new DomainException('La captura instrumental repite una aplicación.');
            }
            $mediciones[$captura['cod_aplicacion']] = $captura;
        }
        foreach ($p->contenido['valoraciones'] as $v) {
            foreach ($v['soportes'] as $s) {
                if ($s['tipo'] !== 'MEDICION') {
                    continue;
                }
                $original = $s['datos_originales']['captura_original'] ?? null;
                $enInstrumental = $mediciones[$s['registro']] ?? null;
                if (! is_array($original) || ! is_array($enInstrumental)) {
                    throw new DomainException('La medición Petersen no está en la misma captura instrumental.');
                }
                $canonica = (new CapturaInstrumentalCanonica)->construir(['disponible' => true,
                    'fecha_corte' => $i->contenido['capturas']['fecha_corte'], 'capturas' => [$original]], true, true)['capturas'][0];
                if ($canonica !== $enInstrumental) {
                    throw new DomainException('Las ramas discrepan en los originales de una misma medición.');
                }
            }
        }
        if ($d['release'] !== null) {
            $m = $d['release'];
            $sello = $m['huella'] ?? null;
            unset($m['huella']);
            if (($m['incluye_datos_clinicos'] ?? null) !== false || ($m['contrato'] ?? null) !== 'ARTEFACTO-MOTOR-2'
                || ! is_string($sello) || ! hash_equals(CodecInstantanea::huella(CapturaInstrumentalCanonica::ordenar($m)), $sello)) {
                throw new DomainException('El manifiesto del release vinculado fue alterado.');
            }
            if ($d['ejecutor'] !== ReleaseMotorInstrumental::identidadEjecutor($m['archivos'])) {
                throw new DomainException('El release no corresponde al ejecutor capturado al preparar la evaluación.');
            }
        }

        return new self($d, $huella);
    }
}
