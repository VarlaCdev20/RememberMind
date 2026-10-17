<?php
declare(strict_types=1);

namespace MotorExperto\investigacion\g09;

use DomainException;

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

    public function __construct(private readonly LectorSQLiteG08Experimental $lector)
    {
        $this->adaptador = new AdaptadorInstrumentalG08G09Experimental();
        $this->evaluador = new EvaluadorG09Experimental();
        $this->informes = new ConstructorInformeG09Experimental();
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
            || !is_string($base['residente_ficticio'] ?? null)
            || !str_starts_with($base['residente_ficticio'], 'SINTETICO-')) {
            throw new DomainException('El perfil no admite residentes o versiones reales.');
        }
        $ids = [];
        $contextos = [];
        $motivos = [];
        foreach (['COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'] as $criterio) {
            // No copiar evidencia introducida por el llamador.
            $contextos[$criterio] = [
                'uso' => 'TEST_ONLY', 'aislado' => true,
                'residente_ficticio' => $base['residente_ficticio'],
                'evaluacion' => $base['evaluacion'] ?? null,
                'version' => CatalogoReglasG09Experimental::VERSION,
                'criterio' => $criterio,
                'fecha_corte' => $base['fecha_corte'] ?? null,
                'contexto_suficiente' => ($base['contexto_suficiente'] ?? false) === true,
                'senal_contextual' => false,
                'evidencias' => [],
                'motivos_g08' => [],
            ];
        }
        foreach ($tareas as $tarea) {
            if (!is_array($tarea) || !is_string($tarea['regla'] ?? null)
                || !is_string($tarea['aplicacion'] ?? null)) {
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
            $resultadoAdaptacion = $this->adaptador->preparar($regla, $contextos[$criterio], $captura);
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
            $perfil[$criterio] = [
                'resultado' => $resultado,
                'informe' => $this->informes->construir($resultado),
            ];
        }
        return [
            'uso' => 'PRUEBA_TECNICA_NO_CLINICA',
            'version' => CatalogoReglasG09Experimental::VERSION,
            'residente_ficticio' => $base['residente_ficticio'],
            'evaluacion' => $base['evaluacion'],
            'fecha_corte' => $base['fecha_corte'],
            'perfil' => $perfil,
            'clasificacion_dcl' => null,
            'limitacion' => 'Perfil no agregado de cuatro criterios, sin diagnóstico ni validación clínica.',
        ];
    }
}
