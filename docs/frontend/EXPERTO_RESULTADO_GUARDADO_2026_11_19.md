# Resultado institucional guardado · 19/11/2026

## Alcance y contrato

Se reorganiza la pantalla de resultados conservados, compartida por Enfermería,
Psicología y Medicina. Conserva la selección histórica, la comprobación de
integridad y las revisiones profesionales existentes. No modifica el motor,
conocimiento, reglas, permisos, estructura de BDD ni los registros locales.

La fuente exclusiva de las gráficas es `contenido.dominios` del resultado
institucional verificado. El contrato histórico conserva estados categóricos,
no una escala continua de deterioro ni el DTO completo. Por eso se representa:

- Una dona con el número de áreas según su estado, sin porcentajes de riesgo.
- Un mapa de cinco áreas con radios iguales y selección por teclado o botón.
- Los cinco requisitos con su estado original, sin reinterpretar su cumplimiento.
- Cantidades de fuentes distintas por identificador de origen: aplicaciones,
  respuestas y valoraciones. Varios campos de una aplicación no se cuentan como
  varias aplicaciones.

La interfaz distingue alteración sustentada, rendimiento esperado, información
insuficiente, no interpretable y contradicción. Mantiene «Posible DCL» y su
carácter orientativo. La ausencia de información no se presenta como normalidad.

## Presentación e integración

Cabecera con residente, fecha de corte y resultado destacado. Tarjetas del
sistema, verde salvia, acento cognitivo compartido y bordes suaves. Se recupera
el radio institucional dentro del informe; no se cambia el estilo de otras
pantallas del experto.

La explicación extensa, los datos originales y las reglas quedan en apartados
desplegables. Los datos tienen búsqueda por texto/valor, filtro de fuente y
paginación de ocho filas. En móvil cada fila conserva sus cuatro etiquetas.
La proyección se escapa en Blade/JavaScript y se muestra con `x-text`.

Se reutilizan `ui.section-card`, `ui.section-header`, tabla, controles de filtro,
botones y el gráfico cognitivo compartido. Su nueva variante dona no cambia las
variantes anteriores. `ResultadoInstitucionalVisible` realiza únicamente la
proyección de presentación; no consulta SQL ni ejecuta inferencia.

Un resultado que falla su integridad no produce resumen ni gráficas.
Enfermería conserva consulta contextual, sin ejecución ni revisión profesional.
El control de acceso del residente continúa en backend. Las revisiones de
Psicología/Medicina no se reemplazan ni se exponen a Enfermería.

## Verificación técnica

Pruebas acotadas, no equivalentes a la suite completa:

- SQLite: cinco pruebas, 197 aserciones. Estados diferentes, cero/falso/null,
  fuentes distintas, lectura histórica, selección explícita, ausencia de
  resultados, acceso ajeno rechazado y corrupción sin gráficas.
- PostgreSQL: las mismas cinco pruebas y 197 aserciones en un clúster temporal
  independiente; parada y limpieza verificadas. No se ejecutaron migraciones
  sobre `remembermind_dev` para esta tarea.
- Frontend: búsqueda/paginación, interacción del mapa compartido y circuito E2E
  optativo del resultado guardado. El circuito genera resultados mediante el
  motor real en SQLite en memoria y exporta solo a un archivo temporal nuevo,
  protegido por el router de pruebas. No relaja las guardias del seeder.
- Build: `npm run build`. Conserva el aviso previo sobre paquetes de más de 500 kB.

Evidencia local no versionada: `storage/app/private/experto-vista-*.log`, XML de
ambos motores y `output/frontend-experto-guardado-20261119/`.

### Dictamen visual y funcional acotado

PASS para el contenido del resultado conservado: revisadas capturas en 1440,
1280, 1024, 768 y 390 px, sin desbordamiento horizontal, con título entre 25 y
32 px. Tres pruebas frontend aprobadas. Se verificaron selección por teclado,
tema oscuro, movimiento reducido, filtro sin coincidencias, paginación sin
superposición, resultado insuficiente y rechazo 404 de un residente ajeno.
No aparecen literales `null`, `NaN`, `undefined`, `Infinity`, `[object Object]`
ni directivas Blade visibles. Se corrigieron los hallazgos de las primeras
pasadas antes de repetir la comprobación.

Fuera del informe se observa una deuda de la navegación compartida: en la
captura de escritorio algunas etiquetas del lateral estrecho quedan visibles
en vertical. El sidebar no recibe estilos de esta modificación. No se declara
PASS global del shell ni se amplía esta tarea a su rediseño. El logo del fixture
no se usa como criterio del informe porque la prueba bloquea recursos externos.

La sesión de Chrome disponible quedó en el formulario de acceso y su ventana
minimizada. No se introdujeron ni recuperaron contraseñas. La comprobación del
flujo se realiza en el servidor sintético aislado, con las rutas y componentes
reales; queda pendiente volver a abrirlo en la sesión personal iniciada.

Sin dependencias nuevas ni legacy V1 introducido. Sin push automático.
