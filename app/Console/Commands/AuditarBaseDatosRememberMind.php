<?php

namespace App\Console\Commands;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\EvaluarReduccionConservacion;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\InventarioExperto;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:auditar-base-datos-remember-mind {--contar-filas : Consultar también conteos agregados de las tablas expertas} {--evaluar-reduccion : Ensayar cuatro consolidaciones en memoria, sin escrituras}')]
#[Description('Inventaría tablas expertas y FK entrantes mediante lectura, sin mostrar registros clínicos ni modificar estructura')]
class AuditarBaseDatosRememberMind extends Command
{
    public function handle(): int
    {
        if ($this->option('evaluar-reduccion')) {
            $resultado = app(EvaluarReduccionConservacion::class)->evaluar(DB::connection());
            $this->line($resultado['estado']);
            $this->table(['Origen', 'Filas comprobadas'], array_map(static fn ($tabla, $cantidad) => [$tabla, $cantidad],
                array_keys($resultado['filas_por_tabla']), array_values($resultado['filas_por_tabla'])));
            $this->line('Filas originales reconstruidas exactamente. Sin DDL; no acredita migración ni recálculo institucional completo.');

            return self::SUCCESS;
        }
        $inventario = InventarioExperto::diagnosticar(DB::connection(), (bool) $this->option('contar-filas'));
        $this->line('Inventario de lectura: '.$inventario['motor']);
        $this->line('Expertas presentes: '.$inventario['expertas_presentes'].' / '.$inventario['expertas_esperadas']);
        $this->line('Otras tablas físicas (incluye técnicas): '.count($inventario['otras_tablas_fisicas']));
        $this->table(['Tabla experta', 'Grupo', 'Presente', 'Filas', 'FK entrantes'], array_map(static fn ($t) => [
            $t['tabla'], $t['grupo'], $t['presente'] ? 'SI' : 'NO', $t['filas'] ?? 'NO CONTADAS', count($t['referencias_entrantes']),
        ], $inventario['detalle']));
        $this->line('Un conteo cero no demuestra desuso. Este inventario no autoriza eliminaciones ni certifica integridad clínica o 4FN.');

        return $inventario['expertas_ausentes'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
