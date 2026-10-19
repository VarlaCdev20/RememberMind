<?php

declare(strict_types=1);

namespace MotorExperto\dominio\petersen;

use DomainException;
use MotorExperto\dominio\instrumentos\ExtractorCapturaInstrumental;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Clasificación orientativa institucional de Petersen.
 * Aplica el contrato E–H adoptado el 19/10/2026, con acreditaciones originales individuales.
 * Deriva C02 y los cuatro patrones; conserva abstenciones, causas secundarias y soportes.
 * No convierte observaciones, G09, D-131 ni z aislados en estados de dominio o requisitos.
 * No consulta Laravel, SQL ni configuración; no retira las guardas del laboratorio.
 */
final class EvaluarPetersenInstitucional
{
    public const CONTRATO = 'PETERSEN-INSTITUCIONAL-1';

    public const DOMINIOS = ['COG-MEM', 'COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'];

    /** @param list<ValoracionOriginalPetersen> $valoraciones Adquisición original autorizada por el adaptador. */
    public function evaluar(ContextoEjecucion $contexto, array $valoraciones): array
    {
        if (! array_is_list($valoraciones)) {
            throw new DomainException('Petersen requiere una lista de valoraciones originales.');
        }
        $porElemento = $excluidas = $vistas = $originales = [];
        $elementos = array_merge(self::DOMINIOS, ['C01', 'C03', 'C04', 'C05']);
        foreach ($valoraciones as $v) {
            if (! $v instanceof ValoracionOriginalPetersen) {
                throw new DomainException('No se admiten estados ni patrones sueltos como entrada Petersen.');
            }
            foreach ([$v->origen, ...$v->soportes] as $fuente) {
                if ($fuente->residente !== $contexto->residente) {
                    throw new DomainException('La valoración o su soporte pertenecen a otro residente.');
                }
                if (isset($originales[$fuente->identidad()]) && $originales[$fuente->identidad()] !== $fuente->referencia()) {
                    throw new DomainException('Un mismo registro original tiene representaciones incompatibles.');
                }
                $originales[$fuente->identidad()] = $fuente->referencia();
            }
            $clave = $v->origen->identidad().'|'.$v->elemento;
            if (isset($vistas[$clave])) {
                throw new DomainException('No se repite una misma valoración original por elemento.');
            }
            $vistas[$clave] = true;
            $motivos = $this->comprobar($v, $contexto);
            if ($motivos !== []) {
                $excluidas[] = ['elemento' => $v->elemento, 'estado_original' => $v->estado, 'justificacion' => $v->justificacion,
                    'comprobaciones' => $v->comprobaciones, 'origen' => $v->origen->referencia(),
                    'dominios_revisados' => $v->dominiosRevisados,
                    'soportes' => array_map(static fn ($s) => $s->referencia(), $v->soportes), 'motivos' => $motivos];
            } else {
                $porElemento[$v->elemento][] = $v;
            }
        }
        $estados = [];
        $uso = [];
        foreach ($elementos as $elemento) {
            $valores = $porElemento[$elemento] ?? [];
            $estados[$elemento] = ['estado' => $valores === [] ? 'U' : $this->resolver($valores),
                'valoraciones' => array_map(static fn ($v) => ['estado' => $v->estado, 'justificacion' => $v->justificacion,
                    'origen' => $v->origen->referencia(), 'comprobaciones' => $v->comprobaciones,
                    'dominios_revisados' => $v->dominiosRevisados,
                    'soportes' => array_map(static fn ($s) => $s->referencia(), $v->soportes)], $valores),
                'motivos' => $valores === [] ? ['SIN_VALORACION_ORIGINAL_ADMISIBLE'] : []];
            foreach ($valores as $valoracion) {
                // El mismo original no acredita dos requisitos o dos dominios independientes.
                foreach ($valoracion->soportes as $s) {
                    $uso[$s->identidad()][$elemento] = true;
                    if ($s->episodio !== null) {
                        $uso['EPISODIO:'.$s->episodio][$elemento] = true;
                    }
                }
            }
        }
        foreach ($uso as $identidad => $elementosCompartidos) {
            if (count($elementosCompartidos) > 1) {
                foreach (array_keys($elementosCompartidos) as $elemento) {
                    if (! in_array($estados[$elemento]['estado'], ['X', 'N', 'I'], true)) {
                        $estados[$elemento]['estado'] = 'U';
                    }
                    $estados[$elemento]['motivos'][] = 'SOPORTE_COMPARTIDO_NO_INDEPENDIENTE:'.$identidad;
                }
            }
        }
        $dominios = array_intersect_key($estados, array_flip(self::DOMINIOS));
        $a = array_keys(array_filter($dominios, static fn ($d) => $d['estado'] === 'A'));
        $completo = count(array_filter($dominios, static fn ($d) => in_array($d['estado'], ['A', 'P'], true))) === 5;
        if (! $completo && $estados['C05']['estado'] === 'S') {
            $estados['C05']['estado'] = 'U';
            $estados['C05']['motivos'][] = 'SIN_CINCO_DOMINIOS_INTERPRETABLES';
        }
        foreach ($porElemento['C05'] ?? [] as $revision) {
            if ($revision->estado !== 'S') {
                continue;
            }
            foreach (self::DOMINIOS as $dominio) {
                $utilizados = $porElemento[$dominio] ?? [];
                $originalesRevisados = array_filter($utilizados, static fn ($v) => $v->origen->identidad() === ($revision->dominiosRevisados[$dominio] ?? null)
                    && $v->origen->fecha <= $revision->origen->fecha);
                // C05 revisa un juicio concreto por dominio: una coincidencia no acredita un conjunto ampliado.
                if ((count($utilizados) !== 1 || count($originalesRevisados) !== 1) && ! in_array($estados['C05']['estado'], ['X', 'N'], true)) {
                    $estados['C05']['estado'] = 'U';
                    $estados['C05']['motivos'][] = 'C05_NO_IDENTIFICA_VALORACION_REVISADA:'.$dominio;
                }
            }
        }
        $c02 = in_array('X', array_column($dominios, 'estado'), true) ? 'X' : ($a !== [] ? 'S' : ($completo ? 'N' : 'U'));
        $estados['C02'] = ['estado' => $c02, 'derivado_de' => $a !== [] ? $a : self::DOMINIOS, 'valoraciones' => [],
            'motivos' => $c02 === 'U' ? ['SIN_PERFIL_OBJETIVO_SUFICIENTE'] : []];
        $motivos = [];
        if (in_array('X', array_column($estados, 'estado'), true)) {
            $motivos[] = 'ABSTENCION_CONTRADICCION';
        }
        $impedimento = false;
        foreach ($porElemento['C04'] ?? [] as $v) {
            $impedimento = $impedimento || ($v->comprobaciones['impedimento_no_resuelto'] ?? null) === true;
        }
        if ($impedimento || $estados['C04']['estado'] === 'N') {
            $motivos[] = 'ABSTENCION_EVALUACION_DIFERENCIAL';
        }
        if (array_intersect(['U', 'I'], array_column($estados, 'estado')) || ! $completo) {
            $motivos[] = 'ABSTENCION_INFORMACION_INSUFICIENTE';
        }
        if (in_array('N', array_column($estados, 'estado'), true)) {
            $motivos[] = 'SIN_PATRON_CLASIFICABLE_POR_REQUISITO';
        }
        $patron = null;
        if ($motivos === []) {
            $memoria = $dominios['COG-MEM']['estado'] === 'A';
            $patron = $memoria ? (count($a) === 1 ? 'PET-01' : 'PET-02') : (count($a) === 1 ? 'PET-03' : 'PET-04');
        }

        return ['contrato' => self::CONTRATO, 'evaluacion' => $contexto->evaluacion, 'residente' => $contexto->residente,
            'version' => $contexto->version, 'fecha_corte' => $contexto->fechaCorte->format('Y-m-d\TH:i:s.uP'),
            'patron' => $patron, 'estado' => $patron === null ? $motivos[0] : 'PATRON_ORIENTATIVO_CON_SOPORTE',
            'impedimentos' => $motivos, 'dominios' => $dominios,
            'requisitos' => array_intersect_key($estados, array_flip(['C01', 'C02', 'C03', 'C04', 'C05'])),
            'valoraciones_excluidas' => $excluidas, 'revision_profesional' => 'PENDIENTE', 'persistido' => false];
    }

    private function resolver(array $valoraciones): string
    {
        $estados = array_unique(array_column($valoraciones, 'estado'));

        return count($estados) === 1 ? reset($estados) : 'X';
    }

    private function comprobar(ValoracionOriginalPetersen $v, ContextoEjecucion $c): array
    {
        $motivos = [];
        foreach ([$v->origen, ...$v->soportes] as $s) {
            if (! $s->admisible($c->residente, $c->fechaCorte)) {
                $motivos[] = 'FUENTE_FUERA_DE_CORTE_O_NO_VIGENTE:'.$s->identidad();
            }
        }
        if ($v->soportes === []) {
            $motivos[] = 'SIN_SOPORTES_ORIGINALES';
        }
        $dominio = in_array($v->elemento, self::DOMINIOS, true);
        if ($v->estado === 'N' || $v->estado === 'I') {
            return $motivos; // La negación o inaplicabilidad explícita no exige una acreditación positiva.
        }
        $requeridas = $dominio ? ['alcance_dominio', 'contexto_interpretado'] : match ($v->elemento) {
            'C01' => ['cambio_respecto_previo', 'corroboracion_independiente'],
            'C03' => ['revision_integrada', 'oportunidad_adecuada', 'atribucion_revisada'],
            'C04' => ['revision_explicita', 'factores_pertinentes_revisados'],
            'C05' => ['suficiencia_conjunto', 'condiciones_revisadas', 'exclusiones_revisadas'],
        };
        foreach ($requeridas as $clave) {
            if (($v->comprobaciones[$clave] ?? null) !== true) {
                $motivos[] = 'CONDICION_ORIGINAL_NO_ACREDITADA:'.$clave;
            }
        }
        $tipos = array_column($v->soportes, 'tipo');
        if ($dominio) {
            $medidas = array_filter($v->soportes, fn ($s) => $s->tipo === 'MEDICION' && ($s->datos['dominio'] ?? null) === $v->elemento);
            if ($medidas === []) {
                $motivos[] = 'SIN_MEDICION_OBJETIVA_DEL_DOMINIO';
            }
            foreach ($medidas as $s) {
                $captura = $s->datos['captura_original'] ?? null;
                if (! is_array($captura) || $s->tabla !== 'aplicaciones_instrumento'
                    || ($captura['cod_aplicacion'] ?? null) !== $s->registro || ($captura['cod_residente'] ?? null) !== $s->residente
                    || ($captura['cod_personal'] ?? null) !== $s->autor || ($captura['fecha_hora'] ?? null) !== $s->fecha->format('Y-m-d\TH:i:s.uP')
                    || ($captura['estado_original'] ?? null) !== 'COMPLETA' || ($captura['motivos'] ?? null) !== []
                    || ! is_array($captura['perfil_captura_original'] ?? null) || ($captura['instrumento_actual']['tipo'] ?? null) === 'OBSERVACIONAL') {
                    $motivos[] = 'MEDICION_SIN_CAPTURA_ORIGINAL_COMPATIBLE';

                    continue;
                }
                $e = (new ExtractorCapturaInstrumental)->extraer($captura['perfil_captura_original'], $captura,
                    $captura['respuestas_originales'], $captura['edad_en_aplicacion']);
                $atencion = $captura['aplicacion_actual']['cod_atencion'] ?? null;
                if (($atencion === null && $s->episodio !== null)
                    || ($atencion !== null && (($captura['atencion_original']['cod_atencion'] ?? null) !== $atencion
                        || ($captura['atencion_original']['cod_residente'] ?? null) !== $s->residente || $s->episodio !== 'ATENCION:'.$atencion))) {
                    $motivos[] = 'EPISODIO_INCOMPATIBLE_CON_LA_CAPTURA';
                }
                if (($e['aplicabilidad_geriatrica']['alcance_etario_compatible'] ?? null) !== true) {
                    $motivos[] = 'APLICACION_FUERA_DEL_ALCANCE_60_MAS';
                }
                $normalizadas = [];
                foreach ($e['normalizacion']['componentes'] as $componente => $resultado) {
                    if (str_starts_with($componente, substr($v->elemento, 4).'-')) {
                        foreach ($resultado['medidas'] ?? [$resultado] as $medida) {
                            if (($medida['estado'] ?? null) === 'NORMALIZADO') {
                                $normalizadas[] = $medida;
                            }
                        }
                    }
                }
                if ($normalizadas === []) {
                    $motivos[] = 'MEDICION_NO_INTERPRETABLE';
                }
            }
        } elseif ($v->elemento === 'C01') {
            $previas = array_filter($v->soportes, fn ($s) => $s->tipo === 'HISTORIA_PREVIA');
            $corroboraciones = array_filter($v->soportes, fn ($s) => $s->tipo === 'CORROBORACION');
            $independiente = false;
            foreach ($previas as $previa) {
                foreach ($corroboraciones as $otra) {
                    $independiente = $independiente || ($previa->episodio !== null && $otra->episodio !== null
                        && $previa->episodio !== $otra->episodio && $previa->identidad() !== $otra->identidad()
                        && trim($previa->datos['descripcion'] ?? '') !== '' && trim($otra->datos['descripcion'] ?? '') !== ''
                        && $previa->fecha < $otra->fecha);
                }
            }
            if (! $independiente) {
                $motivos[] = 'SIN_CAMBIO_CORROBORADO_INDEPENDIENTEMENTE';
            }
        } elseif ($v->elemento === 'C03') {
            $iadl = array_filter($v->soportes, fn ($s) => $s->tipo === 'IADL');
            if ($iadl === []) {
                $motivos[] = 'SIN_IADL_ORIGINAL';
            }
            foreach ($iadl as $s) {
                if (! in_array($s->datos['oportunidad'] ?? null, ['OBSERVADA', 'REFERIDA'], true)
                    || ! in_array($s->datos['nivel_previo'] ?? null, ['INDEPENDIENTE', 'CON_APOYO'], true)
                    || ! in_array($s->datos['desempeno'] ?? null, ['INDEPENDIENTE', 'CON_APOYO'], true)
                    || ! in_array($s->datos['actividad'] ?? null, ['MEDICACION', 'PERTENENCIAS', 'RUTINAS', 'CITAS', 'COMUNICACION', 'FINANZAS', 'COMPRAS', 'TRANSPORTE'], true)
                    || ! in_array($s->datos['tipo_ayuda'] ?? null, ['NINGUNA', 'RECORDATORIO', 'SUPERVISION', 'SUSTITUCION', 'NO_APLICA'], true)
                    || ! in_array($s->datos['causa_ayuda'] ?? null, ['NINGUNA', 'COGNITIVA', 'FISICA', 'SENSORIAL', 'INSTITUCIONAL', 'MIXTA', 'NO_DETERMINADA', 'NO_APLICA'], true)
                    || ! in_array($s->datos['fuente'] ?? null, ['OBSERVACION_DIRECTA', 'RESIDENTE', 'FAMILIAR', 'EQUIPO_CUIDADOS'], true)) {
                    $motivos[] = 'IADL_SIN_CONTEXTO_INTERPRETABLE';
                }
                $sinAyuda = in_array($s->datos['tipo_ayuda'] ?? null, ['NINGUNA', 'NO_APLICA'], true);
                $sinCausa = in_array($s->datos['causa_ayuda'] ?? null, ['NINGUNA', 'NO_APLICA'], true);
                if ((($s->datos['desempeno'] ?? null) === 'CON_APOYO' && ($sinAyuda || $sinCausa))
                    || (($s->datos['desempeno'] ?? null) === 'INDEPENDIENTE' && (! $sinAyuda || ! $sinCausa))) {
                    $motivos[] = 'IADL_CON_DATOS_DOCUMENTALES_INCOMPATIBLES';
                }
            }
        } elseif (! in_array($v->elemento === 'C04' ? 'DIFERENCIAL' : 'CALIDAD', $tipos, true)) {
            $motivos[] = 'SIN_REVISION_ORIGINAL_PERTINENTE';
        }
        foreach ($v->soportes as $s) {
            if (in_array($s->tipo, ['DIFERENCIAL', 'CALIDAD'], true) && trim($s->datos['descripcion'] ?? '') === '') {
                $motivos[] = 'REVISION_ORIGINAL_SIN_CONTENIDO';
            }
        }
        if ($v->elemento === 'C04' && ! is_bool($v->comprobaciones['impedimento_no_resuelto'] ?? null)) {
            $motivos[] = 'SIN_CONCLUSION_DIFERENCIAL_EXPLICITA';
        }

        return array_values(array_unique($motivos));
    }
}
