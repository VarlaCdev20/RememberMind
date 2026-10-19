# Fase 2 — Procedencia de aplicaciones y transcripción histórica

Fecha: 19/10/2026. **APROBADA por respuesta explícita de la propietaria: «Sí, aprobar estructura y permiso propuestos».** Alcance: fuentes originales de aplicaciones instrumentales, no instantáneas expertas de la fase 6. Se mantienen las 106 tablas de negocio: cero tablas nuevas. Aplicación y pruebas se registran en el acta de fase 2.

## Problema comprobado

`aplicaciones_instrumento.cod_personal` conserva al aplicador y `fecha_hora` el instante clínico. La entidad no distingue quién digitó después, cuándo lo hizo, qué documento/hoja originó el registro ni qué aplicación rectifica. La captura vigente deriva el autor de la sesión: no puede reutilizarse para atribuir a esa persona la aplicación de otra. `documentos_clinicos` ya conserva residente, archivo privado y hash; una atención compartida no prueba qué hoja originó una medición.

La comprobación de desarrollo fue una transacción PostgreSQL de solo lectura: 487 aplicaciones, 2296 respuestas, cero pares aplicación/pregunta duplicados y siete documentos con archivo/hash coincidentes. No se copiaron historias al repositorio ni se modificaron datos. Estos números describen ese instante, no un inventario permanente.

## Diccionario propuesto: cinco columnas nullable

Sobre **`aplicaciones_instrumento`**, preservando las filas históricas sin rellenar metadatos desconocidos:

| Columna | Tipo | Significado |
| --- | --- | --- |
| `cod_usuario_registro` | `string(20)` nullable, FK `usuarios.cod_usuario` | Cuenta que registra ahora; se deriva de la sesión, nunca del cliente |
| `registrado_en` | `datetime(6)` nullable | Instante técnico generado por el servidor; no sustituye `fecha_hora` original |
| `cod_documento_clinico` | `string(20)` nullable, FK `documentos_clinicos.cod_documento_clinico` | Documento privado original cotejado y del mismo residente |
| `localizador_fuente` | `string(120)` nullable | Identidad estable de la entrada en ese documento: página/ficha/fila; no contiene respuestas clínicas |
| `cod_aplicacion_anterior` | `string(20)` nullable, self-FK | Aplicación que se rectifica, del mismo residente; nunca autoriza borrado o sobrescritura |

FK restrictivas e indexadas; sin cascadas. Checks: usuario/instante técnico se aportan juntos; documento/localizador se aportan juntos y el localizador no es vacío; una aplicación no puede señalarse a sí misma. El backend valida residente, archivo/hash, atención, firma original y pertenencia de preguntas/opciones.

Identidad de fuente y correcciones:

- Índice UNIQUE parcial `(cod_documento_clinico, localizador_fuente)` para la primera transcripción, con `cod_aplicacion_anterior IS NULL` y documento no nulo.
- UNIQUE sobre `cod_aplicacion_anterior` cuando exista: una sucesora directa por aplicación; las rectificaciones forman una cadena.
- La primera unicidad no se aplica a sucesoras: rectificar una transcripción del mismo original conserva su documento/localizador sin impedir guardar la corrección.
- Repetir fuente y contenido devuelve el registro existente, sin duplicar ni reasignar autor. Un contenido distinto se informa como conflicto; exige rectificación explícita de la cabeza vigente. Fecha+instrumento o igualdad de respuestas no identifican por sí solos un acto clínico.
- Un registro previo sin fuente puede vincularse por su PK tras cotejar íntegramente cabecera, puntajes y respuestas. Se añaden únicamente documento/localizador y auditoría de vinculación, sin inventar su transcriptor ni instante de digitación anteriores. Un posible legado con residente/instrumento/fecha coincidentes se informa como conflicto hasta ese cotejo; no se crea automáticamente otra aplicación. `VIGENTE` conserva su estado en vinculación/reintento; no se convierte en `COMPLETA` ni se hace elegible para cálculo.
- Una rectificación crea otra aplicación y sus respuestas, enlaza la anterior y la anula para nuevas lecturas. No modifica sus respuestas, fecha, autor ni texto originales. El motivo y la transición quedan en Activitylog dentro de la transacción. La restauración arbitraria no forma parte de esta propuesta.
- Bloqueo de documento/cadena y transacción para impedir dos primeras transcripciones o dos sucesoras concurrentes; repetir las pruebas en PostgreSQL y SQLite.

## Autoría y autorización propuesta

Crear únicamente el permiso **`aplicaciones_instrumento.transcribir`**, concedido a **Medicina y Psicología**, sujeto además a cuenta/personal activos, lectura de fuentes/documento, autorización contextual sobre el residente y validación del original. Superadministración consulta; no recibe escritura clínica. Los demás profesionales mantienen la captura propia ya autorizada.

Alcance de la lectura fuente: metadatos del documento del residente bajo el permiso específico de transcripción; hash comprobado internamente, sin devolver ruta o archivo. No se concede por inferencia el permiso general `documentos_clinicos.ver`; descargar documentos mantiene su gate existente. El operador coteja el original que posee o consulta por un acceso ya autorizado.

La transcripción conserva al aplicador que figura en el original, aunque ya no trabaje actualmente; debe existir en `personal` y su identificación debe ser aportada/cotejada. La cuenta del transcriptor y el momento de digitación son distintos. No se selecciona un profesional por disponibilidad, rol, turno actual o semejanza de nombres. Un documento sin autor identificable queda en el informe de pendientes; no se fuerza una FK.

No habilita diagnóstico, aprobación profesional precargada ni sustitución de respuestas por resultados esperados. No identifica instrumentos históricos mediante bibliografía.

## 4FN y alternativas

Las cinco columnas son escalares del acto, no colecciones independientes. `cod_aplicacion → aplicador, fecha clínica, transcriptor, instante técnico, documento/localizador, antecesora`. Las respuestas permanecen en su relación; instrumentos/preguntas/opciones en sus catálogos; el archivo/hash en documentos; la auditoría en Spatie. No se añade JSON/EAV ni se duplican nombres, instrumentos o firmas.

Alternativa sin estructura: mantener originales como documentos y ofrecer solo conciliación previa. Es segura y permite avanzar, pero no cierra el vínculo relacional aplicación→documento ni el autor histórico separado. Activitylog puede registrar al actor técnico, pero no debe sustituir las FK de procedencia clínica.

## Aceptación antes de aplicar

- Migración forward reversible; filas previas intactas y nuevas columnas NULL; baseline/diccionario/Models sincronizados.
- Captura propia con fecha explícita y auditoría; transcripción autorizada con original, autor y documento distintos del actor.
- Conciliación previa, conflictos, NULL/cero/No, reintento idéntico, corrección explícita y preservación del original.
- Preguntas/opciones/atención/documento de otro residente o instrumento rechazados, sin efectos parciales.
- Revocación de permiso/cuenta/personal, autor no identificable, archivo ausente/hash cambiado y original incompleto rechazados.
- Carreras PostgreSQL de primera importación/sucesora y rollback ante fallo de auditoría o respuestas.

La aprobación fue solicitada por `database/AGENTS.md` (modelo congelado) y `AGENTS.md` (fronteras de permisos sensibles), y se recibió expresamente en este chat antes de implementar la migración o el permiso. No autoriza completar metadatos históricos desconocidos ni nuevos catálogos instrumentales.
