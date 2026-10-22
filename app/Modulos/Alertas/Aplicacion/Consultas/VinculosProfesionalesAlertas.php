<?php

namespace App\Modulos\Alertas\Aplicacion\Consultas;

use App\Models\AdministracionMedicacion;
use App\Models\Atencion;
use App\Models\EjecucionCuidado;
use App\Models\Incidente;
use App\Models\SignoVital;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Organización de lectura por vínculos guardados, nunca triaje ni autorización. */
final class VinculosProfesionalesAlertas
{
    public const ESPECIALIDADES = [
        'ENFERMEROS' => 'Enfermería', 'MEDICO GENERAL/GERIATRA' => 'Medicina y geriatría',
        'PSICOLOGO/A' => 'Psicología', 'NUTRICIONISTA' => 'Nutrición',
        'FISIOTERAPEUTA' => 'Fisioterapia', 'PEDAGOGO' => 'Pedagogía',
    ];

    public function aplicar(Builder $consulta, User $usuario, string $rol): Builder
    {
        abort_unless($rol === '' || $rol === 'SIN_VINCULO' || isset(self::ESPECIALIDADES[$rol]), 422);
        if ($rol === '') {
            return $consulta;
        }
        $roles = $rol === 'SIN_VINCULO' ? array_keys(self::ESPECIALIDADES) : [$rol];
        $condicion = fn (Builder $q) => $this->condicion($q, $usuario, $roles);

        return $rol === 'SIN_VINCULO' ? $consulta->whereNot($condicion) : $consulta->where($condicion);
    }

    private function condicion(Builder $q, User $usuario, array $roles): void
    {
        $q->whereHas('responsable.usuario.roles', fn ($r) => $r->whereIn('name', $roles));
        foreach ($this->fuentes($usuario) as $fuente) {
            $modelo = new $fuente['modelo'];
            $tabla = $modelo->getTable();
            $origen = $this->consultaFuente($fuente)
                ->whereColumn($tabla.'.'.$modelo->getKeyName(), 'alertas.cod_registro')
                ->whereColumn($tabla.'.cod_residente', 'alertas.cod_residente')
                ->where(function (Builder $origen) use ($fuente, $roles) {
                    foreach ($fuente['autores'] as $relacion => $motivo) {
                        $origen->orWhereHas($relacion, fn ($r) => $r->whereIn('name', $roles));
                    }
                });
            $q->orWhere(function (Builder $q) use ($fuente, $origen) {
                $this->modulo($q, $fuente);
                $q->whereExists($origen->selectRaw('1')->toBase());
            });
        }
    }

    /** Una carga por tipo de fuente presente en la página, sin consultas por tarjeta. */
    public function paraAlertas(User $usuario, Collection $alertas): array
    {
        (new \Illuminate\Database\Eloquent\Collection($alertas->all()))->loadMissing('responsable.usuario.roles');
        $resultado = [];
        foreach ($alertas as $alerta) {
            $resultado[$alerta->getKey()] = [];
            $this->agregar($resultado[$alerta->getKey()], $alerta->responsable?->usuario?->roles ?? collect(), 'Responsable del seguimiento');
        }
        foreach ($this->fuentes($usuario) as $fuente) {
            $candidatas = $alertas->filter(fn ($a) => filled($a->cod_registro)
                && in_array(mb_strtoupper(trim((string) $a->modulo)), $fuente['modulos'], true)
                && (! isset($fuente['tipo']) || $a->tipo === $fuente['tipo']));
            if ($candidatas->isEmpty()) {
                continue;
            }
            $registros = $this->consultaFuente($fuente)->whereKey($candidatas->pluck('cod_registro'))
                ->with(array_keys($fuente['autores']))->get()->keyBy(fn ($r) => $r->getKey());
            foreach ($candidatas as $alerta) {
                $registro = $registros->get($alerta->cod_registro);
                if (! $registro || $registro->cod_residente !== $alerta->cod_residente) {
                    continue;
                }
                foreach ($fuente['autores'] as $relacion => $motivo) {
                    $this->agregar($resultado[$alerta->getKey()], data_get($registro, $relacion) ?? collect(), $motivo);
                }
            }
        }

        return array_map(fn ($vinculos) => array_values($vinculos), $resultado);
    }

    private function agregar(array &$vinculos, Collection $roles, string $motivo): void
    {
        foreach ($roles as $rol) {
            if (isset(self::ESPECIALIDADES[$rol->name])) {
                $vinculos[$rol->name] ??= ['rol' => $rol->name, 'nombre' => self::ESPECIALIDADES[$rol->name], 'motivos' => []];
                if (! in_array($motivo, $vinculos[$rol->name]['motivos'], true)) {
                    $vinculos[$rol->name]['motivos'][] = $motivo;
                }
            }
        }
    }

    private function fuentes(User $usuario): array
    {
        $fuentes = [
            ['modelo' => Incidente::class, 'modulos' => ['INCIDENTE', 'INCIDENTES'], 'permiso' => 'incidentes.ver', 'autores' => ['personal.usuario.roles' => 'Profesional que registró el incidente de origen']],
            ['modelo' => Atencion::class, 'modulos' => ['SOLICITUD_MEDICA'], 'permiso' => 'atenciones.ver', 'autores' => ['personal.usuario.roles' => 'Profesional de la atención de origen']],
            ['modelo' => SignoVital::class, 'modulos' => ['SIGNOS', 'SIGNOS_VITALES'], 'permiso' => 'signos_vitales.ver', 'autores' => ['personal.usuario.roles' => 'Profesional de la medición de origen']],
            ['modelo' => AdministracionMedicacion::class, 'modulos' => ['MEDICACION'], 'permiso' => 'administraciones_medicacion.ver', 'autores' => ['personal.usuario.roles' => 'Profesional de la administración de origen']],
            ['modelo' => EjecucionCuidado::class, 'modulos' => ['PLAN'], 'permiso' => 'ejecuciones_cuidado.ver', 'autores' => ['personal.usuario.roles' => 'Profesional de la ejecución de cuidado']],
        ];
        if ($usuario->can('intervenciones_cuidado.ver') && $usuario->can('planes_cuidado.ver')) {
            $fuentes[4]['autores']['intervencion.plan.personal.usuario.roles'] = 'Profesional responsable del plan de origen';
        }
        $fuentes[] = ['modelo' => Atencion::class, 'modulos' => ['INCIDENTE'], 'tipo' => 'INCIDENTE EN SEGUIMIENTO',
            'permiso' => 'atenciones.ver', 'autores' => ['personal.usuario.roles' => 'Profesional de la atención de origen']];

        return array_values(array_filter($fuentes, fn ($f) => $usuario->can($f['permiso'])));
    }

    private function consultaFuente(array $fuente): Builder
    {
        $q = $fuente['modelo']::query();
        if ($fuente['modelo'] === EjecucionCuidado::class) {
            $q->whereHas('intervencion.plan', fn ($p) => $p->whereColumn('planes_cuidado.cod_residente', 'ejecuciones_cuidado.cod_residente'));
        }
        if (isset($fuente['tipo'])) {
            // El lector de procedencia da precedencia al incidente real si coincide su referencia.
            $q->whereNotExists(fn ($i) => $i->selectRaw('1')->from('incidentes')
                ->whereColumn('incidentes.cod_incidente', 'atenciones.cod_atencion')
                ->whereColumn('incidentes.cod_residente', 'atenciones.cod_residente'));
        }

        return $q;
    }

    private function modulo(Builder $q, array $fuente): void
    {
        $q->whereNotNull('alertas.modulo')->whereIn(DB::raw('UPPER(TRIM(alertas.modulo))'), $fuente['modulos']);
        if (isset($fuente['tipo'])) {
            $q->where('alertas.tipo', $fuente['tipo']);
        }
    }
}
