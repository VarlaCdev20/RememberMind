<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

use App\Modulos\Cuidados\Dominio\ObservacionCognitiva;
use App\SistemaExperto\Application\DTOs\InformeExpertoDTO;
use App\SistemaExperto\Domain\Entities\Dcl\ResultadoClasificacionDcl;
use App\SistemaExperto\Domain\Entities\Hechos\EvidenciaCognitiva;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\EstadoDominioEnum as Estado;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\HallazgoInstrumentalEnum as Hallazgo;
use App\SistemaExperto\Domain\ValueObjects\Dcl\EstadoCriterioDclEnum as Criterio;
use App\SistemaExperto\Domain\ValueObjects\Dcl\MotivoAbstencionEnum;
use App\SistemaExperto\Domain\ValueObjects\Funcionalidad\EstadoDiferencialEnum as Diferencial;
use DomainException;

/** Presenta el patrón experimental conservado; mantiene su aviso de simulación y no emite diagnósticos. */
final class PresentarPatronExperimental
{
    /** Traduce el perfil y sus requisitos sin cambiar los estados ni las fuentes originales. */
    public function presentar(ResultadoClasificacionDcl $resultado): array
    {
        $perfil = $resultado->perfil;
        if (! str_starts_with($perfil->cognicion->memoria->residenteId, 'SINTETICO-')
            || ! str_starts_with($perfil->cognicion->memoria->evaluacionId, 'SIM-')) {
            throw new DomainException('La presentación Petersen requiere un caso sintético.');
        }
        $funciones = $fundamentos = $faltantes = $referencias = [];
        $alteradas = [];
        foreach ($perfil->cognicion->resultados as $codigo => $r) {
            $nombre = ExplicarResultadoNeurocognitivo::FUNCIONES[$codigo];
            $frase = match ($r->estado) {
                Estado::ALTERACION => 'Las referencias conservadas identifican dificultades en '.mb_strtolower($nombre).'.',
                Estado::RENDIMIENTO_ESPERADO => 'Las referencias conservadas no identifican dificultades en los componentes evaluados de '.mb_strtolower($nombre).'.',
                Estado::CONTRADICTORIO => 'Las fuentes de '.mb_strtolower($nombre).' presentan conclusiones diferentes que requieren revisión.',
                Estado::NO_INTERPRETABLE => 'La evidencia de '.mb_strtolower($nombre).' no es interpretable en las condiciones conservadas.',
                Estado::DESCONOCIDO => 'Falta una evaluación específica interpretable de '.mb_strtolower($nombre).'.',
            };
            $soportes = $razones = $detalleReferencias = $fuentesLegibles = [];
            foreach ($r->evidencias as $e) {
                $clave = $e->hecho->claveOrigen().'|'.$e->hecho->episodioId;
                $referencias[$clave] ??= ['area' => $nombre, 'origen' => $e->hecho->origenId,
                    'episodio' => $e->hecho->episodioId, 'fecha' => $e->hecho->fecha->format('d/m/Y H:i'),
                    'motivo_original' => $e->justificacion];
                $referencia = $this->presentarReferencia($e, $nombre);
                $detalleReferencias[$clave] = $referencia;
                $soportes[$clave] = $referencia['titulo'].' · '.$referencia['fecha_visible'].' · '.$referencia['descripcion'];
                $grupo = $e->hecho->fuenteId.'|'.$e->hecho->episodioId.'|'.$referencia['fecha_iso'];
                $fuentesLegibles[$grupo] ??= ['titulo' => $referencia['fuente'], 'fecha' => $referencia['fecha_visible'],
                    'fecha_iso' => $referencia['fecha_iso'], 'uso' => $referencia['uso'], 'datos' => []];
                $fuentesLegibles[$grupo]['datos'][$clave] = $referencia['descripcion'];
                if (in_array($r->estado, [Estado::NO_INTERPRETABLE, Estado::CONTRADICTORIO], true)) {
                    $razones[$clave] = 'Fuente de '.mb_strtolower($nombre).' del '.$e->hecho->fecha->format('d/m/Y H:i').': '
                        .ExplicarResultadoNeurocognitivo::motivoNatural($e->justificacion);
                }
            }
            $funciones[$codigo] = ['nombre' => $nombre, 'frase' => $frase, 'evidencia' => array_values($soportes),
                'referencias' => array_values($detalleReferencias),
                'fuentes_legibles' => array_values($fuentesLegibles),
                'evaluada' => in_array($r->estado, [Estado::ALTERACION, Estado::RENDIMIENTO_ESPERADO], true)];
            if ($r->estado === Estado::ALTERACION) {
                $alteradas[] = mb_strtolower($nombre);
            }
            $fundamentos[] = ['titulo' => $nombre, 'texto' => $frase, 'fuentes' => array_values($razones ?: $soportes)];
            if (! $funciones[$codigo]['evaluada']) {
                $faltantes[] = ['titulo' => 'Revisar '.mb_strtolower($nombre), 'texto' => trim($frase.' '.implode(' ', $razones))];
            }
        }
        $requisitos = [
            'DCL-C01' => ['Cambio respecto al funcionamiento anterior', 'Falta corroborar la historia del cambio cognitivo y su fuente.'],
            'DCL-C02' => ['Evidencia objetiva y coherencia del perfil', 'Falta acreditar la dificultad objetiva y comprobar su coherencia con las evaluaciones de los dominios.'],
            'DCL-C03' => ['Autonomía cotidiana anterior y actual', 'Falta una revisión funcional de las actividades habituales, la oportunidad de realizarlas y la causa de la ayuda. No se asume independencia.'],
            'DCL-C04' => ['Factores médicos y revisión diferencial', 'Hay una revisión diferencial pendiente, impedida o contradictoria. Debe resolverse antes de proponer un patrón.'],
            'DCL-C05' => ['Condiciones de interpretación', 'Falta comprobar si las fuentes son interpretables en sus condiciones de aplicación.'],
        ];
        $detalleRequisitos = [];
        foreach ($perfil->criterios as $codigo => $estado) {
            $detalleRequisitos[] = ['nombre' => $requisitos[$codigo][0], 'estado' => match ($estado) {
                Criterio::ACREDITADO => 'Acreditado', Criterio::NO_ACREDITADO => 'No acreditado',
                Criterio::PENDIENTE => 'Pendiente', Criterio::CONTRADICTORIO => 'Contradictorio',
            }, 'fuentes' => array_values(array_unique(array_map(
                fn ($h) => 'Referencia de la simulación del '.$h->fecha->format('d/m/Y').' a las '.$h->fecha->format('H:i'), $perfil->soportes[$codigo]))),
                'razon' => match ($estado) {
                    Criterio::ACREDITADO => 'Los soportes conservados acreditan este requisito en la evaluación.',
                    Criterio::NO_ACREDITADO => 'Los soportes conservados indican que este requisito no se cumple.',
                    Criterio::PENDIENTE => $requisitos[$codigo][1],
                    Criterio::CONTRADICTORIO => 'Los soportes presentan discrepancias y requieren revisión.',
                }];
            if ($estado !== Criterio::ACREDITADO) {
                [$titulo, $texto] = $requisitos[$codigo];
                $faltantes[] = ['titulo' => $titulo, 'texto' => $estado === Criterio::NO_ACREDITADO
                    ? ($codigo === 'DCL-C04' ? $texto : 'Los soportes del caso indican que este requisito no se cumple. No se sustituye ese resultado por falta de registros ni se propone un patrón candidato.')
                    : $texto.($estado === Criterio::CONTRADICTORIO ? ' Los soportes de este requisito son discrepantes.' : ''),
                    'tipo' => $estado->value];
            }
        }
        if ($perfil->diferencial !== Diferencial::COMPLETADO_SIN_IMPEDIMENTO && $perfil->criterios['DCL-C04'] === Criterio::ACREDITADO) {
            $faltantes[] = ['titulo' => 'Revisión diferencial', 'texto' => 'La revisión diferencial no permite interpretar todavía el resultado.'];
        }
        $contradiccion = in_array(MotivoAbstencionEnum::ABSTENCION_CONTRADICCION, $resultado->motivos, true);
        $tienePatron = $resultado->patronCandidato !== null;
        $completa = collect($perfil->cognicion->resultados)->every(fn ($r) => in_array($r->estado, [Estado::ALTERACION, Estado::RENDIMIENTO_ESPERADO], true))
            && collect($perfil->criterios)->every(fn ($c) => in_array($c, [Criterio::ACREDITADO, Criterio::NO_ACREDITADO], true))
            && $perfil->diferencial === Diferencial::COMPLETADO_SIN_IMPEDIMENTO;
        if (! $tienePatron && ! $faltantes) {
            $faltantes[] = ['titulo' => 'Coherencia de los requisitos y el perfil',
                'texto' => 'La acreditación de los requisitos no concuerda con el perfil conservado o no permite un patrón clasificable. Revise ambas fuentes antes de concluir.'];
        }
        $lista = implode(', ', $alteradas);
        $conclusion = $tienePatron ? 'Se identifican dificultades en '.$lista.'.'
            : ($completa ? 'La información está disponible, pero los requisitos documentados no permiten proponer un patrón candidato.'
            : ($contradiccion ? 'Los resultados presentan discrepancias que requieren revisión antes de concluir.'
                : 'Todavía no podemos establecer un patrón cognitivo con suficiente fundamento.'));
        $patron = match ($resultado->patronCandidato?->value) {
            'PET-01' => 'amnésico de dominio único', 'PET-02' => 'amnésico multidominio',
            'PET-03' => 'no amnésico de dominio único', 'PET-04' => 'no amnésico multidominio', default => null,
        };
        $autonomia = match ($perfil->funcionalidad->independenciaRelativa) {
            Criterio::ACREDITADO => 'La revisión funcional conservada sustenta una autonomía relativamente conservada en las actividades examinadas.',
            Criterio::NO_ACREDITADO => 'La independencia relativa no está acreditada; no se propone un patrón candidato.',
            Criterio::CONTRADICTORIO => 'Los datos funcionales presentan discrepancias que requieren revisión.',
            Criterio::PENDIENTE => 'No hay información funcional suficiente para determinar si la autonomía cotidiana se mantiene relativamente conservada.',
        };
        $fundamentos[] = ['titulo' => 'Autonomía cotidiana', 'texto' => $autonomia];
        if ($perfil->diferencial === Diferencial::COMPLETADO_SIN_IMPEDIMENTO) {
            $fundamentos[] = ['titulo' => 'Factores que afectan el rendimiento',
                'texto' => 'La revisión diferencial conservada está completada sin impedimento pendiente declarado.'];
        }

        return ['conclusion' => $conclusion, 'resumen' => $patron
            ? 'El perfil conservado corresponde a un patrón candidato de DCL '.$patron.' para revisión profesional.'
            : ($completa ? 'No se asigna un patrón porque los requisitos documentados no se cumplen o no concuerdan con el perfil. No se trata de ausencia de datos.'
                : 'No se asigna un patrón. Complete o revise los puntos señalados; no se sustituye información desconocida por una función conservada.'),
            'funciones' => $funciones, 'fundamentos' => $fundamentos, 'faltantes' => $faltantes, 'requisitos' => $detalleRequisitos,
            'significado' => $patron ? 'El perfil permite proponer un patrón para revisión. No determina una enfermedad, su causa, su gravedad ni su evolución.'
                : ($completa ? 'La información permite revisar el perfil, pero los requisitos documentados no sustentan un patrón candidato. Este resultado conocido no equivale a falta de evaluación ni establece una enfermedad.'
                    : 'Las limitaciones conservadas impiden interpretar un patrón completo. Las observaciones y los resultados parciales no sustituyen las fuentes que faltan.'),
            'autonomia' => ['resumen' => $autonomia, 'actividades' => [], 'limite' => 'La síntesis IADL descriptiva no acredita automáticamente el requisito funcional.'],
            'revision' => $faltantes ? array_column($faltantes, 'titulo') : ['Revisar las fuentes y el alcance del patrón candidato.'],
            'completa' => $completa, 'contradiccion' => $contradiccion, 'experimental' => true,
            'advertencia' => InformeExpertoDTO::ADVERTENCIA_EXPERIMENTAL, 'auditoria' => array_values($referencias),
            'publicable_clinicamente' => false,
            'fecha_corte' => $perfil->cognicion->memoria->fechaCorte->format('d/m/Y').' a las '.$perfil->cognicion->memoria->fechaCorte->format('H:i')];
    }

    /** Expone el dato y la fecha de la instantánea, distinguiendo observación y declaración simulada sin consultar fuentes actuales. */
    private function presentarReferencia(EvidenciaCognitiva $evidencia, string $area): array
    {
        $hecho = $evidencia->hecho;
        $referencia = ['titulo' => 'Evidencia conservada · '.mb_strtolower($area),
            'fuente' => 'Fuente de la valoración de '.mb_strtolower($area),
            'fecha_etiqueta' => 'Fecha de la evidencia', 'fecha' => $hecho->fecha->format('d/m/Y H:i:s').' (UTC'.$hecho->fecha->format('P').')',
            'fecha_visible' => $hecho->fecha->format('d/m/Y').' a las '.$hecho->fecha->format('H:i'),
            'fecha_iso' => $hecho->fecha->format(DATE_ATOM), 'registro' => $hecho->origenId,
            'dato' => match ($hecho->valor) { null => 'Sin dato registrado', true => 'Sí', false => 'No', default => (string) $hecho->valor },
            'uso' => ExplicarResultadoNeurocognitivo::motivoNatural($evidencia->justificacion),
            'edicion' => $evidencia->referenciaSintetica['edicion'] ?? null];
        if (preg_match('/^representacion_literal:controles_cognitivos:([^:]+):([^:]+)$/D', $hecho->origenId, $partes)
            && isset(ObservacionCognitiva::campos()[$partes[2]])) {
            $campo = $partes[2];
            $valor = match ($hecho->valor) { 'SIM_TRUE' => true, 'SIM_FALSE' => false, default => $hecho->valor };
            $etiqueta = in_array($campo, ObservacionCognitiva::SENALES, true) && $valor !== null && ! is_bool($valor)
                ? (string) $valor : ($valor === null ? 'Sin dato registrado' : ObservacionCognitiva::etiqueta($campo, $valor));
            $referencia = array_replace($referencia, ['titulo' => 'Control cognitivo · '.ObservacionCognitiva::campos()[$campo][0],
                'fecha_etiqueta' => 'Fecha del registro', 'registro' => $partes[1],
                'dato' => ObservacionCognitiva::campos()[$campo][0].': '.$etiqueta,
                'fuente' => 'Control cognitivo',
                'uso' => 'Estas observaciones pertenecen al mismo control. Describen lo registrado y, por sí solas, no confirman una alteración cognitiva objetiva.']);
        } elseif ($evidencia->referenciaSintetica !== []) {
            $referencia = array_replace($referencia, ['titulo' => 'Referencia de simulación · '.mb_strtolower($area),
                'fuente' => 'Referencia de simulación',
                'fecha_etiqueta' => 'Fecha de la referencia de simulación',
                'dato' => match ($evidencia->hallazgo) {
                    Hallazgo::ALTERACION_OBJETIVA_SIMULADA => 'Alteración objetiva simulada',
                    Hallazgo::RENDIMIENTO_ESPERADO_SIMULADO => 'Rendimiento esperado simulado',
                    default => $referencia['dato'],
                },
                'uso' => 'Dato definido para la simulación. No corresponde a un examen aplicado al residente.']);
        }
        // Los literales originales siguen disponibles para trazabilidad; la lectura clínica utiliza palabras comprensibles.
        $referencia['descripcion'] = preg_match('/[A-Z]{2,}[_:-][A-Z0-9_:-]+/', $referencia['dato'])
            ? 'El dato guardado necesita revisión para disponer de una descripción comprensible.' : $referencia['dato'];

        return $referencia;
    }
}
