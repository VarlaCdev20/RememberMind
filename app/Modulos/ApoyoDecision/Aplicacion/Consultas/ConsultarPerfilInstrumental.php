<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Dominio\Puertos\FuenteConocimientoOrion;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;
use MotorExperto\dominio\conocimiento\PerfilConceptualCognitivo;
use MotorExperto\dominio\instrumentos\PerfilInstrumentalCognitivo;

/** Orquesta fuentes institucionales autorizadas y la red documental; no usa casos del laboratorio. */
final readonly class ConsultarPerfilInstrumental
{
    public function __construct(private ConsultarConocimientoCognitivo $conocimiento) {}

    public function consultar(User $usuario, Residente $residente, CarbonImmutable $corte): ?array
    {
        $consulta = $this->conocimiento->consultar($usuario, $residente, $corte);
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
        if (isset($consulta['resultado']['version']) && Gate::forUser($cuenta)->allows('consultarOriginales', [ValoracionPetersenOriginal::class, $residente])) {
            $perfil['petersen_institucional'] = app(ConsultarPetersenInstitucional::class)->consultar($cuenta, $residente, $corte, $consulta['resultado']['version']);
        }

        return $perfil;
    }
}
