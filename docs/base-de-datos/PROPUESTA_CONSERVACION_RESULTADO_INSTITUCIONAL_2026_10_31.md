---
title: "Conservación del resultado institucional sin nuevas tablas"
status: REJECTED
last_reviewed: 2026-10-31
owner: ApoyoDecision
source_of_truth: false
---

# Decisión de la propietaria

El 31/10/2026, ante la propuesta de cinco FK/checks y del permiso de ejecución institucional, la propietaria respondió **«Mantener relaciones y permisos actuales»**. No se implementa ni instala esta transición; se conserva este documento para registrar el alcance rechazado y evitar volver a solicitarlo durante el mismo cierre. Las correcciones compatibles de código y pruebas continúan, sin modificar reglas, relaciones ni permisos.

## Propuesta no autorizada

Cerrar la conservación y recuperación del resultado institucional con el modelo existente. No modificar reglas, criterios clínicos ni puntuaciones. No crear tablas. La autorización solicitada se limita a los campos, relaciones, checks y permiso descritos a continuación; no autoriza otro rediseño ni borrado.

## Cabecera: una ejecución en evaluaciones_expertas

Se reutilizan PK, residente, versión cognitiva, personal solicitante, inicio/fin, UUID único en id_evaluacion_dominio, versión de conservación, fecha de corte y huella existentes.

| Campo nuevo | Tipo y contenido |
|---|---|
| cod_version_petersen | varchar(20) NULL; FK RESTRICT a versiones_modelo_experto.cod_version_modelo, indexada. |
| cod_usuario_ejecucion | varchar(20) NULL; FK RESTRICT a usuarios.cod_usuario, indexada; se deriva de sesión. |
| institucional_memoria, institucional_atencion, institucional_ejecutiva, institucional_lenguaje, institucional_visuoespacial | varchar(1) NULL; cada uno conserva A/P/U/I/X originales. |
| institucional_c01, institucional_c02, institucional_c03, institucional_c04, institucional_c05 | varchar(1) NULL; cada uno conserva S/N/U/I/X originales. C02 sigue siendo derivado. |
| institucional_resultado | varchar(80) NULL; código del resultado global original o abstención principal. |
| institucional_patron | varchar(10) NULL; patrón emitido por el motor, sin inferir otro. |
| institucional_explicacion | text NULL; informe narrativo original, sin JSON ni lista serializada. |
| institucional_ejecutor | char(64) NULL; identidad técnica del código que ejecutó la consulta. |

El contrato de conservación nuevo exige los estados, resultado, explicación, actor y versión Petersen presentes; los historiales anteriores mantienen todos estos campos NULL. Check de patrón restringido a los cuatro códigos existentes cuando esté presente. Inmutabilidad de cabecera y de sus fuentes conservadas después de finalizar.

Son atributos singulares de la ejecución, no colecciones. No se crean columnas de instrumentos, respuestas o reglas repetidas. La explicación es un documento narrativo; no se anuncia como una colección estructurada reconstruible.

## Hechos: tres relaciones de procedencia

Se amplía hechos_instantanea_experta con:

| Campo | FK RESTRICT e índice |
|---|---|
| cod_respuesta | respuestas_instrumento.cod_respuesta |
| cod_aplicacion | aplicaciones_instrumento.cod_aplicacion |
| cod_valoracion_petersen | valoraciones_petersen_originales.cod_valoracion_petersen |

Cada campo es varchar(20) NULL. Un hecho de respuesta tiene exclusivamente cod_respuesta; no duplica la aplicación que esta determina. Un hecho de contexto de aplicación tiene exclusivamente cod_aplicacion. Un juicio original tiene exclusivamente cod_valoracion_petersen. Se conservan autor, fecha, escalar original y orden mediante columnas existentes.

Los checks snap_origen y snap_episodio_original se amplían con esas tres ramas y XOR; preservan íntegramente las ramas anteriores. La pertenencia al mismo residente se comprueba también en la operación backend. No se relajan FK anteriores ni se admiten referencias textuales que sustituyan las nuevas FK.

## Reglas y condiciones

Reutilizar evaluacion_criterios, traza consolidada, evaluaciones_reglas, evaluaciones_condiciones_regla y relaciones de soporte existentes. Solo se registran nodos-criterio reales y reglas del paquete ejecutado; no se inventan nodos C01–C05. La regla Petersen usa DCL-PETERSEN y su versión propia. Los estados originales se conservan sin recalcular en la lectura histórica.

El alcance no es retener todo el DTO/configuración de adquisición para replay idéntico de catálogos arbitrarios. Es conservar el resultado original con sus hechos, reglas y explicación. Esa distinción aparece expresamente en la interfaz y en las pruebas.

## Permiso específico

Crear evaluaciones_expertas.ejecutar_institucional y asignarlo exclusivamente a MEDICO GENERAL/GERIATRA y PSICOLOGO/A. La Policy exige además cuenta ACTIVO, sin previsualización de rol, personal propio ACTIVO de MEDICINA/PSICOLOGIA, residente ADMITIDO, acceso al residente y permisos de lectura de todas las fuentes usadas. No concede ejecución a Gerencia, Administración, Superadministración, Enfermería ni Familia por rol.

Los permisos de captura original, revisión posterior y ejecución observacional no cambian. No hay envío automático ni prescripción. La revisión posterior sigue siendo un acto independiente, nunca un requisito para desarrollar o guardar el resultado técnico.

## Operación y pruebas

Acción explícita con UUID idempotente: autorizar → adquirir en corte consistente → reautorizar → escribir atómicamente → recuperar sin recalcular → comprobar huella → confirmar → auditoría mínima. Un fallo revierte la escritura y nunca muestra éxito. Historial y consultas reautorizan cada solicitud; no aceptan un payload clínico del navegador como resultado.

Pruebas requeridas: positivo, abstención, doble envío, falta de permiso, competencia/actor, IDOR, revocación, corrupción, rollback y lectura histórica después de cambios actuales; SQLite y PostgreSQL desechables. No se alteran reglas para pasar pruebas.

## Instalación y reversión

Migración por extensión explícita, sin ejecutarla contra la BDD operativa antes de autorización. La reversión debe rechazar retirar columnas si contienen resultados conservados. Respaldar antes de una instalación local autorizada y verificar inventario, FK e historia previa. Inventario esperado sin cambios: 106 tablas de negocio +16 técnicas.

Esta propuesta es revisable, pero todavía no constituye autorización de DDL ni del permiso. No se solicita estudio con residentes, firma ni aval externo.
