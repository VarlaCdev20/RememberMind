---
title: "Inicio de Gerencia: resumen equilibrado"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: OWNER_PRIORITY_AND_STATIC_SOURCE_REVIEW
runtime_verified: false
---

# Inicio de Gerencia

## 1. Decisión y alcance

La propietaria eligió el 25/10/2026 un **resumen equilibrado de personal, ocupación y alertas**. Se adopta esa prioridad; la composición que sigue es una especificación propuesta para desarrollar paso a paso, no una interfaz implementada ni una ampliación de permisos.

[Plan de vistas y decisiones](../../roles/GERENCIA_PLAN_VISTAS.md), [función de Gerencia](../../roles/gerencia.md) y [contrato visual](../../../frontend/CONTRATO_VISUAL_UX_UI.md).

## 2. Recorrido del usuario

- **Objetivo:** comprender la situación institucional y abrir el detalle que necesita revisión.
- **Actor:** cuenta activa de Gerencia, con permisos de cada fuente/operación.
- **Entrada:** ruta nombrada `dashboard`; [DashboardController](../../../../app/Modulos/Reportes/Presentacion/Http/Controllers/DashboardController.php) selecciona la experiencia del usuario; vista compartida [dashboard.blade.php](../../../../app/Modulos/Reportes/Presentacion/Vistas/paginas/dashboard.blade.php).
- **Flujo:** leer fecha y alcance → comparar los tres bloques → elegir detalle → consultar fuente autorizada → continuar a la operación permitida → volver al Inicio.
- **Salida:** información entendida y acceso al proceso correspondiente; consultar el resumen no crea ni modifica registros.
- **Excepciones:** permisos insuficientes, fuente sin registros, fallo de consulta y destino con autorización incoherente deben mostrarse de forma distinta.

## 3. Distribución propuesta

| Orden | Zona | Contenido y finalidad |
|---|---|---|
| 1 | Cabecera | «Inicio de Gerencia», descripción breve, fecha/hora de consulta legible y contexto institucional |
| 2 | Resumen equilibrado | Tres tarjetas de igual jerarquía: Personal, Ocupación y Alertas |
| 3 | Pendientes de Gerencia | Bloque compacto adoptado en 28A: documentación por revisar/vencer y otros pendientes verificables; cada fila abre su módulo autorizado |
| 4 | Detalle resumido | Tres bloques correspondientes, con información breve y acceso a su fuente |
| 5 | Accesos de continuidad | Personal, planificación/cobertura, ocupación y reportes, según permiso |
| 6 | Contenido complementario | Próximas actividades registradas, con menor jerarquía que los tres ámbitos elegidos |

En móvil, apilar los bloques manteniendo sus títulos y acciones. Igual peso general no oculta una prioridad alta/crítica ya determinada por el dominio en el bloque de alertas. No crear una alerta nueva ni un color clínico desde el dashboard.

## 4. Bloque Personal

**Pregunta que responde:** ¿qué equipo está activo y qué asignaciones hay registradas para hoy?

- Resumen: personal activo y personas únicas con asignación activa en las jornadas consideradas.
- Detalle: distribución por área; profesión puede ser detalle secundario.
- Ayuda visible: «Asignación registrada; no acredita presencia física».
- Accesos previstos: «Ver personal» y «Ver cobertura». La planificación se abre en su vista autorizada.
- No mostrar porcentaje de cobertura suficiente, déficit asistencial o asistencia si no existe un criterio/registro que lo sustente.
- Un área sin fila de asignación no se declara cubierta, descubierta o inexistente automáticamente; debe comprobarse el universo de áreas antes de definir esa presentación.

La consulta [RoleDashboardDataService::management](../../../../app/Modulos/Reportes/Aplicacion/Consultas/RoleDashboardDataService.php) ya prepara personal activo, asignados del día y cobertura por área. Su filtro por fecha de jornada debe contrastarse con guardias nocturnas; no se presenta como comprobación de presencia «ahora».

## 5. Bloque Ocupación

**Pregunta que responde:** ¿cuál es la situación de alojamiento registrada?

- Resumen propuesto: camas con ocupación activa y camas disponibles según las reglas actuales de disponibilidad.
- Detalle: tabla breve por habitación/área cuando las relaciones y permisos existentes lo permitan.
- Acceso previsto: «Ver ocupación».
- Mantener separadas camas no disponibles por estado y camas ocupadas. No calcular disponibilidad como total menos residentes.
- No usar nombres o datos clínicos como explicación de la ocupación general.
- No añadir porcentaje de ocupación hasta definir denominador y estados incluidos de forma verificable.

Hay relaciones y consulta de disponibilidad en la rama institucional de `RoleDashboardDataService`, pero **no está integrada como bloque de `management()`**. Se debe reutilizar la capacidad pertinente con autorización de Gerencia, sin copiar la consulta global de Superadministración indiscriminadamente. El destino actual «Ocupación» conserva el defecto H04 (redirect a 403); debe corregirse antes de aceptarlo como recorrido funcional.

## 6. Bloque Alertas

**Pregunta que responde:** ¿qué avisos autorizados siguen abiertos y cuáles tienen prioridad registrada?

- Resumen propuesto: alertas abiertas de su alcance y subconjunto de prioridad alta/crítica, sin inventar niveles.
- Detalle: título permitido, fecha legible, prioridad y estado; no cargar automáticamente el contenido clínico completo.
- Acceso previsto: «Ver alertas relevantes».
- Consultar no implica reconocer, asignar, cerrar o registrar atención clínica desde este panel.
- Un cero solo se muestra después de una consulta autorizada y correcta. Sin permiso o con error no se muestra «Sin alertas».

La fuente reutilizable es [BandejaAlertas](../../../../app/Modulos/Alertas/Aplicacion/Consultas/BandejaAlertas.php), que conserva permiso/ámbito. La rama gerencial `management()` aún no incorpora este bloque. La prioridad visual procede de datos persistidos y reglas existentes.

## 7. Botones y destinos localizados

| Botón propuesto | Destino existente | Condición y efecto |
|---|---|---|
| Ver personal | `admin.personal-institucional` | Lectura de personal; gestión solo mediante la operación autorizada en destino |
| Ver cobertura | Destino de consulta tabular por integrar | El enlace observado `admin.turnos-enfermeria.index` abre catálogo de turnos, no cobertura. [Contrato y diferencia](15-COBERTURA-DEL-EQUIPO.md#2-ubicación-y-diferencia-con-la-navegación-actual) |
| Ver planificación | `admin.turnos-asignaciones.index` | Entrada de planificación; no concede asignación por un permiso de lectura |
| Ver ocupación | `admin.habitaciones.index` actualmente | H04 pendiente: resolver toda la cadena de autorización; no aceptar un 403 como destino correcto |
| Ver alertas relevantes | `admin.alertas-clinicas.index` | Lectura de alertas y detalle permitido |
| Ver reportes | `admin.reportes.institucional.preview` | Vista previa autorizada; no implica exportación |

Destinos localizados en [SidebarService::getGerenteSidebar](../../../../app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php). Verificar disponibilidad por rol en implementación; esta tabla no afirma pruebas nuevas del recorrido.

## 8. Formularios, gráficos y estados

El Inicio es principalmente consulta: los formularios de alta/edición permanecen en sus procesos propietarios. Para esta primera composición no se incorporan registros clínicos ni altas rápidas de personal/residentes.

- Fecha de referencia: separar «estado actual» de «asignaciones de hoy» y «próximas actividades». No aplicar un filtro global que aparente retrospectiva en fuentes que solo conservan estado actual.
- Gráficas: barras para distribución de personas por área si permiten comparar; tabla alternativa accesible. No equiparar conteos con suficiencia de dotación.
- Ocupación: cifras/tabla bastan hasta cerrar su definición; no gráfico porcentual sin denominador.
- Estados: carga, resultado consultado, sin registros, acceso restringido y fallo con reintento claramente distintos.
- Apariencia: tokens del Design System existente, cifras/títulos legibles, nombres humanos, foco visible, acciones táctiles y semántica independiente del color. Sin paleta nueva.

## 9. Decisión de navegación registrada

**¿Cómo debe abrirse el detalle al pulsar una tarjeta?**

1. Pantalla completa del módulo correspondiente — recomendado para reutilizar los recorridos existentes.
2. Resumen en panel lateral con botón para abrir la pantalla completa.

**Respuesta de la propietaria, 25/10/2026:** «Ir directamente a la pantalla completa del módulo (recomendado).»

Se adopta la apertura directa del módulo. No se añade un resumen lateral previo ni un formulario de gestión dentro de las tarjetas. La pantalla destino deberá conservar una vuelta clara al Inicio; los controles de autorización siguen aplicándose en ella.

## 10. Criterios de aceptación futuros

- Tres ámbitos con jerarquía equilibrada según la elección registrada.
- Cada cifra tiene fuente, fecha/período y significado comprobables.
- Consulta del detalle y vuelta funcionan sin pérdida de contexto pertinente.
- Permisos revocados eliminan datos del payload; no solo botones.
- Sin datos, sin acceso y error se distinguen.
- Ocupación y alertas incorporadas a Gerencia con su alcance, sin copiar privilegios de otro rol.
- Guardias nocturnas, personas repetidas y estados de ocupación no producen conteos engañosos.
- Teclado, alternativas a gráficas y responsive verificados en navegador.

**Estado actual:** prioridad equilibrada, apertura directa y bloque de pendientes (28A del 25/10/2026) adoptados; fuentes localizadas. El bloque muestra pendientes con estado real, sin duplicar registros o convertir entregas pendientes de revisión en falta de entrega. Implementación, pruebas nuevas y QA visual no ejecutadas en esta entrega. Continúa el detalle de [Personal institucional](02-PERSONAL.md).
