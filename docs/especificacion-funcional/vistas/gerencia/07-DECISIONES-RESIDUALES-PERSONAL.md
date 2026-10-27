---
title: "Gerencia: decisiones residuales de Personal y documentación"
status: CURRENT
version: "1.1"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
verification_scope: OWNER_RESPONSES_RECORDED_NOT_RUNTIME
runtime_verified: false
---

# Decisiones residuales de Personal

Las decisiones 1–29 permanecen adoptadas. Estas tres preguntas proceden del contraste de catálogos y operaciones reales. La propietaria respondió todas el 25/10/2026: **R01A con solicitud de documentación por rol; R02A; R03A**. Se conservan las alternativas como historia; gobierna la matriz final. No se instalan permisos ni se cambia estructura al registrar estas decisiones.

## R01. Documentos adicionales requeridos

El mínimo inmediato está ratificado: cédula, fotografía, CV, título para las seis profesiones y matrícula para Medicina. El plazo se aplica a otros requeridos diferibles, pero no se ha ratificado cuáles son. Los catálogos actuales discrepan; [comparación completa](05-DOCUMENTACION-LABORAL.md#2-catálogo-autoridad-y-divergencias).

- **A:** solo el mínimo inmediato queda obligatorio por ahora; los respaldos adicionales se mantienen opcionales hasta una decisión por función. No se inventan documentos en mora para llenar un checklist.
- **B:** además del mínimo, ficha institucional, contrato/prestación, confidencialidad y acta de funciones quedan requeridos con plazo de siete días; otros respaldos son opcionales salvo decisión específica posterior.
- **C:** definir por documento y función qué es requerido con plazo, condicional u opcional. Puede indicarse una lista propia; los requisitos inmediatos ya ratificados no se cambian por omisión.

Los documentos que la institución emite requieren identificar su responsable; no se atribuye al trabajador una falta causada por emisión institucional pendiente. La clasificación de requisitos del sistema no acredita cumplimiento legal ni exige pruebas médicas/ocupacionales por inferencia.

## R02. Anulación documental por Gerencia

Ya se aprobó cargar y revisar, incluida la corrección con historia. Existe además un método de anulación en el servicio, pero no equivale a una facultad concedida por su presencia en código.

- **A:** Gerencia puede anular un documento laboral erróneo dentro de su alcance, con motivo y registro de actor/fecha; conserva archivo e historial. No puede borrar ni anular registros clínicos por esta decisión.
- **B:** Gerencia valida, observa y recibe sustituciones; la anulación queda reservada al proceso autorizado de Superadministración.

«Anular» deja sin vigencia un registro; «sustituir» crea un sucesor corregido. Ninguna opción admite borrar el archivo anterior para ocultarlo.

## R03. Operación de baja laboral

La edición de datos y suspensión/activación de acceso ya están aprobadas. La Action acepta estado_personal y el panel usa un toggle de cuenta como si fuera estado laboral. Falta concretar la operación y su continuidad sobre planificación, sin introducir reglas laborales externas.

- **A:** incluir una operación propia de Gerencia para pasar al trabajador a INACTIVO o reactivarlo, con motivo y fecha. Su cuenta se gestiona por separado; Administración revisa las asignaciones afectadas mediante su proceso. No se eliminan relaciones o registros pasados automáticamente. Los efectos concretos se formalizan y prueban antes de habilitarla.
- **B:** mantener en este alcance consulta del estado laboral y gestión de acceso ya aprobada; documentar la baja laboral y su continuidad como flujo posterior separado. No mostrar un botón de baja que solo suspende la cuenta.

Esta pregunta no concede cambios a prescripciones, atención clínica o historia del profesional. Estado laboral y disponibilidad de la cuenta deben seguir siendo hechos distintos.

## Pendientes técnicos que no son otra pregunta de negocio

- Representación relacional de fecha de alta, plazo/prórroga, cargador/hora y cambio inicial obligatorio: inspección adicional y propuesta concreta si el esquema no alcanza. No pedir aprobación genérica de columnas aún no diseñadas.
- Conciliación de códigos y consumidores de los tres catálogos; conservar historia. No cambiar códigos congelados o FK sin la autorización específica aplicable.
- Validaciones, formatos y tamaño de archivo, transacciones, ownership, foco, mensajes y pruebas se resuelven técnicamente dentro del alcance acordado, documentando las diferencias reales.
- Observación tardía no borra entrega original ni concede plazo nuevo automáticamente; se conserva el límite original/prorrogado y el seguimiento manual aprobado. No se infiere una segunda prórroga por cambiar de archivo.

## Respuestas adoptadas el 25/10/2026

Respuesta literal recibida, en orden R01/R02/R03:

```text
A QUE HAYA UN CAMPO PARA AGREGAR ALGUN TIPO DE PEDIDA DE DOCUMENTACION POR ROL
A
A
```

| Decisión | Elección | Alcance adoptado |
|---|---|---|
| R01 | A con extensión | Mantener el mínimo inmediato obligatorio; los demás respaldos no son obligatorios por defecto. Incorporar un campo/formulario para solicitar documentación según el rol laboral, con tipo/nombre e indicaciones y destinatarios de su alcance. No adoptar el paquete obligatorio de R01B |
| R02 | A | Gerencia anula documentos laborales erróneos de su alcance con motivo, autor/fecha e historia, sin borrado ni facultad sobre documentos clínicos |
| R03 | A | Gerencia cambia estado laboral ACTIVO/INACTIVO con motivo y fecha; cuenta separada y revisión de asignaciones afectadas por Administración, sin borrar relaciones o pasado |

**R01–R03: ADOPTADAS FUNCIONALMENTE; implementación y pruebas pendientes.** El formulario de [solicitud documental por rol](08-SOLICITUD-DOCUMENTAL-POR-ROL.md) desarrolla la extensión de R01. Solicitar documentación adicional no cambia por sí solo el mínimo inmediato ni acredita competencia profesional. La aplicabilidad temporal, destinatarios y fecha límite de cada petición deben quedar explícitos antes de emitirla; no atribuir automáticamente mora retroactiva a personal existente.

Siguen pendientes las soluciones técnicas y, si corresponden, propuestas estructurales concretas descritas arriba. La aprobación del campo de solicitud es funcional: no se interpreta como aprobación de tablas, columnas, FK o catálogos físicos aún no diseñados.
