# Lectura del resultado Petersen en Enfermería — 22/10/2026

La propietaria autorizó en esta sesión que Enfermería consulte las fuentes originales de **sus residentes asignados**, exclusivamente en modo lectura, para ver el mismo perfil orientativo calculado que Psicología. La autorización no concede creación o rectificación de valoraciones, revisión profesional, firma, diagnóstico ni gobierno de reglas.

## Implementación

- La pantalla «Sistema experto» del residente en Enfermería usa `ConsultarPerfilInstrumental::consultar`, el mismo coordinador de consulta que utiliza Psicología. La salida Petersen procede del motor y de las fuentes SQL actuales; no se toma del resultado esperado del manifiesto de la cohorte.
- `EvaluacionExpertaPolicy::consultarFuentesParaResultado` exige rol ENFERMEROS y `consultarResultados` sobre el residente. Esta última autorización comprueba cuenta y personal activos, permisos de ficha y residente, y asignación vigente para lectura. El permiso contextual se aplica en `CapturasInstrumentalesResidente`, `ConsultarPetersenInstitucional`, `ValoracionPetersenPolicy` y la lectura IADL. No se concedieron permisos globales de las tablas fuente al rol.
- La pantalla muestra el patrón orientativo o la abstención, las cinco áreas, los cinco criterios, un mapa cualitativo accesible y un fundamento resumido. Conserva por separado los controles de seguimiento y los cuidados. No presenta identificadores internos como información principal.
- La presentación de Enfermería comparte con Psicología la composición del residente y el resultado: cabecera contextual, aviso de interpretación, perfil por dominios, proceso/interpretación y criterios. En escritorio usa paneles compactos y en móvil una sola columna. Sus acciones conducen a registros, fundamento y cuidados; no ofrece registrar valoración, revisar ni firmar.
- Se diferencia la consulta calculada actual de una evaluación histórica conservada. El cálculo actual mantiene `persistido=false` y no permite revisión profesional. Los casos de la cohorte local se rotulan como ficticios de entrenamiento.

## Evidencia

En SQLite desechable, `EnfermeriaPerfilPetersenTest` comprueba que una cuenta de Enfermería asignada obtiene PET-04 desde fuentes y reglas para el caso sintético S10; confirma que otra residencia no asignada devuelve 404 y que la cuenta no puede crear valoraciones ni ejecutar el análisis reservado a Medicina/Psicología. La suite `RedisenoEnfermeriaIntegralTest` comprueba además la navegación a registros, diagnóstico médico y cuidados.

Tras la unificación visual: `EnfermeriaPerfilPetersenTest` pasó con 19 aserciones; `RedisenoEnfermeriaIntegralTest` pasó con 12 pruebas y 78 aserciones; `psychology-results-responsive.test.js` pasó tres pruebas para los estilos compartidos, incluida Enfermería a 1440 y 390 px y el perfil multidominio. `npm run build` finalizó correctamente. Esta prueba de CSS usa un documento sintético y no sustituye una revisión interactiva de la pantalla real.

En `remembermind_dev` solo se hicieron consultas de lectura: Matilde Pacheco Luján (`COD_RES_000090`) está marcada como caso de entrenamiento y la cuenta de Rosa Mamani Choque (`COD_USU_000003`) tiene alcance contextual de lectura. El motor devolvió PET-04 con atención y funciones ejecutivas alteradas, y memoria, lenguaje y capacidad visuoespacial con rendimiento esperado. La salida fue `persistido=false`.

## Límites

El perfil de Matilde es **sintético y orientativo**, no un diagnóstico ni una valoración clínica de una persona real. No se guardó una nueva evaluación, no se activaron revisiones profesionales y no se modificó el esquema de base de datos. La inspección visual interactiva del navegador local no pudo completarse desde esta sesión porque la automatización del navegador está deshabilitada en Work; la salida HTML sí se verificó mediante la prueba Livewire.
