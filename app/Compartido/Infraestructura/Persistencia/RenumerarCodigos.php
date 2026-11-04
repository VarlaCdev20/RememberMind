<?php

namespace App\Compartido\Infraestructura\Persistencia;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use RuntimeException;

/** Conversión administrativa autorizada; nunca usa borrado de historia ni desactiva triggers. */
final class RenumerarCodigos
{
    public function ejecutar(Connection $db, string $directorioPrivado, bool $revertirEnsayo = false, ?callable $copiarArchivo = null, ?string $fallarEn = null, ?callable $verificarConsumidores = null): array
    {
        if ($db->getDriverName() !== 'pgsql' || ! is_dir($directorioPrivado) || $db->transactionLevel() !== 0) {
            throw new RuntimeException('Se requiere PostgreSQL y un directorio privado existente.');
        }
        $archivo = $directorioPrivado.'/mapa-'.bin2hex(random_bytes(8)).'.json';
        $db->beginTransaction();
        try {
            $db->statement("SET LOCAL lock_timeout = '10s'");
            $tablas = array_map(fn ($r) => $r->table_name, $db->select("SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_type='BASE TABLE' ORDER BY table_name"));
            $db->statement('LOCK TABLE '.implode(', ', array_map($this->nombre(...), $tablas)).' IN ACCESS EXCLUSIVE MODE');
            foreach (['jobs', 'failed_jobs', 'job_batches'] as $tabla) {
                if (in_array($tabla, $tablas, true) && $db->table($tabla)->exists()) {
                    throw new RuntimeException('La cola debe estar vacía antes de convertir identidades.');
                }
            }
            if (! in_array('secuencias_codigos', $tablas, true)) {
                throw new RuntimeException('Falta el contador técnico aprobado.');
            }
            $fk = $this->restricciones($db);
            foreach ($fk as $r) {
                if (! $r->validada || ! $r->exigida) {
                    throw new RuntimeException('La base tiene una FK sin garantía vigente; revisar antes de convertir.');
                }
            }
            $this->verificarRelaciones($db);
            $columnas = [];
            $mapa = [];
            $temporal = [];
            $serial = 0;
            foreach (CatalogoCodigos::ENTIDADES as $tabla => [$pk, $prefijo]) {
                if (! in_array($tabla, $tablas, true)) {
                    throw new RuntimeException('Inventario incompleto: '.$tabla);
                }
                $ids = $db->table($tabla)->pluck($pk)->all();
                $principal = $tabla === 'usuarios' ? $db->table('usuarios')->where('correo', 'admincasaamandita@gmail.com')->value($pk) : null;
                usort($ids, function (string $a, string $b) use ($principal): int {
                    if ($principal !== null && ($a === $principal || $b === $principal)) {
                        return $a === $principal ? -1 : 1;
                    }
                    // Las identidades semilla numeradas conservan su orden institucional.
                    $na = preg_match('/^(?:COD_)?[A-Z]{2,6}_([0-9]{4,12})$/', $a, $ma) ? (int) $ma[1] : PHP_INT_MAX;
                    $nb = preg_match('/^(?:COD_)?[A-Z]{2,6}_([0-9]{4,12})$/', $b, $mb) ? (int) $mb[1] : PHP_INT_MAX;

                    return ($na <=> $nb) ?: strcmp($a, $b);
                });
                foreach ($ids as $i => $id) {
                    $mapa[$tabla][(string) $id] = CatalogoCodigos::formatear($tabla, $i + 1);
                    $temporal[$tabla][(string) $id] = 'TMP_'.str_pad((string) ++$serial, 16, '0', STR_PAD_LEFT);
                }
                $mapa[$tabla] ??= [];
                $columnas[$tabla][$pk] = $tabla;
                foreach ($ids as $id) {
                    if (str_starts_with((string) $id, 'TMP_')) {
                        throw new RuntimeException('El espacio temporal está ocupado; no convertir.');
                    }
                }
            }
            foreach ($db->select("SELECT t.relname AS tabla,a.attname AS columna,r.relname AS destino,b.attname AS columna_destino FROM pg_constraint c JOIN pg_class t ON t.oid=c.conrelid JOIN pg_class r ON r.oid=c.confrelid JOIN pg_namespace n ON n.oid=t.relnamespace JOIN LATERAL unnest(c.conkey,c.confkey) k(ka,kb) ON true JOIN pg_attribute a ON a.attrelid=t.oid AND a.attnum=k.ka JOIN pg_attribute b ON b.attrelid=r.oid AND b.attnum=k.kb WHERE c.contype='f' AND n.nspname='public'") as $ref) {
                if (($catalogo = CatalogoCodigos::ENTIDADES[$ref->destino] ?? null) && $catalogo[0] === $ref->columna_destino) {
                    if (isset($columnas[$ref->tabla][$ref->columna]) && $columnas[$ref->tabla][$ref->columna] !== $ref->destino) {
                        throw new RuntimeException('Referencia estructural ambigua.');
                    }
                    $columnas[$ref->tabla][$ref->columna] = $ref->destino;
                }
            }
            $pendientes = $columnas;
            $columnas = [];
            while ($pendientes) {
                $avance = false;
                foreach ($pendientes as $tabla => $campos) {
                    $dependencias = array_filter(array_unique(array_values($campos)), fn ($destino) => $destino !== $tabla && isset($pendientes[$destino]));
                    if ($dependencias) {
                        continue;
                    }
                    $columnas[$tabla] = $campos;
                    unset($pendientes[$tabla]);
                    $avance = true;
                }
                if (! $avance) {
                    throw new RuntimeException('Dependencia circular requiere ordenar la conversión explícitamente.');
                }
            }
            $especiales = [];
            $esperado = [];
            foreach ($tablas as $tabla) {
                if (in_array($tabla, ['cache', 'cache_locks', 'sessions', 'secuencias_codigos'], true)) {
                    continue;
                }
                $filas = $db->table($tabla)->get()->map(fn ($r) => (array) $r)->all();
                foreach ($filas as &$fila) {
                    $original = $fila;
                    foreach ($columnas[$tabla] ?? [] as $columna => $destino) {
                        $valor = $fila[$columna];
                        if ($valor !== null && isset($mapa[$destino][(string) $valor])) {
                            $fila[$columna] = $mapa[$destino][(string) $valor];
                        }
                    }
                    $despuesSql = $fila;
                    $this->referenciasExternas($tabla, $fila, $original, $mapa, $db);
                    $diferencias = array_diff_assoc($fila, $original);
                    foreach (array_keys($columnas[$tabla] ?? []) as $columna) {
                        unset($diferencias[$columna]);
                    }
                    if ($diferencias) {
                        $pk = CatalogoCodigos::ENTIDADES[$tabla][0] ?? 'id';
                        $selector = isset($despuesSql[$pk]) ? [$pk => $despuesSql[$pk]] : $despuesSql;
                        $especiales[] = [$tabla, $selector, $diferencias];
                        if ($tabla === 'documentos_clinicos' && isset($diferencias['ruta_archivo'])) {
                            if ($copiarArchivo === null) {
                                throw new RuntimeException('La conversión requiere copia verificada de archivos privados.');
                            }
                            $copiarArchivo($original['ruta_archivo'], $fila['ruta_archivo']);
                        }
                    }
                }
                unset($fila);
                $esperado[$tabla] = $this->huella($filas);
            }
            $this->guardarPrivado($archivo, ['estado' => 'preparado', 'base' => $db->getDatabaseName(), 'mapa' => $mapa, 'huellas_esperadas' => $esperado, 'restricciones' => $fk]);
            $db->statement('CREATE TEMP TABLE rm_mapa_codigos (tabla varchar(64), anterior varchar(20), temporal varchar(20), nuevo varchar(20), PRIMARY KEY(tabla,anterior), UNIQUE(tabla,temporal), UNIQUE(tabla,nuevo)) ON COMMIT DROP');
            $lote = [];
            foreach ($mapa as $tabla => $valores) {
                foreach ($valores as $antes => $nuevo) {
                    $lote[] = ['tabla' => $tabla, 'anterior' => $antes, 'temporal' => $temporal[$tabla][$antes], 'nuevo' => $nuevo];
                    if (count($lote) >= 500) {
                        $db->table('rm_mapa_codigos')->insert($lote);
                        $lote = [];
                    }
                }
            }
            if ($lote) {
                $db->table('rm_mapa_codigos')->insert($lote);
            }
            foreach ($fk as $r) {
                if (str_contains($r->definicion, 'ON UPDATE RESTRICT')) {
                    // RESTRICT no difiere su comprobación. Bajo el bloqueo exclusivo, mantener
                    // una FK NO ACTION vigente durante la conversión y restituir su definición.
                    $definicion = str_replace('ON UPDATE RESTRICT', 'ON UPDATE NO ACTION', $r->definicion);
                    $definicion = preg_replace('/\s+(?:NOT )?DEFERRABLE(?: INITIALLY (?:DEFERRED|IMMEDIATE))?/', '', $definicion);
                    $db->statement('ALTER TABLE '.$this->nombre($r->tabla).' DROP CONSTRAINT '.$this->nombre($r->nombre)
                        .', ADD CONSTRAINT '.$this->nombre($r->nombre).' '.$definicion.' DEFERRABLE INITIALLY DEFERRED');
                    continue;
                }
                $db->statement('ALTER TABLE '.$this->nombre($r->tabla).' ALTER CONSTRAINT '.$this->nombre($r->nombre).' DEFERRABLE INITIALLY DEFERRED');
            }
            $db->statement('SET CONSTRAINTS ALL DEFERRED');
            foreach ([['anterior', 'temporal'], ['temporal', 'nuevo']] as [$origen,$destino]) {
                foreach ($columnas as $tabla => $campos) {
                    $sets = [];
                    $bindings = [];
                    foreach ($campos as $campo => $objetivo) {
                        $sets[] = $this->nombre($campo).' = COALESCE((SELECT '.$destino.' FROM rm_mapa_codigos WHERE tabla = ? AND '.$origen.' = t.'.$this->nombre($campo).'), t.'.$this->nombre($campo).')';
                        $bindings[] = $objetivo;
                    }
                    $db->update('UPDATE '.$this->nombre($tabla).' AS t SET '.implode(', ', $sets), $bindings);
                }
                if ($fallarEn === $destino) {
                    throw new RuntimeException('Fallo de ensayo en fase '.$destino);
                }
            }
            foreach ($especiales as [$tabla, $selector, $cambios]) {
                if ($db->table($tabla)->where($selector)->update($cambios) !== 1) {
                    throw new RuntimeException('Referencia externa no localizada inequívocamente: '.$tabla);
                }
            }
            // Son estados técnicos transitorios; una sesión antigua puede contener modelos serializados.
            foreach (['sessions', 'cache', 'cache_locks'] as $tabla) {
                if (in_array($tabla, $tablas, true)) {
                    $db->table($tabla)->delete();
                }
            }
            foreach ($mapa as $tabla => $valores) {
                $db->table('secuencias_codigos')->updateOrInsert(['entidad' => $tabla], ['ultimo_valor' => count($valores)]);
            }
            $db->statement('SET CONSTRAINTS ALL IMMEDIATE');
            $this->verificarRelaciones($db);
            foreach ($fk as $r) {
                if (str_contains($r->definicion, 'ON UPDATE RESTRICT')) {
                    $db->statement('ALTER TABLE '.$this->nombre($r->tabla).' DROP CONSTRAINT '.$this->nombre($r->nombre)
                        .', ADD CONSTRAINT '.$this->nombre($r->nombre).' '.$r->definicion);
                    continue;
                }
                $db->statement('ALTER TABLE '.$this->nombre($r->tabla).' ALTER CONSTRAINT '.$this->nombre($r->nombre).' '.($r->diferible ? 'DEFERRABLE' : 'NOT DEFERRABLE').' INITIALLY '.($r->diferida ? 'DEFERRED' : 'IMMEDIATE'));
            }
            if ($this->contratoRelacional($fk) !== $this->contratoRelacional($this->restricciones($db))) {
                throw new RuntimeException('El contrato FK cambió durante la conversión.');
            }
            foreach ($esperado as $tabla => $huella) {
                if ($this->huella($db->table($tabla)->get()->map(fn ($r) => (array) $r)->all()) !== $huella) {
                    throw new RuntimeException('Contenido no conservado: '.$tabla);
                }
            }
            if ($verificarConsumidores !== null) {
                $verificarConsumidores($mapa);
                foreach ($esperado as $tabla => $huella) {
                    if ($this->huella($db->table($tabla)->get()->map(fn ($r) => (array) $r)->all()) !== $huella) {
                        throw new RuntimeException('El consumidor alteró contenido durante verificación: '.$tabla);
                    }
                }
                foreach ($mapa as $tabla => $valores) {
                    if ((int) $db->table('secuencias_codigos')->where('entidad', $tabla)->value('ultimo_valor') !== count($valores)) {
                        throw new RuntimeException('El consumidor alteró reservas durante verificación.');
                    }
                }
                if ($this->contratoRelacional($fk) !== $this->contratoRelacional($this->restricciones($db))) {
                    throw new RuntimeException('El consumidor alteró el contrato FK.');
                }
            }
            if ($fallarEn === 'verificado') {
                throw new RuntimeException('Fallo de ensayo después de verificar.');
            }
            $resultado = ['entidades' => count($mapa), 'identidades' => $serial, 'fk' => count($fk), 'tablas_verificadas' => count($esperado), 'manifest' => $archivo, 'revertido' => $revertirEnsayo];
            $this->guardarPrivado($archivo.'.verificado.json', ['estado' => 'verificado_antes_de_commit'] + $resultado);
            if ($revertirEnsayo) {
                $db->rollBack();
            } else {
                $db->commit();
            }
            try {
                $this->guardarPrivado($archivo.'.resultado.json', $resultado);
            } catch (\Throwable $errorDocumental) {
                report($errorDocumental);
                $resultado['advertencia'] = $revertirEnsayo
                    ? 'El ensayo se revirtió; falta guardar su confirmación documental.'
                    : 'La conversión está aplicada y verificada; falta guardar su confirmación documental. No repetir la operación.';
            }

            return $resultado;
        } catch (\Throwable $e) {
            if ($db->transactionLevel() > 0) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    private function referenciasExternas(string $tabla, array &$fila, array $antes, array $mapa, Connection $db): void
    {
        foreach (match ($tabla) {
            'model_has_roles','model_has_permissions' => [['model_type', 'model_id']],
            'personal_access_tokens' => [['tokenable_type', 'tokenable_id']],
            'activity_log' => [['subject_type', 'subject_id'], ['causer_type', 'causer_id']],
            default => [],
        } as [$tipo,$id]) {
            if ($fila[$tipo] === null || $fila[$id] === null) {
                continue;
            }
            $clase = Relation::getMorphedModel($fila[$tipo]) ?? $fila[$tipo];
            if (! is_a($clase, Model::class, true)) {
                throw new RuntimeException('Tipo polimórfico sin resolver: '.$fila[$tipo]);
            }
            $destino = (new $clase)->getTable();
            $fila[$id] = $mapa[$destino][(string) $fila[$id]] ?? $fila[$id];
        }
        if ($tabla === 'alertas') {
            if ($fila['cod_registro'] !== null) {
                $fila['cod_registro'] = $this->exacto($fila['cod_registro'], $mapa);
            }
            $fila['descripcion'] = preg_replace_callback('/^\[([a-z_]+):([^\]]+)\]/', fn ($m) => '['.$m[1].':'.($mapa[$m[1]][$m[2]] ?? $m[2]).']', $fila['descripcion'] ?? '');
            if ($antes['descripcion'] === null) {
                $fila['descripcion'] = null;
            }
        }
        if ($tabla === 'activity_log' && $fila['properties'] !== null) {
            $props = json_decode($fila['properties'], true, 512, JSON_THROW_ON_ERROR);
            $contextos = ['usuario' => 'usuarios', 'actor' => 'usuarios', 'personal' => 'personal', 'residente' => 'residentes',
                'residentes' => 'residentes', 'version' => 'versiones_modelo_experto', 'incidente' => 'incidentes', 'receptor' => 'personal'];
            if ($antes['subject_type'] !== null) {
                $clase = Relation::getMorphedModel($antes['subject_type']) ?? $antes['subject_type'];
                if (is_a($clase, Model::class, true)) {
                    $contextos['codigo'] = (new $clase)->getTable();
                }
            }
            $transformar = function ($valor, ?string $clave = null) use (&$transformar, $mapa, $contextos) {
                if (is_array($valor)) {
                    foreach ($valor as $k => &$v) {
                        $v = $transformar($v, is_int($k) ? $clave : (string) $k);
                    }

                    return $valor;
                }
                if (! is_string($valor)) {
                    return $valor;
                }
                if (isset($contextos[$clave])) {
                    return $mapa[$contextos[$clave]][$valor] ?? $valor;
                }

                // Solo atributos de identidad: jamás observación, descripción o texto clínico.
                return $clave !== null && str_starts_with($clave, 'cod_') ? $this->exacto($valor, $mapa) : $valor;
            };
            $cambiado = $transformar($props);
            if ($cambiado !== $props) {
                $fila['properties'] = json_encode($cambiado, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            }
        }
        if ($tabla === 'evidencias_evaluacion' && $fila['cod_registro_fuente'] !== null) {
            $fuente = $db->table('mapeos_variables_fuente as m')->join('fuentes_datos_expertas as f', 'f.cod_fuente_dato_experta', '=', 'm.cod_fuente_dato_experta')->where('m.cod_mapeo_variable_fuente', $antes['cod_mapeo_variable_fuente'])->value('f.tabla_raiz');
            if (! isset($mapa[$fuente][$fila['cod_registro_fuente']])) {
                throw new RuntimeException('La evidencia tiene una fuente no resoluble.');
            }
            $fila['cod_registro_fuente'] = $mapa[$fuente][$fila['cod_registro_fuente']];
        }
        if ($tabla === 'documentos_clinicos') {
            $fila['ruta_archivo'] = preg_replace_callback('~(clinical/residents/|documentos-clinicos/)([^/]+)~', fn ($m) => $m[1].($mapa['residentes'][$m[2]] ?? $m[2]), $fila['ruta_archivo']);
            $fila['ruta_archivo'] = preg_replace_callback('~(/wounds/|/curations/)([^/]+)~', fn ($m) => $m[1].($mapa[$m[1] === '/wounds/' ? 'heridas' : 'curaciones_herida'][$m[2]] ?? $m[2]), $fila['ruta_archivo']);
        }
    }

    private function exacto(string $valor, array $mapa): string
    {
        $coincidencias = [];
        foreach ($mapa as $valores) {
            if (isset($valores[$valor])) {
                $coincidencias[] = $valores[$valor];
            }
        }
        if (count(array_unique($coincidencias)) > 1) {
            throw new RuntimeException('Referencia sin tabla destino y código ambiguo; requiere resolver el contexto.');
        }

        return $coincidencias[0] ?? $valor;
    }

    private function contratoRelacional(array $restricciones): array
    {
        return array_map(static function (object $restriccion): array {
            $contrato = (array) $restriccion;
            // Solo RESTRICT se recrea: su OID técnico cambia, su contrato debe ser idéntico.
            if (str_contains($contrato['definicion'], 'ON UPDATE RESTRICT')) unset($contrato['oid']);
            return $contrato;
        }, $restricciones);
    }

    private function restricciones(Connection $db): array
    {
        return $db->select("SELECT t.relname AS tabla,c.conname AS nombre,c.oid,c.condeferrable AS diferible,c.condeferred AS diferida,c.convalidated AS validada,c.conenforced AS exigida,c.conkey::text AS columnas,c.confkey::text AS referencias,pg_get_constraintdef(c.oid) AS definicion FROM pg_constraint c JOIN pg_class t ON t.oid=c.conrelid JOIN pg_namespace n ON n.oid=t.relnamespace WHERE c.contype='f' AND n.nspname='public' ORDER BY tabla,nombre");
    }

    private function huella(array $filas): array
    {
        $hashes = [];
        foreach ($filas as $fila) {
            if (isset($fila['properties']) && is_string($fila['properties'])) {
                $props = json_decode($fila['properties'], true, 512, JSON_THROW_ON_ERROR);
                $ordenar = function (array $valores) use (&$ordenar): array {
                    if (! array_is_list($valores)) {
                        ksort($valores);
                    }
                    foreach ($valores as &$valor) {
                        if (is_array($valor)) {
                            $valor = $ordenar($valor);
                        }
                    }

                    return $valores;
                };
                $fila['properties'] = serialize($ordenar($props));
            }
            ksort($fila);
            $hashes[] = hash('sha256', serialize($fila));
        }
        sort($hashes);

        return ['filas' => count($filas), 'sha256' => hash('sha256', implode('', $hashes))];
    }

    private function nombre(string $nombre): string
    {
        if (! preg_match('/^[a-z_][a-z0-9_]*$/', $nombre)) {
            throw new RuntimeException('Identificador SQL inesperado.');
        }

        return '"'.$nombre.'"';
    }

    private function guardarPrivado(string $archivo, array $contenido): void
    {
        $bytes = json_encode($contenido, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $temporal = $archivo.'.tmp';
        if (file_exists($archivo) || file_put_contents($temporal, $bytes, LOCK_EX) !== strlen($bytes)
            || hash_file('sha256', $temporal) !== hash('sha256', $bytes) || ! rename($temporal, $archivo)) {
            throw new RuntimeException('No se pudo conservar y verificar el manifiesto privado de conversión.');
        }
    }

    private function verificarRelaciones(Connection $db): void
    {
        foreach ($db->getSchemaBuilder()->getTables() as $tabla) {
            if (($tabla['schema'] ?? 'public') !== 'public') {
                continue;
            }
            foreach ($db->getSchemaBuilder()->getForeignKeys($tabla['name']) as $fk) {
                $noNulos = [];
                $igualdades = [];
                foreach ($fk['columns'] as $i => $campo) {
                    $noNulos[] = 't.'.$this->nombre($campo).' IS NOT NULL';
                    $igualdades[] = 't.'.$this->nombre($campo).' = r.'.$this->nombre($fk['foreign_columns'][$i]);
                }
                $regla = $db->selectOne('SELECT confmatchtype FROM pg_constraint WHERE conname=? AND conrelid=?::regclass', [$fk['name'], $tabla['name']])->confmatchtype;
                $condicion = implode($regla === 'f' ? ' OR ' : ' AND ', $noNulos);
                $invalida = $db->selectOne('SELECT EXISTS (SELECT 1 FROM '.$this->nombre($tabla['name']).' t WHERE ('.$condicion.') '
                    .'AND NOT EXISTS (SELECT 1 FROM '.$this->nombre($fk['foreign_table']).' r WHERE '.implode(' AND ', $igualdades).')) AS invalida');
                if ($invalida->invalida) {
                    throw new RuntimeException('Relación sin destino válido: '.$tabla['name'].'.'.$fk['name']);
                }
            }
        }
    }
}
