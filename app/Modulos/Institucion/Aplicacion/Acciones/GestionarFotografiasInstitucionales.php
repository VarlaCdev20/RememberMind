<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Models\FotografiaInstitucional;
use App\Models\User;
use App\Modulos\Institucion\Aplicacion\Contratos\ArchivosFotografias;
use App\Modulos\Institucion\Aplicacion\Contratos\GestionFotografias;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GestionarFotografiasInstitucionales
{
    public function __construct(private ArchivosFotografias $archivos, private GestionFotografias $policy) {}

    private function autorizar(?User $usuario): User
    {
        $actor = $usuario ? User::find($usuario->getKey()) : null;
        abort_unless($this->policy->manage($actor), 403);
        return $actor;
    }

    private function metadatos(array $entrada): array
    {
        $entrada['titulo'] = trim((string) ($entrada['titulo'] ?? ''));
        return Validator::make($entrada, ['titulo' => ['required', 'string', 'max:160'],
            'descripcion' => ['nullable', 'string', 'max:2000'], 'fecha_fotografia' => ['nullable', 'date_format:Y-m-d'],
            'texto_alternativo' => ['nullable', 'string', 'max:255'], 'usar_en_bienvenida' => ['required', 'boolean']])->validate();
    }

    /** @return array{foto: FotografiaInstitucional, nueva: bool} */
    public function subir(?User $usuario, object $archivo, array $entrada): array
    {
        $actor = $this->autorizar($usuario);
        $datos = $this->metadatos($entrada);
        Validator::make(['archivo' => $archivo], ['archivo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']])->validate();
        $preparada = $this->archivos->preparar($archivo);
        $rutas = [$preparada['ruta_archivo'], $preparada['ruta_miniatura']];
        try {
            $existente = FotografiaInstitucional::where('hash_archivo', $preparada['hash_archivo'])->first();
            if ($existente) {
                $this->archivos->compensar($rutas);
                return ['foto' => $existente, 'nueva' => false];
            }
            $foto = DB::transaction(function () use ($actor, $datos, $preparada) {
                $actor = $this->autorizar($actor);
                $foto = FotografiaInstitucional::create([...$datos, ...$preparada, 'cod_usuario' => $actor->getKey(),
                    'estado' => 'ACTIVA', 'fecha_registro' => now(), 'fecha_actualizacion' => now()]);
                activity('Galería institucional')->causedBy($actor)->performedOn($foto)->event('fotografia_subida')
                    ->withProperties(['hash' => $foto->hash_archivo])->log('Guardó una fotografía institucional');
                return $foto;
            });
            return ['foto' => $foto, 'nueva' => true];
        } catch (\Throwable $error) {
            try { $this->archivos->compensar($rutas); } catch (\Throwable $limpieza) { report($limpieza); }
            if ($error instanceof UniqueConstraintViolationException) {
                $existente = FotografiaInstitucional::where('hash_archivo', $preparada['hash_archivo'])->first();
                if ($existente) return ['foto' => $existente, 'nueva' => false];
            }
            throw $error;
        }
    }

    public function editar(?User $usuario, string $codigo, array $entrada): FotografiaInstitucional
    {
        $actor = $this->autorizar($usuario);
        $datos = $this->metadatos($entrada);
        return DB::transaction(function () use ($actor, $codigo, $datos) {
            $foto = FotografiaInstitucional::whereKey($codigo)->lockForUpdate()->firstOrFail();
            $antes = $foto->only(array_keys($datos));
            $foto->update([...$datos, 'fecha_actualizacion' => now()]);
            activity('Galería institucional')->causedBy($actor)->performedOn($foto)->event('fotografia_editada')
                ->withProperties(['antes' => $antes, 'despues' => $datos])->log('Editó los datos de una fotografía');
            return $foto;
        });
    }

    public function estado(?User $usuario, string $codigo, string $estado): void
    {
        $actor = $this->autorizar($usuario);
        Validator::make(['estado' => $estado], ['estado' => ['required', 'in:ACTIVA,ARCHIVADA']])->validate();
        DB::transaction(function () use ($actor, $codigo, $estado) {
            $foto = FotografiaInstitucional::whereKey($codigo)->lockForUpdate()->firstOrFail();
            if ($foto->estado === $estado) return;
            $anterior = $foto->estado;
            $foto->update(['estado' => $estado, 'fecha_actualizacion' => now()]);
            activity('Galería institucional')->causedBy($actor)->performedOn($foto)->event('fotografia_estado')
                ->withProperties(['antes' => $anterior, 'despues' => $estado])->log('Cambió el estado de una fotografía');
        });
    }
}
