<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResidenteAdministrativoController extends Controller
{
    private const TABS = [
        'resumen' => 'Resumen', 'admision' => 'Admisión', 'ubicacion' => 'Ubicación',
        'contactos' => 'Contactos', 'documentacion' => 'Documentación',
        'consentimientos' => 'Consentimientos', 'seguro' => 'Seguro',
        'actividades' => 'Actividades', 'visitas' => 'Visitas',
        'alertas' => 'Alertas', 'historial' => 'Historial',
    ];

    public function show(Request $request, Residente $residente)
    {
        $persona = $residente;
        $this->authorize('view', $persona);
        $tab = $request->validate(['tab' => ['sometimes', 'in:'.implode(',', array_keys(self::TABS))]])['tab'] ?? 'resumen';

        $ocupacion = DB::table('ocupaciones_cama as oc')
            ->join('camas as c', 'c.cod_cama', '=', 'oc.cod_cama')
            ->join('habitaciones as h', 'h.cod_habitacion', '=', 'c.cod_habitacion')
            ->where('oc.cod_residente', $persona->cod_residente)
            ->whereNull('oc.fecha_hora_liberacion')
            ->where('oc.estado', 'ACTIVA')
            ->select('c.codigo as cama', 'h.codigo as habitacion', 'oc.fecha_hora_asignacion')->first();
        $responsable = DB::table('residentes_contactos as rc')
            ->join('contactos as co', 'co.cod_contacto', '=', 'rc.cod_contacto')
            ->where('rc.cod_residente', $persona->cod_residente)
            ->where('rc.estado', 'ACTIVO')
            ->where('rc.responsable_principal', true)
            ->select('co.nombres', 'co.apellido_paterno', 'co.telefono', 'co.celular', 'rc.parentesco')->first();

        $codigo = $persona->cod_residente;
        $filas = match ($tab) {
            'admision' => DB::table('admisiones')->where('cod_residente', $codigo)
                ->orderByDesc('fecha_hora_admision')->get()
                ->map(fn ($r) => $this->fila('Admisión '.$r->cod_admision, $r->tipo_ingreso, $r->fecha_hora_admision, $r->estado)),
            'ubicacion' => DB::table('ocupaciones_cama as oc')
                ->join('camas as c', 'c.cod_cama', '=', 'oc.cod_cama')
                ->join('habitaciones as h', 'h.cod_habitacion', '=', 'c.cod_habitacion')
                ->where('oc.cod_residente', $codigo)->orderByDesc('oc.fecha_hora_asignacion')
                ->get(['c.codigo as cama', 'h.codigo as habitacion', 'oc.fecha_hora_asignacion', 'oc.fecha_hora_liberacion', 'oc.estado'])
                ->map(fn ($r) => $this->fila('Habitación '.$r->habitacion.' · Cama '.$r->cama,
                    $r->fecha_hora_liberacion ? 'Liberada '.$r->fecha_hora_liberacion : 'Ocupación vigente',
                    $r->fecha_hora_asignacion, $r->estado)),
            'contactos' => DB::table('residentes_contactos as rc')->join('contactos as c', 'c.cod_contacto', '=', 'rc.cod_contacto')
                ->where('rc.cod_residente', $codigo)->orderByDesc('rc.responsable_principal')
                ->get(['c.nombres', 'c.apellido_paterno', 'c.celular', 'rc.parentesco', 'rc.responsable_principal', 'rc.estado'])
                ->map(fn ($r) => $this->fila($r->nombres.' '.$r->apellido_paterno,
                    $r->parentesco.($r->responsable_principal ? ' · Responsable principal' : '').($r->celular ? ' · '.$r->celular : ''), null, $r->estado)),
            'documentacion' => DB::table('documentos')->where('cod_residente', $codigo)
                ->orderBy('nombre')->get(['nombre', 'tipo_documento', 'fecha_vencimiento', 'estado'])
                ->map(fn ($r) => $this->fila($r->nombre, $r->tipo_documento, $r->fecha_vencimiento, $r->estado)),
            'consentimientos' => DB::table('consentimientos')->where('cod_residente', $codigo)
                ->orderByDesc('fecha_consentimiento')->get(['tipo_consentimiento', 'firma_residente', 'fecha_consentimiento', 'estado'])
                ->map(fn ($r) => $this->fila($r->tipo_consentimiento,
                    $r->firma_residente ? 'Firmante: residente' : 'Firmante: contacto responsable', $r->fecha_consentimiento, $r->estado)),
            'seguro' => DB::table('seguros_residente')->where('cod_residente', $codigo)
                ->orderBy('entidad')->get(['entidad', 'plan', 'numero_afiliacion', 'estado'])
                ->map(fn ($r) => $this->fila($r->entidad, trim(($r->plan ?: 'Sin plan').' · '.($r->numero_afiliacion ?: 'Sin número')), null, $r->estado)),
            'actividades' => DB::table('participantes_actividad as pa')->join('actividades as a', 'a.cod_actividad', '=', 'pa.cod_actividad')
                ->where('pa.cod_residente', $codigo)->orderByDesc('a.fecha_hora')
                ->get(['a.nombre', 'a.lugar', 'a.fecha_hora', 'a.estado'])
                ->map(fn ($r) => $this->fila($r->nombre, $r->lugar, $r->fecha_hora, $r->estado)),
            'visitas' => DB::table('visitas as v')->join('contactos as c', 'c.cod_contacto', '=', 'v.cod_contacto')
                ->where('v.cod_residente', $codigo)->orderByDesc('v.cod_visita')
                ->get(['c.nombres', 'c.apellido_paterno', 'v.fecha_hora_programada', 'v.fecha_hora_ingreso', 'v.estado'])
                ->map(fn ($r) => $this->fila($r->nombres.' '.$r->apellido_paterno, 'Visita',
                    $r->fecha_hora_ingreso ?: $r->fecha_hora_programada, $r->estado)),
            'alertas' => DB::table('alertas')->where('cod_residente', $codigo)->orderByDesc('fecha_hora')
                ->get(['titulo', 'prioridad', 'fecha_hora', 'estado'])
                ->map(fn ($r) => $this->fila($r->titulo, 'Prioridad '.$r->prioridad, $r->fecha_hora, $r->estado)),
            'historial' => DB::table('historial_estados_residente')->where('cod_residente', $codigo)
                ->orderByDesc('fecha_hora')->get(['estado_nuevo', 'motivo', 'fecha_hora'])
                ->map(fn ($r) => $this->fila('Estado '.$r->estado_nuevo, $r->motivo, $r->fecha_hora, $r->estado_nuevo)),
            default => collect(),
        };

        return view('pages.admin.administracion.residente', [
            'persona' => $persona,
            'ocupacion' => $ocupacion,
            'responsable' => $responsable,
            'tabs' => self::TABS,
            'tab' => $tab,
            'filas' => $filas,
        ]);
    }

    private function fila(string $titulo, ?string $detalle, ?string $fecha, ?string $estado): array
    {
        return compact('titulo', 'detalle', 'fecha', 'estado');
    }
}
