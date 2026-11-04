# Sistema experto: empieza aquí

El código principal está agrupado en **seis carpetas por componente**, sin niveles de `dominio/conocimiento/versiones`, `adaptadores/entrada` o `aplicacion/casos_uso` para recorrer el experto institucional.

```text
src/motor_experto/
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
| Las 17 reglas cognitivas | [ReglasCognitivas.php](src/motor_experto/conocimiento/ReglasCognitivas.php): `reglasG09()` y `MEMORIA`. |
| Las 4 reglas Petersen | [ReglasPetersen.php](src/motor_experto/conocimiento/ReglasPetersen.php): `condiciones()` y sus resultados. |
| Las 3 reglas observacionales | [ReglasObservacionales.php](src/motor_experto/conocimiento/ReglasObservacionales.php): constante `REGLAS`. |
| La red semántica | [RedSemantica.php](src/motor_experto/conocimiento/RedSemantica.php) y [PerfilConceptualCognitivo.php](src/motor_experto/conocimiento/PerfilConceptualCognitivo.php). |
| Qué hechos recibe el motor | [ConstructorMemoriaInstrumental.php](src/motor_experto/hechos/ConstructorMemoriaInstrumental.php) y [MemoriaTrabajo.php](src/motor_experto/hechos/MemoriaTrabajo.php). |
| Cómo razona con esos hechos | [EvaluarPerfilInstitucional.php](src/motor_experto/inferencia/EvaluarPerfilInstitucional.php); [EvaluarPetersenInstitucional.php](src/motor_experto/inferencia/EvaluarPetersenInstitucional.php). |
| Cómo explica el resultado | [ConstructorTraza.php](src/motor_experto/explicacion/ConstructorTraza.php). |
| MoCA, RUDAS-PE y FAQ | [CircuitoInstrumentalInicial.php](src/motor_experto/instrumentos/CircuitoInstrumentalInicial.php). |
| Resultado y explicación adquiridos | [InstantaneaEvaluacionInstitucional.php](src/motor_experto/conservacion/InstantaneaEvaluacionInstitucional.php); su guardado conjunto sigue pendiente. |

Recorrido: **originales autorizados → hechos → conocimiento → inferencia → resultado y explicación**.

Las reglas registradas y sus versiones vigentes están en la BDD; los tres archivos `Reglas*` definen los paquetes iniciales. El motor aplica la versión que recibe: no se modifican las reglas actuales de un residente editando una pantalla o estos constructores.

## Dónde se conecta con Laravel

La entrada del caso completo es [PrepararEvaluacionInstitucional.php](../app/Modulos/ApoyoDecision/Aplicacion/Consultas/PrepararEvaluacionInstitucional.php). Autorización, lectura SQL, pantallas y persistencia permanecen en `app/Modulos/ApoyoDecision/`. Los formularios originales continúan en sus módulos institucionales. No se necesita abrirlos para localizar las reglas.

`dominio/orion/` conserva tipos y contratos con consumidores e identidad histórica; no es una segunda entrada al motor institucional. `aplicacion/puertos/` contiene el contrato técnico anterior usado por las regresiones aisladas. `tests/fixtures/` conserva exclusivamente pruebas retiradas del producto. No se trasladan ni mezclan con las seis carpetas principales.

## Verificación de la reorganización del 04/11/2026

Se reducen 12 ubicaciones del recorrido principal a 6 carpetas y se trasladan 62 archivos, conservando sus responsabilidades. Se actualizan imports, rutas y enlaces; sin alias permanentes de clases antiguas, cambios de reglas/umbrales, permisos, esquema o datos. Las identidades de versiones, reglas y formatos históricos permanecen. Las comprobaciones anteriores y posteriores se registran en el [mapa técnico vigente](../docs/sistema-experto/organizacion/MAPA_COMPONENTES.md).

## Antecedentes técnicos conservados

## Consolidación del 18/10/2026

El dominio O.R.I.O.N. reside en `src/motor_experto/dominio/orion`: entidades, vocabularios, puertos y servicios puros trasladados desde la ubicación inicial `app/SistemaExperto`. Aplicación e infraestructura Laravel residen en `app/Modulos/ApoyoDecision`. No existen alias de clases antiguas.

`soporte/conservacion/CodecInstantanea` mantiene la identidad contractual original al calcular huellas de objetos y enums del dominio trasladado. Es una identidad del formato histórico, no una dependencia ejecutable de `App`. Se conservaron el orden, tipos y contenido de los objetos; no se reescribieron instantáneas ni reglas.

Verificación adicional independiente: `php motor-experto/tests/orion_independencia.php`. Las pruebas `CodecIdentidadHistoricaTest` comparan siete huellas originales: enum/fecha/NULL/cero, memoria, requisitos, vector, clasificaciones positiva/abstención y regla con AST anidado. El cambio de ubicación no modifica los contratos experimentales ni los activa clínicamente.

El producto contiene los evaluadores institucionales, conocimiento versionado, red semántica, memoria de trabajo y explicación. El piloto COG-MEM y los algoritmos exclusivos del laboratorio fueron retirados de `src/` y aislados bajo `tests/fixtures/` el 27/10/2026.

`MotorExperto\` se carga desde `src/motor_experto/`; no requiere Laravel, Livewire ni Eloquent. Los fixtures históricos se cargan exclusivamente mediante `tests/bootstrap.php`; no existe binding operativo del piloto `EvaluadorExperto`. El adaptador puro transforma hechos ya extraidos; la lectura Eloquent queda en Laravel.

Verificacion independiente: `php motor-experto/tests/independencia.php`. Ejecuta cuatro fixtures tecnicos validos y comprueba el rechazo del fixture con cobertura insuficiente, sin arrancar Laravel ni conectar una base de datos. Las pruebas de regresion completas del piloto permanecen en `tests/Unit/SistemaExperto` y `tests/Feature/SistemaExperto` de la raiz, usando este motor separado.

El análisis multicriterio y el adaptador de salida remoto siguen pendientes porque no existe una metodología/servicio aprobado que migrar. No se conservan directorios de reserva vacíos para esas capacidades; la documentación mantiene su alcance pendiente. No son evidencia de inferencia clínica implementada.
