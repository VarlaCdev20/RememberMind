---
title: "Gerencia: contrato de documentación laboral"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
verification_scope: APPROVED_DECISIONS_AND_STATIC_DOCUMENT_LIFECYCLE_REVIEW
runtime_verified: false
---

# Documentación laboral

**Implementación posterior al corte de especificación:** el [informe39](39-ACEPTACION-REGRESIONES-Y-CASOS.md) resuelve la proyección de fotografía FOTO/FOTO_DOC sin renombrar catálogo ni historia y distingue entrega puntual observada tarde de entrega tardía. Las incompatibilidades G04 y P30 descritas más abajo conservan el hallazgo inicial; su corrección concreta está trazada en ese informe. La unicidad de prórroga por alta frente a destinatario continúa pendiente de conciliación, sin instalación institucional de35/37.

## 1. Objetivo y recorrido

Dar seguimiento a documentos reales de un trabajador, desde entrega y revisión hasta corrección, fecha límite y única prórroga. [Decisiones aprobadas](02-PERSONAL.md#12-decisión-registrada-documentación-pendiente-con-plazo), [campos y acciones](04-FICHA-Y-OPERACIONES-PERSONAL.md) y [aceptación](06-ACEPTACION-PERSONAL-DOCUMENTOS.md).

Personal → trabajador → Documentación → seleccionar requisito → cargar archivo → CARGADO → Gerencia valida u observa con motivo → corrección mediante nueva versión → historial. El trabajador entra por documentación propia desde su perfil; la ruta específica aún debe integrarse. No crear un segundo directorio documental.

Subir, generar, firmar y validar son actos diferentes. Un contrato generado no es un contrato firmado. La fotografía de cuenta no acredita por sí sola la entrega del requisito de fotografía.

## 2. Catálogo: autoridad y divergencias

Fuentes observadas: [catálogo de alta](../../../../app/Modulos/Identidad/Dominio/Reglas/DocumentosPersonalInstitucional.php), [checklist y operaciones de ficha](../../../../app/Modulos/Identidad/Aplicacion/Acciones/DocumentacionUsuarioService.php) y [paquete documental](../../../../app/Compartido/Infraestructura/Documentos/DocumentosUsuarioService.php). Hay tres definiciones diferentes; no se considera aprobado el conjunto unión de todos los documentos porque aparezcan en código.

**R01A adoptada el 25/10/2026, con extensión de la propietaria:** solo el mínimo inmediato queda obligatorio por defecto. Los respaldos adicionales de la tabla son opcionales hasta una solicitud expresa aplicable por rol; no se adopta automáticamente el paquete de contrato/confidencialidad/funciones de R01B. Incorporar [solicitud documental por rol](08-SOLICITUD-DOCUMENTAL-POR-ROL.md). Las diferencias de catálogo listadas siguen siendo evidencia técnica; ya no son una pregunta pendiente de obligatoriedad general.

| Requisito visible | Rol/aplicabilidad | Decisión vigente | Diferencia o pendiente |
|---|---|---|---|
| Cédula de identidad | Siete roles laborales | Obligatorio al finalizar alta | Archivo y número de documento son datos distintos |
| Fotografía actual | Siete roles laborales | Obligatorio al finalizar alta | Alta usa FOTO_DOC; checklist usa FOTO; no se renombra catálogo o historia sin autorización |
| Hoja de vida/CV | Siete roles laborales | Obligatorio al finalizar alta | No sustituir por una observación sin archivo |
| Título o certificado de formación | Seis profesiones | Obligatorio al finalizar alta | No se exige a Administración por esta decisión |
| Matrícula profesional | Medicina | Obligatorio al finalizar alta | Número de matrícula y archivo deben distinguirse |
| Matrícula profesional | Enfermería, Psicología, Nutrición, Fisioterapia | Adicional opcional por defecto según R01A; puede solicitarse expresamente | Checklist también la ofrece a Pedagogía, alta no; conciliar aplicabilidad sin fabricar requisitos |
| Certificado de especialidad | Medicina cuando se declara especialidad | Adicional opcional por defecto según R01A, con posible solicitud contextual | No identificar especialista por el nombre del rol conjunto |
| Comprobante de domicilio | Catálogo de alta | Adicional opcional por defecto según R01A | Solicitud expresa si corresponde |
| Certificados de experiencia | Catálogo de alta | Adicional opcional por defecto según R01A | No exigir experiencia inexistente |
| Capacitaciones | Catálogo de alta | Adicional opcional por defecto según R01A | No inventar cursos exigidos |
| Antecedentes | Catálogo de alta/paquete | Adicional opcional por defecto según R01A | Una solicitud no se presenta como obligación legal investigada |
| Cartas de recomendación | Catálogo de alta | Identificado como opcional | No generar mora por ausencia |
| Otros respaldos | Catálogo de alta | Identificado como opcional | No convertir tipo genérico en almacenamiento de reglas o plazos |
| Ficha institucional | Generada por el sistema según catálogo | No obligatoria por defecto en R01A | Distinguir emisión, firma y revisión; solicitud no implica emisión realizada |
| Contrato laboral/prestación | Catálogo institucional | No obligatorio por defecto en R01A para este checklist | No se adopta R01B ni se declara dispensada ninguna obligación legal externa |
| Confidencialidad | Catálogo institucional | No obligatoria por defecto en R01A para este checklist | La elección funcional no sustituye el resguardo de información |
| Acta de funciones y horario | Catálogo institucional | No obligatoria por defecto en R01A para este checklist | No sustituye planificación/relaciones de asignación |
| Registro SEDES, salud ocupacional, vacunas y contacto de emergencia | Solo aparecen en el paquete o su descripción | Fuera del mínimo obligatorio ratificado | No importarlos automáticamente al catálogo ni inferir obligación legal |

La propietaria aprobó siete días para los **otros requeridos que admitan plazo**, no que todo documento mencionado en un catálogo sea obligatorio. R01A conserva los adicionales como opcionales por defecto y permite solicitudes por rol expresas, con aplicabilidad y fecha límite visibles antes de emitir. No alterar códigos del catálogo congelado ni duplicar requisitos como FOTO/FOTO_DOC para aparentar entregas faltantes.

## 3. Pantalla, columnas y campos

Cabecera: trabajador, función y estado de cuenta; resumen separado de requeridos entregados, requeridos por entregar, pendientes de revisión y observados. Si no hay requisitos definidos para una función, mostrar «Requisitos por definir», no 100 % completo.

Tabla principal: nombre de requisito, condición (inmediato/diferible/opcional/condicional cuando se defina), entrega, revisión, vigencia del archivo cuando corresponda, última actuación conocida y acciones autorizadas. «Entrega vencida» no equivale a «Documento caducado». Filtrar por situación documental y buscar por nombre; vista no necesita una gráfica porcentual para comprender el expediente.

Detalle de requisito: archivo actual, historial de sucesores, metadatos comprobables, revisor y motivo de observación. El tipo se obtiene del catálogo aplicado; no aceptar un código arbitrario enviado desde el navegador.

| Campo de carga/revisión | Fuente y regla | Persistencia actual |
|---|---|---|
| Titular | Contexto autenticado propio o target autorizado por Gerencia | documentos.cod_usuario: titular; no usar como autor de carga |
| Tipo y nombre | Catálogo aplicable | tipo_documento, nombre |
| Archivo | Selección y validación técnica antes de guardar | ruta_archivo, tipo_archivo, hash_archivo; bytes mediante almacenamiento existente |
| Emisión | Fecha original cuando el documento la aporta | No hay fecha_emision en esta migración; servicio la concatena en observacion. No afirmar preservación nominal |
| Caducidad del documento | Solo cuando aplica, coherente con emisión | fecha_vencimiento; no reutilizar para el límite de entrega |
| Observación del aportante | Texto pertinente, sin secretos | observacion; no introducir estructura de plazos |
| Autor/hora de carga | Sesión y reloj del servidor | Sin FK/hora específicas de carga en documentos; texto «Subido por» y actividad actuales no completan ese contrato relacional |
| Revisor y fecha de validación | Sesión de Gerencia autorizada | cod_usuario_validacion, fecha_validacion; no son fecha/autor de carga |
| Motivo de observación | Usuario revisor, concreto y accionable | Servicio concatena texto y audita; revisión histórica completa por contrastar |
| Antecesor | Documento anterior del mismo titular/tipo | cod_documento_anterior con FK; preservar archivo anterior |

## 4. Estados y transiciones

Son estados de presentación y operación observada; no se crean nuevos valores SQL por este documento.

| Situación | Significado | Acción prevista o restricción |
|---|---|---|
| Sin entrega / PENDIENTE visual | No existe archivo vigente para el requisito | Cargar; no insertar documento sin ruta para simular entrega |
| CARGADO | Archivo recibido, todavía no validado | Gerencia valida u observa; no contabilizar como validado |
| VALIDADO | Revisión positiva registrada | Conservar revisor/fecha y cadena al sustituir |
| OBSERVADO | Gerencia señala un problema con motivo | Nueva entrega vinculada; no sobrescribir el archivo observado |
| REEMPLAZADO | Existe sucesor del mismo titular/tipo | Historial; impedir nueva validación del antecesor |
| ANULADO / variante histórica ANULADA | Registro no vigente | R02A aprueba anulación laboral por Gerencia con motivo/autor/fecha y scope; no borrar archivo/historia ni anular clínica |
| VENCIDO observado en checklist | Estado de documento que requiere interpretación de vigencia | No emplearlo para cualquier falta de entrega ni mezclar ambos conceptos |
| Plazo de entrega vencido | Fecha límite superada y requeridos sin entregar | Avisar, seguimiento de Gerencia, sin suspensión automática |

No añadir RECHAZADO porque la interfaz use «rechazo» para capturar un motivo: el servicio actual escribe OBSERVADO. Si se requiere una decisión irreversible distinta, debe formalizarse y autorizarse específicamente.

## 5. Plazo, recordatorios y prórroga

- Fecha de alta técnica, con zona institucional America/La_Paz, es el origen; no fecha_ingreso laboral ni instante de abrir la pantalla.
- Fecha límite inicial = fecha del alta + siete días calendario. El día límite sigue admitiendo entrega; el vencimiento se evalúa una vez superada esa fecha local, sin anticiparlo por zona UTC. Detalle técnico de ejecución se probará al implementar.
- Recordatorio interno dos días antes de la fecha límite y aviso al vencer para trabajador y Gerencia, únicamente por requeridos sin entregar. No repetir el mismo aviso por cada render/reintento.
- Gerencia puede conceder una sola prórroga de siete días añadidos al límite original: total de catorce días desde el alta. Conservar motivo, actor, fecha y fechas límite anterior/nueva. Una segunda solicitud se rechaza; no crear otra alta para reiniciar plazo.
- El historial conserva la mora anterior; el seguimiento actual respeta la nueva fecha. La prórroga requiere confirmación; no la concede automáticamente un job.
- El vencimiento del plazo original o prorrogado no suspende acceso ni da de baja laboral. Gerencia puede actuar manualmente dentro de la autorización aprobada.
- No responsabilizar al trabajador por el tiempo que un archivo entregado permanece sin revisión. Una observación que exige nueva entrega debe informar motivo y plazo vigente; no borra la entrega original, reinicia el plazo ni concede automáticamente otra prórroga. Si el límite ya pasó, el caso sigue bajo seguimiento manual de Gerencia, sin suspensión automática.

Ejemplo ficticio de calendario para pruebas: alta 01/11/2026 → límite 08/11 → recordatorio 06/11 → mora desde 09/11. Prórroga concedida → nuevo límite 15/11, recordatorio 13/11 y mora desde 16/11. Son fechas de escenario técnico, no datos de trabajadores reales.

## 6. Seguridad, archivos y consistencia

El trabajador carga solo documentos propios; Gerencia carga y revisa targets autorizados. Comprobar el titular esperado también en consulta, descarga, carga, revisión, sustitución y cualquier reintento Livewire. Ni un permiso documentos.gestionar amplio ni el identificador de la ficha reemplazan el scope aprobado.

No divulgar rutas de almacenamiento, metadatos de otras personas o credenciales. Archivo validado por tipo/tamaño real; no confiar en extensión. Conservar hash técnico, no pedirlo al operador. Reutilizar el contrato de almacenamiento; no mover documentos privados a una URL pública para una vista previa.

Guardar archivo/documento/cadena/actividad de manera consistente: si falla SQL, compensar únicamente el archivo nuevo; si falla envío externo posterior, informar fallo separado sin duplicar alta. Concurrencia sobre mismo titular/tipo no puede crear dos sucesores vigentes incompatibles. Los bloqueos reales requieren prueba PostgreSQL al implementar.

Los límites actuales difieren: alta 5 MB y DOC/DOCX además de PDF/imágenes; ficha 10 MB y solo PDF/JPG/JPEG/PNG. Unificar por decisión técnica comprobada, sin prometer formatos admitidos que un consumidor rechaza; documentar el contrato elegido al implementar.

## 7. Brechas estructurales y de integración

| Brecha | Evidencia | Impacto y tratamiento |
|---|---|---|
| G01: alta/plazo/prórroga sin destino nominal completo | Usuarios sin timestamps; personal con fecha_ingreso laboral; documentos con caducidad/validación | No hay prueba de entidad de seguimiento. Analizar solución normalizada existente o propuesta específica antes de migrar |
| G02: autoría y fecha de carga | Documentos no tiene FK de cargador ni instante de carga; servicios añaden actor en texto | No afirmar autoría relacional completa. No rellenar historia desconocida ni convertir observacion en EAV |
| G03: cambio inicial obligatorio | Columna/estado propio no identificado en usuarios; Action rechaza forzar_cambio_password | Diseñar soporte seguro en identidad y comprobar si requiere extensión aprobada |
| G04: tres catálogos divergentes | Alta FOTO_DOC, ficha FOTO; paquete exige otros conceptos y mezcla datos de contacto | Resolver mapeos con prueba de historia; los cambios de catálogo congelado necesitan aprobación específica |
| G05: ciclo documental parcial | Servicio observa/valida/anula; restricciones por permiso no equivalen a ownership/competencia completas | Reutilizar y reforzar al implementar; no añadir servicio documental paralelo |
| G06: plazos residuales de 48 horas | Alta y catálogo actuales | Decisión posterior de siete días gobierna el nuevo desarrollo; no reabrirla |

No se usa Spatie como base EAV del seguimiento ni fecha_vencimiento como columna multiuso. Las operaciones técnicas auditadas permanecen en Activitylog; la procedencia y el seguimiento de dominio deben tener su representación correcta. Estas brechas no autorizan tablas, columnas, FK o catálogos concretos.

## 8. Evidencia y cierre

Fuente principal de operaciones: [DocumentacionUsuarioService](../../../../app/Modulos/Identidad/Aplicacion/Acciones/DocumentacionUsuarioService.php). [Modelo Documento](../../../../app/Models/Documento.php) hereda usuario desde [ModeloOperativo](../../../../app/Models/ModeloOperativo.php); no declarar ausente esa relación por leer solo la subclase. [Migración de documentos](../../../../database/migrations/2026_09_18_001019_create_documentos_table.php) conserva self-FK y validación.

Pruebas existentes localizadas: [ContratoDocumentacionUsuarioTest](../../../../tests/Feature/ContratoDocumentacionUsuarioTest.php) y [ContratoPersonalInstitucionalTest](../../../../tests/Feature/ContratoPersonalInstitucionalTest.php). No se ejecutaron durante esta tarea documental. La [matriz de aceptación](06-ACEPTACION-PERSONAL-DOCUMENTOS.md) y [preguntas residuales](07-DECISIONES-RESIDUALES-PERSONAL.md) distinguen lo definido y lo no decidido.

**Estado:** especificación de proceso, catálogos contrastados y brechas documentadas; R01–R03 adoptadas funcionalmente. Implementación integral, representación de solicitudes, DDL concreto cuando corresponda, pruebas funcionales y QA visual pendientes.
