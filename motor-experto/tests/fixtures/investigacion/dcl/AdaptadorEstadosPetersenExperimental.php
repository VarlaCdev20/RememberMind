<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\dcl;

use DomainException;

/**
 * Puente DOCUMENTAL de prueba, no interpretador neuropsicológico.
 *
 * En G08 todavía no existen baremos admitidos: solo admite interpretaciones
 * de referencia SINTÉTICAS, independientes de D-131 y G09, con procedencia,
 * edición/tarea/norma SIMULADAS declaradas. Nunca deriva A o P de que se
 * cumpla/falle una regla positiva, del score total ni de una nota libre.
 */
final class AdaptadorEstadosPetersenExperimental
{
    public const VERSION = 'PTE-DCL-G08-TEST-01';

    private const TIPOS = [
        'DCL-C01' => 'CAMBIO_LONGITUDINAL_SIMULADO',
        'DCL-C02' => 'OBJETIVIDAD_SIMULADA',
        'DCL-C03' => 'IADL_FUNCIONAL_SIMULADA',
        'DCL-C04' => 'DIFERENCIAL_SIMULADO',
        'DCL-C05' => 'EVALUABILIDAD_SIMULADA',
    ];

    /**
     * $referencias['dominios'][COG-...] = lista de valoraciones INDEPENDIENTES
     * ficticias; $referencias['criterios'][DCL-C..] = acta simulada separada.
     * No se acepta un A/P procedente del resultado descriptivo del motor.
     */
    public function preparar(array $integrado, array $referencias): array
    {
        if (($integrado['uso'] ?? null) !== 'PRUEBA_TECNICA_NO_CLINICA'
            || ($integrado['version_orquestacion'] ?? null) !== OrquestadorCincoDominiosExperimental::VERSION_PERFIL
            || ! is_string($integrado['evaluacion'] ?? null)
            || ! str_starts_with($integrado['evaluacion'], 'SIM-')
            || ! is_string($integrado['residente_ficticio'] ?? null)
            || ! str_starts_with($integrado['residente_ficticio'], 'SINTETICO-')
            || ! is_string($integrado['fecha_corte'] ?? null)
            || ! is_array($integrado['perfil'] ?? null)
            || ! is_array($integrado['versiones'] ?? null)
            || ! is_array($referencias['dominios'] ?? null)
            || ! is_array($referencias['criterios'] ?? null)
            || ($referencias['uso'] ?? null) !== 'REFERENCIAS_SINTETICAS_INDEPENDIENTES'
            || ($referencias['version_puente'] ?? null) !== self::VERSION) {
            throw new DomainException('El puente solo acepta perfil y referencias ficticios versionados.');
        }
        foreach (ClasificadorPetersenExperimental::DOMINIOS as $d) {
            if (! isset($integrado['perfil'][$d])
                || ($integrado['perfil'][$d]['estado_dcl'] ?? null) !== 'U') {
                throw new DomainException('Un resultado descriptivo no puede preasignar A/P de DCL.');
            }
        }
        if (array_diff(array_keys($referencias['dominios']), ClasificadorPetersenExperimental::DOMINIOS)
            || array_diff(array_keys($referencias['criterios']), ClasificadorPetersenExperimental::CRITERIOS)) {
            throw new DomainException('Referencia con dominio o criterio fuera del contrato.');
        }
        $caso = [
            'uso' => 'TEST_ONLY', 'aislado' => true,
            'id_evaluacion' => $integrado['evaluacion'],
            'residente_ficticio' => $integrado['residente_ficticio'],
            'fecha_corte' => $integrado['fecha_corte'],
            'version' => ClasificadorPetersenExperimental::VERSION,
            'criterios' => [], 'dominios' => [],
        ];
        $auditoria = ['dominios' => [], 'criterios' => []];
        foreach (ClasificadorPetersenExperimental::DOMINIOS as $dominio) {
            $entradas = $referencias['dominios'][$dominio] ?? [];
            if (! is_array($entradas) || ! array_is_list($entradas)) {
                throw new DomainException('Las interpretaciones de dominio deben ser una lista explícita.');
            }
            $validas = [];
            $rechazos = [];
            $versionMotor = $dominio === 'COG-MEM'
                ? ($integrado['versiones']['COG-MEM'] ?? null)
                : ($integrado['versiones']['G09'] ?? null);
            $familia = $dominio === 'COG-MEM' ? 'COG-MEM' : 'G09';
            $versionContrato = $integrado['versiones_fuente'][$familia] ?? $versionMotor;
            if (! is_string($versionMotor) || $versionMotor === '') {
                throw new DomainException('Falta versión del motor de dominio.');
            }
            foreach ($entradas as $ref) {
                if (! is_array($ref)) {
                    throw new DomainException('Referencia documental no es objeto.');
                }
                $this->frontera($ref, $integrado, $dominio, $versionContrato);
                $interpretacion = $ref['interpretacion'] ?? null;
                if (! in_array($interpretacion, ['ALTERACION_OBJETIVA_SIMULADA', 'RENDIMIENTO_ESPERADO_SIMULADO'], true)
                    || ($ref['evaluabilidad'] ?? null) !== 'INTERPRETABLE'
                    || ($ref['confusores'] ?? null) !== 'REVISADOS'
                    || ($ref['procedencia'] ?? null) !== 'VERIFICADA'
                    || ($ref['norma_aplicable'] ?? null) !== 'SIMULADA_VERIFICABLE'
                    || ! $this->sim($ref, 'instrumento')
                    || ! $this->sim($ref, 'edicion')
                    || ! $this->sim($ref, 'subprueba')
                    || ! $this->sim($ref, 'norma_id')
                    || ! $this->sim($ref, 'idioma')
                    || ! $this->sim($ref, 'poblacion')
                    || ! $this->sim($ref, 'revisor_independiente')) {
                    $rechazos[] = ['referencia' => $ref['id'], 'motivo' => 'PERFIL_G08_O_INTERPRETACION_NO_ADMISIBLE'];

                    continue;
                }
                $validas[] = $ref;
            }
            $estados = array_values(array_unique(array_map(
                fn (array $r) => $r['interpretacion'] === 'ALTERACION_OBJETIVA_SIMULADA' ? 'A' : 'P',
                $validas
            )));
            if (count($estados) > 1) {
                $caso['dominios'][$dominio] = ['estado' => 'X'];
                $motivo = 'REFERENCIAS_INDEPENDIENTES_CONTRADICTORIAS';
            } elseif ($rechazos) {
                $caso['dominios'][$dominio] = ['estado' => 'I'];
                $motivo = 'EXISTE_INTERPRETACION_NO_ADMISIBLE';
            } elseif (count($estados) === 1) {
                $soportes = array_map(fn ($r) => $this->soporte($r, $integrado), $validas);
                // Seleccionar de forma estable, conservar TODAS en auditoría.
                usort($soportes, fn ($a, $b) => strcmp($a['id'], $b['id']));
                $caso['dominios'][$dominio] = ['estado' => $estados[0], 'soporte' => $soportes[0]];
                $motivo = $estados[0] === 'P'
                    ? 'PRUEBA_POSITIVA_DE_RENDIMIENTO_ESPERADO_EN_SIMULACION'
                    : 'PRUEBA_POSITIVA_DE_ALTERACION_EN_SIMULACION';
            } else {
                $caso['dominios'][$dominio] = ['estado' => 'U'];
                $motivo = 'SIN_REFERENCIA_ESPECIFICA_VALIDA';
            }
            $auditoria['dominios'][$dominio] = [
                'estado' => $caso['dominios'][$dominio]['estado'],
                'razon' => $motivo,
                'referencias_admitidas' => array_values(array_map(fn ($r) => $r['id'], $validas)),
                'rechazadas' => $rechazos,
                'version_motor' => $versionMotor,
                'version_contrato_fuente' => $versionContrato,
            ];
        }

        foreach (ClasificadorPetersenExperimental::CRITERIOS as $criterio) {
            $f = $referencias['criterios'][$criterio] ?? null;
            if ($f === null) {
                $caso['criterios'][$criterio] = ['estado' => 'U'];
                $auditoria['criterios'][$criterio] = ['estado' => 'U', 'razon' => 'SIN_REFERENCIA_GENERAL'];

                continue;
            }
            if (! is_array($f)) {
                throw new DomainException('Referencia general inválida.');
            }
            $this->frontera($f, $integrado, $criterio, ClasificadorPetersenExperimental::VERSION);
            if (($f['tipo_revision'] ?? null) !== self::TIPOS[$criterio]
                || ($f['procedencia'] ?? null) !== 'VERIFICADA'
                || ! $this->sim($f, 'revisor_independiente')
                || ($criterio === 'DCL-C03' && ($f['iadl_observadas'] ?? false) !== true)) {
                $caso['criterios'][$criterio] = ['estado' => 'U'];
                $auditoria['criterios'][$criterio] = ['estado' => 'U', 'razon' => 'REVISION_GENERAL_INCOMPLETA'];

                continue;
            }
            $estado = $f['estado'] ?? null;
            if (! in_array($estado, $criterio === 'DCL-C04'
                ? ['S', 'N', 'U', 'X', 'B'] : ['S', 'N', 'U', 'X'], true)) {
                throw new DomainException('Estado general fuera del contrato '.$criterio);
            }
            $caso['criterios'][$criterio] = ['estado' => $estado];
            if (in_array($estado, ['S', 'N'], true)) {
                $caso['criterios'][$criterio]['soporte'] = $this->soporte($f, $integrado);
            }
            $auditoria['criterios'][$criterio] = [
                'estado' => $estado, 'razon' => 'REVISION_GENERAL_SINTETICA_INDEPENDIENTE',
                'referencia' => $f['id'],
            ];
        }

        return [
            'uso' => 'PRUEBA_TECNICA_NO_CLINICA',
            'version_puente' => self::VERSION,
            'caso' => $caso,
            'auditoria' => $auditoria,
            'advertencia' => 'Las referencias G08 y C01–C05 son totalmente ficticias; A/P solo se usan para demostrar el contrato de traducción. Ningún dato clínico ha sido interpretado.',
        ];
    }

    private function sim(array $ref, string $campo): bool
    {
        return is_string($ref[$campo] ?? null)
            && str_starts_with($ref[$campo], 'SIM-')
            && strlen($ref[$campo]) <= 128;
    }

    private function frontera(array $ref, array $integrado, string $codigo, string $version): void
    {
        if (($ref['uso'] ?? null) !== 'REFERENCIA_INDEPENDIENTE_SINTETICA'
            || ! $this->sim($ref, 'id') || ! $this->sim($ref, 'episodio')
            || ($ref['codigo'] ?? null) !== $codigo
            || ($ref['id_evaluacion'] ?? null) !== $integrado['evaluacion']
            || ($ref['residente_ficticio'] ?? null) !== $integrado['residente_ficticio']
            || ($ref['version_conocimiento'] ?? null) !== $version
            || ($ref['fecha'] ?? null) === null
            || ! is_string($ref['fecha'])
            || preg_match('/^\d{4}-\d{2}-\d{2}$/D', $ref['fecha']) !== 1
            || $ref['fecha'] > $integrado['fecha_corte']
            || ($ref['origen'] ?? null) !== 'REFERENCIA_EXTERNA_SINTETICA_PREDEFINIDA') {
            throw new DomainException('Referencia no independiente, fuera de fecha, versión, sujeto o entorno TEST_ONLY.');
        }
    }

    private function soporte(array $r, array $integrado): array
    {
        return [
            'uso' => 'REFERENCIA_SINTETICA',
            'id' => $r['id'],
            'episodio' => $r['episodio'],
            'fecha' => $r['fecha'],
            'version' => ClasificadorPetersenExperimental::VERSION,
            'id_evaluacion' => $integrado['evaluacion'],
            'residente_ficticio' => $integrado['residente_ficticio'],
        ];
    }
}
