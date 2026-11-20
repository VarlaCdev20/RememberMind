<?php

declare(strict_types=1);

namespace Tests\Support;

/** Sitúa las pruebas históricas de conservación en su versión de esquema. */
trait EsquemaHistoricoHechos
{
    private function transicionesPosterioresDeHechos(bool $revertir): void
    {
        $archivos = [
            '2026_11_14_000013_consolidate_componentes_regla',
            '2026_11_17_000003_consolidate_evaluaciones_reglas',
            '2026_11_17_000004_consolidate_instantaneas_reglas',
            '2026_11_18_000002_remove_derived_context_columns',
            '2026_11_20_000003_derive_iadl_functional_context',
            '2026_11_20_000007_separate_semantic_declarations',
            '2026_11_20_000008_normalize_event_contexts',
            '2026_11_20_000009_rename_expert_evaluation_facts',
        ];
        foreach ($revertir ? array_reverse($archivos) : $archivos as $archivo) {
            $migracion = require database_path('migrations/'.$archivo.'.php');
            if ($revertir) {
                $migracion->down();
            } else {
                $migracion->up();
            }
        }
    }
}
