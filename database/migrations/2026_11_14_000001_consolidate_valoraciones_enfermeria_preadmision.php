<?php

use App\Modulos\Valoraciones\Infraestructura\Persistencia\BloqueValoracionInicial as Bloque;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PREFIJO = 'pre_bve_';

    private const OBLIGATORIAS = ['cod_usuario_registro', 'cod_personal_valorador', 'hay_dolor', 'hay_heridas', 'confirmacion_documentacion'];

    private const REVISIONES = [
        'dolor' => '(intensidad_dolor IS NULL OR intensidad_dolor BETWEEN 0 AND 10) AND (NOT hay_dolor OR intensidad_dolor BETWEEN 1 AND 10)',
        'pa' => '(pa_sistolica IS NULL OR pa_sistolica BETWEEN 1 AND 400) AND (pa_diastolica IS NULL OR pa_diastolica BETWEEN 1 AND 400) AND (pa_sistolica IS NULL OR pa_diastolica IS NULL OR pa_sistolica > pa_diastolica)',
        'fc' => 'frecuencia_cardiaca IS NULL OR frecuencia_cardiaca BETWEEN 1 AND 300',
        'fr' => 'frecuencia_respiratoria IS NULL OR frecuencia_respiratoria BETWEEN 1 AND 100',
        'temperatura' => 'temperatura IS NULL OR temperatura BETWEEN 25 AND 45',
        'saturacion' => 'saturacion_oxigeno IS NULL OR saturacion_oxigeno BETWEEN 0 AND 100',
        'peso' => 'peso IS NULL OR peso BETWEEN 20 AND 300',
        'talla' => 'talla IS NULL OR talla BETWEEN 50 AND 240',
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            $this->bloquear(true);
            $this->verificarOrigen();
            foreach (Bloque::TIPOS as $campo => $tipo) {
                DB::statement('ALTER TABLE '.Bloque::TABLA.' ADD COLUMN '.Bloque::columna($campo).' '.$tipo.$this->referencia($campo));
            }
            DB::statement('CREATE UNIQUE INDEX pre_bve_codigo_u ON preadmisiones (cod_valoracion_enfermeria)');
            DB::statement('CREATE INDEX pre_bve_usuario_i ON preadmisiones (valoracion_cod_usuario_registro)');
            DB::statement('CREATE INDEX pre_bve_personal_i ON preadmisiones (valoracion_cod_personal_valorador)');
            $asignaciones = [];
            foreach (Bloque::TIPOS as $campo => $_) {
                $asignaciones[] = Bloque::columna($campo).' = v.'.$campo;
            }
            DB::statement('UPDATE preadmisiones SET '.implode(', ', $asignaciones)
                .' FROM valoraciones_enfermeria_preadmision v WHERE preadmisiones.cod_preadmision = v.cod_preadmision');
            $this->comprobarCopia();
            $this->instalarReglas();
            Schema::drop(Bloque::ORIGEN);
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $this->bloquear(false);
            $definiciones = [
                'cod_valoracion_enfermeria varchar(20) NOT NULL PRIMARY KEY',
                'cod_preadmision varchar(20) NOT NULL'.(DB::getDriverName() === 'pgsql' ? ' UNIQUE' : '').' REFERENCES preadmisiones(cod_preadmision) ON DELETE RESTRICT',
            ];
            foreach (Bloque::TIPOS as $campo => $tipo) {
                if ($campo === Bloque::CODIGO) {
                    continue;
                }

                $regla = match (true) {
                    in_array($campo, ['hay_dolor', 'hay_heridas', 'confirmacion_documentacion'], true) => ' NOT NULL DEFAULT FALSE',
                    in_array($campo, self::OBLIGATORIAS, true) => ' NOT NULL',
                    default => '',
                };
                $definiciones[] = $campo.' '.$tipo.$regla.$this->referencia($campo, restaurar: true);
            }
            DB::statement('CREATE TABLE '.Bloque::ORIGEN.' ('.implode(', ', $definiciones).')');
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('CREATE UNIQUE INDEX valoracion_inicial_preadmision_u ON '.Bloque::ORIGEN.' (cod_preadmision)');
            }
            $indice = DB::getDriverName() === 'pgsql'
                ? 'valoraciones_enfermeria_preadmision_cod_personal_valorador_inde'
                : 'valoraciones_enfermeria_preadmision_cod_personal_valorador_index';
            DB::statement('CREATE INDEX '.$indice.' ON '.Bloque::ORIGEN.' (cod_personal_valorador)');
            foreach (self::REVISIONES as $nombre => $expresion) {
                $this->instalarRegla(Bloque::ORIGEN, 'ck_val_enf_'.$nombre, $expresion, array_keys(Bloque::TIPOS));
            }
            DB::statement('INSERT INTO '.Bloque::ORIGEN.' (cod_preadmision, '.implode(', ', array_keys(Bloque::TIPOS)).') '.$this->seleccionConsolidada());
            $this->comprobarCopia();
            $this->retirarReglas();

            foreach (['pre_bve_codigo_u', 'pre_bve_usuario_i', 'pre_bve_personal_i'] as $indice) {
                DB::statement('DROP INDEX '.$indice);
            }
            foreach (Bloque::columnas() as $columna) {
                DB::statement('ALTER TABLE preadmisiones DROP COLUMN '.$columna);
            }
        });
    }

    private function bloquear(bool $subir): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SET LOCAL lock_timeout = '5s'");
            DB::statement('LOCK TABLE preadmisiones'.($subir ? ', '.Bloque::ORIGEN : '').' IN ACCESS EXCLUSIVE MODE');
        } elseif (DB::getDriverName() !== 'sqlite') {
            throw new RuntimeException('La consolidación requiere PostgreSQL o SQLite.');
        }
    }

    private function verificarOrigen(): void
    {
        $origen = Schema::getColumnListing(Bloque::ORIGEN);
        $esperadas = ['cod_preadmision', ...array_keys(Bloque::TIPOS)];
        if (array_diff($origen, $esperadas) || array_diff($esperadas, $origen)) {
            throw new RuntimeException('El esquema de la valoración de Enfermería cambió. Revisar antes de trasladarlo.');
        }

        foreach (Schema::getTables() as $tabla) {
            foreach (Schema::getForeignKeys($tabla['name']) as $referencia) {
                if ($referencia['foreign_table'] === Bloque::ORIGEN) {
                    throw new RuntimeException('Una tabla referencia la valoración original. Se requiere una transición explícita.');
                }
            }
        }
    }

    private function referencia(string $campo, bool $restaurar = false): string
    {
        $fk = match ($campo) {
            'cod_usuario_registro' => ' REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT',
            'cod_personal_valorador' => ' REFERENCES personal(cod_personal) ON DELETE RESTRICT',
            default => '',
        };

        return $restaurar && $fk !== ''
            ? ' CONSTRAINT '.Bloque::ORIGEN.'_'.$campo.'_foreign'.$fk
            : $fk;
    }

    private function seleccionConsolidada(): string
    {
        return 'SELECT cod_preadmision, '.implode(', ', Bloque::columnas())
            .' FROM preadmisiones WHERE cod_valoracion_enfermeria IS NOT NULL';
    }

    private function comprobarCopia(): void
    {
        $original = 'SELECT cod_preadmision, '.implode(', ', array_keys(Bloque::TIPOS)).' FROM '.Bloque::ORIGEN;
        $consolidada = $this->seleccionConsolidada();
        foreach ([[$original, $consolidada], [$consolidada, $original]] as [$a, $b]) {
            if (DB::scalar("SELECT EXISTS (".$a." EXCEPT ".$b.")")) {
                throw new RuntimeException('La comparación de valoraciones difiere. Se revierte la transición.');
            }
        }
    }

    private function reglas(): array
    {
        $nulas = array_diff(Bloque::columnas(), [Bloque::CODIGO]);
        $requeridas = array_map(Bloque::columna(...), self::OBLIGATORIAS);
        $reglas = ['presencia' => '(cod_valoracion_enfermeria IS NULL AND '.implode(' IS NULL AND ', $nulas).' IS NULL)'
            .' OR (cod_valoracion_enfermeria IS NOT NULL AND '.implode(' IS NOT NULL AND ', $requeridas).' IS NOT NULL)'];
        foreach (self::REVISIONES as $nombre => $expresion) {
            $reglas[$nombre] = preg_replace_callback('/[a-z_]+/', fn ($m) => isset(Bloque::TIPOS[$m[0]]) ? Bloque::columna($m[0]) : $m[0], $expresion);
        }

        return $reglas;
    }

    private function instalarReglas(): void
    {
        foreach ($this->reglas() as $nombre => $expresion) {
            $this->instalarRegla(Bloque::TABLA, self::PREFIJO.$nombre, $expresion, Bloque::columnas());
        }
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
                CREATE FUNCTION pre_bve_preservar() RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN
                    IF TG_OP = 'DELETE' THEN
                        RAISE EXCEPTION 'La preadmisión conserva una valoración de Enfermería' USING ERRCODE = '23503';
                    ELSIF NEW.cod_valoracion_enfermeria IS DISTINCT FROM OLD.cod_valoracion_enfermeria
                       OR NEW.cod_preadmision IS DISTINCT FROM OLD.cod_preadmision THEN
                        RAISE EXCEPTION 'La identidad de la valoración de Enfermería no puede cambiar' USING ERRCODE = '23514';
                    END IF;
                    RETURN NEW;
                END;
                $$;
                CREATE TRIGGER pre_bve_preservar BEFORE UPDATE OR DELETE ON preadmisiones
                FOR EACH ROW WHEN (OLD.cod_valoracion_enfermeria IS NOT NULL) EXECUTE FUNCTION pre_bve_preservar();
                SQL);
        } else {
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER pre_bve_preservar_del BEFORE DELETE ON preadmisiones
                FOR EACH ROW WHEN OLD.cod_valoracion_enfermeria IS NOT NULL
                BEGIN SELECT RAISE(ABORT, 'La preadmisión conserva una valoración de Enfermería'); END;
                CREATE TRIGGER pre_bve_preservar_upd BEFORE UPDATE ON preadmisiones
                FOR EACH ROW WHEN OLD.cod_valoracion_enfermeria IS NOT NULL
                    AND (NEW.cod_valoracion_enfermeria IS NOT OLD.cod_valoracion_enfermeria
                         OR NEW.cod_preadmision IS NOT OLD.cod_preadmision)
                BEGIN SELECT RAISE(ABORT, 'La identidad de la valoración de Enfermería no puede cambiar'); END;
                SQL);
        }
    }

    private function instalarRegla(string $tabla, string $nombre, string $expresion, array $columnas): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE '.$tabla.' ADD CONSTRAINT '.$nombre.' CHECK ('.$expresion.')');

            return;
        }

        $condicion = preg_replace_callback('/[a-z_]+/', fn ($m) => in_array($m[0], $columnas, true) ? 'NEW.'.$m[0] : $m[0], $expresion);
        $prefijo = $tabla === Bloque::ORIGEN ? 'trg_' : '';
        foreach (['INSERT' => 'ins', 'UPDATE' => 'upd'] as $evento => $sufijo) {
            DB::unprepared('CREATE TRIGGER '.$prefijo.$nombre.'_'.$sufijo.' BEFORE '.$evento.' ON '.$tabla
                ." FOR EACH ROW WHEN NOT ($condicion) BEGIN SELECT RAISE(ABORT, 'Valoración inicial inválida: $nombre'); END");
        }
    }

    private function retirarReglas(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP TRIGGER pre_bve_preservar ON preadmisiones');
            DB::statement('DROP FUNCTION pre_bve_preservar()');
            foreach (array_keys($this->reglas()) as $nombre) {
                DB::statement('ALTER TABLE preadmisiones DROP CONSTRAINT '.self::PREFIJO.$nombre);
            }
        } else {
            DB::statement('DROP TRIGGER pre_bve_preservar_del');
            DB::statement('DROP TRIGGER pre_bve_preservar_upd');
            foreach (array_keys($this->reglas()) as $nombre) {
                foreach (['ins', 'upd'] as $sufijo) {
                    DB::statement('DROP TRIGGER '.self::PREFIJO.$nombre.'_'.$sufijo);
                }
            }
        }
    }
};
