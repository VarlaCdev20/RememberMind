---
title: "Gerencia: bandeja única de Alertas"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: OWNER_DECISION_AND_STATIC_ALERT_INBOX_REVIEW
runtime_verified: false
---

# Alertas del equipo

## 1. Fuentes y autoridad

**Decisión vigente:** 25A, una bandeja única con filtros, registrada en el [cuestionario adoptado](../../roles/GERENCIA_CUESTIONARIO_PENDIENTE.md). El [rol Gerencia](../../roles/gerencia.md) tiene supervisión institucional, no escritura clínica. Los [contratos de Alertas](../../../modulos/alertas/README.md), [baseline de roles](../../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md) y [baseline BDD](../../../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) siguen vigentes por su alcance. El contrato compilado conserva DEC-OPEN-002 sobre estados y DEC-OPEN-003 sobre significado de prioridad: no se resuelven cambiando colores o nombres en esta ficha.

**Evidencia de implementación:** [AlertasPanel](../../../../app/Modulos/Alertas/Presentacion/Livewire/Paginas/AlertasPanel.php), [BandejaAlertas](../../../../app/Modulos/Alertas/Aplicacion/Consultas/BandejaAlertas.php), [ExperienciaAlertas](../../../../app/Modulos/Alertas/Aplicacion/Consultas/ExperienciaAlertas.php) y [presentación actual](../../../../app/Modulos/Alertas/Presentacion/Vistas/livewire/alertas/alertas-panel.blade.php). No son aprobación de cada grant ni acreditan QA actual.

**Propuesta de presentación:** composición compacta en [24, imágenes y diseño](24-DISENO-IMAGENES-ALERTAS.md). Los ejemplos gráficos no son nuevas reglas ni registros institucionales. No se vuelve a crear un módulo de alertas.

## 2. UX FLOW y límites

Actor: Gerencia con cuenta activa y permiso explícito. Objetivo: identificar pendientes, conocer responsable y trayectoria, y continuar al origen autorizado o a coordinación operativa. Entrada: Inicio → tarjeta Alertas, menú Alertas o notificación vinculada. Recorrido: revisar resumen → filtrar → abrir caso → consultar resumen/historial/origen → cerrar detalle o continuar al destino permitido → volver conservando filtros.

La acción principal es **Ver detalle**. Abrir una alerta no reconoce, asigna, atiende o cierra. El siguiente paso asistencial corresponde al rol competente. No se presenta un formulario de intervención a Gerencia.

La bandeja utiliza alertas persistidas y su historia. Una notificación de campana no es una alerta nueva; marcar una notificación como leída no cambia el estado de la alerta. Los pendientes laborales/documentales ya especificados conservan su fuente y bloque «Pendientes de Gerencia»; no se insertan artificialmente en `alertas` con un residente ficticio. La integración transversal necesita un mapeo legítimo de fuente y permisos antes de mostrar esas categorías dentro de la bandeja única.

## 3. Distribución de la vista

| Orden | Zona | Contenido y comportamiento |
|---|---|---|
| 1 | Cabecera | «Alertas del equipo», subtítulo de supervisión, botón Actualizar; navegación y fecha legibles |
| 2 | Tres indicadores | Prioridades altas o críticas activas, Sin responsable, Con límite superado; misma jerarquía y cantidades verificables |
| 3 | Alcance | Explicar que los indicadores representan el ámbito autorizado antes de los filtros de la lista |
| 4 | Filtros esenciales | Buscar por residente/motivo, Estado, Prioridad; Más filtros despliega origen, responsable y fechas |
| 5 | Lista principal | Tabla: Residente, Motivo y fecha, Prioridad, Estado, Responsable, Acción |
| 6 | Resumen del equipo | Barras horizontales y cantidades por responsable, selección filtra lista; tabla alternativa y límite de grupos visible |
| 7 | Paginación | Total filtrado, rango/página y siguiente/anterior; no contar eventos como alertas |
| 8 | Detalle | Drawer con [resumen, historia y origen](22-DETALLE-TRAZABILIDAD-ALERTAS.md), sin perder la lista |

En pantallas estrechas, resumen del equipo plegable debajo de lista; filtros secundarios plegables desde el inicio. Evitar ocho KPI, gráficas de varios tipos, todos los filtros expandidos y relato completo de cada alerta a la vez. La prioridad alta/crítica permanece reconocible, también con filtros: si hay un aviso de prioridades fuera del filtro, etiquetar su ámbito y abrir su lista, no mezclarlo en el total filtrado.

## 4. Datos, fuentes y ausencia

| ID | Dato visible | Fuente/interpretación y límite |
|---|---|---|
| GA01 | Residente | alertas.cod_residente → residentes; nombre y contexto permitido, nunca PK como subtítulo |
| GA02 | Motivo | alertas.titulo y descripción autorizada; resumen breve sin inventar contenido o mostrar notas clínicas restringidas |
| GA03 | Tipo/origen | alertas.tipo, modulo y referencia cod_registro; etiqueta humana y origen verificado, no FK universal supuesta |
| GA04 | Fecha de registro | alertas.fecha_hora, formato local legible y fecha completa en detalle; no fecha de última consulta |
| GA05 | Prioridad registrada | alertas.prioridad, etiqueta de LenguajeAlerta; no severidad global ni nuevo umbral |
| GA06 | Estado de la alerta | alertas.estado; diferenciar pendiente, en atención, atendida por cerrar, cerrada, anulada y estado anterior |
| GA07 | Responsable | alertas.cod_personal_responsable → personal, no usuario tomado como profesional; NULL: Sin responsable asignado |
| GA08 | Fecha límite | alertas.fecha_hora_limite; NULL: Sin fecha límite registrada, no vencida ni dentro de plazo por defecto |
| GA09 | Forma de generación | alertas.generacion, manual/automática cuando consta; no completar autoría ausente por inferencia |
| GA10 | Trayectoria | eventos_alerta de la misma alerta, autor usuario, fecha y acción/detalle conservados |
| GA11 | Ubicación | ocupación de cama autorizada; distinguir ubicación actual de ubicación al hecho si esta última no se conservó |
| GA12 | Resumen/alcance | conteos de alertas distintas con permiso y filtros descritos; distribución por responsable no mide desempeño |

Ni cero alertas ni prioridad baja acreditan normalidad clínica. «Sin responsable» requiere FK NULL: una FK con referencia no resoluble es una inconsistencia diferente, no un pendiente sin asignación normal. Una fecha límite superada describe únicamente un límite guardado anterior a la hora de consulta y una alerta activa; no crea un plazo clínico universal.

## 5. Botones y permisos

| ID | Control | Efecto y condición |
|---|---|---|
| GA-A01 | Actualizar | Releer ámbito/filtros; sin detectar ni crear alertas como efecto secundario |
| GA-A02 | Buscar | Consulta por texto permitido; debounce y estado de carga, no filtrar en cliente un payload institucional completo |
| GA-A03 | Estado/Prioridad | Aplican filtros de lista con etiquetas legibles; no cambian datos originales |
| GA-A04 | Más filtros | Expande origen/responsable/fechas, con aria-expanded; no ejecuta mutaciones |
| GA-A05 | Limpiar | Restablece filtros y página; conserva las alertas/historia |
| GA-A06 | Indicador | Abre filtro asociado, mantiene alcance explícito y selección visible |
| GA-A07 | Responsable del gráfico | Filtra por responsable real o NULL; no reasigna ni clasifica personal |
| GA-A08 | Ver detalle | Revalidar alerta, residente, cuenta y fuente; abrir drawer de lectura |
| GA-A09 | Consultar origen | Solo si módulo/registro/residente coinciden y permiso de fuente permite lectura; si no, explicar indisponibilidad |
| GA-A10 | Cerrar detalle | Cierra overlay, restaura foco y filtros; no cierra la alerta |

No se ofrecen por el rol gerencial los botones Crear, Detectar, Asignar, Reconocer, Atender, Registrar intervención, Cerrar alerta o Anular. El código puede permitir algunas acciones con permisos adicionales; eso no convierte esos permisos en parte del contrato gerencial. Cualquier ampliación material debe tener decisión específica. No conceder `alertas.gestionar` para sortear un 403 de lectura.

## 6. Seguridad y hallazgos estáticos

La [ruta actual](../../../../app/Modulos/Alertas/Presentacion/Rutas/web.php), dentro del grupo definido en [routes/web.php](../../../../routes/web.php), utiliza `admin.alertas-clinicas.index`; antes de conectar el menú se verifica nombre, middleware y destino reales. Una etiqueta de menú «Alertas» no cambia esa identidad técnica.

- BandejaAlertas exige `alertas.ver`, cuenta activa y excluye Familiar; el panel acepta para lectura `alertas.ver` o `alertas.gestionar`. Es una diferencia de contratos técnicos que debe conciliarse sin ampliar facultades.
- El seeder de roles otorga a Gerencia `alertas.ver`; no demuestra los permisos instalados de una cuenta concreta ni autoriza ejecutarlo en esta tarea.
- El panel contempla reconocer/asignar para roles operativos con permisos explícitos, incluyendo Gerencia en ciertos caminos. La presente ficha conserva lectura de supervisión; no activa esas mutaciones por implicación.
- Bandeja institucional no equivale a permiso para todas las fuentes clínicas de origen. Verificar cada enlace, descripción y payload; no confiar solo en ocultar tabs.
- Los indicadores del panel se calculan antes de los filtros de la lista; el resumen por responsable usa su propio ámbito y tope de ocho grupos. Mostrar esos alcances o alinear consultas durante implementación, sin declarar que siempre coinciden.

Esta tarea no corrige esos caminos. [23, aceptación](23-ACEPTACION-ALERTAS-GERENCIA.md) define la evidencia necesaria para implementar y verificar.
