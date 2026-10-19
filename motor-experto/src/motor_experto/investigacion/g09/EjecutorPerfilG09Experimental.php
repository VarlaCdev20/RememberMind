<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\g09;

use DomainException;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;

/**
 * Flujo integral TEST_ONLY de cuatro criterios G09:
 * SQLite en memoria → inspector de respuestas → evidencia → regla → informe.
 * Produce un perfil NO agregado; no evalúa Memoria ni clasifica DCL.
 */
final class EjecutorPerfilG09Experimental
{
    private readonly AdaptadorInstrumentalG08G09Experimental $adaptador;

    private readonly EvaluadorG09Experimental $evaluador;

    private readonly ConstructorInformeG09Experimental $informes;

    public function __construct(private readonly LectorSQLiteG08Experimental $lector, private readonly bool $exigirControlesTarea = false,
        private readonly ?PaqueteConocimiento $conocimiento = null)
    {
        $this->adaptador = new AdaptadorInstrumentalG08G09Experimental;
        $this->evaluador = new EvaluadorG09Experimental;
        $this->informes = new ConstructorInformeG09Experimental;
    }

    /**
     * $tareas: [['regla' => 'R-ATE-S01-P01', 'aplicacion' => 'SIM-AP-...'], ...].
     * $base: evaluación ficticia, residente ficticio, fecha_corte y contexto.
     */
    public function evaluar(array $base, array $tareas): array
    {
        if (($base['uso'] ?? null) !== 'TEST_ONLY'
            || ($base['aislado'] ?? null) !== true
            || ($base['version'] ?? null) !== CatalogoReglasG09Experimental::VERSION
            || ! is_string($base['residente_ficticio'] ?? null)
            || ! str_starts_with($base['residente_ficticio'], 'SINTETICO-')) {
            throw new DomainException('El perfil no admite residentes o versiones reales.');
        }
        $ids = [];
        $contextos = [];
        $motivos = [];
        $capturasExaminadas = [];
        $controlesTareas = $base['controles_tareas'] ?? [];
        if ($this->exigirControlesTarea && (! is_array($controlesTareas)
            || (array_key_exists('controles_tareas', $base) && $base['controles_tareas'] === null))) {
            throw new DomainException('Colección de controles de tarea inválida.');
        }
        $porDominio = array_key_exists('contextos_dominios', $base) ? $base['contextos_dominios'] : [];
        if (! is_array($porDominio)
            || array_diff(array_keys($porDominio), ['COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'])) {
            throw new DomainException('Contexto ajeno a G09; Memoria requiere su contrato D-131 propio.');
        }
        foreach (['COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'] as $criterio) {
            $particular = array_key_exists($criterio, $porDominio) ? $porDominio[$criterio] : [];
            if (! is_array($particular)) {
                throw new DomainException('Contexto de dominio inválido.');
            }
            if (array_diff(array_keys($particular), ['contexto_suficiente', 'senal_contextual', 'contradiccion_documentada', 'confusor_invalidante'])) {
                throw new DomainException('Campo de contexto desconocido.');
            }
            $resolver = static fn (string $campo, mixed $defecto = null): mixed => array_key_exists($campo, $particular)
                ? $particular[$campo] : (array_key_exists($campo, $base) ? $base[$campo] : $defecto);
            foreach (['contexto_suficiente', 'senal_contextual', 'contradiccion_documentada', 'confusor_invalidante'] as $bandera) {
                $valor = $resolver($bandera);
                if ($valor !== null && ! is_bool($valor)) {
                    throw new DomainException('Atestación técnica no booleana: '.$bandera);
                }
            }
            // No copiar evidencia introducida por el llamador.
            $contextos[$criterio] = [
                'uso' => 'TEST_ONLY', 'aislado' => true,
                'residente_ficticio' => $base['residente_ficticio'],
                'evaluacion' => $base['evaluacion'] ?? null,
                'version' => CatalogoReglasG09Experimental::VERSION,
                'criterio' => $criterio,
                'fecha_corte' => $base['fecha_corte'] ?? null,
                'contexto_suficiente' => $resolver('contexto_suficiente'),
                'senal_contextual' => $resolver('senal_contextual'),
                'contradiccion_documentada' => $resolver('contradiccion_documentada'),
                'confusor_invalidante' => $resolver('confusor_invalidante', false),
                'evidencias' => [],
                'motivos_g08' => [],
            ];
        }
        foreach ($tareas as $tarea) {
            if (! is_array($tarea) || ! is_string($tarea['regla'] ?? null)
                || ! is_string($tarea['aplicacion'] ?? null)) {
                throw new DomainException('Plan de tareas G09 inválido.');
            }
            $regla = $tarea['regla'];
            $meta = CatalogoReglasG09Experimental::obtener($regla);
            $aplicacion = $tarea['aplicacion'];
            if (isset($ids[$aplicacion])) {
                throw new DomainException('Aplicación reutilizada indebidamente en el mismo perfil.');
            }
            $ids[$aplicacion] = true;
            $criterio = $meta['criterio'];
            $captura = $this->lector->leer($aplicacion, $base['residente_ficticio']);
            $captura['confusor_invalidante'] = $contextos[$criterio]['confusor_invalidante'];
            $declaracion = $controlesTareas[$aplicacion] ?? null;
            if ($this->exigirControlesTarea && $declaracion !== null && ! is_array($declaracion)) {
                throw new DomainException('Controles de tarea mal formados.');
            }
            $controles = $this->exigirControlesTarea ? (new ControlesTareaG09Experimental)->revisar($criterio, $declaracion) : null;
            $resultadoAdaptacion = $controles !== null && ! $controles['admisible']
                ? ['evidencias' => [], 'motivos_g08' => $controles['motivos']]
                : $this->adaptador->preparar($regla, $contextos[$criterio], $captura);
            $capturasExaminadas[] = ['regla' => $regla, 'criterio' => $criterio,
                'perfil_instrumental' => PerfilInstrumentalG08Experimental::paraRegla($regla),
                'captura_original' => $captura,
                'controles_tarea' => $controles,
                'evidencias_generadas' => array_column($resultadoAdaptacion['evidencias'], 'id'),
                'motivos_no_admision' => $resultadoAdaptacion['motivos_g08']];
            foreach ($resultadoAdaptacion['evidencias'] as $e) {
                $contextos[$criterio]['evidencias'][] = $e;
            }
            foreach ($resultadoAdaptacion['motivos_g08'] as $motivo) {
                $contextos[$criterio]['motivos_g08'][] = $aplicacion.': '.$motivo;
            }
            // La compuerta sólo se determina después de consolidar fuentes válidas.
        }
        $perfil = [];
        foreach ($contextos as $criterio => $contexto) {
            $contexto['motivos_g08'] = array_values(array_unique($contexto['motivos_g08']));
            $resultado = $this->evaluador->evaluar($contexto);
            // Comparar toda inferencia técnica contra EvaluadorReglas D-130.
            // Si divergen, abortar: nunca ocultar incoherencias con una salida única.
            $cotejo = (new VerificadorNucleoG09Experimental)->cotejar($resultado, $base);
            if ($this->conocimiento !== null) {
                $declarativa = (new EjecutorConocimientoG09Experimental)->evaluar($this->conocimiento, $base, $resultado);
                $resultado['inferencia_declarativa'] = $declarativa;
                $resultado['consecuencias_candidatas'] = $declarativa['consecuencias'];
                $resultado['estado_tecnico'] = $declarativa['estado_tecnico'];
                $cotejo = ['coincidencia' => 'EJECUCION_DECLARATIVA_D130', 'version' => $this->conocimiento->version,
                    'cotejo_catalogo_fuente' => $cotejo, 'reglas_nucleo' => count($declarativa['reglas'])];
            }
            $perfil[$criterio] = [
                'resultado' => $resultado,
                'informe' => $this->informes->construir($resultado, array_values(array_filter($capturasExaminadas,
                    static fn ($c) => $c['criterio'] === $criterio))),
                'cotejo_nucleo_d130' => $cotejo,
            ];
        }

        return [
            'uso' => 'PRUEBA_TECNICA_NO_CLINICA',
            'version' => CatalogoReglasG09Experimental::VERSION,
            'version_conocimiento' => $this->conocimiento?->version ?? CatalogoReglasG09Experimental::VERSION,
            'residente_ficticio' => $base['residente_ficticio'],
            'evaluacion' => $base['evaluacion'],
            'fecha_corte' => $base['fecha_corte'],
            'perfil' => $perfil,
            'capturas_examinadas' => $capturasExaminadas,
            'clasificacion_dcl' => null,
            'limitacion' => 'Perfil no agregado de cuatro criterios, sin diagnóstico ni validación clínica.',
        ];
    }
}
