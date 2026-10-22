# Valoraciones interactivas — implementación del 22/10/2026

Estado: implementación integrada; aceptación visual final y recorrido autenticado pendientes. Dictamen visual final: FAIL por evidencia final incompleta; no se declara un defecto nuevo observado sin revisión.
Rama: `feature/valoraciones-explorador-guias`. No se hizo push.

## Fuente y base de trabajo

Se consultaron las instrucciones por ámbito, los contratos visuales, tokens,
roles congelados, documentación de Valoraciones, diccionario de adquisición y
contrato institucional de Petersen. La instrucción actual del propietario
prevalece sobre los textos del ZIP, que se trataron como referencias.

Se compararon `main` (`0f067549`), `develop` (`155a1516`) y la dependencia funcional
vigente (`f855ccdf`). La rama de trabajo nace de develop e incorpora por avance
rápido esa dependencia, que ya desciende de develop. Se trabaja en el checkout
existente `C:/laragon/www/RememberMind`; no queda otro worktree. Se revisaron los nueve PR abiertos; no se fusionaron ramas independientes ni se alteró su destino. Los cambios
preexistentes y concurrentes de otros módulos se conservan fuera de esta entrega.

El ZIP se extrajo fuera del repositorio y se revisaron las pantallas, escenas y
poses. Sus pacientes, resultados, textos de autoguardado, tiempos y estímulos no
se incorporaron al producto. No se copiaron los PNG de la mascota: sus derechos
no acreditan explotación definitiva. Se implementaron un personaje vectorial
original y cuatro escenas de orientación, con tokens oficiales y sin contenidos
de pruebas protegidas. La revisión de identidad institucional queda pendiente.

## Antes / Después por área

| Área | Antes | Después | Catálogo instalado observado, solo lectura |
|---|---|---|---|
| Cognitiva `ARE_COG` | Acordeón compacto y correspondencias de tipos especulativas | Explorador, cinco dominios documentados, catálogo separado de biblioteca investigada, guía y captura existente | Sin instrumentos cognitivos en la consulta local; no se instalaron candidatos |
| Afectiva `ARE_AFE` | Vacío instrumental, formulario con respuestas clínicas iniciales y creación de área implícita | Vacío explicado, continuidad a ficha, campos clínicos vacíos, atención y valoración profesional transaccionales con área real del autor | Sin instrumentos asociados; valoración psicológica disponible según autorización |
| Funcionamiento `ARE_FUN` | Lista básica y acceso a registro IADL desde Psicología | Instrumentos FUNCIONAL reales, guía, historial y consulta IADL según permiso; retirados edición IADL y porcentajes arbitrarios | 26 instrumentos FUNCIONAL activos en el catálogo local; disponibilidad de captura depende de preguntas y contexto |
| Entorno `ARE_SOC` | Tipos sociales presumidos y lectura de contactos sin permiso específico | Contexto y ficha existente; contactos únicamente con permisos de fuente y vínculos activos | Sin instrumentos sociales; no se creó un instrumento ni test sustituto |

`ARE_NUT` conserva únicamente la ruta que ya existía, de consulta para
Superadministración. Psicología no obtiene competencia de Nutrición. Estos códigos
son navegación de áreas de valoración: no se usan como FK ni se insertan como
áreas maestras para satisfacer una atención.

## Integración y flujo de datos

1. `EvaluacionesAreaPanel` comprueba cuenta, rol, personal profesional activo y
   permisos. La consulta de residentes autorizados se aplica antes del historial,
   indicadores y filtros. Se conserva el alcance clínico ya aprobado para Psicología;
   no se inventa otra asignación de pacientes.
2. `ExploradorInstrumentos` proyecta catálogo y preguntas vigentes. Los dominios
   provienen de campos existentes, no del nombre. La búsqueda normaliza acentos y
   combina términos de nombre/código/finalidad/dominio; no es búsqueda por embeddings.
3. `abrirGuia(id)` vuelve a resolver la tarjeta del área en el servidor. La guía
   tipada conserva versión, fuente, estado `REFERENCIA_NO_CLINICA` y limitaciones.
   Los pasos orientan a preparar y registrar; no constituyen manuales clínicos.
4. `nuevaEvaluacion(residente, instrumento)` revalida permisos, área, estado y
   preguntas. Emite el evento existente a `EvaluacionGeriatricaAreaModal`, que
   preselecciona el instrumento y lo conserva al elegir residente. La selección
   manipulada se rechaza también al revisar y guardar.
5. `PrepararCapturaInstrumental`, `CapturaInstrumentalModal` y `AplicarInstrumento`
   conservan autoría propia, huella de catálogo, preguntas/tipos/opciones,
   revisión, original cotejado y rectificación enlazada. Cero, No y ausencia
   siguen siendo diferentes. No se añade borrador ni autoguardado.
6. El éxito del avatar depende del evento de registro confirmado por el servidor.
   Errores de transporte, sesión o catálogo muestran un aviso persistente;
   nunca simulan confirmación. El usuario debe consultar el historial si una
   interrupción deja incierto el resultado de guardar.
7. Afectiva reutiliza `RegistrarValoracionProfesional` y `ValoracionPsicologica`,
   dentro de una transacción con la atención del profesional y auditoría existente. Solo se transfieren campos validados; la atención generada y el autor no pueden sustituirse agregando claves al formulario.
   El área procede de su asignación activa. La cancelación conserva los cambios
   hasta que se descartan explícitamente.

Se reutilizan `x-ui.selector`, `x-ui.calendario`, `x-ui.modal-livewire`,
`x-ui.field`, `x-ui.section-card`, notificaciones y RMCharts. Selector/calendario
admiten contenido dentro del modal sin teleport y respetan bloqueo/readonly.
Se compilan localmente las mismas bibliotecas de iconos y notificaciones que antes
se cargaban por CDN. No se crea otro sistema de componentes, CRUD o motor.

## Archivos propios

- Consultas: `ExploradorInstrumentos.php` (nuevo), `InstrumentosDelArea.php`,
  `PrepararCapturaInstrumental.php`.
- Livewire: `EvaluacionesAreaPanel.php`, `CapturaInstrumentalModal.php`,
  `EvaluacionGeriatricaAreaModal.php`, `FichaPsicologicaResidente.php`.
- Vistas del módulo: panel, captura, ficha, `explorador-instrumentos.blade.php`
  y `guia-instrumento.blade.php` (estas dos últimas nuevas).
- Compartidos: `ui/avatar-guia.blade.php`, `ui/escena-guia.blade.php` (nuevos),
  selector, calendario, carga local de notificaciones y layout del sistema.
- Frontend: `modules/valoraciones-explorador.js`,
  `patterns/valoraciones-explorador.css` (nuevos), controles institucionales,
  entradas de JS/CSS, `package.json` y lock.
- Pruebas: `ExploradorValoracionesTest.php`, extensión de
  `FichaPsicologicaResidenteTest.php`, pruebas JS y router E2E sintético opt-in.
- Documentación: este informe y compatibilidad en `database/seeders/LaPaz/README.md`.

## Seguridad, integridad y seeders

Superadministración conserva consulta global; no recibe escritura clínica por
ese rol. Médico, Nutricionista y Familiar no entran en el flujo de Psicología.
No se concedieron permisos nuevos. Los contactos requieren permiso para su fuente
y vínculo; sin permiso IADL no se consultan ni conteos ni tendencia de esa fuente.
La ficha exige los permisos de lectura de sus fuentes clínicas principales. Los puntajes del historial conservan dos decimales y el máximo guardado en la aplicación, sin deducirlo del catálogo actual. El historial muestra la procedencia de la aplicación sin consultar títulos ni archivos de documentos clínicos fuera de su permiso.

La biblioteca de investigación no escribe en instrumentos ni activa reglas,
licencias o publicaciones clínicas. Las pruebas verifican que aplicaciones y
salidas expertas permanecen vacías al consultar candidatos. No se modificaron
esquema, PK/FK, catálogos, estados ni reglas clínicas. No se ejecutaron seeders
contra la instalación. La documentación de La Paz se actualiza porque la mejora
usa sus campos ya existentes y no necesita ampliar su carga.

El router E2E vive fuera de `public/`, exige `APP_ENV=testing`, archivo SQLite
nuevo con nombre y directorio temporal validados y token aleatorio de 48 caracteres.
No añade rutas operativas, cuentas ni acceso alternativo a la instalación real.

Legacy: se conserva la arquitectura modular actual. El modelo instrumental aún
contiene aliases históricos como `adulto()` y otros accessors usados por módulos
fuera de esta entrega; no se creó una capa de compatibilidad nueva. Su limpieza
transversal queda identificada para no romper consumidores ajenos.

## Verificación ejecutada

Entorno: Windows, PHP 8.4.13, PHPUnit 12.5.36; dependencias instaladas del lock.
Las extensiones SQLite se habilitaron solo en cada proceso de pruebas.

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit --filter='ExploradorValoracionesTest|FichaPsicologicaResidenteTest'
# 21 pruebas, 102 assertions: PASS (incluye restricciones de fuentes).

php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit --filter='ExploradorValoracionesTest|FichaPsicologicaResidenteTest|CapturaInstrumentalFormularioTest'
# Iteración anterior: 52 pruebas, 323 assertions: PASS.

pwsh -NoProfile -File scripts/probar-postgres.ps1 -Filtro 'ExploradorValoracionesTest|FichaPsicologicaResidenteTest|CapturaInstrumentalFormularioTest' -Informe output/valoraciones-postgres.junit.xml -Log output/valoraciones-postgres.log
# 54 pruebas, 329 assertions: PASS; cluster desechable detenido al finalizar.

node --test tests/Frontend/valoraciones-explorador.test.js tests/Frontend/controles-institucionales.test.js
# 13 pruebas: PASS.

npm run build
# PASS. Aviso de chunks mayores de 500 kB; no error de compilación.
```

Una corrida ampliada de 128 pruebas detectó dos fallos de expectativas de ficha
(denegación de permiso y etiqueta anterior). Se corrigieron y las pruebas
específicas pasaron; esa corrida ampliada completa no se presenta como verde.
La comprobación final de autoría con `formAfectiva.cod_atencion` y
`formAfectiva.cod_personal` manipulados pasó en SQLite (1 prueba, 4 assertions).
La atención y el autor proceden del servidor, no de esas claves. El mismo
caso pasó también en PostgreSQL desechable: 1 prueba, 4 assertions, mediante
`scripts/probar-postgres.ps1 -Filtro test_psicologo_puede_guardar_valoracion_afectiva_vinculada_a_atencion_y_soporte_c04`.
Tras restringir la consulta de procedencia, `ExploradorValoracionesTest` volvió a
pasar en SQLite: 8 pruebas, 38 assertions.
No se ejecutó toda la suite de todos los módulos en esta entrega.

Antes de que se deshabilitara el acceso al navegador, la prueba E2E con SQLite
sintético recorrió áreas, guía, selectores y calendario, y una versión previa
completó un guardado real de No. La matriz de 1440/1280/1024/768/390/320 px registró
cero overflow del documento. Una extensión posterior inyectó un fallo HTTP 503
para probar recuperación: detectó el botón de revisión aún deshabilitado. Se
retiró el bloqueo duplicado del submit, conservando el bloqueo propio de Livewire.
Esa corrección y las escenas vectoriales finales **no tienen repetición E2E final**.
No se declara aceptación visual ni recuperación final aprobadas.

## Capturas reales de la interfaz con datos sintéticos

Son evidencia de una iteración anterior a los últimos ajustes, no de la base
operativa ni de una sesión real de Superadministración.

| Área/estado | Captura |
|---|---|
| Cognitiva, escritorio | [1440 px](../../output/valoraciones-20261022/cognitiva-1440.png) |
| Afectiva | [1440 px](../../output/valoraciones-20261022/afectiva-1440.png) |
| Funcionamiento | [1440 px](../../output/valoraciones-20261022/funcionamiento-1440.png) |
| Entorno | [1440 px](../../output/valoraciones-20261022/entorno-1440.png) |
| Tablet | [768 px](../../output/valoraciones-20261022/cognitiva-768.png) |
| Móvil | [390 px](../../output/valoraciones-20261022/cognitiva-390.png) |
| Guía y modal móvil | [Guía escritorio](../../output/valoraciones-20261022/guia-desktop.png), [390 px](../../output/valoraciones-20261022/guia-390.png) |
| Captura instrumental | [1280 px](../../output/valoraciones-20261022/captura-1280.png) |
| Aviso de transporte | [HTTP 503 sintético](../../output/valoraciones-20261022/error-conexion.png) |

Las cuatro áreas muestran historial sin registros en estas capturas sintéticas.
No hay captura final de validación de formulario ni de éxito: quedan pendientes.
Los artefactos permanecen locales y se excluyen del commit de código.

## UX GAP DETECTADO y pendientes

- Acceso automatizado al navegador deshabilitado en Work. La página operativa
  permanece en `http://127.0.0.1:56154/login`. Se identificó una cuenta existente
  activa de Superadministración y se verificó la credencial local sin imprimirla;
  **no se completó el login ni la navegación de roles**. No se crearon usuarios ni
  se cambiaron contraseñas.
- Repetir E2E final, recuperación de fallo, foco/retorno y ausencia de imágenes,
  zoom 200 %, lector de pantalla, tema, móvil y todos los roles cuando se habilite
  el navegador. La prueba automatizada no sustituye esa revisión humana.
- Las guías específicas de aplicación requieren manual autorizado, edición y
  validación clínica. Ninguna guía se marca VALIDADA. Si faltan, se informa la
  brecha y se permite consultar la orientación documental segura.
- Derechos y aprobación de identidad definitiva del avatar; no se usa la mascota
  derivada del ZIP como activo comercial validado.
- La ficha preexistente calcula resúmenes longitudinales que requieren una revisión
  clínica separada; esta entrega no los convierte en criterios diagnósticos ni
  modifica el motor. Los porcentajes IADL arbitrarios sí se retiraron y la
  distribución afectiva conserva estados originales sin reclasificar desconocidos
  como estabilidad.
- Sin instrumentos cognitivos instalados no se fabrican tarjetas clínicas. Su
  incorporación o licencia requiere decisión explícita del responsable.
- Revisión de PR e integración a develop pendientes. No se publica ni modifica main.


## Consolidación solicitada posteriormente

El propietario solicitó reunir estos cambios en `feature/apoyo-decision-informe-neurocognitivo`
y publicarlos para el equipo. El registro conjunto y las instrucciones de actualización
están en `docs/sistema/PUBLICACION_CONJUNTA_2026_10_22.md`. Esta solicitud posterior
no modifica los límites de QA visual ni supone validación clínica.
