# Infrastructure de O.R.I.O.N.

Alcance: PoC experimental con datos sintéticos. Las lecturas se restringen a
`testing` y a destinos admitidos por `BaseTecnicaDesechable`. No hay registro
automático en providers, acceso asistencial, inferencia clínica ni cambios de esquema.

## Componentes implementados

```text
Infrastructure/
├── Adapters/
│   ├── FuentesOperativas/
│   │   ├── AdaptadorControlesCognitivos.php
│   │   ├── AdaptadorIadlExperimental.php
│   │   ├── AdaptadorInstrumentos.php
│   │   ├── AdaptadorSeguimientosPedagogicos.php
│   │   └── AdaptadorValoracionesFuncionales.php
│   └── MapeoFuentes/
│       ├── ExtractorVariableMapeada.php
│       └── ResolutorFuenteExperta.php
├── Configuracion/
│   └── VinculadorRepositorios.php
└── Persistence/
    ├── Models/       (reservado; sin identidades Eloquent duplicadas)
    ├── Mappers/
    │   ├── MapeadorEvidencias.php
    │   ├── MapeadorRedSemantica.php
    │   ├── MapeadorReglas.php
    │   └── MapeadorResultados.php
    └── Repositories/
        ├── EvaluacionExpertaEloquentRepository.php
        ├── EvidenciaEloquentRepository.php
        ├── RedSemanticaEloquentRepository.php
        └── ReglaExpertaEloquentRepository.php
```

Las dieciséis clases incluyen `declare(strict_types=1);` y PHPDoc en español.
Los modelos permanecen en `App\Models` y conservan sus PK `cod_*`.

| Puerto de Domain | Implementación en Persistence/Repositories | Mapper |
|---|---|---|
| `Conocimiento\ReglaExpertaRepositoryInterface` | `ReglaExpertaEloquentRepository` | `MapeadorReglas` |
| `Conocimiento\RedSemanticaRepositoryInterface` | `RedSemanticaEloquentRepository` | `MapeadorRedSemantica` |
| `Hechos\EvidenciaRepositoryInterface` | `EvidenciaEloquentRepository` | `MapeadorEvidencias` |
| `Evaluaciones\EvaluacionExpertaRepositoryInterface` | `EvaluacionExpertaEloquentRepository` | `MapeadorResultados` |

Los puertos tienen prefijo `App\SistemaExperto\Domain\Repositories`.

## Registro explícito en Laravel

`VinculadorRepositorios` recibe por constructor, en este orden:

1. Nombre explícito de la conexión experimental.
2. `DateTimeZone` del almacenamiento SQL, compartida por todo el grafo.
3. Tipo literal de regla del contrato versionado.
4. Lista de roles de soporte autorizados. Vacía no autoriza soportes.
5. Registro `clave_adaptador => instancia conocida`. Los selectores instrumentales
   se entregan a `AdaptadorInstrumentos` agrupados por versión del conocimiento.
6. `Closure(string $evaluacionId, string $residenteId, string $versionConocimiento,
   DateTimeImmutable $fechaCorte): MemoriaTrabajo` que recupera la instantánea original.

Después de construirlo, `registrar($contenedorLaravel)` comprueba `testing` y el
destino desechable antes de registrar catorce bindings: cuatro interfaces,
cuatro implementaciones concretas, cuatro mappers, resolver y extractor.
Una interfaz y su implementación resuelven la misma instancia. Estos componentes
no conservan resultados por caso; el recuperador de memoria se invoca con el
contexto completo en cada lectura de evaluación.

Repetir `registrar` con la misma instancia es idempotente. Una configuración
distinta, binding, alias o instancia ajenos producen `LogicException` sin
sobrescribirlos ni ejecutar sus factories. No se registran nuevamente
`ConocimientoVersionado`, `ExtraccionFuentesCOGMEM` o `FuenteConocimientoOrion`
de `ApoyoDecision`.

No se añade el vinculador a `bootstrap/providers.php` ni se modifican providers
existentes. Las pruebas muestran la llamada explícita y su resolución completa
en [VinculadorRepositoriosFeatureTest](../../../tests/Feature/SistemaExperto/Infrastructure/Configuracion/VinculadorRepositoriosFeatureTest.php).
Un registro futuro en el arranque institucional permanece fuera de esta entrega.

## Fuentes y límites de representación

| Adaptador | Fuente física | Alcance |
|---|---|---|
| Controles cognitivos | `controles_cognitivos` | Campos explícitos, observaciones descriptivas y booleanos nullable |
| Seguimientos pedagógicos | `seguimientos_pedagogicos` | Campos explícitos y actividad original como episodio |
| Valoraciones funcionales | `valoraciones_funcionales` | BADL como hechos; no sustituye IADL ni acredita C03 |
| Instrumentos | `aplicaciones_instrumento`, `instrumentos`, `respuestas_instrumento`, `preguntas_instrumento`, `opciones_pregunta` | Totales brutos o respuestas mediante selector registrado y versión formal exacta |
| IADL experimental | `controles_funcionales_iadl` | No habilitado: informa ausencia física o falta de contrato de lectura aprobado |

`NULL` permanece `NULL`. Una fila ausente es un error de disponibilidad y no
un resultado normal. Un booleano `false` y un cero conocido no son ausencia.
Los accessors de presentación no se usan para completar valores.
Los decimales se transportan como texto sin baremación; se respeta la
materialización del motor SQL, sin declarar que SQLite preserve su escala como PostgreSQL.
Las aplicaciones instrumentales deben conservar su estado original `COMPLETA`.

Las fuentes posteriores al corte solo aportan metadatos para excluirlas;
no se leen sus valores ni respuestas. Se contrastan residente, versión, estado,
fecha, episodio y procedencia. Una misma fuente/episodio no se convierte en
corroboración independiente.

D-137 no conserva el corte clínico ni toda la memoria original de Domain.
El vinculador exige un recuperador explícito y no fabrica un almacén ni una
instantánea vacía para ocultar esa limitación. Los literales descriptivos G09
no se convierten en A/P/U/I/X. Los resultados de Domain solo se recuperan si sus
literales, soportes y memoria originales son representables y coherentes.

`guardar`, `guardarClasificacion` y la recuperación DCL de una evaluación
existente fallan explícitamente: el esquema no representa íntegramente la
instantánea ni C01–C05 y su versión contractual. No hay escrituras parciales,
diagnósticos persistidos ni confirmaciones ficticias de conservación.

## Modelos existentes y traslado posterior

Las veintitrés correspondencias físicas D-137 se verifican en las pruebas de
contrato; no se crean modelos alternativos en `Persistence/Models`.

| Tabla | Modelo en App\Models |
|---|---|
| versiones_modelo_experto | VersionModeloExperto |
| nodos_semanticos | NodoSemantico |
| relaciones_semanticas | RelacionSemantica |
| dominios_valores_expertos | DominioValoresExperto |
| valores_semanticos | ValorSemantico |
| variables_expertas | VariableExperta |
| fuentes_datos_expertas | FuenteDatoExperta |
| mapeos_variables_fuente | MapeoVariableFuente |
| mapeos_valores_fuente | MapeoValorFuente |
| criterios_dominios_resultado | CriterioDominioResultado |
| reglas_expertas | ReglaExperta |
| condiciones_regla_experta | CondicionReglaExperta |
| consecuencias_regla_experta | ConsecuenciaReglaExperta |
| evaluaciones_expertas | EvaluacionExperta |
| evidencias_evaluacion | EvidenciaEvaluacion |
| relaciones_evidencias_evaluacion | RelacionEvidenciaEvaluacion |
| evaluacion_criterios | EvaluacionCriterio |
| evidencias_criterio_evaluacion | EvidenciaCriterioEvaluacion |
| resultados_criterio | ResultadoCriterio |
| trazas_inferencia | TrazaInferencia |
| evaluaciones_reglas | EvaluacionRegla |
| evaluaciones_condiciones_regla | EvaluacionCondicionRegla |
| evidencias_soporte_condicion | EvidenciaSoporteCondicion |

Un traslado posterior requiere una tarea aprobada y coordinada con el ADR híbrido:
inventariar imports, relaciones, `BuilderExperto`, Policies, bindings de
`ApoyoDecision`, comandos, tests y cualquier identidad polimórfica/auditada;
mover cada clase una sola vez; actualizar consumidores y verificar lectura,
historia y autorización. No cambiar tablas, PK/FK ni crear aliases permanentes
para mantener dos identidades Eloquent. Esta entrega no realiza ese traslado.

## Verificación reproducible

SQLite en memoria y unitarias del módulo:

```powershell
php vendor/phpunit/phpunit/phpunit --do-not-cache-result tests/Unit/SistemaExperto tests/Feature/SistemaExperto/Infrastructure
```

Integración PostgreSQL con clúster temporal y runner propio:

```powershell
./scripts/probar-postgres.ps1 -Filtro 'Tests\\Feature\\SistemaExperto\\Infrastructure'
```

`Tests\TestCase` rechaza destinos no desechables antes de preparar migraciones.
El script PostgreSQL crea y elimina su propio clúster, valida rutas de limpieza
y restaura las variables del proceso. Las unitarias del vinculador prohíben
abrir PDO. No se usa `.env` para apuntar las pruebas a una base institucional.

Las suites técnicas acreditan los casos ejecutados en cada motor. No constituyen
validación clínica ni prueban una integración asistencial que continúa deshabilitada.
