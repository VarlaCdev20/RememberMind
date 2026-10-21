# Ensayo de unión tipada de vínculos de evidencia

> El máximo vigente posterior es **104** tablas de negocio. Un [ensayo físico en SQLite desechable](../../prototipos/orion-consolidacion-104/ensayar-vinculos-sqlite.php) añadió evidencia sintética de transición, reconstrucción, FK/checks y rollback. No acredita migración poblada PostgreSQL ni autoriza alterar la BDD.

Fecha: 20/10/2026. Estado: **candidato de solo lectura; no aprobado para DDL**.

## Grano y motivo

`evidencias_criterio_evaluacion` conserva una participación de evidencia o
hecho en un criterio. `evidencias_soporte_condicion` conserva el soporte de una
condición evaluada. Ambas son afirmaciones independientes y multiplicables:
una evidencia puede participar en un criterio y respaldar varias condiciones.
La propuesta mantiene **una fila por vínculo**, con dos destinos tipados y
exclusivos. No convierte esos soportes en columnas repetidas ni en JSON/EAV.

Inventario de solo lectura en `remembermind_dev`: 225 participaciones (150
por `cod_evidencia_evaluacion` y 75 por `cod_hecho_instantanea`) y 150 soportes
de condición. Ninguna de las dos tablas tiene FK entrantes. El
[`ensayo reproducible`](../../prototipos/orion-reduccion-100/ensayar-vinculos-evidencia.php)
proyecta las 375 filas en dos variantes nominales y reconstruye exactamente
todas las columnas y NULL actuales; rechaza referencias ausentes. También
rechaza ejecutarse fuera de una base local de desarrollo o desechable.

## Diccionario candidato, sujeto a prototipo físico

Destino: `evidencias_criterio_evaluacion`. Se conservaría su PK física
`cod_evidencia_criterio`, generando un código nuevo para cada soporte migrado.
El identificador histórico del soporte permanecería en
`cod_evidencia_soporte_condicion` como alias único nullable. El destino de
condición ocuparía `cod_evaluacion_condicion` nullable con FK a
`evaluaciones_condiciones_regla`; el destino actual
`cod_evaluacion_criterio` pasaría a nullable. La evidencia de origen
`cod_evidencia_evaluacion` seguirá siendo FK; la rama existente de hecho
`cod_hecho_instantanea` y su alias `cod_soporte_sintetico` no cambian.

| Variante | Criterio | Condición | Alias soporte | Origen | Atributos propios |
|---|---|---|---|---|---|
| Participación | presente | NULL | NULL | evidencia XOR hecho | rol, estado y fecha presentes; justificación y orden conforme al contrato actual |
| Soporte | NULL | presente | presente | evidencia presente; hecho NULL | rol, estado, justificación, fecha y orden NULL |

Se necesitarían `UNIQUE(cod_evaluacion_condicion,
cod_evidencia_evaluacion)` y `UNIQUE(cod_evidencia_soporte_condicion)`, además
de checks de rama, FK y los índices de consulta. Los UNIQUE existentes para la
participación deben conservar su significado. El código original del soporte
no se reutiliza ciegamente como PK nueva, aunque los códigos actuales no
colisionen; la secuencia de la tabla destino debe reservar una identidad
nueva y guardar el alias.

## Transición y reversión exigidas

En base desechable: bloquear escritores; ampliar columnas y nulabilidad;
insertar soportes con códigos nuevos y alias antiguos; cotejar cada fila
original y cada NULL; redirigir lectores, escritores, Model y catálogo de
códigos; comprobar cero consumidores antiguos; retirar la tabla solo después
de aprobar el cotejo. La reversión debe reconstruir soportes desde los alias,
comparar contenido y fallar si encuentra una variante nueva no representable.
Se requieren pruebas PostgreSQL y SQLite, FK negativas, ausencia, doble
soporte, concurrencia y conservación de los 75 vínculos por hecho.

Esta comprobación **no** acredita normalización física, rendimiento, carrera
de escritores ni reversión. Una eventual fusión reduciría 104 a 103 tablas de
negocio; por sí sola no alcanza el máximo institucional de 100 ni autoriza
modificar la BDD congelada.
