# Confirmaciones y avisos del sistema

Fecha: 08/11/2026. Solicitud: unificar las ventanas nativas del navegador mostradas
al confirmar acciones, usando el diseño aprobado de RememberMind.

## Alcance implementado

La inspección inicial encontró 27 declaraciones de confirmación de Livewire y
25 llamadas directas a diálogos nativos, distribuidas en 28 archivos. Se sustituyen
por el componente compartido: Nunito, superficie café grisácea, esquinas suaves,
botones redondeados, foco visible, lectura clara y variante de peligro explícita.
Áreas identifica además el nombre y la acción concreta de activar/desactivar.

Se integran los usos encontrados en Admisiones, ApoyoDecision, Clínica, Identidad,
Institución, Medicación, Residentes y Social, además de los scripts compartidos.
Los módulos que ya usaban ventanas del sistema conservan sus componentes.
La inspección final de PHP, Blade y JavaScript de `app/` y `resources/` no encuentra
llamadas directas a los tres diálogos nativos ni declaraciones nativas de Livewire.
Una prueba automática protege este límite para cambios futuros.

## Contrato compartido

- `data-rm-confirm` conserva el texto y la acción existentes. Los atributos
  opcionales `data-rm-confirm-title`, `data-rm-confirm-subject`,
  `data-rm-confirm-label` y `data-rm-confirm-tone` aportan contexto/semántica.
- La captura del evento lo detiene antes de esperar la respuesta. Confirmar
  reanuda una vez el botón o el formulario; cancelar, cerrar y Escape no envían
  la operación. Se conservan validación HTML, Enter y el botón de envío original.
- Se comprueba que el origen continúe conectado y que sus atributos de acción
  no hayan cambiado durante la espera. Se bloquean clics repetidos.
- Los cuatro layouts cargan una protección previa al bundle. Si la ventana no
  está disponible, bloquea la acción y muestra un error; nunca confirma por defecto.
- `rmAvisar` y `rmPedirTexto` comparten presentación y foco con `rmConfirmar`.
  La captura de motivos conserva la validación existente; no se cambian reglas
  clínicas. La navegación interna confirma el descarte antes de continuar.
- Las ventanas permanecen en la misma página y utilizan la capa nativa de
  superposición para aparecer sobre paneles/gráficas abiertos. Se conserva el
  portal estable de los modales para actualizaciones de Livewire.

Se usan las capacidades instaladas de Livewire y SweetAlert2, sin modificar
vendor, sobrescribir las funciones nativas ni añadir dependencias. La integración
de navegación toma el evento cancelable y la API pública de
[Livewire 4](https://livewire.laravel.com/docs/4.x/navigate#javascript-hooks).

## Integridad, autorización y legacy relacionado

No hay cambios de base de datos, roles, permisos, destinatarios ni reglas clínicas.
El servidor sigue decidiendo autorización, validación y resultado. Se mantienen
los datos al cancelar y no se reintentan automáticamente operaciones rechazadas.

Se eliminan los avisos que simulaban compartir o descargar documentos. El informe
del estudio usa ahora la descarga protegida existente; si no hay URL disponible,
se muestra esa ausencia. La acción de compartir que solo afirmaba generar un
enlace se retira sin crear publicación ni permisos adicionales. La consulta de
documentos de eventos conserva la ausencia de archivo cuando no existe un enlace.

## Verificación y límites

- Build de producción: PASS.
- `personal-confirmaciones-e2e.test.js`: PASS en cinco anchos y 390 oscuro;
  cancelación, Escape, ejecución única, refresh y contenido seguro conservados.
- `confirmaciones-sistema-e2e.test.js`: PASS, incluyendo protección estática,
  cambio real de estado del área, cancelar sin petición, envío único, validación
  HTML/Enter, avisos, motivo validado, Galería con cambios sin guardar, panel en
  capa nativa y navegación interna. También prueba que una carga fallida del
  componente no permita enviar acciones sin confirmar.
- Matriz visual: 1440, 1280, 1024, 768 y 390 px, más 390 oscuro. Ventana centrada,
  dentro del viewport, Nunito y controles de al menos 44 px. Sin diálogos nativos
  ni errores JavaScript en los recorridos sintéticos ejercitados.
- Regresión PHP de seis clases: 55 casos correctos y uno que exigía el antiguo
  botón simulado «Compartir». Se corrige esa expectativa, conservando las demás
  comprobaciones; la repetición de ese caso y ResultadosEstudiosReales pasa
  (3 pruebas, 42 aserciones). La verificación adicional de las URLs protegidas
  pasa con 2 pruebas y 27 aserciones. No queda un fallo del alcance sin corregir.

La comprobación utiliza servidores y datos sintéticos aislados, sin operar la
cuenta de la propietaria. No se declara ejecutada la suite general completa ni
una revisión global de toda la funcionalidad de los módulos.

**Límite del navegador:** los avisos de protección al cerrar/recargar una pestaña
con capturas pendientes (`beforeunload`) pertenecen al navegador. No admiten este
diseño y se conservan para proteger datos. Los avisos originados por los botones,
formularios y flujos internos detectados usan el diseño del sistema.
