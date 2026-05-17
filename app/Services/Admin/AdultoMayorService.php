<?php

namespace App\Services\Admin;

use App\Models\AdultoMayor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class AdultoMayorService
{
    /**
     * Obtener listado completo de adultos mayores con indicadores.
     */
    public function obtenerListado(array $filtros = [])
    {
        $query = AdultoMayor::with(['estado'])
            ->withCount([
                'familiares as fam_total',
                'observaciones as obs_total',
                'actividades as act_total',
                'atenciones as aten_total'
            ]);

        if (!empty($filtros['buscar'])) {
            $buscar = $filtros['buscar'];
            $query->where(function ($q) use ($buscar) {
                $q->where('cod_am', 'like', "%{$buscar}%")
                  ->orWhere('nombres', 'ilike', "%{$buscar}%")
                  ->orWhere('ap_paterno', 'ilike', "%{$buscar}%")
                  ->orWhere('ap_materno', 'ilike', "%{$buscar}%")
                  ->orWhere('ci', 'like', "%{$buscar}%");
            });
        }

        if (!empty($filtros['estado'])) {
            $query->whereHas('estado', function ($q) use ($filtros) {
                $q->where('estado', $filtros['estado']);
            });
        }

        if (!empty($filtros['genero'])) {
            $query->where('genero', $filtros['genero']);
        }

        if (!empty($filtros['permanencia'])) {
            $query->where('permanencia', $filtros['permanencia']);
        }

        if (!empty($filtros['nivel_educat'])) {
            $query->where('nivel_educat', $filtros['nivel_educat']);
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->where('fecha_ing', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->where('fecha_ing', '<=', $filtros['fecha_hasta']);
        }

        $paginated = $query->paginate(12)->withQueryString();
        
        $paginated->getCollection()->transform(function ($adulto) {
            $adulto->edad = $this->calcularEdad($adulto->fecha_nac);
            $adulto->estado_adulto = $adulto->estado->estado ?? 'ACTIVO';
            return $adulto;
        });

        return $paginated;
    }

    /**
     * Obtener detalle completo de un adulto mayor.
     */
    public function obtenerDetalle($codAm)
    {
        return AdultoMayor::with([
            'estado',
            'familiares',
            'observaciones',
            'actividades',
            'atenciones',
            'documentos',
            'voluntarios'
        ])->findOrFail($codAm);
    }

    /**
     * Obtener estados disponibles.
     */
    public function obtenerEstados()
    {
        if (Schema::hasTable('estado_adulto')) {
            return DB::table('estado_adulto')
                ->select('cod_est_adul', 'estado')
                ->orderBy('estado')
                ->get();
        }
        return collect();
    }

    /**
     * Obtener tipos de atención disponibles ordenados alfabéticamente.
     */
    public function obtenerTiposAtenciones()
    {
        if (Schema::hasTable('tipo_atenciones_adulto')) {
            return \App\Models\TipoAtencionAdulto::orderBy('tipo')->get();
        }
        return collect();
    }

    /**
     * Calcular edad desde fecha de nacimiento.
     */
    public function calcularEdad($fechaNac)
    {
        if (!$fechaNac) return '-';
        return Carbon::parse($fechaNac)->age;
    }

    /**
     * Guardar foto del adulto mayor.
     */
    public function guardarFoto($file)
    {
        if ($file) {
            $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            return $file->storeAs('adultos-mayores', $filename, 'public');
        }
        return null;
    }

    /**
     * Crear un nuevo adulto mayor.
     */
    public function crearAdultoMayor(array $data, $foto = null)
    {
        if ($foto) {
            $data['foto'] = $this->guardarFoto($foto);
        }
        
        return AdultoMayor::create($data);
    }

    /**
     * Actualizar información de un adulto mayor.
     */
    public function actualizarAdultoMayor($codAm, array $data, $foto = null)
    {
        $adulto = AdultoMayor::findOrFail($codAm);
        
        if ($foto) {
            // Eliminar foto anterior si existe
            if ($adulto->foto) {
                Storage::disk('public')->delete($adulto->foto);
            }
            $data['foto'] = $this->guardarFoto($foto);
        } else {
            // Asegurar que no se sobreescriba a null si no se envió foto
            unset($data['foto']);
        }
        
        $adulto->update($data);
        return $adulto;
    }

    /**
     * Archivar un registro (desactivar).
     */
    public function archivar($codAm, $motivo = null)
    {
        $adulto = AdultoMayor::findOrFail($codAm);

        // Buscar el estado ARCHIVADO dinámicamente por nombre
        $estadoArchivado = DB::table('estado_adulto')
            ->whereRaw('UPPER(estado) = ?', ['ARCHIVADO'])
            ->first();

        if (!$estadoArchivado) {
            throw new \RuntimeException('No existe un estado ARCHIVADO en la tabla estado_adulto. Por favor ejecute el seeder correspondiente.');
        }

        $adulto->cod_est_adul   = $estadoArchivado->cod_est_adul;
        $adulto->archivado_en   = now();
        $adulto->motivo_archivado = $motivo ?? 'Archivado administrativamente';
        $adulto->save();

        return $adulto;
    }

    /**
     * Restaurar un registro.
     */
    public function restaurar($codAm)
    {
        $adulto = AdultoMayor::findOrFail($codAm);

        // Buscar el estado ACTIVO dinámicamente por nombre
        $estadoActivo = DB::table('estado_adulto')
            ->whereRaw('UPPER(estado) = ?', ['ACTIVO'])
            ->first();

        if (!$estadoActivo) {
            throw new \RuntimeException('No existe un estado ACTIVO en la tabla estado_adulto. Por favor ejecute el seeder correspondiente.');
        }

        $adulto->cod_est_adul    = $estadoActivo->cod_est_adul;
        $adulto->archivado_en    = null;
        $adulto->motivo_archivado = null;
        $adulto->save();

        return $adulto;
    }
}
