---
title: "Extensión aprobada IADL — fuente descriptiva del experto"
status: APPROVED
version: "1.0"
last_reviewed: 2026-10-17
owner: RememberMind
source_of_truth: true
verification_scope: OWNER_APPROVED_SOURCE_SCHEMA_MODEL_ADAPTER
runtime_verified: false
---

# Decisión de la propietaria — 17/10/2026

En este chat la propietaria respondió «Sí, incorporar esa extensión y su
adaptador» a la propuesta de incorporar `controles_funcionales_iadl` y su Model
según PR #16, commit `195a39cdbe805580a11691e51c9403c04768308c`.

La aprobación comprende exclusivamente tabla, Model, lectura descriptiva y
pruebas desechables. No comprende fusionar aquella rama, pantallas, rutas,
permisos, competencias de captura o clasificación clínica. El inventario pasa
de 72 a **73 tablas operativas**. Las 23 tablas expertas D-137 no cambian.

## Diccionario aprobado

| Campo | Tipo | Relación o significado |
|---|---|---|
| cod_control_iadl | string(20), PK | Observación individual |
| cod_residente | string(20), obligatorio | FK residentes.cod_residente |
| cod_personal | string(20), obligatorio | FK personal.cod_personal, autor clínico |
| cod_usuario_registro | string(20), obligatorio | FK usuarios.cod_usuario, cuenta técnica |
| cod_atencion | string(20), nullable | FK atenciones.cod_atencion; mismo residente |
| cod_control_anterior | string(20), nullable | Self-FK cod_control_iadl; rectificación append-only |
| fecha_hora | datetime, obligatorio | Momento de observación/reporte |
| actividad | string(30), obligatorio | Actividad instrumental |
| oportunidad | string(25), obligatorio | Oportunidad de observación |
| desempeno | string(25), obligatorio | Desempeño descriptivo |
| tipo_ayuda | string(30), obligatorio | Ayuda proporcionada |
| causa_ayuda | string(30), obligatorio | Causa declarada, sin inferir causalidad |
| nivel_previo | string(25), obligatorio | Antecedente funcional |
| fuente | string(25), obligatorio | Procedencia de la observación |
| estado | string(20), default REGISTRADO | REGISTRADO / RECTIFICACION |
| observacion | text, nullable | Texto original; no acredita C03 |
| registrado_en | datetime, obligatorio | Fecha técnica separada de la clínica |

FK restrictivas. Índices por residente/fecha, residente/actividad/fecha y otras
FK. Sin cascadas, JSON/EAV o timestamps adicionales.

Catálogos cerrados conservados del contrato inspeccionado:

- Actividad: MEDICACION, PERTENENCIAS, RUTINAS, CITAS, COMUNICACION, FINANZAS, COMPRAS, TRANSPORTE.
- Oportunidad: OBSERVADA, REFERIDA, SIN_OPORTUNIDAD, NO_HABITUAL.
- Desempeño: INDEPENDIENTE, CON_APOYO, NO_EVALUABLE.
- Ayuda: NINGUNA, RECORDATORIO, SUPERVISION, SUSTITUCION, NO_APLICA.
- Causa: NINGUNA, COGNITIVA, FISICA, SENSORIAL, INSTITUCIONAL, MIXTA, NO_DETERMINADA, NO_APLICA.
- Nivel previo: INDEPENDIENTE, CON_APOYO, NO_HABITUAL, DESCONOCIDO.
- Fuente: OBSERVACION_DIRECTA, RESIDENTE, FAMILIAR, EQUIPO_CUIDADOS.

Se usa el generador correlativo técnico existente con prefijo IAD. No cambia
ningún código anterior ni se incorpora un segundo generador.

## Límites de esta integración

El adaptador conserva la guarda testing/base desechable; no abre acceso
institucional por habilitar la tabla. Lee IDs explícitos del mismo residente
hasta el corte y devuelve datos originales. Sin atención vinculada conserva
NULL; no fabrica un episodio para MemoriaTrabajo.

OBSERVADA/REFERIDA no equivalen a REAL_Y_ADECUADA; COGNITIVA no equivale a una
atribución cognitiva acreditada o sintética. C03 exige revisión independiente.
No se sustituyen IADL por BADL ni se incorporan escalas o baremos.

El propietario funcional es Valoraciones, según el contrato de captura de PR #16.
El Model permanece transitoriamente en App\Models, como las demás identidades
Eloquent; el consumidor experto no cambia esa propiedad institucional.

En la fecha de esta decisión inicial, la conservación íntegra de memoria/DCL
seguía pendiente. La instrucción posterior aprobó la alternativa de nueve
tablas: véase [Conservación experta V2](DECISION_CONSERVACION_EXPERTA_V2_2026_10_17.md).
La [integración local posterior](../sistema-experto/SIMULACION_LOCAL_2026_10_17.md)
aplicó ambas migraciones a la base de desarrollo confirmada y cargó 160
observaciones IADL ficticias para comprobar los cinco casos autorizados.
Esta evidencia no habilita captura o clasificación asistencial.
