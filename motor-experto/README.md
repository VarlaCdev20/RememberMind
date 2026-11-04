# Sistema experto: empieza aquí

La documentación base de investigación no da nombre al producto. La lectura usa `ArchivoConocimientoCognitivo`; el importador documental anterior está retirado. Los identificadores ya guardados se concentran en [IdentificadoresHistoricos.php](conservacion/IdentificadoresHistoricos.php) para conservar versiones, restricciones y huellas. Las pantallas muestran etiquetas neutras y los manifiestos originales mantienen sus bytes.

El código principal está directamente en **seis carpetas por componente dentro de `motor-experto/`**. No hay carpetas intermedias `src/motor_experto/`. `src` era una convención para separar código fuente, no un requisito de PHP o Composer.

```text
motor-experto/
├── conocimiento/   → reglas y red semántica
├── hechos/         → fuentes, contexto y memoria del caso
├── inferencia/     → ejecución de reglas y clasificación orientativa
├── explicacion/    → trazas, motivos y procedencia
├── instrumentos/   → captura, puntuación y métodos instrumentales
└── conservacion/   → instantáneas, integridad y reproducción
```

## Los archivos que necesitas abrir

| Quiero ver… | Archivo |
|---|---|
| Las 17 reglas cognitivas | [ReglasCognitivas.php](conocimiento/ReglasCognitivas.php): `reglasG09()` y `MEMORIA`. |
| Las 4 reglas Petersen | [ReglasPetersen.php](conocimiento/ReglasPetersen.php): `condiciones()` y sus resultados. |
| Las 3 reglas observacionales | [ReglasObservacionales.php](conocimiento/ReglasObservacionales.php): constante `REGLAS`. |
| La red semántica | [RedSemantica.php](conocimiento/RedSemantica.php) y [PerfilConceptualCognitivo.php](conocimiento/PerfilConceptualCognitivo.php). |
| Qué hechos recibe el motor | [ConstructorMemoriaInstrumental.php](hechos/ConstructorMemoriaInstrumental.php) y [MemoriaTrabajo.php](hechos/MemoriaTrabajo.php). |
| Cómo razona con esos hechos | [EvaluarPerfilInstitucional.php](inferencia/EvaluarPerfilInstitucional.php); [EvaluarPetersenInstitucional.php](inferencia/EvaluarPetersenInstitucional.php). |
| Cómo explica el resultado | [ConstructorTraza.php](explicacion/ConstructorTraza.php). |
| MoCA, RUDAS-PE y FAQ | [CircuitoInstrumentalInicial.php](instrumentos/CircuitoInstrumentalInicial.php). |
| Resultado y explicación adquiridos | [InstantaneaEvaluacionInstitucional.php](conservacion/InstantaneaEvaluacionInstitucional.php); su guardado conjunto sigue pendiente. |

Recorrido: **originales autorizados → hechos → conocimiento → inferencia → resultado y explicación**.

Las reglas registradas y sus versiones vigentes están en la BDD; los tres archivos `Reglas*` definen los paquetes iniciales. El motor aplica la versión que recibe: no se modifican las reglas actuales de un residente editando una pantalla o estos constructores.

## Dónde se conecta con Laravel

La entrada del caso completo es [PrepararEvaluacionInstitucional.php](../app/Modulos/ApoyoDecision/Aplicacion/Consultas/PrepararEvaluacionInstitucional.php). Autorización, lectura SQL, pantallas y persistencia permanecen en `app/Modulos/ApoyoDecision/`. Los formularios originales continúan en sus módulos institucionales. No se necesita abrirlos para localizar las reglas.

`dominio/` conserva tipos y contratos con consumidores e identidad histórica; no es una segunda entrada al motor institucional. `aplicacion/puertos/` contiene el contrato técnico anterior usado por las regresiones aisladas. `tests/fixtures/` conserva exclusivamente pruebas retiradas del producto. No se trasladan ni mezclan con las seis carpetas principales.

## Verificación de la reorganización del 04/11/2026

Primero se agruparon 62 archivos del recorrido principal en seis componentes; después se quitaron las dos carpetas intermedias y los 105 archivos productivos quedaron directamente bajo `motor-experto/`. Composer, ejecutores independientes, recuperación de software, pruebas y enlaces usan la ubicación directa. Los artefactos históricos conservan sus rutas y huellas originales para lectura, y las nuevas recuperaciones excluyen fixtures. No cambia ninguna regla, umbral, permiso, esquema o dato. Las comprobaciones están en el [mapa técnico vigente](../docs/sistema-experto/organizacion/MAPA_COMPONENTES.md).

## Antecedentes técnicos conservados

## Consolidación del 18/10/2026

Los tipos y contratos anteriores permanecen en `dominio/`. Aplicación e infraestructura Laravel residen en `app/Modulos/ApoyoDecision`. No existen alias de clases antiguas.

`soporte/conservacion/CodecInstantanea` mantiene la identidad contractual original al calcular huellas de objetos y enums del dominio trasladado. Es una identidad del formato histórico, no una dependencia ejecutable de `App`. Se conservaron el orden, tipos y contenido de los objetos; no se reescribieron instantáneas ni reglas.

Verificación adicional independiente: `php motor-experto/tests/dominio_independencia.php`. Las pruebas `CodecIdentidadHistoricaTest` comparan siete huellas originales: enum/fecha/NULL/cero, memoria, requisitos, vector, clasificaciones positiva/abstención y regla con AST anidado. El cambio de ubicación no modifica los contratos experimentales ni los activa clínicamente.

El producto contiene los evaluadores institucionales, conocimiento versionado, red semántica, memoria de trabajo y explicación. El piloto COG-MEM y los algoritmos exclusivos del laboratorio fueron retirados de `src/` y aislados bajo `tests/fixtures/` el 27/10/2026.

`MotorExperto\` se carga directamente desde `motor-experto/`; no requiere Laravel, Livewire ni Eloquent. Composer excluye `tests/` de la exploración de clases; los fixtures históricos se cargan exclusivamente mediante `tests/bootstrap.php`. El adaptador puro transforma hechos ya extraidos; la lectura Eloquent queda en Laravel.

Verificacion independiente: `php motor-experto/tests/independencia.php`. Ejecuta cuatro fixtures tecnicos validos y comprueba el rechazo del fixture con cobertura insuficiente, sin arrancar Laravel ni conectar una base de datos. Las pruebas de regresion completas del piloto permanecen en `tests/Unit/SistemaExperto` y `tests/Feature/SistemaExperto` de la raiz, usando este motor separado.

El análisis multicriterio y el adaptador de salida remoto siguen pendientes porque no existe una metodología/servicio aprobado que migrar. No se conservan directorios de reserva vacíos para esas capacidades; la documentación mantiene su alcance pendiente. No son evidencia de inferencia clínica implementada.
