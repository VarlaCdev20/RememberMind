# RememberMind — Baseline funcional congelado de roles

**Estado:** CONGELADO
**Aprobación:** modificación expresamente aprobada por la responsable del proyecto
**Ámbito:** roles, permisos, navegación, dashboards y flujos funcionales
**Persistencia:** Spatie Permission; no se crean tablas empresariales de roles
**BDD vigente:** Operativa V2.1, exactamente 70 tablas operativas congeladas

## Modelo funcional

- `SUPERADMINISTRADOR` → máxima autoridad técnica y supervisión global del sistema.
- `GERENTE` → dirección institucional y gestión del personal.
- `ADMINISTRADOR` (nombre visual: **Administración**) → coordinación de la operación diaria.
- Profesionales → atención y registro clínico/interdisciplinario dentro de su competencia.
- `FAMILIAR` → acceso únicamente a residentes vinculados e información autorizada.

Roles profesionales vigentes: `ENFERMEROS`, `MEDICO GENERAL/GERIATRA`, `PSICOLOGO/A`, `PEDAGOGO`, `NUTRICIONISTA` y `FISIOTERAPEUTA`. `VOLUNTARIO` permanece fuera de alcance.

## Separación obligatoria

| Nivel | Finalidad | Escritura propia | No obtiene por su rol |
| --- | --- | --- | --- |
| Superadministrador | Control técnico, cuentas, roles, seguridad, auditoría y supervisión integral | Técnica, administrativa y operativa conforme a reglas de negocio | Escritura clínica por el solo rol de Superadministrador |
| Gerente | Personal, áreas, turnos maestros, planificación, cobertura e indicadores | Institucional y de RR. HH. no financiero | Diagnóstico, prescripción, notas o valoraciones clínicas |
| Administración | Preadmisión, admisión, alojamiento, jornadas, asignaciones, documentos, visitas, actividades y alertas | Operativa y administrativa | Gestión técnica de cuentas, dirección de RR. HH. o escritura clínica |
| Profesionales | Atención y registro por disciplina | Clínica/interdisciplinaria según competencia, permiso y contexto | Facultades de otras disciplinas |
| Familiar | Consulta autorizada | Ninguna clínica | Historia clínica sensible o residentes no vinculados |

## Reglas de autorización

Toda operación sensible exige conjuntamente:

`sesión autenticada + cuenta ACTIVA + permiso Spatie explícito + Policy/servicio contextual + regla de negocio + alcance/relación/competencia`.

La visibilidad del sidebar no reemplaza la autorización backend. Superadministrador conserva lectura global y administración amplia en dominios técnicos, administrativos y operativos, pero la lectura nunca concede mutación clínica. Gerente y Administración tampoco reciben escritura clínica por el nombre de su rol.

La escritura clínica exige siempre rol profesional competente, permiso explícito, Policy o servicio contextual, personal activo y regla de negocio. Un usuario con `SUPERADMINISTRADOR + MEDICO GENERAL/GERIATRA` puede ejecutar actos médicos por su rol profesional; el rol `SUPERADMINISTRADOR` no constituye un bypass clínico.

## Flujos congelados

- Preadmisión: `PENDIENTE → APROBADA | RECHAZADA`. Aprobar no crea residente.
- Admisión: preadmisión aprobada → preparación documental y contactos → consentimiento/seguro → cama disponible → confirmación → residente admitido.
- No existe alta directa de residentes fuera de la formalización de admisión.
- Gerencia define personal, pertenencia institucional y planificación maestra; Administración abre la jornada y realiza asignaciones operativas; el profesional ejecuta y registra la atención.
- Alertas: generación → reconocimiento → asignación/coordinación → seguimiento → atención → cierre, preservando `eventos_alerta`.
- La prescripción ordinaria corresponde exclusivamente a `MEDICO GENERAL/GERIATRA`; Enfermería administra medicación, pero no prescribe.

## Navegación y dashboards

- Superadministrador: sistema, institución, residencia, operación, lectura del expediente clínico y reportes globales. El residente permanece como núcleo del expediente; no se convierte cada tabla en una opción del sidebar.
- Gerente: personal, áreas, turnos, planificación, cobertura, consulta administrativa de residentes/ocupación, alertas e incidentes relevantes y reportes gerenciales.
- Administración: admisiones, residentes administrativos, alojamiento, jornadas, asignaciones, documentos, consentimientos, seguros, visitas, actividades, alertas e incidentes.
- Profesionales: residentes asignados, pendientes y funciones propias de su competencia.
- Familiar: información expresamente autorizada del residente vinculado.

Cada nivel utiliza un perfil de dashboard distinto. Los accesos y métricas se filtran por permiso; ninguna opción se protege solamente mediante CSS.

## SUPERADMINISTRADOR

- **Lectura:** total sobre los dominios reales de RememberMind y la auditoría técnica.
- **Administración:** global en sistema, institución, residencia y operación, siempre respetando admisión formal, ocupación única, integridad referencial, estados e invariantes.
- **Escritura clínica:** solo mediante competencia profesional adicional + permisos + Policy/servicio + contexto.
- **Auditoría:** consulta total; las entradas históricas de Spatie Activitylog no se editan ni eliminan para ocultar acciones.

## Modo «Ver como rol»

`Ver como rol` es una previsualización temporal de experiencia almacenada en sesión mediante `preview_role`.

- No es impersonación.
- No cambia `Auth::user()`, `cod_usuario`, roles ni permisos persistidos.
- No utiliza `loginUsingId()` ni atribuye acciones a otro usuario.
- Cambia exclusivamente la presentación del sidebar y dashboard hacia el rol seleccionado.
- Muestra un banner permanente y claramente visible.
- Es estrictamente de solo lectura: oculta o deshabilita controles mutables y un middleware bloquea solicitudes backend no seguras mientras el modo está activo.
- Alterar manualmente la sesión no concede permisos: solo un usuario autenticado con rol real `SUPERADMINISTRADOR` puede mantener el estado de preview.
- Salir del modo elimina el estado temporal y restaura la experiencia completa de Superadministrador.

## Gobierno de datos

Este baseline es exclusivamente funcional. No agrega, elimina, renombra ni modifica tablas, columnas, PK, FK, cardinalidades o restricciones de las 70 tablas operativas V2.1. Los datos de roles y permisos pertenecen a las tablas técnicas de Spatie, excluidas del conteo operativo. Cualquier necesidad futura de alterar el esquema debe detenerse y seguir el procedimiento formal de aprobación del baseline de base de datos.
