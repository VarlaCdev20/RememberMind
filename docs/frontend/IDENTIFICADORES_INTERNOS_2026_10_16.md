# Identificadores internos en la interfaz

## Instrucción del propietario

Los códigos internos de registros no deben aparecer como contenido visible. La referencia de aceptación fue el marcador técnico de una herida dentro del motivo de una alerta.

## Implementación

- `App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos()` elimina los marcadores de vínculo de heridas y los identificadores `COD_*` en los textos de lectura compartidos. Conserva anotaciones clínicas, saltos de línea, valores, CIE/ATC y otros términos médicos. Ubicación consolidada el 17/10/2026 para su uso desde aplicación y presentación, sin dependencia inversa.
- Alertas, motivos, historial, agenda, avisos y resúmenes relacionados utilizan el mismo formateador.
- Se retiraron etiquetas de identificadores de fichas, selectores, confirmaciones, consultas de medicación, cuidados, directorios, admisiones, bandejas administrativas y el expediente exportado.
- Los identificadores siguen disponibles en relaciones, rutas, selección de registros y acciones autorizadas. No se modifican los registros persistidos, el esquema, las Policies ni los permisos.
- Números de habitación/cama, documento, prefijos telefónicos y terminología clínica conservan su función. Los códigos de conocimiento del entorno técnico del sistema experto no son identificadores de historia clínica.

## Verificación

- Regresión de alertas: motivo e historial legibles sin identificadores; texto original y vínculo persistidos sin cambios. Abrir las consultas no cambia el estado de la alerta.
- 128 pruebas relacionadas superadas entre la ejecución principal y la repetición de la prueba HTTP de medicación. Incluyen alertas, incidentes, cuidados, explorador administrativo, resultados del experto, formulario de medicación y el formateador aislado.
- La prueba HTTP inicialmente devolvía 401: su reloj congelado generaba una cookie caducada para el navegador real. Se configuró una cookie de sesión únicamente en el router de fixtures aislados; la repetición completa pasó, incluidos escritorio y móvil.
- En el navegador conectado se comprobó que tarjeta y detalle muestran el motivo de la herida sin marcador técnico. Después el navegador informó suspensión de red; no se obtuvo captura final ni se acredita navegación manual por todas las pantallas.
- Compilación de Blade y build de frontend verificados. Revisión de diff sin errores de whitespace.

## Integración y límites

No se reescribe historia ni se ocultan datos mediante CSS o manipulación global del DOM. Los consumidores nuevos deben usar nombres, fechas y contexto en sus etiquetas, dejando las claves para operaciones internas.

No se creó un commit de estos cambios: varias vistas y fixtures contienen trabajo previo entremezclado. Se preservó ese trabajo y no se realizó push.
