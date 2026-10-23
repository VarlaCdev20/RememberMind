# Revisión de Psicología, avatar y acceso a los cambios — 22/10/2026

## Qué estaba publicado y dónde

La rama vigente es `feature/apoyo-decision-informe-neurocognitivo`.
El commit `5b3d4a13` ya es antecesor de `f9128b2e`, cuyo título es
`UI UX SISTEMA EXPERTO`. Los componentes de Valoraciones y del avatar permanecen
en esa línea de historial. No faltaba copiarlos ni mezclarlos desde otra rama.
Al iniciar la revisión, HEAD era `e1c6d907`; se conservaron los cambios locales
en curso de otros paneles sin incorporarlos al commit de esta corrección.

## Causa de lo que no se veía

1. En Superadministración, «Valoraciones» abría
   `/admin/salud-seguimiento/valoraciones`, un selector de expedientes de valoración
   funcional general. El explorador estaba en
   `/admin/psicologia/evaluacion/cognitiva` y las otras tres rutas de Psicología.
   Faltaba una conexión visible entre esas entradas.
2. «Instrumentos» apuntaba al endpoint `admin.instrumentos.index`, que devuelve
   JSON del catálogo. No era la pantalla de tarjetas y guías.
3. El avatar implementado es un personaje vectorial original con ocho estados.
   **No son las ocho imágenes de la mascota del ZIP.** Se sustituyeron por la
   limitación de derechos indicada en el propio material. Esa diferencia debe
   comunicarse como una adaptación pendiente de aprobación de identidad, no como
   reproducción de la mascota aportada.
4. Las mejoras de campos se aplicaron a la captura instrumental y a la valoración
   afectiva de la ficha psicológica. No se rediseñaron todos los formularios del
   sistema ni el formulario funcional general de otra competencia.

## Qué funciona en la implementación

| Superficie | Cambio concreto | Límite |
|---|---|---|
| Cuatro áreas de Psicología | Navegación de áreas, tarjetas/lista, búsqueda y filtros | Catálogo real; no instala pruebas para llenar tarjetas |
| Cognitiva | Cinco dominios y biblioteca de 23 métodos separada | Los candidatos solo permiten consulta documental |
| Guía | Siete pestañas, cuatro escenas originales, avance y retroceso | Orientación de registro; manual clínico específico pendiente |
| Captura instrumental | Selector y calendario institucionales, instrumento preseleccionado, revisión y confirmación backend | Superadministración consulta; no captura por su rol |
| Afectiva | Estado de ánimo, afecto, ansiedad, apatía, conducta, conclusión y recomendación | Campos clínicos vacíos inicialmente; autor y atención del servidor |
| Funcionamiento | Instrumentos funcionales reales e IADL de consulta según permiso | Psicología no registra IADL; no se fabrican porcentajes |
| Entorno | Continuidad a la ficha y fuentes autorizadas | Sin tests sociales; contactos sujetos a sus permisos |
| Avatar | Saludo, explicación, atención, duda, éxito, aviso, empatía y descanso | Los estados disponibles no implican que todas las pantallas lo usen |
| Sistema experto | Gobierno, catálogo, versiones, red y consulta de fuentes | No se activan decisiones clínicas por el diseño |

## Corrección de esta revisión

- La pantalla habitual de valoración funcional general muestra ahora una entrada
  con el avatar y enlaces a las cuatro áreas. Se reutilizan `section-card`,
  `avatar-guia`, tokens y navegación del explorador.
- El enlace del menú de Superadministración se llama «Instrumentos y guías» y
  abre el explorador existente. La API JSON conserva su ruta y respuesta.
- El acceso visual se limita a Superadministración/Psicología con permisos de
  fuentes y consulta de residentes. Los endpoints mantienen su autorización de
  servidor; el enlace no concede escritura clínica ni una nueva competencia.
- No se cambiaron base de datos, catálogos, roles, credenciales ni seeders.

Archivos propios de la corrección: `SidebarService.php`,
`salud-seguimiento-list-panel.blade.php`, `ui/acceso-valoraciones.blade.php`,
`ExploradorValoracionesTest.php` y este informe. No se incorporan al commit
las modificaciones concurrentes del explorador experto ni archivos temporales.

## Revisión en navegador real

El acceso automatizado volvió a estar disponible. Se completó el login con una
cuenta existente de Superadministración mediante el formulario normal de
`http://127.0.0.1:8000/login`, sin mostrar ni modificar su credencial.
**8000 sí corresponde a RememberMind en esta revisión actual**; las observaciones
anteriores sobre otro servicio en ese puerto eran históricas.

Se observaron el avatar en el explorador, la separación catálogo/biblioteca,
las tarjetas documentales, la apertura de ayuda y el avance de pasos. Se verificó
también el acceso nuevo desde «Valoraciones» y la portada del sistema experto.
No se registraron datos clínicos ni se ejecutó el laboratorio.

Capturas actuales de interfaz, guardadas localmente fuera del commit:

- `output/revision-avatar-20261022/explorador-avatar-actual.png`
- `output/revision-avatar-20261022/guia-pasos-actual.png`
- `output/revision-avatar-20261022/acceso-valoraciones-corregido.png`
- `output/revision-avatar-20261022/sistema-experto-actual.png`

La captura de la entrada corregida utiliza un filtro sin coincidencias para
excluir los expedientes de residentes. No representa una base sin registros.
Las páginas quedan abiertas con la sesión autorizada para revisión del propietario.

## Comprobaciones de esta corrección

- Dos pruebas nuevas de acceso y exclusión por rol: **PASS, 2 pruebas / 10 assertions**.
  Incluyen entrada habitual, enlace del menú y conservación del endpoint JSON.
- `node --test tests/Frontend/valoraciones-explorador.test.js tests/Frontend/controles-institucionales.test.js`:
  **PASS, 13 pruebas**.
- `npm run build`: **PASS, 12,69 s**; aviso de tamaño de chunks, sin error de build.
- `php -l SidebarService.php`: correcto; diff limitado al enlace.
- Pint detecta cinco categorías de estilo preexistentes en SidebarService.
  Se reprodujeron también sobre una copia de su HEAD anterior; no se reforma
  globalmente ese archivo para corregir un enlace. Las pruebas nuevas no
  presentan hallazgos de estilo.
- `SidebarServiceTest`: **PASS, 14 pruebas / 89 assertions**. No se introdujeron duplicados ni cambios de estructura de los otros roles.

## Qué falta mejorar

- Acordar y validar la identidad del avatar: la mascota del ZIP no está integrada
  definitivamente; los derechos y la aprobación del personaje siguen pendientes.
- Sustituir la orientación documental por guías específicas únicamente cuando
  existan manual, edición, derechos y validación clínica. Ninguna se marca VALIDADA.
- Completar la matriz visual y accesible actual en tablet/móvil, zoom 200 %, lector
  de pantalla y recuperación tras errores. Lo observado en escritorio no acredita
  todas esas variantes.
- Revisar como profesional autorizado los formularios de captura, con datos
  sintéticos; esta sesión de Superadministración no prueba escritura clínica.
- Conservar explícito el alcance: esta corrección conecta los campos de Psicología;
  no convierte la valoración funcional general en un formulario psicológico.
