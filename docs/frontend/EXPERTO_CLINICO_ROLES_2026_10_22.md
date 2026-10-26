# Exploración clínica del experto por residente — 22/10/2026

## Alcance y fuentes

La propietaria pidió una lectura dinámica, organizada y comprensible para Medicina, Psicología y Enfermería: resultados por residente, criterios, factores, casos de investigación y documentos/evaluaciones externos. También pidió frecuencias entre residentes para Superadministración y evitar que los mapas reduzcan excesivamente sus nodos. Posteriormente solicitó integrar las dos instalaciones mencionadas y continuar sin subagentes. Se detuvo el revisor iniciado; la implementación y pruebas posteriores se realizaron sin delegación.

Se reutilizan las rutas y Policies de `ResultadosExpertoResidente`, los contratos de `ConsultarPerfilInstrumental`, `ConsultaCognitivaEnfermeria`, las fuentes de Petersen y el Design System. Las decisiones de consolidación del 22/10 prevalecen sobre inventarios históricos. El 23/10 se aplicaron las dos migraciones de consolidación ya aprobadas y los instaladores acotados de permisos existentes. No se definieron nuevas estructuras, métodos clínicos, umbrales, competencias, prescripción ni asignaciones de Enfermería.

## Flujo de consulta

Objetivo: entender qué resultado tiene el residente, qué factores lo explican y qué información falta.

1. Medicina/Psicología entran desde el directorio existente o la ficha del residente. Enfermería entra desde sus residentes asignados y conserva su autorización contextual.
2. La vista inicial muestra el resultado actual, cuando existe, separado de la historia conservada y su revisión profesional.
3. Seleccionar **Áreas y factores** permite consultar cada área y abrir sus componentes, medidas originales/normalizadas, observaciones, fechas, autoría y límites.
4. **Requisitos del resultado** presenta C01–C05 con el estado devuelto por el motor, valoraciones vinculadas y dependencias expresamente declaradas, como la derivación de C02 desde los dominios.
5. **Fuentes y documentos** diferencia aplicaciones disponibles, documentos vinculados y soportes ligados explícitamente a las valoraciones. La mera presencia de una fuente no se presenta como participación en el resultado.
6. Los controles existentes permiten abrir fuentes completas, conexiones, historial y fundamento autorizado. Medicina/Psicología conservan la acción de análisis; Enfermería no ejecuta inferencias nuevas.

| Información | Fuente | Presentación |
|---|---|---|
| Estado actual | Salida Petersen de la consulta autorizada | Cálculo actual, fecha de corte y condición sin guardar |
| Resultado histórico | Lector e informe de evaluación conservada | Bloque separado con revisión profesional |
| Factores | Componentes de la consulta instrumental autorizada | Detalle junto al selector; una columna en móvil |
| Evidencia utilizada | Referencias explícitas de soportes/valoraciones | Vínculo por identidad; sin correspondencias por parecido |
| Documento original | Captura autorizada o soporte documental original | Fecha, verificación cuando consta, descripción autorizada y alcance |
| Investigación | Cohorte nominal existente y capacidad local configurada | Filtro y etiqueta sintética; no completa expedientes ajenos |

Las versiones de auditoría del historial requieren `consultarTrazabilidad`. La versión de un instrumento permanece como procedencia de su medición. No se divulgan rutas de archivos ni hashes mediante la nueva proyección clínica.

## Superadministración y mapas

`PanoramaResidentes` consulta diagnósticos o alergias de residentes `ADMITIDO`, con permisos explícitos del campo. Reutiliza los estados de la ficha clínica: diagnósticos ACTIVO/ACTIVA/VIGENTE y alergias ACTIVO/ACTIVA. Excluye fechas futuras y diagnósticos de atenciones anuladas, canceladas o incompatibles con el residente.

Las barras cuentan residentes distintos por etiqueta exacta y usan el universo de la búsqueda antes de paginar. Un residente puede aparecer en varias barras. Se muestran número, denominador, porcentaje y ausencia de registro activo. Seleccionar una barra filtra el directorio; elegir una persona presenta sus campos y autoría junto a la gráfica. Estos recuentos descriptivos no constituyen prevalencia clínica ni generan diagnósticos.

Por defecto, el panorama y el directorio administrativo excluyen el lote COH30 por documento nominal y marca de admisión, incluso si después se deshabilita su capacidad local. El selector **30 casos sintéticos de investigación** consulta exclusivamente ese lote cuando el entorno y la base exacta lo permiten. Cambiar de población limpia búsqueda, patrón y residente seleccionado; los denominadores nunca mezclan ambos grupos.

El mapa por residente comienza enfocado en la selección y sus vecinos; conserva todos los nodos accesibles mediante lista/paginación. Centrar o seguir una conexión recupera escala 100 %, sin comprimir el texto para encajar todo el expediente. El usuario puede reducirla explícitamente. La consulta administrativa sitúa los campos junto al lienzo cuando hay espacio y los reorganiza en una columna en pantallas estrechas.

## Integración de instalaciones: diagnóstico previo y resultado

Antes de la carga, la conexión PostgreSQL local `RememberMind` tenía:

- 73 residentes admitidos.
- Ningún caso identificado por documento COH30 y marca de admisión del lote.
- Ninguna versión del conocimiento en SQL.
- Capacidad de simulación local no habilitada.
- `remembermind_dev` no existe en el servidor PostgreSQL local activo. Los procesos PostgreSQL observados solo escuchan en el puerto 5432.

Las actas históricas describen otra instalación con 30 casos cargados. No se encontraron sus archivos `carga.json` ni un respaldo de esa cohorte en los directorios privados del proyecto revisados. El respaldo anterior de La Paz pertenece a otra carga y no se sustituye por aquella fuente.

La [revisión de Git](../sistema-experto/LOCALIZACION_COHORTE_GIT_2026_10_22.md) localizó el commit `f855ccdf`, ya integrado, con el seeder y los 30 casos reconstruibles. No encontró el dump de aquella instalación. El único respaldo SQL versionado hallado pertenece al modelo V1 y no se restauró.

El 23/10, con la autorización de completar los seeders, se reconstruyó aditivamente la cohorte desde sus definiciones vigentes. Resultado: **103 residentes (73 anteriores preservados + 30 sintéticos), dos paquetes de conocimiento y 30 cálculos coincidentes con el manifiesto técnico**. Una segunda verificación agregó cero filas y encontró cero discrepancias. No se recuperó ni fusionó la base inexistente ni su otra historia. Los resultados se calcularon desde SQL, sin guardar salidas esperadas ni firmas profesionales.

El [acta de instalación](../sistema-experto/COMPLETAR_EXPERTO_LOCAL_2026_10_23.md) describe respaldo, migraciones aprobadas, instaladores de permisos, fallos revertidos, comprobaciones y límites. `.env` conserva la conexión existente; únicamente se habilitaron las dos claves locales del lote después de verificarlo.

## Pruebas y aceptación

Se ejecutan archivos concretos con SQLite efímera mediante:

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit tests/Feature/PanoramaResidentesTest.php
node --test tests/Frontend/expert-resident-workspace.test.js tests/Frontend/orion-explorador.test.js
npm run build
```

| Archivo / comprobación | Resultado |
|---|---|
| `ConsultaConectadaSuperadminTest` + `InformeNeurocognitivoTest`, última repetición | 28 pruebas / 355 aserciones |
| `EjecucionConsultaCognitivaTest` | 8 / 58 |
| `ExploracionClinicaVisibleTest` + `PanoramaResidentesTest`, última repetición | 7 / 50; incluye rol familiar adicional y ausencia de lectura de alergias |
| `NavegacionSistemaExpertoTest` | 4 / 38 |
| `SimulacionLocalExpertoTest` (dos pruebas concretas), última repetición | 2 / 21; lectura de Enfermería e IDOR/asignación revocada |
| `InformeNeurocognitivoTest` | 23 / 333, archivo completo repetido después de las correcciones |
| `CohorteInferencia30Test::test_treinta_admisiones_fuentes_originales_dos_ramas_y_cuatro_perfiles_reproducibles` | 1 / 2174, SQLite efímera; no carga en la base operativa |
| Frontend: workspace + explorador | 15 pruebas |
| Preparación local explícita, PostgreSQL desechable | 1 / 17; rollback, conservación, permisos, separación de población y cálculo desde SQL |
| Seeders de permisos Petersen/IADL | 2 / 28 |
| Pint (7 archivos PHP) | PASS |
| Build | PASS; advertencia existente por chunk JS mayor de 500 kB |

La primera ejecución de `InformeNeurocognitivoTest` tuvo dos fallos: el estado de revisión pendiente no aparecía y la plantilla histórica mostraba la versión técnica a una lectura sin auditoría. Se restauró el estado profesional y se condicionó la versión histórica a `consultarTrazabilidad`; la repetición completa pasó. Las fixtures nuevas también se corrigieron para enlazar explícitamente la cuenta de la autora y esperar el rechazo de una referencia cruzada por el trigger existente. No se debilitaron esos invariantes.

La preparación explícita se verificó en PostgreSQL desechable. Sobre la instalación local se verificaron 25 tablas expertas, 116 físicas, siete FK trasladadas, lectura de ambas versiones y los 30 cálculos del lote. No se ejecutó una suite global ni se afirma validación clínica. Una petición HTTP de Enfermería coincidió con mantenimiento y recibió 503; repetida después del levantamiento, las dos pruebas pasaron.

La selección de área en el panel utiliza `seleccionarCriterio` ya reautorizado; el selector local recupera el criterio vigente cuando se renueva su contenido. La ruta de cuatro pasos permite abrir fuentes, áreas, requisitos y enfocar el resultado mediante teclado. El recorrido del mapa, las páginas y la selección conservan las fuentes completas. La última repetición de Node pasó 15 pruebas; el último build pasó en 26,70 s. La compilación de Blade también pasó.

QA visual: **FAIL por cobertura incompleta**, sin atribuir defectos de layout no observados. Revisión de seguridad del cambio: separación de lectura/ejecución, reautorización de la cuenta, permisos del campo, fuente/residente, auditoría técnica y DTO mínimo; las pruebas negativas citadas complementan la revisión. Sin nueva aprobación clínica.

El acceso CUA devolvió `Local browser use is disabled in Work settings`. No se utilizaron otros navegadores, CDP ni herramientas de terminal para eludirlo. La inspección visual autenticada y la matriz 1440/1280/1024/768/390, claro/oscuro, permanecen pendientes. El build y las pruebas de Livewire no equivalen a aceptación visual ni validación clínica. No se repite como evidencia actual la matriz visual de la entrega anterior.

Sin commit de la interfaz: falta aceptación visual. No hay push ni despliegue. Los artefactos de `output/` y los respaldos privados quedan fuera de Git. Los cambios concurrentes en Valoraciones, guía/avatar y sus recursos no pertenecen a esta implementación y se conservaron.
