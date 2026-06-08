<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;

class AsignacionAdultoMayor extends Model
{
    use GeneraCodigo;
    protected $table = 'asignacion_adulto_mayor';
    protected $primaryKey = 'cod_asig_adulto';
    protected $keyType = 'string';
    protected $prefixCode = 'AAM';
    protected $digitsCode = 5;
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'cod_asig_adulto',
        'cod_am',
        'cod_habitacion',
        'cod_cama',
        'fecha_asignacion',
        'hora_asignacion',
        'estado',
        'observaciones',
        'registrado_por',
    ];

    protected static function booted(): void
    {
        static::creating(function ($asignacion) {
            if (!$asignacion->cod_asig_adulto) {
                $ultimo = self::where('cod_asig_adulto', 'like', 'AAM_%')
                    ->orderByDesc('cod_asig_adulto')
                    ->value('cod_asig_adulto');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 4)) + 1
                    : 1;

                $asignacion->cod_asig_adulto = 'AAM_' . str_pad($numero, 5, '0', STR_PAD_LEFT);
            }
        });

        static::saved(function (self $asignacion) {
            $estado = strtoupper((string) $asignacion->estado);

            if (in_array($estado, ['ACTIVO', 'ACTIVA'], true)) {
                AdultoMayor::where('cod_am', $asignacion->cod_am)->update([
                    'cod_habitacion' => $asignacion->cod_habitacion,
                    'cod_cama' => $asignacion->cod_cama,
                ]);

                if ($asignacion->cod_cama) {
                    Cama::where('cod_cama', $asignacion->cod_cama)->update(['estado' => 'OCUPADA']);
                }

                if ($asignacion->cod_habitacion) {
                    static::actualizarEstadoHabitacion($asignacion->cod_habitacion);
                }

                return;
            }

            $adulto = AdultoMayor::where('cod_am', $asignacion->cod_am)->first();
            if ($adulto && $adulto->cod_cama === $asignacion->cod_cama) {
                $adulto->update([
                    'cod_habitacion' => null,
                    'cod_cama' => null,
                ]);
            }

            if ($asignacion->cod_cama) {
                Cama::where('cod_cama', $asignacion->cod_cama)->update(['estado' => 'DISPONIBLE']);
            }

            if ($asignacion->cod_habitacion) {
                static::actualizarEstadoHabitacion($asignacion->cod_habitacion);
            }
        });
    }

    private static function actualizarEstadoHabitacion(string $codHabitacion): void
    {
        $total = Cama::where('cod_habitacion', $codHabitacion)->count();

        if ($total === 0) {
            return;
        }

        $ocupadas = Cama::where('cod_habitacion', $codHabitacion)
            ->where('estado', 'OCUPADA')
            ->count();

        Habitacion::where('cod_habitacion', $codHabitacion)->update([
            'estado' => $ocupadas >= $total ? 'OCUPADA' : 'DISPONIBLE',
        ]);
    }
}
