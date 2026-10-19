<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\g09;

use DomainException;
use MotorExperto\adaptadores\entrada\InspectorComponenteInstrumental;

/**
 * Puente de datos con estructura de aplicaciones_instrumento y respuestas_instrumento
 * hacia G09-EXP. NO consulta BDD ni interpreta pruebas reales.
 */
final class AdaptadorInstrumentalG08G09Experimental
{
    public function preparar(string $regla, array $contexto, array $captura): array
    {
        if (($contexto['uso'] ?? null) !== 'TEST_ONLY' || ($contexto['aislado'] ?? false) !== true
            || ($captura['fixture_sintetico'] ?? false) !== true) {
            throw new DomainException('G08/G09: acceso restringido a fixtures sintéticos aislados.');
        }
        if (($contexto['version'] ?? null) !== CatalogoReglasG09Experimental::VERSION) {
            throw new DomainException('Versión de investigación incompatible.');
        }
        $meta = CatalogoReglasG09Experimental::obtener($regla);
        if (($contexto['criterio'] ?? null) !== $meta['criterio']) {
            throw new DomainException('Criterio no compatible con regla.');
        }
        $perfil = PerfilInstrumentalG08Experimental::paraRegla($regla);
        foreach (['aplicacion', 'instrumento', 'preguntas', 'opciones', 'respuestas'] as $campo) {
            if (! isset($captura[$campo]) || ! is_array($captura[$campo])) {
                throw new DomainException('Captura instrumental incompleta: '.$campo);
            }
        }
        $aplicacion = $captura['aplicacion'];
        $instrumento = $captura['instrumento'];
        if (($instrumento['cod_instrumento'] ?? null) !== $perfil['instrumento']
            || ($instrumento['version'] ?? null) !== $perfil['version_instrumento']
            || ($aplicacion['cod_instrumento'] ?? null) !== $perfil['instrumento']
            || ($aplicacion['estado'] ?? null) !== 'COMPLETA'
            || ($aplicacion['cod_residente'] ?? null) !== ($contexto['residente_ficticio'] ?? null)
            || ! isset($aplicacion['cod_aplicacion'])
            || ($aplicacion['cod_aplicacion'] ?? '') === '') {
            return $this->noAdmitida($contexto, 'PERFIL_SINTETICO_O_APLICACION_INCOMPATIBLE');
        }
        // DDL operacional: fecha_hora DATETIME, sin inventar fecha_aplicacion.
        $fecha = $aplicacion['fecha_hora'] ?? null;
        $corte = $contexto['fecha_corte'] ?? null;
        $hora = is_string($fecha) ? \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $fecha) : false;
        $fechaValida = $hora !== false && $hora->format('Y-m-d H:i:s') === $fecha;
        $corteFecha = is_string($corte) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $corte) === 1;
        $corteHora = is_string($corte) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $corte) === 1;
        if (! $fechaValida || (! $corteFecha && ! $corteHora)
            || ($corteFecha && substr($fecha, 0, 10) > $corte)
            || ($corteHora && $fecha > $corte)) {
            return $this->noAdmitida($contexto, 'FECHA_FUERA_DEL_CORTE');
        }
        $inspeccion = (new InspectorComponenteInstrumental)->inspeccionar(
            $aplicacion, $instrumento,
            $captura['preguntas'], $captura['opciones'], $captura['respuestas'], $perfil
        );
        if (! $inspeccion['verificado']) {
            return $this->noAdmitida($contexto, implode(',', $inspeccion['motivos']));
        }
        $valor = $inspeccion['valor'] ?? null;
        if (! in_array($valor, ['DIFICULTAD_SINTETICA', 'SIN_HALLAZGO_SINTETICO'], true)) {
            return $this->noAdmitida($contexto, 'VALOR_SINTETICO_DESCONOCIDO');
        }
        // Una tarea admisible sin dificultad simulada conserva EV-2, sin inferir normalidad.
        $respuestas = $inspeccion['procedencia']['respuestas'];
        sort($respuestas, SORT_STRING);

        $salida = $contexto;
        $salida['evidencias'] = [[
            'id' => 'SIM-EV-'.$aplicacion['cod_aplicacion'].'-'.implode('-', $respuestas),
            'regla' => $regla,
            'residente_ficticio' => $contexto['residente_ficticio'],
            'evaluacion' => $contexto['evaluacion'],
            'version' => CatalogoReglasG09Experimental::VERSION,
            'fecha' => $fecha,
            'fuente' => 'aplicaciones_instrumento:'.$aplicacion['cod_aplicacion'],
            'episodio' => 'SIM-APLICACION-'.$aplicacion['cod_aplicacion'],
            'procedencia_verificada' => true,
            'instrumento_test_only' => true,
            'tarea_atribuible' => true,
            'confusor_invalidante' => array_key_exists('confusor_invalidante', $captura) ? $captura['confusor_invalidante'] : false,
            'dificultad_especifica_test_only' => $valor === 'DIFICULTAD_SINTETICA',
            'atribucion_test_only' => true,
            'contexto_regla_suficiente' => ($contexto['contexto_suficiente'] ?? false) === true,
            'perfil_instrumental' => $perfil['codigo'],
            'version_instrumento' => $perfil['version_instrumento'],
            'respuestas_fuente' => $respuestas,
            'valor_semantico_test_only' => $valor,
            'procedencia_instrumental' => $inspeccion['procedencia'],
            'respuestas_originales' => $captura['respuestas'],
            'estado_operacional' => $aplicacion['estado'],
            'autor_original' => $aplicacion['cod_personal'] ?? null,
        ]];
        $salida['motivos_g08'] = [];

        return $salida;
    }

    private function noAdmitida(array $contexto, string $motivo): array
    {
        $contexto['evidencias'] = [];
        $contexto['senal_contextual'] = false;
        $contexto['motivos_g08'] = [$motivo];

        return $contexto;
    }
}
