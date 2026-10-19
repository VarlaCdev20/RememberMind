# Motor experto tecnico

## Consolidación del 18/10/2026

El dominio O.R.I.O.N. reside en `src/motor_experto/dominio/orion`: entidades, vocabularios, puertos y servicios puros trasladados desde la ubicación inicial `app/SistemaExperto`. Aplicación e infraestructura Laravel residen en `app/Modulos/ApoyoDecision`. No existen alias de clases antiguas.

`soporte/conservacion/CodecInstantanea` mantiene la identidad contractual original al calcular huellas de objetos y enums del dominio trasladado. Es una identidad del formato histórico, no una dependencia ejecutable de `App`. Se conservaron el orden, tipos y contenido de los objetos; no se reescribieron instantáneas ni reglas.

Verificación adicional independiente: `php motor-experto/tests/orion_independencia.php`. Las pruebas `CodecIdentidadHistoricaTest` comparan siete huellas originales: enum/fecha/NULL/cero, memoria, requisitos, vector, clasificaciones positiva/abstención y regla con AST anidado. El cambio de ubicación no modifica los contratos experimentales ni los activa clínicamente.

Este componente contiene la implementacion PHP existente del piloto COG-MEM: conocimiento versionado, red semantica, admissibilidad/evaluabilidad, memoria de trabajo, reglas, resolucion de soportes y explicacion. No incorpora reglas clinicas nuevas ni activa diagnosticos.

`MotorExperto\` se carga desde `src/motor_experto/`; no requiere Laravel, Livewire ni Eloquent. El puerto `aplicacion/puertos/EvaluadorExperto` es implementado por el motor y consumido por la persistencia tecnica de `ApoyoDecision`. El adaptador puro transforma hechos ya extraidos; la lectura Eloquent queda en Laravel.

Verificacion independiente: `php motor-experto/tests/independencia.php`. Ejecuta cuatro fixtures tecnicos validos y comprueba el rechazo del fixture con cobertura insuficiente, sin arrancar Laravel ni conectar una base de datos. Las pruebas de regresion completas del piloto permanecen en `tests/Unit/SistemaExperto` y `tests/Feature/SistemaExperto` de la raiz, usando este motor separado.

El análisis multicriterio y el adaptador de salida remoto siguen pendientes porque no existe una metodología/servicio aprobado que migrar. No se conservan directorios de reserva vacíos para esas capacidades; la documentación mantiene su alcance pendiente. No son evidencia de inferencia clínica implementada.
