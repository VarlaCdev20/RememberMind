<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Acciones\DeteccionAlertasService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class VerificarVigilanciaAlertas extends Command
{
    protected $signature = 'alertas:verificar-vigilancia {--preparar-cuenta : Prepara la identidad técnica sin revisar registros}';

    protected $description = 'Revisa omisiones de medicación registradas con autoría técnica institucional.';

    public function handle(DeteccionAlertasService $detector): int
    {
        if ($this->option('preparar-cuenta')) {
            $cuenta = User::whereRaw('LOWER(correo) = ?', [User::CORREO_VIGILANCIA_ALERTAS])->first()
                ?? User::firstOrCreate(['correo' => User::CORREO_VIGILANCIA_ALERTAS], [
                    'contrasena' => Hash::make(Str::random(64)),
                    'estado' => 'INACTIVO',
                ]);

            if (! $cuenta->esCuentaTecnicaVigilancia()) {
                $this->error('La identidad reservada existe con atributos incompatibles. Revisa la cuenta técnica.');

                return self::FAILURE;
            }

            $this->info('Identidad técnica preparada sin acceso interactivo.');

            return self::SUCCESS;
        }

        $inicio = config('vigilancia_alertas.desde');
        if (Validator::make(['inicio' => $inicio], ['inicio' => 'required|date_format:Y-m-d H:i:s'])->fails()) {
            $this->error('Configura una fecha de inicio válida en VIGILANCIA_ALERTAS_DESDE.');

            return self::FAILURE;
        }

        try {
            $creadas = $detector->vigilarOmisiones(CarbonImmutable::parse($inicio, config('app.timezone')));
        } catch (HttpException $error) {
            if ($error->getStatusCode() !== 403) {
                throw $error;
            }
            $this->error('La vigilancia requiere la identidad técnica preparada y una ejecución sin sesión.');

            return self::FAILURE;
        }

        $this->info("Alertas de omisión creadas: {$creadas}.");

        return self::SUCCESS;
    }
}
