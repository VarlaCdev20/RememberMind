<?php

namespace App\Modulos\Cuidados\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\ArchivosAplicacion;
use App\Models\CuracionHerida;
use App\Models\DocumentoClinico;
use App\Models\Herida;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

final class PersistirFotosHerida
{
    public function __construct(private readonly ArchivosAplicacion $archivos, private readonly AutorizacionClinicaService $autorizacion) {}

    public function validar(array $fotografias, array $descripciones = []): void
    {
        Validator::make(compact('fotografias', 'descripciones'), [
            'fotografias' => ['array', 'max:'.config('fotografias_clinicas.max_archivos')],
            'fotografias.*' => ['required', 'file', 'image', 'mimetypes:'.implode(',', array_keys(config('fotografias_clinicas.formatos'))), 'max:'.config('fotografias_clinicas.max_kb')],
            'descripciones' => ['array', 'max:'.config('fotografias_clinicas.max_archivos')],
            'descripciones.*' => ['nullable', 'string', 'max:'.config('fotografias_clinicas.descripcion_max')],
        ])->validate();
        foreach ($fotografias as $indice => $archivo) {
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($archivo->getRealPath());
            if (! array_key_exists($mime, config('fotografias_clinicas.formatos')) || ! is_array(@getimagesize($archivo->getRealPath()))) {
                throw ValidationException::withMessages(['fotografias.'.$indice => 'El archivo debe contener una fotografía JPG, PNG o WEBP válida.']);
            }
        }
    }

    /** El llamador autoriza antes de crear; esta frontera revalida el contexto antes de almacenar. */
    public function conRegistro(User $usuario, array $fotografias, array $descripciones, callable $crear): Herida|CuracionHerida
    {
        $this->validar($fotografias, $descripciones);
        if ($fotografias === []) {
            return DB::transaction($crear);
        }
        if (DB::transactionLevel() > 0 && app()->environment() !== 'testing') {
            throw new RuntimeException('El registro con archivos debe controlar su transacción exterior.');
        }
        $rutas = [];
        $disco = config('fotografias_clinicas.disk');
        try {
            return DB::transaction(function () use ($usuario, $fotografias, $descripciones, $crear, &$rutas, $disco) {
                $registro = $crear();
                $herida = $registro instanceof Herida ? $registro : $registro->herida;
                $permiso = $registro instanceof Herida ? 'heridas.crear' : 'curaciones_herida.crear';
                if ($fotografias !== []) {
                    Gate::forUser($usuario)->authorize('adjuntar-evidencia-herida', [$herida->residente, $permiso]);
                }
                $personal = $this->autorizacion->autorizarMutacion($usuario, $herida->residente, $permiso,
                    $registro instanceof Herida ? ['MEDICO GENERAL/GERIATRA', 'ENFERMEROS'] : ['ENFERMEROS']);
                foreach ($fotografias as $i => $archivo) {
                    $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($archivo->getRealPath());
                    $extension = config('fotografias_clinicas.formatos')[$mime] ?? throw new RuntimeException('Formato no permitido.');
                    $carpeta = 'clinical/residents/'.$herida->cod_residente.'/wounds/'.$herida->cod_herida;
                    if ($registro instanceof CuracionHerida) {
                        $carpeta .= '/curations/'.$registro->cod_curacion;
                    }
                    $ruta = $carpeta.'/'.Str::uuid().'.'.$extension;
                    // Registrar antes de escribir permite compensar una escritura parcial.
                    $rutas[] = $ruta;
                    $guardada = $this->archivos->recibirComo($archivo, $carpeta, basename($ruta), $disco);
                    if ($guardada !== $ruta || ! $this->archivos->existe($disco, $ruta)) {
                        throw new RuntimeException('No se pudo guardar la fotografía.');
                    }
                    $hash = $this->archivos->hash($disco, $ruta);
                    if (! is_string($hash) || strlen($hash) !== 64) {
                        throw new RuntimeException('No se pudo verificar la fotografía.');
                    }
                    $documento = DocumentoClinico::create([
                        'cod_documento_clinico' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('documentos_clinicos'),
                        'cod_residente' => $herida->cod_residente, 'cod_personal' => $personal->cod_personal,
                        'cod_herida' => $registro instanceof Herida ? $herida->cod_herida : null,
                        'cod_curacion' => $registro instanceof CuracionHerida ? $registro->cod_curacion : null,
                        'tipo_documento' => 'FOTO_HERIDA', 'titulo' => 'Fotografía de herida - '.$herida->ubicacion,
                        'descripcion' => filled($descripciones[$i] ?? null) ? trim($descripciones[$i]) : null,
                        'ruta_archivo' => $ruta, 'formato' => $mime, 'tamano_bytes' => $archivo->getSize(),
                        'hash_archivo' => $hash, 'fecha_hora' => now(), 'origen' => $registro instanceof Herida ? 'HERIDA' : 'CURACION_HERIDA', 'estado' => 'VIGENTE',
                    ]);
                    activity('evidencia_heridas')->causedBy($usuario)->performedOn($documento)->log('Fotografía clínica registrada');
                }

                return $registro;
            });
        } catch (Throwable $error) {
            foreach ($rutas as $ruta) {
                try {
                    if (! $this->archivos->eliminar($disco, $ruta)) {
                        report(new RuntimeException('No se pudo compensar un archivo de evidencia clínica.'));
                    }
                } catch (Throwable $compensacion) {
                    report($compensacion);
                }
            }
            throw $error;
        }
    }
}
