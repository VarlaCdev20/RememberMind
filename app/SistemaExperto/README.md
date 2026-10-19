# O.R.I.O.N. — módulo experimental aislado

> Antecedente histórico. Desde el 18/10/2026 esta carpeta no contiene PHP ejecutable. El dominio está en `motor-experto/src/motor_experto/dominio/orion`, el codec en `motor-experto/src/motor_experto/soporte/conservacion`, y Application/Infrastructure en `app/Modulos/ApoyoDecision/Aplicacion` e `Infraestructura`. El contenido siguiente describe el corte anterior; no define otra implementación vigente. Véase `docs/sistema-experto/CORRECCIONES_POR_PUNTOS_2026_10_18.md`.

Domain contiene los 15 Value Objects, 14 Entities, 4 interfaces y 10 servicios.
Application contiene 5 DTOs y los 6 casos de uso del reglamento. Domain y
Application no dependen de Illuminate, SQL, HTTP ni servicios externos.

## Flujo disponible

`AnalizarCasoExperimentalUseCase` coordina secuencialmente:

1. `ExtraerHechosUseCase`: recupera una memoria por el puerto de evidencias.
2. `PrepararMemoriaTrabajoUseCase`: prepara otra instantánea conservando los hechos originales.
3. `EjecutarInferenciaUseCase`: recupera reglas/red de la versión exacta e invoca Domain.
4. `ClasificarDclUseCase`: transfiere las declaraciones independientes C01–C05 y funcionalidad a Domain.
5. `GenerarInformeUseCase::desdeResultados`: obtiene los tres niveles del generador de explicación.

Las declaraciones originales de procedencia/contexto y los soportes DCL deben
estar en la memoria extraída. No se generan referencias favorables como fallback.
La entrada DCL tiene forma cerrada y sus parámetros coinciden con el contrato
de `ClasificarDclUseCase::ejecutar`.

La prueba reproducible de los cuatro patrones y del recorrido sin evidencia
está en `tests/Unit/SistemaExperto/Application/UseCases/AnalizarCasoExperimentalUseCaseTest.php`.
Solo simula los puertos de repositorio; los servicios Domain y los cinco pasos
son reales. No es evidencia de una entrada HTTP o integración asistencial.

## Contrato de inferencia

La lógica declarativa es estricta y trivalente: `null`/ausencia son desconocidos;
`false`, cero y texto conservan sus tipos. `NO` no vuelve verdadero un dato
ausente. Las consecuencias de reglas no se incorporan automáticamente como
hechos originales ni acreditan A/P desde G09. La participación requiere una
arista directa con contrato tipado y habilitación explícita.

El integrador conserva cinco dominios y 19 subcomponentes sin índice global.
A/P exige referencia externa SIM de alcance completo, procedencia acreditada y
compuerta de integración válida. Contradicciones y no interpretabilidad
conservan los originales X/I y sus motivos, incluso si se excluyen del análisis
de reglas. La dependencia entre episodios/instrumentos no se cuenta como
corroboración independiente.

Petersen utiliza el contrato experimental DCL-PET-20261017-v0.1: contradicción,
diferencial, insuficiencia, incoherencia C02 y requisito no acreditado son salidas
separadas de PET-01..04. CAS-11 fija adjudicación C02 antes del veto general N.
Los CSV oficiales de las 62 filas 03A y su procedencia se conservan en
`tests/Unit/SistemaExperto/Domain/Services/Dcl/Fixtures`.

## Persistencia e IADL

[Infrastructure/README.md](Infrastructure/README.md) describe los puertos,
vinculador explícito, adaptadores y límites SQL.
La extensión IADL de esta tarea está aprobada e implementada como lectura
descriptiva. No acredita C03 ni activa permisos/pantallas.

La conservación de memoria, perfil y DCL está implementada con la ampliación
aprobada de nueve tablas y reutilización de D-137: 32 expertas y 105 de negocio.
`guardar` y `guardarClasificacion` son atómicos e idempotentes; recuperar no
requiere el proceso original ni reextraer una fuente mutable. Los catálogos
experimentales se preparan explícitamente y nunca como fallback de escritura.
[Diccionario aprobado](../../docs/base-de-datos/DECISION_CONSERVACION_EXPERTA_V2_2026_10_17.md).

El módulo anterior `motor-experto` y la integración `ApoyoDecision` se conservan.
No se sustituye su motor ni se registran providers nuevos o se activan eventos
clínicos en esta entrega.

## Verificación

```powershell
php vendor/phpunit/phpunit/phpunit --do-not-cache-result tests/Unit/SistemaExperto tests/Feature/SistemaExperto/Infrastructure
./scripts/probar-postgres.ps1 -Filtro 'Tests\\Feature\\SistemaExperto\\Infrastructure'
```

Las pruebas son técnicas, con datos ficticios; no equivalen a validación G12,
aprobación G13 o uso diagnóstico. Todo informe mantiene la advertencia:

> SIMULACIÓN ACADÉMICA — SIN VALIDEZ DIAGNÓSTICA — NO UTILIZAR PARA DECISIONES ASISTENCIALES.
