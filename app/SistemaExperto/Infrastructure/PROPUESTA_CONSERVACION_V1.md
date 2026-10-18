# Diccionario propuesto: conservación exacta de memoria, cognición y DCL

Estado: **ALTERNATIVA NO APLICADA**. Fecha: 17/10/2026.
La propietaria aprobó posteriormente la [alternativa V2 de nueve tablas](PROPUESTA_REUTILIZACION_D137_V2.md). Las dieciséis de este documento no se incorporan.
Este documento conserva el diseño alternativo anterior que evitaba modificar
D-137. Su conteo de dieciséis no corresponde al esquema incorporado. La decisión
posterior de la propietaria eligió nueve tablas nuevas y ampliaciones explícitas
de las existentes. No se ha creado ninguna migración de esta alternativa V1.
Alcance de diseño confirmado por la propietaria: casos sintéticos y hechos
extraídos de fuentes institucionales. No autoriza uso asistencial del motor.

## Alcance de la alternativa histórica

Incorporar **16 tablas suplementarias expertas** para instantáneas inmutables.
No se modifican las 23 tablas D-137 ni las 73 operativas aprobadas.
Se mantienen testing/base desechable y los contratos actuales de repositorio.

Se solicita aprobar expresamente la copia escalar tipada del hecho **ya
extraído y mapeado por D-137**. Es una instantánea técnica de evaluación, con
FK a su evidencia original; no un formulario clínico universal, un catálogo
libre de campos o una sustitución de las tablas operativas. No utiliza JSON,
serialización PHP ni archivos como almacén alternativo.

La alternativa de copiar cada columna operativa/instrumental en proyecciones
separadas necesita al menos 31 tablas y excede lo necesario para reconstruir
los objetos actuales. La opción aquí propuesta conserva el dato efectivamente
utilizado y su procedencia, sin replicar todo el registro fuente.

## Convenciones físicas cerradas

- PK nuevas: `string(20)` con el generador correlativo existente; todas las FK
  preservan el tipo original y tienen índice y borrado restrictivo.
- `E` abrevia `cod_evaluacion_experta`, FK a la tabla 1.
- `H` abrevia `cod_hecho_instantanea`, FK a la tabla 2.
- Identidades originales Domain: `text`, sin truncarlas a 20 caracteres.
- `orden`: integer >= 0; único dentro de su colección propietaria.
- Cada fecha Domain se conserva con tres columnas: `<nombre>_utc datetime(6)`,
  `<nombre>_zona string(80)` y `<nombre>_offset integer`. La lectura valida los
  tres datos y reconstruye la zona original, no la configuración global.
- Todas las columnas son obligatorias salvo las marcadas `?`.
- No se añaden timestamps automáticos, soft-delete ni cascadas.

El grupo escalar fijo `S(nombre)` contiene:

```text
nombre_tipo string(8): NULL | STRING | INT | FLOAT | BOOL
nombre_texto text ?
nombre_entero bigint ?
nombre_real_ieee754 char(16) ?
nombre_logico boolean ?
```

Un CHECK exige exclusivamente la columna correspondiente; NULL exige todas
vacías. FLOAT conserva los 64 bits finitos de PHP como hexadecimal, incluido
-0.0. Los decimales del extractor siguen siendo STRING: "0", "0.00" y 0 no se
confunden. Texto no representable en UTF-8 se rechaza explícitamente, sin
conversión con pérdida. No hay expresiones ejecutables.

## Las 16 tablas y sus columnas

### 1. instantaneas_expertas

```text
cod_evaluacion_experta string(20) PK/FK -> evaluaciones_expertas
fecha_corte_utc datetime(6)
fecha_corte_zona string(80)
fecha_corte_offset integer
version_conservacion string(30)
huella_memoria_cognicion char(64)
capturada_en_utc datetime(6)
```

Residente y versión proceden de la cabecera física, contrastados con Domain.
No se crea una evaluación/residente de conveniencia. La versión de conservación
es técnica y no acredita una versión clínica de conocimiento.

### 2. hechos_instantanea_experta

```text
cod_hecho_instantanea string(20) PK
E
id_hecho_dominio text
fuente_id_dominio text
origen_id_dominio text
episodio_id_dominio text
concepto_dominio text
fecha_hecho_utc datetime(6)
fecha_hecho_zona string(80)
fecha_hecho_offset integer
S(valor_bruto)
orden integer
tipo_origen string(40)
cod_evidencia_evaluacion string(20) ? FK -> evidencias_evaluacion
cod_atencion string(20) ? FK -> atenciones
cod_actividad string(20) ? FK -> actividades
UNIQUE(E, id_hecho_dominio), UNIQUE(E, orden)
```

`tipo_origen` es cerrado: D137, REFERENCIA_EXTERNA_SINTETICA,
REFERENCIA_CRITERIO_SINTETICA, REFERENCIA_IADL_SINTETICA y
REVISION_TECNICA_SINTETICA.

D137 exige evidencia existente de la misma evaluación; su fuente, variable y
mapeo se alcanzan por las FK aprobadas. Antes de guardar se contrasta el valor
original extraído, la procedencia y el episodio físico, sin permitir campos
clínicos libres nuevos. La lectura histórica usa el valor conservado, sin
reextraer el registro mutable ni reinterpretar su valor.

Las formas sintéticas no simulan FK institucionales: exigen SIM-/SINTETICO-,
sus fuentes explícitas y los constructores vigentes. La revisión técnica admite
solo boolean/null para VERIFICACION_PROCEDENCIA:<evidencia existente> y los
seis conceptos COG-*:campo de EvaluadorConfusoresService. El resto se rechaza.
El código fuente literal de cada hecho se conserva aunque su clase técnica de
almacenamiento sea REVISION_TECNICA_SINTETICA.

### 3. evidencias_cognitivas_instantanea

```text
cod_evidencia_cognitiva_instantanea string(20) PK
H UNIQUE
dominio string(20)
subcomponente string(20) ?
hallazgo string(50)
instrumento_id_dominio text ?
justificacion text
orden integer
referencia_instrumento text ?
referencia_edicion text ?
referencia_subprueba text ?
referencia_norma text ?
referencia_idioma text ?
referencia_poblacion text ?
referencia_revisor text ?
```

Siete columnas fijas representan únicamente la referencia SIM exigida por
Domain. Todas ausentes equivalen a referencia vacía; no a cadenas vacías.

### 4. dominios_instantanea_experta

```text
cod_dominio_instantanea string(20) PK
E
dominio string(20)
estado char(1): A | P | U | I | X
evaluabilidad integer: 0 | 1 | 2
justificacion text
UNIQUE(E, dominio)
```

Cinco dominios exactos. No se inventan filas de resultados_criterio ni valores
semánticos para satisfacer un catálogo D-137 que no representa estos estados.

### 5. soportes_dominios_instantanea

```text
cod_soporte_dominio_instantanea string(20) PK
cod_dominio_instantanea FK -> tabla 4
cod_evidencia_cognitiva_instantanea FK -> tabla 3
orden integer
UNIQUE(dominio, evidencia), UNIQUE(dominio, orden)
```

### 6. subcomponentes_instantanea_experta

```text
cod_subcomponente_instantanea string(20) PK
cod_dominio_instantanea FK -> tabla 4
subcomponente string(20)
hallazgo string(50)
justificacion text
orden integer
UNIQUE(dominio, subcomponente), UNIQUE(dominio, orden)
```

### 7. soportes_subcomponentes_instantanea

```text
cod_soporte_subcomponente string(20) PK
cod_subcomponente_instantanea FK -> tabla 6
cod_evidencia_cognitiva_instantanea FK -> tabla 3
orden integer
UNIQUE(subcomponente, evidencia), UNIQUE(subcomponente, orden)
```

### 8. funcionalidad_instantanea_experta

```text
cod_funcionalidad_instantanea string(20) PK
E UNIQUE
independencia_relativa string(20)
revisor_sintetico_id text ?
justificacion text
```

### 9. evidencias_iadl_instantanea

```text
cod_evidencia_iadl_instantanea string(20) PK
cod_funcionalidad_instantanea FK -> tabla 8
H
actividad_id_dominio text
estado string(30)
oportunidad string(40)
nivel_previo string(30) ?
causa_ayuda text ?
atribucion_cognitiva_simulada boolean ?
revisor_sintetico_id text ?
orden integer
UNIQUE(funcionalidad, H), UNIQUE(funcionalidad, orden)
```

No convierte las causas capturadas en IADL operativa en atribuciones SIM.

### 10. clasificaciones_dcl_instantanea

```text
cod_clasificacion_dcl_instantanea string(20) PK
E UNIQUE
cod_funcionalidad_instantanea FK -> tabla 8 UNIQUE
version_contrato string(80)
diferencial string(40)
patron_candidato string(10) ?
justificacion text
uso string(50): SIMULACION_ACADEMICA_NO_CLINICA
huella_clasificacion char(64)
```

### 11. requisitos_dcl_instantanea

```text
cod_requisito_dcl_instantanea string(20) PK
cod_clasificacion_dcl_instantanea FK -> tabla 10
criterio string(20): DCL-C01..DCL-C05
estado string(20)
justificacion text
UNIQUE(clasificacion, criterio)
```

### 12. soportes_requisitos_dcl_instantanea

```text
cod_soporte_requisito_dcl string(20) PK
cod_requisito_dcl_instantanea FK -> tabla 11
H
orden integer
UNIQUE(requisito, H), UNIQUE(requisito, orden)
```

### 13. motivos_dcl_instantanea

```text
cod_motivo_dcl_instantanea string(20) PK
cod_clasificacion_dcl_instantanea FK -> tabla 10
motivo string(80)
orden integer
UNIQUE(clasificacion, motivo), UNIQUE(clasificacion, orden)
```

### 14. reglas_instantanea_experta

```text
cod_regla_instantanea string(20) PK
E
cod_regla_experta string(20) ? FK -> reglas_expertas
id_regla_dominio text
origen string(10): D137 | SIM
habilitada boolean
fuente text
concepto_consecuencia text
S(consecuencia)
orden integer
UNIQUE(E, id_regla_dominio), UNIQUE(E, orden)
```

D137 exige FK y versión coherentes. SIM exige identidad ficticia. Consecuencia
no admite NULL. Se conserva la regla original, aunque cambie después su fuente.

### 15. condiciones_regla_instantanea

```text
cod_condicion_instantanea string(20) PK
cod_regla_instantanea FK -> tabla 14
cod_condicion_padre string(20) ? self-FK
operador string(20)
variable text ?
S(literal)
orden integer
```

Una raíz por regla. Comparaciones tienen variable/literal y cero hijas; Y/O
tienen una o más hijas; NO tiene una. Sin ciclos, profundidad <= 32 ni padres
de otra regla. Esto conserva conocimiento declarativo, no datos clínicos EAV.

### 16. reglas_aplicadas_dcl_instantanea

```text
cod_regla_aplicada_dcl string(20) PK
cod_clasificacion_dcl_instantanea FK -> tabla 10
cod_regla_instantanea FK -> tabla 14
orden integer
UNIQUE(clasificacion, regla), UNIQUE(clasificacion, orden)
```

## Garantías de implementación exigidas

1. Guardar cognición en una transacción, completa o ninguna fila nueva.
2. Clasificación exige cognición previamente conservada e idéntica; no la crea
   como fallback. La escritura de clasificación tiene su propia transacción.
3. Reintento idéntico idempotente; misma identidad con contenido diferente se rechaza.
4. Hash canónico tipado de memoria/resultados, preservando orden, null/false/0,
   float exacto y zonas. Verificar integridad al leer, no usar el hash como dato.
5. Construir cada hecho una sola vez y compartirlo al reconstruir memoria,
   evidencia, perfiles y soportes; usar todos los constructores reales de Domain.
6. Fuentes, estados o escalares no representables se rechazan antes de escribir.
7. El episodio IADL ausente no se inventa; no se añade un residente para una FK.
8. Pruebas de lectura tras nueva instancia/proceso, rollback, tipos, fechas,
   idempotencia y coherencia cruzada en SQLite y PostgreSQL desechables.

Este alcance conserva exactamente VectorCognitivo/MemoriaTrabajo y
ResultadoClasificacionDcl, que son los objetos del puerto actual. No promete
reconstruir todo el paquete de conocimiento o las columnas de una fuente que
no fueron extraídas; tampoco persiste un InformeExpertoDTO como documento clínico.
