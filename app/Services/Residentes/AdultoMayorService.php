<?php

namespace App\Services\Residentes;

use App\Models\Residente;
use App\Models\HistorialEstadoResidente;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdultoMayorService
{
    /**
     * Obtener listado completo de residentes con indicadores V2.
     */
    public function obtenerListado(array $filtros = [])
    {
        $query = Residente::with(['contactos', 'atenciones', 'evaluacionesGeriatricas'])
            ->withCount([
                'contactos as fam_total',
                'observaciones as obs_total',
                'actividades as act_total',
                'atenciones as aten_total'
            ]);

        if (!empty($filtros['buscar'])) {
            $buscar = trim($filtros['buscar']);
            $query->where(function ($q) use ($buscar) {
                $q->where('cod_residente', 'like', "%{$buscar}%")
                  ->orWhere('nombres', 'ilike', "%{$buscar}%")
                  ->orWhere('apellido_paterno', 'ilike', "%{$buscar}%")
                  ->orWhere('apellido_materno', 'ilike', "%{$buscar}%")
                  ->orWhere('numero_documento', 'like', "%{$buscar}%");
            });
        }

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['genero'])) {
            $query->where('genero', $filtros['genero']);
        }

        if (!empty($filtros['nivel_educat'])) {
            $query->where('nivel_educativo', $filtros['nivel_educat']);
        }

        if (!empty($filtros['ciudad_municipio'])) {
            $query->where('direccion', 'ilike', "%{$filtros['ciudad_municipio']}%");
        }

        if (!empty($filtros['rango_edad'])) {
            $rango = $filtros['rango_edad'];
            if ($rango === '60-70') {
                $query->whereBetween('fecha_nacimiento', [now()->subYears(70)->format('Y-m-d'), now()->subYears(60)->format('Y-m-d')]);
            } elseif ($rango === '70-80') {
                $query->whereBetween('fecha_nacimiento', [now()->subYears(80)->format('Y-m-d'), now()->subYears(70)->format('Y-m-d')]);
            } elseif ($rango === '80+') {
                $query->where('fecha_nacimiento', '<', now()->subYears(80)->format('Y-m-d'));
            }
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->whereHas('admisiones', fn ($q) => $q->whereDate('fecha_hora_admision', '>=', $filtros['fecha_desde']));
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->whereHas('admisiones', fn ($q) => $q->whereDate('fecha_hora_admision', '<=', $filtros['fecha_hasta']));
        }

        $paginated = $query->paginate(12)->withQueryString();
        
        $paginated->getCollection()->transform(function ($adulto) {
            $adulto->edad = $this->calcularEdad($adulto->fecha_nacimiento);
            $adulto->estado_adulto = $adulto->estado;
            return $adulto;
        });

        return $paginated;
    }

    /**
     * Obtener detalle completo de un residente.
     */
    public function obtenerDetalle($codResidente)
    {
        return Residente::with([
            'contactos',
            'observaciones',
            'actividades',
            'atenciones',
            'documentos'
        ])->findOrFail($codResidente);
    }

    /**
     * Obtener estados disponibles.
     */
    public function obtenerEstados()
    {
        return Residente::query()
            ->select('estado')
            ->distinct()
            ->orderBy('estado')
            ->get()
            ->map(fn ($item) => (object) ['cod_est_adul' => $item->estado, 'estado' => $item->estado]);
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
     * Guardar foto del residente.
     */
    public function guardarFoto($file)
    {
        if ($file) {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            return $file->storeAs('residentes', $filename, 'public');
        }
        return null;
    }

    /**
     * Actualizar información de un residente.
     */
    public function actualizarAdultoMayor($codResidente, array $data, $foto = null)
    {
        $residente = Residente::findOrFail($codResidente);
        
        if ($foto) {
            if ($residente->foto) {
                Storage::disk('public')->delete($residente->foto);
            }
            $data['foto'] = $this->guardarFoto($foto);
        }

        // El formulario conserva nombres visuales históricos, pero la escritura
        // se reduce explícitamente a columnas físicas de residentes V2.
        $mapeo = [
            'ap_paterno' => 'apellido_paterno',
            'ap_materno' => 'apellido_materno',
            'ci' => 'numero_documento',
            'expedicion_ci' => 'expedicion_documento',
            'fecha_nac' => 'fecha_nacimiento',
            'nivel_educat' => 'nivel_educativo',
            'foto' => 'foto',
            'observaciones' => 'observacion',
            'fecha_ing' => 'fecha_ingreso',
        ];

        $v2Data = [];
        foreach ($data as $key => $val) {
            $targetCol = $mapeo[$key] ?? $key;
            if (\Illuminate\Support\Facades\Schema::hasColumn('residentes', $targetCol)) {
                $v2Data[$targetCol] = $val;
            }
        }

        if (isset($data['celular']) || isset($data['telefono_fijo'])) {
            $v2Data['telefono'] = $data['celular'] ?? $data['telefono_fijo'] ?? $residente->telefono;
        }

        if (isset($data['ciudad_municipio']) || isset($data['calle']) || isset($data['zona'])) {
            $partes = array_filter([$data['calle'] ?? null, $data['zona'] ?? null, $data['ciudad_municipio'] ?? null]);
            if ($partes) {
                $v2Data['direccion'] = implode(', ', $partes);
            }
        }

        $residente->update($v2Data);
        return $residente;
    }

    /**
     * Archivar un registro (desactivar).
     */
    public function archivar($codResidente, $motivo = null)
    {
        $residente = Residente::findOrFail($codResidente);
        $anterior = $residente->estado;
        $motivoTexto = $motivo ?? 'Archivado administrativamente';

        $residente->estado = 'INACTIVO';
        $residente->observacion = $motivoTexto;
        $residente->save();

        HistorialEstadoResidente::create([
            'cod_historial_estado' => 'HER_' . strtoupper(Str::random(10)),
            'cod_residente' => $residente->cod_residente,
            'cod_usuario_registro' => auth()->id() ?? User::value('cod_usuario'),
            'estado_anterior' => $anterior,
            'estado_nuevo' => 'INACTIVO',
            'fecha_hora' => now(),
            'motivo' => $motivoTexto,
        ]);

        return $residente;
    }

    /**
     * Restaurar un registro.
     */
    public function restaurar($codResidente)
    {
        $residente = Residente::findOrFail($codResidente);
        $anterior = $residente->estado;

        $residente->estado = 'ACTIVO';
        $residente->save();

        HistorialEstadoResidente::create([
            'cod_historial_estado' => 'HER_' . strtoupper(Str::random(10)),
            'cod_residente' => $residente->cod_residente,
            'cod_usuario_registro' => auth()->id() ?? User::value('cod_usuario'),
            'estado_anterior' => $anterior,
            'estado_nuevo' => 'ACTIVO',
            'fecha_hora' => now(),
            'motivo' => 'Restaurado administrativamente',
        ]);

        return $residente;
    }
}
