<?php

namespace App\Modulos\Alertas\Aplicacion\Consultas;

use App\Compartido\Aplicacion\Contratos\EnlacesAplicacion;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Medicacion\Aplicacion\Consultas\AgendaMedicacionService;

final class AlertasOrientativasSalud
{
    public function __construct(private readonly EnlacesAplicacion $enlaces) {}

    public function consultar(?User $usuario, string $filtroTipo = ''): array
    {

        abort_unless($usuario && $usuario->estado === 'ACTIVO' && $usuario->can('alertas.ver') && ! $usuario->hasRole('FAMILIAR'), 403);
        $usuario = $usuario->fresh();
        abort_unless($usuario?->estado === 'ACTIVO', 403);
        $mostrarFicha = $usuario->can('atenciones.ver');
        $mostrarValoracion = $usuario->can('valoraciones_funcionales.ver');
        $mostrarMedicacion = $usuario->canAny(['prescripciones.ver', 'administraciones_medicacion.ver']);
        $relaciones = [];
        if ($mostrarFicha) {
            $relaciones['fichasMedicas'] = fn ($q) => $q->where('estado', 'ACTIVA')->latest('fecha_hora')->limit(1);
        }
        if ($mostrarValoracion) {
            $relaciones['valoracionesFuncionales'] = fn ($q) => $q->latest('fecha_hora')->limit(1);
        }
        $adultos = Residente::with($relaciones)
            ->when(! $usuario->hasAnyRole(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR']), fn ($q) => $q->whereIn('cod_residente', app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosQuery($usuario)->select('cod_residente')))
            ->whereIn('estado', ['ACTIVO', 'ADMITIDO', 'SEGUIMIENTO_ESPECIAL'])->orderBy('apellido_paterno')->get();
        $vencidas = $mostrarMedicacion
            ? app(AgendaMedicacionService::class)->paraAdultos($adultos->modelKeys())->where('estado', 'VENCIDA')->groupBy(fn ($dosis) => $dosis['medicacion']->cod_residente)
            : collect();
        $alertas = collect();
        foreach ($adultos as $adulto) {
            $fichaMedica = $mostrarFicha ? $adulto->fichasMedicas->first() : null;
            $valFuncional = $mostrarValoracion ? $adulto->valoracionesFuncionales->first() : null;
            if ($mostrarFicha && ! $fichaMedica) {
                $alertas->push(['adulto' => $adulto, 'tipo' => 'Ficha Médica', 'nivel' => 'atencion', 'mensaje' => 'Ficha médica no registrada.', 'accion' => 'requiere revisión', 'ruta' => $this->enlaces->destino('admin.salud-seguimiento.ficha', $adulto->cod_residente)]);
            }
            if ($vencidas->has($adulto->cod_residente)) {
                $alertas->push(['adulto' => $adulto, 'tipo' => 'Medicación', 'nivel' => 'atencion',
                    'mensaje' => $vencidas->get($adulto->cod_residente)->count().' dosis programada(s) vencida(s) sin administración u omisión registrada.',
                    'accion' => 'revisar agenda', 'ruta' => $this->enlaces->destino('admin.salud-seguimiento.administracion', $adulto->cod_residente)]);
            }
            if ($valFuncional) {
                if ($valFuncional->riesgo_caida === 'ALTO') {
                    $alertas->push(['adulto' => $adulto, 'tipo' => 'Valoración', 'nivel' => 'critica', 'mensaje' => 'Riesgo de caída alto.', 'accion' => 'requiere revisión', 'ruta' => $this->enlaces->destino('admin.salud-seguimiento.valoracion', $adulto->cod_residente)]);
                }
                if (\in_array($valFuncional->nivel_dependencia, ['ALTA_DEPENDENCIA', 'SUPERVISION_PERMANENTE'])) {
                    $alertas->push(['adulto' => $adulto, 'tipo' => 'Valoración', 'nivel' => 'atencion', 'mensaje' => 'Dependencia funcional alta.', 'accion' => 'requiere revisión', 'ruta' => $this->enlaces->destino('admin.salud-seguimiento.valoracion', $adulto->cod_residente)]);
                }
            } elseif ($mostrarValoracion) {
                $alertas->push(['adulto' => $adulto, 'tipo' => 'Valoración', 'nivel' => 'atencion', 'mensaje' => 'Sin valoración funcional registrada.', 'accion' => 'requiere revisión', 'ruta' => $this->enlaces->destino('admin.salud-seguimiento.valoracion', $adulto->cod_residente)]);
            }
        }
        if ($filtroTipo) {
            $alertas = $alertas->where('tipo', $filtroTipo);
        }

        return ['alertas' => $alertas, 'conteos' => ['total' => $alertas->count(), 'criticas' => $alertas->where('nivel', 'critica')->count(), 'preventivas' => $alertas->where('nivel', 'atencion')->count(), 'tipos' => $alertas->groupBy('tipo')->map->count()]];
    }
}
