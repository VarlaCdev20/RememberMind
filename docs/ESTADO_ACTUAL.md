---
title: "Estado actual de RememberMind"
status: CURRENT
version: "3.0"
last_reviewed: 2026-11-04
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [observed_state]
verified_against_commit: 57285c7e3988cf5df86cdbd24f03e6a9650807bf
verification_scope: CURRENT_TREE_DOCUMENTATION_AND_REGISTERED_ROUTES
runtime_verified: false
---

# Estado actual de RememberMind

**Revisión: 04/11/2026, America/La_Paz.** Referencia de código: `57285c7e`, más los cambios locales existentes de Gerencia, avisos y Reportes. Esta revisión documental contrasta archivos, decisiones y rutas registradas. No ejecuta una aceptación integral del producto, no consulta historias clínicas ni vuelve a contar la BDD operativa.

## Organización comprobada

RememberMind organiza una residencia geriátrica mediante **12 módulos Laravel y 10 roles**. El motor experto es PHP independiente, integrado mediante `ApoyoDecision`. Los recuentos y fuentes reproducibles están en el [inventario actual](sistema/INVENTARIO_ACTUAL.md), las [responsabilidades de módulos](modulos/README.md) y el [catálogo documental](INDICE_DOCUMENTAL.md).

Flujo institucional: **preadmisión → revisión → aprobación/rechazo → admisión formal con cama → residente admitido → atención y cuidado por profesionales/turnos → seguimiento, alertas y pase de turno**. Aprobar una preadmisión no crea un residente. La cuenta, el personal y el contacto son entidades distintas.

| Área | Implementación o decisión localizada | Alcance pendiente o límite |
|---|---|---|
| Admisiones y Residentes | Revisión de preadmisión, formalización con cama, expediente, alojamiento y contactos | No sustituir el contrato por un alta directa; las diferencias de vocabulario físico se conservan en el registro de decisiones. |
| Identidad e Institucion | Cuentas, personal, estructura residencial, jornadas, asignaciones, documentación laboral y planificación | La presencia de rutas no certifica todos los escenarios de Gerencia. Cuenta y relación laboral se gestionan por separado. |
| Clinica y Valoraciones | Atenciones, notas, antecedentes, estudios, valoraciones, instrumentos, contexto de sesión y capturas originales | Cada flujo mantiene competencia, autoría, fecha clínica y acceso contextual. No se declara una aceptación global por disciplina. |
| Cuidados y Medicacion | Turno, residentes asignados, registros diarios, cuidados, pase de turno y agenda de dosis | Enfermería documenta/administra; la prescripción y su modificación conservan competencia médica. |
| Alertas y Social | Incidentes/alertas con continuidad; actividades, participación y visitas | La información debe conservar fuente y permisos. Una lectura no genera nuevos eventos clínicos. |
| Reportes | Consultas/exportaciones autorizadas, reportes de Enfermería por período, residente y áreas | Las ampliaciones locales y sus pruebas tienen alcance propio; no publican el expediente completo a Familia. |
| ApoyoDecision | Reglas, red semántica, adquisición, inferencia explicable, abstención, captura/puntuación instrumental y observaciones conservadas | La **conservación institucional conjunta instrumental/Petersen sigue pendiente**. Un DTO transitorio no equivale a un resultado guardado. |

## BDD y decisiones vigentes

Inventario documentado tras las extensiones autorizadas: **81 tablas operativas + 25 expertas = 106 de negocio; 16 técnicas; 122 físicas**. Los 70 del diccionario son el núcleo original. Los recuentos anteriores de 100/104 describen otros cortes. Los archivos Eloquent incluyen proyecciones/identidades de bloques consolidados; su número no es un inventario físico de tablas.

- [Prohibición de crear tablas adicionales, 27/10](base-de-datos/DECISION_SIN_NUEVAS_TABLAS_2026_10_27.md). Ampliar campos exige el alcance autorizado y preservar grano e integridad.
- [Gerencia35/37, 27/10](base-de-datos/DECISION_GERENCIA_DOCUMENTACION_HISTORIA_2026_10_27.md) y [aviso compartido instalado](base-de-datos/DECISION_AVISO_COMPARTIDO_CAMPOS_2026_10_27.md).
- [Escolaridad y lectoescritura, 28/10](base-de-datos/DECISION_CONTEXTO_ESCOLARIDAD_4FN_2026_10_28.md): tres campos por atención.
- [Idioma e informante principal, 29/10](base-de-datos/PROPUESTA_CONTEXTO_IDIOMA_INFORMANTE_2026_10_29.md): tres campos por atención y FK opcional autorizada al vínculo existente. Sin listas, JSON/EAV o backfill.
- [Conservación conjunta propuesta, 31/10](base-de-datos/PROPUESTA_CONSERVACION_RESULTADO_INSTITUCIONAL_2026_10_31.md): **rechazada en ese alcance**; no instalar sus FK ni su permiso como si estuvieran aprobados.

Fuentes rectoras: [índice BDD](base-de-datos/README.md), baseline y decisiones específicas. No se ejecutaron migraciones en esta actualización.

## Sistema experto al 04/11

- Seis componentes principales: `conocimiento`, `hechos`, `inferencia`, `explicacion`, `instrumentos` y `conservacion`; tipos de dominio y puertos auxiliares en `dominio` y `aplicacion`. No existe el envoltorio `src/motor_experto`.
- **24 reglas fuente:** 17 cognitivas, 4 Petersen y 3 observacionales. La BDD conserva versiones, revisiones y reglas registradas; ese recuento de filas puede diferir del catálogo fuente.
- **26 métodos instrumentales:** 23 por componentes y 3 perfiles iniciales descriptivos. MoCA 8.1 español, RUDAS-PE 2020 y FAQ B7S están activados localmente para captura de puntuaciones y cálculo descriptivo, con 35 campos de catálogo en conjunto. No reproducen estímulos ni generan automáticamente C02/C03 desde un total.
- La denominación de investigación fue retirada del producto en `57285c7e`. El importador documental y su respaldo automático en consultas están retirados. Solo se preservan identificadores históricos y evidencias necesarias para comprobar versiones y huellas; la interfaz usa etiquetas neutras.
- Las observaciones pueden conservarse y revisarse en su circuito. La preparación conjunta de dominios/Petersen, su explicación y replay técnico no acreditan guardado institucional conjunto.

Entradas: [mapa del código](../motor-experto/README.md), [índice experto](sistema-experto/README.md), [activación instrumental](sistema-experto/ENTREGA_ACTIVACION_PERFILES_INICIALES_2026_11_04.md) y [matriz de trazabilidad](sistema-experto/MATRIZ_TRAZABILIDAD_DCL.md).

Durante el desarrollo se implementa y prueba el comportamiento autorizado sin exigir estudios con residentes, firmas ni avales externos para continuar. No se fabrican mediciones, autorizaciones ni diagnósticos.

## Seguridad y correo

Toda operación sensible combina cuenta activa, permiso, Policy cuando corresponda, estado válido y contexto/competencia. El rol por sí solo no concede escritura clínica. Las rutas y botones deben conservar los mismos límites.

El remitente saliente protegido es **remembermind.health@gmail.com**. Credenciales exclusivamente en configuración secreta local. La [guía de correo](sistema/CORREO_SMTP_CONFIGURACION.md) diferencia sus comprobaciones fechadas de la disponibilidad actual: esta revisión no abre SMTP ni envía mensajes.

## Evidencia técnica y límites

La limpieza de nombres del 04/11 dejó evidencia acotada sobre su árbol de trabajo:

| Comprobación | Resultado de ese corte |
|---|---|
| Grupo unitario del experto | 1.151 casos:1.150 correctos y1 omitido por perfil PostgreSQL específico;15.116 aserciones. |
| Retirada documental / integración seleccionada |7 casos/191 aserciones y15 casos seleccionados correctos; no son toda la suite Feature. |
| Arquitectura seleccionada |4 casos/1.106 aserciones correctos. |
| PostgreSQL desechable, circuito inicial |12 casos/88 aserciones correctos, sin usar la BDD operativa. |
| JavaScript y navegador aislado |13 pruebas JS; recorridos del experto y de reglas correctos en 1440/1280/1024/768/390 px. |
| Build frontend | Correcto; aviso de tamaño de bundle preexistente. |

No sumar filtros solapados como cobertura única. Los logs de esa sesión permanecen en `output/retirada-nombre-*`, son evidencia local y no un artefacto garantizado en otra copia. La actualización documental no reejecutó esas suites ni acredita CI remoto, despliegue o aceptación integral.

La [auditoría del 25/10](auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) y el [estado anterior](historico/ESTADO_ANTERIOR_A_2026_11_04.md) conservan sus resultados. Sus hallazgos no se trasladan automáticamente como fallos abiertos actuales: las entregas posteriores resuelven alcances concretos. [Deuda](DEUDA_TECNICA.md), [decisiones](DECISIONES_PENDIENTES.md) y [roadmap](ROADMAP.md) separan los pendientes conocidos de los que requieren reproducción actual.
