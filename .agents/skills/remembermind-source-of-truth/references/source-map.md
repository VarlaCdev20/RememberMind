# Mapa de fuentes y conflictos

Fecha de revisión: 2026-10-06. Catálogo de descubrimiento, no copia de los contratos. Verificar estado/alcance y decisiones posteriores antes de cada tarea.

## Resolución

1. Leer instrucción actual de la propietaria y [AGENTS raíz](../../../../AGENTS.md), más AGENTS de carpetas afectadas.
2. Consultar [índice general](../../../../docs/README.md), [índice BDD](../../../../docs/base-de-datos/README.md) y [arquitectura](../../../../docs/arquitectura/README.md).
3. Para estructura: [baseline](../../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](../../../../docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md) y decisiones explícitamente aprobadas aplicables. Una aprobación posterior de la propietaria modifica exclusivamente su alcance. Descubrir nuevas decisiones; no perpetuar un contador.
4. Para comportamiento: documentación funcional vigente del área. Código V2 muestra implementación; tests muestran evidencia ejecutable y pueden estar desactualizados.
5. README auxiliar e historia no reemplazan esas fuentes. Si falta documentación funcional, identificar el hueco; el código por sí solo no autoriza una regla sensible nueva.
6. Registrar fuente, sección/línea, fecha/versión, estado, ámbito y motivo de clasificación. La clasificación puede ser parcial dentro de un documento.

## Catálogo por área

| Fuente o familia | Estado revisado | Alcance y cautela |
|---|---|---|
| AGENTS raíz/app/database/resources/tests | VIGENTE | Contratos del proyecto por ámbito. |
| docs/README.md y arquitectura/README.md | VIGENTE | Navegación y organización canónica. Índices también pueden contener enlaces obsoletos. |
| REMEMBERMIND_BASELINE_TECNICO.md | VIGENTE | Stack y motores soportados; contrastar locks para versión realmente instalada. PostgreSQL integrado; SQLite rápido; MySQL/MariaDB fuera del baseline. |
| REMEMBERMIND_ROLES_BASELINE_CONGELADO.md | VIGENTE | Competencias y límites por rol; no otorgar permisos desde una lista conceptual. |
| PERMISOS_TEMPORALES_SUPERADMIN.md | VIGENTE, excepción acotada | Solo entorno/flag/identidad y condiciones allí definidos; no autorización clínica permanente. |
| DASHBOARDS_POR_ROL.md | VIGENTE para contrato específico | Algunas propuestas/expectativas necesitan contraste con límites actuales y código. |
| REMEMBERMIND_ARQUITECTURA_FRONTEND.md | CONFLICTIVA parcialmente | Conservar intención pertinente; carpetas Features/Pages u otras prescripciones divergentes no prevalecen sobre arquitectura/AGENTS actuales. |
| Baseline congelado y diccionario físico | VIGENTE con extensiones aprobadas | Autoridad de estructura y terminología. Los valores físicos de estado requieren resolver divergencias puntuales. |
| DECISION_ARQUITECTURA_BDD_V2_1.md | VIGENTE aprobada | Normalización y autor clínico frente a actor técnico. |
| DECISION_UNIDAD_TALLA_CM.md | VIGENTE aprobada | Unidad y migración de talla; no convertir silenciosamente datos ambiguos. |
| DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md | VIGENTE aprobada | Extensión versionada de objetivos; validación PostgreSQL pendiente según documento. |
| AUDITORIA_FORMULARIO_* | VIGENTE como evidencia localizada / PROPUESTA en apartados señalados | No autoriza columnas o umbrales propuestos. |
| AUDITORIA_FORMULARIO_SIGNOS_VITALES: objetivo “propuesto”/neutralidad anterior | DEPRECADA parcialmente | Sustituida en ese alcance por decisión V2.2; otras partes siguen siendo evidencia contextual. |
| SEEDERS_LOCALES.md | VIGENTE auxiliar | Instrucción local, no prueba de esquema actual ni permiso para resetear datos. |
| REMEMBERMIND_BDD_69_TABLAS.md, INVENTARIO_MIGRACION_APLICACION.md, RESULTADO_MIGRACION_APLICACION_V2.md | HISTÓRICA | Snapshots de transición; no reintroducir V1. |
| docs/architecture-audit/* y docs/refactorizacion-total/* | HISTÓRICA | Inventarios, opciones y arquitectura anterior. |
| docs/auditoria.md y auditoria_roles_permisos.md | HISTÓRICA | Snapshot V1/inventarios anteriores; no matriz vigente. |
| CONTRATO_VISUAL_UX_UI.md y AUDITORIA_SKILLS_UX_UI.md | VIGENTE | Contrato/selección visual; no método clínico. |
| STACK_SKILLS_UX_UI.md | VIGENTE para guía UX, CONFLICTIVA por pegado preexistente | Texto de tarea de sistema insertado en cambios locales: preservar; usar instrucción actual para alcance de skills, no como protocolo aprobado. |
| PLAN_UNIFICACION_VISUAL.md y FASE_9*, FASE_11* | HISTÓRICA como evidencia de entregas; contratos puntuales a contrastar | Fotografías de implementación y limitaciones, no nuevas reglas clínicas. |
| PENDIENTE_BADGES_ESTADO_RESIDENTE.md | NO RESUELTA la clasificación clínica; VIGENTE su restricción | No inferir estabilidad por ausencia de alertas. |
| docs/produccion/DESPLIEGUE_SEGURO.md | VIGENTE auxiliar; conteo anterior DEPRECADO | Operación segura del entorno; actualizar alcance por decisiones posteriores, no ejecutar despliegue por leerlo. |
| docs/sistema-experto/README.md | VIGENTE para estado de propuesta | No existe aprobación clínica por declarar una intención académica. |
| Tres diseños en docs/sistema-experto/ | PROPUESTA; CONFLICTIVA ubicación/arquitectura vieja | Árboles/arquitectura futura, no reglas ejecutables aprobadas. |
| RM-EXPERT-001 en baseline | PROPUESTA para aprobación formal / DOCUMENTAL | No fingir aprobación del sistema experto por estar en el baseline. |
| Pesos, umbrales, catálogos abiertos, PRN/reintentos, licencias de instrumentos | NO RESUELTA donde no haya decisión concreta | Aislar requisito, ofrecer opciones; continuar trabajo técnico independiente. |

## SOURCE OF TRUTH

Emitir al iniciar trabajo que requiere reconstruir contrato:

```text
SOURCE OF TRUTH
Área:
Documentos vigentes: ruta + sección + versión/decisión + alcance
Código relevante: rutas y operación; evidencia de implementación
Tests relevantes: rutas + propiedad demostrada; ejecutados/no ejecutados
Documentación histórica ignorada: rutas + razón
Conflictos: ninguno / lista con alcance y resolución pendiente
Contrato que se aplicará: actores, operación, estados, invariantes y límites
```

## SOURCE CONFLICT

```text
SOURCE CONFLICT
Documento A: ruta, sección/línea, estado, afirmación
Documento B: ruta, sección/línea, estado, afirmación
Conflicto: diferencia específica
Impacto: operación/entidades/usuarios afectados
Decisión necesaria: autoridad que resuelve / cuestión concreta pendiente
```

Si el conflicto involucra código, señalarlo como evidencia B, sin convertirlo en documentación normativa. Aplicar una autoridad superior explícita cuando resuelve el punto y registrar resolución. Si permanece una decisión institucional o estructural, detener solo el trabajo dependiente y continuar el independiente.

Ejemplo real: baseline describe residente ACTIVO y preadmisión PENDIENTE/APROBADA/RECHAZADA; FormalizarAdmision persiste ADMITIDO/ADMITIDA. AGENTS actual exige condición institucional ADMITIDO, pero eso no determina silenciosamente todo catálogo físico. Identificar campo y transición antes de corregir. No mezclar vocabularios ni “solucionar” cambiando el esquema congelado.
