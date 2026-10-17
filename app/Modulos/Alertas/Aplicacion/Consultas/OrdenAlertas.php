<?php
namespace App\Modulos\Alertas\Aplicacion\Consultas;
use Illuminate\Database\Eloquent\Builder;
final class OrdenAlertas
{
    public static function aplicar(Builder $query): Builder
    {
        return $query->orderByRaw("CASE WHEN estado IN ('ABIERTA','EN_ATENCION','RECONOCIDA','ASIGNADA','PENDIENTE') THEN 0 ELSE 1 END")
            ->orderByRaw("CASE UPPER(prioridad) WHEN 'CRITICO' THEN 0 WHEN 'CRITICA' THEN 0 WHEN 'ALTO' THEN 1 WHEN 'ALTA' THEN 1 WHEN 'MEDIO' THEN 2 WHEN 'MEDIA' THEN 2 ELSE 3 END")
            ->orderBy('fecha_hora')->orderBy('cod_alerta');
    }
}
