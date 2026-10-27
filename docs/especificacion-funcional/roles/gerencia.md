---
title: "Funciones y alcance — Gerencia"
status: CURRENT
version: "1.2"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [role_functional_compilation]
verification_scope: APPROVED_ROLE_BASELINE_AND_STATIC_CODE_COMPARISON
runtime_verified: false
---

# Gerencia

[Definición progresiva de vistas, formularios y accesos](GERENCIA_PLAN_VISTAS.md): mapa inicial y preguntas por paso. Las preferencias pendientes no se tratan como decisiones aprobadas.

**Bloque 15–29 adoptado el 25/10/2026:** [matriz de respuestas](GERENCIA_CUESTIONARIO_PENDIENTE.md#registro-y-próximos-pasos). Incluye recuperación por enlace iniciada por Gerencia, registro/corrección de vínculos familiares y exportación PDF/CSV de indicadores agregados. Las opciones dobles son dos presentaciones de los mismos datos. Adopción funcional, con implementación y pruebas pendientes.

> **Decisiones expresas del 25/10/2026:** Gerencia crea cuentas de Administración, seis roles profesionales y Familiar; no crea Gerencia ni Superadministración. También puede editar sus datos y activar/suspender su acceso, dentro del mismo alcance. Superadministración crea los diez roles. Véanse [alta](../vistas/gerencia/02-PERSONAL.md#7-decisión-de-la-propietaria-alta-de-cuentas-por-rol) y [mantenimiento aprobado](../vistas/gerencia/02-PERSONAL.md#9-decisión-registrada-mantenimiento-de-datos-y-acceso). No incluye cambio de roles, edición del catálogo de permisos o eliminación. La implementación de esta extensión sigue pendiente.

**Identificador del catálogo:** `GERENTE`. **Grupo:** Institucional y dirección. **Corte documental:** 25/10/2026.

## 1. Finalidad

Dirigir la residencia mediante gestión de personal, organización, planificación y supervisión de cobertura.

La funcionalidad de esta ficha compila [el baseline aprobado](../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md) y decisiones allí enlazadas. La descripción no crea permisos ni certifica su implementación completa.

## 2. Funciones principales

| Función | Qué le corresponde hacer |
|---|---|
| Personal | Gestionar personal y pertenencia institucional según permisos y estado laboral. |
| Creación de cuentas | Crear cuentas de Administración, los seis roles profesionales y Familiar; excluir Gerencia y Superadministración conforme a la decisión del 25/10. |
| Mantenimiento de cuentas | Editar datos y activar/suspender acceso dentro de esos ocho roles; mantener excluidas las cuentas de Gerencia/Superadministración. |
| Recuperación de acceso | Iniciar/reemitir enlace de recuperación para las cuentas de su alcance, sin conocer o elegir la nueva contraseña ni eludir suspensiones; decisión 16B. |
| Familiares y contactos | Registrar y corregir vínculos familiares trazables, activar Familiar cuando cuenta y vínculo sean aptos, y consultar por residente o contacto; decisiones 17B, 18A, 19B/A. No publica clínica por implicación. |
| Alta laboral conjunta | Registrar al nuevo trabajador y crear su cuenta vinculada en una operación atómica, conforme a la opción B del 25/10; no ofrecer alta sin cuenta en este flujo. La cuenta no acredita competencia clínica. |
| Documentación laboral | Subir, revisar y anular documentos laborales con motivo e historia dentro del alcance aprobado; conservar autoría de carga separada de la revisión. Mínimo inmediato obligatorio y peticiones adicionales por rol, sin obligación general de otros respaldos. |
| Estado laboral | Cambiar ACTIVO/INACTIVO con motivo/fecha, separado del acceso a la cuenta; Administración revisa asignaciones afectadas, sin borrar pasado ni alterar clínica. R03A, implementación pendiente. |
| Organización | Mantener áreas y planificación maestra de turnos/cobertura. |
| Supervisión residencial | Consultar residentes, ocupación y capacidad administrativa necesarias para la dirección. |
| Incidencias | Consultar alertas e incidentes relevantes para coordinación institucional, respetando acceso al contenido. |
| Indicadores | Consultar reportes institucionales y cobertura por áreas; exportar PDF/CSV de indicadores agregados con autorización y auditoría, según 26B/C. |
| Continuidad operativa | Comunicar la planificación para que Administración abra jornadas y realice asignaciones operativas; avisos internos a Administración y trabajadores afectados por cambios confirmados, según 23A. |

## 3. Módulos relacionados

- [Identidad](../../../app/Modulos/Identidad/README.md)
- [Institucion](../../../app/Modulos/Institucion/README.md)
- [Residentes](../../../app/Modulos/Residentes/README.md)
- [Alertas](../../../app/Modulos/Alertas/README.md)
- [Reportes](../../../app/Modulos/Reportes/README.md)

Relacionar un módulo no concede todas sus operaciones; cada fuente/acción conserva su autorización.

## 4. Información que puede consultar

Información de personal, organización, cobertura y consulta administrativa de residentes/ocupación, alertas e indicadores pertinentes.

## 5. Información que puede registrar o gestionar

Gestión institucional y de personal/planificación autorizada, más alta, edición de datos, activación/suspensión y recuperación por enlace de los ocho roles destino definidos. Solicita documentación por rol laboral, anula documentos laborales erróneos con historia y cambia estado laboral ACTIVO/INACTIVO con motivo/fecha según R01–R03 adoptadas. Registra/corrige vínculos familiares con trazabilidad y exporta indicadores gerenciales agregados en PDF/CSV. Las asignaciones concretas siguen el contrato de jornadas y la autorización de la operación. No incluye cambiar roles/permisos, eliminar cuentas o publicar información clínica por implicación.

## 6. Límites

- No obtiene diagnóstico, prescripción, notas o valoraciones clínicas por su rol gerencial.
- No sustituye a Superadministración en la gestión técnica global de cuentas y permisos.
- Una entrada visible o un permiso de lectura de personal no autoriza por sí mismo guardar horarios.

## 7. Dashboard y recorrido de trabajo

Inicio equilibrado de Personal, Ocupación y Alertas, con bloque compacto «Pendientes de Gerencia» debajo de las tres tarjetas. Personal activo, cobertura, distribución por área y actividades registradas se sustentan en sus fuentes; no inferir cobertura futura con una sola jornada ni introducir nómina/finanzas no contratadas. La nueva composición está especificada, no verificada en ejecución.

1. Revisar personal y cobertura.
2. Ajustar organización o planificación autorizada.
3. Consultar ocupación e incidencias institucionales.
4. Entregar planificación a la operación diaria.

Fuente de navegación observada: [SidebarService](../../../app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php). [El contrato de dashboards](../../arquitectura/DASHBOARDS_POR_ROL.md) aporta fuentes/períodos por su corte. Las vistas y cada botón se detallarán en las fichas de pantalla, sin duplicar esta responsabilidad por cada vista.

## 8. Autorización y ejemplos de permisos

Cuenta activa + permiso explícito + Policy/contexto + regla/estado válido + relación/competencia. En actos clínicos, personal activo y autoría propia cuando corresponda. Un grant del seeder no demuestra permiso efectivo en la BDD instalada ni sustituye la Policy.

Ejemplos localizados en [el seeder de roles](../../../database/seeders/RolesAndPermissionsSeeder.php): `personal.gestionar`, `areas.gestionar`, `turnos.gestionar`, `reportes.institucional`. No es una lista exhaustiva ni autoriza a ejecutar el seeder; se consultó como código.

## 9. Continuidad con otros actores

Planifica; Administración organiza la jornada concreta; los profesionales ejecutan y registran el cuidado.

## 10. Participación en el sistema experto

No recibe valoración cognitiva, firma clínica o gestión de reglas por este rol. Consulta gerencial únicamente dentro de las fuentes permitidas.

[Fuentes expertas vigentes](../../sistema-experto/README.md): distinguir resultado observacional conservado de evaluación conjunta transitoria, revisión de resultado y aprobación de una regla. Son actos diferentes.

## 11. Evidencia y limitaciones actuales

Galería tiene [vista y doce controles](../vistas/gerencia/29-GALERIA-GERENCIA.md), [carga/privacidad](../vistas/gerencia/30-CARGA-PRIVACIDAD-GALERIA.md), [24 casos](../vistas/gerencia/31-ACEPTACION-GALERIA-GERENCIA.md) y [dos imágenes de propuesta](../vistas/gerencia/32-DISENO-IMAGENES-GALERIA.md). Conserva 27B y el mecanismo ya aprobado/implementado según informe del 17/10; no se reejecutó aquí ni se amplió su alcance. La [matriz de cobertura del rol](../vistas/gerencia/33-MATRIZ-COBERTURA-GERENCIA.md) reúne fichas principales y 212 casos especificados, distinguiendo documentación, código existente, integración/QA y decisiones pendientes; no declara producto completo.

Reportes tiene [indicadores agregados](../vistas/gerencia/25-REPORTES-GERENCIA.md), [preparación PDF/CSV](../vistas/gerencia/26-EXPORTACION-REPORTES-GERENCIA.md), [24 casos especificados](../vistas/gerencia/27-ACEPTACION-REPORTES-GERENCIA.md) y [dos imágenes de propuesta](../vistas/gerencia/28-DISENO-IMAGENES-REPORTES.md). Desarrolla 26B/C sin exportación nominal, salud, documentos o XLSX por implicación. Stock actual y movimientos temporales son distintos; consulta/descarga deben conservar contenido/alcance o exigir actualización explícita. Adaptación de consultas/exportadores, seguridad y QA pendientes; no se emitieron reportes institucionales en esta tarea.

Alertas tiene [bandeja única y diez controles](../vistas/gerencia/21-ALERTAS-GERENCIA.md), [detalle de lectura e historia](../vistas/gerencia/22-DETALLE-TRAZABILIDAD-ALERTAS.md), [24 casos especificados](../vistas/gerencia/23-ACEPTACION-ALERTAS-GERENCIA.md) y [dos propuestas visuales](../vistas/gerencia/24-DISENO-IMAGENES-ALERTAS.md). Conserva 25A y supervisión gerencial; no activa asignación, reconocimiento, cierre o intervención por disponer de lectura. Los indicadores, la lista filtrada y el resumen por responsable declaran su ámbito. Implementación/QA pendientes; no se confunde alerta persistida con notificación o pendiente documental.

Residentes/ocupación tiene [directorio administrativo](../vistas/gerencia/18-RESIDENTES-GERENCIA.md), [mapa y tabla de camas](../vistas/gerencia/19-OCUPACION-HABITACIONES-CAMAS.md) y [24 escenarios no ejecutados](../vistas/gerencia/20-ACEPTACION-RESIDENTES-OCUPACION.md). Conserva 29B y 24A/B, sin códigos visibles, sin alta directa de residentes ni permiso de traslado por implicación. La propietaria pide mejorar el diseño reutilizando componentes institucionales. H04, estados divergentes y fidelidad del historial requieren implementación/verificación; esta ficha no los da por corregidos.

Planificación/cobertura tiene [calendario y guardado/avisos especificados](../vistas/gerencia/14-PLANIFICACION-DEL-EQUIPO.md), [consulta tabular](../vistas/gerencia/15-COBERTURA-DEL-EQUIPO.md), [28 casos no ejecutados](../vistas/gerencia/16-ACEPTACION-PLANIFICACION-COBERTURA.md) y [dos imágenes de propuesta](../vistas/gerencia/17-DISENO-PLANIFICACION-COBERTURA.md). No se confunden planilla calculada, asignación guardada y asistencia. El enlace actual al catálogo de turnos no acredita cobertura. Implementación de ruta, vigencias/historia, avisos y QA pendientes.

Áreas/equipo tiene [diez campos y once acciones especificados](../vistas/gerencia/12-AREAS-Y-EQUIPO.md), con [veinte casos de aceptación no ejecutados](../vistas/gerencia/13-ACEPTACION-AREAS-EQUIPO.md). Se conserva la entrada única 20A y el origen del equipo en asignaciones contextualizadas, sin fabricar una jefatura permanente. DA01/DA02 acotan decisiones de desactivación y representación; no hay nuevas reglas o estructura aprobadas por estas fichas.

Familiares/contactos dispone de [ficha detallada](../vistas/gerencia/09-FAMILIARES-Y-CONTACTOS.md), [formularios/privacidad](../vistas/gerencia/10-FORMULARIOS-FAMILIARES-PRIVACIDAD.md) y [24 escenarios especificados](../vistas/gerencia/11-ACEPTACION-FAMILIARES-CONTACTOS.md). Mantiene las decisiones aprobadas y señala defaults de autorización y diferencias de entradas como hallazgos estáticos, no comportamiento clínico aprobado. Implementación y verificación funcional pendientes.

Personal y documentación tienen [especificación detallada de vistas](../vistas/gerencia/README.md): campos, acciones, permisos/destinos, revisión, plazos y 44 escenarios aún no ejecutados (36 base y ocho de solicitud por rol). Las [respuestas R01–R03](../vistas/gerencia/07-DECISIONES-RESIDUALES-PERSONAL.md#respuestas-adoptadas-el-25102026) están adoptadas; queda la resolución técnica/estructural de las brechas identificadas. Esto amplía la documentación; no acredita correcciones de código, autorización instalada o QA visual.

H02/H03 de la auditoría tienen correcciones de autorización y solapamiento nocturno en Actions, verificadas en el [corte de renovación del 26/10](../vistas/gerencia/36-RENOVACION-GERENCIA-IMPLEMENTACION-Y-PRUEBAS.md). H04, enlace de Ocupación con 403, tiene [corrección técnica del 26/10](../vistas/gerencia/34-CORRECCION-ACCESO-OCUPACION-H04.md): ruta directa sin grants administrativos nuevos. Estas correcciones no cierran toda la historia de horarios/ocupación ni todos los flujos del rol.

La documentación funcional anterior conserva sus decisiones; las frases de implementación pendiente de este corte se contrastan con el informe 36. No se afirma que los 212 escenarios documentales se hayan ejecutado. La extensión de Personal aprobada solo está instalada en bases desechables.

Pruebas localizadas, **no reejecutadas** al redactar esta ficha:

- [tests/Feature/RolesVistasPermisosMatrixTest.php](../../../tests/Feature/RolesVistasPermisosMatrixTest.php)
- [tests/Feature/AccesoDashboardPorRolTest.php](../../../tests/Feature/AccesoDashboardPorRolTest.php)

La [auditoría H01–H32](../../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) conserva resultados ejecutados y límites del 25/10. Esta entrega documenta conteo y funciones; la aceptación vista por vista, permisos por cada acción y corrección de defectos continúan según su evidencia.
