---
title: "Funcionamiento cotidiano: captura descriptiva y rectificación"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-28
owner: Valoraciones
source_of_truth: true
source_of_truth_scope: [owner_approved_iadl_capture_actors_and_permissions]
verification_scope: SCOPED_CAPTURE_AUTHORIZATION_HISTORY_SQLITE_POSTGRES_AND_BROWSER
runtime_verified: true
---

# Funcionamiento cotidiano — captura propia

## Decisión y alcance

La propietaria solicitó completar el funcionamiento cotidiano y aprobó expresamente **Medicina y Psicología** como profesionales de registro y rectificación, respondiendo a la propuesta de permisos `controles_funcionales_iadl.crear` y `controles_funcionales_iadl.rectificar`. Esta decisión posterior amplía exclusivamente el alcance de captura antes excluido en la [decisión inicial IADL del 17/10](../base-de-datos/DECISION_IADL_FUENTE_EXPERTA_2026_10_17.md); conserva tabla, columnas, FK y catálogos. No agrega tablas ni modifica el inventario institucional.

Es un formulario propio de observación descriptiva de actividades instrumentales. **No es FAQ/Pfeffer, no calcula puntajes, no diagnostica DCL ni acredita automáticamente C03.** La falta de oportunidad y el nivel previo desconocido se conservan explícitos. La [matriz D13](MATRIZ_DATOS_DETECCION_DCL_2026_10_27.md) deja de tener pendiente localizar un writer para esta fuente; los otros pendientes de esa matriz mantienen su alcance.

## Ruta y continuidad

Ruta compartida `/admin/residentes/{residente}/funcionamiento-cotidiano`, nombre `admin.funcionamiento-cotidiano`. Accesos desde:

- Ficha de Psicología → Funcionamiento → Funcionamiento cotidiano.
- Valoración cognitiva y funcional original, usada por Medicina/Psicología → Funcionamiento cotidiano.

Recorrido: contexto residente/profesional → actividad, fuente y fecha → oportunidad/desempeño/ayuda/motivo → nivel previo y observaciones → revisión de solo lectura → confirmación → historial. El contador o etiqueta de registro no se presenta como validez clínica. La fecha clínica se captura explícitamente, con precisión de minutos en esta interfaz; `registrado_en` conserva el momento técnico del guardado.

La corrección añade una fila `RECTIFICACION`, enlazada por la FK existente `cod_control_anterior`, con autor y fecha técnica nuevos. El campo de observación solicita el motivo y contexto de la rectificación. El original permanece sin cambios y visible como antecedente; el lector experto existente puede distinguir la fuente rectificada al corte. No se habilita edición destructiva, anulación improvisada o borrado.

## Catálogo y persistencia

Se reutilizan exactamente `ControlFuncionalIadl::ACTIVIDADES`, `OPORTUNIDADES`, `DESEMPENOS`, `AYUDAS`, `CAUSAS`, `NIVELES_PREVIOS` y `FUENTES`. Etiquetas humanas centralizadas sin nuevas opciones. No hay matriz clínica nueva de combinaciones ni causalidad inferida por elegir una etiqueta. Una fila corresponde a una actividad de un residente en un momento; no a una valoración global.

El backend valida catálogo cerrado, campos/longitudes, fecha no futura, identidad del residente y atención del mismo residente cuando se vincula. La atención es opcional; no se fabrica un episodio. Autor y registrador se derivan de la cuenta; estados, PK, FK del antecesor y fechas técnicas no se reciben dentro de los campos editables. Se seleccionan explícitamente los campos validados, incluso si una clave protegida vacía superase `prohibited`.

Transacción: releer/bloquear residente → reautorizar → validar → reintento idéntico o creación/rectificación → Activitylog mínimo. Una misma solicitud confirmada es idempotente; una solicitud divergente no sobrescribe el registro. Bloqueo por residente serializa las rectificaciones del flujo. Una rectificación adicional sobre el mismo original se rechaza; SQL directo no tiene una restricción única nueva sobre la antecesora, y no se afirma que exista. Cualquier fallo de auditoría revierte la operación. El Model mantiene las guardas existentes de inmutabilidad y borrado.

## Matriz de autorización

| Operación | Actores y condiciones |
|---|---|
| Consulta | Permiso específico de lectura y contrato contextual existente de resultados/fuente. No concede escritura. |
| Registro | Cuenta ACTIVO, residente ADMITIDO, lectura autorizada, permiso `crear`, rol Medicina/Psicología y personal ACTIVO de profesión MEDICINA/PSICOLOGIA correspondiente, área/jornada vigente conforme al servicio clínico. |
| Rectificación | Mismas condiciones; permiso `rectificar`, original del mismo residente, vigente sin sucesora y motivo. |
| Vista previa | No permite escritura. |
| Gerencia, Administración, Enfermería, Fisioterapia, Familiar y Superadministración solo por su rol | No reciben competencia para escribir esta fuente; los permisos manipulados no eluden la Policy. Una persona que además tenga un rol clínico legítimo debe cumplir todas sus condiciones. |

Se protegieron los tres lugares que podían conceder permisos automáticamente a Superadministración: evento de creación, seeder general y editor de permisos de rol. El instalador acotado añade solo los dos permisos aprobados a Medicina/Psicología, sin sincronizar ni retirar permisos ajenos. No crea cuentas, personal o residentes. La excepción clínica temporal no sustituye esta competencia.

## Componentes y reutilización

- [Action de registro y rectificación](../../app/Modulos/Valoraciones/Aplicacion/Acciones/RegistrarControlFuncionalIadl.php).
- [Policy contextual](../../app/Modulos/Valoraciones/Presentacion/Policies/ControlFuncionalIadlPolicy.php).
- [Interacción Livewire](../../app/Modulos/Valoraciones/Presentacion/Livewire/Paginas/FuncionamientoCotidianoPanel.php) y [vista compartida](../../app/Modulos/Valoraciones/Presentacion/Vistas/livewire/valoraciones/funcionamiento-cotidiano-panel.blade.php).
- [Etiquetas de catálogos](../../app/Modulos/Valoraciones/Presentacion/Soporte/OpcionesFuncionamientoCotidiano.php).
- [Instalador de permisos acotado](../../database/seeders/PermisosCapturaFuncionamientoCotidianoSeeder.php).

Componentes institucionales `section-card`, `form-section`, `field`, `selector`, inputs/botones y estilos de captura ya existentes. Paleta y tipografía compartidas; una columna en móvil. No se crea otro Design System ni una nueva dependencia. Los códigos internos no se presentan como nombres de actividades o personas.

## Verificación

Evidencias de esta entrega en `output/funcionamiento-cotidiano-*`, excluidas del commit:

- Suite SQLite de captura, ficha psicológica y permisos de lectura: **50 pruebas, 200 aserciones**. Dos pruebas dirigidas posteriores comprueban instalación con permisos ausentes y editor de roles: **2 pruebas, 10 aserciones**.
- PostgreSQL desechable, cuenta no superusuario: **52 pruebas, 215 aserciones**, incluyendo dos procesos simultáneos para reintentos y rectificaciones. Los dos procesos idénticos conservan una sola fila; dos rectificaciones del mismo original conservan una sucesora y rechazan la otra.
- Navegador opt-in: registro → revisión → volver → confirmación → rectificación → historial; cinco anchos **1440/1280/1024/768/390**, sin overflow ni errores JavaScript. Datos sintéticos exclusivamente en SQLite desechable de pruebas; no hay nuevas rutas de laboratorio en el producto.
- Regresión ampliada de permisos/roles en PostgreSQL: **16 pruebas pasan y 1 falla**, por el texto esperado «Organización y planificación» en el dashboard de Gerencia. Es una vista ajena a esta entrega y no se modificó para hacer pasar la prueba. Las dos pruebas nuevas de asignación/edición de permisos están incluidas entre las aprobadas.
- Instalación local acotada en PostgreSQL completada y verificada: dos permisos para Medicina/Psicología, roles ajenos intactos, cero registros clínicos escritos y sin DDL. Evidencia `output/funcionamiento-cotidiano-instalacion.json`.
- Build Vite correcto; permanece el aviso previo de tamaño de chunks.
- Revisiones de seguridad/BDD por fase de solo lectura: autoría, permisos, IDOR, atomicidad, historial y diferencias entre garantía de Action y esquema documentadas. Evidencia técnica no equivale a validación clínica.

El helper PowerShell general de PostgreSQL se detuvo por un bloqueo de tuberías al arrancar el cluster; se paró ese cluster propio y se ejecutó la misma suite con un lanzador local aislado. El resultado PostgreSQL informado corresponde a la ejecución terminada, no al intento interrumpido. No se cambió el helper general.

## Límites que siguen vigentes

Sin nuevas reglas clínicas de compatibilidad entre todas las combinaciones del catálogo; se conservan las declaraciones originales para revisión profesional. No se inventó una FK del informante, una columna de escolaridad/idioma, un baremo ni una conexión FAQ→C03. Las fuentes estandarizadas, conservación conjunta instrumental/Petersen y los otros componentes pendientes mantienen sus decisiones propias. No se envió correo a terceros ni se modificó el remitente protegido.
