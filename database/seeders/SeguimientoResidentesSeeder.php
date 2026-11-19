<?php

namespace Database\Seeders;

use App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo;
use App\Models\ControlFuncionalIadl;
use App\Models\CuracionHerida;
use App\Models\DispositivoClinico;
use App\Models\Herida;
use App\Models\Incidente;
use App\Models\ModeloOperativo;
use App\Models\PaseTurno;
use App\Models\ResidenteContacto;
use App\Models\User;
use App\Modulos\Social\Aplicacion\Acciones\ConservarVersionVinculo;
use Carbon\CarbonImmutable;
use Database\Seeders\LaPaz\ConservacionDatos;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Carga local de observaciones y cuidados, con sus vínculos y procedencia. */
final class SeguimientoResidentesSeeder extends Seeder
{
    private const LOTE = 'seguimiento-residentes-20261113';

    private const TABLAS = [
        'pases_turno', 'incidentes', 'heridas', 'curaciones_herida', 'controles_funcionales_iadl', 'dispositivos_clinicos',
        'versiones_vinculo_contacto',
    ];

    public function run(): void
    {
        $this->command?->info(json_encode($this->ejecutar(false), JSON_THROW_ON_ERROR));
    }

    public function ejecutar(bool $guardar): array
    {
        ComplementarCensoSinteticoSeeder::comprobarDestino();

        if (! $guardar) {
            DB::beginTransaction();
        }

        try {
            return DB::transaction(function (): array {
                DB::select('select pg_advisory_xact_lock(?)', [2026111302]);
                $conservacion = new ConservacionDatos(self::TABLAS);
                $conservacion->capturar();
                $antes = $this->conteos();

                $this->registrarLesion();
                $this->prepararRelevo();
                $this->registrarObservacionesFuncionales();
                $this->registrarApoyoMovilidad();
                $this->conservarVinculosActuales();

                $nuevas = [];
                foreach ($this->conteos() as $tabla => $total) {
                    if ($total > $antes[$tabla]) {
                        $nuevas[$tabla] = $total - $antes[$tabla];
                    }
                }

                return [
                    'nuevas_por_tabla' => $nuevas,
                    'preexistentes_conservados' => $conservacion->comprobar(),
                ];
            });
        } finally {
            if (! $guardar) {
                DB::rollBack();
            }
        }
    }

    private function conteos(): array
    {
        return array_combine(self::TABLAS, array_map(fn (string $tabla): int => RegistrosConsumo::consulta($tabla)->count(), self::TABLAS));
    }

    private function conservarVinculosActuales(): void
    {
        $conservar = app(ConservarVersionVinculo::class);
        $conservar->exigir();
        $documentos = array_map(strval(...), range(99120001, 99120030));
        $vinculos = ResidenteContacto::query()->whereHas('residente', fn ($q) => $q
            ->where('estado', 'ADMITIDO')->whereIn('numero_documento', $documentos))
            ->orderBy('cod_residente_contacto')->lockForUpdate()->get();

        foreach ($vinculos as $vinculo) {
            if (! DB::table('versiones_vinculo_contacto')
                ->where('cod_residente_contacto', $vinculo->getKey())->exists()) {
                $conservar->conservar($vinculo, null, null);
            }
        }
    }

    private function asignacion(string $documento, string $inicio): object
    {
        return RegistrosConsumo::consulta('asignaciones_residente_jornada', 'a')
            ->join('residentes as r', 'r.cod_residente', '=', 'a.cod_residente')
            ->join('admisiones as ad', 'ad.cod_residente', '=', 'r.cod_residente')
            ->join('jornadas as j', 'j.cod_jornada', '=', 'a.cod_jornada')
            ->join('turnos as t', 't.cod_turno', '=', 'j.cod_turno')
            ->joinSub(RegistrosConsumo::consulta('asignaciones_personal'), 'ap', function ($join): void {
                $join->on('ap.cod_jornada', '=', 'a.cod_jornada')->on('ap.cod_personal', '=', 'a.cod_personal');
            })
            ->join('personal as p', 'p.cod_personal', '=', 'a.cod_personal')
            ->join('areas as ar', 'ar.cod_area', '=', 'ap.cod_area')
            ->where('r.numero_documento', $documento)->where('r.estado', 'ADMITIDO')
            ->where('ad.fecha_hora_admision', '<=', '2026-11-13 '.$inicio)
            ->where('j.fecha_jornada', '2026-11-13')->where('t.hora_inicio', $inicio)
            ->whereIn('a.estado', ['ACTIVA', 'ACTIVO'])->whereIn('ap.estado', ['ACTIVA', 'ACTIVO'])
            ->whereIn('ar.estado', ['ACTIVA', 'ACTIVO'])->whereIn('j.estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO', 'PLANIFICADA'])
            ->where('t.estado', 'ACTIVO')->where('p.estado', 'ACTIVO')
            ->select('a.cod_residente', 'a.cod_personal', 'a.cod_jornada', 'p.cod_usuario', 't.hora_cierre')->sole();
    }

    private function actorEnfermeria(object $asignacion, array $permisos): User
    {
        return User::query()->whereKey($asignacion->cod_usuario)->where('estado', 'ACTIVO')->role('ENFERMEROS')->get()
            ->filter(fn (User $u): bool => $u->hasAllPermissions($permisos))->sole();
    }

    private function registrarLesion(): void
    {
        $a = $this->asignacion('99120009', '07:00:00');
        $actor = $this->actorEnfermeria($a, ['incidentes.crear', 'heridas.crear', 'curaciones_herida.crear']);
        $hora = CarbonImmutable::parse('2026-11-13 08:15:00');
        $cura = $hora->addMinutes(15);
        if ($cura->isFuture() || $cura->format('H:i:s') >= $a->hora_cierre) {
            throw new RuntimeException('La curación debe pertenecer a una jornada ya iniciada.');
        }

        $contexto = ['cod_residente' => $a->cod_residente, 'cod_personal' => $a->cod_personal];
        if (! Incidente::query()->where($contexto)->where('cod_jornada', $a->cod_jornada)
            ->where('fecha_hora', $hora)->exists()) {
            $incidente = Incidente::create($contexto + [
                'cod_jornada' => $a->cod_jornada, 'tipo_incidente' => 'LESION', 'gravedad' => 'LEVE',
                'lugar' => 'Habitación', 'fecha_hora' => $hora,
                'descripcion' => 'Rozadura superficial en el antebrazo observada durante el aseo.',
                'medida_inmediata' => 'Se registró la lesión y se revisó la zona.',
                'requiere_medico' => false, 'requiere_derivacion' => false, 'estado' => 'EN_SEGUIMIENTO',
            ]);
            $this->auditar($incidente, $actor);
        }

        $herida = Herida::query()->where($contexto)->where('fecha_hora_identificacion', $hora)->first();
        if (! $herida) {
            $herida = Herida::create($contexto + [
                'tipo_herida' => 'SUPERFICIAL', 'ubicacion' => 'Antebrazo izquierdo', 'causa' => 'Rozadura',
                'clasificacion' => 'LESION_SUPERFICIAL', 'fecha_hora_identificacion' => $hora, 'estado' => 'ACTIVA',
            ]);
            $this->auditar($herida, $actor);
        }
        if (! $herida->curaciones()->where('fecha_hora', $cura)->exists()) {
            $registro = $herida->curaciones()->create([
                'cod_personal' => $a->cod_personal, 'cod_jornada' => $a->cod_jornada, 'fecha_hora' => $cura,
                'longitud' => 1.2, 'ancho' => 0.5, 'profundidad' => 0, 'exudado' => 'AUSENTE',
                'procedimiento' => 'Se revisó y registró el estado de la zona.',
                'respuesta' => 'Sin molestias adicionales durante la revisión.',
            ]);
            $this->auditar($registro, $actor);
        }
    }

    private function prepararRelevo(): void
    {
        $saliente = $this->asignacion('99120005', '07:00:00');
        $entrante = $this->asignacion('99120005', '15:00:00');
        $actor = $this->actorEnfermeria($saliente, ['pases_turno.crear']);
        $this->actorEnfermeria($entrante, ['pases_turno.editar']);
        $hora = CarbonImmutable::parse('2026-11-13 10:15:00');
        if ($saliente->hora_cierre !== '15:00:00' || $saliente->cod_personal === $entrante->cod_personal || $hora->isFuture()) {
            throw new RuntimeException('El relevo requiere dos responsables y jornadas consecutivas.');
        }

        $identidad = ['cod_residente' => $saliente->cod_residente,
            'cod_jornada_saliente' => $saliente->cod_jornada, 'cod_jornada_entrante' => $entrante->cod_jornada];
        if (! PaseTurno::query()->where($identidad)->exists()) {
            $pase = PaseTurno::create($identidad + [
                'cod_personal_saliente' => $saliente->cod_personal, 'cod_personal_entrante' => $entrante->cod_personal,
                'fecha_hora' => $hora, 'estado_general' => 'ESTABLE',
                'resumen' => 'Se registró la movilidad de la residente durante el turno.',
                'pendientes' => 'Continuar el acompañamiento durante los traslados.',
                'estado' => PaseTurno::ESTADO_BORRADOR,
            ]);
            $this->auditar($pase, $actor);
        }
    }

    private function registrarObservacionesFuncionales(): void
    {
        $actor = User::query()->whereKey('COD_USU_000084')->where('estado', 'ACTIVO')->role('PSICOLOGO/A')->sole();
        $personal = $actor->personal()->where('estado', 'ACTIVO')->where('profesion', 'PSICOLOGIA')->sole();
        if (! $actor->can('controles_funcionales_iadl.crear')) {
            throw new RuntimeException('El autor de las observaciones requiere el permiso de registro funcional.');
        }

        foreach (['99120013' => true, '99120015' => false] as $documento => $apoyo) {
            $fecha = '2026-11-12 10:00:00';
            $atencion = DB::table('atenciones as a')
                ->join('residentes as r', 'r.cod_residente', '=', 'a.cod_residente')
                ->join('admisiones as ad', 'ad.cod_residente', '=', 'r.cod_residente')
                ->where('r.numero_documento', (string) $documento)->where('r.estado', 'ADMITIDO')
                ->where('ad.fecha_hora_admision', '<=', $fecha)
                ->where('a.cod_personal', $personal->getKey())->where('a.tipo_atencion', 'VALORACION')
                ->where('a.estado', 'FINALIZADA')->where('a.fecha_hora', '<=', $fecha)
                ->orderByDesc('a.fecha_hora')->orderBy('a.cod_atencion')->first(['a.cod_atencion', 'a.cod_residente', 'a.cod_area']);
            $asignada = $atencion && $personal->asignaciones()->where('cod_area', $atencion->cod_area)
                ->whereIn('estado', ['ACTIVA', 'ACTIVO', 'FINALIZADA'])
                ->whereHas('jornada', fn ($q) => $q->whereDate('fecha_jornada', '2026-11-12')
                    ->whereHas('turno', fn ($t) => $t->where('hora_inicio', '<=', '10:00:00')->where('hora_cierre', '>', '10:00:00')))
                ->exists();
            if (! $asignada) {
                throw new RuntimeException('Falta atención de Psicología y contexto laboral para el caso funcional.');
            }

            $identidad = ['cod_residente' => $atencion->cod_residente, 'cod_personal' => $personal->getKey(),
                'actividad' => 'RUTINAS', 'fecha_hora' => $fecha];
            if (! ControlFuncionalIadl::query()->where($identidad)->exists()) {
                $control = ControlFuncionalIadl::create($identidad + [
                    'cod_usuario_registro' => $actor->getKey(), 'cod_atencion' => $atencion->cod_atencion,
                    'oportunidad' => 'OBSERVADA', 'desempeno' => $apoyo ? 'CON_APOYO' : 'INDEPENDIENTE',
                    'tipo_ayuda' => $apoyo ? 'RECORDATORIO' : 'NINGUNA',
                    'causa_ayuda' => $apoyo ? 'NO_DETERMINADA' : 'NINGUNA',
                    'nivel_previo' => 'DESCONOCIDO', 'fuente' => 'OBSERVACION_DIRECTA',
                    'estado' => 'REGISTRADO', 'registrado_en' => now(),
                    'observacion' => $apoyo ? 'Completó la rutina con un recordatorio verbal.' : 'Completó la rutina sin ayuda.',
                ]);
                $this->auditar($control, $actor);
            }
        }
    }

    private function auditar(ModeloOperativo $registro, User $actor): void
    {
        activity('Desarrollo')->performedOn($registro)->event(self::LOTE)
            ->withProperties(['origen' => 'escenario sintetico local', 'cod_usuario_contexto' => $actor->getKey()])
            ->log('Carga de seguimiento para el censo local.');
    }

    private function registrarApoyoMovilidad(): void
    {
        $asignacion = $this->asignacion('99120005', '07:00:00');
        $actor = $this->actorEnfermeria($asignacion, ['atenciones.crear']);
        $momento = CarbonImmutable::parse('2026-11-13 09:00:00');
        if ($momento->isFuture() || $momento->format('H:i:s') >= $asignacion->hora_cierre) {
            throw new RuntimeException('El apoyo de movilidad requiere una jornada que ya haya iniciado.');
        }

        $identidad = ['cod_residente' => $asignacion->cod_residente,
            'cod_personal' => $asignacion->cod_personal, 'tipo' => 'OTRO', 'fecha_colocacion' => $momento];
        if (! DispositivoClinico::query()->where($identidad)->exists()) {
            $dispositivo = DispositivoClinico::create($identidad + [
                'descripcion' => 'Andador de cuatro apoyos disponible para los traslados.',
                'ubicacion' => 'Habitación', 'estado' => 'ACTIVO',
            ]);
            $this->auditar($dispositivo, $actor);
        }
    }
}
