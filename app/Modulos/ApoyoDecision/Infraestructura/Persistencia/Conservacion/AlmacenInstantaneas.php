<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Compartido\Infraestructura\Persistencia\CatalogoCodigos;
use App\Compartido\Infraestructura\Persistencia\GeneradorCodigo;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\MapeoFuentes\ExtractorVariableMapeada;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorEvidencias;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorReglas;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\EvidenciaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\ReglaExpertaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\ContextoSimulacionLocal;
use Closure;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Illuminate\Database\Connection;
use MotorExperto\dominio\orion\Entities\Cognicion\ResultadoDominio;
use MotorExperto\dominio\orion\Entities\Cognicion\ResultadoSubcomponente;
use MotorExperto\dominio\orion\Entities\Cognicion\VectorCognitivo;
use MotorExperto\dominio\orion\Entities\Conocimiento\ReglaExperta;
use MotorExperto\dominio\orion\Entities\Dcl\PerfilRequisitosDcl;
use MotorExperto\dominio\orion\Entities\Dcl\ResultadoClasificacionDcl;
use MotorExperto\dominio\orion\Entities\Funcionalidad\EvidenciaIadl;
use MotorExperto\dominio\orion\Entities\Funcionalidad\VectorFuncionalidad;
use MotorExperto\dominio\orion\Entities\Hechos\EvidenciaCognitiva;
use MotorExperto\dominio\orion\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\Services\Compuertas\EvaluadorConfusoresService;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum as Dominio;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum as Estado;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EvaluabilidadEnum as Evaluabilidad;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum as Hallazgo;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\SubcomponenteCognitivoEnum as Subcomponente;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\OperadorCondicionEnum as Operador;
use MotorExperto\dominio\orion\ValueObjects\Dcl\EstadoCriterioDclEnum as Criterio;
use MotorExperto\dominio\orion\ValueObjects\Dcl\MotivoAbstencionEnum as Motivo;
use MotorExperto\dominio\orion\ValueObjects\Dcl\SubtipoDclEnum as Patron;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoDiferencialEnum as Diferencial;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoIadlEnum as EstadoIadl;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\OportunidadIadlEnum as Oportunidad;
use MotorExperto\soporte\conservacion\CodecInstantanea;

/**
 * TÍTULO:
 * Conservación relacional de memoria, cognición y clasificación.
 *
 * CAPA:
 * Infrastructure — Persistence.
 *
 * ¿PARA QUÉ SIRVE?
 * Implementa instantáneas recuperables sin un proceso anterior.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Objetos originales y una conexión experimental segregada.
 *
 * ¿QUÉ HACE?
 * Escribe atómicamente, reutiliza D-137 y verifica contenido al leer.
 *
 * ¿QUÉ DEVUELVE?
 * Los mismos valores y relaciones de identidad reconstruidos.
 *
 * ¿QUÉ NO DEBE HACER?
 * No interpreta clínica, crea fuentes ni sobrescribe historia.
 */
final readonly class AlmacenInstantaneas
{
    /**
     * Prepara capacidades sin abrir conexiones ni ejecutar SQL.
     *
     * @param  string  $conexion  Nombre explícito de la base técnica desechable.
     * @param  Closure|null  $extraerOriginal  Extracción autorizada por lote, obligatoria para conservar fuentes institucionales.
     * @param  DateTimeZone  $zona  Zona de las columnas operativas de origen.
     * @param  ContextoSimulacionLocal|null  $simulacionLocal  Capacidad optativa con identidad física/alias validada y versión cerrada.
     *                                                         No produce efectos ni excepciones durante la construcción.
     */
    public function __construct(private string $conexion, private ?Closure $extraerOriginal = null,
        private DateTimeZone $zona = new DateTimeZone('UTC'), private ?ContextoSimulacionLocal $simulacionLocal = null) {}

    private function db(): Connection
    {
        if ($this->simulacionLocal !== null) {
            if ($this->simulacionLocal->conexion !== $this->conexion) {
                throw new DomainException('Capacidad y conexión de conservación diferentes.');
            }

            return $this->simulacionLocal->db();
        }

        return ExtractorVariableMapeada::conexionSegura($this->conexion);
    }

    /**
     * Resuelve identidad original o cabecera anterior sin inventar referencias.
     *
     * @param  string  $id  Identidad original explícita.
     * @return object|null Cabecera del destino solicitado o ausencia física.
     *
     * @throws RuntimeException Por entorno/conexión no autorizados o fallo SQL.
     */
    public function cabecera(string $id): ?object
    {
        return $this->db()->table('evaluaciones_expertas')->where('id_evaluacion_dominio', $id)
            ->orWhere('cod_evaluacion_experta', $id)->first();
    }

    private function insertar(string $tabla, array $datos): string
    {
        $id = (new GeneradorCodigo)->siguiente($tabla, $this->conexion);
        $this->db()->table($tabla)->insert([CatalogoCodigos::clave($tabla) => $id] + $datos);

        return $id;
    }

    private function contexto(string $id, string $residente, string $version): void
    {
        $this->simulacionLocal?->validarVersion($version);
        ExtractorVariableMapeada::validarIds($id, $residente, $version);
        if (! str_starts_with($id, 'SIM-') || strlen($id) <= 4 || ! str_starts_with($residente, 'SINTETICO-') || strlen($residente) <= 10) {
            throw new DomainException('La conservación solo admite casos sintéticos explícitos.');
        }
    }

    /**
     * Conserva la cognición completa o ninguna fila; el reintento no sobrescribe.
     *
     * @param  VectorCognitivo  $vector  Memoria y resultados originales de un caso sintético.
     * @return void Confirma solo la transacción completa o un reintento idéntico.
     *
     * @throws DomainException Por contexto, catálogo, origen o contenido incompatible.
     * @throws RuntimeException Por destino no autorizado o fallo de almacenamiento.
     */
    public function guardar(VectorCognitivo $vector): void
    {
        $m = $vector->memoria;
        $this->contexto($m->evaluacionId, $m->residenteId, $m->versionConocimiento);
        $hash = CodecInstantanea::huella($vector);
        $this->db()->transaction(function () use ($m, $vector, $hash): void {
            $db = $this->db();
            // Serializa también la primera inserción, cuando aún no hay cabecera.
            if (! $db->table('versiones_modelo_experto')->where('cod_version_modelo', $m->versionConocimiento)->lockForUpdate()->exists()
                || ! $db->table('residentes')->where('cod_residente', $this->simulacionLocal?->fisico($m->residenteId) ?? $m->residenteId)->exists()) {
                throw new DomainException('La conservación exige residente y versión existentes; no crea referencias.');
            }
            $cab = $this->cabecera($m->evaluacionId);
            if ($cab !== null && $cab->version_conservacion !== null) {
                $original = $this->obtener($m->evaluacionId, $m->residenteId, $m->versionConocimiento, $m->fechaCorte);
                if (CodecInstantanea::huella($original) !== $hash) {
                    throw new DomainException('No se sustituye una instantánea existente por otro contenido.');
                }

                return;
            }
            $catalogo = CatalogoConservacionExperta::leer($this->conexion, $m->versionConocimiento, $this->simulacionLocal);
            foreach ($vector->resultados as $r) {
                $this->catalogo($catalogo, 'DOMINIO', $r->dominio->value, $r->estado->value);
                foreach ($r->subcomponentes as $s) {
                    $this->catalogo($catalogo, 'SUBCOMPONENTE', $s->subcomponente->value, $s->hallazgo->value);
                }
            }
            $campos = ['id_evaluacion_dominio' => $m->evaluacionId, 'version_conservacion' => CatalogoConservacionExperta::VERSION,
                'huella_memoria_cognicion' => $hash] + CodecInstantanea::fecha('fecha_corte', $m->fechaCorte);
            if ($cab === null) {
                $ahora = now()->format('Y-m-d H:i:s');
                $e = $this->insertar('evaluaciones_expertas', $campos + ['cod_residente' => $this->simulacionLocal?->fisico($m->residenteId) ?? $m->residenteId,
                    'cod_version_modelo' => $m->versionConocimiento, 'origen_activacion' => 'PRUEBA_TECNICA',
                    'estado_ejecucion' => 'PRUEBA_FINALIZADA', 'fecha_hora_inicio' => $ahora, 'fecha_hora_fin' => $ahora]);
            } else {
                $this->validarCabecera($cab, $m->residenteId, $m->versionConocimiento);
                $e = $cab->cod_evaluacion_experta;
                // Adjunta una vez metadatos antes inexistentes; no cambia la historia D-137.
                $db->table('evaluaciones_expertas')->where('cod_evaluacion_experta', $e)->whereNull('version_conservacion')->update($campos);
            }
            $hechos = $this->guardarMemoria($m, $e);
            foreach (array_values($vector->resultados) as $orden => $r) {
                $padre = $this->guardarCriterio($e, $catalogo, 'DOMINIO', $r->dominio->value, $r->estado->value,
                    $r->justificacion, $r->evaluabilidad->codigo($r->dominio), null, $orden);
                $this->guardarSoportes($padre, $r->evidencias, $hechos);
                foreach (array_values($r->subcomponentes) as $i => $s) {
                    $hijo = $this->guardarCriterio($e, $catalogo, 'SUBCOMPONENTE', $s->subcomponente->value,
                        $s->hallazgo->value, $s->justificacion, null, $padre, $i);
                    $this->guardarSoportes($hijo, $s->evidencias, $hechos);
                }
            }
        }, 3);
    }

    private function validarCabecera(object $c, string $residente, string $version): void
    {
        if ($c->cod_residente !== ($this->simulacionLocal?->fisico($residente) ?? $residente) || $c->cod_version_modelo !== $version
            || $c->origen_activacion !== 'PRUEBA_TECNICA' || $c->estado_ejecucion !== 'PRUEBA_FINALIZADA') {
            throw new DomainException('Cabecera de otro contexto o fuera del alcance técnico finalizado.');
        }
    }

    /** @return array<string, array{id:string,original:?string}> Hechos originales y evidencia D-137 cuando existe. */
    private function guardarMemoria(MemoriaTrabajo $m, string $e): array
    {
        $db = $this->db();
        $originales = $db->table('evidencias_evaluacion')->where('cod_evaluacion_experta', $e)->pluck('cod_evidencia_evaluacion')->all();
        $institucional = null;
        if (array_intersect(array_keys($m->hechos), $originales) !== []) {
            if ($this->extraerOriginal === null || $e !== $m->evaluacionId) {
                throw new DomainException('Conservar una fuente institucional exige su extractor original explícito.');
            }
            $institucional = (new EvidenciaEloquentRepository(new MapeadorEvidencias($this->zona), $this->extraerOriginal,
                $this->zona, $this->conexion))->paraEvaluacion($m->evaluacionId, $m->residenteId, $m->versionConocimiento, $m->fechaCorte);
        }
        $meta = $this->metadatosOrigen($e);
        $mapa = [];
        $caracterizaciones = [];
        foreach (array_values($m->evidencias) as $orden => $c) {
            $datos = ['cod_evidencia_cognitiva_instantanea' => app(CodigosEntidad::class)->siguiente('evidencias_cognitivas_instantanea', $this->conexion),
                'cognitiva_dominio' => $c->dominio->value, 'cognitiva_subcomponente' => $c->subcomponente?->value,
                'cognitiva_hallazgo' => $c->hallazgo->value, 'cognitiva_instrumento_id_dominio' => $c->instrumentoId,
                'cognitiva_justificacion' => $c->justificacion, 'cognitiva_orden' => $orden];
            foreach (['instrumento', 'edicion', 'subprueba', 'norma', 'idioma', 'poblacion', 'revisor'] as $campo) {
                $datos['cognitiva_referencia_'.$campo] = $c->referenciaSintetica[$campo] ?? null;
            }
            $caracterizaciones[$c->hecho->id] = $datos;
        }
        foreach (array_values($m->hechos) as $i => $h) {
            $original = in_array($h->id, $originales, true) ? $h->id : null;
            if ($original !== null) {
                if (CodecInstantanea::huella($institucional->hechos[$h->id] ?? null) !== CodecInstantanea::huella($h)) {
                    throw new DomainException('El hecho difiere de su extracción institucional original.');
                }
                $tipo = 'D137';
            } else {
                $tipo = $this->tipoSintetico($h, $m);
            }
            $actividad = $original !== null && $meta[$original]->tabla_raiz === 'seguimientos_pedagogicos';
            $id = $this->insertar('hechos_instantanea_experta', ['cod_evaluacion_experta' => $e, 'id_hecho_dominio' => $h->id,
                'fuente_id_dominio' => $h->fuenteId, 'origen_id_dominio' => $h->origenId, 'episodio_id_dominio' => $h->episodioId,
                'concepto_dominio' => $h->concepto, 'orden' => $i, 'tipo_origen' => $tipo, 'cod_evidencia_evaluacion' => $original,
                'cod_actividad' => $actividad ? $h->episodioId : null,
                'cod_atencion' => $original !== null && ! $actividad ? $h->episodioId : null]
                + CodecInstantanea::fecha('fecha_hecho', $h->fecha) + CodecInstantanea::escalar('valor', $h->valor) + ($caracterizaciones[$h->id] ?? []));
            $mapa[$h->id] = ['id' => $id, 'original' => $original];
        }

        return $mapa;
    }

    private function tipoSintetico(HechoExperto $h, MemoriaTrabajo $m): string
    {
        if (in_array($h->fuenteId, ['REFERENCIA_EXTERNA_SINTETICA', 'REFERENCIA_CRITERIO_SINTETICA', 'REFERENCIA_IADL_SINTETICA'], true)) {
            return $h->fuenteId;
        }
        $prefijo = 'VERIFICACION_PROCEDENCIA:';
        $revision = str_starts_with($h->concepto, $prefijo) && isset($m->evidencias[substr($h->concepto, strlen($prefijo))]);
        $partes = explode(':', $h->concepto, 2);
        $campo = count($partes) === 2 && Dominio::tryFrom($partes[0]) !== null
            && in_array($partes[1], EvaluadorConfusoresService::CAMPOS_CONTEXTO, true);
        if (($revision || $campo) && (is_bool($h->valor) || $h->valor === null)) {
            return 'REVISION_TECNICA_SINTETICA';
        }
        throw new DomainException('Hecho sin mapeo institucional ni forma sintética autorizada: '.$h->id);
    }

    private function metadatosOrigen(string $e): array
    {
        return $this->db()->table('evidencias_evaluacion as ev')
            ->join('mapeos_variables_fuente as mv', 'mv.cod_mapeo_variable_fuente', '=', 'ev.cod_mapeo_variable_fuente')
            ->join('fuentes_datos_expertas as f', 'f.cod_fuente_dato_experta', '=', 'mv.cod_fuente_dato_experta')
            ->where('ev.cod_evaluacion_experta', $e)->select(['ev.cod_evidencia_evaluacion', 'ev.cod_registro_fuente',
                'mv.cod_version_modelo', 'f.cod_version_modelo as version_fuente', 'f.codigo_fuente', 'f.tabla_raiz'])
            ->get()->keyBy('cod_evidencia_evaluacion')->all();
    }

    private function catalogo(array $cat, string $tipo, string $codigo, string $valor): array
    {
        $c = $cat['SNAP:'.$tipo.':'.$codigo] ?? null;
        if ($c === null || ! isset($c['valores'][$valor])) {
            throw new DomainException('Falta catálogo experimental explícito para '.$tipo.':'.$codigo.'='.$valor);
        }

        return [$c['nodo'], $c['valores'][$valor]];
    }

    private function guardarCriterio(string $e, array $cat, string $tipo, string $codigo, string $valor,
        string $razon, ?string $evaluabilidad, ?string $padre, int $orden): string
    {
        [$nodo, $v] = $this->catalogo($cat, $tipo, $codigo, $valor);
        $fecha = now()->format('Y-m-d H:i:s');
        $id = $this->insertar('evaluacion_criterios', ['cod_evaluacion_experta' => $e, 'cod_nodo_criterio' => $nodo,
            'tipo_registro' => $tipo, 'version_conservacion' => CatalogoConservacionExperta::VERSION, 'cod_criterio_padre' => $padre,
            'orden_conservacion' => $orden, 'estado_evaluabilidad' => $evaluabilidad, 'fecha_hora_determinacion' => $fecha]);
        $this->insertar('resultados_criterio', ['cod_evaluacion_criterio' => $id, 'cod_valor_semantico' => $v,
            'justificacion_original' => $razon, 'fecha_hora_determinacion' => $fecha]);

        return $id;
    }

    private function guardarSoportes(string $criterio, array $soportes, array $hechos): void
    {
        foreach ($soportes as $orden => $soporte) {
            $h = $soporte instanceof EvidenciaCognitiva ? $soporte->hecho : $soporte;
            $dato = $hechos[$h->id] ?? throw new DomainException('Soporte sin hecho conservado.');
            if ($dato['original'] !== null) {
                $this->insertar('evidencias_criterio_evaluacion', ['cod_evaluacion_criterio' => $criterio,
                    'cod_evidencia_evaluacion' => $dato['original'], 'rol_en_criterio' => 'SOPORTE_EXPERIMENTAL',
                    'estado_participacion' => 'PRUEBA_TECNICA', 'orden_conservacion' => $orden, 'fecha_hora_vinculacion' => now()->format('Y-m-d H:i:s')]);
            } else {
                $this->insertar('evidencias_criterio_evaluacion', ['cod_evaluacion_criterio' => $criterio,
                    'cod_hecho_instantanea' => $dato['id'], 'cod_evidencia_evaluacion' => null,
                    'cod_soporte_sintetico' => app(CodigosEntidad::class)->siguiente('soportes_sinteticos_criterio', $this->conexion),
                    'rol_en_criterio' => 'SOPORTE_EXPERIMENTAL', 'estado_participacion' => 'PRUEBA_TECNICA',
                    'orden_conservacion' => $orden, 'fecha_hora_vinculacion' => null]);
            }
        }
    }

    /**
     * Recupera valores originales; no vuelve a consultar fuentes mutables.
     *
     * @param  string  $id  Identidad original de evaluación.
     * @param  string  $residente  Sujeto sintético propietario.
     * @param  string  $version  Versión exacta del conocimiento.
     * @param  DateTimeImmutable  $corte  Corte con instante, precisión y zona originales.
     * @return VectorCognitivo|null Perfil completo o ausencia real.
     *
     * @throws DomainException Por contexto, temporalidad, FK o instantánea incompletos/incompatibles.
     * @throws RuntimeException Por destino no autorizado o fallo SQL.
     */
    public function obtener(string $id, string $residente, string $version, DateTimeImmutable $corte): ?VectorCognitivo
    {
        $this->contexto($id, $residente, $version);
        $cab = $this->cabecera($id);
        if ($cab === null) {
            return null;
        }
        $this->validarCabecera($cab, $residente, $version);
        if ($cab->version_conservacion !== CatalogoConservacionExperta::VERSION
            || CodecInstantanea::fecha('f', CodecInstantanea::leerFecha('fecha_corte', $cab)) !== CodecInstantanea::fecha('f', $corte)) {
            throw new DomainException('Versión de conservación o identidad temporal incompatible.');
        }
        $e = $cab->cod_evaluacion_experta;
        $filas = $this->ordenadas('hechos_instantanea_experta', 'cod_evaluacion_experta', $e);
        $metadatos = $this->metadatosOrigen($e);
        $hechos = $porPk = [];
        foreach ($filas as $f) {
            $h = new HechoExperto($f->id_hecho_dominio, $id, $residente, $f->episodio_id_dominio, $f->fuente_id_dominio,
                $f->origen_id_dominio, $version, $f->concepto_dominio, CodecInstantanea::leerFecha('fecha_hecho', $f), CodecInstantanea::leerEscalar('valor', $f));
            if ($f->tipo_origen === 'D137') {
                $meta = $metadatos[$f->cod_evidencia_evaluacion] ?? throw new DomainException('Evidencia original ajena o sin mapeo.');
                $actividad = $meta->tabla_raiz === 'seguimientos_pedagogicos';
                if ($h->id !== $f->cod_evidencia_evaluacion || $h->fuenteId !== $meta->codigo_fuente
                    || $h->origenId !== $meta->cod_registro_fuente || $meta->cod_version_modelo !== $version || $meta->version_fuente !== $version
                    || ($actividad ? $f->cod_actividad : $f->cod_atencion) !== $h->episodioId
                    || ($actividad ? $f->cod_atencion : $f->cod_actividad) !== null) {
                    throw new DomainException('La procedencia conservada no corresponde a su referencia original.');
                }
            } elseif ($f->cod_evidencia_evaluacion !== null || $f->cod_atencion !== null || $f->cod_actividad !== null) {
                throw new DomainException('Una referencia sintética no simula FK institucionales.');
            }
            $hechos[$h->id] = $h;
            $porPk[$f->cod_hecho_instantanea] = $h;
        }
        $cognitivas = $this->db()->table('hechos_instantanea_experta')->whereIn('cod_hecho_instantanea', array_keys($porPk))->whereNotNull('cod_evidencia_cognitiva_instantanea')->orderBy('cognitiva_orden')->get()->map(fn ($h) => (object) $this->bloque((array) $h, ProyeccionReduccionExperta::COG))->all();
        $this->validarOrden($cognitivas, 'orden');
        $evidencias = [];
        foreach ($cognitivas as $f) {
            $referencia = [];
            foreach (['instrumento', 'edicion', 'subprueba', 'norma', 'idioma', 'poblacion', 'revisor'] as $c) {
                if ($f->{'referencia_'.$c} !== null) {
                    $referencia[$c] = $f->{'referencia_'.$c};
                }
            }
            $evidencias[] = new EvidenciaCognitiva($porPk[$f->cod_hecho_instantanea], Dominio::from($f->dominio),
                $f->subcomponente === null ? null : Subcomponente::from($f->subcomponente), Hallazgo::from($f->hallazgo),
                $f->instrumento_id_dominio, $f->justificacion, $referencia);
        }
        $m = new MemoriaTrabajo($id, $residente, $version, $corte, array_values($hechos), $evidencias);
        foreach ($filas as $f) {
            if ($f->tipo_origen !== 'D137' && $this->tipoSintetico($m->hechos[$f->id_hecho_dominio], $m) !== $f->tipo_origen) {
                throw new DomainException('Forma de origen sintético alterada.');
            }
        }
        $criterios = $this->criterios($e, $version);
        $soportesCargados = $this->cargarSoportes($e, $porPk);
        $dominios = array_values(array_filter($criterios, fn ($c) => $c->tipo_registro === 'DOMINIO'));
        $this->validarOrden($dominios, 'orden_conservacion');
        $resultados = $usados = [];
        foreach ($dominios as $c) {
            $dominio = Dominio::from($c->codigo);
            $nivel = null;
            foreach (Evaluabilidad::cases() as $ev) {
                if ($ev->codigo($dominio) === $c->estado_evaluabilidad) {
                    $nivel = $ev;
                }
            }
            if ($nivel === null || $c->cod_criterio_padre !== null) {
                throw new DomainException('Evaluabilidad o jerarquía de dominio incompatible.');
            }
            $partes = array_values(array_filter($criterios, fn ($s) => $s->tipo_registro === 'SUBCOMPONENTE' && $s->cod_criterio_padre === $c->cod_evaluacion_criterio));
            $this->validarOrden($partes, 'orden_conservacion');
            $sub = [];
            foreach ($partes as $s) {
                $usados[$s->cod_evaluacion_criterio] = true;
                $sub[] = new ResultadoSubcomponente(Subcomponente::from($s->codigo), Hallazgo::from($s->valor), $m,
                    $soportesCargados[$s->cod_evaluacion_criterio] ?? [], $s->justificacion_original);
            }
            $resultados[] = new ResultadoDominio($dominio, Estado::from($c->valor), $nivel, $m, $sub,
                $soportesCargados[$c->cod_evaluacion_criterio] ?? [], $c->justificacion_original);
        }
        foreach ($criterios as $c) {
            if ($c->tipo_registro === 'SUBCOMPONENTE' && ! isset($usados[$c->cod_evaluacion_criterio])) {
                throw new DomainException('Subcomponente huérfano o de otro dominio.');
            }
        }
        $vector = new VectorCognitivo($resultados);
        if (! hash_equals($cab->huella_memoria_cognicion, CodecInstantanea::huella($vector))) {
            throw new DomainException('La instantánea conservada está incompleta o alterada.');
        }

        return $vector;
    }

    private function criterios(string $e, string $version): array
    {
        $filas = $this->db()->table('evaluacion_criterios as c')->join('nodos_semanticos as n', 'n.cod_nodo_semantico', '=', 'c.cod_nodo_criterio')
            ->leftJoin('resultados_criterio as r', 'r.cod_evaluacion_criterio', '=', 'c.cod_evaluacion_criterio')
            ->leftJoin('valores_semanticos as v', 'v.cod_valor_semantico', '=', 'r.cod_valor_semantico')
            ->where('c.cod_evaluacion_experta', $e)->where('c.tipo_registro', '<>', 'D137')->orderBy('c.orden_conservacion')
            ->select(['c.*', 'n.codigo_semantico', 'n.cod_version_modelo', 'v.codigo_valor as valor', 'r.cod_valor_semantico', 'r.justificacion_original'])->get()->all();
        $cat = CatalogoConservacionExperta::leer($this->conexion, $version, $this->simulacionLocal);
        foreach ($filas as $f) {
            $prefijo = 'SNAP:'.$f->tipo_registro.':';
            if ($f->version_conservacion !== CatalogoConservacionExperta::VERSION || $f->cod_version_modelo !== $version
                || ! str_starts_with($f->codigo_semantico, $prefijo) || $f->valor === null || $f->justificacion_original === null) {
                throw new DomainException('Criterio sin resultado original o de otro contrato.');
            }
            $f->codigo = substr($f->codigo_semantico, strlen($prefijo));
            [$n, $v] = $this->catalogo($cat, $f->tipo_registro, $f->codigo, $f->valor);
            if ($n !== $f->cod_nodo_criterio || $v !== $f->cod_valor_semantico) {
                throw new DomainException('Nodo conservado de otro catálogo.');
            }
        }

        return $filas;
    }

    /** Carga todos los soportes de la evaluación por lotes, sin consultas por criterio. */
    private function cargarSoportes(string $e, array $porPk): array
    {
        $todos = [];
        $originales = $this->db()->table('hechos_instantanea_experta')->where('cod_evaluacion_experta', $e)
            ->whereNotNull('cod_evidencia_evaluacion')->pluck('id_hecho_dominio', 'cod_evidencia_evaluacion')->all();
        $vinculos = $this->db()->table('evidencias_criterio_evaluacion as s')
            ->join('evaluacion_criterios as c', 'c.cod_evaluacion_criterio', '=', 's.cod_evaluacion_criterio')
            ->where('c.cod_evaluacion_experta', $e)->where('c.tipo_registro', '<>', 'D137')->select('s.*')->get();
        foreach ($vinculos as $f) {
            if ($f->rol_en_criterio !== 'SOPORTE_EXPERIMENTAL' || $f->estado_participacion !== 'PRUEBA_TECNICA') {
                throw new DomainException('Rol o participación original incompatible.');
            }
            $todos[$f->cod_evaluacion_criterio][] = [$f->orden_conservacion,
                $f->cod_hecho_instantanea !== null ? ($porPk[$f->cod_hecho_instantanea]->id ?? throw new DomainException('Soporte de otra evaluación.')) : ($originales[$f->cod_evidencia_evaluacion] ?? throw new DomainException('Soporte sin hecho original.'))];
        }
        $salida = [];
        foreach ($todos as $criterio => $lista) {
            usort($lista, fn ($a, $b) => $a[0] <=> $b[0]);
            $ids = [];
            foreach ($lista as $i => [$orden, $id]) {
                if (! in_array($orden, [$i, (string) $i], true) || in_array($id, $ids, true)) {
                    throw new DomainException('Orden o identidad de soporte duplicados.');
                }
                $ids[] = $id;
            }
            $salida[$criterio] = $ids;
        }

        return $salida;
    }

    private function ordenadas(string $tabla, string $campo, string $id): array
    {
        $r = $this->db()->table($tabla)->where($campo, $id)->orderBy('orden')->get()->all();
        $this->validarOrden($r, 'orden');

        return $r;
    }

    private function validarOrden(array $filas, string $campo): void
    {
        foreach ($filas as $i => $f) {
            if (! in_array($f->{$campo}, [$i, (string) $i], true)) {
                throw new DomainException('Orden conservado discontinuo o duplicado.');
            }
        }
    }

    /**
     * Conserva DCL solo sobre una cognición previa idéntica.
     *
     * @param  ResultadoClasificacionDcl  $r  Requisitos, funcionalidad, salida y reglas originales.
     * @return void Confirma una clasificación íntegra o un reintento idéntico.
     *
     * @throws DomainException Por cognición ausente, contenido divergente o conocimiento adulterado.
     * @throws RuntimeException Por destino no autorizado o fallo SQL, con rollback.
     */
    public function guardarClasificacion(ResultadoClasificacionDcl $r): void
    {
        $m = $r->perfil->cognicion->memoria;
        $this->contexto($m->evaluacionId, $m->residenteId, $m->versionConocimiento);
        $hash = CodecInstantanea::huella($r);
        $this->db()->transaction(function () use ($r, $m, $hash): void {
            $db = $this->db();
            $cab = $this->cabecera($m->evaluacionId) ?? throw new DomainException('Guardar cognición antes de clasificar.');
            $db->table('evaluaciones_expertas')->where('cod_evaluacion_experta', $cab->cod_evaluacion_experta)->lockForUpdate()->first();
            $previa = $this->obtener($m->evaluacionId, $m->residenteId, $m->versionConocimiento, $m->fechaCorte);
            if (CodecInstantanea::huella($previa) !== CodecInstantanea::huella($r->perfil->cognicion)) {
                throw new DomainException('La clasificación debe conservar la misma cognición original.');
            }
            $e = $cab->cod_evaluacion_experta;
            $existente = $db->table('clasificaciones_dcl_instantanea')->where('cod_evaluacion_experta', $e)->first();
            if ($existente !== null) {
                $original = $this->obtenerClasificacion($m->evaluacionId, $m->residenteId, $m->versionConocimiento, $m->fechaCorte, $r->perfil->versionContrato);
                if (CodecInstantanea::huella($original) !== $hash) {
                    throw new DomainException('No se sustituye una clasificación existente.');
                }

                return;
            }
            $mapa = [];
            foreach ($db->table('hechos_instantanea_experta')->where('cod_evaluacion_experta', $e)->get() as $h) {
                $mapa[$h->id_hecho_dominio] = ['id' => $h->cod_hecho_instantanea, 'original' => $h->cod_evidencia_evaluacion];
            }
            $f = $r->perfil->funcionalidad;
            $funcion = app(CodigosEntidad::class)->siguiente('funcionalidad_instantanea_experta', $this->conexion);
            $clasificacion = $this->insertar('clasificaciones_dcl_instantanea', ['cod_evaluacion_experta' => $e,
                'cod_funcionalidad_instantanea' => $funcion, 'version_contrato' => $r->perfil->versionContrato,
                'funcionalidad_independencia_relativa' => $f->independenciaRelativa->value,
                'funcionalidad_revisor_sintetico_id' => $f->revisorSinteticoId, 'funcionalidad_justificacion' => $f->justificacion,
                'diferencial' => $r->perfil->diferencial->value, 'patron_candidato' => $r->patronCandidato?->value,
                'justificacion' => $r->justificacion, 'uso' => $r::USO, 'huella_clasificacion' => $hash]);
            foreach ($f->evidencias as $i => $v) {
                $cambio = ['cod_evidencia_iadl_instantanea' => app(CodigosEntidad::class)->siguiente('evidencias_iadl_instantanea', $this->conexion),
                    'iadl_cod_funcionalidad_instantanea' => $funcion, 'iadl_actividad_id_dominio' => $v->actividadId,
                    'iadl_estado' => $v->estado->value, 'iadl_oportunidad' => $v->oportunidad->value, 'iadl_nivel_previo' => $v->nivelPrevio?->value,
                    'iadl_causa_ayuda' => $v->causaAyuda, 'iadl_atribucion_cognitiva_simulada' => $v->atribucionCognitivaSimulada,
                    'iadl_revisor_sintetico_id' => $v->revisorSinteticoId, 'iadl_orden' => $i];
                if ($db->table('hechos_instantanea_experta')->where('cod_hecho_instantanea', $mapa[$v->hecho->id]['id'])
                    ->whereNull('cod_evidencia_iadl_instantanea')->update($cambio) !== 1) {
                    throw new DomainException('La caracterización IADL ya fue conservada.');
                }
            }
            $cat = CatalogoConservacionExperta::leer($this->conexion, $m->versionConocimiento, $this->simulacionLocal);
            foreach (array_keys($r->perfil->criterios) as $i => $codigo) {
                $c = $this->guardarCriterio($e, $cat, 'DCL', $codigo, $r->perfil->criterios[$codigo]->value,
                    $r->perfil->justificaciones[$codigo], null, null, $i);
                $this->guardarSoportes($c, $r->perfil->soportes[$codigo], $mapa);
            }
            foreach ($r->motivos as $i => $motivo) {
                $this->insertar('motivos_dcl_instantanea', ['cod_clasificacion_dcl_instantanea' => $clasificacion, 'motivo' => $motivo->value, 'orden' => $i]);
            }
            foreach ($r->reglasAplicadas as $i => $regla) {
                $original = $db->table('reglas_expertas')->where('cod_regla_experta', $regla->id)->where('cod_version_modelo', $m->versionConocimiento)->value('cod_regla_experta');
                if ($original !== null) {
                    $tipo = $db->table('reglas_expertas')->where('cod_regla_experta', $original)->value('tipo_regla');
                    $declarada = (new ReglaExpertaEloquentRepository(new MapeadorReglas($tipo), $this->conexion, $this->simulacionLocal))
                        ->obtener($original, $m->versionConocimiento);
                    if (CodecInstantanea::huella($declarada) !== CodecInstantanea::huella($regla)) {
                        throw new DomainException('La regla aplicada difiere de su conocimiento D-137 original.');
                    }
                }
                if ($original === null && ! str_starts_with($regla->id, 'SIM-')) {
                    throw new DomainException('La regla debe tener origen D-137 o identidad sintética explícita.');
                }
                $id = $this->insertar('reglas_aplicadas_dcl_instantanea', ['cod_clasificacion_dcl_instantanea' => $clasificacion,
                    'cod_regla_experta' => $original, 'id_regla_dominio' => $regla->id, 'fuente' => $regla->fuente,
                    'habilitada' => $regla->habilitada, 'concepto_consecuencia' => $regla->consecuencia['concepto'], 'orden' => $i]
                    + CodecInstantanea::escalar('consecuencia', $regla->consecuencia['valor']));
                $this->guardarCondicion($id, $regla->condiciones, null, 0);
            }
        }, 3);
    }

    private function guardarCondicion(string $regla, array $c, ?string $padre, int $orden): void
    {
        $id = $this->insertar('condiciones_regla_instantanea', ['cod_regla_aplicada_dcl' => $regla,
            'cod_condicion_padre' => $padre, 'operador' => $c['operador']->value, 'variable' => $c['variable'] ?? null, 'orden' => $orden]
            + CodecInstantanea::escalar('literal', $c['valor'] ?? null));
        $hijas = $c['condiciones'] ?? (isset($c['condicion']) ? [$c['condicion']] : []);
        foreach ($hijas as $i => $hija) {
            $this->guardarCondicion($regla, $hija, $id, $i);
        }
    }

    /**
     * Recuperación íntegra de DCL; ausencia real se distingue de corrupción.
     *
     * @param  string  $id  Identidad original de evaluación.
     * @param  string  $residente  Sujeto sintético propietario.
     * @param  string  $version  Versión exacta de conocimiento.
     * @param  DateTimeImmutable  $corte  Corte original inclusivo y su zona.
     * @param  string  $contrato  Versión original del contrato experimental DCL.
     * @return ResultadoClasificacionDcl|null Resultado íntegro o ausencia real.
     *
     * @throws DomainException Por contexto, contrato, FK, orden o contenido incompatibles.
     * @throws RuntimeException Por destino no autorizado o fallo SQL.
     */
    public function obtenerClasificacion(string $id, string $residente, string $version, DateTimeImmutable $corte, string $contrato): ?ResultadoClasificacionDcl
    {
        $vector = $this->obtener($id, $residente, $version, $corte);
        if ($vector === null) {
            return null;
        }
        $e = $this->cabecera($id)->cod_evaluacion_experta;
        $db = $this->db();
        $c = $db->table('clasificaciones_dcl_instantanea')->where('cod_evaluacion_experta', $e)->first();
        if ($c === null) {
            if ($db->table('evaluacion_criterios')->where('cod_evaluacion_experta', $e)->where('tipo_registro', 'DCL')->exists()
                || $db->table('hechos_instantanea_experta')->where('cod_evaluacion_experta', $e)->whereNotNull('cod_evidencia_iadl_instantanea')->exists()) {
                throw new DomainException('Clasificación incompleta.');
            }

            return null;
        }
        if ($c->version_contrato !== $contrato || $c->uso !== ResultadoClasificacionDcl::USO) {
            throw new DomainException('Contrato o alcance DCL incompatible.');
        }
        $f = (object) $this->bloque((array) $c, ProyeccionReduccionExperta::FUN);
        $f->cod_funcionalidad_instantanea = $c->cod_funcionalidad_instantanea;
        $m = $vector->memoria;
        $porPk = [];
        foreach ($db->table('hechos_instantanea_experta')->where('cod_evaluacion_experta', $e)->get() as $h) {
            $porPk[$h->cod_hecho_instantanea] = $m->hechos[$h->id_hecho_dominio];
        }
        $iadl = [];
        $filasIadl = $this->db()->table('hechos_instantanea_experta')->where('iadl_cod_funcionalidad_instantanea', $f->cod_funcionalidad_instantanea)->orderBy('iadl_orden')->get()->all();
        $this->validarOrden($filasIadl, 'iadl_orden');
        foreach (array_map(fn ($h) => (object) $this->bloque((array) $h, ProyeccionReduccionExperta::IAD), $filasIadl) as $v) {
            $iadl[] = new EvidenciaIadl($porPk[$v->cod_hecho_instantanea] ?? throw new DomainException('IADL sin hecho propio.'),
                $v->actividad_id_dominio, EstadoIadl::from($v->estado), Oportunidad::from($v->oportunidad),
                $v->nivel_previo === null ? null : EstadoIadl::from($v->nivel_previo), $v->causa_ayuda,
                $v->atribucion_cognitiva_simulada === null ? null : CodecInstantanea::booleano($v->atribucion_cognitiva_simulada), $v->revisor_sintetico_id);
        }
        $funcional = new VectorFuncionalidad($m, Criterio::from($f->independencia_relativa), $iadl, $f->revisor_sintetico_id, $f->justificacion);
        $estados = $soportes = $razones = [];
        $soportesCargados = $this->cargarSoportes($e, $porPk);
        $criterios = array_values(array_filter($this->criterios($e, $version), fn ($c) => $c->tipo_registro === 'DCL'));
        $this->validarOrden($criterios, 'orden_conservacion');
        foreach ($criterios as $v) {
            $estados[$v->codigo] = Criterio::from($v->valor);
            $razones[$v->codigo] = $v->justificacion_original;
            $soportes[$v->codigo] = array_map(fn ($h) => $m->hechos[$h], $soportesCargados[$v->cod_evaluacion_criterio] ?? []);
        }
        $perfil = new PerfilRequisitosDcl($vector, $funcional, Diferencial::from($c->diferencial), $estados, $soportes, $razones, $contrato);
        $motivos = array_map(fn ($v) => Motivo::from($v->motivo), $this->ordenadas('motivos_dcl_instantanea', 'cod_clasificacion_dcl_instantanea', $c->cod_clasificacion_dcl_instantanea));
        $reglas = [];
        foreach ($this->ordenadas('reglas_aplicadas_dcl_instantanea', 'cod_clasificacion_dcl_instantanea', $c->cod_clasificacion_dcl_instantanea) as $v) {
            if ($v->cod_regla_experta !== null && ($v->cod_regla_experta !== $v->id_regla_dominio
                || ! $db->table('reglas_expertas')->where('cod_regla_experta', $v->cod_regla_experta)->where('cod_version_modelo', $version)->exists())) {
                throw new DomainException('Referencia original de regla ajena o de otra versión.');
            }
            $filas = $db->table('condiciones_regla_instantanea')->where('cod_regla_aplicada_dcl', $v->cod_regla_aplicada_dcl)->get()->all();
            $visitados = [];
            $ast = $this->leerCondicion($filas, null, $visitados, 0);
            if (count($visitados) !== count($filas)) {
                throw new DomainException('Árbol de regla con nodos huérfanos.');
            }
            $reglas[] = new ReglaExperta($v->id_regla_dominio, $version, $ast,
                ['concepto' => $v->concepto_consecuencia, 'valor' => CodecInstantanea::leerEscalar('consecuencia', $v)],
                CodecInstantanea::booleano($v->habilitada), $v->fuente);
        }
        $resultado = new ResultadoClasificacionDcl($perfil, $c->patron_candidato === null ? null : Patron::from($c->patron_candidato), $motivos, $c->justificacion, $reglas);
        if (! hash_equals($c->huella_clasificacion, CodecInstantanea::huella($resultado))) {
            throw new DomainException('Clasificación conservada incompleta o alterada.');
        }

        return $resultado;
    }

    private function leerCondicion(array $filas, ?string $padre, array &$visitados, int $profundidad): array
    {
        $hijas = array_values(array_filter($filas, fn ($f) => $f->cod_condicion_padre === $padre));
        usort($hijas, fn ($a, $b) => $a->orden <=> $b->orden);
        $this->validarOrden($hijas, 'orden');
        if ($padre === null && count($hijas) !== 1 || $hijas !== [] && $profundidad > 32) {
            throw new DomainException('Raíz o profundidad de regla inválida.');
        }
        $r = [];
        foreach ($hijas as $f) {
            if (isset($visitados[$f->cod_condicion_instantanea])) {
                throw new DomainException('Ciclo en regla conservada.');
            }
            $visitados[$f->cod_condicion_instantanea] = true;
            $op = Operador::from($f->operador);
            $nodo = ['operador' => $op];
            if (! in_array($op, [Operador::IGUAL_A, Operador::DISTINTO_DE], true)
                && ($f->variable !== null || CodecInstantanea::leerEscalar('literal', $f) !== null)) {
                throw new DomainException('Composición con literal o variable indebidos.');
            }
            $desc = $this->leerCondicion($filas, $f->cod_condicion_instantanea, $visitados, $profundidad + 1);
            if (in_array($op, [Operador::IGUAL_A, Operador::DISTINTO_DE], true)) {
                if ($desc !== []) {
                    throw new DomainException('Una comparación no tiene condiciones hijas.');
                }
                $nodo += ['variable' => $f->variable, 'valor' => CodecInstantanea::leerEscalar('literal', $f)];
            } elseif ($op === Operador::NO) {
                if (count($desc) !== 1) {
                    throw new DomainException('Negación sin una condición exacta.');
                }
                $nodo['condicion'] = $desc[0];
            } else {
                $nodo['condiciones'] = $desc;
            }
            $r[] = $nodo;
        }

        return $padre === null ? $r[0] : $r;
    }

    /** Proyección cerrada del bloque fijo; no consulta tablas retiradas. */
    private function bloque(array $fila, array $mapa): array
    {
        $r = [];
        foreach ($mapa as $columna => $destino) {
            $r[$columna] = $fila[$destino];
        }
        if (isset($fila['cod_hecho_instantanea'])) {
            $r['cod_hecho_instantanea'] = $fila['cod_hecho_instantanea'];
        }

        return $r;
    }
}
