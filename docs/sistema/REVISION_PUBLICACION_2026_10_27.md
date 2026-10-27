# Revisión y publicación del trabajo acumulado — 27/10/2026

## Alcance autorizado

La propietaria pidió leer y revisar los cambios, conservar el trabajo,
actualizar la entrega y ejecutar commit y push. Se conserva la rama existente
`feature/apoyo-decision-informe-neurocognitivo`; esta publicación no integra
directamente `main` o `develop`, no despliega y no acredita aceptación clínica.

El árbol reúne varios trabajos concurrentes, no una sola modificación de correo:

- Gerencia: personal y documentación, contactos y acceso Familiar, áreas,
  planificación, cobertura, ocupación, alertas, galería y reportes.
- Identidad: renovación del acceso, recuperación, cambio inicial de contraseña
  y Recordarme según su decisión específica.
- Correo: transporte real, remitente institucional protegido, cola documental,
  reintentos, estados y consulta del historial propio.
- Reportes de Enfermería: filtros de período y residente, consulta y PDF con
  autorización; nueva imagen de bienvenida.
- ApoyoDecision y Valoraciones: reparación documentada de conflictos que se
  habían guardado dentro de los archivos, preservando las decisiones de la
  integración y sus pruebas.
- Documentación: inventarios, doce módulos, diseños y aceptación de Gerencia,
  decisiones BDD, límites de implementación y evidencia de cada fecha.

## Conservación y seguridad

No se ejecutaron reset, clean, stash, borrados del árbol ni force push. Los
archivos anteriores de acceso y de PDF que aparecían para eliminación se
conservaron; sus rutas de ejecución siguen usando las implementaciones actuales.
Conservar un archivo histórico no vuelve a habilitar su interfaz.

No se publica `.env`, contraseñas de aplicación, almacenamiento persistente,
respaldos privados, capturas de la sesión institucional ni trazas locales.
Los artefactos de `output/` permanecen en disco; el informe distingue resultados
de pruebas sintéticas y no afirma que una captura antigua pruebe la sesión actual.
Los scripts de instalación acotados se conservan, pero no se ejecutaron para
esta publicación ni autorizan nuevas instalaciones.

El análisis de los candidatos no encontró coincidencias con secretos del entorno
local ni marcadores de conflicto activos. Se revisaron los cambios de permisos,
autenticación, archivos privados, reautorización del worker y exportaciones.
Esto no sustituye una certificación integral de seguridad del producto.

El primer índice inspeccionado contenía 440 archivos, incluidos ocho assets
gráficos de documentación: 432 archivos de texto comprobados, cero coincidencias
con secretos locales y cero eliminaciones. Los cambios concurrentes de reportes
se incorporan tras leer su diff y repetir sus pruebas y compilación; no se
atribuyen esos cambios a la corrección del correo.

Durante el cierre comenzó otro trabajo concurrente, «Avisos de planificación»,
con estructura y permisos señalados como propuesta. Sus nuevos archivos y sus
cambios posteriores al índice revisado se conservan localmente y se excluyen
de este corte. No se infiere aprobación de esa tabla a partir del permiso para
hacer push. La versión publicada de conservación de asignaciones corresponde
al diseño37 ya aprobado y no depende de ese trabajo nuevo.
La propietaria confirmó expresamente: «Publica lo revisado; conserva lo demás
localmente». El corte excluye esa propuesta y no modifica sus archivos.

El remitente permanece exclusivamente `remembermind.health@gmail.com`. La
protección y las claves locales no se sustituyeron. Los DDL de Gerencia35/37
mantienen los hashes exactos de la decisión aprobada; no se alteraron ni
reinstalaron contra la base institucional. El inventario vigente es de **106
tablas de negocio y 16 técnicas**, distinto de los cortes históricos conservados.

## Correcciones de esta revisión

- La portada documental ya indica 106 tablas de negocio y distingue el corte
  anterior de 100.
- La prueba visual `management-workspace.test.js` ahora monta las clases del
  inicio vigente de Gerencia. Comprueba una o tres columnas de indicadores y
  una o dos de paneles según el contenedor, sin desbordamiento ni colisiones,
  y controles de al menos 44 px en seis anchos y ambos temas.
- El fixture de `AdministracionMedicacionFormularioTest` crea la prescripción
  antes de la primera dosis de las 08:00. No se debilitó la regla que excluye
  dosis anteriores a la fecha de prescripción ni se modificó el servicio clínico.

## Verificación

Resultados y límites de este corte se completan con la salida final de los
procesos de prueba. La evidencia detallada local está en
`output/revision-publicacion-20261027/`; se conserva sin publicar datos de sesión.

| Comprobación | Resultado |
|---|---|
| Sintaxis de PHP cambiado y nuevo | 236 archivos correctos en el primer barrido |
| Sintaxis del contenido exacto preparado para commit | 239 archivos PHP correctos |
| Composer | `composer validate --strict --no-check-publish`: correcto |
| Motores sin Laravel | `independencia.php` y `orion_independencia.php`: correctos |
| Build Vite | Correcto; continúa la advertencia de paquete mayor de 500 kB |
| Frontend final | 280 casos: 276 correctos, 4 omitidos, cero fallos |
| PostgreSQL ampliado | 193 pruebas; un fixture de Reportes falló por descripción obligatoria ausente |
| Repetición de Reportes PostgreSQL | 9 pruebas y 52 aserciones correctas con el fixture actualizado |
| Medicación: formulario tras corregir el fixture | 29 pruebas y 222 aserciones correctas |
| Acceso, recuperación, Recordarme y correo | 78 pruebas y 422 aserciones correctas |
| Últimos cambios visuales de reportes | Nuevo build correcto; Gerencia y reporte de Enfermería: 2 pruebas de navegador correctas |

Las pruebas no se suman como cobertura única cuando sus filtros se solapan.
La suite general presenta fallos que deben quedar visibles; esta rama conserva
el trabajo y su deuda, sin declararse lista para integrar o desplegar.

La ejecución general iniciada antes de corregir el fixture de Medicación alcanzó
al menos 3.355 de sus 3.889 casos durante el cierre. Sigue en curso con errores
y fallos; no se presenta como una suite aprobada ni como verificación completa
del índice. Entre los grupos señalados están contratos de arquitectura, el
ensayo F02 histórico, Medicación, varios recorridos de Enfermería y del experto.
El grupo del formulario de Medicación sí se repitió después de su corrección y
pasó por completo, como indica la tabla. La auditoría del 25/10 ya documentaba
incidencias F02 y de ocurrencias de medicación; no se atribuye automáticamente
cada fallo restante a una causa sin leer su diagnóstico final.

## Publicación del corte

Se prepara un commit convencional sobre la rama existente y un push normal a
su rama homónima de `origin`, autorizados expresamente por la propietaria.
El estado exacto publicado se consulta en el historial Git; no se incorporan
los artefactos locales de pruebas ni el trabajo concurrente de Avisos. No se
ejecuta merge a `develop`/`main`, despliegue ni modificación de datos reales.
