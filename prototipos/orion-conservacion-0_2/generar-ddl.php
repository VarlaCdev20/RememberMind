<?php

declare(strict_types=1);

/** Construye SQL ESTÁTICO para revisión/ensayo; jamás instala en la conexión institucional. */
$mapa = require __DIR__.'/mapa-cerrado.php';
$sql = <<<'SQL'
-- PROTOTIPO FASE 0.2. Sólo PostgreSQL DESECHABLE. No es migración institucional autorizada.
CREATE SCHEMA orion_f02;
CREATE TABLE orion_f02.releases_motor (
 cod_release_motor varchar(20) PRIMARY KEY,
 huella_manifiesto char(64) NOT NULL UNIQUE CHECK(huella_manifiesto ~ '^[a-f0-9]{64}$'),
 huella_zip char(64) NOT NULL CHECK(huella_zip ~ '^[a-f0-9]{64}$'),
 huella_adaptador char(64) NOT NULL CHECK(huella_adaptador ~ '^[a-f0-9]{64}$'),
 revision_base char(40) NOT NULL, php_version text NOT NULL,
 cantidad_archivos integer NOT NULL CHECK(cantidad_archivos>0), huella_src char(64) NOT NULL,
 huella_runtime_zip char(64) NOT NULL CHECK(huella_runtime_zip ~ '^[a-f0-9]{64}$'),
 huella_php_ejecutable char(64) NOT NULL CHECK(huella_php_ejecutable ~ '^[a-f0-9]{64}$'),
 registrado_en timestamptz NOT NULL DEFAULT clock_timestamp()
);
CREATE TABLE orion_f02.evaluaciones_compuestas (
 cod_evaluacion_experta varchar(20) PRIMARY KEY,
 id_evaluacion_dominio text NOT NULL UNIQUE,
 id_evaluacion_petersen text NOT NULL,
 cod_residente varchar(20) NOT NULL, cod_usuario_ejecucion varchar(20) NOT NULL,
 cod_release_motor varchar(20) NOT NULL REFERENCES orion_f02.releases_motor ON DELETE RESTRICT ON UPDATE NO ACTION,
 huella_compuesta char(64) NOT NULL CHECK(huella_compuesta ~ '^[a-f0-9]{64}$'),
 huella_instrumental char(64) NOT NULL CHECK(huella_instrumental ~ '^[a-f0-9]{64}$'),
 huella_petersen char(64) NOT NULL CHECK(huella_petersen ~ '^[a-f0-9]{64}$'),
 contrato_proyeccion text NOT NULL CHECK(contrato_proyeccion='PROYECCION-COMPUESTA-F02-1'),
 adquirido_en timestamptz NOT NULL DEFAULT clock_timestamp(),
 estado text NOT NULL DEFAULT 'ABIERTA' CHECK(estado IN ('ABIERTA','CERRADA')),
 UNIQUE(cod_evaluacion_experta,cod_residente)
);
CREATE INDEX evaluaciones_release_idx ON orion_f02.evaluaciones_compuestas(cod_release_motor);
CREATE FUNCTION orion_f02.inmutable() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN RAISE EXCEPTION 'El artefacto retenido es inmutable' USING ERRCODE='55000'; END $$;
CREATE TRIGGER release_inmutable BEFORE UPDATE OR DELETE ON orion_f02.releases_motor FOR EACH ROW EXECUTE FUNCTION orion_f02.inmutable();
CREATE FUNCTION orion_f02.guardar_coleccion() RETURNS trigger LANGUAGE plpgsql AS $$
DECLARE propietario varchar(20); cerrado text;
BEGIN
 IF TG_OP<>'INSERT' THEN
  SELECT estado INTO cerrado FROM orion_f02.evaluaciones_compuestas WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta FOR UPDATE;
  IF cerrado<>'ABIERTA' THEN RAISE EXCEPTION 'Historia cerrada: mutación prohibida' USING ERRCODE='55000'; END IF;
  IF TG_OP='UPDATE' AND (NEW.cod_evaluacion_experta<>OLD.cod_evaluacion_experta OR NEW.cod_fila<>OLD.cod_fila) THEN
   RAISE EXCEPTION 'No se reasigna identidad histórica' USING ERRCODE='23514';
  END IF;
 END IF;
 IF TG_OP='DELETE' THEN RETURN OLD; END IF;
 SELECT estado INTO cerrado FROM orion_f02.evaluaciones_compuestas WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta FOR UPDATE;
 IF cerrado IS DISTINCT FROM 'ABIERTA' THEN RAISE EXCEPTION 'Historia cerrada: incorporación prohibida' USING ERRCODE='55000'; END IF;
 RETURN NEW;
END $$;

SQL;
$tablas = [];
$colecciones = [];
function camposSql(array $n, string $p, array &$columnas, array &$checks, string $modelo): void
{
    global $mapa, $colecciones;
    $c = $mapa['columnas'][$p];
    if ($n['clase'] === 'escalar') {
        foreach (['_presente boolean NOT NULL', '_tipo text NOT NULL', '_texto text', '_entero bigint', '_real_ieee754 char(16)', '_logico boolean'] as $s) {
            $columnas[] = $c.$s;
        }
        $checks[] = "($c\_tipo IN ('NULL','STRING','INT','FLOAT','BOOL'))";
        $checks[] = "($c\_presente OR $c\_tipo='NULL')";
        $checks[] = "(($c\_tipo='NULL' AND num_nonnulls($c\_texto,$c\_entero,$c\_real_ieee754,$c\_logico)=0) OR ($c\_tipo<>'NULL' AND num_nonnulls($c\_texto,$c\_entero,$c\_real_ieee754,$c\_logico)=1 AND CASE $c\_tipo WHEN 'STRING' THEN $c\_texto IS NOT NULL WHEN 'INT' THEN $c\_entero IS NOT NULL WHEN 'FLOAT' THEN $c\_real_ieee754 ~ '^[a-f0-9]{16}$' WHEN 'BOOL' THEN $c\_logico IS NOT NULL ELSE false END))";

        return;
    }
    $columnas[] = $c."_estado text NOT NULL CHECK ($c\_estado IN ('AUSENTE','NULL','PRESENTE'))";
    if ($n['clase'] === 'coleccion') {
        $columnas[] = $c."_cantidad integer NOT NULL CHECK ($c\_cantidad>=0 AND ($c\_estado='PRESENTE' OR $c\_cantidad=0))";
        $colecciones[] = ['modelo' => $modelo, 'columna' => $c, 'hijo' => $n['modelo'], 'identidad' => $n['identidad'], 'modo' => $n['modo']];

        return;
    }
    $ausencias = [];
    foreach ($n['campos'] as $k => $h) {
        $hp = $p.'/'.$k;
        $hc = $mapa['columnas'][$hp];
        $ausencias[] = $h['clase'] === 'escalar' ? "NOT $hc\_presente" : "$hc\_estado='AUSENTE'";
        camposSql($h, $hp, $columnas, $checks, $modelo);
    }
    if ($ausencias !== []) {
        $checks[] = "($c\_estado='PRESENTE' OR (".implode(' AND ', $ausencias).'))';
    }
}
// Declarar primero todos los granos y después las FK, pues el mapa tiene referencias descendientes.
foreach ($mapa['modelos'] as $id => $m) {
    $cols = ['cod_fila varchar(20) PRIMARY KEY', 'cod_evaluacion_experta varchar(20) NOT NULL'];
    $checks = [];
    if ($m['padre'] !== null) {
        $cols = array_merge($cols, ['cod_propietario varchar(20) NOT NULL', 'orden integer NOT NULL CHECK(orden>=0)',
            'coleccion text NOT NULL', "clave_tipo text NOT NULL CHECK(clave_tipo IN ('NULL','STRING','INT'))", 'clave_texto text',
            "CHECK((clave_tipo='NULL')=(clave_texto IS NULL))"]);
    }
    camposSql($m['estructura'], '', $cols, $checks, $id);
    $cols[] = 'UNIQUE(cod_evaluacion_experta,cod_fila)';
    foreach ($checks as $check) {
        $cols[] = 'CHECK '.str_replace('\\_', '_', $check);
    }
    $body = str_replace('\\_', '_', implode(",\n ", $cols));
    $sql .= '-- Grano: '.$m['ruta']."\nCREATE TABLE orion_f02.".$m['tabla']." (\n $body\n);\n";
    $tablas[$id] = $m['tabla'];
}
foreach ($mapa['modelos'] as $id => $m) {
    $t = $m['tabla'];
    if ($m['padre'] === null) {
        $sql .= "ALTER TABLE orion_f02.$t ADD UNIQUE(cod_evaluacion_experta);\n";
    }
    $sql .= "ALTER TABLE orion_f02.$t ADD FOREIGN KEY(cod_evaluacion_experta) REFERENCES orion_f02.evaluaciones_compuestas ON DELETE RESTRICT ON UPDATE NO ACTION;\n";
    if ($m['padre'] !== null) {
        $padre = $tablas[$m['padre']];
        $sql .= "ALTER TABLE orion_f02.$t ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES orion_f02.$padre(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT ON UPDATE NO ACTION;\n";
        $sql .= "CREATE INDEX ON orion_f02.$t(cod_evaluacion_experta,cod_propietario);\n";
        $sql .= "CREATE UNIQUE INDEX ON orion_f02.$t(cod_propietario,coleccion,orden);\nCREATE UNIQUE INDEX ON orion_f02.$t(cod_propietario,coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';\n";
        $roles = array_filter($colecciones, fn ($c) => $c['hijo'] === $id);
        $exp = [];
        foreach ($roles as $r) {
            $exp[] = "(coleccion='".$r['identidad']."' AND clave_tipo".($r['modo'] === 'lista' ? "='NULL'" : "<>'NULL'").')';
        }
        $sql .= "ALTER TABLE orion_f02.$t ADD CHECK(".implode(' OR ', $exp).");\n";
    }
    $sql .= "CREATE INDEX ON orion_f02.$t(cod_evaluacion_experta);\nCREATE TRIGGER historia_cerrada BEFORE INSERT OR UPDATE OR DELETE ON orion_f02.$t FOR EACH ROW EXECUTE FUNCTION orion_f02.guardar_coleccion();\n";
    $sql .= "CREATE TRIGGER sin_truncate BEFORE TRUNCATE ON orion_f02.$t FOR EACH STATEMENT EXECUTE FUNCTION orion_f02.inmutable();\n";
}
$pk = MotorExpertoPk();
function MotorExpertoPk(): array
{
    return ['nodos_semanticos' => 'cod_nodo_semantico', 'relaciones_semanticas' => 'cod_relacion_semantica', 'dominios_valores_expertos' => 'cod_dominio_valores', 'valores_semanticos' => 'cod_valor_semantico', 'variables_expertas' => 'cod_variable_experta', 'fuentes_datos_expertas' => 'cod_fuente_dato_experta', 'mapeos_variables_fuente' => 'cod_mapeo_variable_fuente', 'mapeos_valores_fuente' => 'cod_mapeo_valor_fuente', 'criterios_dominios_resultado' => 'cod_criterio_dominio_resultado', 'reglas_expertas' => 'cod_regla_experta', 'condiciones_regla_experta' => 'cod_condicion_regla', 'consecuencias_regla_experta' => 'cod_consecuencia_regla'];
}
$foreign = [
    'relaciones_semanticas' => ['cod_nodo_origen' => 'nodos_semanticos', 'cod_nodo_destino' => 'nodos_semanticos'],
    'valores_semanticos' => ['cod_dominio_valores' => 'dominios_valores_expertos'],
    'variables_expertas' => ['cod_nodo_semantico' => 'nodos_semanticos', 'cod_dominio_valores' => 'dominios_valores_expertos', 'cod_nodo_propietario_primario' => 'nodos_semanticos'],
    'mapeos_variables_fuente' => ['cod_variable_experta' => 'variables_expertas', 'cod_fuente_dato_experta' => 'fuentes_datos_expertas'],
    'mapeos_valores_fuente' => ['cod_mapeo_variable_fuente' => 'mapeos_variables_fuente', 'cod_valor_semantico' => 'valores_semanticos'],
    'criterios_dominios_resultado' => ['cod_nodo_criterio' => 'nodos_semanticos', 'cod_dominio_valores' => 'dominios_valores_expertos'],
    'condiciones_regla_experta' => ['cod_regla_experta' => 'reglas_expertas', 'cod_variable_experta' => 'variables_expertas', 'cod_valor_semantico' => 'valores_semanticos'],
    'consecuencias_regla_experta' => ['cod_regla_experta' => 'reglas_expertas', 'cod_criterio_dominio_resultado' => 'criterios_dominios_resultado', 'cod_valor_semantico' => 'valores_semanticos'],
];
foreach ($pk as $nombre => $p) {
    $t='copia_'.$nombre;
    $sql .= "ALTER TABLE orion_f02.$t ADD CHECK($p\_presente AND $p\_tipo='STRING'), ADD UNIQUE(cod_evaluacion_experta,coleccion,$p\_texto);\n";
}
foreach ($foreign as $nombre => $cols) {
    $t = 'copia_'.$nombre;
    foreach ($cols as $c => $destino) {
        $p = $pk[$destino];
        $obligatoria = !in_array($c,['cod_nodo_propietario_primario'],true);
        $forma = $obligatoria ? "$c\_presente AND $c\_tipo='STRING'" : "(NOT $c\_presente OR $c\_tipo IN ('NULL','STRING'))";
        $sql .= "ALTER TABLE orion_f02.$t ADD CHECK($forma), ADD FOREIGN KEY(cod_evaluacion_experta,coleccion,$c\_texto) REFERENCES orion_f02.copia_$destino(cod_evaluacion_experta,coleccion,$p\_texto) ON DELETE RESTRICT ON UPDATE NO ACTION DEFERRABLE INITIALLY DEFERRED;\nCREATE INDEX ON orion_f02.$t(cod_evaluacion_experta,coleccion,$c\_texto);\n";
    }
}
$sql .= "ALTER TABLE orion_f02.copia_condiciones_regla_experta ADD UNIQUE(cod_evaluacion_experta,coleccion,cod_condicion_regla_texto,cod_regla_experta_texto);\n";
$sql .= <<<'SQL'
-- Índice relacional de pertenencia adquirida; no reemplaza entradas ni guarda una salida calculada.
CREATE TABLE orion_f02.anclajes_condiciones_adquiridas (
 cod_fila varchar(20) PRIMARY KEY, cod_evaluacion_experta varchar(20) NOT NULL,
 rama text NOT NULL CHECK(rama IN ('I','P')), cod_regla_experta varchar(20) NOT NULL,
 cod_condicion_regla varchar(20) NOT NULL,
 UNIQUE(cod_evaluacion_experta,rama,cod_condicion_regla),
 FOREIGN KEY(cod_evaluacion_experta,rama,cod_regla_experta) REFERENCES orion_f02.copia_reglas_expertas(cod_evaluacion_experta,coleccion,cod_regla_experta_texto) ON DELETE RESTRICT ON UPDATE NO ACTION DEFERRABLE INITIALLY DEFERRED,
 FOREIGN KEY(cod_evaluacion_experta,rama,cod_condicion_regla,cod_regla_experta) REFERENCES orion_f02.copia_condiciones_regla_experta(cod_evaluacion_experta,coleccion,cod_condicion_regla_texto,cod_regla_experta_texto) ON DELETE RESTRICT ON UPDATE NO ACTION DEFERRABLE INITIALLY DEFERRED
);
CREATE INDEX ON orion_f02.anclajes_condiciones_adquiridas(cod_evaluacion_experta,rama,cod_regla_experta);
CREATE INDEX ON orion_f02.anclajes_condiciones_adquiridas(cod_evaluacion_experta,rama,cod_condicion_regla,cod_regla_experta);
CREATE TRIGGER historia_cerrada BEFORE INSERT OR UPDATE OR DELETE ON orion_f02.anclajes_condiciones_adquiridas FOR EACH ROW EXECUTE FUNCTION orion_f02.guardar_coleccion();
CREATE TRIGGER sin_truncate BEFORE TRUNCATE ON orion_f02.anclajes_condiciones_adquiridas FOR EACH STATEMENT EXECUTE FUNCTION orion_f02.inmutable();
SQL;
// Residentes de fuentes USADAS y capturas propias. Los metadatos observados de pregunta/documento
// se copian como evidencia incluso cuando son incoherentes; no se reparan para forzar una FK.
foreach ($mapa['modelos'] as $id => $m) {
    $s = $m['estructura'];
    foreach (['cod_residente', 'residente'] as $f) {
        if (isset($s['campos'][$f]) && $s['campos'][$f]['clase'] === 'escalar') {
            $sql .= "ALTER TABLE orion_f02.{$m['tabla']} ADD CHECK($f\_presente AND $f\_tipo='STRING');\n";
            $sql .= "ALTER TABLE orion_f02.{$m['tabla']} ADD FOREIGN KEY(cod_evaluacion_experta,$f\_texto) REFERENCES orion_f02.evaluaciones_compuestas(cod_evaluacion_experta,cod_residente) ON DELETE RESTRICT ON UPDATE NO ACTION;\nCREATE INDEX ON orion_f02.{$m['tabla']}(cod_evaluacion_experta,$f\_texto);\n";
        }
    }
}
$sql .= <<<'SQL'
CREATE FUNCTION orion_f02.cerrar_evaluacion() RETURNS trigger LANGUAGE plpgsql AS $$
DECLARE fila record; cantidad bigint; primero bigint; ultimo bigint; entrada record;
BEGIN
 IF TG_OP='DELETE' THEN RAISE EXCEPTION 'No se borra una evaluación' USING ERRCODE='55000'; END IF;
 IF OLD.estado<>'ABIERTA' OR NEW.estado<>'CERRADA' THEN RAISE EXCEPTION 'Transición de cierre inválida' USING ERRCODE='55000'; END IF;
 NEW.estado:='ABIERTA';
 IF NEW IS DISTINCT FROM OLD THEN RAISE EXCEPTION 'El cierre no reescribe la cabecera' USING ERRCODE='55000'; END IF;
 NEW.estado:='CERRADA';
SQL;
foreach ($colecciones as $c) {
    $parent = $tablas[$c['modelo']];
    $child = $tablas[$c['hijo']];
    $col = $c['columna'];
    $role = $c['identidad'];
    $sql .= "FOR fila IN SELECT cod_fila,$col\_cantidad AS esperada FROM orion_f02.$parent WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta LOOP\n SELECT count(*),min(orden),max(orden) INTO cantidad,primero,ultimo FROM orion_f02.$child WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta AND cod_propietario=fila.cod_fila AND coleccion='$role';\n IF cantidad<>fila.esperada OR (cantidad>0 AND (primero<>0 OR ultimo<>cantidad-1)) THEN RAISE EXCEPTION 'Colección $child incompleta o discontinua' USING ERRCODE='23514'; END IF; END LOOP;\n";
}
$root = $tablas[$mapa['raiz']];
$c = fn (string $p): string => $mapa['columnas'][$p];
$sql .= "SELECT * INTO entrada FROM orion_f02.$root WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta;\n IF NOT FOUND THEN RAISE EXCEPTION 'Faltan entradas' USING ERRCODE='23514'; END IF;\n";
foreach (['instrumental', 'petersen'] as $rama) {
    foreach (['residente' => 'cod_residente', 'usuario_ejecucion' => 'cod_usuario_ejecucion', 'evaluacion' => 'id_evaluacion_dominio'] as $dato => $meta) {
        $col = $c('/'.$rama.'/contexto/'.$dato);
        if ($dato==='evaluacion' && $rama==='petersen') {
            $meta='id_evaluacion_petersen';
        }
        $sql .= "IF entrada.$col\_texto IS DISTINCT FROM NEW.$meta THEN RAISE EXCEPTION 'Contexto $dato cruzado' USING ERRCODE='23514'; END IF;\n";
    }
}
foreach ($pk as $tabla=>$p) {
    $model=$mapa['modelos']['k_'.$tabla];
    if (!isset($model['estructura']['campos']['cod_version_modelo'])) {
        continue;
    }
    $vi=$c('/instrumental/conocimiento/version');
    $vp=$c('/petersen/gobierno/conocimiento/version');
    $sql .= "IF EXISTS(SELECT 1 FROM orion_f02.copia_$tabla k WHERE k.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (NOT k.cod_version_modelo_presente OR k.cod_version_modelo_tipo<>'STRING' OR k.cod_version_modelo_texto IS DISTINCT FROM CASE k.coleccion WHEN 'I' THEN entrada.$vi\_texto WHEN 'P' THEN entrada.$vp\_texto END)) THEN RAISE EXCEPTION 'Versión cruzada en $tabla' USING ERRCODE='23514'; END IF;\n";
}
$sql .= <<<'SQL'
 IF EXISTS(SELECT 1 FROM orion_f02.copia_condiciones_regla_experta c WHERE c.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND NOT EXISTS(
  SELECT 1 FROM orion_f02.anclajes_condiciones_adquiridas a WHERE a.cod_evaluacion_experta=c.cod_evaluacion_experta AND a.rama=c.coleccion AND a.cod_condicion_regla=c.cod_condicion_regla_texto AND a.cod_regla_experta=c.cod_regla_experta_texto))
 THEN RAISE EXCEPTION 'Falta anclaje de condición adquirida' USING ERRCODE='23514'; END IF;
 RETURN NEW;
END $$;
CREATE TRIGGER cierre_inmutable BEFORE UPDATE OR DELETE ON orion_f02.evaluaciones_compuestas FOR EACH ROW EXECUTE FUNCTION orion_f02.cerrar_evaluacion();
CREATE FUNCTION orion_f02.exigir_cierre() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
 IF EXISTS(SELECT 1 FROM orion_f02.evaluaciones_compuestas WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta AND estado<>'CERRADA') THEN
  RAISE EXCEPTION 'No se confirma una evaluación parcial' USING ERRCODE='23514';
 END IF;
 RETURN NULL;
END $$;
CREATE CONSTRAINT TRIGGER confirma_completo AFTER INSERT OR UPDATE ON orion_f02.evaluaciones_compuestas DEFERRABLE INITIALLY DEFERRED FOR EACH ROW EXECUTE FUNCTION orion_f02.exigir_cierre();
CREATE TRIGGER sin_truncate BEFORE TRUNCATE ON orion_f02.evaluaciones_compuestas FOR EACH STATEMENT EXECUTE FUNCTION orion_f02.inmutable();
CREATE TRIGGER sin_truncate BEFORE TRUNCATE ON orion_f02.releases_motor FOR EACH STATEMENT EXECUTE FUNCTION orion_f02.inmutable();
SQL;
$sql = str_replace('\\_', '_', $sql);
file_put_contents(__DIR__.'/esquema.up.sql', $sql);
$down = "-- Sólo rollback vacío; nunca destruye historia para permitir un down().\nDO $$ BEGIN IF EXISTS(SELECT 1 FROM orion_f02.evaluaciones_compuestas) THEN RAISE EXCEPTION 'Rollback bloqueado: conservar y exportar historia' USING ERRCODE='55000'; END IF; END $$;\n";
foreach (array_reverse($tablas) as $t) {
    // Las FK diferidas cruzadas se retiran por esquema exclusivo sólo tras verificar vacío.
}
$down .= 'DROP SCHEMA orion_f02 CASCADE;';
file_put_contents(__DIR__.'/esquema.down.sql', $down);
echo (count($tablas) + 3)." tablas expertas de ensayo; DDL estático generado.\n";
