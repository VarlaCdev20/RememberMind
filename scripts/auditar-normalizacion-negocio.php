<?php

declare(strict_types=1);

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\InventarioExperto;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

// Evidencia de esquema y conteos. No certifica 5FN ni imprime datos personales.
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$c = DB::connection();
if ($c->getDriverName() !== 'pgsql') {
    throw new RuntimeException('La auditoría instalada requiere PostgreSQL.');
}
$tecnicas = ['activity_log', 'cache', 'cache_locks', 'failed_jobs', 'job_batches', 'jobs', 'migrations',
    'model_has_permissions', 'model_has_roles', 'password_reset_tokens', 'permissions', 'personal_access_tokens',
    'role_has_permissions', 'roles', 'secuencias_codigos', 'sessions'];
$expertas = [...InventarioExperto::TABLAS, ...InventarioExperto::CONSERVACION, ...InventarioExperto::REVISION_PROFESIONAL];
$c->beginTransaction();
try {
    $c->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
    if ($c->selectOne("SELECT current_setting('transaction_read_only') AS estado")->estado !== 'on') {
        throw new RuntimeException('No se confirmó el modo de solo lectura.');
    }
    $s = $c->getSchemaBuilder();
    $nombres = array_column($c->select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public' ORDER BY tablename"), 'tablename');
    $tablas = [];
    foreach (array_diff($nombres, $tecnicas) as $nombre) {
        $tablas[$nombre] = [
            'grupo' => in_array($nombre, $expertas, true) ? 'experta' : 'operativa',
            'filas' => $c->table($nombre)->count(),
            'columnas' => $s->getColumns($nombre),
            'indices' => $s->getIndexes($nombre),
            'indices_sql' => $c->select('SELECT x.relname AS nombre, i.indisunique AS unico, i.indisprimary AS primario, i.indisvalid AS valido, pg_get_expr(i.indpred, i.indrelid) AS predicado, pg_get_expr(i.indexprs, i.indrelid) AS expresiones, pg_get_indexdef(i.indexrelid) AS definicion FROM pg_index i JOIN pg_class x ON x.oid = i.indexrelid WHERE i.indrelid = to_regclass(?) ORDER BY x.relname', ['public.'.$nombre]),
            'fk' => $s->getForeignKeys($nombre),
            'restricciones' => $c->select('SELECT conname, contype, convalidated, pg_get_constraintdef(oid) AS definicion FROM pg_constraint WHERE conrelid = to_regclass(?) ORDER BY conname', ['public.'.$nombre]),
            'triggers' => $c->select('SELECT tgname, pg_get_triggerdef(oid) AS definicion, pg_get_functiondef(tgfoid) AS funcion FROM pg_trigger WHERE tgrelid = to_regclass(?) AND NOT tgisinternal ORDER BY tgname', ['public.'.$nombre]),
        ];
    }
    $claves = static fn (array $t): array => array_values(array_filter($t['indices'], static function ($i) use ($t): bool {
        foreach ($t['indices_sql'] as $sql) {
            if ($sql->nombre === $i['name']) {
                return $i['unique'] && $sql->valido && $sql->predicado === null && $sql->expresiones === null;
            }
        }

        return false;
    }));
    $q = static fn (string $columna): string => $c->getQueryGrammar()->wrap($columna);
    $dependencias = [];
    foreach ($tablas as $nombre => $t) {
        foreach ($t['fk'] as $fk) {
            if ($fk['foreign_schema'] !== 'public' || ! isset($tablas[$fk['foreign_table']])) {
                continue;
            }
            foreach ($claves($tablas[$fk['foreign_table']]) as $k) {
                if (count($k['columns']) >= count($fk['foreign_columns']) || array_diff($k['columns'], $fk['foreign_columns'])) {
                    continue;
                }
                $det = array_map(static fn ($v) => $fk['columns'][array_search($v, $fk['foreign_columns'], true)], $k['columns']);
                $dep = array_values(array_diff($fk['columns'], $det));
                // MATCH SIMPLE no impone la dependencia si cualquier componente es NULL.
                $filtro = implode(' AND ', array_map(static fn ($v) => $q($v).' IS NOT NULL', $fk['columns']));
                $agrupacion = implode(', ', array_map($q, $det));
                $valores = implode(', ', array_map($q, $dep));
                $grupos = "SELECT COUNT(*) AS n, COUNT(DISTINCT ROW($valores)) AS v FROM ".$q($nombre)." WHERE $filtro GROUP BY $agrupacion";
                $datos = $c->selectOne("SELECT COALESCE(SUM(n),0)::int AS filas, COUNT(*) FILTER (WHERE n>1)::int AS determinantes_repetidos, COALESCE(MAX(n),0)::int AS maximo_por_determinante, COUNT(*) FILTER (WHERE v>1)::int AS contradicciones FROM ($grupos) g");
                $claveLocal = count(array_filter($claves($t), static fn ($u) => ! array_diff($u['columns'], $det))) > 0;
                $dependencias[] = ['tabla' => $nombre, 'determinante' => $det, 'dependiente' => $dep,
                    'fk' => $fk['name'], 'filtro_no_nulos' => $fk['columns'], 'clave_unica_declarada' => $claveLocal, 'muestra' => $datos];
            }
        }
    }
    $comprobaciones = [];
    foreach ([
        'consentimiento_admision' => 'SELECT COUNT(*)::int AS filas, COUNT(*) FILTER (WHERE c.cod_residente IS DISTINCT FROM a.cod_residente)::int AS incoherencias FROM consentimientos c JOIN admisiones a USING(cod_admision)',
        'ocupacion_admision' => 'SELECT COUNT(*)::int AS filas, COUNT(*) FILTER (WHERE c.cod_residente IS DISTINCT FROM a.cod_residente)::int AS incoherencias FROM ocupaciones_cama c JOIN admisiones a USING(cod_admision)',
        'ejecucion_plan' => 'SELECT COUNT(*)::int AS filas, COUNT(*) FILTER (WHERE e.cod_residente IS DISTINCT FROM p.cod_residente)::int AS incoherencias FROM ejecuciones_cuidado e JOIN intervenciones_cuidado i USING(cod_intervencion) JOIN planes_cuidado p USING(cod_plan)',
        'respuesta_opcion' => 'SELECT COUNT(*)::int AS filas, COUNT(*) FILTER (WHERE r.cod_pregunta IS DISTINCT FROM o.cod_pregunta)::int AS incoherencias FROM respuestas_instrumento r JOIN opciones_pregunta o USING(cod_opcion)',
        'administracion_horario' => 'SELECT COUNT(*)::int AS filas, COUNT(*) FILTER (WHERE a.cod_prescripcion IS DISTINCT FROM h.cod_prescripcion)::int AS incoherencias FROM administraciones_medicacion a JOIN horarios_prescripcion h USING(cod_horario_prescripcion)',
        'dias_horario' => "SELECT COUNT(*)::int AS filas, COUNT(*) FILTER (WHERE dias_semana IS NOT NULL AND BTRIM(dias_semana)<>'')::int AS con_seleccion FROM horarios_prescripcion",
        'dias_cuidado' => "SELECT COUNT(*)::int AS filas, COUNT(*) FILTER (WHERE dias_semana IS NOT NULL AND BTRIM(dias_semana)<>'')::int AS con_seleccion FROM programaciones_cuidado",
    ] as $id => $sql) {
        $derivados = ['consentimiento_admision' => 'consentimientos', 'ejecucion_plan' => 'ejecuciones_cuidado'];
        if (isset($derivados[$id]) && ! $s->hasColumn($derivados[$id], 'cod_residente')) {
            $comprobaciones[$id] = [
                'estado' => 'CONTEXTO_DERIVADO_SIN_COLUMNA_REDUNDANTE',
                'filas' => $c->table($derivados[$id])->count(),
                'nota' => 'La conservación de la migración y sus guardas se verifican mediante pruebas separadas.',
            ];

            continue;
        }
        $semanales = ['dias_horario' => 'horarios_prescripcion', 'dias_cuidado' => 'programaciones_cuidado'];
        if (isset($semanales[$id]) && $s->hasColumn($semanales[$id], 'cod_patron_semanal')) {
            $sql = "SELECT COUNT(*)::int AS filas, COUNT(*) FILTER (WHERE cod_patron_semanal <> 'SEM_TODOS')::int AS con_seleccion FROM ".$q($semanales[$id]);
        }
        $comprobaciones[$id] = $c->selectOne($sql);
    }
    foreach ([
        'asignacion_residente' => ['asignaciones_jornada', ['cod_jornada', 'cod_personal', 'cod_residente'], "tipo_evento='RESIDENTE'"],
        'relacion_semantica' => ['relaciones_semanticas', ['cod_nodo_origen', 'tipo_relacion', 'cod_nodo_destino'], 'TRUE'],
    ] as $id => [$tabla, [$a, $b, $d], $filtro]) {
        $sql = 'WITH r AS (SELECT DISTINCT '.$q($a).','.$q($b).','.$q($d).' FROM '.$q($tabla).' WHERE '.$filtro.'), '
            .'ab AS (SELECT DISTINCT '.$q($a).','.$q($b).' FROM r), ad AS (SELECT DISTINCT '.$q($a).','.$q($d).' FROM r), bd AS (SELECT DISTINCT '.$q($b).','.$q($d).' FROM r), '
            .'j AS (SELECT ab.'.$q($a).',ab.'.$q($b).',ad.'.$q($d).' FROM ab JOIN ad USING('.$q($a).') JOIN bd USING('.$q($b).','.$q($d).')) '
            .'SELECT (SELECT COUNT(*)::int FROM r) AS originales, (SELECT COUNT(*)::int FROM j) AS reunidas, (SELECT COUNT(*)::int FROM (SELECT * FROM j EXCEPT SELECT * FROM r) x) AS combinaciones_espurias';
        $comprobaciones[$id] = $c->selectOne($sql);
    }
    $reconstrucciones = [];
    foreach ([
        ['mediciones_clinicas', 'cod_atencion', 'cod_residente'],
        ['registros_observaciones_cuidado', 'cod_atencion', 'cod_residente'],
        ['nodos_semanticos', 'cod_dominio_valores', 'cod_version_modelo'],
        ['nodos_semanticos', 'cod_nodo_propietario_primario', 'cod_version_modelo'],
    ] as [$tabla, $det, $dep]) {
        $columnas = array_column($tablas[$tabla]['columnas'], 'name');
        if (! in_array($det,$columnas,true) || ! in_array($dep,$columnas,true)) {
            $reconstrucciones[] = ['tabla'=>$tabla,'determinante'=>$det,'dependiente'=>$dep,
                'estado'=>'DEPENDENCIA_NO_ALMACENADA_EN_ESTA_RELACION',
                'nota'=>'La reconstrucción de la descomposición se verifica mediante pruebas pobladas de migración y reversión.'];
            continue;
        }
        $restantes = array_values(array_diff($columnas, [$dep]));
        $proyeccion = implode(',', array_map(static fn ($v) => ($v === $dep ? 'p.' : 'r.').$q($v), $columnas));
        $sql = 'WITH original AS (SELECT * FROM '.$q($tabla).' WHERE '.$q($det).' IS NOT NULL), '
            .'p AS (SELECT DISTINCT '.$q($det).','.$q($dep).' FROM original), '
            .'r AS (SELECT '.implode(',', array_map($q, $restantes)).' FROM original), '
            .'j AS (SELECT '.$proyeccion.' FROM r JOIN p USING('.$q($det).')) '
            .'SELECT (SELECT COUNT(*)::int FROM original) AS filas, '
            .'(SELECT COUNT(*)::int FROM (SELECT * FROM original EXCEPT ALL SELECT * FROM j) x) AS perdidas, '
            .'(SELECT COUNT(*)::int FROM (SELECT * FROM j EXCEPT ALL SELECT * FROM original) x) AS adicionales';
        $reconstrucciones[] = ['tabla' => $tabla, 'determinante' => $det, 'dependiente' => $dep, 'resultado' => $c->selectOne($sql)];
    }
    echo json_encode(['extraido_en' => date(DATE_ATOM), 'base' => $c->getDatabaseName(), 'motor' => 'pgsql',
        'solo_lectura' => true, 'total_fisicas' => count($nombres), 'tecnicas_excluidas' => array_values(array_intersect($nombres, $tecnicas)),
        'expertas_ausentes' => array_values(array_diff($expertas, $nombres)), 'tablas' => $tablas,
        'candidatas_fk_compuesta' => $dependencias, 'comprobaciones' => $comprobaciones,
        'reconstrucciones' => $reconstrucciones], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} finally {
    $c->rollBack();
}
