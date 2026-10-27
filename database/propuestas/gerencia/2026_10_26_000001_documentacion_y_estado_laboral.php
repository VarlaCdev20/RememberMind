<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Diseño 35 aprobado el 26/10; fuera de migrations para no instalarlo por accidente. */
return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['sqlite', 'pgsql'], true)) {
            throw new LogicException('Motor no soportado por esta transición.');
        }
        Schema::table('usuarios', function (Blueprint $t) {
            $t->dateTime('fecha_hora_alta')->nullable();
            $t->boolean('cambio_password_pendiente')->nullable();
        });
        Schema::table('documentos', function (Blueprint $t) {
            $t->string('cod_usuario_carga', 20)->nullable()->index('ger_doc_carga_idx');
            $t->dateTime('fecha_hora_carga')->nullable();
            $t->date('fecha_emision')->nullable();
            $t->string('cod_usuario_anulacion', 20)->nullable()->index('ger_doc_anula_idx');
            $t->dateTime('fecha_hora_anulacion')->nullable();
            $t->text('motivo_anulacion')->nullable();
            $t->foreign('cod_usuario_carga', 'ger_doc_carga_fk')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
            $t->foreign('cod_usuario_anulacion', 'ger_doc_anula_fk')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });
        DB::statement('CREATE TABLE solicitudes_documentales_personal (
            cod_solicitud_documental varchar(20) PRIMARY KEY,
            rol_id bigint NOT NULL REFERENCES roles(id) ON DELETE RESTRICT,
            tipo_documento varchar(60) NOT NULL,
            nombre_documento varchar(160) NOT NULL CHECK (length(trim(nombre_documento)) > 0),
            indicaciones text NULL, motivo text NOT NULL CHECK (length(trim(motivo)) > 0),
            cod_usuario_solicitante varchar(20) NOT NULL REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT,
            fecha_hora_emision timestamp NOT NULL, clave_solicitud uuid NOT NULL UNIQUE
        )');
        DB::statement("CREATE TABLE destinatarios_solicitud_documental (
            cod_destinatario_documental varchar(20) PRIMARY KEY,
            cod_solicitud_documental varchar(20) NOT NULL REFERENCES solicitudes_documentales_personal(cod_solicitud_documental) ON DELETE RESTRICT,
            cod_personal varchar(20) NOT NULL REFERENCES personal(cod_personal) ON DELETE RESTRICT,
            origen_plazo varchar(20) NOT NULL CHECK (origen_plazo IN ('ALTA','SOLICITUD_POSTERIOR')),
            fecha_limite_original date NOT NULL, fecha_limite_prorrogada date NULL,
            cod_usuario_prorroga varchar(20) NULL REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT,
            fecha_hora_prorroga timestamp NULL, motivo_prorroga text NULL,
            UNIQUE(cod_solicitud_documental,cod_personal),
            CONSTRAINT ger_prorroga_completa CHECK (
                (fecha_limite_prorrogada IS NULL AND cod_usuario_prorroga IS NULL AND fecha_hora_prorroga IS NULL AND motivo_prorroga IS NULL)
                OR (fecha_limite_prorrogada IS NOT NULL AND cod_usuario_prorroga IS NOT NULL AND fecha_hora_prorroga IS NOT NULL AND motivo_prorroga IS NOT NULL AND length(trim(motivo_prorroga)) > 0 AND origen_plazo = 'ALTA')
            )
        )");
        DB::statement('CREATE TABLE entregas_solicitud_documental (
            cod_entrega_documental varchar(20) PRIMARY KEY,
            cod_destinatario_documental varchar(20) NOT NULL REFERENCES destinatarios_solicitud_documental(cod_destinatario_documental) ON DELETE RESTRICT,
            cod_documento varchar(20) NOT NULL REFERENCES documentos(cod_documento) ON DELETE RESTRICT,
            cod_usuario_registro varchar(20) NOT NULL REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT,
            fecha_hora_registro timestamp NOT NULL,
            UNIQUE(cod_destinatario_documental,cod_documento)
        )');
        DB::statement("CREATE TABLE movimientos_laborales_personal (
            cod_movimiento_laboral varchar(20) PRIMARY KEY,
            cod_personal varchar(20) NOT NULL REFERENCES personal(cod_personal) ON DELETE RESTRICT,
            estado_anterior varchar(20) NOT NULL CHECK (estado_anterior IN ('ACTIVO','INACTIVO')),
            estado_nuevo varchar(20) NOT NULL CHECK (estado_nuevo IN ('ACTIVO','INACTIVO') AND estado_nuevo <> estado_anterior),
            fecha_hora_efecto timestamp NOT NULL, fecha_hora_registro timestamp NOT NULL,
            cod_usuario_registro varchar(20) NOT NULL REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT,
            motivo text NOT NULL CHECK (length(trim(motivo)) > 0),
            CHECK (fecha_hora_efecto <= fecha_hora_registro)
        )");
        foreach ([
            ['solicitudes_documentales_personal', 'ger_sol_rol_idx', 'rol_id'], ['solicitudes_documentales_personal', 'ger_sol_autor_idx', 'cod_usuario_solicitante'],
            ['destinatarios_solicitud_documental', 'ger_dest_sol_idx', 'cod_solicitud_documental'], ['destinatarios_solicitud_documental', 'ger_dest_per_idx', 'cod_personal'],
            ['destinatarios_solicitud_documental', 'ger_dest_pror_idx', 'cod_usuario_prorroga'], ['destinatarios_solicitud_documental', 'ger_dest_lim_idx', 'fecha_limite_original'],
            ['entregas_solicitud_documental', 'ger_ent_dest_idx', 'cod_destinatario_documental'], ['entregas_solicitud_documental', 'ger_ent_doc_idx', 'cod_documento'],
            ['entregas_solicitud_documental', 'ger_ent_autor_idx', 'cod_usuario_registro'], ['movimientos_laborales_personal', 'ger_mov_per_idx', 'cod_personal,fecha_hora_efecto,cod_movimiento_laboral'],
            ['movimientos_laborales_personal', 'ger_mov_autor_idx', 'cod_usuario_registro'],
        ] as [$tabla,$indice,$columnas]) {
            DB::statement("CREATE INDEX {$indice} ON {$tabla} ({$columnas})");
        }
        $carga = '(cod_usuario_carga IS NULL AND fecha_hora_carga IS NULL) OR (cod_usuario_carga IS NOT NULL AND fecha_hora_carga IS NOT NULL)';
        $anulacion = "(cod_usuario_anulacion IS NULL AND fecha_hora_anulacion IS NULL AND motivo_anulacion IS NULL) OR (cod_usuario_anulacion IS NOT NULL AND fecha_hora_anulacion IS NOT NULL AND motivo_anulacion IS NOT NULL AND length(trim(motivo_anulacion)) > 0 AND estado IN ('ANULADO','ANULADA'))";
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE documentos ADD CONSTRAINT ger_doc_carga_chk CHECK ({$carga})");
            DB::statement("ALTER TABLE documentos ADD CONSTRAINT ger_doc_anula_chk CHECK ({$anulacion})");
            DB::statement('ALTER TABLE destinatarios_solicitud_documental ADD CONSTRAINT ger_prorroga_fecha CHECK (fecha_limite_prorrogada IS NULL OR fecha_limite_prorrogada = fecha_limite_original + 7)');
        } else {
            foreach (['INSERT', 'UPDATE'] as $evento) {
                foreach (['carga' => $carga, 'anula' => $anulacion] as $nombre => $condicion) {
                    $condicion = preg_replace('/\b(cod_usuario_carga|fecha_hora_carga|cod_usuario_anulacion|fecha_hora_anulacion|motivo_anulacion|estado)\b/', 'NEW.$1', $condicion);
                    DB::unprepared("CREATE TRIGGER ger_doc_{$nombre}_".strtolower($evento)." BEFORE {$evento} ON documentos WHEN NOT ({$condicion}) BEGIN SELECT RAISE(ABORT,'Datos documentales incompletos'); END");
                }
                DB::unprepared('CREATE TRIGGER ger_prorroga_'.strtolower($evento)." BEFORE {$evento} ON destinatarios_solicitud_documental WHEN NEW.fecha_limite_prorrogada IS NOT NULL AND date(NEW.fecha_limite_prorrogada) <> date(NEW.fecha_limite_original,'+7 days') BEGIN SELECT RAISE(ABORT,'Prorroga distinta de siete dias'); END");
            }
        }
    }

    public function down(): void
    {
        foreach (['solicitudes_documentales_personal', 'destinatarios_solicitud_documental', 'entregas_solicitud_documental', 'movimientos_laborales_personal'] as $tabla) {
            if (DB::table($tabla)->exists()) {
                throw new LogicException('Se conserva historia: la reversión poblada requiere transición explícita.');
            }
        }
        if (DB::table('usuarios')->whereNotNull('fecha_hora_alta')->orWhereNotNull('cambio_password_pendiente')->exists()
            || DB::table('documentos')->whereNotNull('cod_usuario_carga')->orWhereNotNull('fecha_emision')->orWhereNotNull('cod_usuario_anulacion')->exists()) {
            throw new LogicException('Se conservan datos nuevos: no se eliminan columnas pobladas.');
        }
        if (DB::getDriverName() === 'sqlite') {
            foreach (['insert', 'update'] as $e) {
                foreach (['ger_doc_carga_', 'ger_doc_anula_', 'ger_prorroga_'] as $prefijo) {
                    DB::unprepared("DROP TRIGGER {$prefijo}{$e}");
                }
            }
        } else {
            DB::statement('ALTER TABLE documentos DROP CONSTRAINT ger_doc_carga_chk, DROP CONSTRAINT ger_doc_anula_chk');
        }
        foreach (['entregas_solicitud_documental', 'destinatarios_solicitud_documental', 'solicitudes_documentales_personal', 'movimientos_laborales_personal'] as $tabla) {
            Schema::drop($tabla);
        }
        Schema::table('documentos', function (Blueprint $t) {
            $t->dropForeign(DB::getDriverName() === 'sqlite' ? ['cod_usuario_carga'] : 'ger_doc_carga_fk');
            $t->dropForeign(DB::getDriverName() === 'sqlite' ? ['cod_usuario_anulacion'] : 'ger_doc_anula_fk');
            $t->dropIndex('ger_doc_carga_idx');
            $t->dropIndex('ger_doc_anula_idx');
            $t->dropColumn(['cod_usuario_carga', 'fecha_hora_carga', 'fecha_emision', 'cod_usuario_anulacion', 'fecha_hora_anulacion', 'motivo_anulacion']);
        });
        Schema::table('usuarios', fn (Blueprint $t) => $t->dropColumn(['fecha_hora_alta', 'cambio_password_pendiente']));
    }
};
