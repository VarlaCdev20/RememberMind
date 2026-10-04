<?php

namespace App\Backend\Modulos\Residentes\Servicios;

use App\Models\Contacto;
use App\Models\HistorialEstadoResidente;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\SeguroResidente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdultoMayorService
{
    /**
     * Obtener listado completo de residentes con indicadores V2.
     */
    public function obtenerListado(array $filtros = [])
    {
        $query = Residente::with(['contactos', 'familiares.usuario', 'atenciones', 'evaluacionesGeriatricas', 'ocupacionActiva.cama.habitacion'])
            ->withCount([
                'contactos as fam_total',
                'observaciones as obs_total',
                'actividades as act_total',
                'atenciones as aten_total',
            ]);

        if (! empty($filtros['buscar'])) {
            $buscar = trim($filtros['buscar']);
            $query->where(function ($q) use ($buscar) {
                $q->where('cod_residente', 'like', "%{$buscar}%")
                    ->orWhere('nombres', 'ilike', "%{$buscar}%")
                    ->orWhere('apellido_paterno', 'ilike', "%{$buscar}%")
                    ->orWhere('apellido_materno', 'ilike', "%{$buscar}%")
                    ->orWhere('numero_documento', 'like', "%{$buscar}%");
            });
        }

        if (! empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (! empty($filtros['genero'])) {
            $query->where('genero', $filtros['genero']);
        }

        if (! empty($filtros['nivel_educat'])) {
            $query->where('nivel_educativo', $filtros['nivel_educat']);
        }

        if (! empty($filtros['ciudad_municipio'])) {
            $query->where('direccion', 'ilike', "%{$filtros['ciudad_municipio']}%");
        }

        if (! empty($filtros['rango_edad'])) {
            $rango = $filtros['rango_edad'];
            if ($rango === '60-70') {
                $query->whereBetween('fecha_nacimiento', [now()->subYears(70)->format('Y-m-d'), now()->subYears(60)->format('Y-m-d')]);
            } elseif ($rango === '70-80') {
                $query->whereBetween('fecha_nacimiento', [now()->subYears(80)->format('Y-m-d'), now()->subYears(70)->format('Y-m-d')]);
            } elseif ($rango === '80+') {
                $query->where('fecha_nacimiento', '<', now()->subYears(80)->format('Y-m-d'));
            }
        }

        if (! empty($filtros['fecha_desde'])) {
            $query->whereHas('admisiones', fn ($q) => $q->whereDate('fecha_hora_admision', '>=', $filtros['fecha_desde']));
        }

        if (! empty($filtros['fecha_hasta'])) {
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
            'documentos',
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
        if (! $fechaNac) {
            return '-';
        }

        return Carbon::parse($fechaNac)->age;
    }

    /**
     * Guardar foto del residente.
     */
    public function guardarFoto($file)
    {
        if ($file) {
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

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

        // Traducción explícita del formulario a la entidad residentes V2.
        // Los datos relacionados se persisten después en sus tablas normalizadas.
        $mapeo = [
            'ap_paterno' => 'apellido_paterno',
            'ap_materno' => 'apellido_materno',
            'ci' => 'numero_documento',
            'complemento_ci' => 'complemento_documento',
            'expedicion_ci' => 'expedicion_documento',
            'fecha_nac' => 'fecha_nacimiento',
            'nivel_educat' => 'nivel_educativo',
            'foto' => 'foto',
            'observaciones' => 'observacion',
            'cod_est_adul' => 'estado',
        ];

        $columnasResidente = [
            'nombres', 'apellido_paterno', 'apellido_materno', 'numero_documento',
            'complemento_documento', 'expedicion_documento', 'estado_civil',
            'fecha_nacimiento', 'genero', 'grupo_sanguineo', 'factor_rh',
            'nivel_educativo', 'telefono', 'celular', 'direccion', 'foto',
            'estado', 'observacion',
        ];

        $v2Data = [];
        foreach ($data as $key => $val) {
            $targetCol = $mapeo[$key] ?? $key;
            if (in_array($targetCol, $columnasResidente, true)) {
                $v2Data[$targetCol] = $val;
            }
        }

        if (array_key_exists('grupo_sanguineo', $data)) {
            preg_match('/^(A|B|AB|O)([+-])$/', (string) $data['grupo_sanguineo'], $grupo);
            if (! $grupo) {
                throw ValidationException::withMessages([
                    'grupo_sanguineo' => 'El grupo sanguíneo debe incluir grupo y factor Rh válidos.',
                ]);
            }
            $v2Data['grupo_sanguineo'] = $grupo[1];
            $v2Data['factor_rh'] = $grupo[2];
        }

        if (array_key_exists('celular', $data)) {
            $v2Data['celular'] = $data['celular'];
        }
        if (array_key_exists('telefono_fijo', $data)) {
            $v2Data['telefono'] = $data['telefono_fijo'];
        }

        if (isset($data['ciudad_municipio']) || isset($data['calle']) || isset($data['zona'])) {
            $partes = array_filter([$data['calle'] ?? null, $data['zona'] ?? null, $data['ciudad_municipio'] ?? null]);
            if ($partes) {
                $v2Data['direccion'] = implode(', ', $partes);
            }
        }

        return DB::transaction(function () use ($residente, $v2Data, $data): Residente {
            $residente->update($v2Data);

            if (array_key_exists('seguro_salud', $data)) {
                $this->sincronizarSeguro($residente, $data['seguro_salud']);
            }

            if (array_key_exists('contacto_emergencia_nombre', $data)) {
                $this->sincronizarContactoEmergencia($residente, $data);
            }

            return $residente->refresh();
        });
    }

    private function sincronizarSeguro(Residente $residente, ?string $entidad): void
    {
        $activos = SeguroResidente::query()
            ->where('cod_residente', $residente->cod_residente)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->get();

        if ($activos->count() > 1) {
            throw ValidationException::withMessages([
                'seguro_salud' => 'El residente tiene más de un seguro activo.',
            ]);
        }

        if (! $entidad || $entidad === 'NINGUNO') {
            SeguroResidente::query()
                ->where('cod_residente', $residente->cod_residente)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                ->update(['estado' => 'INACTIVO']);

            return;
        }

        $seguro = $activos->first();
        if ($seguro) {
            $seguro->update(['entidad' => $entidad, 'estado' => 'ACTIVO']);

            return;
        }

        SeguroResidente::create([
            'cod_seguro' => 'SEG_'.Str::upper(Str::random(10)),
            'cod_residente' => $residente->cod_residente,
            'entidad' => $entidad,
            'estado' => 'ACTIVO',
        ]);
    }

    private function sincronizarContactoEmergencia(Residente $residente, array $data): void
    {
        $vinculosEmergencia = ResidenteContacto::query()
            ->with('contacto')
            ->where('cod_residente', $residente->cod_residente)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->where('contacto_emergencia', true)
            ->get();

        if ($vinculosEmergencia->count() > 1) {
            throw ValidationException::withMessages([
                'contacto_emergencia_nombre' => 'El residente tiene más de un contacto de emergencia activo.',
            ]);
        }

        $partesNombre = preg_split('/\s+/u', trim((string) $data['contacto_emergencia_nombre'])) ?: [];
        if (count($partesNombre) < 2) {
            throw ValidationException::withMessages([
                'contacto_emergencia_nombre' => 'Registre al menos un nombre y un apellido del contacto.',
            ]);
        }
        $apellidoPaterno = array_pop($partesNombre);
        $nombres = implode(' ', $partesNombre);

        $vinculo = $vinculosEmergencia->first();
        $contacto = $vinculo?->contacto;
        if (! $contacto) {
            $contacto = Contacto::create([
                'cod_contacto' => 'CON_'.Str::upper(Str::random(10)),
                'nombres' => $nombres,
                'apellido_paterno' => $apellidoPaterno,
                'celular' => $data['contacto_emergencia_celular'],
                'direccion' => $data['contacto_emergencia_direccion'] ?: null,
                'estado' => 'ACTIVO',
            ]);

            $vinculo = ResidenteContacto::create([
                'cod_residente_contacto' => 'RC_'.Str::upper(Str::random(10)),
                'cod_residente' => $residente->cod_residente,
                'cod_contacto' => $contacto->cod_contacto,
                'parentesco' => $data['contacto_emergencia_parentesco'],
                'responsable_principal' => (bool) ($data['responsable_principal'] ?? false),
                'contacto_emergencia' => true,
                'autoriza_informacion' => (bool) ($data['autorizado_informacion_medica'] ?? false),
                'autoriza_salida' => false,
                'estado' => 'ACTIVO',
            ]);
        } else {
            $contacto->update([
                'nombres' => $nombres,
                'apellido_paterno' => $apellidoPaterno,
                'celular' => $data['contacto_emergencia_celular'],
                'direccion' => $data['contacto_emergencia_direccion'] ?: null,
            ]);
            $vinculo->update([
                'parentesco' => $data['contacto_emergencia_parentesco'],
                'responsable_principal' => (bool) ($data['responsable_principal'] ?? false),
                'contacto_emergencia' => true,
                'autoriza_informacion' => (bool) ($data['autorizado_informacion_medica'] ?? false),
            ]);
        }

        ResidenteContacto::query()
            ->where('cod_residente', $residente->cod_residente)
            ->where('cod_residente_contacto', '!=', $vinculo->cod_residente_contacto)
            ->update([
                'contacto_emergencia' => false,
                ...((bool) ($data['responsable_principal'] ?? false) ? ['responsable_principal' => false] : []),
            ]);
    }

    /**
     * Archivar un registro (desactivar).
     */
    public function archivar(string $codResidente, string $codUsuarioRegistro, ?string $motivo = null): Residente
    {
        return DB::transaction(function () use ($codResidente, $codUsuarioRegistro, $motivo) {
            $residente = Residente::findOrFail($codResidente);
            $anterior = $residente->estado;
            $motivoTexto = $motivo ?? 'Archivado administrativamente';

            $residente->estado = 'INACTIVO';
            $residente->observacion = $motivoTexto;
            $residente->save();

            HistorialEstadoResidente::create([
                'cod_historial_estado' => 'HER_'.strtoupper(Str::random(10)),
                'cod_residente' => $residente->cod_residente,
                'cod_usuario_registro' => $codUsuarioRegistro,
                'estado_anterior' => $anterior,
                'estado_nuevo' => 'INACTIVO',
                'fecha_hora' => now(),
                'motivo' => $motivoTexto,
            ]);

            return $residente;
        });
    }

    /**
     * Restaurar un registro.
     */
    public function restaurar(string $codResidente, string $codUsuarioRegistro): Residente
    {
        return DB::transaction(function () use ($codResidente, $codUsuarioRegistro) {
            $residente = Residente::findOrFail($codResidente);
            $anterior = $residente->estado;

            $residente->estado = 'ACTIVO';
            $residente->save();

            HistorialEstadoResidente::create([
                'cod_historial_estado' => 'HER_'.strtoupper(Str::random(10)),
                'cod_residente' => $residente->cod_residente,
                'cod_usuario_registro' => $codUsuarioRegistro,
                'estado_anterior' => $anterior,
                'estado_nuevo' => 'ACTIVO',
                'fecha_hora' => now(),
                'motivo' => 'Restaurado administrativamente',
            ]);

            return $residente;
        });
    }
}
