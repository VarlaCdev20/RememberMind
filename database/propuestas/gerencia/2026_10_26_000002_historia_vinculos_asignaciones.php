<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Propuesta 37: solo ensayos desechables hasta aprobación estructural expresa.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE TABLE versiones_vinculo_contacto (
            cod_version_vinculo varchar(20) PRIMARY KEY,
            cod_residente_contacto varchar(20) NOT NULL REFERENCES residentes_contactos(cod_residente_contacto) ON DELETE RESTRICT,
            numero_version integer NOT NULL CHECK(numero_version>0),
            cod_version_anterior varchar(20) NULL,
            parentesco varchar(40) NOT NULL,
            responsable_principal boolean NOT NULL, contacto_emergencia boolean NOT NULL,
            autoriza_informacion boolean NOT NULL, autoriza_salida boolean NOT NULL,
            estado varchar(20) NOT NULL, observacion text NULL,
            cod_usuario_acto varchar(20) NULL REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT,
            fecha_hora_acto timestamp NULL, motivo text NULL,
            fecha_hora_conservacion timestamp NOT NULL,
            UNIQUE(cod_residente_contacto,numero_version), UNIQUE(cod_residente_contacto,cod_version_vinculo),
            FOREIGN KEY(cod_residente_contacto,cod_version_anterior) REFERENCES versiones_vinculo_contacto(cod_residente_contacto,cod_version_vinculo) ON DELETE RESTRICT,
            CHECK ((cod_usuario_acto IS NULL AND fecha_hora_acto IS NULL AND motivo IS NULL) OR
                (cod_usuario_acto IS NOT NULL AND fecha_hora_acto IS NOT NULL AND motivo IS NOT NULL AND length(trim(motivo))>0)),
            CHECK ((numero_version=1 AND cod_version_anterior IS NULL) OR (numero_version>1 AND cod_version_anterior IS NOT NULL))
        )');
        DB::statement('CREATE INDEX ger_vinculo_autor_idx ON versiones_vinculo_contacto(cod_usuario_acto,fecha_hora_acto)');
        DB::statement("CREATE TABLE eventos_asignacion_personal (
            cod_evento_asignacion varchar(20) PRIMARY KEY,
            cod_asignacion_personal varchar(20) NOT NULL REFERENCES asignaciones_personal(cod_asignacion_personal) ON DELETE RESTRICT,
            numero_evento integer NOT NULL CHECK(numero_evento>0),
            cod_evento_anterior varchar(20) NULL,
            tipo_evento varchar(20) NOT NULL CHECK(tipo_evento IN ('ALTA','FINALIZACION','CANCELACION','RECTIFICACION')),
            estado_anterior varchar(20) NULL, estado_nuevo varchar(20) NOT NULL,
            fecha_hora_efecto timestamp NOT NULL, fecha_hora_registro timestamp NOT NULL,
            cod_usuario_registro varchar(20) NOT NULL REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT,
            motivo text NOT NULL CHECK(length(trim(motivo))>0),
            UNIQUE(cod_asignacion_personal,numero_evento), UNIQUE(cod_asignacion_personal,cod_evento_asignacion),
            FOREIGN KEY(cod_asignacion_personal,cod_evento_anterior) REFERENCES eventos_asignacion_personal(cod_asignacion_personal,cod_evento_asignacion) ON DELETE RESTRICT,
            CHECK ((numero_evento=1 AND cod_evento_anterior IS NULL) OR (numero_evento>1 AND cod_evento_anterior IS NOT NULL))
        )");
        DB::statement('CREATE INDEX ger_asig_evento_actor_idx ON eventos_asignacion_personal(cod_usuario_registro,fecha_hora_registro)');
        $cadenas = [
            ['versiones_vinculo_contacto', 'cod_residente_contacto', 'cod_version_vinculo', 'cod_version_anterior', 'numero_version'],
            ['eventos_asignacion_personal', 'cod_asignacion_personal', 'cod_evento_asignacion', 'cod_evento_anterior', 'numero_evento'],
        ];
        foreach ($cadenas as [$tabla, $propietario, $clave, $anterior, $numero]) {
            $padre = "SELECT 1 FROM {$tabla} p WHERE p.{$propietario}=NEW.{$propietario} AND p.{$clave}=NEW.{$anterior} AND p.{$numero}=NEW.{$numero}-1";
            if (DB::getDriverName() === 'pgsql') {
                DB::unprepared("CREATE OR REPLACE FUNCTION ger_secuencia_{$tabla}() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN IF NEW.{$numero}>1 AND NOT EXISTS ({$padre}) THEN RAISE EXCEPTION 'La secuencia histórica requiere el anterior inmediato'; END IF; RETURN NEW; END; \$\$");
                DB::statement("CREATE TRIGGER ger_secuencia BEFORE INSERT ON {$tabla} FOR EACH ROW EXECUTE FUNCTION ger_secuencia_{$tabla}()");
            } else {
                DB::unprepared("CREATE TRIGGER ger_{$tabla}_secuencia BEFORE INSERT ON {$tabla} WHEN NEW.{$numero}>1 AND NOT EXISTS ({$padre}) BEGIN SELECT RAISE(ABORT,'La secuencia histórica requiere el anterior inmediato'); END");
            }
        }
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared("CREATE OR REPLACE FUNCTION ger_historia_inmutable() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN RAISE EXCEPTION 'La historia nominal es inmutable'; END; \$\$");
            foreach (['versiones_vinculo_contacto', 'eventos_asignacion_personal'] as $tabla) {
                DB::statement("CREATE TRIGGER ger_historia_inmutable BEFORE UPDATE OR DELETE ON {$tabla} FOR EACH ROW EXECUTE FUNCTION ger_historia_inmutable()");
            }
        } else {
            foreach (['versiones_vinculo_contacto', 'eventos_asignacion_personal'] as $tabla) {
                foreach (['UPDATE', 'DELETE'] as $operacion) {
                    DB::unprepared("CREATE TRIGGER ger_{$tabla}_".strtolower($operacion)." BEFORE {$operacion} ON {$tabla} BEGIN SELECT RAISE(ABORT,'La historia nominal es inmutable'); END");
                }
            }
        }
    }

    public function down(): void
    {
        foreach (['versiones_vinculo_contacto', 'eventos_asignacion_personal'] as $tabla) {
            if (DB::table($tabla)->exists()) {
                throw new LogicException('La historia poblada no se elimina mediante rollback.');
            }
        }
        Schema::drop('eventos_asignacion_personal');
        Schema::drop('versiones_vinculo_contacto');
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP FUNCTION ger_historia_inmutable()');
            DB::statement('DROP FUNCTION ger_secuencia_versiones_vinculo_contacto()');
            DB::statement('DROP FUNCTION ger_secuencia_eventos_asignacion_personal()');
        }
    }
};
