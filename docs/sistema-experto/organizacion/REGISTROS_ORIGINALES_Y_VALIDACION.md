---
title: "Primer bloque: registros originales y validación"
status: CURRENT
version: "1.1"
last_reviewed: 2026-10-28
owner: ApoyoDecision
source_of_truth: false
verification_scope: CAPTURE_CONTRACT_REGRESSION_AND_PREOPERATIVE_SCHEMA_WITHOUT_RESIDENTS
runtime_verified: true
---

# Registros originales y validación

Primera entrega de los componentes 1 y 2 de la [estructura adoptada](ESTRUCTURA_POR_COMPONENTES.md). Se concreta el contrato técnico de captura instrumental existente y se cierra el acceso directo al lote retirado. No selecciona una batería cognitiva, crea tablas, añade requisitos médicos o completa los otros seis componentes.

## 1. Qué entra al experto

```mermaid
flowchart LR
    R["Registro original autorizado"] --> V["Validación de procedencia y contenido"]
    V --> O["Original conservado"]
    O --> C{"¿Tiene vínculo instrumental<br/>y condiciones aplicables?"}
    C -->|Sí| H["Evidencia para los hechos del caso"]
    C -->|No| F["Conservar original y explicar el faltante"]
```

Registrar un original, extraer una medición y obtener una interpretación son operaciones diferentes. El guardado del original no genera automáticamente una evaluación experta.

## 2. Ficha de entrada técnica

| Dato | Fuente y control vigente |
|---|---|
| Residente | Identidad canónica `cod_residente`, contexto autorizado y estado admitido conforme al servicio clínico. Un ID enviado directamente recibe los mismos controles que el selector. El lote técnico retirado no es elegible. |
| Instrumento y versión | `cod_instrumento`, código y versión del catálogo. Para nuevas aplicaciones debe estar activo; no se admite sustituirlo por un nombre parecido ni por un catálogo técnico. |
| Fecha clínica | `fecha_hora` explícita en los formatos aceptados por la Action; no futura. Se conserva separada de la fecha de registro. |
| Autor de captura nueva | Personal activo derivado del usuario autenticado y autorizado. El cliente no puede atribuir el acto a otro profesional. |
| Pregunta | `cod_pregunta` perteneciente al instrumento activo; una respuesta por pregunta y referencias coherentes. |
| Valor original | Un único tipo por respuesta: número, booleano, texto u opción del catálogo, compatible con la pregunta. Cero y falso se conservan como valores válidos. |
| Opción | `cod_opcion` perteneciente a esa pregunta y vigente para la captura nueva. No usar la opción de otro instrumento. |
| Puntaje | Se obtiene o contrasta con el catálogo aplicable. Un puntaje no disponible permanece NULL; no se reemplaza por cero. |
| Total | Solo se calcula con cobertura y puntajes suficientes según el contrato actual. La etiqueta operativa `COMPLETA` no acredita por sí sola que estén todas las entradas requeridas por un método interpretativo. |
| Atención vinculada, si se indica | Debe pertenecer al mismo residente. No seleccionar una atención arbitraria para completar una FK. |
| Observación, clasificación e interpretación registradas | Conservan su carácter de registro profesional. No se presentan como una conclusión generada por el motor. |
| Edad para la interpretación | Se obtiene de la fecha de nacimiento en la fecha de aplicación. La edad actual no sustituye la edad del episodio; la norma aplicable depende del método adoptado. |
| Perfil de interpretación | Configuración explícita del instrumento, edición/forma/idioma, método y preguntas. Su ausencia permite conservar el original, pero no fabricar la interpretación. |

En la transcripción histórica se añaden autor original, documento del residente, localizador y cotejo. Se verifican pertenencia, vigencia y huella del archivo. El usuario que transcribe y el profesional del documento mantienen identidades distintas. Las correcciones utilizan antecesora y motivo conforme al mecanismo existente.

## 3. Validación por etapas

| Etapa | Comprobación | Efecto de un fallo |
|---|---|---|
| Acceso y contexto | Cuenta, permiso, rol/competencia, personal, residente y alcance aplicables. | Denegar la operación antes de exponer el contexto o escribir. |
| Identidad de la fuente | Catálogo institucional, instrumento, versión, preguntas/opciones y vínculos del mismo residente. | Rechazar una captura incompatible o identificar la fuente histórica no utilizable. |
| Contenido original | Fecha, tipo único, valor explícito y coherencia del puntaje con el catálogo. | Mensaje de validación; sin cabecera/respuestas parciales ni éxito falso. |
| Conservación | Cabecera, respuestas, autoría, fechas y auditoría en la transacción de la operación. | Un fallo revierte el registro; una conciliación no escribe. |
| Consumo por el experto | Corte temporal, identidad explícita, procedencia, estado, documento cuando corresponda y perfil aplicable. | Conservar los originales y explicar el motivo de exclusión o la falta de interpretación. |

No se convierten una observación nominal o un total aislado en una medición cognitiva estandarizada. Este bloque tampoco define puntos de corte, probabilidades, severidad o diagnósticos.

## 4. Actores y entradas del backend

Los permisos y competencias son los existentes; esta entrega no concede otros. La Action de captura utiliza `AutorizacionClinicaService` y sus roles aplicadores actuales. La transcripción histórica se restringe a Medicina/Psicología con los permisos específicos. La lectura del residente no concede automáticamente escritura clínica.

| Entrada | Responsabilidad | Control reforzado en esta entrega |
|---|---|---|
| [PrepararCapturaInstrumental](../../../app/Modulos/Valoraciones/Aplicacion/Consultas/PrepararCapturaInstrumental.php): contexto, catálogo y fuentes de transcripción | Preparar datos autorizados para el formulario. | Residente elegible también en peticiones directas; el lote técnico responde como recurso no disponible. |
| [AplicarInstrumento](../../../app/Modulos/Valoraciones/Aplicacion/Acciones/AplicarInstrumento.php): aplicar, conciliar y transcribir | Validar y registrar o conciliar dentro de la transacción. | Comprobar elegibilidad del residente al releerlo con bloqueo, antes de validar/guardar la captura. |
| [CapturasInstrumentalesResidente](../../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/CapturasInstrumentalesResidente.php) | Adquirir fuentes para una consulta del experto. | Conserva los controles existentes de lectura autorizada, corte y procedencia. |
| [FuentesValoracionOperativa](../../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/FuentesValoracionOperativa.php) | Centralizar la exclusión de fuentes técnicas ya adoptada. | Misma selección en consulta, preparación y escritura; sin borrar historia. |

La exclusión del lote requiere conjuntamente su documento técnico y la marca exacta de admisión. Una coincidencia aislada no bloquea una persona ordinaria. Las pruebas verifican ambos lados de esa frontera.

## 5. Disponibilidad y decisión clínica pendiente

El inventario de metadatos se repitió el 27/10/2026 mediante [inventariar-experto.php](../../../scripts/inventariar-experto.php), en PostgreSQL con transacción de solo lectura y rollback. Confirmó 26 instrumentos físicos y seis versiones; la configuración efectiva sigue con cero perfiles instrumentales. No se consultaron historias de residentes ni se modificaron catálogos. El [inventario anterior](../inventarios/README.md) clasifica esas entradas, incluidas las técnicas ya excluidas.

**Pendiente para avanzar en interpretación:** elegir los instrumentos concretos y su edición, idioma, condiciones de aplicación, normas aplicables, derechos y capacitación. El [catálogo de candidatos](../instrumentos/CATALOGO_CANDIDATOS.md) y la [matriz D01–D27](../MATRIZ_DATOS_DETECCION_DCL_2026_10_27.md) sirven de base; no son una batería aprobada.

Las brechas de escolaridad, idioma, informante y contexto documentadas en la matriz siguen abiertas en su alcance. Esta entrega no las declara resueltas ni introduce un nuevo formulario paralelo para ellas.

## 6. Verificación del corte de captura del 27/10/2026

- Se reprodujeron cinco fallos antes de la corrección: contexto, catálogo, aplicación, conciliación y transcripción podían alcanzar el lote retirado por ID directo.
- Se agregaron negativos sin escritura y positivos con una sola marca coincidente o ninguna, conservando cero, falso, NULL, autoría y ausencia de evaluación experta automática.
- La suite completa `CapturaInstrumentalFormularioTest` pasó con 41 pruebas y 279 aserciones; incluye validación, interacción Livewire y restricciones de acceso existentes.
- Las ocho regresiones de este bloque pasaron también en PostgreSQL desechable propio, con 52 aserciones; su evidencia local queda en `output/experto-fase1-postgres*`.
- No cambió Blade/CSS/JS ni el esquema. Esta entrega no acredita un nuevo diseño visual, un motor clínicamente validado o el cierre de conservación conjunta instrumental/Petersen.

La evidencia local de captura, reproducción e inventario queda en `output/experto-fase1-*`; no se incorpora al commit junto a datos o secretos. La siguiente entrega debe concretar la batería y el mapeo de cada medición a hechos, reutilizando este contrato de entrada.

## 7. Preparación antes de tener residentes — 28/10/2026

La propietaria indicó que todavía no tiene residentes reales. El componente 1 se prepara sobre las fuentes, formularios y relaciones existentes. **Capacidad de captura disponible y registros clínicos reales disponibles son estados diferentes.** Esta entrega no crea personas, admisiones, aplicaciones, respuestas ni mediciones de ejemplo; tampoco consulta historias para comprobar esa afirmación.

El estado de este bloque es **estructura de entrada comprobada; datos reales pendientes de operación; batería y perfiles por concretar**. El ingreso de un residente mantiene el flujo institucional de preadmisión y admisión formal. No se habilita un alta directa desde el experto.

### Mapa de datos y captura existente

| Grupo de entrada | Tabla y campos originales principales | Quién registra y cómo se controla | Uso permitido y límite |
|---|---|---|---|
| Identidad y edad | `residentes.cod_residente`, `fecha_nacimiento`, `estado`. | Admisión formal y mantenimiento autorizado del expediente; no se crean residentes desde este contrato. | Seleccionar el caso y calcular la edad del episodio. No prueba escolaridad, idioma o estado cognitivo. |
| Definición instrumental | `instrumentos.codigo`, `version`, `tipo`; `preguntas_instrumento.tipo_respuesta`; `opciones_pregunta.puntaje`. | Catálogo institucional gestionado con sus permisos. Esta entrega solo inspecciona la estructura. | Identificar exactamente qué se aplicará. Su existencia no aprueba una batería ni sus normas. |
| Aplicación y respuestas | `aplicaciones_instrumento.cod_residente`, `cod_personal`, `fecha_hora`; `respuestas_instrumento.cod_aplicacion`, `cod_pregunta`, `cod_opcion`, `valor_numero`, `valor_texto`, `valor_logico`, `puntaje`. | [AplicarInstrumento](../../../app/Modulos/Valoraciones/Aplicacion/Acciones/AplicarInstrumento.php) exige actor/competencia, pertenencia, tipos y fechas. Transcripción distingue autor original y registrador. | Mediciones originales; solo una vinculación explícita permite su interpretación. NULL, cero y falso se conservan distintos. |
| Observación cognitiva | `controles_cognitivos.fecha_hora`, `cod_personal`, `repite_preguntas`, `cambio_cognitivo`, `observacion`. | Enfermería/Medicina conforme a [RegistrarObservacionCognitiva](../../../app/Modulos/Cuidados/Aplicacion/Acciones/RegistrarObservacionCognitiva.php), permiso y contexto aplicables. | Puede apoyar historia/corroboración según el contrato; no sustituye una medición instrumental. |
| Contexto psicológico | `valoraciones_psicologicas.estado_animo`, `conducta`, `impresion_cognitiva`, `conclusion`, `fecha_hora`, `cod_personal`. | Psicología mediante [RegistrarValoracionProfesional](../../../app/Modulos/Valoraciones/Aplicacion/Acciones/RegistrarValoracionProfesional.php). | Soporte pertinente del contrato. Una narrativa no acredita una escala estandarizada administrada. |
| Autonomía instrumental | `controles_funcionales_iadl.actividad`, `oportunidad`, `desempeno`, `tipo_ayuda`, `causa_ayuda`, `nivel_previo`, `fuente`. | La [matriz D13](../MATRIZ_DATOS_DETECCION_DCL_2026_10_27.md) mantiene pendiente verificar el writer/formulario institucional completo; no se inventa uno ni un responsable nuevo aquí. | Fuente descriptiva IADL; no determina automáticamente el juicio profesional C03. |
| Antecedentes | `antecedentes_clinicos.tipo_antecedente`, `descripcion`, `fecha_referencia`, `fuente_informacion`, `cod_personal`. | Medicina y permisos de [RegistrarExpedienteClinico](../../../app/Modulos/Clinica/Aplicacion/Acciones/RegistrarExpedienteClinico.php). | Soporte admitido de revisión diferencial; fecha de referencia desconocida no se rellena con la fecha actual. |
| Diagnóstico registrado | `diagnosticos.nombre`, `tipo`, `certeza`, `fecha_hora`, `cod_personal`. | Medicina por el flujo clínico autorizado. | Registro profesional separado del resultado del experto; no equivale a una inferencia automática. |
| Documento original | `documentos_clinicos.cod_residente`, `tipo_documento`, `fecha_hora`, `hash_archivo`, `estado`. | Registro y acceso documental según la Policy y permisos existentes; el archivo se comprueba cuando el contrato lo exige. | Soporte trazable. Existencia del documento no implica resultado normal ni revisión realizada. |
| Juicio profesional original | `valoraciones_petersen_originales.elemento`, `estado_original`, `justificacion`, `fecha_hora`, `cod_personal`, `registrado_en`. | Medicina/Psicología conforme a [ValoracionPetersenPolicy](../../../app/Modulos/ApoyoDecision/Presentacion/Policies/ValoracionPetersenPolicy.php). | Juicio original con comprobaciones y soportes. C02 se deriva de los dominios; no se solicita como juicio manual. |
| Vínculo con la evidencia | `soportes_valoracion_petersen.cod_valoracion_petersen`, `tipo_soporte`, `orden` y FK de fuente admitida. | Se conserva con la valoración por el flujo autorizado; no es un selector de tablas arbitrarias. | La [matriz cerrada de fuentes](../../../app/Modulos/ApoyoDecision/Aplicacion/Servicios/ContratoFuentesPetersen.php) determina qué soporte se admite para cada criterio y tipo. |

Los permisos de lectura y las claves exactas de los ocho soportes admitidos se obtienen de `ContratoFuentesPetersen::FUENTES`, sin mantener una segunda lista clínica independiente. Las otras seis estructuras inspeccionadas corresponden a identidad, catálogos, respuestas y vínculos; no se convierten en seis soportes nuevos.

### Comprobación ejecutable sin datos clínicos

Se amplió [InventarioExperto::entradas](../../../app/Modulos/ApoyoDecision/Infraestructura/Persistencia/InventarioExperto.php), consumido por el [inventario existente](../../../scripts/inventariar-experto.php). Comprueba en PostgreSQL/SQLite:

- Presencia de las tablas y campos de contexto usados por el contrato.
- Correspondencia de la PK declarada con la PK física.
- FK de cada soporte al residente y enlaces aplicación–pregunta–opción–respuesta y valoración–soporte–fuente.
- Nombres reales de fecha/estado, sin asumir que todas las entidades tienen `fecha_hora` y `estado`.

El informe separa `estructura_verificada` de disponibilidad clínica: declara `requiere_residentes=false` y `certifica_preparacion_clinica=false`. Si falta una tabla o campo informa el faltante; no lo crea ni lo rellena. Listar columnas existentes no convierte todas ellas en campos clínicamente obligatorios.

Comando local de solo lectura:

```powershell
php scripts/inventariar-experto.php
```

El resultado añade `contrato_entradas` al inventario previo. La [evidencia de estructura del 28/10](ENTRADAS_ESTRUCTURA_2026_10_28.json) contiene únicamente metadatos: ocho fuentes de soporte y seis estructuras complementarias, **14/14 verificadas**. No contiene identidades, historias ni valores clínicos. No es un archivo para editar reglas ni activar permisos. Los campos ausentes de la matriz, la selección de instrumentos y la conservación conjunta siguen pendientes.

Las pruebas `ContratoEntradasSinResidentesTest` verifican una base operativa vacía, SQLite en modo de solo lectura, una base sin esquema y el rechazo de motores no verificados. Inspeccionan las consultas para impedir escrituras y lecturas de filas clínicas. No utilizan factories de residentes, seeders clínicos ni casos simulados. La evidencia técnica de estas pruebas no certifica desempeño diagnóstico.

Verificación del corte 28/10: ocho pruebas aprobadas en SQLite (296 aserciones); siete aprobadas en PostgreSQL desechable (266 aserciones) y una omitida por corresponder exclusivamente a `PRAGMA query_only` de SQLite. Se conservan las cuatro pruebas unitarias del inventario existente. Sin cambios de interfaz, build no aplicable a esta entrega.
