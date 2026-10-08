<?php

namespace App\Backend\Modulos\Administracion\Servicios;

use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Models\Residente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PanelResidenteService
{
    public function datos(Residente $residente, string $tab): array
    {
        $codigo = $residente->cod_residente;

        $habitacion = DB::table('ocupaciones_cama as oc')
            ->join('camas as c', 'c.cod_cama', '=', 'oc.cod_cama')
            ->join('habitaciones as h', 'h.cod_habitacion', '=', 'c.cod_habitacion')
            ->where('oc.cod_residente', $codigo)
            ->whereIn('oc.estado', FormalizarAdmision::ESTADOS_OCUPACION_ACTIVA)
            ->orderByDesc('oc.fecha_hora_asignacion')
            ->orderByDesc('oc.cod_ocupacion')
            ->first(['h.codigo', 'h.nombre', 'h.piso', 'h.tipo', 'c.codigo as cama', 'c.cod_cama']);

        $responsable = DB::table('residentes_contactos as rc')
            ->join('contactos as co', 'co.cod_contacto', '=', 'rc.cod_contacto')
            ->where('rc.cod_residente', $codigo)
            ->where('rc.estado', 'ACTIVO')
            ->where('rc.responsable_principal', true)
            ->orderBy('rc.cod_residente_contacto')
            ->first(['co.nombres', 'co.apellido_paterno', 'co.apellido_materno', 'co.celular', 'co.telefono', 'co.correo', 'rc.parentesco']);

        $asignacion = DB::table('asignaciones_residente_jornada as arj')
            ->join('jornadas as j', 'j.cod_jornada', '=', 'arj.cod_jornada')
            ->where('arj.cod_residente', $codigo)
            ->whereDate('j.fecha_jornada', today())
            ->whereIn('j.estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO'])
            ->whereIn('arj.estado', ['ACTIVA', 'ACTIVO', 'ASIGNADO'])
            ->whereNotNull('arj.nivel_supervision')
            ->where('arj.nivel_supervision', '<>', '')
            ->orderByDesc('arj.fecha_hora')
            ->first(['arj.nivel_supervision', 'arj.fecha_hora']);

        $observacion = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $residente->observacion)) ?? '');

        return [
            'habitacion' => $habitacion,
            'responsable' => $responsable,
            'supervision' => $asignacion,
            'observacion_breve' => $observacion !== '' ? Str::limit($observacion, 145) : null,
            'alertas_activas' => $tab === 'salud'
                ? DB::table('alertas')->where('cod_residente', $codigo)
                    ->whereNotIn('estado', ['CERRADA', 'ANULADA'])->count()
                : null,
            'documentos' => $tab === 'documentos'
                ? DB::table('documentos')->where('cod_residente', $codigo)
                    ->orderBy('nombre')->get(['nombre', 'tipo_documento', 'fecha_vencimiento', 'estado'])
                : collect(),
            'historial' => $tab === 'historial'
                ? DB::table('historial_estados_residente')->where('cod_residente', $codigo)
                    ->orderByDesc('fecha_hora')->get(['estado_nuevo', 'motivo', 'fecha_hora'])
                : collect(),
            'historial_alojamiento' => $tab === 'historial'
                ? DB::table('ocupaciones_cama as oc')->join('camas as c', 'c.cod_cama', '=', 'oc.cod_cama')
                    ->join('habitaciones as h', 'h.cod_habitacion', '=', 'c.cod_habitacion')
                    ->where('oc.cod_residente', $codigo)->orderByDesc('oc.fecha_hora_asignacion')
                    ->orderByDesc('oc.cod_ocupacion')->limit(30)
                    ->get(['h.codigo as habitacion', 'h.piso', 'c.codigo as cama', 'oc.estado', 'oc.fecha_hora_asignacion', 'oc.fecha_hora_liberacion', 'oc.motivo_liberacion'])
                : collect(),
        ];
    }
}
