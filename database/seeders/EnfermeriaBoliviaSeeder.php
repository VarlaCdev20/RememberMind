<?php

namespace Database\Seeders;

use App\Models\Personal;
use App\Models\User;
use App\Modulos\Identidad\Dominio\Reglas\DatosPersonalesTrabajador;
use App\Modulos\Identidad\Dominio\Reglas\IdentificacionInternacionalPersonal;
use Database\Seeders\LaPaz\ConservacionDatos;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Throwable;

/** Alta optativa de cuentas sintéticas para la instalación local. */
final class EnfermeriaBoliviaSeeder extends Seeder
{
    private const DOMINIO = '@losalmendros.invalid';

    private const EVENTO_ALTA = 'alta_personal_sintetico';

    private const ARCHIVO = 'censo-sintetico/accesos-enfermeria-bolivia.json';

    private const PERFILES = [
        ['NATALIA', 'QUISPE', 'CONDORI', 'F', '1989-03-14', 'LP', 'LA PAZ', 'CUIDADOS GERIÁTRICOS'],
        ['MATEO', 'APAZA', 'HUANCA', 'M', '1993-07-22', 'LP', 'EL ALTO', 'MOVILIDAD Y PREVENCIÓN DE CAÍDAS'],
        ['JIMENA', 'ROJAS', 'VARGAS', 'F', '1987-11-08', 'CB', 'COCHABAMBA', 'ENFERMERÍA COMUNITARIA'],
        ['DIEGO', 'FLORES', 'TÓRREZ', 'M', '1995-05-19', 'SC', 'SANTA CRUZ DE LA SIERRA', 'CUIDADOS DE ENFERMERÍA'],
        ['ELIANA', 'CHOQUE', 'ARUQUIPA', 'F', '1991-09-26', 'OR', 'ORURO', 'CUIDADOS GERIÁTRICOS'],
        ['SEBASTIÁN', 'MENDOZA', 'LIMA', 'M', '1986-02-11', 'PT', 'POTOSÍ', 'ENFERMERÍA COMUNITARIA'],
        ['MARIANA', 'VILLCA', 'SALINAS', 'F', '1997-01-31', 'CH', 'SUCRE', 'CUIDADOS DE ENFERMERÍA'],
        ['ANDREA', 'ARCE', 'GUZMÁN', 'F', '1990-06-17', 'TJ', 'TARIJA', 'CUIDADOS GERIÁTRICOS'],
    ];

    public function run(): void
    {
        $conexion = DB::connection();
        if (! app()->environment('local') || $conexion->getDriverName() !== 'pgsql'
            || $conexion->getDatabaseName() !== 'remembermind_dev'
            || ! in_array($conexion->getConfig('host'), ['127.0.0.1', 'localhost', '::1'], true)) {
            throw new RuntimeException('Este seeder requiere PostgreSQL local y la base remembermind_dev.');
        }

        $disco = Storage::disk('local');
        $archivoCreado = false;

        try {
            $creados = DB::transaction(function () use ($disco, &$archivoCreado): int {
                DB::select('select pg_advisory_xact_lock(?)', [2026111208]);
                $rol = Role::findByName('ENFERMEROS', 'web');
                $conservacion = new ConservacionDatos(['usuarios', 'personal']);
                $conservacion->capturar();
                $accesos = [];

                foreach (self::PERFILES as $i => [$nombres, $paterno, $materno, $genero, $nacimiento, $expedido, $ciudad, $especialidad]) {
                    $numero = $i + 1;
                    $correo = Str::slug($nombres.' '.$paterno, '.').self::DOMINIO;
                    $documento = (string) (99862100 + $numero);
                    $telefono = sprintf('%d9901%03d', 6 + $i % 2, $numero);
                    $datos = Validator::make([
                        'nombres' => $nombres, 'ap_paterno' => $paterno, 'ap_materno' => $materno,
                        'fecha_nacimiento' => $nacimiento, 'pais_documento' => 'BO', 'tipo_documento' => 'CI',
                        'pais_telefono' => 'BO', 'numero_documento' => $documento,
                        'expedido' => $expedido, 'telefono' => $telefono,
                    ], [
                        ...DatosPersonalesTrabajador::reglasNombre(),
                        'fecha_nacimiento' => DatosPersonalesTrabajador::reglaNacimiento(),
                        ...IdentificacionInternacionalPersonal::reglas('BO', 'CI', 'BO'),
                    ])->validate();

                    $cuentaPorCorreo = User::query()->where('correo', $correo)->first();
                    $identidad = Personal::query()->where('numero_documento', $documento)->first();
                    if ($cuentaPorCorreo || $identidad) {
                        $usuario = $identidad?->usuario;
                        if (! $usuario || ! $identidad || $usuario->personal?->getKey() !== $identidad->getKey()
                            || ($cuentaPorCorreo && $cuentaPorCorreo->getKey() !== $usuario->getKey())
                            || $usuario->getRoleNames()->all() !== ['ENFERMEROS']
                            || ! DB::table('activity_log')->where('event', self::EVENTO_ALTA)
                                ->where('subject_type', $identidad->getMorphClass())
                                ->where('subject_id', $identidad->getKey())->exists()) {
                            throw new RuntimeException('Hay una colisión con una cuenta o ficha del lote.');
                        }
                        continue;
                    }

                    $clave = bin2hex(random_bytes(20));
                    $usuario = new User(['correo' => $correo, 'password' => $clave, 'estado' => 'ACTIVO']);
                    $usuario->forceFill(['fecha_hora_alta' => now(), 'cambio_password_pendiente' => true])->save();
                    $usuario->assignRole($rol);

                    $personal = Personal::create([
                        'cod_usuario' => $usuario->getKey(), 'nombres' => $datos['nombres'],
                        'apellido_paterno' => $datos['ap_paterno'], 'apellido_materno' => $datos['ap_materno'],
                        'numero_documento' => $datos['numero_documento'], 'expedicion_documento' => $datos['expedido'],
                        'pais_documento' => 'BO', 'tipo_documento' => 'CI', 'pais_telefono' => 'BO',
                        'fecha_nacimiento' => $datos['fecha_nacimiento'], 'genero' => $genero,
                        'telefono' => IdentificacionInternacionalPersonal::telefonoInternacional('BO', $datos['telefono']),
                        'direccion' => $ciudad.', BOLIVIA',
                        'profesion' => 'ENFERMEROS', 'especialidad' => $especialidad,
                        'fecha_ingreso' => today()->toDateString(), 'estado' => 'ACTIVO',
                    ]);
                    activity('Desarrollo')->performedOn($personal)->event(self::EVENTO_ALTA)
                        ->log('Alta local de personal sintético de Enfermería.');
                    $accesos[] = ['correo' => $correo, 'contrasena_temporal' => $clave];
                }

                $conservacion->comprobar();
                if ($accesos !== [] && count($accesos) !== count(self::PERFILES)) {
                    throw new RuntimeException('El lote existe parcialmente. Se revirtió la carga.');
                }
                if ($accesos !== []) {
                    if ($disco->exists(self::ARCHIVO)) {
                        throw new RuntimeException('Ya existe el archivo privado de accesos para este lote.');
                    }
                    $archivoCreado = true;
                    if (! $disco->put(self::ARCHIVO, json_encode($accesos,
                        JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), 'private')) {
                        throw new RuntimeException('No se pudo guardar el archivo privado de accesos.');
                    }
                }

                return count($accesos);
            });
        } catch (Throwable $error) {
            if ($archivoCreado && ! $disco->delete(self::ARCHIVO)) {
                report(new RuntimeException('No se pudo retirar el archivo privado de la carga revertida.'));
            }
            throw $error;
        }

        $this->command?->info($creados.' perfiles nuevos. El lote contiene ocho cuentas de Enfermería.');
    }
}
