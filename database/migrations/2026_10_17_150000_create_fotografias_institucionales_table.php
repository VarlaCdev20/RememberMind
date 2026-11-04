<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        // SQL estándar para que CHECK también sea una garantía física en SQLite.
        DB::statement("CREATE TABLE fotografias_institucionales (
            cod_fotografia VARCHAR(20) NOT NULL PRIMARY KEY,
            cod_usuario VARCHAR(20) NOT NULL REFERENCES usuarios(cod_usuario) ON UPDATE RESTRICT ON DELETE RESTRICT,
            titulo VARCHAR(160) NOT NULL CHECK (length(trim(titulo)) BETWEEN 1 AND 160),
            descripcion TEXT NULL,
            fecha_fotografia DATE NULL,
            texto_alternativo VARCHAR(255) NULL,
            ruta_archivo VARCHAR(500) NOT NULL UNIQUE CHECK (length(ruta_archivo) <= 500),
            ruta_miniatura VARCHAR(500) NOT NULL UNIQUE CHECK (length(ruta_miniatura) <= 500),
            tipo_archivo VARCHAR(80) NOT NULL,
            tamanio_bytes BIGINT NOT NULL CHECK (tamanio_bytes > 0),
            ancho INTEGER NOT NULL CHECK (ancho > 0),
            alto INTEGER NOT NULL CHECK (alto > 0),
            hash_archivo CHAR(64) NOT NULL UNIQUE CHECK (length(hash_archivo) = 64),
            estado VARCHAR(20) NOT NULL DEFAULT 'ACTIVA' CHECK (estado IN ('ACTIVA', 'ARCHIVADA')),
            usar_en_bienvenida BOOLEAN NOT NULL DEFAULT FALSE,
            fecha_registro TIMESTAMP NOT NULL,
            fecha_actualizacion TIMESTAMP NOT NULL
        )");
        DB::statement('CREATE INDEX fotografias_autor_idx ON fotografias_institucionales (cod_usuario)');
        DB::statement('CREATE INDEX fotografias_galeria_idx ON fotografias_institucionales (estado, fecha_registro, cod_fotografia)');
        foreach (['ver', 'gestionar'] as $accion) {
            // La concesión es explícita: el observer global no debe ampliar estos permisos.
            $permiso = Permission::withoutEvents(fn () => Permission::firstOrCreate([
                'name' => 'fotografias_institucionales.'.$accion, 'guard_name' => 'web',
            ]));
            foreach (['GERENTE', 'ADMINISTRADOR'] as $nombre) {
                Role::where('name', $nombre)->where('guard_name', 'web')->first()?->givePermissionTo($permiso);
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('fotografias_institucionales');
        Permission::whereIn('name', ['fotografias_institucionales.ver', 'fotografias_institucionales.gestionar'])->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
