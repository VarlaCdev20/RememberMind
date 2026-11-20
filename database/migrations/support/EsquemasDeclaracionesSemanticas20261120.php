<?php

/** Contrato fechado del nodo y sus referencias, sin filas de conocimiento. */
return array (
  'sqlite' =>
  array (
    'nodos_semanticos' =>
    array (
      'crear' => 'CREATE TABLE "nodos_semanticos" ("cod_nodo_semantico" varchar not null, "cod_version_modelo" varchar not null, "codigo_semantico" varchar not null, "nombre" varchar not null, "tipo_nodo" varchar not null, "definicion" text not null, "estado" varchar not null, "fecha_hora_creacion" datetime not null, "cod_usuario_creacion" varchar not null, "observacion" text, "cod_variable_experta" varchar, "cod_nodo_propietario_primario" varchar, "cod_dominio_valores" varchar, "tipo_semantico" varchar, "papel_inferencial" varchar, "estado_variable" varchar, "fecha_hora_creacion_variable" datetime, "cod_usuario_creacion_variable" varchar, "observacion_variable" text, cod_criterio_dominio_resultado varchar(20), criterio_cod_dominio_valores varchar(20) REFERENCES dominios_valores_expertos(cod_dominio_valores) ON DELETE RESTRICT, criterio_cod_usuario_creacion varchar(20) REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT, criterio_estado varchar(20), criterio_fecha_hora_creacion timestamp(0), criterio_observacion text, foreign key("cod_dominio_valores", "cod_version_modelo") references dominios_valores_expertos("cod_dominio_valores", "cod_version_modelo") on delete restrict on update no action, foreign key("cod_nodo_propietario_primario", "cod_version_modelo") references nodos_semanticos("cod_nodo_semantico", "cod_version_modelo") on delete restrict on update no action, foreign key("cod_usuario_creacion") references usuarios("cod_usuario") on delete restrict on update no action, foreign key("cod_version_modelo") references versiones_modelo_experto("cod_version_modelo") on delete restrict on update no action, foreign key("cod_nodo_propietario_primario") references nodos_semanticos("cod_nodo_semantico") on delete restrict on update no action, foreign key("cod_dominio_valores") references dominios_valores_expertos("cod_dominio_valores") on delete restrict on update no action, foreign key("cod_usuario_creacion_variable") references "usuarios"("cod_usuario") on delete restrict on update no action, primary key ("cod_nodo_semantico"))',
      'indices_sql' =>
      array (
        0 =>
        array (
          'name' => 'b4_dominio_f_i',
          'sql' => 'CREATE INDEX "b4_dominio_f_i" on "nodos_semanticos" ("cod_dominio_valores")',
        ),
        1 =>
        array (
          'name' => 'b4_dominio_version_f_i',
          'sql' => 'CREATE INDEX "b4_dominio_version_f_i" on "nodos_semanticos" ("cod_dominio_valores", "cod_version_modelo")',
        ),
        2 =>
        array (
          'name' => 'b4_propietario_f_i',
          'sql' => 'CREATE INDEX "b4_propietario_f_i" on "nodos_semanticos" ("cod_nodo_propietario_primario")',
        ),
        3 =>
        array (
          'name' => 'b4_propietario_version_f_i',
          'sql' => 'CREATE INDEX "b4_propietario_version_f_i" on "nodos_semanticos" ("cod_nodo_propietario_primario", "cod_version_modelo")',
        ),
        4 =>
        array (
          'name' => 'b4_variable_autor_f_i',
          'sql' => 'CREATE INDEX "b4_variable_autor_f_i" on "nodos_semanticos" ("cod_usuario_creacion_variable")',
        ),
        5 =>
        array (
          'name' => 'b4_variable_u',
          'sql' => 'CREATE UNIQUE INDEX "b4_variable_u" on "nodos_semanticos" ("cod_variable_experta")',
        ),
        6 =>
        array (
          'name' => 'b4_variable_version_u',
          'sql' => 'CREATE UNIQUE INDEX "b4_variable_version_u" on "nodos_semanticos" ("cod_variable_experta", "cod_version_modelo")',
        ),
        7 =>
        array (
          'name' => 'nodo_criterio_resultado_dominio_i',
          'sql' => 'CREATE INDEX nodo_criterio_resultado_dominio_i ON nodos_semanticos (criterio_cod_dominio_valores)',
        ),
        8 =>
        array (
          'name' => 'nodo_criterio_resultado_estado_i',
          'sql' => 'CREATE INDEX nodo_criterio_resultado_estado_i ON nodos_semanticos (criterio_estado)',
        ),
        9 =>
        array (
          'name' => 'nodo_criterio_resultado_u',
          'sql' => 'CREATE UNIQUE INDEX nodo_criterio_resultado_u ON nodos_semanticos (cod_criterio_dominio_resultado)',
        ),
        10 =>
        array (
          'name' => 'nodo_criterio_resultado_usuario_i',
          'sql' => 'CREATE INDEX nodo_criterio_resultado_usuario_i ON nodos_semanticos (criterio_cod_usuario_creacion)',
        ),
        11 =>
        array (
          'name' => 'se02_i1',
          'sql' => 'CREATE INDEX "se02_i1" on "nodos_semanticos" ("tipo_nodo")',
        ),
        12 =>
        array (
          'name' => 'se02_i2',
          'sql' => 'CREATE INDEX "se02_i2" on "nodos_semanticos" ("cod_usuario_creacion")',
        ),
        13 =>
        array (
          'name' => 'se02_u1',
          'sql' => 'CREATE UNIQUE INDEX "se02_u1" on "nodos_semanticos" ("cod_version_modelo", "codigo_semantico")',
        ),
        14 =>
        array (
          'name' => 'se02_u2',
          'sql' => 'CREATE UNIQUE INDEX "se02_u2" on "nodos_semanticos" ("cod_nodo_semantico", "cod_version_modelo")',
        ),
      ),
      'columnas' =>
      array (
        0 =>
        array (
          'name' => 'cod_nodo_semantico',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        1 =>
        array (
          'name' => 'cod_version_modelo',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        2 =>
        array (
          'name' => 'codigo_semantico',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        3 =>
        array (
          'name' => 'nombre',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        4 =>
        array (
          'name' => 'tipo_nodo',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        5 =>
        array (
          'name' => 'definicion',
          'type_name' => 'text',
          'type' => 'text',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        6 =>
        array (
          'name' => 'estado',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        7 =>
        array (
          'name' => 'fecha_hora_creacion',
          'type_name' => 'datetime',
          'type' => 'datetime',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        8 =>
        array (
          'name' => 'cod_usuario_creacion',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        9 =>
        array (
          'name' => 'observacion',
          'type_name' => 'text',
          'type' => 'text',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        10 =>
        array (
          'name' => 'cod_variable_experta',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        11 =>
        array (
          'name' => 'cod_nodo_propietario_primario',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        12 =>
        array (
          'name' => 'cod_dominio_valores',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        13 =>
        array (
          'name' => 'tipo_semantico',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        14 =>
        array (
          'name' => 'papel_inferencial',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        15 =>
        array (
          'name' => 'estado_variable',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        16 =>
        array (
          'name' => 'fecha_hora_creacion_variable',
          'type_name' => 'datetime',
          'type' => 'datetime',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        17 =>
        array (
          'name' => 'cod_usuario_creacion_variable',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        18 =>
        array (
          'name' => 'observacion_variable',
          'type_name' => 'text',
          'type' => 'text',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        19 =>
        array (
          'name' => 'cod_criterio_dominio_resultado',
          'type_name' => 'varchar',
          'type' => 'varchar(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        20 =>
        array (
          'name' => 'criterio_cod_dominio_valores',
          'type_name' => 'varchar',
          'type' => 'varchar(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        21 =>
        array (
          'name' => 'criterio_cod_usuario_creacion',
          'type_name' => 'varchar',
          'type' => 'varchar(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        22 =>
        array (
          'name' => 'criterio_estado',
          'type_name' => 'varchar',
          'type' => 'varchar(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        23 =>
        array (
          'name' => 'criterio_fecha_hora_creacion',
          'type_name' => 'timestamp',
          'type' => 'timestamp(0)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        24 =>
        array (
          'name' => 'criterio_observacion',
          'type_name' => 'text',
          'type' => 'text',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
      ),
      'columnas_insertables' =>
      array (
        0 => 'cod_nodo_semantico',
        1 => 'cod_version_modelo',
        2 => 'codigo_semantico',
        3 => 'nombre',
        4 => 'tipo_nodo',
        5 => 'definicion',
        6 => 'estado',
        7 => 'fecha_hora_creacion',
        8 => 'cod_usuario_creacion',
        9 => 'observacion',
        10 => 'cod_variable_experta',
        11 => 'cod_nodo_propietario_primario',
        12 => 'cod_dominio_valores',
        13 => 'tipo_semantico',
        14 => 'papel_inferencial',
        15 => 'estado_variable',
        16 => 'fecha_hora_creacion_variable',
        17 => 'cod_usuario_creacion_variable',
        18 => 'observacion_variable',
        19 => 'cod_criterio_dominio_resultado',
        20 => 'criterio_cod_dominio_valores',
        21 => 'criterio_cod_usuario_creacion',
        22 => 'criterio_estado',
        23 => 'criterio_fecha_hora_creacion',
        24 => 'criterio_observacion',
      ),
      'clave_primaria' =>
      array (
        0 => 'cod_nodo_semantico',
      ),
      'foraneas' =>
      array (
        0 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'cod_usuario_creacion_variable',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'usuarios',
          'foreign_columns' =>
          array (
            0 => 'cod_usuario',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
        1 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'cod_dominio_valores',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'dominios_valores_expertos',
          'foreign_columns' =>
          array (
            0 => 'cod_dominio_valores',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
        2 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'cod_nodo_propietario_primario',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'nodos_semanticos',
          'foreign_columns' =>
          array (
            0 => 'cod_nodo_semantico',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
        3 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'cod_version_modelo',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'versiones_modelo_experto',
          'foreign_columns' =>
          array (
            0 => 'cod_version_modelo',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
        4 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'cod_usuario_creacion',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'usuarios',
          'foreign_columns' =>
          array (
            0 => 'cod_usuario',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
        5 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'cod_nodo_propietario_primario',
            1 => 'cod_version_modelo',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'nodos_semanticos',
          'foreign_columns' =>
          array (
            0 => 'cod_nodo_semantico',
            1 => 'cod_version_modelo',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
        6 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'cod_dominio_valores',
            1 => 'cod_version_modelo',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'dominios_valores_expertos',
          'foreign_columns' =>
          array (
            0 => 'cod_dominio_valores',
            1 => 'cod_version_modelo',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
        7 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'criterio_cod_usuario_creacion',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'usuarios',
          'foreign_columns' =>
          array (
            0 => 'cod_usuario',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
        8 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'criterio_cod_dominio_valores',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'dominios_valores_expertos',
          'foreign_columns' =>
          array (
            0 => 'cod_dominio_valores',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
      ),
      'triggers' =>
      array (
        0 =>
        array (
          'name' => 'b4_d04d8fd7a67c_insert',
          'sql' => 'CREATE TRIGGER b4_d04d8fd7a67c_insert BEFORE INSERT ON nodos_semanticos WHEN NOT ((NEW.cod_variable_experta IS NULL AND NEW.cod_nodo_propietario_primario IS NULL AND NEW.cod_dominio_valores IS NULL AND NEW.tipo_semantico IS NULL AND NEW.papel_inferencial IS NULL AND NEW.estado_variable IS NULL AND NEW.fecha_hora_creacion_variable IS NULL AND NEW.cod_usuario_creacion_variable IS NULL AND NEW.observacion_variable IS NULL) OR (NEW.cod_variable_experta IS NOT NULL AND NEW.tipo_semantico IS NOT NULL AND NEW.papel_inferencial IS NOT NULL AND NEW.estado_variable IS NOT NULL AND NEW.fecha_hora_creacion_variable IS NOT NULL AND NEW.cod_usuario_creacion_variable IS NOT NULL)) BEGIN SELECT RAISE(ABORT,\'bloque_experto_incompleto\'); END',
        ),
        1 =>
        array (
          'name' => 'b4_d04d8fd7a67c_update',
          'sql' => 'CREATE TRIGGER b4_d04d8fd7a67c_update BEFORE UPDATE ON nodos_semanticos WHEN NOT ((NEW.cod_variable_experta IS NULL AND NEW.cod_nodo_propietario_primario IS NULL AND NEW.cod_dominio_valores IS NULL AND NEW.tipo_semantico IS NULL AND NEW.papel_inferencial IS NULL AND NEW.estado_variable IS NULL AND NEW.fecha_hora_creacion_variable IS NULL AND NEW.cod_usuario_creacion_variable IS NULL AND NEW.observacion_variable IS NULL) OR (NEW.cod_variable_experta IS NOT NULL AND NEW.tipo_semantico IS NOT NULL AND NEW.papel_inferencial IS NOT NULL AND NEW.estado_variable IS NOT NULL AND NEW.fecha_hora_creacion_variable IS NOT NULL AND NEW.cod_usuario_creacion_variable IS NOT NULL)) OR (OLD.cod_variable_experta IS NOT NULL AND NOT (NEW.cod_variable_experta IS OLD.cod_variable_experta AND NEW.cod_version_modelo IS OLD.cod_version_modelo AND NEW.cod_nodo_semantico IS OLD.cod_nodo_semantico AND NEW.cod_nodo_propietario_primario IS OLD.cod_nodo_propietario_primario AND NEW.cod_dominio_valores IS OLD.cod_dominio_valores AND NEW.tipo_semantico IS OLD.tipo_semantico AND NEW.papel_inferencial IS OLD.papel_inferencial AND NEW.estado_variable IS OLD.estado_variable AND NEW.fecha_hora_creacion_variable IS OLD.fecha_hora_creacion_variable AND NEW.cod_usuario_creacion_variable IS OLD.cod_usuario_creacion_variable AND NEW.observacion_variable IS OLD.observacion_variable)) BEGIN SELECT RAISE(ABORT,\'bloque_experto_inmutable_o_incompleto\'); END',
        ),
        2 =>
        array (
          'name' => 'norm18_nodo_update',
          'sql' => 'CREATE TRIGGER norm18_nodo_update BEFORE UPDATE ON nodos_semanticos FOR EACH ROW WHEN OLD.cod_version_modelo <> NEW.cod_version_modelo AND (EXISTS (SELECT 1 FROM relaciones_semanticas WHERE cod_nodo_origen=OLD.cod_nodo_semantico OR cod_nodo_destino=OLD.cod_nodo_semantico) OR EXISTS (SELECT 1 FROM mapeos_variables_fuente WHERE cod_variable_experta=OLD.cod_variable_experta)) BEGIN SELECT RAISE(ABORT, \'El vínculo no puede cambiar el residente o la versión histórica\'); END',
        ),
        3 =>
        array (
          'name' => 'trg_nodo_criterio_bloque_ins',
          'sql' => 'CREATE TRIGGER trg_nodo_criterio_bloque_ins BEFORE INSERT ON nodos_semanticos FOR EACH ROW WHEN (NEW.cod_criterio_dominio_resultado IS NULL AND (NEW.criterio_cod_dominio_valores IS NOT NULL OR NEW.criterio_cod_usuario_creacion IS NOT NULL OR NEW.criterio_estado IS NOT NULL OR NEW.criterio_fecha_hora_creacion IS NOT NULL OR NEW.criterio_observacion IS NOT NULL)) OR (NEW.cod_criterio_dominio_resultado IS NOT NULL AND (NEW.criterio_cod_dominio_valores IS NULL OR NEW.criterio_cod_usuario_creacion IS NULL OR NEW.criterio_estado IS NULL OR NEW.criterio_fecha_hora_creacion IS NULL)) BEGIN SELECT RAISE(ABORT, \'Bloque de criterio incompleto\'); END',
        ),
        4 =>
        array (
          'name' => 'trg_nodo_criterio_bloque_upd',
          'sql' => 'CREATE TRIGGER trg_nodo_criterio_bloque_upd BEFORE UPDATE ON nodos_semanticos FOR EACH ROW WHEN (NEW.cod_criterio_dominio_resultado IS NULL AND (NEW.criterio_cod_dominio_valores IS NOT NULL OR NEW.criterio_cod_usuario_creacion IS NOT NULL OR NEW.criterio_estado IS NOT NULL OR NEW.criterio_fecha_hora_creacion IS NOT NULL OR NEW.criterio_observacion IS NOT NULL)) OR (NEW.cod_criterio_dominio_resultado IS NOT NULL AND (NEW.criterio_cod_dominio_valores IS NULL OR NEW.criterio_cod_usuario_creacion IS NULL OR NEW.criterio_estado IS NULL OR NEW.criterio_fecha_hora_creacion IS NULL)) BEGIN SELECT RAISE(ABORT, \'Bloque de criterio incompleto\'); END',
        ),
      ),
    ),
    'componentes_regla_experta' =>
    array (
      'crear' => 'CREATE TABLE "componentes_regla_experta" (
    cod_componente_regla varchar(20) PRIMARY KEY NOT NULL,
    tipo_evento varchar(12) NOT NULL,
    cod_regla_experta varchar(20) NOT NULL REFERENCES reglas_expertas(cod_regla_experta) ON DELETE RESTRICT,
    cod_valor_semantico varchar(20) NOT NULL REFERENCES valores_semanticos(cod_valor_semantico) ON DELETE RESTRICT,
    cod_variable_experta varchar(20) REFERENCES nodos_semanticos(cod_variable_experta) ON DELETE RESTRICT,
    cod_criterio_dominio_resultado varchar(20) REFERENCES nodos_semanticos(cod_criterio_dominio_resultado) ON DELETE RESTRICT,
    cod_condicion_regla_ref varchar(20) GENERATED ALWAYS AS
        (CASE WHEN tipo_evento = \'CONDICION\' THEN cod_componente_regla END) STORED UNIQUE,
    UNIQUE (cod_regla_experta, cod_variable_experta, cod_valor_semantico),
    UNIQUE (cod_regla_experta, cod_criterio_dominio_resultado),
    CONSTRAINT ck_componente_regla_tipo CHECK ((
        (tipo_evento = \'CONDICION\' AND cod_variable_experta IS NOT NULL AND cod_criterio_dominio_resultado IS NULL)
        OR (tipo_evento = \'CONSECUENCIA\' AND cod_variable_experta IS NULL AND cod_criterio_dominio_resultado IS NOT NULL)
    ) IS TRUE)
)',
      'indices_sql' =>
      array (
        0 =>
        array (
          'name' => 'componente_regla_criterio_i',
          'sql' => 'CREATE INDEX componente_regla_criterio_i ON componentes_regla_experta (cod_criterio_dominio_resultado)',
        ),
        1 =>
        array (
          'name' => 'componente_regla_valor_i',
          'sql' => 'CREATE INDEX componente_regla_valor_i ON componentes_regla_experta (cod_valor_semantico)',
        ),
        2 =>
        array (
          'name' => 'componente_regla_variable_valor_i',
          'sql' => 'CREATE INDEX componente_regla_variable_valor_i ON componentes_regla_experta (cod_variable_experta, cod_valor_semantico)',
        ),
      ),
      'columnas' =>
      array (
        0 =>
        array (
          'name' => 'cod_componente_regla',
          'type_name' => 'varchar',
          'type' => 'varchar(20)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        1 =>
        array (
          'name' => 'tipo_evento',
          'type_name' => 'varchar',
          'type' => 'varchar(12)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        2 =>
        array (
          'name' => 'cod_regla_experta',
          'type_name' => 'varchar',
          'type' => 'varchar(20)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        3 =>
        array (
          'name' => 'cod_valor_semantico',
          'type_name' => 'varchar',
          'type' => 'varchar(20)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        4 =>
        array (
          'name' => 'cod_variable_experta',
          'type_name' => 'varchar',
          'type' => 'varchar(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        5 =>
        array (
          'name' => 'cod_criterio_dominio_resultado',
          'type_name' => 'varchar',
          'type' => 'varchar(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        6 =>
        array (
          'name' => 'cod_condicion_regla_ref',
          'type_name' => 'varchar',
          'type' => 'varchar(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' =>
          array (
            'type' => 'stored',
            'expression' => 'CASE WHEN tipo_evento = \'CONDICION\' THEN cod_componente_regla END',
          ),
        ),
      ),
      'columnas_insertables' =>
      array (
        0 => 'cod_componente_regla',
        1 => 'tipo_evento',
        2 => 'cod_regla_experta',
        3 => 'cod_valor_semantico',
        4 => 'cod_variable_experta',
        5 => 'cod_criterio_dominio_resultado',
      ),
      'clave_primaria' =>
      array (
        0 => 'cod_componente_regla',
      ),
      'foraneas' =>
      array (
        0 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'cod_criterio_dominio_resultado',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'nodos_semanticos',
          'foreign_columns' =>
          array (
            0 => 'cod_criterio_dominio_resultado',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
        1 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'cod_variable_experta',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'nodos_semanticos',
          'foreign_columns' =>
          array (
            0 => 'cod_variable_experta',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
        2 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'cod_valor_semantico',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'valores_semanticos',
          'foreign_columns' =>
          array (
            0 => 'cod_valor_semantico',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
        3 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'cod_regla_experta',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'reglas_expertas',
          'foreign_columns' =>
          array (
            0 => 'cod_regla_experta',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
      ),
      'triggers' =>
      array (
      ),
    ),
    'mapeos_variables_fuente' =>
    array (
      'crear' => 'CREATE TABLE "mapeos_variables_fuente" ("cod_mapeo_variable_fuente" varchar not null, "cod_variable_experta" varchar not null, "cod_fuente_dato_experta" varchar not null, "tipo_extraccion" varchar not null, "campo_valor" varchar, "clave_selector" varchar, "estado" varchar not null, "fecha_hora_creacion" datetime not null, "cod_usuario_creacion" varchar not null, "observacion" text, foreign key("cod_usuario_creacion") references usuarios("cod_usuario") on delete restrict on update no action, foreign key("cod_fuente_dato_experta") references fuentes_datos_expertas("cod_fuente_dato_experta") on delete restrict on update no action, foreign key("cod_variable_experta") references nodos_semanticos("cod_variable_experta") on delete restrict on update no action, primary key ("cod_mapeo_variable_fuente"))',
      'indices_sql' =>
      array (
        0 =>
        array (
          'name' => 'se08_i2',
          'sql' => 'CREATE INDEX "se08_i2" on "mapeos_variables_fuente" ("cod_variable_experta", "cod_fuente_dato_experta")',
        ),
        1 =>
        array (
          'name' => 'se08_i3',
          'sql' => 'CREATE INDEX "se08_i3" on "mapeos_variables_fuente" ("cod_fuente_dato_experta")',
        ),
        2 =>
        array (
          'name' => 'se08_i4',
          'sql' => 'CREATE INDEX "se08_i4" on "mapeos_variables_fuente" ("cod_usuario_creacion")',
        ),
      ),
      'columnas' =>
      array (
        0 =>
        array (
          'name' => 'cod_mapeo_variable_fuente',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        1 =>
        array (
          'name' => 'cod_variable_experta',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        2 =>
        array (
          'name' => 'cod_fuente_dato_experta',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        3 =>
        array (
          'name' => 'tipo_extraccion',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        4 =>
        array (
          'name' => 'campo_valor',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        5 =>
        array (
          'name' => 'clave_selector',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        6 =>
        array (
          'name' => 'estado',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        7 =>
        array (
          'name' => 'fecha_hora_creacion',
          'type_name' => 'datetime',
          'type' => 'datetime',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        8 =>
        array (
          'name' => 'cod_usuario_creacion',
          'type_name' => 'varchar',
          'type' => 'varchar',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        9 =>
        array (
          'name' => 'observacion',
          'type_name' => 'text',
          'type' => 'text',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
      ),
      'columnas_insertables' =>
      array (
        0 => 'cod_mapeo_variable_fuente',
        1 => 'cod_variable_experta',
        2 => 'cod_fuente_dato_experta',
        3 => 'tipo_extraccion',
        4 => 'campo_valor',
        5 => 'clave_selector',
        6 => 'estado',
        7 => 'fecha_hora_creacion',
        8 => 'cod_usuario_creacion',
        9 => 'observacion',
      ),
      'clave_primaria' =>
      array (
        0 => 'cod_mapeo_variable_fuente',
      ),
      'foraneas' =>
      array (
        0 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'cod_variable_experta',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'nodos_semanticos',
          'foreign_columns' =>
          array (
            0 => 'cod_variable_experta',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
        1 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'cod_fuente_dato_experta',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'fuentes_datos_expertas',
          'foreign_columns' =>
          array (
            0 => 'cod_fuente_dato_experta',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
        2 =>
        array (
          'name' => NULL,
          'columns' =>
          array (
            0 => 'cod_usuario_creacion',
          ),
          'foreign_schema' => 'main',
          'foreign_table' => 'usuarios',
          'foreign_columns' =>
          array (
            0 => 'cod_usuario',
          ),
          'on_update' => 'no action',
          'on_delete' => 'restrict',
        ),
      ),
      'triggers' =>
      array (
        0 =>
        array (
          'name' => 'norm18_mapeo_insert',
          'sql' => 'CREATE TRIGGER norm18_mapeo_insert BEFORE INSERT ON mapeos_variables_fuente FOR EACH ROW WHEN (SELECT cod_version_modelo FROM nodos_semanticos WHERE cod_variable_experta=NEW.cod_variable_experta) <> (SELECT cod_version_modelo FROM fuentes_datos_expertas WHERE cod_fuente_dato_experta=NEW.cod_fuente_dato_experta) BEGIN SELECT RAISE(ABORT, \'El vínculo no puede cambiar el residente o la versión histórica\'); END',
        ),
        1 =>
        array (
          'name' => 'norm18_mapeo_update',
          'sql' => 'CREATE TRIGGER norm18_mapeo_update BEFORE UPDATE ON mapeos_variables_fuente FOR EACH ROW WHEN (SELECT cod_version_modelo FROM nodos_semanticos WHERE cod_variable_experta=NEW.cod_variable_experta) <> (SELECT cod_version_modelo FROM fuentes_datos_expertas WHERE cod_fuente_dato_experta=NEW.cod_fuente_dato_experta) BEGIN SELECT RAISE(ABORT, \'El vínculo no puede cambiar el residente o la versión histórica\'); END',
        ),
      ),
    ),
  ),
  'pgsql' =>
  array (
    'nodos_semanticos' =>
    array (
      'columnas' =>
      array (
        0 =>
        array (
          'name' => 'cod_nodo_semantico',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        1 =>
        array (
          'name' => 'cod_version_modelo',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        2 =>
        array (
          'name' => 'codigo_semantico',
          'type_name' => 'varchar',
          'type' => 'character varying(50)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        3 =>
        array (
          'name' => 'nombre',
          'type_name' => 'varchar',
          'type' => 'character varying(160)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        4 =>
        array (
          'name' => 'tipo_nodo',
          'type_name' => 'varchar',
          'type' => 'character varying(40)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        5 =>
        array (
          'name' => 'definicion',
          'type_name' => 'text',
          'type' => 'text',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        6 =>
        array (
          'name' => 'estado',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        7 =>
        array (
          'name' => 'fecha_hora_creacion',
          'type_name' => 'timestamp',
          'type' => 'timestamp(0) without time zone',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        8 =>
        array (
          'name' => 'cod_usuario_creacion',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        9 =>
        array (
          'name' => 'observacion',
          'type_name' => 'text',
          'type' => 'text',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        10 =>
        array (
          'name' => 'cod_variable_experta',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        11 =>
        array (
          'name' => 'cod_nodo_propietario_primario',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        12 =>
        array (
          'name' => 'cod_dominio_valores',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        13 =>
        array (
          'name' => 'tipo_semantico',
          'type_name' => 'varchar',
          'type' => 'character varying(30)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        14 =>
        array (
          'name' => 'papel_inferencial',
          'type_name' => 'varchar',
          'type' => 'character varying(40)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        15 =>
        array (
          'name' => 'estado_variable',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        16 =>
        array (
          'name' => 'fecha_hora_creacion_variable',
          'type_name' => 'timestamp',
          'type' => 'timestamp(0) without time zone',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        17 =>
        array (
          'name' => 'cod_usuario_creacion_variable',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        18 =>
        array (
          'name' => 'observacion_variable',
          'type_name' => 'text',
          'type' => 'text',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        19 =>
        array (
          'name' => 'cod_criterio_dominio_resultado',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        20 =>
        array (
          'name' => 'criterio_cod_dominio_valores',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        21 =>
        array (
          'name' => 'criterio_cod_usuario_creacion',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        22 =>
        array (
          'name' => 'criterio_estado',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        23 =>
        array (
          'name' => 'criterio_fecha_hora_creacion',
          'type_name' => 'timestamp',
          'type' => 'timestamp(0) without time zone',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        24 =>
        array (
          'name' => 'criterio_observacion',
          'type_name' => 'text',
          'type' => 'text',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
      ),
      'indices_sql' =>
      array (
        0 =>
        array (
          'nombre' => 'b4_dominio_f_i',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX b4_dominio_f_i ON public.nodos_semanticos USING btree (cod_dominio_valores)',
        ),
        1 =>
        array (
          'nombre' => 'b4_dominio_version_f_i',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX b4_dominio_version_f_i ON public.nodos_semanticos USING btree (cod_dominio_valores, cod_version_modelo)',
        ),
        2 =>
        array (
          'nombre' => 'b4_propietario_f_i',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX b4_propietario_f_i ON public.nodos_semanticos USING btree (cod_nodo_propietario_primario)',
        ),
        3 =>
        array (
          'nombre' => 'b4_propietario_version_f_i',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX b4_propietario_version_f_i ON public.nodos_semanticos USING btree (cod_nodo_propietario_primario, cod_version_modelo)',
        ),
        4 =>
        array (
          'nombre' => 'b4_variable_autor_f_i',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX b4_variable_autor_f_i ON public.nodos_semanticos USING btree (cod_usuario_creacion_variable)',
        ),
        5 =>
        array (
          'nombre' => 'b4_variable_u',
          'unico' => true,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE UNIQUE INDEX b4_variable_u ON public.nodos_semanticos USING btree (cod_variable_experta)',
        ),
        6 =>
        array (
          'nombre' => 'b4_variable_version_u',
          'unico' => true,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE UNIQUE INDEX b4_variable_version_u ON public.nodos_semanticos USING btree (cod_variable_experta, cod_version_modelo)',
        ),
        7 =>
        array (
          'nombre' => 'nodo_criterio_resultado_dominio_i',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX nodo_criterio_resultado_dominio_i ON public.nodos_semanticos USING btree (criterio_cod_dominio_valores)',
        ),
        8 =>
        array (
          'nombre' => 'nodo_criterio_resultado_estado_i',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX nodo_criterio_resultado_estado_i ON public.nodos_semanticos USING btree (criterio_estado)',
        ),
        9 =>
        array (
          'nombre' => 'nodo_criterio_resultado_u',
          'unico' => true,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE UNIQUE INDEX nodo_criterio_resultado_u ON public.nodos_semanticos USING btree (cod_criterio_dominio_resultado)',
        ),
        10 =>
        array (
          'nombre' => 'nodo_criterio_resultado_usuario_i',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX nodo_criterio_resultado_usuario_i ON public.nodos_semanticos USING btree (criterio_cod_usuario_creacion)',
        ),
        11 =>
        array (
          'nombre' => 'nodos_semanticos_pkey',
          'unico' => true,
          'primario' => true,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE UNIQUE INDEX nodos_semanticos_pkey ON public.nodos_semanticos USING btree (cod_nodo_semantico)',
        ),
        12 =>
        array (
          'nombre' => 'se02_i1',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX se02_i1 ON public.nodos_semanticos USING btree (tipo_nodo)',
        ),
        13 =>
        array (
          'nombre' => 'se02_i2',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX se02_i2 ON public.nodos_semanticos USING btree (cod_usuario_creacion)',
        ),
        14 =>
        array (
          'nombre' => 'se02_u1',
          'unico' => true,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE UNIQUE INDEX se02_u1 ON public.nodos_semanticos USING btree (cod_version_modelo, codigo_semantico)',
        ),
        15 =>
        array (
          'nombre' => 'se02_u2',
          'unico' => true,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE UNIQUE INDEX se02_u2 ON public.nodos_semanticos USING btree (cod_nodo_semantico, cod_version_modelo)',
        ),
      ),
      'restricciones' =>
      array (
        0 =>
        array (
          'conname' => 'b4_dominio_f',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (cod_dominio_valores) REFERENCES dominios_valores_expertos(cod_dominio_valores) ON DELETE RESTRICT',
        ),
        1 =>
        array (
          'conname' => 'b4_dominio_version_f',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (cod_dominio_valores, cod_version_modelo) REFERENCES dominios_valores_expertos(cod_dominio_valores, cod_version_modelo) ON DELETE RESTRICT',
        ),
        2 =>
        array (
          'conname' => 'b4_propietario_f',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (cod_nodo_propietario_primario) REFERENCES nodos_semanticos(cod_nodo_semantico) ON DELETE RESTRICT',
        ),
        3 =>
        array (
          'conname' => 'b4_propietario_version_f',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (cod_nodo_propietario_primario, cod_version_modelo) REFERENCES nodos_semanticos(cod_nodo_semantico, cod_version_modelo) ON DELETE RESTRICT',
        ),
        4 =>
        array (
          'conname' => 'b4_variable_autor_f',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (cod_usuario_creacion_variable) REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT',
        ),
        5 =>
        array (
          'conname' => 'b4_variable_u',
          'contype' => 'u',
          'convalidated' => true,
          'definicion' => 'UNIQUE (cod_variable_experta)',
        ),
        6 =>
        array (
          'conname' => 'b4_variable_version_u',
          'contype' => 'u',
          'convalidated' => true,
          'definicion' => 'UNIQUE (cod_variable_experta, cod_version_modelo)',
        ),
        7 =>
        array (
          'conname' => 'ck_nodo_criterio_bloque',
          'contype' => 'c',
          'convalidated' => true,
          'definicion' => 'CHECK (((NOT ((cod_criterio_dominio_resultado IS NULL) AND ((criterio_cod_dominio_valores IS NOT NULL) OR (criterio_cod_usuario_creacion IS NOT NULL) OR (criterio_estado IS NOT NULL) OR (criterio_fecha_hora_creacion IS NOT NULL) OR (criterio_observacion IS NOT NULL)))) AND (NOT ((cod_criterio_dominio_resultado IS NOT NULL) AND ((criterio_cod_dominio_valores IS NULL) OR (criterio_cod_usuario_creacion IS NULL) OR (criterio_estado IS NULL) OR (criterio_fecha_hora_creacion IS NULL))))))',
        ),
        8 =>
        array (
          'conname' => 'nodos_semanticos_cod_nodo_semantico_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL cod_nodo_semantico',
        ),
        9 =>
        array (
          'conname' => 'nodos_semanticos_cod_usuario_creacion_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL cod_usuario_creacion',
        ),
        10 =>
        array (
          'conname' => 'nodos_semanticos_cod_version_modelo_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL cod_version_modelo',
        ),
        11 =>
        array (
          'conname' => 'nodos_semanticos_codigo_semantico_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL codigo_semantico',
        ),
        12 =>
        array (
          'conname' => 'nodos_semanticos_criterio_cod_dominio_valores_fkey',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (criterio_cod_dominio_valores) REFERENCES dominios_valores_expertos(cod_dominio_valores) ON DELETE RESTRICT',
        ),
        13 =>
        array (
          'conname' => 'nodos_semanticos_criterio_cod_usuario_creacion_fkey',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (criterio_cod_usuario_creacion) REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT',
        ),
        14 =>
        array (
          'conname' => 'nodos_semanticos_definicion_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL definicion',
        ),
        15 =>
        array (
          'conname' => 'nodos_semanticos_estado_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL estado',
        ),
        16 =>
        array (
          'conname' => 'nodos_semanticos_fecha_hora_creacion_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL fecha_hora_creacion',
        ),
        17 =>
        array (
          'conname' => 'nodos_semanticos_nombre_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL nombre',
        ),
        18 =>
        array (
          'conname' => 'nodos_semanticos_pkey',
          'contype' => 'p',
          'convalidated' => true,
          'definicion' => 'PRIMARY KEY (cod_nodo_semantico)',
        ),
        19 =>
        array (
          'conname' => 'nodos_semanticos_tipo_nodo_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL tipo_nodo',
        ),
        20 =>
        array (
          'conname' => 'se02_f1',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (cod_version_modelo) REFERENCES versiones_modelo_experto(cod_version_modelo) ON DELETE RESTRICT',
        ),
        21 =>
        array (
          'conname' => 'se02_f2',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (cod_usuario_creacion) REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT',
        ),
        22 =>
        array (
          'conname' => 'se02_u1',
          'contype' => 'u',
          'convalidated' => true,
          'definicion' => 'UNIQUE (cod_version_modelo, codigo_semantico)',
        ),
        23 =>
        array (
          'conname' => 'se02_u2',
          'contype' => 'u',
          'convalidated' => true,
          'definicion' => 'UNIQUE (cod_nodo_semantico, cod_version_modelo)',
        ),
      ),
      'triggers' =>
      array (
        0 =>
        array (
          'tgname' => 'b4_d04d8fd7a67c_insert',
          'definicion' => 'CREATE TRIGGER b4_d04d8fd7a67c_insert BEFORE INSERT ON public.nodos_semanticos FOR EACH ROW EXECUTE FUNCTION b4_d04d8fd7a67c()',
          'funcion' => 'CREATE OR REPLACE FUNCTION public.b4_d04d8fd7a67c()
 RETURNS trigger
 LANGUAGE plpgsql
AS $function$ BEGIN
                    IF NOT (((NEW.cod_variable_experta IS NULL AND NEW.cod_nodo_propietario_primario IS NULL AND NEW.cod_dominio_valores IS NULL AND NEW.tipo_semantico IS NULL AND NEW.papel_inferencial IS NULL AND NEW.estado_variable IS NULL AND NEW.fecha_hora_creacion_variable IS NULL AND NEW.cod_usuario_creacion_variable IS NULL AND NEW.observacion_variable IS NULL) OR (NEW.cod_variable_experta IS NOT NULL AND NEW.tipo_semantico IS NOT NULL AND NEW.papel_inferencial IS NOT NULL AND NEW.estado_variable IS NOT NULL AND NEW.fecha_hora_creacion_variable IS NOT NULL AND NEW.cod_usuario_creacion_variable IS NOT NULL))) THEN RAISE EXCEPTION \'bloque_experto_incompleto\' USING ERRCODE=\'23514\'; END IF;
                    IF TG_OP=\'UPDATE\' AND (OLD.cod_variable_experta IS NOT NULL AND NOT (NEW.cod_variable_experta IS NOT DISTINCT FROM OLD.cod_variable_experta AND NEW.cod_version_modelo IS NOT DISTINCT FROM OLD.cod_version_modelo AND NEW.cod_nodo_semantico IS NOT DISTINCT FROM OLD.cod_nodo_semantico AND NEW.cod_nodo_propietario_primario IS NOT DISTINCT FROM OLD.cod_nodo_propietario_primario AND NEW.cod_dominio_valores IS NOT DISTINCT FROM OLD.cod_dominio_valores AND NEW.tipo_semantico IS NOT DISTINCT FROM OLD.tipo_semantico AND NEW.papel_inferencial IS NOT DISTINCT FROM OLD.papel_inferencial AND NEW.estado_variable IS NOT DISTINCT FROM OLD.estado_variable AND NEW.fecha_hora_creacion_variable IS NOT DISTINCT FROM OLD.fecha_hora_creacion_variable AND NEW.cod_usuario_creacion_variable IS NOT DISTINCT FROM OLD.cod_usuario_creacion_variable AND NEW.observacion_variable IS NOT DISTINCT FROM OLD.observacion_variable)) THEN RAISE EXCEPTION \'bloque_experto_inmutable\' USING ERRCODE=\'55000\'; END IF;
                    RETURN NEW; END $function$
',
        ),
        1 =>
        array (
          'tgname' => 'b4_d04d8fd7a67c_update',
          'definicion' => 'CREATE TRIGGER b4_d04d8fd7a67c_update BEFORE UPDATE ON public.nodos_semanticos FOR EACH ROW EXECUTE FUNCTION b4_d04d8fd7a67c()',
          'funcion' => 'CREATE OR REPLACE FUNCTION public.b4_d04d8fd7a67c()
 RETURNS trigger
 LANGUAGE plpgsql
AS $function$ BEGIN
                    IF NOT (((NEW.cod_variable_experta IS NULL AND NEW.cod_nodo_propietario_primario IS NULL AND NEW.cod_dominio_valores IS NULL AND NEW.tipo_semantico IS NULL AND NEW.papel_inferencial IS NULL AND NEW.estado_variable IS NULL AND NEW.fecha_hora_creacion_variable IS NULL AND NEW.cod_usuario_creacion_variable IS NULL AND NEW.observacion_variable IS NULL) OR (NEW.cod_variable_experta IS NOT NULL AND NEW.tipo_semantico IS NOT NULL AND NEW.papel_inferencial IS NOT NULL AND NEW.estado_variable IS NOT NULL AND NEW.fecha_hora_creacion_variable IS NOT NULL AND NEW.cod_usuario_creacion_variable IS NOT NULL))) THEN RAISE EXCEPTION \'bloque_experto_incompleto\' USING ERRCODE=\'23514\'; END IF;
                    IF TG_OP=\'UPDATE\' AND (OLD.cod_variable_experta IS NOT NULL AND NOT (NEW.cod_variable_experta IS NOT DISTINCT FROM OLD.cod_variable_experta AND NEW.cod_version_modelo IS NOT DISTINCT FROM OLD.cod_version_modelo AND NEW.cod_nodo_semantico IS NOT DISTINCT FROM OLD.cod_nodo_semantico AND NEW.cod_nodo_propietario_primario IS NOT DISTINCT FROM OLD.cod_nodo_propietario_primario AND NEW.cod_dominio_valores IS NOT DISTINCT FROM OLD.cod_dominio_valores AND NEW.tipo_semantico IS NOT DISTINCT FROM OLD.tipo_semantico AND NEW.papel_inferencial IS NOT DISTINCT FROM OLD.papel_inferencial AND NEW.estado_variable IS NOT DISTINCT FROM OLD.estado_variable AND NEW.fecha_hora_creacion_variable IS NOT DISTINCT FROM OLD.fecha_hora_creacion_variable AND NEW.cod_usuario_creacion_variable IS NOT DISTINCT FROM OLD.cod_usuario_creacion_variable AND NEW.observacion_variable IS NOT DISTINCT FROM OLD.observacion_variable)) THEN RAISE EXCEPTION \'bloque_experto_inmutable\' USING ERRCODE=\'55000\'; END IF;
                    RETURN NEW; END $function$
',
        ),
        2 =>
        array (
          'tgname' => 'norm18_nodo',
          'definicion' => 'CREATE TRIGGER norm18_nodo BEFORE UPDATE ON public.nodos_semanticos FOR EACH ROW EXECUTE FUNCTION norm18_nodo()',
          'funcion' => 'CREATE OR REPLACE FUNCTION public.norm18_nodo()
 RETURNS trigger
 LANGUAGE plpgsql
AS $function$ BEGIN  IF (OLD.cod_version_modelo <> NEW.cod_version_modelo AND (EXISTS (SELECT 1 FROM relaciones_semanticas WHERE cod_nodo_origen=OLD.cod_nodo_semantico OR cod_nodo_destino=OLD.cod_nodo_semantico) OR EXISTS (SELECT 1 FROM mapeos_variables_fuente WHERE cod_variable_experta=OLD.cod_variable_experta))) THEN RAISE EXCEPTION \'El vínculo no puede cambiar el residente o la versión histórica\' USING ERRCODE=\'23514\'; END IF; RETURN NEW; END; $function$
',
        ),
      ),
    ),
    'componentes_regla_experta' =>
    array (
      'columnas' =>
      array (
        0 =>
        array (
          'name' => 'cod_componente_regla',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        1 =>
        array (
          'name' => 'tipo_evento',
          'type_name' => 'varchar',
          'type' => 'character varying(12)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        2 =>
        array (
          'name' => 'cod_regla_experta',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        3 =>
        array (
          'name' => 'cod_valor_semantico',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        4 =>
        array (
          'name' => 'cod_variable_experta',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        5 =>
        array (
          'name' => 'cod_criterio_dominio_resultado',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        6 =>
        array (
          'name' => 'cod_condicion_regla_ref',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' =>
          array (
            'type' => 'stored',
            'expression' => '
CASE
    WHEN ((tipo_evento)::text = \'CONDICION\'::text) THEN cod_componente_regla
    ELSE NULL::character varying
END',
          ),
        ),
      ),
      'indices_sql' =>
      array (
        0 =>
        array (
          'nombre' => 'componente_regla_criterio_i',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX componente_regla_criterio_i ON public.componentes_regla_experta USING btree (cod_criterio_dominio_resultado)',
        ),
        1 =>
        array (
          'nombre' => 'componente_regla_valor_i',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX componente_regla_valor_i ON public.componentes_regla_experta USING btree (cod_valor_semantico)',
        ),
        2 =>
        array (
          'nombre' => 'componente_regla_variable_valor_i',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX componente_regla_variable_valor_i ON public.componentes_regla_experta USING btree (cod_variable_experta, cod_valor_semantico)',
        ),
        3 =>
        array (
          'nombre' => 'componentes_regla_experta_cop_cod_regla_experta_cod_criteri_key',
          'unico' => true,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE UNIQUE INDEX componentes_regla_experta_cop_cod_regla_experta_cod_criteri_key ON public.componentes_regla_experta USING btree (cod_regla_experta, cod_criterio_dominio_resultado)',
        ),
        4 =>
        array (
          'nombre' => 'componentes_regla_experta_cop_cod_regla_experta_cod_variabl_key',
          'unico' => true,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE UNIQUE INDEX componentes_regla_experta_cop_cod_regla_experta_cod_variabl_key ON public.componentes_regla_experta USING btree (cod_regla_experta, cod_variable_experta, cod_valor_semantico)',
        ),
        5 =>
        array (
          'nombre' => 'componentes_regla_experta_copia_cod_condicion_regla_ref_key',
          'unico' => true,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE UNIQUE INDEX componentes_regla_experta_copia_cod_condicion_regla_ref_key ON public.componentes_regla_experta USING btree (cod_condicion_regla_ref)',
        ),
        6 =>
        array (
          'nombre' => 'componentes_regla_experta_copia_pkey',
          'unico' => true,
          'primario' => true,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE UNIQUE INDEX componentes_regla_experta_copia_pkey ON public.componentes_regla_experta USING btree (cod_componente_regla)',
        ),
      ),
      'restricciones' =>
      array (
        0 =>
        array (
          'conname' => 'ck_componente_regla_tipo',
          'contype' => 'c',
          'convalidated' => true,
          'definicion' => 'CHECK ((((((tipo_evento)::text = \'CONDICION\'::text) AND (cod_variable_experta IS NOT NULL) AND (cod_criterio_dominio_resultado IS NULL)) OR (((tipo_evento)::text = \'CONSECUENCIA\'::text) AND (cod_variable_experta IS NULL) AND (cod_criterio_dominio_resultado IS NOT NULL))) IS TRUE))',
        ),
        1 =>
        array (
          'conname' => 'componentes_regla_experta_cop_cod_criterio_dominio_resulta_fkey',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (cod_criterio_dominio_resultado) REFERENCES nodos_semanticos(cod_criterio_dominio_resultado) ON DELETE RESTRICT',
        ),
        2 =>
        array (
          'conname' => 'componentes_regla_experta_cop_cod_regla_experta_cod_criteri_key',
          'contype' => 'u',
          'convalidated' => true,
          'definicion' => 'UNIQUE (cod_regla_experta, cod_criterio_dominio_resultado)',
        ),
        3 =>
        array (
          'conname' => 'componentes_regla_experta_cop_cod_regla_experta_cod_variabl_key',
          'contype' => 'u',
          'convalidated' => true,
          'definicion' => 'UNIQUE (cod_regla_experta, cod_variable_experta, cod_valor_semantico)',
        ),
        4 =>
        array (
          'conname' => 'componentes_regla_experta_copia_cod_componente_regla_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL cod_componente_regla',
        ),
        5 =>
        array (
          'conname' => 'componentes_regla_experta_copia_cod_condicion_regla_ref_key',
          'contype' => 'u',
          'convalidated' => true,
          'definicion' => 'UNIQUE (cod_condicion_regla_ref)',
        ),
        6 =>
        array (
          'conname' => 'componentes_regla_experta_copia_cod_regla_experta_fkey',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (cod_regla_experta) REFERENCES reglas_expertas(cod_regla_experta) ON DELETE RESTRICT',
        ),
        7 =>
        array (
          'conname' => 'componentes_regla_experta_copia_cod_regla_experta_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL cod_regla_experta',
        ),
        8 =>
        array (
          'conname' => 'componentes_regla_experta_copia_cod_valor_semantico_fkey',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (cod_valor_semantico) REFERENCES valores_semanticos(cod_valor_semantico) ON DELETE RESTRICT',
        ),
        9 =>
        array (
          'conname' => 'componentes_regla_experta_copia_cod_valor_semantico_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL cod_valor_semantico',
        ),
        10 =>
        array (
          'conname' => 'componentes_regla_experta_copia_cod_variable_experta_fkey',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (cod_variable_experta) REFERENCES nodos_semanticos(cod_variable_experta) ON DELETE RESTRICT',
        ),
        11 =>
        array (
          'conname' => 'componentes_regla_experta_copia_pkey',
          'contype' => 'p',
          'convalidated' => true,
          'definicion' => 'PRIMARY KEY (cod_componente_regla)',
        ),
        12 =>
        array (
          'conname' => 'componentes_regla_experta_copia_tipo_evento_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL tipo_evento',
        ),
      ),
      'triggers' =>
      array (
      ),
    ),
    'mapeos_variables_fuente' =>
    array (
      'columnas' =>
      array (
        0 =>
        array (
          'name' => 'cod_mapeo_variable_fuente',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        1 =>
        array (
          'name' => 'cod_variable_experta',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        2 =>
        array (
          'name' => 'cod_fuente_dato_experta',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        3 =>
        array (
          'name' => 'tipo_extraccion',
          'type_name' => 'varchar',
          'type' => 'character varying(30)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        4 =>
        array (
          'name' => 'campo_valor',
          'type_name' => 'varchar',
          'type' => 'character varying(80)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        5 =>
        array (
          'name' => 'clave_selector',
          'type_name' => 'varchar',
          'type' => 'character varying(80)',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        6 =>
        array (
          'name' => 'estado',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        7 =>
        array (
          'name' => 'fecha_hora_creacion',
          'type_name' => 'timestamp',
          'type' => 'timestamp(0) without time zone',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        8 =>
        array (
          'name' => 'cod_usuario_creacion',
          'type_name' => 'varchar',
          'type' => 'character varying(20)',
          'collation' => NULL,
          'nullable' => false,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
        9 =>
        array (
          'name' => 'observacion',
          'type_name' => 'text',
          'type' => 'text',
          'collation' => NULL,
          'nullable' => true,
          'default' => NULL,
          'auto_increment' => false,
          'comment' => NULL,
          'generation' => NULL,
        ),
      ),
      'indices_sql' =>
      array (
        0 =>
        array (
          'nombre' => 'mapeos_variables_fuente_pkey',
          'unico' => true,
          'primario' => true,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE UNIQUE INDEX mapeos_variables_fuente_pkey ON public.mapeos_variables_fuente USING btree (cod_mapeo_variable_fuente)',
        ),
        1 =>
        array (
          'nombre' => 'se08_i2',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX se08_i2 ON public.mapeos_variables_fuente USING btree (cod_variable_experta, cod_fuente_dato_experta)',
        ),
        2 =>
        array (
          'nombre' => 'se08_i3',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX se08_i3 ON public.mapeos_variables_fuente USING btree (cod_fuente_dato_experta)',
        ),
        3 =>
        array (
          'nombre' => 'se08_i4',
          'unico' => false,
          'primario' => false,
          'valido' => true,
          'predicado' => NULL,
          'expresiones' => NULL,
          'definicion' => 'CREATE INDEX se08_i4 ON public.mapeos_variables_fuente USING btree (cod_usuario_creacion)',
        ),
      ),
      'restricciones' =>
      array (
        0 =>
        array (
          'conname' => 'mapeos_variables_fuente_cod_fuente_dato_experta_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL cod_fuente_dato_experta',
        ),
        1 =>
        array (
          'conname' => 'mapeos_variables_fuente_cod_mapeo_variable_fuente_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL cod_mapeo_variable_fuente',
        ),
        2 =>
        array (
          'conname' => 'mapeos_variables_fuente_cod_usuario_creacion_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL cod_usuario_creacion',
        ),
        3 =>
        array (
          'conname' => 'mapeos_variables_fuente_cod_variable_experta_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL cod_variable_experta',
        ),
        4 =>
        array (
          'conname' => 'mapeos_variables_fuente_estado_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL estado',
        ),
        5 =>
        array (
          'conname' => 'mapeos_variables_fuente_fecha_hora_creacion_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL fecha_hora_creacion',
        ),
        6 =>
        array (
          'conname' => 'mapeos_variables_fuente_pkey',
          'contype' => 'p',
          'convalidated' => true,
          'definicion' => 'PRIMARY KEY (cod_mapeo_variable_fuente)',
        ),
        7 =>
        array (
          'conname' => 'mapeos_variables_fuente_tipo_extraccion_not_null',
          'contype' => 'n',
          'convalidated' => true,
          'definicion' => 'NOT NULL tipo_extraccion',
        ),
        8 =>
        array (
          'conname' => 'se08_f2',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (cod_variable_experta) REFERENCES nodos_semanticos(cod_variable_experta) ON DELETE RESTRICT',
        ),
        9 =>
        array (
          'conname' => 'se08_f3',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (cod_fuente_dato_experta) REFERENCES fuentes_datos_expertas(cod_fuente_dato_experta) ON DELETE RESTRICT',
        ),
        10 =>
        array (
          'conname' => 'se08_f4',
          'contype' => 'f',
          'convalidated' => true,
          'definicion' => 'FOREIGN KEY (cod_usuario_creacion) REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT',
        ),
      ),
      'triggers' =>
      array (
        0 =>
        array (
          'tgname' => 'norm18_mapeo',
          'definicion' => 'CREATE TRIGGER norm18_mapeo BEFORE INSERT OR UPDATE ON public.mapeos_variables_fuente FOR EACH ROW EXECUTE FUNCTION norm18_mapeo()',
          'funcion' => 'CREATE OR REPLACE FUNCTION public.norm18_mapeo()
 RETURNS trigger
 LANGUAGE plpgsql
AS $function$ BEGIN PERFORM 1 FROM nodos_semanticos WHERE cod_variable_experta=NEW.cod_variable_experta FOR SHARE; PERFORM 1 FROM fuentes_datos_expertas WHERE cod_fuente_dato_experta=NEW.cod_fuente_dato_experta FOR SHARE; IF ((SELECT cod_version_modelo FROM nodos_semanticos WHERE cod_variable_experta=NEW.cod_variable_experta) <> (SELECT cod_version_modelo FROM fuentes_datos_expertas WHERE cod_fuente_dato_experta=NEW.cod_fuente_dato_experta)) THEN RAISE EXCEPTION \'El vínculo no puede cambiar el residente o la versión histórica\' USING ERRCODE=\'23514\'; END IF; RETURN NEW; END; $function$
',
        ),
      ),
    ),
  ),
);
