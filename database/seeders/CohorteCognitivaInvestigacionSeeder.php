<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Atencion;
use App\Models\Cama;
use App\Models\Contacto;
use App\Models\ControlCognitivo;
use App\Models\ControlFuncionalIadl;
use App\Models\Habitacion;
use App\Models\Instrumento;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Preadmision;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Models\ValoracionPsicologica;
use App\Models\VersionModeloExperto;
use App\Modulos\Admisiones\Aplicacion\Acciones\FormalizarAdmision;
use App\Modulos\Admisiones\Aplicacion\Acciones\RevisarSolicitudPreadmision;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoPetersen;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\CohorteCognitivaLocal;
use App\Modulos\Clinica\Aplicacion\Acciones\RegistrarExpedienteClinico;
use App\Modulos\Valoraciones\Aplicacion\Acciones\AplicarInstrumento;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Seeders\Datos\ManifiestoCognitivo30;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use LogicException;
use MotorExperto\dominio\conocimiento\versiones\PaqueteCognitivoInstitucional;
use MotorExperto\dominio\conocimiento\versiones\PaquetePetersenInstitucional;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;

/** Cohorte de ingeniería explícitamente sintética. Nunca se registra en DatabaseSeeder. */
final class CohorteCognitivaInvestigacionSeeder extends Seeder
{
    public const VERSION = CohorteCognitivaLocal::VERSION_LOTE;

    public const METODOS = CohorteCognitivaLocal::METODOS;

    public User $psicologia;

    public User $medicina;

    private array $areas = [];

    private array $instrumentos = [];

    public array $perfiles = [];

    public array $residentes = [];

    /** Entrada Artisan optativa: exige confirmar el destino de testing o la carga local autorizada. */
    public function run(): void
    {
        self::verificarDestino();
        if (getenv('RM_COHORTE30_DESTINO') !== DB::connection()->getDatabaseName()) {
            throw new LogicException('Confirme la base exacta mediante RM_COHORTE30_DESTINO antes de ejecutar este seeder.');
        }

        $this->residentes = $this->cargar(getenv('RM_COHORTE30_PREPARAR_CONOCIMIENTO') === '1');
        $this->command?->info('Cohorte sintética cargada: 30 residentes; no se han guardado diagnósticos DCL ni resultados esperados.');
    }

    /** Referencias y resultados esperados externos a las fuentes clínicas insertadas. */
    public static function manifiesto(): array
    {
        return ManifiestoCognitivo30::construir(self::casos(), self::METODOS);
    }

    /** El manifiesto contiene entradas originales de ensayo, nunca resultados persistidos. */
    public static function casos(): array
    {
        $patrones = [['COG-MEM'], ['COG-MEM'], ['COG-MEM'], ['COG-MEM', 'COG-ATE'], ['COG-MEM', 'COG-LEN'],
            ['COG-MEM', 'COG-VIS'], ['COG-ATE'], ['COG-EJE'], ['COG-LEN'], ['COG-ATE', 'COG-EJE'],
            ['COG-EJE', 'COG-VIS'], ['COG-LEN', 'COG-VIS']];
        $variantes = ['c01_negativo', 'c03_negativo', 'c04_negativo', 'impedimento_diferencial', 'c05_negativo',
            'sin_visuoespacial', 'visuoespacial_no_interpretable', 'contexto_no_interpretado',
            'sin_corroboracion', 'sin_oportunidad_iadl', 'contradiccion_memoria', 'revision_calidad_desactualizada'];
        $diagnosticos = ['Hipertensión arterial', 'Diabetes mellitus tipo 2', 'Artrosis', 'Hipoacusia', 'Catarata', 'Hipotiroidismo'];
        $casos = [];
        for ($i = 1; $i <= 30; $i++) {
            $casos[] = ['caso' => sprintf('S%02d', $i), 'alterados' => $i <= 12 ? $patrones[$i - 1] : ($i <= 18 ? [] : ['COG-MEM']),
                'identidad' => array_combine(['nombres', 'apellido_paterno', 'apellido_materno'], ManifiestoCognitivo30::NOMBRES[$i - 1]),
                'variante' => $i <= 18 ? 'completo' : $variantes[$i - 19], 'edad' => 61 + (($i * 3) % 29),
                'escolaridad_anios' => [4, 8, 12][($i - 1) % 3], 'caida' => $i % 2 === 0,
                'diagnostico_contextual' => $diagnosticos[($i - 1) % count($diagnosticos)], 'sintetico' => true];
        }

        return $casos;
    }

    public static function verificarDestino(): void
    {
        $db = DB::connection();
        if (CohorteCognitivaLocal::habilitada() && getenv('RM_COHORTE30_CARGA_LOCAL') === '1'
            && getenv('RM_COHORTE30_DESTINO') === $db->getDatabaseName()) {
            return;
        }
        if (! app()->environment('testing') || ! BaseTecnicaDesechable::permite($db->getDriverName(), $db->getDatabaseName(),
            $db->getConfig('username'), $db->getConfig('host')) || ($db->getDriverName() === 'pgsql'
                && (preg_match('/^remembermind_test_[a-f0-9]{8}_testing$/D', $db->getDatabaseName()) !== 1
                    || $db->getConfig('username') !== $db->getDatabaseName().'_runner' || $db->getConfig('host') !== '127.0.0.1'))) {
            throw new LogicException('La cohorte requiere una base desechable de testing o la carga local optativa con destino exacto; no admite producción.');
        }
    }

    /** Confirma todas las fuentes antes de consultar el coordinador, que abre su propia transacción. */
    public function cargar(bool $prepararConocimientoLocal = false): array
    {
        self::verificarDestino();
        if ($prepararConocimientoLocal && (! app()->environment('local')
            || ! CohorteCognitivaLocal::habilitada() || getenv('RM_COHORTE30_CARGA_LOCAL') !== '1'
            || getenv('RM_COHORTE30_DESTINO') !== DB::connection()->getDatabaseName())) {
            throw new LogicException('Preparar conocimiento exige la carga local explícita y la base exacta.');
        }
        if (Residente::query()->where('numero_documento', 'like', 'COH30-%')->exists()) {
            throw new LogicException('La cohorte ya existe. No se repite ni se sobreescribe historia; use otra base desechable.');
        }
        // Un intento revertido no debe dejar selectores/identidades del intento anterior en memoria.
        $this->areas = $this->instrumentos = $this->perfiles = $this->residentes = [];
        $anterior = Carbon::getTestNow();
        $anteriorInmutable = CarbonImmutable::getTestNow();
        $perfilesAnteriores = config('sistema_experto.perfiles_instrumentales');
        try {
            return DB::transaction(function () use ($prepararConocimientoLocal): array {
                $this->reloj('2026-10-15 09:00:00');
                if (! app()->environment('local')) {
                    app(RolesAndPermissionsSeeder::class)->run();
                } else {
                    // Por defecto reutiliza gobierno instalado; la preparación requiere opt-in independiente.
                    foreach ([PaqueteCognitivoInstitucional::class,
                        PaquetePetersenInstitucional::class] as $paquete) {
                        if (! VersionModeloExperto::query()->where('codigo_version', $paquete::CODIGO)
                            ->where('observacion', $paquete::FUNDAMENTO)->where('estado', 'ACTIVO')->exists()) {
                            if (! $prepararConocimientoLocal) {
                                throw new LogicException('Falta el conocimiento institucional vigente; el seeder no lo activará.');
                            }
                        }
                    }
                }
                $admin = $this->cuenta('coh30.administracion@example.invalid');
                $admin->assignRole('ADMINISTRADOR');
                $admin->givePermissionTo(['preadmisiones.revisar', 'admisiones.formalizar']);
                $this->psicologia = $this->profesional('PSICOLOGO/A', 'PSICOLOGIA');
                $this->medicina = $this->profesional('MEDICO GENERAL/GERIATRA', 'MEDICINA');
                if (! app()->environment('local') || $prepararConocimientoLocal) {
                    app(RegistrarConocimientoPetersen::class)->ejecutar($this->psicologia);
                    app(RegistrarConocimientoInstitucional::class)->ejecutar($this->psicologia);
                    foreach ([PaqueteCognitivoInstitucional::class, PaquetePetersenInstitucional::class] as $paquete) {
                        if (! VersionModeloExperto::query()->where('codigo_version', $paquete::CODIGO)
                            ->where('observacion', $paquete::FUNDAMENTO)->where('estado', 'ACTIVO')->exists()) {
                            throw new LogicException('El paquete existente no está vigente; no se activa ni sobrescribe automáticamente.');
                        }
                    }
                }
                $this->catalogo();
                config(['sistema_experto.perfiles_instrumentales' => $this->perfiles]);
                $habitacion = Habitacion::create(['codigo' => 'COHORTE-SINTETICA-30', 'capacidad' => 30, 'estado' => 'ACTIVA']);
                $residentes = [];
                foreach (self::casos() as $caso) {
                    $this->reloj('2026-01-01 09:00:00');
                    $contacto = Contacto::create(['nombres' => 'Contacto sintético', 'apellido_paterno' => $caso['caso'], 'estado' => 'ACTIVO']);
                    $cama = Cama::create(['cod_habitacion' => $habitacion->getKey(), 'codigo' => 'COH30-'.$caso['caso'], 'estado' => 'ACTIVA']);
                    $pre = Preadmision::create(['cod_contacto' => $contacto->getKey(), 'cod_usuario_registro' => $admin->getKey(),
                        ...$caso['identidad'], 'numero_documento' => 'COH30-'.$caso['caso'],
                        'fecha_nacimiento' => (2026 - $caso['edad']).'-01-01', 'motivo_ingreso' => 'Cohorte de ingeniería, sin historia clínica real.',
                        'fecha_solicitud' => '2026-01-01', 'estado' => 'PENDIENTE']);
                    app(RevisarSolicitudPreadmision::class)->aprobar($admin, $pre);
                    $this->reloj('2026-01-02 10:00:00');
                    $r = app(FormalizarAdmision::class)->ejecutar($pre->fresh(), ['cod_cama' => $cama->getKey(), 'cod_contacto' => $contacto->getKey(),
                        'fecha_hora_admision' => '2026-01-02 10:00:00', 'nivel_educativo' => $caso['escolaridad_anios'] <= 8 ? 'PRIMARIA' : 'SECUNDARIA',
                        'observacion' => CohorteCognitivaLocal::MARCA_ADMISION], $admin);
                    $this->reloj('2026-10-15 08:00:00');
                    $this->contexto($r, $caso);
                    $this->reloj('2026-10-15 09:00:00');
                    $this->cognicion($r, $caso);
                    $residentes[$caso['caso']] = $r;
                }

                return $residentes;
            });
        } catch (\Throwable $e) {
            config(['sistema_experto.perfiles_instrumentales' => $perfilesAnteriores]);
            throw $e;
        } finally {
            Carbon::setTestNow($anterior);
            CarbonImmutable::setTestNow($anteriorInmutable);
        }
    }

    private function reloj(string $fecha): void
    {
        $instante = CarbonImmutable::parse($fecha, config('app.timezone'));
        Carbon::setTestNow($instante);
        CarbonImmutable::setTestNow($instante);
    }

    private function profesional(string $rol, string $profesion): User
    {
        $u = $this->cuenta('coh30.'.strtolower($profesion).'@example.invalid');
        $u->assignRole($rol);
        $p = Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional sintético', 'apellido_paterno' => $profesion,
            'numero_documento' => 'COH30-'.$profesion, 'profesion' => $profesion, 'estado' => 'ACTIVO']);
        $area = Area::create(['nombre' => 'Área sintética '.$profesion, 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno sintético '.$profesion, 'hora_inicio' => '00:00', 'hora_cierre' => '23:59', 'orden' => count($this->areas) + 1, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_personal' => $p->getKey(), 'cod_jornada' => $jornada->getKey(),
            'cod_area' => $area->getKey(), 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
        $this->areas[$profesion] = $area->getKey();

        return $u->fresh('personal');
    }

    private function cuenta(string $correo): User
    {
        // Procedencia del fixture, sin publicar una contraseña conocida para iniciar sesión.
        return User::factory()->create(['estado' => 'ACTIVO', 'correo' => $correo, 'password' => Hash::make(Str::random(64))]);
    }

    private function catalogo(): void
    {
        foreach (self::METODOS as $dominio => $metodo) {
            $meta = (new RegistroMetodosInstrumentales)->metodos()[$metodo];
            $inst = Instrumento::create(['codigo' => 'COH30-'.$dominio, 'nombre' => 'Captura sintética '.$dominio,
                'tipo' => 'COGNITIVO', 'version' => 'TEST-1', 'estado' => 'ACTIVO']);
            $selectores = [];
            foreach ($meta['entradas'] as $campo => $tipo) {
                // Campos de entrada del algoritmo; NO preguntas, láminas ni estímulos de una prueba protegida.
                $q = PreguntaInstrumento::create(['cod_instrumento' => $inst->getKey(), 'codigo' => 'ENTRADA-'.count($selectores),
                    'enunciado' => 'Dato sintético: '.$campo, 'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO',
                    'orden' => count($selectores) + 1, 'estado' => 'ACTIVA']);
                $selectores[$campo] = ['cod_pregunta' => $q->getKey(), 'campo' => $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero'];
            }
            $this->instrumentos[$dominio] = $inst;
            $this->perfiles[] = ['id' => 'COH30-'.$dominio, 'cod_instrumento' => $inst->getKey(), 'codigo_instrumento' => $inst->codigo,
                'version_instrumento' => 'TEST-1', 'metodo' => $metodo, 'identidad_instrumento' => $meta['identidad'], 'selectores' => $selectores];
        }
    }

    private function atencion(Residente $r, User $u): Atencion
    {
        return app(RegistrarExpedienteClinico::class)->crearAtencion($u, ['cod_area' => $this->areas[$u->personal->profesion],
            'tipo_atencion' => 'VALORACION', 'motivo' => self::VERSION.' · Acto sintético.'], $r);
    }

    private function contexto(Residente $r, array $caso): void
    {
        $accion = app(RegistrarExpedienteClinico::class);
        $a = $this->atencion($r, $this->medicina);
        $accion->registrar($this->medicina, ['cod_atencion' => $a->getKey(), 'nombre' => $caso['diagnostico_contextual'],
            'tipo' => 'CONTEXTO_SINTETICO', 'observacion' => 'Antecedente de ensayo; no representa un diagnóstico real ni determina DCL.'], $r, 'diagnostico');
        $accion->registrar($this->medicina, ['tipo_antecedente' => 'PERSONAL', 'descripcion' => 'Antecedente sintético: '.$caso['diagnostico_contextual'],
            'fecha_referencia' => '2025-01-01', 'fuente_informacion' => self::VERSION], $r, 'antecedente');
        $accion->registrar($this->medicina, ['cod_atencion' => $a->getKey(), 'tipo_nota' => 'EVOLUCION',
            'contenido' => 'Caso sintético. Escolaridad: '.$caso['escolaridad_anios'].' años. Sin traslado de datos reales.'], $r, 'nota');
        // Seguimiento longitudinal de contexto, sembrado como fuentes de ingeniería.
        // No sustituye mediciones ni se cuenta como 14 corroboraciones de un mismo episodio.
        foreach (range(0, 13) as $dia) {
            $fecha = CarbonImmutable::parse('2026-09-20 10:00:00')->addDays($dia);
            $observacion = Atencion::create(['cod_residente' => $r->getKey(), 'cod_personal' => $this->medicina->personal->getKey(),
                'cod_area' => $this->areas['MEDICINA'], 'tipo_atencion' => 'SEGUIMIENTO', 'fecha_hora' => $fecha, 'estado' => 'FINALIZADA']);
            ControlCognitivo::create(['cod_residente' => $r->getKey(), 'cod_personal' => $this->medicina->personal->getKey(),
                'cod_atencion' => $observacion->getKey(), 'fecha_hora' => $fecha, 'memoria_reciente' => null,
                'repite_preguntas' => $dia % 4 === 0 && in_array('COG-MEM', $caso['alterados'], true),
                'sigue_instrucciones' => $dia % 5 === 0 ? null : true, 'confusion' => false,
                'observacion' => self::VERSION.' · Observación nominal sintética, episodio '.$dia, 'estado' => 'VIGENTE']);
        }
        foreach ([0, 1, 2] as $dia) {
            $this->reloj('2026-10-15 08:0'.$dia.':00');
            $accion->registrar($this->medicina, ['cod_atencion' => $a->getKey(), 'presion_sistolica' => 120 + 5 * $dia,
                'presion_diastolica' => 75 + $dia, 'frecuencia_cardiaca' => 70 + $dia, 'frecuencia_respiratoria' => 16,
                'temperatura' => 36.5, 'saturacion_oxigeno' => 96, 'glucemia' => null, 'observacion' => 'Medición sintética seriada '.$dia], $r, 'signo-vital');
        }
        $accion->registrar($this->medicina, ['cod_atencion' => $a->getKey(), 'intensidad' => $caso['caida'] ? 2 : 0,
            'ubicacion' => $caso['caida'] ? 'Rodilla, caso sintético' : null, 'observacion' => 'Dato sintético explícito.'], $r, 'dolor');
        if ($caso['caida']) {
            $accion->registrar($this->medicina, ['tipo_incidente' => 'CAIDA', 'lugar' => 'Espacio de prueba',
                'descripcion' => 'Caída sintética durante traslado. No se infiere DCL de este incidente.',
                'medida_inmediata' => 'Revisión contextual de ensayo.', 'requiere_medico' => true, 'requiere_derivacion' => false], $r, 'incidente');
        }
    }

    private function juicio(Residente $r, string $elemento, string $estado, array $soportes, array $comprobaciones = []): ValoracionPetersenOriginal
    {
        return app(RegistrarValoracionPetersenOriginal::class)->ejecutar($this->psicologia, $r, [
            'solicitud_uuid' => (string) Str::uuid(), 'elemento' => $elemento, 'estado_original' => $estado,
            'justificacion' => self::VERSION.' · Juicio original sintético '.$elemento.'='.$estado.'. No es una firma clínica real.',
            'fecha_hora' => now()->format('Y-m-d H:i:s'), 'tipo_registro' => 'REGISTRO', 'soportes' => $soportes, ...$comprobaciones]);
    }

    private function cognicion(Residente $r, array $caso): void
    {
        $v = $caso['variante'];
        $revisados = $mediciones = [];
        foreach ($this->perfiles as $perfil) {
            $dominio = array_search($perfil['metodo'], self::METODOS, true);
            if ($dominio === 'COG-VIS' && $v === 'sin_visuoespacial') {
                continue;
            }
            $alterado = in_array($dominio, $caso['alterados'], true);
            $valores = ['ensayo_1' => $alterado ? 3 : 8, 'ensayo_2' => $alterado ? 4 : 10, 'ensayo_3' => $alterado ? 5 : 11,
                'recuerdo_diferido' => $alterado ? 2 : 10, 'demora_minutos' => 22, 'escolaridad_anios' => $caso['escolaridad_anios'],
                'forma_n_aciertos' => $alterado ? 3 : 9, 'forma_l_aciertos' => $alterado ? 4 : 9,
                'segundos_parte_b' => $alterado ? 240.5 : 90.5, 'errores_observados' => 0,
                'aciertos_espontaneos' => $alterado ? 20 : 55, 'aciertos_clave_semantica' => 2,
                'puntaje_copia' => $alterado ? 16.5 : 34.5, 'elementos_revisados' => 18];
            $respuestas = [];
            foreach ($perfil['selectores'] as $campo => $s) {
                // Contexto BO declarado para TODOS los casos de ingeniería de esta cohorte,
                // con audición/visión/motricidad compatibles y administración estándar.
                $valor = $s['campo'] === 'valor_logico' ? true : ($valores[$campo] ?? throw new LogicException('Bruto sintético no definido: '.$campo));
                $respuestas[] = ['cod_pregunta' => $s['cod_pregunta'], $s['campo'] => $valor];
            }
            $a = $this->atencion($r, $this->psicologia);
            $app = app(AplicarInstrumento::class)->aplicar($this->psicologia, ['fecha_hora' => now()->format('Y-m-d H:i:s'),
                'cod_atencion' => $a->getKey(), 'respuestas' => $respuestas, 'observacion' => self::VERSION.' · Valores sintéticos sin reactivos protegidos.'],
                $this->instrumentos[$dominio], $r);
            $mediciones[$dominio] = $app->getKey();
            $estado = $dominio === 'COG-VIS' && $v === 'visuoespacial_no_interpretable' ? 'I' : ($alterado ? 'A' : 'P');
            $original = $this->juicio($r, $dominio, $estado, [['tipo_soporte' => 'MEDICION', 'cod_aplicacion' => $app->getKey()]],
                ['alcance_dominio' => true, 'contexto_interpretado' => ! ($v === 'contexto_no_interpretado' && $dominio === 'COG-MEM')]);
            $revisados[] = ['tipo_soporte' => 'DOMINIO_REVISADO', 'cod_valoracion_revisada' => $original->getKey()];
        }
        $cambios = [];
        foreach (['2026-10-10 09:00:00', '2026-10-15 09:00:00'] as $i => $fecha) {
            $a = Atencion::create(['cod_residente' => $r->getKey(), 'cod_personal' => $this->medicina->personal->getKey(),
                'cod_area' => $this->areas['MEDICINA'], 'tipo_atencion' => 'VALORACION', 'fecha_hora' => $fecha, 'estado' => 'FINALIZADA']);
            $cambios[] = ControlCognitivo::create(['cod_residente' => $r->getKey(), 'cod_personal' => $this->medicina->personal->getKey(),
                'cod_atencion' => $a->getKey(), 'fecha_hora' => $fecha, 'memoria_reciente' => null,
                'repite_preguntas' => $i === 1 && in_array('COG-MEM', $caso['alterados'], true), 'confusion' => false,
                'observacion' => self::VERSION.($i === 0 ? ' · Referencia previa: manejo autónomo de indicaciones habituales.'
                    : ($v === 'c01_negativo' ? ' · Corroboración: no se identifica un cambio respecto a la referencia previa.'
                        : ' · Episodio posterior independiente: dificultades nuevas en las tareas habituales respecto a la referencia previa.')),
                'estado' => 'VIGENTE']);
        }
        $soportes = [['tipo_soporte' => 'HISTORIA_PREVIA', 'cod_control_cognitivo' => $cambios[0]->getKey()]];
        if ($v !== 'sin_corroboracion') {
            $soportes[] = ['tipo_soporte' => 'CORROBORACION', 'cod_control_cognitivo' => $cambios[1]->getKey()];
        }
        $this->juicio($r, 'C01', $v === 'c01_negativo' ? 'N' : 'S', $soportes,
            ['cambio_respecto_previo' => $v !== 'c01_negativo', 'corroboracion_independiente' => $v !== 'sin_corroboracion']);
        $iadl = ControlFuncionalIadl::create(['cod_residente' => $r->getKey(), 'cod_personal' => $this->psicologia->personal->getKey(),
            'cod_usuario_registro' => $this->psicologia->getKey(), 'cod_atencion' => $this->atencion($r, $this->psicologia)->getKey(),
            'fecha_hora' => now(), 'registrado_en' => now(), 'actividad' => 'MEDICACION', 'oportunidad' => $v === 'sin_oportunidad_iadl' ? 'SIN_OPORTUNIDAD' : 'OBSERVADA',
            'desempeno' => $v === 'sin_oportunidad_iadl' ? 'NO_EVALUABLE' : ($v === 'c03_negativo' ? 'CON_APOYO' : 'INDEPENDIENTE'),
            'tipo_ayuda' => $v === 'sin_oportunidad_iadl' ? 'NO_APLICA' : ($v === 'c03_negativo' ? 'SUSTITUCION' : 'NINGUNA'),
            'causa_ayuda' => $v === 'sin_oportunidad_iadl' ? 'NO_APLICA' : ($v === 'c03_negativo' ? 'COGNITIVA' : 'NINGUNA'),
            'nivel_previo' => 'INDEPENDIENTE', 'fuente' => 'OBSERVACION_DIRECTA', 'estado' => 'REGISTRADO']);
        $this->juicio($r, 'C03', $v === 'c03_negativo' ? 'N' : 'S', [['tipo_soporte' => 'IADL', 'cod_control_iadl' => $iadl->getKey()]],
            ['revision_integrada' => true, 'oportunidad_adecuada' => $v !== 'sin_oportunidad_iadl', 'atribucion_revisada' => true]);
        foreach (['C04', 'C05'] as $elemento) {
            $psico = ValoracionPsicologica::create(['cod_residente' => $r->getKey(), 'cod_personal' => $this->psicologia->personal->getKey(),
                'cod_atencion' => $this->atencion($r, $this->psicologia)->getKey(), 'fecha_hora' => now(),
                'conclusion' => self::VERSION.' · Revisión sintética '.$elemento.'. Variante '.$v, 'estado' => 'VIGENTE']);
            $condiciones = $elemento === 'C04' ? ['revision_explicita' => true, 'factores_pertinentes_revisados' => true,
                'impedimento_no_resuelto' => $v === 'impedimento_diferencial'] : ['suficiencia_conjunto' => $v !== 'c05_negativo',
                    'condiciones_revisadas' => true, 'exclusiones_revisadas' => true];
            $negativo = ($elemento === 'C04' && $v === 'c04_negativo') || ($elemento === 'C05' && $v === 'c05_negativo');
            $this->juicio($r, $elemento, $negativo ? 'N' : 'S', [['tipo_soporte' => $elemento === 'C04' ? 'DIFERENCIAL' : 'CALIDAD',
                'cod_valoracion_psicologica' => $psico->getKey()], ...($elemento === 'C05' ? $revisados : [])], $condiciones);
        }
        if (in_array($v, ['contradiccion_memoria', 'revision_calidad_desactualizada'], true)) {
            $this->juicio($r, 'COG-MEM', $v === 'contradiccion_memoria' ? 'P' : 'A',
                [['tipo_soporte' => 'MEDICION', 'cod_aplicacion' => $mediciones['COG-MEM']]], ['alcance_dominio' => true, 'contexto_interpretado' => true]);
        }
    }
}
