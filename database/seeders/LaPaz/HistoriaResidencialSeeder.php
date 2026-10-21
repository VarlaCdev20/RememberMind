<?php

namespace Database\Seeders\LaPaz;

use App\Models\Preadmision;
use App\Models\User;
use App\Modulos\Admisiones\Aplicacion\Acciones\FormalizarAdmision;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Seeders\MedicamentosInvestigadosSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;

/** Carga optativa de desarrollo; no se invoca desde DatabaseSeeder. */
class HistoriaResidencialSeeder extends Seeder
{
    public const REFERENCE_DATE = '2026-10-20';

    public const CUTOFF = '2026-10-20 20:00:00';

    public const VERSION = 'la-paz-2025-2026-v1';

    private array $keys = [];

    private ?array $personas = null;

    private function personas(): array
    {
        return $this->personas ??= require __DIR__.'/datos/personas.php';
    }

    private array $pending = [];

    private array $credentials = [];

    private array $manifest = [];

    private array $medications = [];

    private array $turnos = [];

    private string $tipoGlucemia = 'LP_TEC_GLU';

    private array $archivosNuevos = [];

    public ?\Closure $avance = null;

    private array $filasInsertadas = [];

    public static function tablasPermitidas(): array
    {
        return [
            'actividades',
            'activity_log',
            'administraciones_medicacion',
            'admisiones',
            'alergias',
            'alertas',
            'antecedentes_clinicos',
            'aplicaciones_instrumento',
            'areas',
            'asignaciones_personal',
            'asignaciones_residente_jornada',
            'atenciones',
            'camas',
            'componentes_estudio',
            'consentimientos',
            'contactos',
            'controles_cognitivos',
            'curaciones_herida',
            'derivaciones',
            'diagnosticos',
            'dispositivos_clinicos',
            'documentos',
            'documentos_clinicos',
            'ejecuciones_cuidado',
            'estudios_clinicos',
            'eventos_alerta',
            'habitaciones',
            'heridas',
            'historial_estados_residente',
            'horarios_prescripcion',
            'incidentes',
            'indicaciones_clinicas',
            'informes_estudio',
            'instrumentos',
            'intervenciones_cuidado',
            'jornadas',
            'medicamentos',
            'mediciones_antropometricas',
            'model_has_roles',
            'notas_clinicas',
            'objetivos_signos_vitales',
            'ocupaciones_cama',
            'opciones_pregunta',
            'participantes_actividad',
            'pases_turno',
            'personal',
            'planes_cuidado',
            'preadmisiones',
            'preguntas_instrumento',
            'prescripciones',
            'programaciones_cuidado',
            'registros_conductuales',
            'registros_eliminacion',
            'registros_hidratacion',
            'registros_ingesta',
            'registros_movilidad',
            'registros_sueno',
            'residentes',
            'residentes_contactos',
            'respuestas_instrumento',
            'resultados_estudio',
            'seguimientos_pedagogicos',
            'seguros_residente',
            'secuencias_codigos',
            'signos_vitales',
            'tipos_estudio_clinico',
            'turnos',
            'usuarios',
            'valoraciones_dolor',
            'valoraciones_enfermeria_preadmision',
            'valoraciones_funcionales',
            'valoraciones_nutricionales',
            'valoraciones_psicologicas',
            'visitas',
        ];
    }

    public static function solicitudes(): array
    {
        return array_map(fn ($i) => sprintf('LP_PRE_%02d', $i), range(1, 48));
    }

    private function assertRoles(): void
    {
        foreach (['ADMINISTRADOR', 'ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A',
            'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO', 'FAMILIAR'] as $rol) {
            if (! Role::where('name', $rol)->where('guard_name', 'web')->exists()) {
                throw new RuntimeException('Falta el rol institucional '.$rol.'. Ejecute primero la instalación normal.');
            }
        }
    }

    private function direccion(int $i, int $numero): string
    {
        static $zonas;
        $zonas ??= require __DIR__.'/datos/ubicaciones.php';
        [$zona, $macro] = $zonas[($i - 1) % count($zonas)];

        return 'Zona '.$zona.', macrodistrito '.$macro.', domicilio '.($numero + $i).', La Paz, Bolivia';
    }

    private function nombrePersona(int $i): string
    {
        $persona = $this->personas()[$i - 1];

        return implode(' ', array_slice($persona, 0, 3));
    }

    public function run(): void
    {
        $this->guardEnvironment();
        $this->assertRoles();
        $cases = $this->personas();
        $existing = DB::table('preadmisiones')->whereIn('cod_preadmision', self::solicitudes())->count();
        if ($existing) {
            if ($existing !== count($cases)) {
                throw new RuntimeException('Carga histórica parcial: restaurar el respaldo; no completar ni sobrescribir silenciosamente.');
            }
            $this->assertIntegrity();
            $this->command?->info('Carga 2026 existente y verificada; no se sobrescribieron registros ni contraseñas.');

            return;
        }

        $clock = Carbon::getTestNow();
        $immutableClock = CarbonImmutable::getTestNow();
        $zonaAnterior = date_default_timezone_get();
        $configZonaAnterior = config('app.timezone');
        try {
            config(['app.timezone' => 'America/La_Paz']);
            date_default_timezone_set('America/La_Paz');
            Carbon::setTestNow(self::CUTOFF);
            CarbonImmutable::setTestNow(self::CUTOFF);
            DB::transaction(function () use ($cases): void {
                if (DB::getDriverName() === 'pgsql') {
                    // Evita dos cargas concurrentes del mismo conjunto, sin desactivar restricciones.
                    DB::select('select pg_advisory_xact_lock(?)', [2026102048]);
                    if (DB::table('preadmisiones')->where('cod_preadmision', 'LP_PRE_01')->exists()) {
                        throw new RuntimeException('Otro proceso inició la misma carga.');
                    }
                }
                $this->call(MedicamentosInvestigadosSeeder::class);
                $this->base();
                $this->catalogs();
                $this->calendar();
                $this->pendingAdmissions();
                foreach ($cases as $offset => $case) {
                    $this->resident($offset + 1, $case);
                    $this->flush();
                    $this->command?->info('Historia '.($offset + 1).'/48 incorporada.');
                    ($this->avance)?->__invoke($offset + 1);
                }
                $this->assertIntegrity();
                activity('Desarrollo')->causedBy(User::findOrFail('LP_USU_ADMIN'))
                    ->withProperties(['dataset' => self::VERSION, 'referencia' => self::REFERENCE_DATE, 'residentes' => 48])
                    ->log('Carga explícita de historiales sintéticos 2026; sin modificación del esquema.');
                if (app()->environment('local')) {
                    $this->writePrivate('la-paz-2025-2026/credenciales.json', json_encode($this->credentials, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
                    $this->writePrivate('la-paz-2025-2026/manifest.json', json_encode([
                        'version' => self::VERSION, 'seed_reference_date' => self::REFERENCE_DATE,
                        'cutoff' => self::CUTOFF, 'timezone' => config('app.timezone'),
                        'synthetic' => true, 'casos' => $this->manifest,
                        'filas_insertadas_por_seeder' => $this->filasInsertadas,
                        'salidas_experto_precargadas' => false,
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
                }
            });
        } catch (\Throwable $error) {
            $this->retirarArchivosRevertidos();
            throw $error;
        } finally {
            Carbon::setTestNow($clock);
            CarbonImmutable::setTestNow($immutableClock);
            config(['app.timezone' => $configZonaAnterior]);
            date_default_timezone_set($zonaAnterior);
        }

        $this->command?->info('48 residentes admitidos, 48 camas y controles diarios hasta '.self::CUTOFF.'.');
    }

    public function retirarArchivosRevertidos(): void
    {
        foreach ($this->archivosNuevos as $archivo) {
            if (! Storage::disk('local')->delete($archivo)) {
                report(new RuntimeException('No se pudo retirar el archivo de una carga revertida: '.$archivo));
            }
        }
        $this->archivosNuevos = [];
    }

    private function guardEnvironment(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Dataset sintético prohibido fuera de local/testing.');
        }
        if (env('LAPAZ_FECHA_CORTE', self::REFERENCE_DATE) !== self::REFERENCE_DATE) {
            throw new RuntimeException('La referencia aprobada es 2026-10-20.');
        }
        $database = DB::connection()->getDatabaseName();
        if (! (app()->environment('testing') && $database === ':memory:')
            && config('seeders.lapaz.base_autorizada', env('LAPAZ_CARGA_BASE')) !== $database) {
            throw new RuntimeException('Indique LAPAZ_CARGA_BASE con el nombre exacto de la BDD desechable/de desarrollo autorizada.');
        }
        if (! in_array(DB::getDriverName(), ['pgsql', 'sqlite'], true)) {
            throw new RuntimeException('Esta carga requiere PostgreSQL o SQLite testing.');
        }
        if (DB::getDriverName() === 'pgsql' && ! in_array(config('database.connections.'.config('database.default').'.host'), ['127.0.0.1', 'localhost', '::1'], true)) {
            throw new RuntimeException('La carga solo se permite en PostgreSQL local.');
        }
    }

    private function put(string $table, string $id, array $data, bool $buffer = false): void
    {
        if (isset($data['cod_turno'], $this->turnos[$data['cod_turno']])) {
            $data['cod_turno'] = $this->turnos[$data['cod_turno']];
        }
        if (($data['cod_tipo_estudio'] ?? null) === 'LP_TEC_GLU') {
            $data['cod_tipo_estudio'] = $this->tipoGlucemia;
        }
        // Solo resuelve la PK del esquema aprobado; los atributos se mapean explícitamente debajo.
        $pk = $this->keys[$table] ??= collect(DB::getSchemaBuilder()->getIndexes($table))->firstWhere('primary', true)['columns'][0];
        $row = [$pk => $id] + $data;
        $this->filasInsertadas[$table] = ($this->filasInsertadas[$table] ?? 0) + 1;
        if (strlen($id) > 20) {
            throw new RuntimeException('Código mayor de 20 caracteres: '.$id);
        }
        if ($buffer) {
            $this->pending[$table][] = $row;
            if (count($this->pending[$table]) >= 300) {
                DB::table($table)->insert($this->pending[$table]);
                $this->pending[$table] = [];
            }
        } else {
            DB::table($table)->insert($row);
        }
    }

    private function flush(): void
    {
        foreach ($this->pending as $table => $rows) {
            if ($rows) {
                DB::table($table)->insert($rows);
            }
        }
        $this->pending = [];
    }

    private function writePrivate(string $path, string $content): void
    {
        $disk = Storage::disk('local');
        if ($disk->exists($path)) {
            throw new RuntimeException('Ya existe un archivo de esta cohorte; no se sobrescribirá: '.$path);
        }
        $this->archivosNuevos[] = $path;
        if (! $disk->put($path, $content) || ! $disk->exists($path)
            || hash('sha256', $disk->get($path)) !== hash('sha256', $content)) {
            throw new RuntimeException('No se pudo conservar el archivo privado de la carga: '.$path);
        }
    }

    private function account(string $id, string $email, string $role): void
    {
        $password = bin2hex(random_bytes(16)); // Seguridad deliberadamente no determinista.
        $this->put('usuarios', $id, ['correo' => $email, 'contrasena' => Hash::make($password), 'estado' => 'ACTIVO']);
        User::findOrFail($id)->assignRole($role);
        $this->credentials[] = ['correo' => $email, 'contrasena' => $password, 'rol' => $role];
    }

    private function base(): void
    {
        $this->account('LP_USU_ADMIN', 'admisiones@lapaz.example.invalid', 'ADMINISTRADOR');
        $staff = [
            ['Lucía', 'Mamani'], ['Daniel', 'Rojas'], ['Paola', 'Quispe'], ['Miguel', 'Arce'],
            ['Verónica', 'Vargas'], ['Diego', 'Salazar'], ['Adriana', 'Paredes'], ['Sergio', 'Condori'],
            ['Claudia', 'Flores'], ['Gabriel', 'Rivera'], ['Sandra', 'Cabrera'], ['Andrés', 'López'],
            ['Mónica', 'Medina'], ['David', 'Navarro'], ['Fabiola', 'Romero'], ['Pablo', 'Torres'],
        ];
        foreach ($staff as $n => [$name, $surname]) {
            $i = $n + 1;
            $user = sprintf('LP_USU_N%02d', $i);
            $this->account($user, sprintf('enfermeria.%02d@lapaz.example.invalid', $i), 'ENFERMEROS');
            $this->staff(sprintf('LP_PER_N%02d', $i), $user, $name, $surname, 'ENFERMERIA', $i);
        }
        foreach ([
            ['MED', 'Carolina', 'Soria', 'MEDICO GENERAL/GERIATRA', 'MEDICINA', 'Medicina general'],
            ['GER', 'Esteban', 'Molina', 'MEDICO GENERAL/GERIATRA', 'MEDICINA', 'Geriatría'],
            ['PSI', 'Lorena', 'Castro', 'PSICOLOGO/A', 'PSICOLOGIA', 'Psicología clínica'],
            ['NUT', 'Gabriela', 'Pinto', 'NUTRICIONISTA', 'NUTRICION', 'Nutrición geriátrica'],
            ['FIS', 'Marcos', 'Valdez', 'FISIOTERAPEUTA', 'FISIOTERAPIA', 'Rehabilitación'],
            ['PED', 'Natalia', 'Suárez', 'PEDAGOGO', 'PEDAGOGIA', 'Estimulación y aprendizaje'],
        ] as $n => [$code, $name, $surname, $role, $profession, $specialty]) {
            $this->account('LP_USU_'.$code, strtolower($code).'@lapaz.example.invalid', $role);
            $this->staff('LP_PER_'.$code, 'LP_USU_'.$code, $name, $surname, $profession, 20 + $n, $specialty);
        }
        $this->staff('LP_PER_ADMIN', 'LP_USU_ADMIN', 'Patricia', 'León', 'ADMINISTRACION', 30);
        foreach (['ENF' => 'Enfermería', 'MED' => 'Medicina', 'PSI' => 'Psicología', 'NUT' => 'Nutrición', 'FIS' => 'Fisioterapia', 'PED' => 'Pedagogía', 'ADM' => 'Admisiones'] as $code => $name) {
            $this->put('areas', 'LP_ARE_'.$code, ['nombre' => $name.' residencial La Paz', 'descripcion' => 'Atención institucional del área de '.$name.' en residencia de La Paz.', 'estado' => 'ACTIVA']);
        }
        foreach ([['00:00:00', '12:00:00', 'Guardia de madrugada y mañana'], ['12:00:00', '00:00:00', 'Guardia de tarde y noche']] as $s => [$start, $end, $name]) {
            $datos = ['nombre' => $name, 'hora_inicio' => $start, 'hora_cierre' => $end, 'orden' => $s + 1, 'estado' => 'ACTIVO'];
            $coincidencias = DB::table('turnos')->where('nombre', $name)->get();
            if ($coincidencias->isEmpty()) {
                $this->put('turnos', 'LP_TUR_'.$s, $datos);
                $this->turnos['LP_TUR_'.$s] = 'LP_TUR_'.$s;
            } else {
                $existente = $coincidencias->sole();
                foreach ($datos as $campo => $valor) {
                    if ((string) $existente->$campo !== (string) $valor) {
                        throw new RuntimeException('El turno existente no coincide con la cohorte; no se modifica ni sustituye.');
                    }
                }
                $this->turnos['LP_TUR_'.$s] = $existente->cod_turno;
            }
        }
        for ($i = 1; $i <= 48; $i++) {
            $this->put('habitaciones', sprintf('LP_HAB_%02d', $i), [
                'codigo' => (string) (600 + $i), 'nombre' => 'Habitación '.(600 + $i),
                'tipo' => 'INDIVIDUAL', 'piso' => $i <= 24 ? '1' : '2', 'capacidad' => 1, 'estado' => 'ACTIVA',
            ]);
            $this->put('camas', sprintf('LP_CAM_%02d', $i), ['cod_habitacion' => sprintf('LP_HAB_%02d', $i),
                'codigo' => (600 + $i).'-A', 'tipo' => 'ESTANDAR', 'estado' => 'ACTIVA']);
        }
        foreach (require dirname(__DIR__).'/data/medicamentos_investigados.php' as [$code, $name, $strength]) {
            $match = DB::table('medicamentos')->where('concentracion', $strength)
                ->whereIn('nombre_generico', $name === 'Losartán' ? [$name, 'Losartán Potásico'] : [$name])->first();
            if (! $match) {
                throw new RuntimeException('Falta la presentación investigada '.$code);
            }
            $this->medications[$code] = $match->cod_medicamento;
        }
    }

    private function staff(string $id, string $user, string $name, string $surname, string $profession, int $serial, ?string $specialty = null): void
    {
        $this->put('personal', $id, ['cod_usuario' => $user, 'nombres' => $name, 'apellido_paterno' => $surname,
            'apellido_materno' => 'Gutiérrez', 'numero_documento' => (string) (88001000 + $serial),
            'expedicion_documento' => 'LP', 'fecha_nacimiento' => (1970 + $serial % 22).'-'.sprintf('%02d-%02d', $serial % 12 + 1, $serial % 27 + 1),
            'genero' => in_array($name, ['Daniel', 'Miguel', 'Diego', 'Sergio', 'Gabriel', 'Andrés', 'David', 'Pablo', 'Esteban', 'Marcos']) ? 'MASCULINO' : 'FEMENINO',
            'telefono' => '7200'.sprintf('%04d', $serial), 'direccion' => $this->direccion($serial, 100),
            'profesion' => $profession, 'especialidad' => $specialty, 'matricula_profesional' => 'LP-'.sprintf('%04d', $serial),
            'fecha_ingreso' => '2024-12-16', 'estado' => 'ACTIVO']);
    }

    private function catalogs(): void
    {
        // Autorización expresa de la propietaria: glucemia y Barthel 10 actividades, sin clasificación nueva.
        $datosTipo = ['nombre' => 'Glucemia de laboratorio', 'categoria' => 'LABORATORIO',
            'descripcion' => 'Determinación de glucosa en sangre; condiciones de obtención consignadas en el informe.',
            'requiere_componentes' => true, 'requiere_informe' => true, 'estado' => 'ACTIVO'];
        $coincidencias = DB::table('tipos_estudio_clinico')->where('nombre', $datosTipo['nombre'])->get();
        if ($coincidencias->isEmpty()) {
            $this->put('tipos_estudio_clinico', 'LP_TEC_GLU', $datosTipo);
        } else {
            $existente = $coincidencias->sole();
            foreach (['categoria', 'estado'] as $campo) {
                if ($datosTipo[$campo] !== $existente->$campo) {
                    throw new RuntimeException('El tipo de estudio existente no coincide con la cohorte; no se modifica ni sustituye.');
                }
            }
            if (! $existente->requiere_componentes || ! $existente->requiere_informe) {
                throw new RuntimeException('El tipo de estudio existente no admite los componentes e informes requeridos.');
            }
            $this->tipoGlucemia = $existente->cod_tipo_estudio;
        }
        $this->put('componentes_estudio', 'LP_COM_GLU', ['cod_tipo_estudio' => 'LP_TEC_GLU', 'nombre' => 'Glucosa',
            'unidad_referencia' => 'mg/dL', 'tipo_resultado' => 'NUMERICO', 'orden' => 1, 'estado' => 'ACTIVO']);
        $this->put('instrumentos', 'LP_INS_BARTHEL', ['codigo' => 'BARTHEL_LP_100', 'nombre' => 'Índice de Barthel',
            'tipo' => 'FUNCIONAL', 'version' => '10 actividades / 100 puntos', 'puntaje_maximo' => 100,
            'descripcion' => 'Actividades y puntuaciones del formulario vigente; no aplica clasificación experta automática.', 'estado' => 'ACTIVO']);
        // Solo nombres de actividades y valores, sin reproducir un cuestionario/licencia ajenos.
        $items = [
            ['Alimentación', [0, 5, 10]], ['Baño', [0, 5]], ['Aseo personal', [0, 5]], ['Vestido', [0, 5, 10]],
            ['Control intestinal', [0, 5, 10]], ['Control vesical', [0, 5, 10]], ['Uso del retrete', [0, 5, 10]],
            ['Traslados', [0, 5, 10, 15]], ['Deambulación', [0, 5, 10, 15]], ['Escaleras', [0, 5, 10]],
        ];
        foreach ($items as $n => [$name, $scores]) {
            $q = sprintf('LP_PREG_%02d', $n + 1);
            $this->put('preguntas_instrumento', $q, ['cod_instrumento' => 'LP_INS_BARTHEL', 'codigo' => 'ADL_'.($n + 1),
                'enunciado' => $name, 'dominio' => 'ACTIVIDADES_VIDA_DIARIA', 'tipo_respuesta' => 'OPCION_UNICA',
                'puntaje_maximo' => max($scores), 'orden' => $n + 1, 'estado' => 'ACTIVA']);
            foreach ($scores as $order => $score) {
                $this->put('opciones_pregunta', sprintf('LP_OPT_%02d_%02d', $n + 1, $score), ['cod_pregunta' => $q,
                    'nombre' => $score.' puntos', 'valor' => (string) $score, 'puntaje' => $score, 'orden' => $order + 1, 'estado' => 'ACTIVO']);
            }
        }
    }

    private function calendar(): void
    {
        foreach (CarbonImmutable::parse('2025-01-01')->daysUntil(self::REFERENCE_DATE.' 23:59:59') as $day) {
            $date = $day->toDateString();
            for ($shift = 0; $shift <= 1; $shift++) {
                $j = $this->jornada($date, $shift);
                $start = $date.($shift ? ' 12:00:00' : ' 00:00:00');
                $closed = $date < self::REFERENCE_DATE || $shift === 0;
                $this->put('jornadas', $j, ['cod_turno' => 'LP_TUR_'.$shift, 'cod_usuario_apertura' => 'LP_USU_ADMIN',
                    'cod_usuario_cierre' => $closed ? 'LP_USU_ADMIN' : null, 'fecha_jornada' => $date,
                    'estado' => $closed ? 'CERRADA' : 'ABIERTA']);
                for ($slot = 0; $slot < 4; $slot++) {
                    $nurse = $this->nurse($date, $shift, $slot);
                    $this->put('asignaciones_personal', 'LP_AP_'.substr($date, 2, 2).substr($date, 5, 2).substr($date, 8, 2).'_'.$shift.$slot,
                        ['cod_jornada' => $j, 'cod_personal' => $nurse, 'cod_area' => 'LP_ARE_ENF', 'funcion' => 'Cuidados y continuidad asistencial',
                            'tipo_asignacion' => 'TITULAR', 'fecha_asignacion' => $start,
                            'estado' => $closed ? 'FINALIZADA' : 'ACTIVA']);
                }
                if ($shift === 0 && $day->isWeekday()) {
                    foreach (['MED', 'GER', 'PSI', 'NUT', 'FIS', 'PED'] as $code) {
                        $this->put('asignaciones_personal', 'LP_AP_'.str_replace('-', '', substr($date, 2)).'_'.$code,
                            ['cod_jornada' => $j, 'cod_personal' => 'LP_PER_'.$code, 'cod_area' => 'LP_ARE_'.($code === 'GER' ? 'MED' : $code),
                                'funcion' => 'Atención interdisciplinaria', 'tipo_asignacion' => 'TITULAR', 'fecha_asignacion' => $date.' 08:00:00',
                                'estado' => $closed ? 'FINALIZADA' : 'ACTIVA']);
                    }
                }
            }
        }
    }

    private function jornada(string $date, int $shift): string
    {
        return 'LP_J_'.str_replace('-', '', substr($date, 2)).'_'.$shift;
    }

    private function pendingAdmissions(): void
    {
        foreach ([['Adela', 'Morales', '1949-09-10', 'PENDIENTE'], ['Ramiro', 'Tapia', '1944-12-08', 'APROBADA'],
            ['Lidia', 'Estrada', '1950-02-20', 'RECHAZADA'], ['Ernesto', 'Reyes', '1941-08-12', 'PENDIENTE']] as $n => [$name, $surname, $birth, $state]) {
            $this->put('preadmisiones', 'LP_SOL_'.($n + 1), ['cod_usuario_registro' => 'LP_USU_ADMIN',
                'cod_usuario_revision' => $state === 'PENDIENTE' ? null : 'LP_USU_ADMIN',
                'nombres' => $name, 'apellido_paterno' => $surname, 'apellido_materno' => 'Pérez',
                'numero_documento' => (string) (89000100 + $n), 'fecha_nacimiento' => $birth,
                'genero' => $n % 2 ? 'MASCULINO' : 'FEMENINO', 'motivo_ingreso' => 'Solicitud de residencia y acompañamiento.',
                'procedencia' => 'DOMICILIO', 'tipo_ingreso' => 'REGULAR', 'prioridad' => 'MEDIA',
                'fecha_solicitud' => '2026-10-05 09:00:00', 'fecha_revision' => $state === 'PENDIENTE' ? null : '2026-10-06 09:00:00',
                'estado' => $state, 'motivo_rechazo' => $state === 'RECHAZADA' ? 'La persona solicitante decide no continuar el proceso.' : null]);
        }
    }

    private function nurse(string $date, int $shift, int $slot): string
    {
        $team = ((int) CarbonImmutable::parse($date)->dayOfYear % 2) + $shift * 2;

        return sprintf('LP_PER_N%02d', $team * 4 + $slot + 1);
    }

    private function code(string $kind, int $i, string $date, int $slot = 0): string
    {
        return sprintf('LP_%s_%02d_%s%d', $kind, $i, str_replace('-', '', substr($date, 2)), $slot);
    }

    private function variation(int $i, string $date, string $metric, int $max): int
    {
        return hexdec(substr(hash('sha256', self::VERSION.':'.$i.':'.$date.':'.$metric), 0, 6)) % $max;
    }

    private function resident(int $i, array $case): void
    {
        [$name, $paternal, $maternal, $birth, $gender, $admit, $profile] = $case;
        $serial = sprintf('%02d', $i);
        $contact = 'LP_CTO_'.$serial;
        $familyUser = 'LP_USU_F'.$serial;
        $this->account($familyUser, 'familia.'.$serial.'@lapaz.example.invalid', 'FAMILIAR');
        $this->put('contactos', $contact, ['cod_usuario' => $familyUser, 'nombres' => $this->personas()[$i - 1][7],
            'apellido_paterno' => $paternal, 'apellido_materno' => $this->personas()[$i - 1][8], 'numero_documento' => (string) (87000000 + $i),
            'telefono' => '7300'.sprintf('%04d', $i), 'celular' => '7400'.sprintf('%04d', $i),
            'correo' => 'familia.'.$serial.'@lapaz.example.invalid', 'direccion' => $this->direccion($i, 300), 'estado' => 'ACTIVO']);
        $pre = 'LP_PRE_'.$serial;
        $request = CarbonImmutable::parse($admit)->subDays(4)->setTime(9, 0)->toDateTimeString();
        $review = CarbonImmutable::parse($admit)->subDays(1)->setTime(9, 0)->toDateTimeString();
        $nurse = $this->nurse($admit, 0, ($i - 1) % 4);
        $reason = match ($profile) {
            'movilidad', 'incidente' => 'Apoyo para movilidad y supervisión de las actividades cotidianas.',
            'cognicion' => 'Supervisión de actividades cotidianas y acompañamiento cognitivo.',
            'nutricion' => 'Apoyo en alimentación y seguimiento nutricional.',
            default => 'Residencia permanente con acompañamiento y seguimiento de salud.',
        };
        $this->put('preadmisiones', $pre, ['cod_contacto' => $contact, 'cod_usuario_registro' => 'LP_USU_ADMIN',
            'nombres' => $name, 'apellido_paterno' => $paternal, 'apellido_materno' => $maternal,
            'numero_documento' => (string) (89000000 + $i), 'expedicion_documento' => 'LP', 'fecha_nacimiento' => $birth,
            'genero' => $gender, 'estado_civil' => $i % 3 ? 'VIUDO/A' : 'CASADO/A',
            'telefono' => '7500'.sprintf('%04d', $i), 'direccion' => $this->direccion($i, 400),
            'motivo_ingreso' => $reason, 'procedencia' => 'DOMICILIO', 'tipo_ingreso' => 'REGULAR',
            'permanencia' => 'PERMANENTE', 'prioridad' => 'MEDIA', 'descripcion_caso' => $reason,
            'fecha_solicitud' => $request, 'estado' => 'PENDIENTE']);
        $preadNurse = $this->nurse(substr($review, 0, 10), 0, ($i - 1) % 4);
        $this->put('valoraciones_enfermeria_preadmision', 'LP_VEP_'.$serial, [
            'cod_preadmision' => $pre, 'cod_usuario_registro' => str_replace('PER', 'USU', $preadNurse),
            'cod_personal_valorador' => $preadNurse, 'fecha_hora' => $review,
            'estado_general' => 'ESTABLE', 'nivel_conciencia' => 'ALERTA',
            'orientacion_persona' => 'ORIENTADO', 'orientacion_tiempo' => $profile === 'cognicion' ? 'PARCIALMENTE_ORIENTADO' : 'ORIENTADO', 'orientacion_espacio' => 'ORIENTADO',
            'comunicacion' => 'VERBAL', 'hay_dolor' => false, 'intensidad_dolor' => 0,
            'movilidad' => in_array($profile, ['movilidad', 'incidente']) ? 'ASISTIDA' : 'INDEPENDIENTE',
            'apoyo_movilidad' => $profile === 'movilidad' ? 'ANDADOR' : 'NINGUNO',
            'riesgo_caida' => in_array($profile, ['movilidad', 'incidente']) ? 'ALTO' : 'BAJO',
            'piel_estado' => 'INTEGRA', 'hay_heridas' => false, 'higiene_ingreso' => 'ADECUADA',
            'continencia_basica' => 'CONTINENTE', 'alimentacion_aparente' => 'ADECUADA',
            'pa_sistolica' => 120, 'pa_diastolica' => 75, 'frecuencia_cardiaca' => 72, 'frecuencia_respiratoria' => 16,
            'temperatura' => 36.5, 'saturacion_oxigeno' => 91 + $i % 4, 'peso' => 54 + $i % 20, 'talla' => 150 + $i % 20,
            'antecedentes_relevantes' => $this->history($profile, $i), 'medicacion_referida' => 'Se conciliará con el médico durante la admisión.',
            'alergias_referidas' => $i === 6 ? 'Penicilina: urticaria referida.' : 'Sin alergias referidas en la entrevista.',
            'dependencia_funcional' => $profile === 'movilidad' ? 'PARCIAL' : 'INDEPENDIENTE',
            'riesgo_nutricional' => $profile === 'nutricion' ? 'ALTO' : 'BAJO', 'riesgo_cognitivo' => $profile === 'cognicion' ? 'ALTO' : 'BAJO',
            'necesidad_apoyo_inmediato' => false, 'prioridad_sugerida' => 'MEDIA', 'confirmacion_documentacion' => true,
            'recomendacion_enfermeria' => $reason,
        ]);
        DB::table('preadmisiones')->where('cod_preadmision', $pre)->update([
            'estado' => 'APROBADA', 'cod_usuario_revision' => 'LP_USU_ADMIN', 'fecha_revision' => $review,
        ]);

        // Determinismo limitado a los códigos de la Action; nunca se usa para contraseñas.
        $sequence = 0;
        Str::createRandomStringsUsing(function (int $length) use ($i, &$sequence): string {
            return substr(hash('sha256', self::VERSION.':admision:'.$i.':'.(++$sequence)), 0, $length);
        });
        try {
            $resident = app(FormalizarAdmision::class)->ejecutar(Preadmision::findOrFail($pre), [
                'cod_cama' => 'LP_CAM_'.$serial, 'cod_contacto' => $contact, 'fecha_hora_admision' => $admit.' 07:00:00',
                'fecha_consentimiento' => $admit.' 07:00:00', 'parentesco' => 'HIJO/A',
                'autoriza_informacion' => true, 'autoriza_salida' => true, 'celular' => '7600'.sprintf('%04d', $i),
                'nivel_educativo' => $i % 2 ? 'SECUNDARIA' : 'PRIMARIA', 'grupo_sanguineo' => ['O', 'O', 'O', 'A', 'A', 'B', 'AB'][$i % 7],
                'factor_rh' => $i % 11 === 0 ? '-' : '+', 'seguro_entidad' => 'Sistema Único de Salud', 'seguro_plan' => 'Atención en red pública',
                'seguro_afiliacion' => '9900'.sprintf('%04d', $i), 'seguro_titular' => $name.' '.$paternal.' '.$maternal,
                'seguro_cobertura' => 'Consulta y atención de salud',
            ], User::findOrFail('LP_USU_ADMIN'));
        } finally {
            Str::createRandomStringsNormally();
        }
        $r = $resident->cod_residente;
        $this->manifest[] = ['numero' => $i, 'cod_residente' => $r, 'perfil' => $profile, 'admision' => $admit];
        $context = ['cod_residente' => $r];
        $this->documents($i, $r, $pre, $admit, $name.' '.$paternal.' '.$maternal);
        $medical = $this->attention($i, $r, $admit, 'MED', 'ADM', 'Conciliación y valoración médica de ingreso.');
        $this->put('antecedentes_clinicos', 'LP_ANT_'.$serial, $context + ['cod_personal' => 'LP_PER_MED', 'tipo_antecedente' => 'PERSONAL',
            'descripcion' => $this->history($profile, $i), 'fecha_referencia' => $admit, 'fuente_informacion' => 'Entrevista de ingreso y documentación aportada', 'estado' => 'VIGENTE']);
        $this->put('notas_clinicas', 'LP_NOT_'.$serial, $context + ['cod_atencion' => $medical, 'cod_personal' => 'LP_PER_MED',
            'tipo_nota' => 'EVOLUCION', 'contenido' => $this->history($profile, $i).' Conciliación de medicación y plan interdisciplinario documentados. Residente sin síntomas agudos al ingreso.',
            'fecha_hora' => $admit.' 08:00:00', 'estado' => 'VIGENTE']);
        if (in_array($profile, ['hta', 'diabetes', 'tiroides', 'movilidad'], true)) {
            $diagnosis = ['hta' => 'Hipertensión arterial', 'diabetes' => 'Diabetes mellitus tipo 2', 'tiroides' => 'Hipotiroidismo', 'movilidad' => 'Artrosis de rodilla'][$profile];
            $this->put('diagnosticos', 'LP_DIA_'.$serial, $context + ['cod_atencion' => $medical, 'cod_personal' => 'LP_PER_MED',
                'nombre' => $diagnosis, 'tipo' => 'CRONICO', 'certeza' => 'CONFIRMADO', 'fecha_hora' => $admit.' 08:00:00', 'estado' => 'ACTIVO',
                'observacion' => 'Antecedente previamente documentado; sin inferencia automática a partir de mediciones.']);
        }
        $known = $this->personas()[$i - 1][9];
        foreach ($known as $n => $condition) {
            $this->put('diagnosticos', 'LP_DX_'.$serial.'_'.$n, $context + [
                'cod_atencion' => $medical, 'cod_personal' => 'LP_PER_MED',
                'nombre' => $condition, 'tipo' => 'CRONICO', 'certeza' => 'CONFIRMADO',
                'fecha_hora' => $admit.' 08:00:00', 'estado' => 'ACTIVO',
                'observacion' => 'Antecedente de ingreso consignado en la historia individual de '.$name.' '.$paternal.'.',
            ]);
        }
        if ($i === 6) {
            $this->put('alergias', 'LP_ALE_'.$serial, $context + ['cod_personal' => 'LP_PER_MED', 'tipo' => 'MEDICAMENTO',
                'sustancia' => 'Penicilina', 'reaccion' => 'Urticaria referida', 'gravedad' => 'MODERADA', 'fecha_hora' => $admit.' 08:00:00', 'estado' => 'ACTIVA']);
        }
        if ($profile === 'movilidad') {
            $this->put('dispositivos_clinicos', 'LP_DIS_'.$serial, $context + ['cod_personal' => 'LP_PER_FIS', 'tipo' => 'ANDADOR',
                'descripcion' => 'Andador de cuatro apoyos ajustado al residente.', 'ubicacion' => 'Apoyo para marcha', 'fecha_colocacion' => $admit, 'estado' => 'ACTIVO']);
        }
        $this->put('indicaciones_clinicas', 'LP_IND_'.$serial, $context + ['cod_atencion' => $medical, 'cod_personal' => 'LP_PER_MED',
            'tipo_indicacion' => 'CUIDADO', 'descripcion' => $reason.' Registrar signos, alimentación, líquidos, eliminación y tolerancia a la actividad.',
            'prioridad' => 'MEDIA', 'fecha_hora' => $admit.' 08:00:00', 'estado' => 'VIGENTE']);
        $plan = 'LP_PLA_'.$serial;
        $intervention = 'LP_INT_'.$serial;
        $this->put('planes_cuidado', $plan, $context + ['cod_area' => 'LP_ARE_ENF', 'cod_personal' => $nurse, 'tipo_plan' => 'INICIAL',
            'nombre' => 'Plan de cuidados individual', 'objetivo_general' => $reason, 'prioridad' => 'MEDIA', 'fecha_hora_apertura' => $admit.' 08:15:00', 'estado' => 'ACTIVO']);
        $this->put('intervenciones_cuidado', $intervention, ['cod_plan' => $plan, 'nombre' => 'Acompañamiento en actividades diarias',
            'descripcion' => 'Comprobar confort y apoyar higiene, movilidad y alimentación según autonomía observada.',
            'objetivo_especifico' => 'Conservar autonomía y documentar cambios.', 'prioridad' => 'MEDIA', 'estado' => 'ACTIVA']);
        $this->put('programaciones_cuidado', 'LP_PRO_'.$serial, ['cod_intervencion' => $intervention, 'cod_turno' => 'LP_TUR_0',
            'frecuencia' => 'DIARIA', 'hora_programada' => '09:30:00', 'fecha_activacion' => $admit, 'estado' => 'ACTIVA']);
        $rx = $this->prescription($i, $r, $medical, $admit, $profile);
        if ($profile === 'diabetes') {
            $this->put('objetivos_signos_vitales', 'LP_OSV_'.$serial, $context + ['cod_personal' => 'LP_PER_MED',
                'parametro' => 'glucemia', 'min_objetivo' => 100, 'max_objetivo' => 180, 'min_critico' => null, 'max_critico' => null,
                'vigente_desde' => $admit.' 08:00:00', 'estado' => 'VIGENTE',
                'motivo' => 'Objetivo individual preprandial consignado en el plan médico de ingreso.']);
        }
        foreach (CarbonImmutable::parse($admit)->daysUntil(self::REFERENCE_DATE.' 23:59:59') as $day) {
            $this->daily($i, $r, $day, $admit, $profile, $intervention, $rx);
            if ($day->isWeekday() && ((int) $day->diffInDays(CarbonImmutable::parse($admit), true) % 28 === 0 || $day->toDateString() === $admit)) {
                $this->assessments($i, $r, $day->toDateString(), $profile);
            }
        }
        $this->flush();
        $this->events($i, $r, $admit, $profile);
    }

    private function history(string $profile, int $i): string
    {
        $principal = match ($profile) {
            'hta' => 'Hipertensión arterial conocida, con tratamiento previo y controles clínicos.',
            'diabetes' => 'Diabetes mellitus tipo 2 conocida; seguimiento médico, nutricional y glucémico.',
            'tiroides' => 'Hipotiroidismo en tratamiento sustitutivo previo.',
            'movilidad' => 'Artrosis de rodilla con apoyo para marcha; sin lesión aguda al ingreso.',
            'cognicion' => 'Dificultad para orientación temporal referida; requiere seguimiento descriptivo.',
            'nutricion' => 'Disminución del apetito referida; se programa valoración nutricional.',
            'deterioro' => 'Seguimiento de autonomía con aumento gradual de apoyo para actividades cotidianas.',
            'sueno' => 'Episodios de sueño interrumpido y ansiedad observada; seguimiento descriptivo.',
            'dolor' => 'Dolor musculoesquelético recurrente con valoración médica y seguimiento de respuesta.',
            'participacion' => 'Participación parcial en actividades; respeta preferencias y ritmo individual.',
            default => 'Artrosis previamente documentada; seguimiento de autonomía y confort.',
        };
        $antecedentes = $this->personas()[$i - 1][9];

        return $principal.' Antecedentes adicionales de '.$this->nombrePersona($i).': '.implode('; ', $antecedentes).'.';
    }

    private function attention(int $i, string $r, string $date, string $professional, string $kind, string $reason, string $time = '08:00:00'): string
    {
        $id = $this->code('AT', $i, $date, ['MED' => 0, 'GER' => 1, 'PSI' => 2, 'NUT' => 3, 'FIS' => 4, 'PED' => 5][$professional]);
        $this->put('atenciones', $id, ['cod_residente' => $r, 'cod_area' => 'LP_ARE_'.($professional === 'GER' ? 'MED' : $professional),
            'cod_personal' => 'LP_PER_'.$professional, 'tipo_atencion' => $kind, 'motivo' => $reason,
            'fecha_hora' => $date.' '.$time, 'estado' => 'FINALIZADA']);

        return $id;
    }

    private function documents(int $i, string $r, string $pre, string $date, string $name): void
    {
        $content = "Expediente institucional\nNombre: $name\nIngreso: $date\nOrigen: datos sintéticos de desarrollo. No válido para atención real.\n";
        $path = sprintf('la-paz-2025-2026/expedientes/%02d.txt', $i);
        $this->writePrivate($path, $content);
        $this->put('documentos', sprintf('LP_DOC_%02d', $i), ['cod_residente' => $r, 'cod_usuario_validacion' => 'LP_USU_ADMIN',
            'tipo_documento' => 'EXPEDIENTE', 'nombre' => 'Expediente de ingreso', 'ruta_archivo' => $path,
            'tipo_archivo' => 'text/plain', 'hash_archivo' => hash('sha256', $content), 'fecha_validacion' => $date.' 08:00:00', 'estado' => 'VALIDADO']);
    }

    private function prescription(int $i, string $r, string $attention, string $date, string $profile): ?array
    {
        // Órdenes de casos sintéticos, no recomendaciones ni una regla para residentes reales.
        $definition = match ($profile) {
            'hta' => ['C0902', 50, 'mg', ['08:30:00'], 'Tratamiento antihipertensivo conciliado'],
            'diabetes' => ['A1007', 500, 'mg', ['09:00:00', '19:00:00'], 'Tratamiento previo de diabetes, con comidas'],
            'tiroides' => ['H0303', 0.05, 'mg', ['08:30:00'], 'Tratamiento sustitutivo previamente prescrito; separación de comidas consignada en la orden'],
            'dolor' => ['N0208', 500, 'mg', ['09:00:00', '19:00:00'], 'Tratamiento temporal de dolor, por cinco días tras valoración médica'],
            default => null,
        };
        if (! $definition) {
            return null;
        }
        [$med, $dose, $unit, $times, $indication] = $definition;
        $rx = sprintf('LP_RX_%02d', $i);
        $end = $profile === 'dolor' ? CarbonImmutable::parse($date)->addDays(5)->setTime(8, 0)->toDateTimeString() : null;
        $this->put('prescripciones', $rx, ['cod_residente' => $r, 'cod_atencion' => $attention, 'cod_medicamento' => $this->medications[$med],
            'cod_personal' => 'LP_PER_MED', 'dosis' => $dose, 'unidad_dosis' => $unit, 'via_administracion' => 'ORAL',
            'frecuencia' => count($times) === 2 ? 'DOS_VECES_AL_DIA' : 'DIARIA', 'indicacion' => $indication,
            'segun_necesidad' => false, 'fecha_hora_prescripcion' => $date.' 08:00:00', 'estado' => $end ? 'SUSPENDIDA' : 'ACTIVA',
            'fecha_hora_suspension' => $end, 'cod_personal_suspension' => $end ? 'LP_PER_MED' : null,
            'motivo_suspension' => $end ? 'Tratamiento temporal concluido; respuesta favorable documentada.' : null,
            'observacion' => 'Orden conciliada durante la valoración médica de ingreso.']);
        foreach ($times as $slot => $time) {
            $this->put('horarios_prescripcion', sprintf('LP_HRX_%02d_%d', $i, $slot), ['cod_prescripcion' => $rx,
                'hora_programada' => $time, 'dosis_programada' => $dose, 'estado' => $end ? 'INACTIVO' : 'ACTIVO']);
        }

        return compact('rx', 'dose', 'times', 'end');
    }

    private function daily(int $i, string $r, CarbonImmutable $day, string $admit, string $profile, string $intervention, ?array $rx): void
    {
        $date = $day->toDateString();
        $minutos = intdiv($i - 1, 4) * 3;
        $hora = fn (string $reloj): string => CarbonImmutable::parse($date.' '.$reloj)->addMinutes($minutos)->toDateTimeString();
        $today = $date === self::REFERENCE_DATE;
        $elapsed = (int) $day->diffInDays(CarbonImmutable::parse($admit), true);
        $sleepEpisode = $profile === 'sueno' && $elapsed >= 35 && $elapsed <= 49;
        $woundEpisode = $profile === 'herida' && $elapsed >= 21 && $elapsed <= 28;
        $postFall = $profile === 'incidente' && $elapsed >= 21 && $elapsed <= 34;
        $nurse = $this->nurse($date, 0, ($i - 1) % 4);
        $j = $this->jornada($date, 0);
        $context = ['cod_residente' => $r, 'cod_personal' => $nurse, 'cod_jornada' => $j];
        $dailyAttention = $this->code('AD', $i, $date);
        $this->put('atenciones', $dailyAttention, ['cod_residente' => $r, 'cod_area' => 'LP_ARE_ENF', 'cod_personal' => $nurse,
            'tipo_atencion' => 'SEGUIMIENTO_DIARIO', 'motivo' => 'Seguimiento de '.$this->nombrePersona($i).' el '.$date, 'fecha_hora' => $hora('08:00:00'), 'estado' => 'FINALIZADA',
            'observacion' => 'Control diario, higiene, confort, ingesta y líquidos registrados. '.$this->history($profile, $i)]);
        $clinicalContext = $context + ['cod_atencion' => $dailyAttention];
        $this->put('notas_clinicas', $this->code('ND', $i, $date), ['cod_residente' => $r, 'cod_atencion' => $dailyAttention,
            'cod_personal' => $nurse, 'tipo_nota' => 'EVOLUCION', 'contenido' => 'Evolución del '.$date.' de '.$this->nombrePersona($i).': signos, ingesta, movilidad y tolerancia registrados. '.$this->history($profile, $i),
            'fecha_hora' => $hora('09:50:00'), 'estado' => 'VIGENTE'], true);
        foreach ([0, 1] as $shift) {
            $this->put('asignaciones_residente_jornada', $this->code('AR', $i, $date, $shift), [
                'cod_residente' => $r, 'cod_personal' => $this->nurse($date, $shift, ($i - 1) % 4),
                'cod_jornada' => $this->jornada($date, $shift), 'nivel_supervision' => in_array($profile, ['movilidad', 'cognicion', 'incidente']) ? 'ALTA' : 'BASICA',
                'fecha_hora' => $date.($date === $admit && ! $shift ? ' 07:00:00' : ($shift ? ' 12:00:00' : ' 00:00:00')),
                'estado' => $today && $shift === 1 ? 'ACTIVA' : 'FINALIZADA',
            ], true);
        }
        $pulse = 61 + $i % 10 + $this->variation($i, $date, 'pulse', 18);
        $sis = 112 + $this->variation($i, $date, 'sis', $profile === 'hta' ? 23 : 17);
        $weight = 54 + $i % 20 + sin((int) $day->dayOfYear / 30) * 0.4;
        $assisted = ($profile === 'movilidad' && $elapsed < 84) || ($profile === 'deterioro' && $elapsed >= 84) || $postFall;
        $this->put('signos_vitales', $this->code('SV', $i, $date), $clinicalContext + [
            'fecha_hora' => $hora('08:00:00'), 'presion_sistolica' => $sis,
            'presion_diastolica' => 66 + $this->variation($i, $date, 'dia', 14),
            'frecuencia_cardiaca' => $pulse, 'frecuencia_respiratoria' => 16 + $this->variation($i, $date, 'resp', 3),
            'temperatura' => 36.2 + $this->variation($i, $date, 'temp', 7) / 10,
            'saturacion_oxigeno' => 90 + $this->variation($i, $date, 'spo', 5),
            'glucemia' => $profile === 'diabetes' ? 110 + $this->variation($i, $date, 'glu', 41) : null,
            'estado' => 'VIGENTE', 'observacion' => 'Control de '.$this->nombrePersona($i).' en reposo, '.$date.'. Residente habitual de La Paz; lectura en aire ambiente.',
        ], true);
        $pain = $woundEpisode ? max(0, 3 - (int) (($elapsed - 21) / 2)) : ($postFall ? 2 : ($profile === 'dolor' ? ($elapsed < 5 ? 4 : ($elapsed % 35 === 0 ? 2 : 0)) : ($profile === 'movilidad' ? ($elapsed < 56 ? 3 : 1) : 0)));
        $this->put('valoraciones_dolor', $this->code('DO', $i, $date), ['cod_residente' => $r, 'cod_personal' => $nurse,
            'fecha_hora' => $hora(($woundEpisode || $postFall) && $elapsed === 21 ? '08:40:00' : '08:10:00'), 'intensidad' => $pain,
            'ubicacion' => $woundEpisode ? 'Antebrazo izquierdo' : ($postFall || $profile === 'dolor' ? 'Región lumbar' : ($profile === 'movilidad' ? 'Rodilla derecha' : null)), 'tipo_dolor' => null,
            'duracion' => $profile === 'movilidad' ? 'Intermitente con la actividad' : null,
            'intervencion' => $pain > 0 ? 'Se registra dolor, se proporciona posición confortable y se comunica o aplica la indicación médica vigente según el contexto.' : 'No requiere intervención por dolor en este control.',
            'respuesta' => null, 'estado' => 'VIGENTE'], true);
        $this->put('registros_conductuales', $this->code('CO', $i, $date), $clinicalContext + ['fecha_hora' => $hora('09:10:00'),
            'estado_animo' => $sleepEpisode ? 'ANSIOSO' : 'TRANQUILO', 'apatia' => false, 'agitacion' => false, 'agresividad' => false, 'ansiedad' => $sleepEpisode,
            'aislamiento' => false, 'deambulacion' => false, 'participacion' => $profile === 'cognicion' ? 'PARCIAL' : 'ACTIVA',
            'cambio_conducta' => $sleepEpisode, 'descripcion' => $sleepEpisode ? 'Ansiedad leve observada; se registra junto con el sueño sin afirmar causalidad.' : 'Acepta el cuidado y mantiene interacción con el personal.',
            'intervencion' => 'Acompañamiento y explicación de la actividad.', 'respuesta' => 'Colabora con el cuidado.', 'estado' => 'VIGENTE'], true);
        if ($date > $admit) {
            $this->put('registros_sueno', $this->code('SU', $i, $date), $context + ['fecha' => $date,
                'horas_sueno' => $sleepEpisode ? 4.5 + $this->variation($i, $date, 'sleep', 3) / 2 : 6.5 + $this->variation($i, $date, 'sleep', 4) / 2, 'despertares' => $sleepEpisode ? 3 : $this->variation($i, $date, 'wake', 2),
                'insomnio' => $sleepEpisode, 'somnolencia_diurna' => $sleepEpisode, 'agitacion_nocturna' => false, 'calidad' => $sleepEpisode ? 'INTERRUMPIDO' : 'NORMAL',
                'observacion' => 'Resumen del descanso nocturno en la jornada de madrugada.', 'estado' => 'VIGENTE'], true);
        }
        foreach ([['DESAYUNO', '09:00:00'], ['ALMUERZO', '13:00:00'], ['CENA', '19:00:00']] as $slot => [$meal, $time]) {
            $shift = $time >= '12:00:00' ? 1 : 0;
            $this->put('registros_ingesta', $this->code('IN', $i, $date, $slot), [
                'cod_residente' => $r, 'cod_personal' => $this->nurse($date, $shift, ($i - 1) % 4), 'cod_jornada' => $this->jornada($date, $shift),
                'fecha_hora' => $date.' '.$time, 'tipo_comida' => $meal,
                'porcentaje_consumido' => (($profile === 'nutricion' && $elapsed >= 28 && $elapsed < 84) || ($profile === 'abierta' && $date >= '2026-10-05')) ? 60 + $this->variation($i, $date, $meal, 16) : 85 + $this->variation($i, $date, $meal, 16),
                'apetito' => $profile === 'nutricion' ? 'DISMINUIDO' : 'CONSERVADO', 'tolerancia' => 'BUENA', 'dificultad_deglucion' => false,
                'observacion' => $profile === 'nutricion' ? 'Acompañamiento durante la comida y registro de lo consumido.' : 'Tolera la comida sin molestias referidas.', 'estado' => 'VIGENTE',
            ], true);
        }
        foreach (['09:00:00', '11:00:00', '14:00:00', '17:00:00', '20:00:00'] as $slot => $time) {
            $shift = $time >= '12:00:00' ? 1 : 0;
            $this->put('registros_hidratacion', $this->code('HI', $i, $date, $slot), [
                'cod_residente' => $r, 'cod_personal' => $this->nurse($date, $shift, ($i - 1) % 4), 'cod_jornada' => $this->jornada($date, $shift),
                'fecha_hora' => $date.' '.$time, 'cantidad_ml' => 200 + $this->variation($i, $date, 'water'.$slot, 5) * 25,
                'tipo_liquido' => $slot % 2 ? 'INFUSION' : 'AGUA', 'tolerancia' => 'ADECUADA', 'observacion' => 'Volumen oral efectivamente consumido; no equivale al total de agua de alimentos.', 'estado' => 'VIGENTE',
            ], true);
        }
        foreach (['URINARIA', 'INTESTINAL'] as $slot => $type) {
            $this->put('registros_eliminacion', $this->code('EL', $i, $date, $slot), $context + ['fecha_hora' => $date.' 09:00:00',
                'tipo_eliminacion' => $type, 'cantidad' => $slot ? 'Una deposición' : 'Una micción; volumen no cuantificado',
                'caracteristica' => $slot ? 'Formada' : 'Amarilla clara', 'continencia' => 'CONTINENTE',
                'observacion' => $slot ? 'Sin dolor ni sangre referidos.' : 'Sin disuria referida.', 'estado' => 'VIGENTE'], true);
        }
        $this->put('registros_movilidad', $this->code('MO', $i, $date), $clinicalContext + ['fecha_hora' => $hora('09:30:00'),
            'marcha' => $assisted ? 'ASISTIDA' : 'INDEPENDIENTE', 'equilibrio' => $assisted ? 'INESTABLE' : 'ESTABLE',
            'traslado' => $assisted ? 'AYUDA_UNA_PERSONA' : 'INDEPENDIENTE', 'tipo_apoyo' => $assisted ? 'SUPERVISION' : 'NINGUNO',
            'dispositivo' => $profile === 'movilidad' ? 'ANDADOR' : null, 'fatiga' => $assisted ? 'LEVE' : 'SIN_FATIGA',
            'riesgo_caida' => $assisted ? 'ALTO' : 'BAJO', 'observacion' => $assisted ? 'Traslado con acompañamiento; sin caída en esta actividad.' : 'Realiza traslado sin ayuda.', 'estado' => 'VIGENTE'], true);
        $this->put('ejecuciones_cuidado', $this->code('EC', $i, $date), $context + ['cod_intervencion' => $intervention,
            'fecha_hora_programada' => $hora('09:30:00'), 'fecha_hora_ejecucion' => $hora('09:35:00'),
            'resultado' => 'Cuidados de higiene y confort realizados.', 'estado' => 'REALIZADA'], true);
        if ((int) $day->diffInDays(CarbonImmutable::parse($admit), true) % 7 === 0) {
            $this->put('controles_cognitivos', $this->code('CG', $i, $date), $clinicalContext + ['fecha_hora' => $hora('09:40:00'),
                'orientacion_persona' => 'ORIENTADO', 'orientacion_lugar' => 'ORIENTADO', 'orientacion_tiempo' => $profile === 'cognicion' ? 'PARCIALMENTE_ORIENTADO' : 'ORIENTADO',
                'memoria_reciente' => $profile === 'cognicion' ? 'ALTERACION_LEVE' : 'CONSERVADA', 'memoria_remota' => 'CONSERVADA',
                'atencion' => 'CONSERVADA', 'comprension' => 'CONSERVADA', 'lenguaje' => 'CONSERVADO', 'sigue_instrucciones' => true,
                'repite_preguntas' => $profile === 'cognicion', 'olvida_indicaciones' => $profile === 'cognicion', 'reconoce_personas' => true,
                'reconoce_entorno' => true, 'confusion' => false, 'cambio_cognitivo' => false,
                'observacion' => 'Control de orientación, memoria, atención y respuesta a instrucciones de esta jornada.', 'estado' => 'VIGENTE'], true);
        }
        if ($rx && (! $rx['end'] || $date < substr($rx['end'], 0, 10))) {
            foreach ($rx['times'] as $slot => $time) {
                if ($today && $time > '20:00:00') {
                    continue;
                }
                $shift = $time >= '12:00:00' ? 1 : 0;
                $this->put('administraciones_medicacion', $this->code('AM', $i, $date, $slot), [
                    'cod_prescripcion' => $rx['rx'], 'cod_horario_prescripcion' => sprintf('LP_HRX_%02d_%d', $i, $slot),
                    'cod_residente' => $r, 'cod_jornada' => $this->jornada($date, $shift), 'cod_personal' => $this->nurse($date, $shift, ($i - 1) % 4),
                    'fecha_hora_programada' => $date.' '.$time, 'fecha_hora_administracion' => $hora($time),
                    'resultado' => 'ADMINISTRADA', 'dosis_administrada' => $rx['dose'], 'efecto_observado' => 'Sin molestias inmediatas referidas.',
                    'reaccion_adversa' => null, 'estado' => 'VIGENTE',
                ], true);
            }
        }
        if ($date <= self::REFERENCE_DATE) {
            $this->put('pases_turno', $this->code('PT', $i, $date), [
                'cod_residente' => $r, 'cod_jornada_saliente' => $j, 'cod_jornada_entrante' => $this->jornada($date, 1),
                'cod_personal_saliente' => $nurse, 'cod_personal_entrante' => $this->nurse($date, 1, ($i - 1) % 4),
                'fecha_hora' => $date.' 11:55:00', 'estado_general' => 'ESTABLE',
                'resumen' => 'Control de signos y cuidados de mañana registrados. '.$this->history($profile, $i),
                'pendientes' => $profile === 'diabetes' ? 'Medicación de la tarde según orden vigente.' : 'Continuar controles y cuidados de la tarde.',
                'vigilancia' => $assisted ? 'Acompañar los traslados y observar tolerancia.' : 'Observar cambios respecto de su estado habitual.',
                'recomendacion' => 'Documentar cualquier cambio y comunicar al profesional correspondiente.', 'estado' => 'RECIBIDO',
                'fecha_hora_recepcion' => $date.' 12:00:00', 'observacion_recepcion' => 'Se recibe el pase y se revisan los pendientes.',
            ], true);
        }
    }

    private function assessments(int $i, string $r, string $date, string $profile): void
    {
        foreach (['NUT', 'FIS', 'PSI', 'PED'] as $professional) {
            $rank = 0;
            foreach ($this->personas() as $other => $case) {
                if ($other + 1 >= $i) {
                    break;
                }
                if ($case[5] <= $date && (int) CarbonImmutable::parse($date)->diffInDays(CarbonImmutable::parse($case[5]), true) % 28 === 0) {
                    $rank++;
                }
            }
            $time = $professional === 'PED' ? '09:40:00' : CarbonImmutable::parse($date.' 08:00:00')
                ->addMinutes($rank * 20 + ['NUT' => 0, 'FIS' => 20, 'PSI' => 40][$professional])->format('H:i:s');
            if ($date === self::REFERENCE_DATE && $time > '20:00:00') {
                throw new RuntimeException('La agenda de valoraciones excede el corte aprobado.');
            }
            $at = $this->attention($i, $r, $date, $professional, 'VALORACION', 'Seguimiento interdisciplinario del plan individual.', $time);
            $base = ['cod_residente' => $r, 'cod_personal' => 'LP_PER_'.$professional, 'cod_atencion' => $at, 'fecha_hora' => $date.' '.$time, 'estado' => 'VIGENTE'];
            if ($professional === 'NUT') {
                $elapsed = (int) CarbonImmutable::parse($date)->diffInDays(CarbonImmutable::parse($this->personas()[$i - 1][5]), true);
                $weight = round(54 + $i % 20 + sin((int) CarbonImmutable::parse($date)->dayOfYear / 30) * 0.4
                    + ($profile === 'nutricion' ? ($elapsed <= 84 ? -min(4.8, max(0, $elapsed - 28) * 4.8 / 56) : -4.8 + min(3.2, ($elapsed - 84) * 3.2 / 84)) : 0), 2);
                $height = 150 + $i % 20;
                $measurement = $this->code('MA', $i, $date);
                $this->put('mediciones_antropometricas', $measurement, ['cod_residente' => $r, 'cod_personal' => 'LP_PER_NUT',
                    'fecha_hora' => $date.' '.$time, 'peso' => $weight, 'talla' => $height,
                    'imc' => round($weight / (($height / 100) ** 2), 2), 'perimetro_braquial' => 25 + $i % 4,
                    'perimetro_pantorrilla' => 32 + $i % 4, 'observacion' => 'Medición con equipo calibrado y técnica habitual.']);
                $this->put('valoraciones_nutricionales', $this->code('VN', $i, $date), $base + ['cod_medicion' => $measurement,
                    'estado_nutricional' => 'EN_SEGUIMIENTO', 'apetito' => $profile === 'nutricion' ? 'DISMINUIDO' : 'CONSERVADO',
                    'deglucion' => 'CONSERVADA', 'riesgo_desnutricion' => $profile === 'nutricion' ? 'ALTO' : 'BAJO',
                    'necesidad_asistencia' => $profile === 'nutricion' ? 'SUPERVISION' : 'NO_REQUIERE', 'requerimiento_hidrico' => null,
                    'restricciones_alimentarias' => $profile === 'diabetes' ? 'Distribución de carbohidratos según el plan individual.' : 'Sin restricciones adicionales indicadas.',
                    'conclusion' => 'Se revisan peso, consumo y tolerancia; se mantiene seguimiento.', 'recomendacion' => 'Registrar ingesta y comunicar cambios persistentes.']);
            } elseif ($professional === 'FIS') {
                $elapsed = (int) CarbonImmutable::parse($date)->diffInDays(CarbonImmutable::parse($this->personas()[$i - 1][5]), true);
                $help = ($profile === 'movilidad' && $elapsed < 84) || ($profile === 'deterioro' && $elapsed >= 84) || ($profile === 'incidente' && $elapsed >= 21 && $elapsed <= 34);
                $this->put('valoraciones_funcionales', $this->code('VF', $i, $date), $base + ['marcha' => $help ? 'ASISTIDA' : 'INDEPENDIENTE',
                    'equilibrio' => $help ? 'INESTABLE' : 'ESTABLE', 'traslado' => $help ? 'ASISTIDO' : 'INDEPENDIENTE',
                    'fuerza_funcional' => 'CONSERVADA', 'resistencia' => $help ? 'LIMITADA' : 'CONSERVADA',
                    'alimentacion_autonoma' => 'INDEPENDIENTE', 'bano_autonomo' => $help ? 'ASISTIDO' : 'INDEPENDIENTE', 'vestido_autonomo' => $help ? 'ASISTIDO' : 'INDEPENDIENTE', 'higiene_autonoma' => 'INDEPENDIENTE',
                    'continencia' => 'CONTINENTE', 'movilidad_autonoma' => $help ? 'ASISTIDO' : 'INDEPENDIENTE', 'necesita_supervision' => $help,
                    'nivel_dependencia' => $help ? 'PARCIAL' : 'INDEPENDIENTE',
                    'conclusion' => 'Autonomía y apoyos observados directamente.', 'recomendacion' => $help ? 'Mantener apoyo y actividad supervisada.' : 'Mantener la actividad cotidiana tolerada.']);
                $scores = $help ? [10, 0, 5, 5, 10, 10, 5, 10, 10, 0] : [10, 5, 5, 10, 10, 10, 10, 15, 15, 10];
                $app = $this->code('AI', $i, $date);
                $this->put('aplicaciones_instrumento', $app, $base + ['cod_instrumento' => 'LP_INS_BARTHEL',
                    'puntaje_total' => array_sum($scores), 'puntaje_maximo' => 100,
                    'clasificacion' => null, 'interpretacion' => null]);
                foreach ($scores as $q => $score) {
                    $this->put('respuestas_instrumento', $this->code('RI', $i, $date, $q), ['cod_aplicacion' => $app,
                        'cod_pregunta' => sprintf('LP_PREG_%02d', $q + 1), 'cod_opcion' => sprintf('LP_OPT_%02d_%02d', $q + 1, $score),
                        'valor_numero' => $score, 'puntaje' => $score]);
                }
            } elseif ($professional === 'PSI') {
                $this->put('valoraciones_psicologicas', $this->code('VP', $i, $date), $base + ['estado_animo' => 'TRANQUILO',
                    'afecto' => 'CONGRUENTE', 'ansiedad' => 'NO_OBSERVADA', 'apatia' => 'NO_OBSERVADA', 'percepcion' => 'SIN_CAMBIOS_REFERIDOS',
                    'conducta' => 'COLABORADORA', 'comunicacion' => 'VERBAL', 'interaccion_social' => 'CONSERVADA',
                    'impresion_cognitiva' => $profile === 'cognicion' ? 'Dificultad de orientación temporal observada; pendiente seguimiento.' : 'Sin cambios observados durante la entrevista.',
                    'conclusion' => 'Entrevista y observación descriptiva, sin inferencia diagnóstica automática.', 'recomendacion' => 'Mantener acompañamiento y registrar cambios.']);
            } else {
                $activity = 'LP_AC_'.str_replace('-', '', substr($date, 2));
                if (! DB::table('actividades')->where('cod_actividad', $activity)->exists()) {
                    $this->put('actividades', $activity, ['cod_area' => 'LP_ARE_PED', 'cod_personal' => 'LP_PER_PED',
                        'tipo' => 'ESTIMULACION', 'nombre' => ['Lectura y conversación guiada', 'Música y evocación de experiencias', 'Clasificación de objetos cotidianos', 'Jardinería acompañada'][(int) CarbonImmutable::parse($date)->dayOfYear % 4], 'descripcion' => 'Lectura breve y conversación sobre experiencias cotidianas.',
                        'fecha_hora' => $date.' 09:40:00', 'duracion_minutos' => 20, 'lugar' => 'Sala de actividades', 'cupo' => 48, 'estado' => 'FINALIZADA']);
                }
                $this->put('participantes_actividad', $this->code('PA', $i, $date), ['cod_actividad' => $activity, 'cod_residente' => $r,
                    'asistencia' => 'ASISTIO', 'nivel_participacion' => $profile === 'participacion' ? 'PARCIAL' : 'ACTIVA', 'desempeno' => 'Actividad con apoyo verbal', 'observacion' => 'Participación y tolerancia registradas.']);
                $pedBase = $base;
                unset($pedBase['cod_atencion']); // No existe ese FK en seguimientos pedagógicos V2.
                $this->put('seguimientos_pedagogicos', $this->code('SP', $i, $date), $pedBase + ['cod_actividad' => $activity,
                    'atencion' => 'CONSERVADA', 'comprension_instrucciones' => 'CONSERVADA', 'ejecucion_tarea' => 'CON_APOYO_VERBAL',
                    'reconocimiento' => 'CONSERVADO', 'orientacion' => $profile === 'cognicion' ? 'PARCIAL' : 'CONSERVADA',
                    'participacion' => $profile === 'participacion' ? 'PARCIAL' : 'ACTIVA', 'interaccion' => 'COLABORADORA', 'cambio_desempeno' => false, 'observacion' => 'Se documentan la tarea, la participación y el apoyo requerido.']);
            }
        }
    }

    private function events(int $i, string $r, string $admit, string $profile): void
    {
        $date = CarbonImmutable::parse($admit)->addDays(1)->toDateString();
        $serial = sprintf('%02d', $i);
        // La visita de contacto sucede después del ingreso y antes del corte.
        $this->put('visitas', 'LP_VIS_'.$serial, ['cod_residente' => $r, 'cod_contacto' => 'LP_CTO_'.$serial,
            'cod_usuario_autorizacion' => 'LP_USU_ADMIN', 'fecha_hora_programada' => $date.' 09:00:00',
            'fecha_hora_ingreso' => $date.' 09:00:00', 'fecha_hora_salida' => $date.' 09:45:00', 'motivo' => 'Visita familiar', 'estado' => 'FINALIZADA']);
        if ($i % 3 !== 0) {
            $interval = $i % 2 ? 7 : 30;
            foreach (CarbonImmutable::parse($admit)->addDays($interval)->daysUntil(self::REFERENCE_DATE, $interval) as $visit) {
                $d = $visit->toDateString();
                $this->put('visitas', $this->code('VI', $i, $d), ['cod_residente' => $r, 'cod_contacto' => 'LP_CTO_'.$serial,
                    'cod_usuario_autorizacion' => 'LP_USU_ADMIN', 'fecha_hora_programada' => $d.' 09:00:00',
                    'fecha_hora_ingreso' => $d.' 09:00:00', 'fecha_hora_salida' => $d.' 09:30:00', 'motivo' => 'Visita familiar', 'estado' => 'FINALIZADA']);
            }
        }
        if ($profile === 'diabetes') {
            $at = $this->attention($i, $r, $date, 'MED', 'CONTROL', 'Control de glucemia y revisión clínica.');
            $study = 'LP_EST_'.$serial;
            $this->put('estudios_clinicos', $study, ['cod_residente' => $r, 'cod_atencion' => $at, 'cod_tipo_estudio' => 'LP_TEC_GLU',
                'cod_personal' => 'LP_PER_MED', 'motivo' => 'Seguimiento de diabetes conocida', 'prioridad' => 'MEDIA',
                'fecha_solicitud' => $date.' 08:00:00', 'fecha_realizacion' => $date.' 09:00:00', 'centro_medico' => 'Laboratorio institucional', 'estado' => 'REALIZADO']);
            $this->put('resultados_estudio', 'LP_RES_'.$serial, ['cod_estudio' => $study, 'cod_componente' => 'LP_COM_GLU',
                'valor_numerico' => 128 + $i % 12, 'unidad' => 'mg/dL', 'rango_referencia' => null, 'clasificacion' => null,
                'observacion' => 'Muestra en ayunas; resultado cuantificado en el informe de laboratorio.']);
            $this->put('informes_estudio', 'LP_INF_'.$serial, ['cod_estudio' => $study, 'cod_personal' => 'LP_PER_MED',
                'fecha_hora' => $date.' 09:30:00', 'hallazgos' => 'Glucemia registrada en el componente del estudio.',
                'conclusion' => 'Resultado revisado en el contexto de diabetes conocida.', 'recomendacion' => 'Mantener seguimiento individual indicado.', 'origen' => 'INTERNO', 'estado' => 'VIGENTE']);
            $content = "Informe de glucemia\nFecha: $date\nGlucosa: ".(128 + $i % 12)." mg/dL\nMuestra en ayunas. Datos sintéticos; no válido para uso clínico real.\n";
            $path = 'la-paz-2025-2026/informes/'.$serial.'.txt';
            $this->writePrivate($path, $content);
            $this->put('documentos_clinicos', 'LP_DCL_'.$serial, ['cod_residente' => $r, 'cod_estudio' => $study, 'cod_atencion' => $at,
                'cod_personal' => 'LP_PER_MED', 'tipo_documento' => 'INFORME', 'titulo' => 'Informe de glucemia', 'descripcion' => 'Informe vinculado al resultado de laboratorio.',
                'ruta_archivo' => $path, 'formato' => 'TXT', 'tamano_bytes' => strlen($content), 'hash_archivo' => hash('sha256', $content),
                'fecha_hora' => $date.' 09:30:00', 'origen' => 'INTERNO', 'estado' => 'VIGENTE']);
        }
        if (in_array($profile, ['movilidad', 'cognicion', 'nutricion'], true)) {
            $professional = ['movilidad' => 'FIS', 'cognicion' => 'PSI', 'nutricion' => 'NUT'][$profile];
            $at = $this->attention($i, $r, $date, 'MED', 'CONTROL', 'Revisión y coordinación interdisciplinaria.');
            $this->put('derivaciones', 'LP_DER_'.$serial, ['cod_residente' => $r, 'cod_area_solicitante' => 'LP_ARE_MED',
                'cod_area_receptora' => 'LP_ARE_'.$professional, 'cod_personal_solicitante' => 'LP_PER_MED', 'cod_personal_receptor' => 'LP_PER_'.$professional,
                'cod_atencion' => $at, 'motivo' => $this->history($profile, $i), 'prioridad' => 'MEDIA', 'fecha_hora' => $date.' 08:15:00',
                'respuesta' => 'Se coordina valoración y se mantiene seguimiento en el plan individual.', 'estado' => 'ATENDIDA']);
        }
        if ($profile === 'herida' || $profile === 'incidente') {
            $eventDate = CarbonImmutable::parse($admit)->addDays(21)->toDateString();
            $nurse = $this->nurse($eventDate, 0, ($i - 1) % 4);
            $j = $this->jornada($eventDate, 0);
            $this->put('incidentes', 'LP_INCI_'.$serial, ['cod_residente' => $r, 'cod_personal' => $nurse, 'cod_jornada' => $j,
                'tipo_incidente' => $profile === 'herida' ? 'LESION' : 'CAIDA', 'gravedad' => 'LEVE', 'lugar' => 'Habitación',
                'fecha_hora' => $eventDate.' 08:30:00', 'descripcion' => $profile === 'herida' ? 'Rozadura superficial en antebrazo durante una actividad.' : 'Caída al intentar levantarse sin apoyo; sin lesión observada.',
                'medida_inmediata' => 'Se asegura el entorno, se valora al residente y se comunica al médico.', 'requiere_medico' => true,
                'requiere_derivacion' => false, 'estado' => 'CERRADO']);
            $alert = 'LP_AL_'.$serial;
            $this->put('alertas', $alert, ['cod_residente' => $r, 'cod_personal_responsable' => 'LP_PER_MED',
                'tipo' => 'INCIDENTE', 'prioridad' => 'ALTA', 'modulo' => 'incidentes', 'cod_registro' => 'LP_INCI_'.$serial,
                'titulo' => 'Revisión de incidente', 'descripcion' => 'Revisión médica solicitada por el incidente documentado.',
                'fecha_hora' => $eventDate.' 08:35:00', 'generacion' => 'MANUAL', 'estado' => 'CERRADA']);
            foreach ([['CREACION', null, 'ABIERTA', '08:35:00', str_replace('PER', 'USU', $nurse), 'Incidente comunicado.'],
                ['ATENCION', 'ABIERTA', 'EN_ATENCION', '09:00:00', 'LP_USU_MED', 'Médico valora el incidente y documenta medidas.'],
                ['CIERRE', 'EN_ATENCION', 'CERRADA', '09:30:00', 'LP_USU_MED', 'Atención documentada; seguimiento de cuidados indicado.']] as $slot => [$kind, $before, $after, $time, $user, $description]) {
                $this->put('eventos_alerta', 'LP_EAL_'.$serial.'_'.$slot, ['cod_alerta' => $alert, 'cod_usuario' => $user,
                    'tipo_evento' => $kind, 'estado_anterior' => $before, 'estado_nuevo' => $after, 'fecha_hora' => $eventDate.' '.$time, 'descripcion' => $description]);
            }
            $at = $this->attention($i, $r, $eventDate, 'MED', 'CONTROL', 'Valoración posterior al incidente.', '09:00:00');
            $this->put('notas_clinicas', 'LP_NIN_'.$serial, ['cod_residente' => $r, 'cod_atencion' => $at, 'cod_personal' => 'LP_PER_MED',
                'tipo_nota' => 'EVOLUCION', 'contenido' => 'Valoración tras incidente. Residente alerta, sin síntomas sistémicos. Se documenta cuidado local o acompañamiento y vigilancia según el incidente.',
                'fecha_hora' => $eventDate.' 09:00:00', 'estado' => 'VIGENTE']);
            if ($profile === 'herida') {
                $close = CarbonImmutable::parse($eventDate)->addDays(7)->toDateString();
                $this->put('heridas', 'LP_HER_'.$serial, ['cod_residente' => $r, 'cod_personal' => $nurse, 'tipo_herida' => 'SUPERFICIAL',
                    'ubicacion' => 'Antebrazo izquierdo', 'causa' => 'Rozadura', 'clasificacion' => 'LESION_SUPERFICIAL',
                    'fecha_hora_identificacion' => $eventDate.' 08:30:00', 'fecha_hora_cierre' => $close.' 09:00:00', 'estado' => 'CERRADA']);
                for ($n = 0; $n <= 7; $n++) {
                    $d = CarbonImmutable::parse($eventDate)->addDays($n)->toDateString();
                    $this->put('curaciones_herida', $this->code('CH', $i, $d), ['cod_herida' => 'LP_HER_'.$serial,
                        'cod_personal' => $this->nurse($d, 0, ($i - 1) % 4), 'cod_jornada' => $this->jornada($d, 0), 'fecha_hora' => $d.' 09:00:00',
                        'longitud' => round(max(0, 1.4 - $n * 0.2), 2), 'ancho' => round(max(0, 0.7 - $n * 0.1), 2), 'profundidad' => 0,
                        'tejido' => $n < 7 ? 'EN_EPITELIZACION' : 'EPITELIZADO', 'exudado' => 'AUSENTE', 'olor' => 'AUSENTE', 'dolor' => $n < 3 ? 1 : 0,
                        'procedimiento' => 'Valoración y limpieza según la indicación de cuidado local.', 'materiales' => 'Solución salina y material de cura indicado.',
                        'respuesta' => $n < 7 ? 'Evolución sin signos locales de infección.' : 'Piel íntegra; cierre documentado.']);
                }
            }
        }
        if ($profile === 'movilidad') {
            $end = CarbonImmutable::parse($admit)->addDays(84)->toDateString();
            $this->put('planes_cuidado', 'LP_PFH_'.$serial, ['cod_residente' => $r, 'cod_area' => 'LP_ARE_FIS',
                'cod_personal' => 'LP_PER_FIS', 'tipo_plan' => 'INICIAL', 'nombre' => 'Recuperación de movilidad',
                'objetivo_general' => 'Mejorar traslados y tolerancia a la marcha con apoyo ajustado.', 'prioridad' => 'MEDIA',
                'fecha_hora_apertura' => $admit.' 08:30:00', 'fecha_hora_cierre' => $end.' 10:00:00', 'estado' => 'CERRADO']);
            $this->put('intervenciones_cuidado', 'LP_IFH_'.$serial, ['cod_plan' => 'LP_PFH_'.$serial,
                'nombre' => 'Ejercicio y marcha supervisada', 'descripcion' => 'Sesiones progresivas según tolerancia observada.',
                'prioridad' => 'MEDIA', 'estado' => 'INACTIVA']);
            $this->put('programaciones_cuidado', 'LP_PGH_'.$serial, ['cod_intervencion' => 'LP_IFH_'.$serial,
                'cod_turno' => 'LP_TUR_0', 'frecuencia' => 'SEMANAL', 'hora_programada' => '10:00:00',
                'fecha_activacion' => $admit, 'fecha_desactivacion' => $end, 'estado' => 'INACTIVA']);
            foreach (CarbonImmutable::parse($admit)->daysUntil($end, 7) as $session) {
                $d = $session->toDateString();
                $this->put('ejecuciones_cuidado', $this->code('EF', $i, $d), ['cod_intervencion' => 'LP_IFH_'.$serial,
                    'cod_residente' => $r, 'cod_jornada' => $this->jornada($d, 0), 'cod_personal' => 'LP_PER_FIS',
                    'fecha_hora_programada' => $d.' 10:00:00', 'fecha_hora_ejecucion' => $d.' 10:00:00',
                    'resultado' => 'Sesión realizada; mejor tolerancia y apoyo reevaluado.', 'estado' => 'REALIZADA']);
            }
        }
    }

    public function assertIntegrity(): void
    {
        $admissions = DB::table('admisiones')->whereIn('cod_preadmision', DB::table('preadmisiones')->select('cod_preadmision')->whereIn('cod_preadmision', self::solicitudes()))->get();
        if ($admissions->count() !== 48) {
            throw new RuntimeException('El conjunto debe contener 48 admisiones formales.');
        }
        foreach ($admissions as $a) {
            $r = DB::table('residentes')->where('cod_residente', $a->cod_residente)->first();
            $beds = DB::table('ocupaciones_cama')->where('cod_residente', $a->cod_residente)->where('estado', 'ACTIVA')->count();
            $days = (int) CarbonImmutable::parse(substr($a->fecha_hora_admision, 0, 10))->diffInDays(CarbonImmutable::parse(self::REFERENCE_DATE), true) + 1;
            if ($r->estado !== 'ADMITIDO' || $beds !== 1) {
                throw new RuntimeException('Admisión/ocupación incoherente.');
            }
            foreach (['signos_vitales' => 1, 'valoraciones_dolor' => 1, 'registros_conductuales' => 1,
                'registros_ingesta' => 3, 'registros_hidratacion' => 5, 'registros_eliminacion' => 2,
                'registros_movilidad' => 1, 'asignaciones_residente_jornada' => 2, 'pases_turno' => 1] as $tabla => $porDia) {
                if (DB::table($tabla)->where('cod_residente', $a->cod_residente)->count() !== $days * $porDia) {
                    throw new RuntimeException('Número de registros incompleto en '.$tabla.'.');
                }
            }
            if (! DB::table('diagnosticos')->where('cod_residente', $a->cod_residente)->exists()
                || ! DB::table('residentes_contactos')->where('cod_residente', $a->cod_residente)->where('estado', 'ACTIVO')->exists()) {
                throw new RuntimeException('Falta antecedente clínico o vínculo familiar.');
            }
            foreach (['signos_vitales', 'valoraciones_dolor', 'registros_conductuales', 'registros_ingesta', 'registros_hidratacion', 'registros_eliminacion', 'registros_movilidad', 'ejecuciones_cuidado'] as $table) {
                $time = $table === 'ejecuciones_cuidado' ? 'fecha_hora_ejecucion' : 'fecha_hora';
                $dates = DB::table($table)->where('cod_residente', $a->cod_residente)->whereNotNull($time)->selectRaw('COUNT(DISTINCT DATE('.$time.')) AS n')->value('n');
                if ((int) $dates !== $days || DB::table($table)->where('cod_residente', $a->cod_residente)->where(function ($q) use ($a, $time) {
                    $q->where($time, '<', $a->fecha_hora_admision)->orWhere($time, '>', self::CUTOFF);
                })->exists()) {
                    throw new RuntimeException('Cobertura diaria o fechas inválidas en '.$table.'.');
                }
            }
            if (DB::table('registros_sueno')->where('cod_residente', $a->cod_residente)->count() !== $days - 1
                || DB::table('controles_cognitivos')->where('cod_residente', $a->cod_residente)->count() !== (int) ceil($days / 7)
                || DB::table('atenciones')->where('cod_residente', $a->cod_residente)->where('tipo_atencion', 'SEGUIMIENTO_DIARIO')->count() !== $days) {
                throw new RuntimeException('Sueño, cognición o seguimiento diario incompletos.');
            }
            $assessments = 0;
            foreach (CarbonImmutable::parse(substr($a->fecha_hora_admision, 0, 10))->daysUntil(self::REFERENCE_DATE.' 23:59:59') as $day) {
                if ($day->isWeekday() && (int) $day->diffInDays(CarbonImmutable::parse(substr($a->fecha_hora_admision, 0, 10)), true) % 28 === 0) {
                    $assessments++;
                }
            }
            foreach (['valoraciones_psicologicas', 'valoraciones_nutricionales', 'valoraciones_funcionales', 'seguimientos_pedagogicos', 'aplicaciones_instrumento', 'mediciones_antropometricas'] as $table) {
                if (DB::table($table)->where('cod_residente', $a->cod_residente)->count() !== $assessments) {
                    throw new RuntimeException('Valoraciones incompletas en '.$table.'.');
                }
            }
        }
        $ids = $admissions->pluck('cod_residente');
        if (DB::table('residentes')->whereIn('cod_residente', $ids)->distinct()->count('fecha_nacimiento') !== 48
            || DB::table('asignaciones_residente_jornada')->whereIn('cod_residente', $ids)->where('cod_jornada', $this->jornada(self::REFERENCE_DATE, 1))->where('estado', 'ACTIVA')->count() !== 48) {
            throw new RuntimeException('Fechas de nacimiento o asignación actual incompletas.');
        }
        if (DB::table('administraciones_medicacion as a')->join('prescripciones as p', 'p.cod_prescripcion', '=', 'a.cod_prescripcion')
            ->whereIn('a.cod_residente', $ids)->whereColumn('a.cod_residente', '<>', 'p.cod_residente')->exists()) {
            throw new RuntimeException('Administración cruzada entre residentes.');
        }
        if (DB::table('administraciones_medicacion as a')->join('horarios_prescripcion as h', 'h.cod_horario_prescripcion', '=', 'a.cod_horario_prescripcion')
            ->whereIn('a.cod_residente', $ids)->whereColumn('a.cod_prescripcion', '<>', 'h.cod_prescripcion')->exists()) {
            throw new RuntimeException('Horario de otra prescripción.');
        }
        if (DB::table('resultados_estudio as r')->join('estudios_clinicos as e', 'e.cod_estudio', '=', 'r.cod_estudio')
            ->join('componentes_estudio as c', 'c.cod_componente', '=', 'r.cod_componente')
            ->whereIn('e.cod_residente', $ids)->whereColumn('e.cod_tipo_estudio', '<>', 'c.cod_tipo_estudio')->exists()) {
            throw new RuntimeException('Resultado de un componente ajeno al estudio.');
        }
        if (DB::table('ocupaciones_cama')->whereIn('cod_residente', $ids)->where('estado', 'ACTIVA')
            ->select('cod_cama')->groupBy('cod_cama')->havingRaw('count(*) > 1')->exists()) {
            throw new RuntimeException('Una cama tiene dos ocupaciones activas.');
        }
        if (DB::table('respuestas_instrumento as r')->join('aplicaciones_instrumento as a', 'a.cod_aplicacion', '=', 'r.cod_aplicacion')
            ->join('preguntas_instrumento as p', 'p.cod_pregunta', '=', 'r.cod_pregunta')->join('opciones_pregunta as o', 'o.cod_opcion', '=', 'r.cod_opcion')
            ->whereIn('a.cod_residente', $ids)->where(function ($q) {
                $q->whereColumn('a.cod_instrumento', '<>', 'p.cod_instrumento')->orWhereColumn('o.cod_pregunta', '<>', 'p.cod_pregunta');
            })->exists()) {
            throw new RuntimeException('Respuesta de instrumento incoherente.');
        }
        foreach (DB::table('aplicaciones_instrumento')->whereIn('cod_residente', $ids)->get() as $application) {
            $answers = DB::table('respuestas_instrumento')->where('cod_aplicacion', $application->cod_aplicacion);
            if ($answers->count() !== 10 || (float) $answers->sum('puntaje') !== (float) $application->puntaje_total || (float) $application->puntaje_maximo !== 100.0) {
                throw new RuntimeException('Aplicación Barthel incompleta o puntuación incoherente.');
            }
        }
        foreach (['documentos', 'documentos_clinicos'] as $table) {
            foreach (DB::table($table)->whereIn('cod_residente', $ids)->get() as $document) {
                if (! Storage::disk('local')->exists($document->ruta_archivo)
                    || hash('sha256', Storage::disk('local')->get($document->ruta_archivo)) !== $document->hash_archivo) {
                    throw new RuntimeException('Archivo clínico ausente o alterado.');
                }
            }
        }
        if (DB::table('documentos')->whereIn('cod_residente', $ids)->count() !== 48
            || DB::table('documentos_clinicos')->whereIn('cod_residente', $ids)->count() !== 4
            || DB::table('objetivos_signos_vitales')->whereIn('cod_residente', $ids)->count() !== 4) {
            throw new RuntimeException('Expedientes, informes u objetivos individuales incompletos.');
        }
    }
}
