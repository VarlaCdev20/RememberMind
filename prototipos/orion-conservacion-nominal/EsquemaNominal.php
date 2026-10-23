<?php

declare(strict_types=1);

namespace OrionNominal;

use DomainException;

/** DDL de ensayo; no es una migración registrada ni puede instalarse en una base institucional. */
final class EsquemaNominal
{
    public static function tablas(array $mapa): array
    {
        $usos = [];
        $modos = [];
        $recorrer = static function (array $nodo) use (&$recorrer, &$usos, &$modos): void {
            if (in_array($nodo['clase'], ['coleccion', 'registro'], true)) {
                $usos[$nodo['modelo']][] = $nodo['identidad'];
                $modos[$nodo['modelo']][$nodo['identidad']] = $nodo['modo'] ?? 'lista';
            } elseif ($nodo['clase'] === 'objeto') {
                foreach ($nodo['campos'] as $hijo) {
                    $recorrer($hijo);
                }
            }
        };
        foreach ($mapa['modelos'] as $modelo) {
            $recorrer($modelo['estructura']);
        }
        $tablas = [];
        foreach ($mapa['modelos'] as $id => $modelo) {
            $tabla = $modelo['tabla'];
            $campos = ContratoNominal::campos($modelo['estructura'], $id);
            if (isset($tablas[$tabla]) && $tablas[$tabla]['campos'] !== $campos) {
                throw new DomainException('Dos modelos no comparten el mismo grano nominal: '.$tabla);
            }
            $tablas[$tabla]['campos'] = $campos;
            $tablas[$tabla]['modelos'][$id] = $modelo;
            $tablas[$tabla]['roles'][$id] = array_values(array_unique($usos[$id] ?? []));
            $tablas[$tabla]['modos'][$id] = $modos[$id] ?? [];
            if ($modelo['padre'] !== null) {
                $tablas[$tabla]['padres'][$modelo['fk_propietario']] = $mapa['modelos'][$modelo['padre']]['tabla'];
            }
        }

        return $tablas;
    }

    public static function ddl(array $mapa, string $motor): string
    {
        if (! in_array($motor, ['pgsql', 'sqlite'], true)) {
            throw new DomainException('Motor no verificado.');
        }
        $boolean = $motor === 'pgsql' ? 'BOOLEAN' : 'INTEGER';
        $verdad = $motor === 'pgsql' ? 'TRUE' : '1';
        $falso = $motor === 'pgsql' ? 'FALSE' : '0';
        $tablas = self::tablas($mapa);
        $contrato = $mapa['contrato_sql'] ?? ContratoNominal::VERSION;
        if (! preg_match('/^[A-Z0-9_-]{1,40}$/D', $contrato)) {
            throw new DomainException('Identidad SQL no declarada.');
        }
        $sql = ['CREATE TABLE releases_nominales (
            huella_release CHAR(64) PRIMARY KEY,
            cod_release_motor VARCHAR(20) NOT NULL,
            huella_zip CHAR(64) NOT NULL,
            huella_adaptador CHAR(64) NOT NULL,
            revision_base CHAR(40) NOT NULL,
            php_version VARCHAR(30) NOT NULL,
            cantidad_archivos BIGINT NOT NULL CHECK(cantidad_archivos>0),
            huella_src CHAR(64) NOT NULL,
            huella_runtime_zip CHAR(64) NOT NULL,
            huella_php_ejecutable CHAR(64) NOT NULL
        )', "CREATE TABLE evaluaciones_nominales (
            cod_evaluacion_experta VARCHAR(20) PRIMARY KEY,
            solicitud_uuid VARCHAR(36) NOT NULL UNIQUE,
            cod_residente VARCHAR(20) NOT NULL REFERENCES residentes(cod_residente) ON DELETE RESTRICT,
            cod_usuario VARCHAR(20) NOT NULL REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT,
            huella_entrada CHAR(64) NOT NULL CHECK(length(huella_entrada)=64),
            huella_compuesta CHAR(64) NOT NULL CHECK(length(huella_compuesta)=64),
            huella_release CHAR(64) NOT NULL REFERENCES releases_nominales(huella_release) ON DELETE RESTRICT,
            contrato VARCHAR(40) NOT NULL CHECK(contrato='$contrato'),
            estado VARCHAR(8) NOT NULL CHECK(estado IN ('ABIERTA','CERRADA'))
        )"];
        // SQLite acepta FK hacia tablas aún no creadas; PostgreSQL las incorpora después de todas las relaciones.
        $posteriores = [];
        foreach ($tablas as $tabla => $def) {
            $cols = ['cod_fila VARCHAR(20) PRIMARY KEY', 'cod_evaluacion_experta VARCHAR(20) NOT NULL',
                'UNIQUE(cod_evaluacion_experta,cod_fila)',
                'FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT'];
            if ($tabla === $mapa['modelos'][$mapa['raiz']]['tabla']) {
                $cols[] = 'UNIQUE(cod_evaluacion_experta)';
            } else {
                $cols[] = 'posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0)';
                $cols[] = 'rol_coleccion VARCHAR(32) NOT NULL';
                $cols[] = "clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING'))";
                $cols[] = 'clave_texto TEXT';
                $cols[] = "CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL))";
                foreach ($def['padres'] as $fk => $padre) {
                    $cols[] = "$fk VARCHAR(20)";
                    $cols[] = "UNIQUE($fk,rol_coleccion,posicion_coleccion)";
                    $constraint = "FOREIGN KEY(cod_evaluacion_experta,$fk) REFERENCES $padre(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT";
                    if ($motor === 'sqlite') {
                        $cols[] = $constraint;
                    } else {
                        $posteriores[] = "ALTER TABLE $tabla ADD $constraint";
                    }
                }
                $contarPadres = implode('+', array_map(static fn ($fk) => "CASE WHEN $fk IS NULL THEN 0 ELSE 1 END", array_keys($def['padres'])));
                $cols[] = "CHECK(($contarPadres)=1)";
                $variantes = [];
                $claves = [];
                foreach ($def['modelos'] as $id => $modelo) {
                    $roles = $def['roles'][$id];
                    if ($roles === []) {
                        throw new DomainException('Modelo hijo sin rol conocido: '.$id);
                    }
                    $variantes[] = "({$modelo['fk_propietario']} IS NOT NULL AND rol_coleccion IN ('".implode("','", $roles)."'))";
                    foreach ($def['modos'][$id] as $rol => $modo) {
                        $claves[] = "(rol_coleccion='$rol' AND clave_tipo ".($modo === 'mapa' ? "IN ('INT','STRING')" : "='NULL'").')';
                    }
                }
                $cols[] = 'CHECK('.implode(' OR ', array_unique($variantes)).')';
                $cols[] = 'CHECK('.implode(' OR ', array_unique($claves)).')';
            }
            foreach ($def['campos'] as $ruta => $campo) {
                $col = $campo['columna'];
                if ($campo['clase'] !== 'escalar') {
                    $cols[] = "{$col}__estado VARCHAR(8) NOT NULL CHECK({$col}__estado IN ('AUSENTE','NULL','PRESENTE'))";
                    if ($campo['clase'] === 'coleccion') {
                        $cols[] = "{$col}__cantidad BIGINT NOT NULL CHECK({$col}__cantidad>=0)";
                        $cols[] = "CHECK({$col}__estado='PRESENTE' OR {$col}__cantidad=0)";
                    }

                    continue;
                }
                $cols[] = "{$col}__presente $boolean NOT NULL";
                if ($motor === 'sqlite') {
                    $cols[] = "CHECK({$col}__presente IN (0,1))";
                }
                $tipos = $campo['tipos'];
                if (count($tipos) === 1) {
                    $tipo = $tipos[0];
                    $sqlTipo = match ($tipo) {
                        'STRING' => 'TEXT', 'INT' => 'BIGINT', 'BOOL' => $boolean,
                        'FLOAT' => $motor === 'pgsql' ? 'DOUBLE PRECISION' : 'REAL'
                    };
                    $cols[] = "$col $sqlTipo";
                    $cols[] = "CHECK({$col}__presente=$verdad OR $col IS NULL)";
                    if ($tipo === 'BOOL' && $motor === 'sqlite') {
                        $cols[] = "CHECK($col IS NULL OR $col IN (0,1))";
                    }
                    if ($tipo === 'FLOAT') {
                        $cols[] = "{$col}__cero_negativo $boolean NOT NULL DEFAULT $falso";
                        $cols[] = "CHECK({$col}__cero_negativo=$falso OR $col=0)";
                    }
                } else {
                    $cols[] = "{$col}__tipo VARCHAR(6) NOT NULL CHECK({$col}__tipo IN ('NULL','".implode("','", $tipos)."'))";
                    $cols[] = "CHECK({$col}__presente=$verdad OR {$col}__tipo='NULL')";
                    $alternativas = ["({$col}__tipo='NULL'".implode('', array_map(static fn ($t) => ' AND '.$col.'_'.self::sufijo($t).' IS NULL', $tipos)).')'];
                    foreach ($tipos as $tipo) {
                        $nombre = $col.'_'.self::sufijo($tipo);
                        $sqlTipo = match ($tipo) {
                            'STRING' => 'TEXT', 'INT' => 'BIGINT', 'FLOAT' => $motor === 'pgsql' ? 'DOUBLE PRECISION' : 'REAL', 'BOOL' => $boolean
                        };
                        $cols[] = "$nombre $sqlTipo";
                        $condicion = "({$col}__tipo='$tipo' AND $nombre IS NOT NULL";
                        foreach ($tipos as $otro) {
                            if ($otro !== $tipo) {
                                $condicion .= ' AND '.$col.'_'.self::sufijo($otro).' IS NULL';
                            }
                        }
                        $alternativas[] = $condicion.')';
                    }
                    $cols[] = 'CHECK('.implode(' OR ', $alternativas).')';
                    if (in_array('FLOAT', $tipos, true)) {
                        // El signo de -0.0 no sobrevive a todos los drivers SQLite; es parte del valor original.
                        $cols[] = "{$col}__cero_negativo $boolean NOT NULL DEFAULT $falso";
                        $cols[] = "CHECK({$col}__cero_negativo=$falso OR ({$col}__tipo='FLOAT' AND {$col}_real=0))";
                    }
                }
            }
            foreach ($def['campos'] as $ruta => $campo) {
                if ($campo['clase'] !== 'objeto') {
                    continue;
                }
                $hijas = [];
                foreach ($def['campos'] as $rutaHija => $hija) {
                    if ($rutaHija !== '' && substr($rutaHija, 0, (int) strrpos($rutaHija, '/')) === $ruta) {
                        $hijas[] = $hija['columna'].($hija['clase'] === 'escalar' ? "__presente=$falso" : "__estado='AUSENTE'");
                    }
                }
                if ($hijas !== []) {
                    $cols[] = "CHECK({$campo['columna']}__estado='PRESENTE' OR (".implode(' AND ', $hijas).'))';
                }
            }
            $restricciones = array_values(array_filter($cols, static fn ($col) => preg_match('/^(UNIQUE|FOREIGN KEY|CHECK)\(/', $col)));
            $columnas = array_values(array_filter($cols, static fn ($col) => ! preg_match('/^(UNIQUE|FOREIGN KEY|CHECK)\(/', $col)));
            $sql[] = "CREATE TABLE $tabla (\n    ".implode(",\n    ", array_merge($columnas, $restricciones))."\n)";
        }
        $sql = array_merge($sql, $posteriores);
        foreach (['cod_residente', 'cod_usuario', 'huella_release'] as $fk) {
            $sql[] = "CREATE INDEX evaluaciones_nominales_{$fk}_i ON evaluaciones_nominales($fk)";
        }
        foreach ($tablas as $tabla => $def) {
            $sql[] = "CREATE INDEX {$tabla}_evaluacion_i ON $tabla(cod_evaluacion_experta)";
            foreach ($def['padres'] ?? [] as $fk => $padre) {
                $nombreIndice = substr($tabla, 0, 29).'_'.substr(hash('sha256', $tabla.$fk), 0, 10).'_k';
                $sql[] = "CREATE UNIQUE INDEX $nombreIndice ON $tabla($fk,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL'";
            }
        }
        $reglasCierre = self::reglasCierre($mapa);
        if ($motor === 'sqlite') {
            $sql[] = "CREATE TRIGGER nominal_insertar BEFORE INSERT ON evaluaciones_nominales
                WHEN NEW.estado<>'ABIERTA'
                BEGIN SELECT RAISE(ABORT,'Debe conservar antes de cerrar'); END";
            foreach (['UPDATE', 'DELETE'] as $op) {
                $sql[] = 'CREATE TRIGGER release_nominal_'.strtolower($op)." BEFORE $op ON releases_nominales
                    BEGIN SELECT RAISE(ABORT,'Release inmutable'); END";
            }
            foreach ($tablas as $tabla => $def) {
                foreach (['INSERT' => 'NEW', 'UPDATE' => 'OLD', 'DELETE' => 'OLD'] as $op => $ref) {
                    $extra = $op === 'UPDATE' ? " OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'" : '';
                    $sql[] = "CREATE TRIGGER {$tabla}_".strtolower($op)." BEFORE $op ON $tabla
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=$ref.cod_evaluacion_experta)='CERRADA'$extra
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END";
                }
            }
            $sql[] = "CREATE TRIGGER nominal_validar_cierre BEFORE UPDATE ON evaluaciones_nominales
                WHEN OLD.estado='CERRADA' OR NEW.estado<>'CERRADA' OR ".self::identidadModificada('IS NOT')."
                BEGIN SELECT RAISE(ABORT,'Transición histórica inválida'); END";
            $sql[] = "CREATE TRIGGER nominal_sin_borrado BEFORE DELETE ON evaluaciones_nominales
                BEGIN SELECT RAISE(ABORT,'No se elimina historia'); END";
            $sql[] = "CREATE TRIGGER nominal_cierre_completo BEFORE UPDATE ON evaluaciones_nominales
                WHEN NEW.estado='CERRADA' AND (".implode(' OR ', $reglasCierre).")
                BEGIN SELECT RAISE(ABORT,'Conservación incompleta'); END";
        } else {
            $sql[] = "CREATE OR REPLACE FUNCTION nominal_guardar_hija() RETURNS trigger LANGUAGE plpgsql AS \$\$
                DECLARE id VARCHAR(20); estado_actual TEXT;
                BEGIN
                    id:=CASE WHEN TG_OP='DELETE' THEN OLD.cod_evaluacion_experta ELSE NEW.cod_evaluacion_experta END;
                    PERFORM pg_advisory_xact_lock(hashtextextended(id,1));
                    SELECT estado INTO estado_actual FROM evaluaciones_nominales WHERE cod_evaluacion_experta=id FOR UPDATE;
                    IF estado_actual='CERRADA' THEN RAISE EXCEPTION 'Evaluación cerrada' USING ERRCODE='55000'; END IF;
                    IF TG_OP='UPDATE' AND OLD.cod_evaluacion_experta<>NEW.cod_evaluacion_experta THEN
                        SELECT estado INTO estado_actual FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta FOR UPDATE;
                        IF estado_actual='CERRADA' THEN RAISE EXCEPTION 'Evaluación cerrada' USING ERRCODE='55000'; END IF;
                    END IF;
                    IF TG_OP='DELETE' THEN RETURN OLD; END IF; RETURN NEW;
                END \$\$";
            foreach ($tablas as $tabla => $def) {
                $sql[] = "CREATE TRIGGER {$tabla}_historia BEFORE INSERT OR UPDATE OR DELETE ON $tabla FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija()";
            }
            $sql[] = "CREATE OR REPLACE FUNCTION nominal_validar_cabecera() RETURNS trigger LANGUAGE plpgsql AS \$\$
                BEGIN
                    IF TG_OP='DELETE' THEN RAISE EXCEPTION 'No se elimina historia' USING ERRCODE='55000'; END IF;
                    IF OLD.estado='CERRADA' OR NEW.estado<>'CERRADA' OR ".self::identidadModificada('IS DISTINCT FROM')." THEN
                        RAISE EXCEPTION 'Transición histórica inválida' USING ERRCODE='55000'; END IF;
                    IF ".implode(' OR ', $reglasCierre)." THEN RAISE EXCEPTION 'Conservación incompleta' USING ERRCODE='23514'; END IF;
                    RETURN NEW;
                END \$\$";
            $sql[] = 'CREATE TRIGGER nominal_cabecera BEFORE UPDATE OR DELETE ON evaluaciones_nominales FOR EACH ROW EXECUTE FUNCTION nominal_validar_cabecera()';
            $sql[] = "CREATE OR REPLACE FUNCTION nominal_iniciar() RETURNS trigger LANGUAGE plpgsql AS \$\$
                BEGIN
                    PERFORM pg_advisory_xact_lock(hashtextextended(NEW.cod_evaluacion_experta,1));
                    IF NEW.estado<>'ABIERTA' THEN RAISE EXCEPTION 'Debe conservar antes de cerrar' USING ERRCODE='23514'; END IF;
                    RETURN NEW;
                END \$\$";
            $sql[] = 'CREATE TRIGGER nominal_insertar BEFORE INSERT ON evaluaciones_nominales FOR EACH ROW EXECUTE FUNCTION nominal_iniciar()';
            $sql[] = "CREATE OR REPLACE FUNCTION nominal_exigir_cerrada() RETURNS trigger LANGUAGE plpgsql AS \$\$
                BEGIN
                    IF (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)<>'CERRADA'
                        THEN RAISE EXCEPTION 'No se confirma una evaluación parcial' USING ERRCODE='23514'; END IF;
                    RETURN NULL;
                END \$\$";
            $sql[] = 'CREATE CONSTRAINT TRIGGER nominal_confirmar AFTER INSERT OR UPDATE ON evaluaciones_nominales
                DEFERRABLE INITIALLY DEFERRED FOR EACH ROW EXECUTE FUNCTION nominal_exigir_cerrada()';
            $sql[] = "CREATE OR REPLACE FUNCTION nominal_release_inmutable() RETURNS trigger LANGUAGE plpgsql AS \$\$
                BEGIN RAISE EXCEPTION 'Release inmutable' USING ERRCODE='55000'; END \$\$";
            $sql[] = 'CREATE TRIGGER nominal_release BEFORE UPDATE OR DELETE ON releases_nominales FOR EACH ROW EXECUTE FUNCTION nominal_release_inmutable()';
            $sql[] = "CREATE OR REPLACE FUNCTION nominal_sin_truncate() RETURNS trigger LANGUAGE plpgsql AS \$\$
                BEGIN RAISE EXCEPTION 'No se trunca historia' USING ERRCODE='55000'; END \$\$";
            foreach (array_merge(array_keys($tablas), ['evaluaciones_nominales', 'releases_nominales']) as $tabla) {
                $sql[] = "CREATE TRIGGER {$tabla}_sin_truncate BEFORE TRUNCATE ON $tabla FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate()";
            }
        }

        return implode(";\n\n", $sql).";\n";
    }

    /** Reversión exclusiva del laboratorio. No contiene retiros ni modificaciones del baseline. */
    public static function ddlReversion(array $mapa, string $motor): string
    {
        if (! in_array($motor, ['pgsql', 'sqlite'], true)) {
            throw new DomainException('Motor no verificado.');
        }
        $pendientes = self::tablas($mapa);
        $orden = ['releases_nominales', 'evaluaciones_nominales'];
        while ($pendientes !== []) {
            $antes = count($pendientes);
            foreach ($pendientes as $tabla => $def) {
                if (array_diff(array_values($def['padres'] ?? []), $orden) === []) {
                    $orden[] = $tabla;
                    unset($pendientes[$tabla]);
                }
            }
            if (count($pendientes) === $antes) {
                throw new DomainException('El grafo de propietarios contiene un ciclo sin reversión definida.');
            }
        }
        $sql = array_map(static fn ($tabla) => "DROP TABLE $tabla", array_reverse($orden));
        if ($motor === 'pgsql') {
            foreach (['nominal_guardar_hija', 'nominal_validar_cabecera', 'nominal_iniciar',
                'nominal_exigir_cerrada', 'nominal_release_inmutable', 'nominal_sin_truncate'] as $funcion) {
                $sql[] = "DROP FUNCTION $funcion()";
            }
        }

        return implode(";\n", $sql).";\n";
    }

    private static function identidadModificada(string $operador): string
    {
        return implode(' OR ', array_map(static fn ($c) => "NEW.$c $operador OLD.$c", ['cod_evaluacion_experta',
            'solicitud_uuid', 'cod_residente', 'cod_usuario', 'huella_entrada', 'huella_compuesta', 'huella_release', 'contrato']));
    }

    public static function sufijo(string $tipo): string
    {
        return match ($tipo) {
            'STRING' => 'texto', 'INT' => 'entero', 'FLOAT' => 'real', 'BOOL' => 'logico'
        };
    }

    /** La clausura SQL comprueba presencia y cardinalidad de todas las colecciones, no sólo un código final. */
    private static function reglasCierre(array $mapa): array
    {
        $raiz = $mapa['modelos'][$mapa['raiz']]['tabla'];
        $reglas = ["(SELECT count(*) FROM $raiz WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)<>1"];
        $tablas = self::tablas($mapa);
        $caminar = static function (array $nodo, string $ruta, string $tabla, string $modelo) use (&$caminar, &$reglas, $mapa, $tablas): void {
            if (in_array($nodo['clase'], ['coleccion', 'registro'], true)) {
                $hijo = $mapa['modelos'][$nodo['modelo']];
                $col = $nodo['columna'] ?? ContratoNominal::columna($ruta, $modelo);
                $cuenta = "(SELECT count(*) FROM {$hijo['tabla']} h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.{$hijo['fk_propietario']}=p.cod_fila AND h.rol_coleccion='{$nodo['identidad']}')";
                $esperado = $nodo['clase'] === 'coleccion' ? "p.{$col}__cantidad" : "CASE WHEN p.{$col}__estado='PRESENTE' THEN 1 ELSE 0 END";
                $roles = $tablas[$tabla]['roles'][$modelo];
                $filtro = $roles === [] ? '' : " AND p.rol_coleccion IN ('".implode("','", $roles)."')";
                $reglas[] = "EXISTS(SELECT 1 FROM $tabla p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta$filtro AND $cuenta<>($esperado))";
                $limites = "(SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>($esperado)-1 FROM {$hijo['tabla']} h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.{$hijo['fk_propietario']}=p.cod_fila AND h.rol_coleccion='{$nodo['identidad']}')";
                $reglas[] = "EXISTS(SELECT 1 FROM $tabla p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta$filtro AND ($esperado)>0 AND $limites)";
            } elseif ($nodo['clase'] === 'objeto') {
                foreach ($nodo['campos'] as $k => $hijo) {
                    $caminar($hijo, $ruta.'/'.$k, $tabla, $modelo);
                }
            }
        };
        foreach ($mapa['modelos'] as $id => $modelo) {
            $caminar($modelo['estructura'], '', $modelo['tabla'], $id);
        }

        return array_values(array_unique($reglas));
    }
}
