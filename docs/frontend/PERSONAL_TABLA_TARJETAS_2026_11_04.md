---
title: "Personal de Gerencia: tabla y tarjetas compartidas con Enfermería"
status: VERIFIED_SCOPED_UI
last_reviewed: 2026-11-04
runtime_verified: true
---

# Alcance

Petición de la propietaria: ordenar Personal institucional, unificar tamaños y ofrecer tabla/tarjetas usando como base Enfermería. Ruta: `/admin/personal-institucional`, variante de Gerencia.

## Antes y flujo aplicado

Cada fila mostraba hasta cinco acciones con igual peso; las filas crecían según las acciones disponibles. Filtros, recuento y selector de vista estaban separados. El recorrido ahora es buscar/filtrar → comparar personas en tabla o tarjetas → abrir ficha → consultar acciones secundarias cuando se necesitan. El alta sigue siendo la acción principal de la cabecera.

## Implementación

- Una superficie de trabajo con título, total, selector Tabla/Tarjetas, filtros y paginación compartidos.
- Reutilización real de `ui.resident-card` y `ui.resident-card-identity`, ya empleados por Enfermería. Las props opcionales `secondary` y `entity` permiten mostrar correo e identidad laboral sin edad o alojamiento. Slot opcional de acciones; los consumidores anteriores conservan su comportamiento por defecto, detalles y notas.
- Estado laboral y acceso separados mediante `ui.status-badge`; ningún cambio de estado se deduce de otro.
- Ficha visible y acciones secundarias en `dropdown`: horarios, situación laboral, suspensión/activación y enlace de acceso, según las mismas autorizaciones/confirmaciones anteriores.
- Tabla de cinco columnas en escritorio; reflow etiquetado en espacios estrechos, sin eliminar datos. Tarjetas en tres/dos/una columnas, con la geometría e identidad del componente de Enfermería.
- Tokens y escalas existentes; fondo/superficies cálidas y acento salvia. No se crea una paleta de rol.
- La regla global de densidad deja de reducir este directorio a 40 px; controles de al menos 44 px. Menús con Escape/retorno de foco y acciones accesibles sin hover.

## Límites

Se conserva todo el trabajo previo. No cambia BDD, consultas, permisos, reglas laborales, archivos o correo. No se envían enlaces ni se suspenden cuentas reales durante QA. No se crean usuarios en la base operativa. La revisión visual utiliza el router sintético existente con SQLite desechable.

## Evidencia ejecutada

- GerenciaPersonalRenovacionTest + PersonalInstitucionalResponsiveTest: 13 pruebas /132 aserciones correctas antes de aplicar la reutilización final.
- GerenciaPersonalRenovacionTest + PersonalInstitucionalResponsiveTest + MisResidentesNavegacionTest: 25 pruebas /187 aserciones correctas.
- PersonEntityPresentationTest: 3 pruebas /15 aserciones correctas: tarjeta de Enfermería sin regresión, tarjeta laboral sin edad/alojamiento y preservación de slots de detalle/nota.
- Build Vite correcto; advertencia de tamaño de bundle existente.
- El recorrido E2E amplio de Gerencia se interrumpió por su límite de300 segundos y no se declara aprobado. Se añadió una ejecución focal opcional `RM_GERENCIA_QA_PERSONAL=1` al test existente para verificar esta pantalla en1440/1280/1024/768/390 px, ambas presentaciones, menú/Escape/foco, filtros y medidas.
- La primera QA focal detectó controles reducidos por la regla global; se corrigió la causa antes de repetir. El resultado final se registra al cerrar la revisión.

Sin cambios nuevos de dominio/legacy. Esta entrega comprueba la presentación de Personal y la compatibilidad de sus componentes; no es aceptación global de todos los flujos de Gerencia.

## Cierre verificado

QA visual-funcional focal: PASS. Diez combinaciones (tabla/tarjetas en1440,1280,1024,768 y390 px), sin desbordamiento horizontal, celdas contenidas en sus filas y controles de al menos44 px; menú dentro de pantalla, Escape y retorno de foco, filtrado/vacío/limpieza. Modo oscuro de tarjetas comprobado en390 px. Cero errores JS o respuestas HTTP fallidas. Capturas actuales revisadas en escritorio y móvil.

La inspección visual detectó además la altura fija de fila heredada, que causaba superposición vertical al reflow; se corrigió y se incorporó una aserción geométrica a la regresión. Se revisó el resultado corregido antes de aceptar.

Ejecución focal final: 1 test E2E correcto (85 s), diez combinaciones más modo oscuro y estados. Evidencia local sintética: output/auditoria-integral-20261025/gerencia-renovada/personal-unificado/resultado.json y capturas del mismo directorio. Build final correcto. PHP:28 pruebas únicas /202 aserciones (25 de Personal/navegación y3 de compatibilidad del componente); no sumar el filtro inicial solapado.

Verificación limitada a la presentación y regresiones indicadas. No se reejecutó la CI completa ni se modificó la base operativa. La rama conserva trabajo previo de otras tareas; el commit incluye únicamente esta entrega.
