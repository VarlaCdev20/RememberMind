<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratosConocimiento;
use App\Modulos\ApoyoDecision\Dominio\Puertos\FuenteConocimientoOrion;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;
use MotorExperto\dominio\conocimiento\PerfilConceptualCognitivo;
use MotorExperto\dominio\instrumentos\PerfilInstrumentalCognitivo;
use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional;
use Symfony\Component\HttpKernel\Exception\HttpException;

/** Orquesta fuentes institucionales autorizadas y la red documental; no usa casos del laboratorio. */
final readonly class ConsultarPerfilInstrumental
{
    public function __construct(private ConsultarConocimientoCognitivo $conocimiento) {}

    /** Proyección de la ejecución compuesta ya adquirida y autorizada; no vuelve a consultar fuentes. */
    public function desdePreparacion(InstantaneaEvaluacionInstitucional $evaluacion): array
    {
        $instrumental = $evaluacion->contenido['instrumental'];
        $salida = $instrumental['contenido']['salida'];
        $perfil = $salida['perfil_instrumental'];
        $perfil['inferencia_declarativa'] = [
            'disponible' => true,
            'resultado' => $salida['resultado'],
            'fuentes_excluidas' => $salida['fuentes_excluidas'],
            'preparacion_conservacion' => ['contrato' => $instrumental['contenido']['contrato'],
                'huella' => $instrumental['huella'], 'persistido' => false, 'release_recuperable' => false],
            'persistido' => false,
            'revision_profesional' => 'PENDIENTE',
        ];
        $perfil['petersen_institucional'] = $evaluacion->contenido['petersen']['contenido']['salida'];
        $perfil['evaluacion_institucional'] = ['contrato' => $evaluacion->contenido['contrato'],
            'huella' => $evaluacion->huella, 'persistido' => false];

        return $perfil;
    }

    /** Consulta de Enfermería: organiza mediciones autorizadas sin ejecutar inferencia. */
    public function consultarMediciones(User $usuario, Residente $residente, CarbonImmutable $corte): ?array
    {
        try {
            $capturas = app(CapturasInstrumentalesResidente::class)->consultar($usuario, $residente, $corte);
        } catch (HttpException $e) {
            return null;
        }
        if (! $capturas['disponible']) {
            return null;
        }
        $red = (new PerfilConceptualCognitivo)->construir(app(FuenteConocimientoOrion::class)->leer());

        return (new PerfilInstrumentalCognitivo)->construir($red, $capturas);
    }

    public function consultar(User $usuario, Residente $residente, CarbonImmutable $corte): ?array
    {
        $consulta = $this->conocimiento->adquirir($usuario, $residente, $corte);
        $instantanea = $consulta['_instantanea'] ?? null;
        unset($consulta['_instantanea']);
        $captura = $consulta['capturas'];
        if (! $captura['disponible']) {
            return null;
        }
        // Se muestra exactamente el perfil reproducible de la misma captura y revisión.
        // Sin versión vinculada se conserva la consulta instrumental previa, sin inferencia.
        if (isset($consulta['perfil_instrumental'])) {
            $perfil = $consulta['perfil_instrumental'];
        } else {
            $red = (new PerfilConceptualCognitivo)->construir(app(FuenteConocimientoOrion::class)->leer());
            $perfil = (new PerfilInstrumentalCognitivo)->construir($red, $captura);
        }
        unset($consulta['capturas'], $consulta['perfil_instrumental']);
        $perfil['inferencia_declarativa'] = $consulta;
        $cuenta = User::query()->findOrFail($usuario->getKey());
        $versionParaPetersen = $consulta['resultado']['version']
            ?? app(ContratosConocimiento::class)->versionPetersenVigente($corte)
            ?? app(ContratosConocimiento::class)->versionInstitucionalVigente($corte);

        if ($versionParaPetersen && Gate::forUser($cuenta)->allows('consultarOriginales', [ValoracionPetersenOriginal::class, $residente])) {
            // Petersen interpreta exactamente las medidas ya adquiridas por esta consulta.
            // Un fallo de adquisición debe llegar al manejador de la pantalla, nunca aparentar éxito.
            $perfil['petersen_institucional'] = app(ConsultarPetersenInstitucional::class)
                ->preparar($cuenta, $residente, $corte, $versionParaPetersen, $instantanea)->contenido['salida'];
        }

        return $perfil;
    }
}
