# Motor experto tecnico

Este componente contiene la implementacion PHP existente del piloto COG-MEM: conocimiento versionado, red semantica, admissibilidad/evaluabilidad, memoria de trabajo, reglas, resolucion de soportes y explicacion. No incorpora reglas clinicas nuevas ni activa diagnosticos.

`MotorExperto\` se carga desde `src/motor_experto/`; no requiere Laravel, Livewire ni Eloquent. El puerto `aplicacion/puertos/EvaluadorExperto` es implementado por el motor y consumido por la persistencia tecnica de `ApoyoDecision`. El adaptador puro transforma hechos ya extraidos; la lectura Eloquent queda en Laravel.

Verificacion independiente: `php motor-experto/tests/independencia.php`. Ejecuta cuatro fixtures tecnicos validos y comprueba el rechazo del fixture con cobertura insuficiente, sin arrancar Laravel ni conectar una base de datos. Las pruebas de regresion completas del piloto permanecen en `tests/Unit/SistemaExperto` y `tests/Feature/SistemaExperto` de la raiz, usando este motor separado.

`multicriterio` y el adaptador de salida remoto permanecen sin implementacion porque no existe una metodologia/servicio aprobado que migrar. No son evidencia de inferencia clinica implementada.
