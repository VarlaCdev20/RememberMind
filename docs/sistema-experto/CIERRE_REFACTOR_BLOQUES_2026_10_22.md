# Cierre de la refactorización de cuatro bloques expertos

**22/10/2026. Implementada, integrada y aplicada en PostgreSQL local.** Este cierre corresponde a las cuatro consolidaciones autorizadas. La conservación íntegra de `EVALUACION-INSTITUCIONAL-1` no queda cerrada por esta entrega.

## Resultado instalado

| Inventario | Antes | Después |
|---|---:|---:|
| Operativas | 75 | 75 |
| Expertas | 29 | 25 |
| Negocio | **104** | **100** |
| Técnicas | 16 | 16 |
| Total físico | 120 | 116 |

Se trasladaron **147 resultados, 18 trazas, 87 variables y 5 clasificaciones: 257 filas**. No se borró su contenido ni se renumeraron sus identidades. La huella SHA-256 de la proyección completa, con campos ordenados para comparar, fue idéntica antes y después:

`31acc560bc09b9f14c9a80f7619fbfb7fb9fb0d10acc25494a0cad63899b3a2d`

La instalación confirmó a las **06:37:50 UTC** el destino `remembermind_dev`, tomó un respaldo privado PostgreSQL en `%LOCALAPPDATA%/RememberMind/backups/`, comprobó su catálogo y aplicó sólo `2026_10_22_120000_consolidate_bloques_expertos_singulares`. El respaldo tiene SHA-256 `2f6714c7b69904ee40aafb57ad0453f7ee54beb19bde1e5e34e3c7e0adb3ee91`. Sus datos no se incorporan al repositorio ni se envían a servicios externos. No hubo fresh, seed, migraciones ajenas, commit, push o despliegue.

El comando de comprobación posterior, dentro de una transacción PostgreSQL de **sólo lectura**, confirmó:

- 25 relaciones expertas presentes y ninguna ausente.
- Las cuatro relaciones retiradas no existen como tablas ni vistas.
- Siete FK entrantes con los destinos nominales aprobados.
- Lectura Eloquent de las mismas 257 entidades, incluidas sus relaciones.
- Lectura del conocimiento de las seis versiones instaladas.
- Recuperación de las cinco clasificaciones **sintéticas** existentes, comparando sus huellas conservadas. No se transformaron en valoraciones reales.
- Anchuras de 19 columnas en criterio, 19 en nodo y 27 en evaluación.

Evidencias agregadas: [preservación instalada](../auditorias/refactor-bloques-2026-10-22/preservacion-instalada.json) y [verificación de lectores y FK](../auditorias/refactor-bloques-2026-10-22/verificacion-instalada.json). No contienen respuestas, nombres de residentes ni datos clínicos originales.

## Modelo e integración

El [diccionario y decisión aprobada](../base-de-datos/DECISION_CONSOLIDACION_BLOQUES_EXPERTOS_2026_10_22.md) contienen las 28 columnas, tipos, nulabilidad, claves alternativas, FK, índices, guardas, justificación de normalización, diagrama y reversión.

Las cuatro dependencias 0..1 se incorporan a su propietario; no se fusionan colecciones independientes. Resultado y traza conservan ausencias independientes. La variable mantiene estado, autor, fecha y observación propios, aunque difieran del nodo. El conocimiento original del nodo se proyecta sin esos atributos añadidos para preservar el contrato del motor y sus huellas.

`BloquesExpertos` define cuatro proyecciones cerradas y un escritor transaccional que sólo completa un bloque ausente. Los modelos lógicos mantienen sus PK, relaciones y casts. No se mantiene una copia paralela ni una vista que simule la reducción. Los escritores del conocimiento, del seguimiento y de la conservación existente se conectan a ese destino real. El motor PHP no se reemplazó.

Se conserva el gobierno de reglas, archivo y sucesión histórica; la reducción no concede permisos. El recorrido observacional sigue pudiendo guardar, recuperar y recibir revisiones independientes de Medicina y Psicología sin alterar el resultado. No se permite revisar el DTO compuesto transitorio.

## Código y trazabilidad

Todas las rutas siguientes son relativas a la raíz del repositorio:

| Requisito | Código principal | Prueba/evidencia |
|---|---|---|
| Copia reversible y comprobada, 104→100 | `database/migrations/2026_10_22_120000_consolidate_bloques_expertos_singulares.php`; `app/Modulos/ApoyoDecision/Infraestructura/Persistencia/Conservacion/TransicionBloquesExpertos.php` | `ConsolidacionBloquesExpertosTest`; proyección instalada |
| Campos nominales y escritura única | `.../Conservacion/BloquesExpertos.php` | bloques incompletos, ausencia independiente y concurrencia PostgreSQL |
| Relaciones Eloquent originales | `app/Models/ModeloBloqueExperto.php`; `VariableExperta.php`, `ResultadoCriterio.php`, `TrazaInferencia.php`, `ClasificacionDclInstantanea.php` | repositorios, mappers y verificador instalado |
| Conocimiento y versiones | `.../Persistencia/Eloquent/CargadorConocimiento.php`; `IdentidadesConocimiento.php`; `Aplicacion/Servicios/ContratosConocimiento.php` | `ConocimientoCognitivoInstitucionalTest`, `GestionConocimientoExperimentalTest`, `GobiernoReglasExpertasTest` |
| Cargas e inferencia existente | acciones `CargarConocimientoOrion`, `CargarPaqueteInvestigadoCOGMEM`, `RegistrarConocimientoInstitucional`, `RegistrarConocimientoPetersen`, `GestionarConocimientoExperimental`, `GestionarReglasExpertas`, `PersistirEvaluacionTecnica` | conocimiento e integración de cinco dominios |
| Lectura/escritura histórica | `.../Conservacion/AlmacenInstantaneas.php`, `Persistencia/ConservarObservaciones.php`, `PaqueteObservacional.php`, `Aplicacion/Consultas/ConsultarObservacionesConservadas.php` | `ConservacionExpertaTest`, `CicloObservacionalConservadoTest` |
| Códigos e inventario | `app/Compartido/Infraestructura/Persistencia/CatalogoCodigos.php`; `.../Persistencia/InventarioExperto.php` | `CodigosCorrelativosTest`, `InventarioExpertoTest` |
| Cohorte sintética histórica conservada | `.../Simulacion/PaqueteSimulacionLocal.php`, `TrazaSimulacionLocal.php` | `SimulacionLocalExpertoTest`; cinco huellas instaladas |
| Instalación con respaldo y lectura posterior | `scripts/consolidar-bloques-expertos.php`; `scripts/verificar-consolidacion-experta.php` | JSON agregados de instalación |

Los helpers de pruebas se actualizaron para preparar los bloques físicos. Las corrupciones deliberadas se realizan únicamente mediante `CorromperConservacionParaPrueba`, limitado a destinos desechables; se retiran/restauran temporalmente sus guardas para probar también la detección del lector. No se introduce una vía de mutación en la aplicación. Las pruebas de migraciones anteriores retroceden primero esta consolidación para comprobar cada transición en su esquema histórico real.

## Pruebas ejecutadas

Se usaron SQLite `:memory:` y clusters PostgreSQL 18 creados/eliminados por el runner. Los conteos son por ejecución, **no deben sumarse como pruebas únicas** porque hay solapamientos.

| Comando/grupo | Resultado |
|---|---|
| `php artisan test --filter='ConocimientoCognitivoInstitucionalTest\|ContratoMapeadoresTest'` | 58 pruebas / 678 aserciones satisfactorias |
| `php artisan test --filter='GestionConocimientoExperimentalTest\|CodigosCorrelativosTest\|SistemaExperto\\Infrastructure\\Repositories'` | 108 satisfactorias / 1123 aserciones; una omisión específica de PostgreSQL en SQLite |
| `php artisan test --filter='SistemaExpertoOrionNivel1Test\|PreparacionCompuestaCoherenteTest\|ConsolidacionBloquesExpertosTest\|CicloObservacionalConservadoTest\|InventarioExpertoTest\|CapturaPetersenOriginalTest'` | 51 satisfactorias / 483 aserciones; tres carreras PostgreSQL omitidas en SQLite |
| `scripts/probar-postgres.ps1 -Filtro 'ConsolidacionBloquesExpertosTest\|ConservacionExpertaTest\|CicloObservacionalConservadoTest\|RevisionExpertaConcurrenciaPostgresTest\|ConservacionCierrePostgresTest'` | 40 pruebas / 266 aserciones satisfactorias |
| `scripts/probar-postgres.ps1 -Filtro ConsolidacionBloquesConcurrenciaPostgresTest` | Una prueba / cinco aserciones: dos escritores sobre la tabla real, timeout de bloqueo y reintento sin sustitución |
| `npm run build` | Satisfactorio; Vite compiló el árbol local existente |
| `php scripts/verificar-consolidacion-experta.php` | Satisfactorio, lectura instalada sin mutaciones |

El caso `ConservacionCierrePostgresTest` corresponde a un esquema de ensayo aislado ya existente: no se presenta como cierre completo del DTO institucional. La prueba nueva `ConsolidacionBloquesConcurrenciaPostgresTest` sí usa los bloques de producción migrados sobre una base desechable. La revisión concurrente existente también se prueba con sus tablas reales.

Se detectaron y corrigieron tres expectativas antiguas de la interfaz: la frase del perfil pendiente, la ausencia de una gráfica vacía cuando todos los dominios están sin valorar y la ocultación del código de versión. Se preservaron las comprobaciones de hallazgos, no clasificación y acciones. Los resultados finales de esa regresión y de las pruebas ampliadas de transición se registran al pie del informe.

### Ejecuciones finales

- **156 pruebas / 831 aserciones satisfactorias** en gobierno, informe, revisión clínica existente, simulación segregada, hallazgos, sucesión y revisión observacional. [JUnit](../auditorias/refactor-bloques-2026-10-22/interfaz-regresion-sqlite.xml).
- **Siete pruebas / 166 aserciones satisfactorias** en SQLite para la transición final; incluye las cuatro combinaciones de ausencia/presencia independiente de resultado y traza y el rechazo de estructura inesperada aun con una tabla vacía. [JUnit](../auditorias/refactor-bloques-2026-10-22/migracion-sqlite.xml).
- **Ocho pruebas / 63 aserciones satisfactorias** en PostgreSQL para esa transición final y la carrera sobre el bloque real. [JUnit](../auditorias/refactor-bloques-2026-10-22/migracion-concurrencia-postgres.xml).
- [Integración PostgreSQL, 40 pruebas / 266 aserciones](../auditorias/refactor-bloques-2026-10-22/integracion-postgres.xml), ejecutada antes de las dos pruebas negativas adicionales. No se suman estos conteos como casos únicos.

Comandos exactos, desde la raíz del repositorio:

```powershell
php artisan test --filter='ConocimientoCognitivoInstitucionalTest|ContratoMapeadoresTest' --compact
php artisan test --filter='GestionConocimientoExperimentalTest|CodigosCorrelativosTest|SistemaExperto\\Infrastructure\\Repositories' --compact
php artisan test --filter='GobiernoReglasExpertasTest|InformeCognitivoTest|RevisionValoracionExpertaTest|SimulacionLocalExpertoTest|HallazgosObservacionalesTest|SucesionObservacionalTest|RevisionObservacionalExpertaTest' --compact --log-junit=work/b4-interfaz-regresion.xml
php artisan test --filter=ConsolidacionBloquesExpertosTest --compact --log-junit=work/b4-migracion-sqlite.xml
& scripts/probar-postgres.ps1 -Filtro 'ConsolidacionBloquesExpertosTest|ConsolidacionBloquesConcurrenciaPostgresTest'
& scripts/probar-postgres.ps1 -Filtro 'ConsolidacionBloquesExpertosTest|ConservacionExpertaTest|CicloObservacionalConservadoTest|RevisionExpertaConcurrenciaPostgresTest|ConservacionCierrePostgresTest'
npm run build
php scripts/verificar-consolidacion-experta.php
git status --short
git diff --stat
```

## Aceptación y pendientes materiales

| Área | Estado de esta entrega |
|---|---|
| Cuatro consolidaciones, lectores/escritores e inventario | **Implementado, probado e instalado** |
| Recuperación exacta de los cuatro bloques existentes | **Verificada en SQLite, PostgreSQL desechable y base local instalada** |
| Reversión poblada y rollback de fallo parcial | **Verificados en bases desechables**; no se revirtió la base institucional |
| Concurrencia del escritor de bloque | **Verificada con dos conexiones PostgreSQL** |
| Reglas, cinco dominios y Petersen existentes | Reutilizados; no se declara una nueva aprobación clínica |
| Observaciones clínicas actuales | Continúan siendo observaciones; no se inventaron medidas ni firmas |
| Conservación compuesta instrumental/Petersen | **Pendiente material**, no implementada por esta reducción |
| Revisiones del DTO compuesto | Bloqueadas hasta disponer de una evaluación conjunta durable y recuperable |
| Validación clínica | No acreditada por pruebas técnicas |

El presupuesto pasa a disponer de diez relaciones antes del máximo 110. Eso **no demuestra** que las entradas/salidas completas del DTO compuesto quepan en diez relaciones nuevas ni autoriza su instalación. El [ensayo nominal anterior](ENSAYO_NOMINAL_CONSERVACION_2026_10_22.md) sigue siendo evidencia de brechas, no diseño instalado: sus tablas adicionales no se crearon. Falta cerrar un diccionario completo y mantenible de las entradas, conocimiento adquirido, salidas y membresías; demostrar recuperación exacta/replay de ambas ramas; y aprobar específicamente sus modificaciones estructurales. `persistido=false` se mantiene en ese recorrido. No se oculta este límite para declarar culminación total.

Git conserva la rama y los cambios simultáneos del árbol. No se ejecutaron add, commit, push, cambio de rama ni despliegue. La comprobación de whitespace en las rutas propias del refactor pasó; el diff general contiene espacios finales preexistentes en la agenda de Enfermería, fuera de este cambio.

Continuación documentada del 22/10: [lectura íntegra y replay separados](CONSERVACION_LECTURA_Y_REPLAY_2026_10_22.md). Añade la API de lectura sin inferencia y las pruebas de integridad, e inventaría las salidas omitidas por el mapa anterior. No instala otro esquema ni cierra la conservación SQL compuesta.
