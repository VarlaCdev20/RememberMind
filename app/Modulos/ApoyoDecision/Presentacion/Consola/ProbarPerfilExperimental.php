<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Consola;

use App\Modulos\ApoyoDecision\Aplicacion\Servicios\EscenariosPerfilExperimental;
use Illuminate\Console\Command;
use MotorExperto\aplicacion\casos_uso\EjecutarPerfilExperimental;
use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental;

/** Laboratorio sin acceso a Models ni conexión institucional; no admite códigos de residentes ni datos del expediente. */
final class ProbarPerfilExperimental extends Command
{
    protected $signature = 'experto:probar-perfil {--escenario=sin-referencias : sin-referencias, memoria, memoria-atencion, ejecutivo, atencion-ejecutivo, incompleto, contradictorio, diferencial o iadl-incompleta} {--conocimiento-g09= : Archivo JSON declarativo TEST_ONLY; usar sin-referencias} {--json : Mostrar contrato estructurado completo}';

    protected $description = 'Ejecuta cinco dominios con fuentes ficticias en SQLite en memoria, sin guardar en expedientes';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('El laboratorio está disponible únicamente en desarrollo o pruebas.');

            return self::FAILURE;
        }
        $escenario = $this->option('escenario');
        if (! is_string($escenario) || ! array_key_exists($escenario, EscenariosPerfilExperimental::NOMBRES)) {
            $this->error('Indique uno de los escenarios de laboratorio descritos en --help.');

            return self::INVALID;
        }
        $caso = EscenariosPerfilExperimental::entrada($escenario);
        try {
            if ($this->option('conocimiento-g09') !== null) {
                if ($caso['referencias'] !== null) {
                    throw new \DomainException('El paquete alternativo exige referencias independientes de su versión; use sin-referencias o la entrada integral explícita.');
                }
                $caso['conocimientoG09'] = LectorDeclaracionExperimental::leer($this->option('conocimiento-g09'));
                $version = $caso['conocimientoG09']['version'] ?? null;
                if (! is_string($version) || (! str_starts_with($version, 'SIM-')
                    && $version !== CatalogoReglasG09Experimental::VERSION)) {
                    throw new \DomainException('El laboratorio sin Models solo admite versiones ficticias no registradas; use archivo-perfil con revision-g09 para SQL.');
                }
            }
            $resultado = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        } catch (\DomainException|\InvalidArgumentException|\JsonException $e) {
            $this->error($e instanceof \JsonException ? 'El archivo declarativo no contiene JSON válido.' : $e->getMessage());

            return self::FAILURE;
        }
        if ($this->option('json')) {
            $this->line(json_encode($resultado, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->warn($resultado['advertencia']);
            $perfil = $resultado['perfil_experimental'];
            $this->line('Caso: '.$perfil['residente_ficticio'].'; evaluación: '.$perfil['evaluacion'].'; corte: '.$perfil['fecha_corte']);
            $filas = [];
            foreach ($perfil['perfil'] as $dominio => $r) {
                $salida = $r['salida'];
                $filas[] = [$dominio, $r['version'], $dominio === 'COG-MEM'
                    ? ($salida['resultado']['codigo'] ?? $salida['evaluabilidad']['estado'])
                    : $salida['resultado']['estado_tecnico']];
            }
            $this->table(['Dominio', 'Versión', 'Salida del motor'], $filas);
            $this->line('Petersen experimental: '.$perfil['clasificacion_dcl']['estado']);
            $this->line('Los estados DCL proceden de referencias ficticias independientes; no de los hallazgos descriptivos.');
            $this->line('Ejecución en memoria; no se guardó ninguna evaluación en el expediente.');
        }

        return self::SUCCESS;
    }
}
