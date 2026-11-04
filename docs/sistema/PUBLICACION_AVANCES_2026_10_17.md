# Publicación de avances — 17/10/2026

La propietaria solicitó commit y push y confirmó expresamente «Todos los cambios pendientes del proyecto» tras conocer que había cambios de módulos anteriores. Rama de trabajo: `feature/institucion-asignaciones-planificadas`. No se integra directamente a `develop` ni `main`.

## Alcance del corte

Incluye el conjunto pendiente de códigos correlativos, identidad, cuidados, medicación, heridas, pase de turno, signos y consultas, fuentes del experto, componentes visuales compartidos, inicio de Gerencia, Personal y Galería del centro, con documentación y pruebas. Conserva las eliminaciones anteriores de material histórico del experto. Se recuperó la vista V2 de conocimiento que había quedado eliminada pese a seguir registrada y enlazada; no se recupera lógica V1.

Las 32 fotografías importadas, sus miniaturas, la base de datos, los mapas de importación, los respaldos y los logs de pruebas permanecen fuera del commit. No se añaden credenciales ni configuración `.env` real. La importación local está documentada en el [informe de galería](../frontend/GALERIA_INSTITUCIONAL_2026_10_17.md).

## Correcciones de cierre

- Aplicación de galería depende de contratos propios de archivos y gestión; almacenamiento y Policy implementan esos contratos, con bindings en el proveedor modular. Se conserva la misma validación, compensación y autorización.
- La consulta de administración de medicación consume el contrato de imágenes, conservando el adaptador de almacenamiento existente.
- El filtro puro `TextoVisible` se consolida en Compartido/Aplicacion/Formato para reutilizarse desde aplicación y presentación sin dependencia inversa; consumidores y pruebas actualizados.
- Snapshot de rutas incorpora las dos rutas de galería manteniendo las anteriores. La normalización del prefijo Livewire conserva sus nueve endpoints y verifica el prefijo real. El test de vistas usa el finder de la fábrica que renderiza, con sus namespaces.
- Las expectativas estáticas de paleta/escala/texto siguen el contrato visual aprobado; no se restaura la paleta amarillenta anterior para satisfacer pruebas antiguas. El doble de pruebas de Chart.js implementa la estructura de los presets reales sin retirar comprobaciones.
- CI instala GD/EXIF e incluye galería, Gerencia, Personal y códigos en el grupo PostgreSQL. Se conserva la autorización de lectura/escritura existente.
- Renumeración técnica compatible con la FK RESTRICT de galería, con definición final idéntica y rollback probado. No se ejecuta sobre la base operativa. [Detalle](../base-de-datos/DECISION_CODIGOS_CORRELATIVOS.md).
- Los accesos a historial/evolución de dolor y eliminación respetan también el permiso de lectura en Blade; la consulta de datos ya lo exigía. Los negativos verifican datos vacíos y ausencia del acceso correspondiente.
- Fixtures de pase preparan la asignación entrante antes de entregar, conforme al contrato vigente; la prueba de dashboard concede únicamente su permiso de lectura. La prueba del mapa define explícitamente los permisos de su rol de prueba.
- Se elimina la siembra fuera de transacción mediante `$seed = true` en dos clases que contaminaban el PDO en memoria compartido. Sus seeders se ejecutan dentro del caso. La verificación de tablas de modelos consulta el esquema migrado real, incluyendo el DDL estándar de galería, en lugar de reconocer solo cadenas `Schema::create`.

## Verificación

Los primeros intentos detectaron fallos que se corrigieron; las ejecuciones PHP con clases ya cargadas se interrumpieron y sustituyeron por ejecuciones limpias. La suite SQLite completa se divide por clases en cuatro particiones disjuntas, cada una con `LARAVEL_STORAGE_PATH` propio bajo `%TEMP%` y base SQLite en memoria; no comparten discos fake ni cachés de vistas. La unión de los informes JUnit se comprobó contra el descubrimiento completo: 164 clases, 1.242 casos distintos, sin duplicados ni casos perdidos.

| Control final local | Resultado |
|---|---|
| Suite PHP/SQLite completa, cuatro particiones | 1.220 PASS, 22 SKIP; 33.290 aserciones; cero errores/fallos |
| PostgreSQL galería y renumeración | 20 PASS / 150 aserciones |
| PostgreSQL concurrencia clínica, galería y renumeración reforzada | 7 PASS / 85 aserciones; incluye dos workers de medicación, cierre de herida, pase y duplicado de fotografía |
| Frontend sobre build final y fixtures sintéticos | 217 PASS, cero omisiones |
| Frontend focal tras guards de historial | 31 PASS |
| Arquitectura y filtro de texto | 21 PASS / 3.982 aserciones en grupo focal; incluidos después en la suite completa |
| Sintaxis PHP de archivos cambiados | 239 archivos comprobados; los ajustes finales de fixtures/Blade se ejecutaron en sus grupos y suite completa |
| `npm run build` | PASS |
| Composer validate estricto, plataforma y auditoría | PASS; sin avisos de vulnerabilidad |
| `npm audit --omit=dev` | Cero vulnerabilidades |
| Índice Git | `git diff --cached --check` limpio; sin archivos privados, dumps, logs ni `.env` real |

Las 22 omisiones de SQLite corresponden a pruebas exclusivas de PostgreSQL y a funciones Jetstream/Fortify deshabilitadas, incluido registro público. No se presentan como aprobadas: las pruebas PostgreSQL seleccionadas se ejecutaron separadamente en el runner desechable. Estos resultados locales no afirman un resultado futuro de CI remoto.

Evidencias finales fuera de Git: `%TEMP%/remembermind-prepush-part-{0,1,2}-rerun.log/.xml`, `%TEMP%/remembermind-prepush-part-3-final.log/.xml`, `%TEMP%/remembermind-prepush-summary.json`, `%TEMP%/remembermind-prepush-frontend-built.log`, logs PostgreSQL `remembermind-pg-6b5ac6f47b4e4c30945e21b08c8d035b` y `remembermind-pg-821295c0e2ee4c43a173d38488cd1cb4`, y `%TEMP%/remembermind-prepush-build.log`.

La verificación técnica de este corte no reemplaza la aceptación visual de sesión pendiente ni validación institucional/clínica. No se afirma despliegue, restauración de respaldos o publicación de las fotografías por un push del código.
