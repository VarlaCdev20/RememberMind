<?php

namespace Database\Seeders;

use App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo;
use App\Models\EjecucionCuidado;
use App\Models\IntervencionCuidado;
use App\Models\PlanCuidado;
use App\Models\ProgramacionCuidado;
use Carbon\CarbonImmutable;
use Database\Seeders\LaPaz\ConservacionDatos;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Escenarios locales sobre el censo sintético y las jornadas de Enfermería ya instaladas. */
final class HistoriaCuidadosResidentesSeeder extends Seeder
{
    private const LOTE = 'cuidados-residentes-20261113';

    private const CORTE = '2026-11-13 10:30:00';

    private const TABLAS = ['planes_cuidado', 'intervenciones_cuidado', 'programaciones_cuidado', 'ejecuciones_cuidado'];

    public function run(): void
    {
        $r = $this->ejecutar(false);
        $this->command?->info('Ensayo revertido. Planes previstos: '.$r['nuevos_planes'].'.');
    }

    public function ejecutar(bool $guardar): array
    {
        ComplementarCensoSinteticoSeeder::comprobarDestino();
        if (CarbonImmutable::parse(self::CORTE)->isFuture()) {
            throw new RuntimeException('El corte del escenario todavía no ha ocurrido.');
        }

        if (! $guardar) {
            DB::beginTransaction();
        }

        try {
            return DB::transaction(fn () => $this->cargar());
        } finally {
            if (! $guardar) {
                DB::rollBack();
            }
        }
    }

    private function cargar(): array
    {
        DB::select('select pg_advisory_xact_lock(?)', [2026111301]);
        $conservacion = new ConservacionDatos(self::TABLAS);
        $conservacion->capturar();
        $corte = CarbonImmutable::parse(self::CORTE);
        $residentes = $this->residentes();
        $perfiles = require __DIR__.'/data/historical2026.php';
        $nuevos = $realizados = 0;

        foreach ($residentes as $residente) {
            $existentes = PlanCuidado::query()->where('cod_residente', $residente->cod_residente)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])->get();
            if ($existentes->isNotEmpty()) {
                $this->verificarLote($existentes->sole(), $residente->cod_residente);
                continue;
            }

            $jornada = $residente->jornadas->first();
            $inicio = CarbonImmutable::parse($jornada->fecha_jornada.' '.$jornada->hora_inicio);
            $programada = $inicio->addMinutes(30);
            $realizada = $programada->addMinutes(10)->lessThanOrEqualTo($corte);
            $perfil = $perfiles[(int) $residente->numero_documento - 99120001][6];
            $this->crearPlan($residente, $jornada, $programada, $realizada, $perfil);
            $nuevos++;
            $realizados += (int) $realizada;
        }

        return ['residentes' => $residentes->count(), 'nuevos_planes' => $nuevos,
            'nuevas_realizadas' => $realizados, 'nuevas_pendientes' => $nuevos - $realizados,
            'preexistentes_conservados' => $conservacion->comprobar()];
    }

    private function residentes(): Collection
    {
        $documentos = array_map(strval(...), range(99120001, 99120030));
        $residentes = DB::table('residentes as r')->join('admisiones as ad', 'ad.cod_residente', '=', 'r.cod_residente')
            ->where('r.estado', 'ADMITIDO')->whereIn('r.numero_documento', $documentos)
            ->get(['r.cod_residente', 'r.numero_documento', 'ad.fecha_hora_admision'])->sortBy('numero_documento');
        $jornadas = RegistrosConsumo::consulta('asignaciones_residente_jornada', 'a')
            ->join('jornadas as j', 'j.cod_jornada', '=', 'a.cod_jornada')
            ->join('turnos as t', 't.cod_turno', '=', 'j.cod_turno')
            ->joinSub(RegistrosConsumo::consulta('asignaciones_personal'), 'ap', function ($join) {
                $join->on('ap.cod_jornada', '=', 'a.cod_jornada')->on('ap.cod_personal', '=', 'a.cod_personal');
            })
            ->join('areas as ar', 'ar.cod_area', '=', 'ap.cod_area')
            ->join('personal as p', 'p.cod_personal', '=', 'a.cod_personal')
            ->join('usuarios as u', 'u.cod_usuario', '=', 'p.cod_usuario')
            ->whereExists(fn ($q) => $q->from('model_has_roles as mr')->join('roles as ro', 'ro.id', '=', 'mr.role_id')
                ->whereColumn('mr.model_id', 'u.cod_usuario')->where('mr.model_type', (new \App\Models\User)->getMorphClass())
                ->where('ro.name', 'ENFERMEROS')->where('ro.guard_name', 'web'))
            ->whereIn('a.estado', ['ACTIVA', 'ACTIVO'])->whereIn('ap.estado', ['ACTIVA', 'ACTIVO'])
            ->whereIn('j.estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO', 'PLANIFICADA'])
            ->whereIn('ar.estado', ['ACTIVA', 'ACTIVO'])->where('u.estado', 'ACTIVO')->where('p.estado', 'ACTIVO')
            ->whereBetween('j.fecha_jornada', ['2026-11-13', '2026-12-12'])
            ->orderBy('j.fecha_jornada')->orderBy('t.hora_inicio')->orderBy('a.cod_jornada')
            ->get(['a.cod_residente', 'a.cod_personal', 'a.cod_jornada', 'ap.cod_area', 'j.cod_turno',
                'j.fecha_jornada', 't.hora_inicio', 'u.cod_usuario'])->groupBy('cod_residente');

        if ($residentes->count() !== 30 || $residentes->unique('numero_documento')->count() !== 30) {
            throw new RuntimeException('El censo sintético debe contener treinta residentes con una admisión cada uno.');
        }

        foreach ($residentes as $r) {
            $r->jornadas = $jornadas->get($r->cod_residente, collect())->filter(
                fn ($j) => $r->fecha_hora_admision <= $j->fecha_jornada.' '.$j->hora_inicio,
            );
            if ($r->jornadas->isEmpty() || $r->jornadas->count() !== $r->jornadas->unique('cod_jornada')->count()) {
                throw new RuntimeException('El residente necesita jornadas válidas y sin responsables duplicados.');
            }
        }

        return $residentes;
    }

    private function crearPlan(object $residente, object $jornada, CarbonImmutable $momento, bool $realizada, string $perfil): void
    {
        [$nombre, $descripcion] = match ($perfil) {
            'movilidad', 'incidente', 'dolor' => ['Acompañamiento en traslados', 'Ofrecer apoyo en los traslados según la autonomía observada.'],
            'nutricion', 'diabetes' => ['Seguimiento de alimentación', 'Registrar la aceptación de alimentos y líquidos durante el turno.'],
            'cognicion', 'deterioro', 'participacion' => ['Orientación y acompañamiento', 'Explicar las actividades del turno y registrar la participación.'],
            'sueno' => ['Seguimiento de descanso', 'Consultar la calidad del descanso y registrar las observaciones del turno.'],
            default => ['Seguimiento de confort', 'Consultar las necesidades de confort y registrar observaciones de Enfermería.'],
        };

        $plan = PlanCuidado::create(['cod_residente' => $residente->cod_residente, 'cod_personal' => $jornada->cod_personal,
            'cod_area' => $jornada->cod_area, 'tipo_plan' => 'INICIAL', 'nombre' => $nombre,
            'objetivo_general' => $descripcion, 'prioridad' => 'ESTANDAR',
            'fecha_hora_apertura' => $momento->subMinutes(15), 'estado' => 'ACTIVO']);
        $intervencion = IntervencionCuidado::create(['cod_plan' => $plan->getKey(), 'nombre' => $nombre,
            'descripcion' => $descripcion, 'estado' => 'ACTIVA']);
        ProgramacionCuidado::create(['cod_intervencion' => $intervencion->getKey(), 'cod_turno' => $jornada->cod_turno,
            'frecuencia' => 'DIARIA', 'hora_programada' => $momento->format('H:i:s'), 'fecha_activacion' => $momento->toDateString(),
            'fecha_desactivacion' => $momento->toDateString(), 'estado' => 'ACTIVA']);
        EjecucionCuidado::create(['cod_intervencion' => $intervencion->getKey(), 'cod_residente' => $residente->cod_residente,
            'cod_jornada' => $jornada->cod_jornada, 'cod_personal' => $jornada->cod_personal,
            'fecha_hora_programada' => $momento, 'fecha_hora_ejecucion' => $realizada ? $momento->addMinutes(10) : null,
            'estado' => $realizada ? 'REALIZADA' : 'PENDIENTE',
            'resultado' => $realizada ? 'Cuidado registrado durante la jornada.' : null]);
        activity('Desarrollo')->performedOn($plan)->event(self::LOTE)
            ->withProperties(['origen' => 'escenario sintetico local', 'cod_usuario_contexto' => $jornada->cod_usuario])
            ->log('Carga de historial de cuidados para el censo local.');
        $this->verificarLote($plan, $residente->cod_residente);
    }

    private function verificarLote(PlanCuidado $plan, string $residente): void
    {
        $delLote = DB::table('activity_log')->where('event', self::LOTE)
            ->where('subject_type', $plan->getMorphClass())->where('subject_id', $plan->getKey())->exists();
        if (! $delLote) {
            return;
        }

        $intervencion = $plan->intervenciones()->sole();
        $intervencion->programaciones()->sole();
        $ejecucion = $intervencion->ejecuciones()->where('cod_residente', $residente)->sole();
        $vinculo = RegistrosConsumo::consulta('asignaciones_residente_jornada')->where('cod_residente', $residente)
            ->where('cod_jornada', $ejecucion->cod_jornada)->where('cod_personal', $ejecucion->cod_personal)->exists();
        if (! $vinculo) {
            throw new RuntimeException('La ejecución del cuidado requiere el mismo residente, responsable y jornada.');
        }
    }
}
