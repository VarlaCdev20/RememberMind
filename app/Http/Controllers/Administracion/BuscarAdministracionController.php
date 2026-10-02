<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuscarAdministracionController extends Controller
{
    public function __invoke(Request $request)
    {
        $busqueda = trim((string) ($request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ])['q'] ?? ''));
        $resultados = [];
        if (mb_strlen($busqueda) >= 2) {
            $termino = '%'.$busqueda.'%';
            $grupos = [
                ['Residentes', 'residentes.ver', 'residentes', 'cod_residente', 'nombres', 'apellido_paterno', 'admin.administracion.residentes'],
                ['Preadmisiones', 'preadmisiones.ver', 'preadmisiones', 'cod_preadmision', 'nombres', 'apellido_paterno', 'admin.admisiones.preadmisiones'],
                ['Contactos', 'contactos.ver', 'contactos', 'cod_contacto', 'nombres', 'apellido_paterno', 'admin.administracion.contactos'],
                ['Documentación', 'documentos.ver', 'documentos', 'cod_documento', 'nombre', 'tipo_documento', 'admin.administracion.documentacion'],
                ['Habitaciones', 'habitaciones.ver', 'habitaciones', 'cod_habitacion', 'codigo', 'nombre', 'admin.administracion.habitaciones'],
                ['Camas', 'camas.ver', 'camas', 'cod_cama', 'codigo', 'tipo', 'admin.administracion.habitaciones'],
            ];
            foreach ($grupos as [$titulo, $permiso, $tabla, $codigo, $nombre, $detalle, $ruta]) {
                if (! $request->user()?->can($permiso)) {
                    continue;
                }
                $query = DB::table($tabla);
                if ($tabla === 'documentos') {
                    $query->where(fn ($where) => $where->whereNotNull('cod_residente')
                        ->orWhereNotNull('cod_preadmision')->orWhereNotNull('cod_contacto'));
                }
                $filas = $query->where(function ($query) use ($codigo, $nombre, $detalle, $termino) {
                    $query->where($codigo, 'like', $termino)->orWhere($nombre, 'like', $termino)
                        ->orWhere($detalle, 'like', $termino);
                })->limit(6)->get([$codigo, $nombre, $detalle]);
                if ($filas->isNotEmpty()) {
                    $resultados[$titulo] = $filas->map(fn ($fila) => [
                        'codigo' => $fila->{$codigo},
                        'nombre' => $fila->{$nombre},
                        'detalle' => $fila->{$detalle},
                        'url' => $tabla === 'residentes'
                            ? route('admin.administracion.residentes.show', $fila->{$codigo})
                            : route($ruta, ['search' => $fila->{$codigo}]),
                    ]);
                }
            }
            if ($request->user()?->can('admisiones.ver')) {
                $admisiones = DB::table('admisiones as a')
                    ->join('residentes as r', 'r.cod_residente', '=', 'a.cod_residente')
                    ->where(fn ($query) => $query->where('a.cod_admision', 'like', $termino)
                        ->orWhere('r.nombres', 'like', $termino)
                        ->orWhere('r.apellido_paterno', 'like', $termino))
                    ->limit(6)->get(['a.cod_admision', 'r.nombres', 'r.apellido_paterno']);
                if ($admisiones->isNotEmpty()) {
                    $resultados['Admisiones'] = $admisiones->map(fn ($fila) => [
                        'codigo' => $fila->cod_admision,
                        'nombre' => $fila->nombres.' '.$fila->apellido_paterno,
                        'detalle' => 'Admisión formalizada',
                        'url' => route('admin.administracion.admisiones', [
                            'tab' => 'admitidos', 'search' => $fila->cod_admision,
                        ]),
                    ]);
                }
            }
        }

        return view('pages.admin.administracion.buscar', compact('busqueda', 'resultados'));
    }
}
