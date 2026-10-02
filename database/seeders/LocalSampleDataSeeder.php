<?php

namespace Database\Seeders;

use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Models\Preadmision;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;

/**
 * Datos ficticios y reproducibles para recorrer los 70 módulos de la BDD V2.1.
 * Se ejecuta solo de forma explícita; nunca forma parte del seeder predeterminado.
 */
class LocalSampleDataSeeder extends Seeder
{
    private const CASE_COUNT = 20;

    private const TABLES = [
        'usuarios', 'personal', 'areas', 'turnos', 'jornadas', 'asignaciones_personal',
        'preadmisiones', 'valoraciones_enfermeria_preadmision', 'contactos',
        'residentes_contactos', 'admisiones', 'residentes', 'historial_estados_residente',
        'habitaciones', 'camas', 'ocupaciones_cama', 'documentos', 'consentimientos',
        'atenciones', 'notas_clinicas', 'antecedentes_clinicos', 'diagnosticos', 'alergias',
        'seguros_residente', 'dispositivos_clinicos', 'signos_vitales', 'valoraciones_dolor',
        'mediciones_antropometricas', 'tipos_estudio_clinico', 'componentes_estudio',
        'estudios_clinicos', 'resultados_estudio', 'informes_estudio', 'documentos_clinicos',
        'derivaciones', 'incidentes', 'indicaciones_clinicas', 'asignaciones_residente_jornada',
        'controles_cognitivos', 'registros_conductuales', 'registros_sueno',
        'registros_ingesta', 'registros_hidratacion', 'registros_eliminacion',
        'registros_movilidad', 'heridas', 'curaciones_herida', 'pases_turno', 'planes_cuidado',
        'intervenciones_cuidado', 'programaciones_cuidado', 'ejecuciones_cuidado',
        'medicamentos', 'prescripciones', 'horarios_prescripcion',
        'administraciones_medicacion', 'instrumentos', 'preguntas_instrumento',
        'opciones_pregunta', 'aplicaciones_instrumento', 'respuestas_instrumento',
        'valoraciones_psicologicas', 'valoraciones_nutricionales',
        'valoraciones_funcionales', 'seguimientos_pedagogicos', 'actividades',
        'participantes_actividad', 'visitas', 'alertas', 'eventos_alerta',
    ];

    private const CREATED_BY_ADMISSION = [
        'residentes', 'admisiones', 'residentes_contactos',
        'ocupaciones_cama', 'historial_estados_residente', 'consentimientos',
        'seguros_residente',
    ];

    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) || DB::getDriverName() !== 'pgsql') {
            throw new RuntimeException('La carga ficticia requiere PostgreSQL en local o testing.');
        }

        if (count(self::TABLES) !== 70 || count(array_unique(self::TABLES)) !== 70) {
            throw new RuntimeException('El inventario de seeders debe coincidir con las 70 tablas operativas V2.1.');
        }

        if (! Role::query()->where('name', 'ADMINISTRADOR')->where('guard_name', 'web')->exists()
            || ! Role::query()->where('name', 'ENFERMEROS')->where('guard_name', 'web')->exists()) {
            throw new RuntimeException('Ejecute primero php artisan db:seed para instalar roles y permisos.');
        }

        Storage::disk('local')->put('fixtures-local/resumen.txt', 'Archivo ficticio para validación local.');

        DB::transaction(function (): void {
            for ($case = 1; $case <= self::CASE_COUNT; $case++) {
                $refs = $this->seedBase($case);
                $this->seedAdmission($refs);

                foreach (self::TABLES as $index => $table) {
                    if (isset($refs[$table]) || in_array($table, self::CREATED_BY_ADMISSION, true)) {
                        continue;
                    }

                    $pk = $this->primaryKey($table);
                    $id = sprintf('FIC_%02d', $index + 1);
                    if ($case > 1) {
                        $id .= sprintf('_%03d', $case);
                    }
                    $refs[$table] = $id;

                    if (DB::table($table)->where($pk, $id)->exists()) {
                        continue;
                    }

                    $row = [$pk => $id];
                    foreach ($this->requiredColumns($table) as $column => $type) {
                        if ($column === $pk) {
                            continue;
                        }

                        $parent = $this->foreignTable($table, $column);
                        $row[$column] = $parent !== null
                            ? ($refs[$parent] ?? throw new RuntimeException("Falta el padre {$parent} de {$table}.{$column}."))
                            : $this->valueFor($table, $column, $type, $index, $case);
                    }

                    $row = array_merge($row, $this->extraValues($table, $refs, $case));
                    $columns = array_flip(array_column(DB::select(
                        'select column_name from information_schema.columns where table_schema = current_schema() and table_name = ?',
                        [$table]
                    ), 'column_name'));
                    DB::table($table)->insert(array_intersect_key($row, $columns));
                }
            }

            foreach (self::TABLES as $table) {
                if (DB::table($table)->count() < self::CASE_COUNT) {
                    throw new RuntimeException("La tabla {$table} quedó con menos de 20 registros.");
                }
            }
        });

        $this->command?->info('Las 70 tablas operativas tienen al menos 20 registros ficticios para local/testing.');
    }

    private function seedBase(int $case): array
    {
        $serial = sprintf('%03d', $case);
        $first = sprintf('%03d', $case * 2 - 1);
        $second = sprintf('%03d', $case * 2);
        $userCode = 'USU_FIC_001';
        if (! DB::table('usuarios')->where('cod_usuario', $userCode)->exists()) {
            DB::table('usuarios')->insert([
                'cod_usuario' => $userCode,
                'correo' => 'equipo.ficticio@remembermind.test',
                'contrasena' => Hash::make(Str::password(32)),
                'estado' => 'ACTIVO',
            ]);
        }

        $user = User::query()->findOrFail($userCode);
        if ($user->correo !== 'equipo.ficticio@remembermind.test') {
            throw new RuntimeException('El código del usuario ficticio pertenece a otra cuenta.');
        }
        if (! $user->hasRole('ADMINISTRADOR')) {
            $user->assignRole('ADMINISTRADOR');
        }

        $nurseCode = sprintf('USU_FIC_%03d', $case + 1);
        $nurseEmail = $case === 1
            ? 'enfermeria.ficticia@remembermind.test'
            : sprintf('enfermeria.ficticia.%02d@remembermind.test', $case);
        if (! DB::table('usuarios')->where('cod_usuario', $nurseCode)->exists()) {
            DB::table('usuarios')->insert([
                'cod_usuario' => $nurseCode,
                'correo' => $nurseEmail,
                'contrasena' => Hash::make(Str::password(32)),
                'estado' => 'ACTIVO',
            ]);
        }
        $nurse = User::query()->findOrFail($nurseCode);
        if ($nurse->correo !== $nurseEmail) {
            throw new RuntimeException('El código de enfermería ficticia pertenece a otra cuenta.');
        }
        if (! $nurse->hasRole('ENFERMEROS')) {
            $nurse->assignRole('ENFERMEROS');
        }

        $base = [
            'personal' => ['cod_personal' => 'PER_FIC_'.$serial, 'cod_usuario' => $nurseCode,
                'nombres' => 'ELENA', 'apellido_paterno' => 'QUISPE',
                'numero_documento' => 'FIC-PER-'.$serial, 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO'],
            'areas' => ['cod_area' => 'ARE_FIC_'.$first,
                'nombre' => $case === 1 ? 'Cuidados de muestra' : 'Cuidados ficticios '.$serial,
                'estado' => 'ACTIVA'],
            'turnos' => ['cod_turno' => 'TUR_FIC_'.$first,
                'nombre' => $case === 1 ? 'Turno de mañana' : 'Turno ficticio '.$first,
                'hora_inicio' => '07:00:00', 'hora_cierre' => '15:00:00', 'orden' => 1, 'estado' => 'ACTIVO'],
            'jornadas' => ['cod_jornada' => 'JOR_FIC_'.$first, 'cod_turno' => 'TUR_FIC_'.$first,
                'fecha_jornada' => today()->toDateString(), 'estado' => 'ABIERTA'],
            'asignaciones_personal' => ['cod_asignacion_personal' => 'ASP_FIC_'.$serial,
                'cod_jornada' => 'JOR_FIC_'.$first, 'cod_personal' => 'PER_FIC_'.$serial,
                'cod_area' => 'ARE_FIC_'.$first, 'tipo_asignacion' => 'TITULAR',
                'fecha_asignacion' => now(), 'estado' => 'ACTIVA'],
            'contactos' => ['cod_contacto' => 'CTO_FIC_'.$serial, 'nombres' => 'LUCÍA',
                'apellido_paterno' => 'ROJAS', 'estado' => 'ACTIVO'],
            'habitaciones' => ['cod_habitacion' => 'HAB_FIC_'.$serial,
                'codigo' => sprintf('FIC-%03d', $case + 100),
                'capacidad' => 1, 'estado' => 'ACTIVA'],
            'camas' => ['cod_cama' => 'CAM_FIC_'.$serial, 'cod_habitacion' => 'HAB_FIC_'.$serial,
                'codigo' => sprintf('FIC-%03d-A', $case + 100), 'estado' => 'ACTIVA'],
            'preadmisiones' => ['cod_preadmision' => 'PRE_FIC_'.$serial,
                'cod_usuario_registro' => $userCode, 'cod_contacto' => 'CTO_FIC_'.$serial,
                'nombres' => 'MARTA', 'apellido_paterno' => 'ROJAS',
                'fecha_nacimiento' => '1944-05-14', 'numero_documento' => 'FIC-RES-'.$serial,
                'motivo_ingreso' => 'Solicitud ficticia para comprobar el flujo de ingreso.',
                'fecha_solicitud' => now()->subDay(), 'estado' => 'PENDIENTE'],
            'valoraciones_enfermeria_preadmision' => [
                'cod_valoracion_enfermeria' => 'VEN_FIC_'.$serial,
                'cod_preadmision' => 'PRE_FIC_'.$serial, 'cod_usuario_registro' => $nurseCode,
                'cod_personal_valorador' => 'PER_FIC_'.$serial, 'hay_dolor' => false,
                'pa_sistolica' => 120, 'pa_diastolica' => 80, 'frecuencia_cardiaca' => 72,
                'frecuencia_respiratoria' => 16, 'temperatura' => 36.5,
                'saturacion_oxigeno' => 97, 'peso' => 64, 'talla' => 160,
            ],
        ];

        foreach ($base as $table => $row) {
            $pk = $this->primaryKey($table);
            if (! DB::table($table)->where($pk, $row[$pk])->exists()) {
                DB::table($table)->insert($row);
            }
        }

        $samplePersonal = DB::table('personal')->where('cod_personal', 'PER_FIC_'.$serial)->first();
        if (! $samplePersonal || ! in_array($samplePersonal->cod_usuario, [$userCode, $nurseCode], true)
            || $samplePersonal->numero_documento !== 'FIC-PER-'.$serial) {
            throw new RuntimeException('El código de personal ficticio pertenece a otra identidad.');
        }
        DB::table('personal')->where('cod_personal', 'PER_FIC_'.$serial)
            ->update(['cod_usuario' => $nurseCode]);

        DB::table('areas')->insertOrIgnore(['cod_area' => 'ARE_FIC_'.$second,
            'nombre' => $case === 1 ? 'Rehabilitación de muestra' : 'Rehabilitación ficticia '.$serial,
            'estado' => 'ACTIVA']);
        DB::table('turnos')->insertOrIgnore(['cod_turno' => 'TUR_FIC_'.$second,
            'nombre' => $case === 1 ? 'Turno de tarde' : 'Turno ficticio '.$second,
            'hora_inicio' => '15:00:00',
            'hora_cierre' => '23:00:00', 'orden' => 2, 'estado' => 'ACTIVO']);
        DB::table('jornadas')->insertOrIgnore(['cod_jornada' => 'JOR_FIC_'.$second,
            'cod_turno' => 'TUR_FIC_'.$second, 'fecha_jornada' => today()->toDateString(),
            'estado' => 'PROGRAMADA']);

        return array_map(static fn (array $row): string => reset($row), $base)
            + ['usuarios' => $userCode, 'area_receptora' => 'ARE_FIC_'.$second,
                'jornada_entrante' => 'JOR_FIC_'.$second];
    }

    private function seedAdmission(array &$refs): void
    {
        $solicitud = Preadmision::query()->findOrFail($refs['preadmisiones']);
        $admision = $solicitud->admision;
        $residente = $admision?->residente;

        if (! $residente) {
            if ($solicitud->estado === 'PENDIENTE') {
                $solicitud->update(['estado' => 'APROBADA']);
            }
            $residente = app(FormalizarAdmision::class)->ejecutar($solicitud, [
                'cod_cama' => $refs['camas'],
                'cod_contacto' => $refs['contactos'],
                'parentesco' => 'HIJA',
                'seguro_entidad' => 'Cobertura ficticia local',
                'observacion' => 'Registro ficticio exclusivo de desarrollo local.',
            ], User::query()->findOrFail($refs['usuarios']));
            $admision = $residente->admisiones()->where('cod_preadmision', $solicitud->cod_preadmision)->firstOrFail();
        }

        $refs['residentes'] = $residente->cod_residente;
        $refs['admisiones'] = $admision->cod_admision;
        foreach (['residentes_contactos', 'ocupaciones_cama', 'historial_estados_residente',
            'consentimientos', 'seguros_residente'] as $table) {
            $pk = $this->primaryKey($table);
            $refs[$table] = DB::table($table)->where('cod_residente', $residente->cod_residente)->value($pk)
                ?? throw new RuntimeException("La admisión no creó {$table}.");
        }
    }

    private function primaryKey(string $table): string
    {
        $column = DB::selectOne('select a.attname as name from pg_index i '
            .'join pg_attribute a on a.attrelid = i.indrelid and a.attnum = i.indkey[0] '
            .'where i.indrelid = ?::regclass and i.indisprimary', [$table]);

        return $column?->name ?? throw new RuntimeException("Sin PK: {$table}.");
    }

    private function requiredColumns(string $table): array
    {
        $rows = DB::select("select column_name, data_type from information_schema.columns
            where table_schema = current_schema() and table_name = ?
            and is_nullable = 'NO' and column_default is null", [$table]);

        return array_column($rows, 'data_type', 'column_name');
    }

    private function foreignTable(string $table, string $column): ?string
    {
        $row = DB::selectOne('select c.confrelid::regclass::text as parent from pg_constraint c '
            .'join pg_attribute a on a.attrelid = c.conrelid and a.attnum = c.conkey[1] '
            .'where c.contype = ? and c.conrelid = ?::regclass and a.attname = ?',
            ['f', $table, $column]);

        return $row?->parent;
    }

    private function valueFor(string $table, string $column, string $type, int $index, int $case): mixed
    {
        return match ($column) {
            'estado' => in_array($table, ['atenciones', 'actividades', 'prescripciones',
                'planes_cuidado', 'programaciones_cuidado'], true) ? 'ACTIVA' : 'ACTIVO',
            'fecha', 'fecha_activacion' => today()->toDateString(),
            'fecha_hora', 'fecha_hora_apertura', 'fecha_hora_prescripcion',
            'fecha_hora_identificacion', 'fecha_solicitud' => now(),
            'hora_inicio' => '07:00:00', 'hora_cierre' => '15:00:00',
            'hora_programada' => '08:00:00',
            'orden', 'capacidad' => 1,
            'cantidad_ml' => 200,
            'requiere_componentes', 'requiere_informe', 'control_especial',
            'segun_necesidad', 'requiere_medico', 'requiere_derivacion' => false,
            'codigo' => sprintf('FIC-%02d-%03d', $index + 1, $case),
            'nombre' => ($this->names()[$table] ?? 'Registro ficticio local').' '.$case,
            'titulo' => 'Registro ficticio de seguimiento',
            'categoria' => 'GENERAL',
            'tipo' => $this->types()[$table] ?? 'GENERAL',
            'tipo_documento' => 'INFORME',
            'tipo_nota' => 'EVOLUCION',
            'tipo_atencion' => 'CONTROL',
            'tipo_antecedente' => 'PERSONAL',
            'tipo_estudio' => 'LABORATORIO',
            'tipo_resultado' => 'NUMERICO',
            'tipo_incidente' => 'OBSERVACION',
            'tipo_indicacion' => 'CUIDADO',
            'tipo_herida' => 'SUPERFICIAL',
            'tipo_plan' => 'GENERAL',
            'tipo_comida' => 'ALMUERZO',
            'tipo_eliminacion' => 'URINARIA',
            'tipo_respuesta' => 'OPCION_UNICA',
            'tipo_evento' => 'REGISTRO',
            'frecuencia' => 'DIARIA',
            'via_administracion' => 'ORAL',
            'resultado' => 'ADMINISTRADA',
            'prioridad' => 'MEDIA',
            'generacion' => 'MANUAL',
            'origen' => 'INTERNO',
            'formato' => 'TXT',
            'tipo_archivo' => 'text/plain',
            'ruta_archivo' => 'fixtures-local/resumen.txt',
            'hash_archivo' => hash('sha256', 'Archivo ficticio para validación local.'),
            'nombre_generico' => 'Medicamento ficticio sin uso clínico',
            'sustancia' => 'Sin sustancia confirmada',
            'entidad' => 'Cobertura ficticia local',
            'ubicacion' => 'Antebrazo derecho',
            'contenido', 'descripcion', 'procedimiento', 'motivo', 'resumen',
            'objetivo_general', 'enunciado' => 'Registro ficticio para verificar la interfaz en desarrollo local.',
            default => match ($type) {
                'boolean' => false,
                'date' => today()->toDateString(),
                'timestamp without time zone' => now(),
                'time without time zone' => '08:00:00',
                'integer', 'smallint', 'bigint' => 1,
                'numeric', 'double precision', 'real' => 1,
                default => throw new RuntimeException("Defina {$table}.{$column} antes de sembrar."),
            },
        };
    }

    private function extraValues(string $table, array $refs, int $case): array
    {
        return match ($table) {
            'documentos' => ['nombre' => 'Resumen institucional ficticio',
                'ruta_archivo' => 'fixtures-local/resumen.txt', 'tipo_archivo' => 'text/plain',
                'hash_archivo' => hash('sha256', 'Archivo ficticio para validación local.')],
            'notas_clinicas' => ['contenido' => 'Seguimiento ficticio de estado general estable.'],
            'antecedentes_clinicos' => ['descripcion' => 'Antecedente ficticio sin relevancia asistencial.'],
            'diagnosticos' => ['nombre' => 'Valoración ficticia de control'],
            'alergias' => ['sustancia' => 'Sin sustancia confirmada'],
            'seguros_residente' => ['entidad' => 'Cobertura ficticia local'],
            'dispositivos_clinicos' => ['tipo' => 'APOYO_MOVILIDAD'],
            'signos_vitales' => ['pa_sistolica' => 120, 'pa_diastolica' => 80,
                'frecuencia_cardiaca' => 72, 'frecuencia_respiratoria' => 16,
                'temperatura' => 36.5, 'saturacion_oxigeno' => 97],
            'valoraciones_dolor' => ['intensidad' => 0],
            'mediciones_antropometricas' => ['peso' => 64, 'talla' => 160],
            'tipos_estudio_clinico' => ['nombre' => 'Control básico ficticio '.$case],
            'componentes_estudio' => ['nombre' => 'Indicador general'],
            'informes_estudio' => ['origen' => 'INTERNO'],
            'documentos_clinicos' => ['titulo' => 'Informe ficticio',
                'ruta_archivo' => 'fixtures-local/resumen.txt',
                'hash_archivo' => hash('sha256', 'Archivo ficticio para validación local.')],
            'derivaciones' => ['cod_area_receptora' => $refs['area_receptora'],
                'motivo' => 'Revisión ficticia interdisciplinaria.'],
            'incidentes' => ['descripcion' => 'Evento ficticio sin daño.'],
            'pases_turno' => ['cod_jornada_entrante' => $refs['jornada_entrante'],
                'resumen' => 'Entrega ficticia de turno para comprobación local.'],
            'planes_cuidado' => ['nombre' => 'Plan ficticio de bienestar',
                'objetivo_general' => 'Comprobar el registro de cuidados.'],
            'intervenciones_cuidado' => ['nombre' => 'Acompañamiento ficticio',
                'descripcion' => 'Actividad de prueba local.'],
            'medicamentos' => ['nombre_generico' => 'Medicamento ficticio sin uso clínico'],
            'prescripciones' => ['segun_necesidad' => false],
            'administraciones_medicacion' => ['resultado' => 'ADMINISTRADA'],
            'instrumentos' => ['nombre' => 'Instrumento ficticio local '.$case],
            'preguntas_instrumento' => ['enunciado' => '¿Se completó la observación ficticia?'],
            'opciones_pregunta' => ['nombre' => 'Sí'],
            'alertas' => ['titulo' => 'Alerta ficticia de revisión',
                'descripcion' => 'Aviso local para comprobar el seguimiento de alertas.'],
            default => [],
        };
    }

    private function names(): array
    {
        return [
            'actividades' => 'Taller ficticio de memoria',
            'instrumentos' => 'Escala ficticia de observación',
            'medicamentos' => 'Medicamento ficticio sin uso clínico',
            'planes_cuidado' => 'Plan ficticio de bienestar',
            'intervenciones_cuidado' => 'Acompañamiento ficticio',
        ];
    }

    private function types(): array
    {
        return [
            'actividades' => 'COGNITIVA',
            'alertas' => 'SEGUIMIENTO',
            'instrumentos' => 'FUNCIONAL',
            'dispositivos_clinicos' => 'APOYO_MOVILIDAD',
        ];
    }
}
