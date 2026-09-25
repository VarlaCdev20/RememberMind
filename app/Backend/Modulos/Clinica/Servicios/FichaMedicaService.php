<?php

namespace App\Backend\Modulos\Clinica\Servicios;

use App\Models\Alergia;
use App\Models\AntecedenteClinico;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\Diagnostico;
use App\Models\NotaClinica;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FichaMedicaService
{
    public const CONDICIONES_MAP = [
        'hipertension'            => 'Hipertensión',
        'diabetes'                => 'Diabetes',
        'problemas_cardiacos'     => 'Cardiopatía',
        'acv'                     => 'ACV / Ictus',
        'parkinson'               => 'Parkinson',
        'epilepsia'               => 'Epilepsia',
        'alzheimer_diagnosticado' => 'Alzheimer',
        'depresion'               => 'Depresión',
        'ansiedad'                => 'Ansiedad',
        'problemas_sueno'         => 'Trastorno del sueño',
        'problemas_visuales'      => 'Déficit visual',
        'problemas_auditivos'     => 'Déficit auditivo',
        'dolor_cronico'           => 'Dolor crónico',
    ];

    /**
     * Resuelve el código personal asociado a un usuario o el primer personal activo.
     */
    public function resolverPersonal(?string $codUsuario = null): string
    {
        $codUsuario = $codUsuario ?? auth()->user()?->cod_usuario ?? User::value('cod_usuario');
        $personal = Personal::where('cod_usuario', $codUsuario)->first();
        if ($personal) {
            return $personal->cod_personal;
        }

        $primerPersonal = Personal::first();
        return $primerPersonal?->cod_personal ?? 'PER_0001';
    }

    /**
     * Resuelve el área médica para atenciones.
     */
    public function resolverAreaMedica(): string
    {
        $area = Area::where('nombre', 'like', '%Med%')->first() ?? Area::first();
        return $area?->cod_area ?? 'ARE_0001';
    }

    /**
     * Construye la vista agregada de la Ficha Médica de un Residente.
     */
    public function obtenerFichaAgregada(string $codResidente): ?object
    {
        $residente = Residente::find($codResidente);
        if (!$residente) {
            return null;
        }

        $alergias = Alergia::where('cod_residente', $codResidente)
            ->where('estado', '!=', 'ANULADO')
            ->get();

        $antecedentes = AntecedenteClinico::where('cod_residente', $codResidente)
            ->where('estado', '!=', 'ANULADO')
            ->get();

        $diagnosticos = Diagnostico::where('cod_residente', $codResidente)
            ->where('estado', '!=', 'ANULADO')
            ->get();

        $atencion = Atencion::where('cod_residente', $codResidente)
            ->latest('fecha_hora')
            ->first();

        $nota = NotaClinica::where('cod_residente', $codResidente)
            ->latest('fecha_hora')
            ->first();

        // Si no hay ningún dato clínico, retorna null para indicar que requiere registro
        if ($alergias->isEmpty() && $antecedentes->isEmpty() && $diagnosticos->isEmpty() && !$atencion && !$nota) {
            return null;
        }

        // Construir banderas booleanas
        $condiciones = [];
        foreach (self::CONDICIONES_MAP as $campo => $nombreDiag) {
            $presente = $diagnosticos->contains(function ($d) use ($nombreDiag) {
                return stripos($d->nombre, $nombreDiag) !== false && $d->estado === 'ACTIVO';
            });
            $condiciones[$campo] = $presente;
        }

        // Textos agrupados
        $alergiasTexto = $alergias->pluck('sustancia')->filter()->implode(', ');
        $cirugias = $antecedentes->where('tipo_antecedente', 'QUIRURGICO')->pluck('descripcion')->filter()->implode("\n");
        $hospitalizaciones = $antecedentes->where('tipo_antecedente', 'HOSPITALIZACION')->pluck('descripcion')->filter()->implode("\n");
        $restricciones = $antecedentes->where('tipo_antecedente', 'NUTRICIONAL')->pluck('descripcion')->filter()->implode("\n");
        $obsMedica = $nota?->contenido ?? $atencion?->observacion ?? '';

        // Fechas y registrador
        $ultimaFecha = collect([
            $alergias->max('fecha_hora'),
            $antecedentes->max('fecha_referencia'),
            $diagnosticos->max('fecha_hora'),
            $atencion?->fecha_hora,
            $nota?->fecha_hora,
        ])->filter()->max();

        $updatedAt = $ultimaFecha ? Carbon::parse($ultimaFecha) : now();

        $personal = $atencion?->personal ?? $nota?->personal ?? Personal::where('cod_personal', $alergias->first()?->cod_personal)->first();
        $registradorNombre = $personal ? trim("{$personal->nombres} {$personal->apellido_paterno}") : 'Personal de Salud';

        $ficha = new \stdClass();
        $ficha->cod_residente = $codResidente;
        $ficha->estado = 'ACTIVA';
        $ficha->updated_at = $updatedAt;
        $ficha->created_at = $updatedAt;
        $ficha->registrador = (object) ['name' => $registradorNombre];

        foreach ($condiciones as $campo => $val) {
            $ficha->$campo = $val;
        }

        $ficha->alergias = $alergiasTexto;
        $ficha->cirugias = $cirugias;
        $ficha->hospitalizaciones = $hospitalizaciones;
        $ficha->restricciones_alimentarias = $restricciones;
        $ficha->observacion_medica = $obsMedica;

        return $ficha;
    }

    /**
     * Persiste la Ficha Médica a las entidades operativas V2.
     */
    public function guardarFicha(string $codResidente, array $datos, ?string $codUsuario = null): object
    {
        return DB::transaction(function () use ($codResidente, $datos, $codUsuario) {
            $codPersonal = $this->resolverPersonal($codUsuario);
            $codArea = $this->resolverAreaMedica();
            $ahora = now();

            // 1. Alergias
            if (!empty($datos['alergias'])) {
                $sustancias = array_map('trim', explode(',', $datos['alergias']));
                foreach ($sustancias as $sustancia) {
                    if ($sustancia === '') continue;
                    $existente = Alergia::where('cod_residente', $codResidente)
                        ->where('sustancia', $sustancia)
                        ->first();
                    if ($existente) {
                        $existente->update(['estado' => 'ACTIVO', 'fecha_hora' => $ahora]);
                    } else {
                        Alergia::create([
                            'cod_alergia'   => 'ALE_' . strtoupper(Str::random(10)),
                            'cod_residente' => $codResidente,
                            'cod_personal'  => $codPersonal,
                            'tipo'          => 'GENERAL',
                            'sustancia'     => $sustancia,
                            'reaccion'      => 'Reacción reportada en ficha médica',
                            'gravedad'      => 'MODERADA',
                            'fecha_hora'    => $ahora,
                            'estado'        => 'ACTIVO',
                        ]);
                    }
                }
            }

            // 2. Antecedentes Quirúrgicos
            if (isset($datos['cirugias']) && trim((string)$datos['cirugias']) !== '') {
                AntecedenteClinico::updateOrCreate(
                    ['cod_residente' => $codResidente, 'tipo_antecedente' => 'QUIRURGICO'],
                    [
                        'cod_antecedente'    => 'ANT_' . strtoupper(Str::random(10)),
                        'cod_personal'       => $codPersonal,
                        'descripcion'        => trim($datos['cirugias']),
                        'fecha_referencia'   => $ahora->toDateString(),
                        'fuente_informacion' => 'Ficha médica',
                        'estado'             => 'ACTIVO',
                    ]
                );
            }

            // 3. Antecedentes Hospitalizaciones
            if (isset($datos['hospitalizaciones']) && trim((string)$datos['hospitalizaciones']) !== '') {
                AntecedenteClinico::updateOrCreate(
                    ['cod_residente' => $codResidente, 'tipo_antecedente' => 'HOSPITALIZACION'],
                    [
                        'cod_antecedente'    => 'ANT_' . strtoupper(Str::random(10)),
                        'cod_personal'       => $codPersonal,
                        'descripcion'        => trim($datos['hospitalizaciones']),
                        'fecha_referencia'   => $ahora->toDateString(),
                        'fuente_informacion' => 'Ficha médica',
                        'estado'             => 'ACTIVO',
                    ]
                );
            }

            // 4. Restricciones Alimentarias (Nutricional)
            if (isset($datos['restricciones_alimentarias']) && trim((string)$datos['restricciones_alimentarias']) !== '') {
                AntecedenteClinico::updateOrCreate(
                    ['cod_residente' => $codResidente, 'tipo_antecedente' => 'NUTRICIONAL'],
                    [
                        'cod_antecedente'    => 'ANT_' . strtoupper(Str::random(10)),
                        'cod_personal'       => $codPersonal,
                        'descripcion'        => trim($datos['restricciones_alimentarias']),
                        'fecha_referencia'   => $ahora->toDateString(),
                        'fuente_informacion' => 'Ficha médica',
                        'estado'             => 'ACTIVO',
                    ]
                );
            }

            // 5. Diagnósticos
            foreach (self::CONDICIONES_MAP as $campo => $nombreDiag) {
                if (isset($datos[$campo]) && $datos[$campo]) {
                    $existente = Diagnostico::where('cod_residente', $codResidente)
                        ->where('nombre', $nombreDiag)
                        ->first();
                    if ($existente) {
                        $existente->update(['estado' => 'ACTIVO', 'fecha_hora' => $ahora]);
                    } else {
                        Diagnostico::create([
                            'cod_diagnostico' => 'DIA_' . strtoupper(Str::random(10)),
                            'cod_residente'   => $codResidente,
                            'cod_personal'    => $codPersonal,
                            'nombre'          => $nombreDiag,
                            'tipo'            => 'CRONICO',
                            'certeza'         => 'CONFIRMADO',
                            'fecha_hora'      => $ahora,
                            'estado'          => 'ACTIVO',
                        ]);
                    }
                } elseif (isset($datos[$campo]) && !$datos[$campo]) {
                    Diagnostico::where('cod_residente', $codResidente)
                        ->where('nombre', $nombreDiag)
                        ->update(['estado' => 'INACTIVO']);
                }
            }

            // 6. Observación Médica / Atención / Nota Clínica
            if (isset($datos['observacion_medica']) && trim((string)$datos['observacion_medica']) !== '') {
                $codAtencion = 'ATN_' . strtoupper(Str::random(10));
                Atencion::create([
                    'cod_atencion'  => $codAtencion,
                    'cod_residente' => $codResidente,
                    'cod_area'      => $codArea,
                    'cod_personal'  => $codPersonal,
                    'tipo_atencion' => 'VALORACION_MEDICA',
                    'motivo'        => 'Apertura / Actualización de Ficha Médica',
                    'fecha_hora'    => $ahora,
                    'estado'        => 'COMPLETADA',
                    'observacion'   => trim($datos['observacion_medica']),
                ]);

                NotaClinica::create([
                    'cod_nota'      => 'NOT_' . strtoupper(Str::random(10)),
                    'cod_atencion'  => $codAtencion,
                    'cod_residente' => $codResidente,
                    'cod_personal'  => $codPersonal,
                    'tipo_nota'     => 'EVOLUCION_MEDICA',
                    'contenido'     => trim($datos['observacion_medica']),
                    'fecha_hora'    => $ahora,
                    'estado'        => 'REGISTRADA',
                ]);
            }

            return $this->obtenerFichaAgregada($codResidente);
        });
    }

    /**
     * Calcula estadísticas para el panel general de fichas.
     */
    public function contarEstadisticas(): array
    {
        $totalAdultos = Residente::count();
        $conFicha = Residente::where(function ($q) {
            $q->whereHas('atenciones')
              ->orWhereHas('diagnosticos')
              ->orWhereHas('antecedentesClinicos')
              ->orWhereHas('alergias');
        })->count();

        $sinFicha = max(0, $totalAdultos - $conFicha);
        $alergiasCount = Alergia::where('estado', 'ACTIVO')->distinct('cod_residente')->count('cod_residente');
        $cuidadosCount = AntecedenteClinico::where('estado', 'ACTIVO')->count();

        return [
            'total'     => $totalAdultos,
            'con_ficha' => $conFicha,
            'sin_ficha' => $sinFicha,
            'alergias'  => $alergiasCount,
            'cuidados'  => $cuidadosCount,
        ];
    }
}