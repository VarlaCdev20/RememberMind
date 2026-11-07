# Gerencia: residentes y acceso a su red de apoyo

## Diagnóstico y alcance

La propietaria pide situar Familiares y contactos junto a Residentes, poder consultar los residentes existentes y revisar por qué no aparecen datos.

Se revisaron la sesión real del navegador, las rutas y `ResidenteController`, `RedApoyoPanel`, `ResidentePolicy`, el menú de Gerencia y sus pruebas. La conexión existente funciona: el directorio mostró **110 residentes**. La pantalla de contactos tenía opciones cargadas desde BDD, pero abría un estado de selección sin mostrar un directorio visual.

Se reprodujo además un fallo de búsqueda: nombre y apellido juntos se buscaban como una sola cadena dentro de cada columna, produciendo cero coincidencias aunque existiera la persona. No fue necesario cambiar conexión, credenciales, esquema ni datos.

## Cambios

- En Gerencia, Consulta institucional muestra consecutivamente «Residentes» y «Familiares y contactos». Se reutiliza el acceso existente a Residentes y se retira Familiares de Personal; no se duplican rutas ni se amplían permisos.
- Al entrar a Familiares sin selección, se muestran tarjetas de residentes admitidos con nombre y conteo de vínculos registrados, calculado mediante `withCount` en la consulta. Cada tarjeta abre la red del residente elegido mediante la ruta existente.
- Se conservan el límite de 40 coincidencias y el filtro ADMITIDO. La pantalla los explica y permite abrir el directorio completo, que conserva sus estados administrativos, filtros y paginación. No se elige automáticamente la primera fila ni se convierte otro estado en ADMITIDO.
- La búsqueda por nombre y apellidos separa palabras y combina las coincidencias entre nombres, apellido paterno y materno, con entrada acotada. No cambia la relación entre contacto y residente.
- El directorio de Gerencia y la ficha administrativa incluyen enlaces autorizados a Familiares y contactos; el menú de cada tarjeta del directorio conserva el residente como contexto.

## Integridad y seguridad

No se crean residentes, cuentas, contactos, vínculos o registros de relleno. No se ejecutan migraciones/seeders contra la instalación operativa ni se modifica `.env`. La admisión formal, cama e historia conservan su flujo. Sin nuevas tablas, columnas, relaciones o permisos.

Las rutas existentes siguen exigiendo cuenta activa, permiso y Policy. El parámetro `residente` se vuelve a autorizar antes de renderizar la red. El directorio de Gerencia conserva su ficha administrativa y no carga ni ofrece escritura clínica. Los enlaces adicionales exigen `residentes_contactos.ver`.

El menú compartido tenía un cambio previo de Enfermería; se conserva fuera de este commit, separando únicamente la reubicación de Gerencia.

## Comprobación en la sesión existente

Se confirmó la lectura del directorio (110 registros), las 40 opciones iniciales de la red, el resultado correcto de una búsqueda de nombre y apellido y la lectura de un vínculo ya existente. En una pestaña nueva se comprobó búsqueda → coincidencia → Limpiar → 40 residentes, sin rechazo; se cerró únicamente esa pestaña de prueba. La pestaña de la propietaria conserva su residente seleccionado.

Durante la actualización de la vista se observó un aviso de petición rechazada. No se reprodujo en la comprobación aislada con la página nueva; no se redujeron controles de acceso para ocultarlo. Las capturas de verificación del cambio utilizan datos sintéticos.

## Pruebas y cierre

- SQLite: **16 pruebas / 102 aserciones aprobadas**. PostgreSQL desechable: **16 pruebas / 102 aserciones aprobadas**. Grupo `GerenciaContactosTest|GerenciaConsultaResidentesTest|SidebarServiceTest.*gerencia`.
- Regresiones nuevas: residente existente visible sin selección, búsqueda entre nombre y dos apellidos, conteo de vínculos conservado, ningún registro de relleno y menú sin duplicados fuera de Personal. Se conservan los negativos existentes de autorización/ficha administrativa.
- E2E aprobado en 1440/1280/1024/768/390px: catálogo inicial, búsqueda por varias palabras, selección desde la tarjeta, lectura de la red, tabla, edición/cancelación, residente sin vínculos, directorio por persona y enlace visible a familiares desde el directorio de Residentes.
- Build aprobado. Capturas de catálogo en escritorio/móvil y directorio móvil inspeccionadas; sin nueva paleta ni componentes paralelos. No se ejecutó CI completa ni un rediseño global del directorio.

Evidencia: `output/gerencia-residentes-conexion-{sqlite,postgres,build}-20261107.txt`, `output/gerencia-residentes-conexion-e2e-cierre-20261107.txt` y capturas sintéticas en `output/gerencia-residentes-conexion-20261107/`. La prueba PostgreSQL creó y cerró su propio servidor/base temporal; no alteró la base observada desde el navegador.
