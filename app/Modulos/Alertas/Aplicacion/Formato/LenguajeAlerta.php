<?php

namespace App\Modulos\Alertas\Aplicacion\Formato;

use App\Compartido\Aplicacion\Formato\TextoVisible;
use App\Models\Alerta;
use App\Models\Personal;

/** Etiquetas de lectura; conserva intactos los valores y las reglas del dominio. */
final class LenguajeAlerta
{
    public static function responsable(?Personal $personal): string
    {
        if (! $personal) {
            return 'Sin responsable asignado';
        }

        $nombre = trim($personal->nombres.' '.$personal->apellido_paterno.' '.$personal->apellido_materno);

        return $nombre.' · '.($personal->profesion ?: 'Profesión no registrada');
    }

    public static function tipo(?string $valor): string
    {
        return match ($valor) {
            'INCIDENTE' => 'Incidente registrado',
            'SEGUIMIENTO' => 'Seguimiento de cuidados',
            default => self::legible(TextoVisible::sinCodigosInternos($valor) ?: 'Alerta registrada'),
        };
    }

    public static function prioridad(?string $valor): string
    {
        return match ($valor) {
            'CRITICO', 'CRITICA' => 'Prioridad crítica',
            'ALTO', 'ALTA' => 'Prioridad alta',
            'MEDIO', 'MEDIA' => 'Prioridad media',
            'BAJO', 'BAJA' => 'Prioridad baja',
            default => 'Prioridad sin registrar',
        };
    }

    public static function estado(?string $valor): string
    {
        return match ($valor) {
            'ABIERTA', 'PENDIENTE' => 'Por atender',
            'RECONOCIDA' => 'Aviso reconocido · por atender',
            'ASIGNADA' => 'Responsable asignado · por atender',
            'EN_ATENCION' => 'En atención',
            'ATENDIDA' => 'Atendida · por cerrar',
            'CERRADA' => 'Cerrada',
            'ANULADA' => 'Anulada',
            default => 'Registro anterior · '.self::legible($valor ?: 'Sin estado'),
        };
    }

    public static function situacion(Alerta $alerta): string
    {
        if (in_array($alerta->estado, Alerta::ESTADOS_ACTIVOS, true)) {
            return in_array($alerta->prioridad, Alerta::PRIORIDADES_ALTAS, true)
                ? 'Urgente · prioridad alta o crítica con atención pendiente'
                : self::estado($alerta->estado).' · '.self::prioridad($alerta->prioridad);
        }

        return match ($alerta->estado) {
            'CERRADA', 'ANULADA' => self::estado($alerta->estado).' · fuera de los pendientes actuales',
            'ATENDIDA' => 'Atención registrada · pendiente de cierre',
            default => 'Estado anterior conservado · no clasificado como pendiente actual',
        };
    }

    private static function legible(string $valor): string
    {
        return mb_strtoupper(mb_substr($valor, 0, 1)).mb_strtolower(str_replace('_', ' ', mb_substr($valor, 1)));
    }
}
