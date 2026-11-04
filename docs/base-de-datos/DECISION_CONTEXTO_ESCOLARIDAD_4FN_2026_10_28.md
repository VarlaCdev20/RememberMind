---
title: "Contexto escalar de escolaridad por atención, conservando 4FN"
status: ADOPTED_INSTALLED_LOCAL
version: "1.0"
last_reviewed: 2026-10-28
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [attention_education_context_columns, education_context_normalization]
verification_scope: SCOPED_SQLITE_POSTGRES_UI_AND_LOCAL_ADDITIVE_INSTALLATION
runtime_verified: true
---

# Decisión y alcance

Después de elegir conservar el esquema, la propietaria indicó **«amplia los campos de ser necesario pero no rompas 4fn»**. Esta instrucción posterior permite ampliar campos en este flujo, preservando normalización. No revoca la prohibición de crear tablas, ni autoriza nuevas competencias, protocolos o relaciones arbitrarias. Sustituye el rechazo anterior solo para la ampliación escalar descrita aquí; no adopta íntegramente los seis campos de la [propuesta anterior](PROPUESTA_CONTEXTO_ATENCION_COGNITIVA_2026_10_28.md).

Se añaden tres columnas opcionales a `atenciones`, manteniendo su PK, FK, índices, cardinalidades y grano de episodio profesional. No cambia el inventario de tablas: 106 de negocio +16 técnicas en la instalación vigente. Las bases de prueba con el núcleo y extensiones anteriores no representan ese inventario instalado completo.

| Columna | Tipo y ausencia | Semántica exacta |
|---|---|---|
| `escolaridad_anios` | SMALLINT NULL, entero entre 0 y 32767 | Años de educación formal informados para ese episodio. Cero conocido se distingue de NULL. El máximo es la capacidad técnica del tipo, no un umbral clínico. |
| `puede_leer` | BOOLEAN NULL | Capacidad referida para leer recogida en esa sesión: sí/no/desconocido. No acredita un examen de comprensión. |
| `puede_escribir` | BOOLEAN NULL | Capacidad referida para escribir recogida en esa sesión: sí/no/desconocido. No se deduce de leer o firmar. |

El contexto queda atribuido al profesional, residente, área y fecha de la atención existente. No es una nueva ficha maestra del residente ni actualiza `residentes.nivel_educativo`. Las filas previas permanecen NULL, sin backfill desde admisión, notas, edad u otros episodios. No se convierte automáticamente en entradas de un método instrumental o en hechos del experto.

## Justificación de 4FN de la ampliación

Grano: una fila = una atención identificada por `cod_atencion` (K). Las nuevas dependencias son K → años informados en ese episodio, K → capacidad referida de lectura en ese episodio y K → capacidad referida de escritura en ese episodio. Ninguna columna contiene conjuntos independientes, listas de personas/idiomas o grupos repetidos. No se atribuye al residente una dependencia atemporal sobre valores que pueden variar entre relatos y fechas. No hay dependencia `nivel_educativo` → años, ni leer → escribir.

Las dependencias multivaluadas de conjuntos independientes de informantes, idiomas, pruebas o diagnósticos no se incorporan a esta relación. No se duplica la fila de atención para combinar esos conjuntos. Las respuestas y demás colecciones existentes permanecen en sus relaciones nativas. Por tanto la ampliación no introduce una violación de 4FN bajo este grano y dependencias; esta comprobación acotada no certifica toda la BDD histórica.

**No adoptado en esta entrega:** `cod_residente_contacto` e `informante_conoce_nivel_previo` en la cabecera. Impondrían un único informante sin contrato, y una relación de varios informantes requeriría su propio grano y revisión institucional. Tampoco se añade una lista textual de idiomas ni se confunde idioma de entrevista con idioma de cada aplicación. Idioma e informantes siguen parciales, sin JSON/EAV, columnas numeradas o FK textual de reemplazo. La prohibición de nuevas tablas no se elude mediante desnormalización.

## Implementación y responsabilidades

Caso de uso: registrar una nueva sesión psicológica con contexto opcional. Actor: Psicología ya autorizada. Entrada: motivo/tipo/observación existentes y tres valores escalares. Policy: lectura contextual del residente; mutación: cuenta ACTIVO, permiso `atenciones.crear`, rol y personal PSICOLOGIA activos, área vigente. Action: `RegistrarSesionPsicologica`, con validación, autoría derivada y transacción atención + Activitylog. Model: casts y rechazo de sobrescritura de contexto ya conservado. Livewire conserva interacción y errores por campo; Blade usa los componentes compartidos. No se amplían permisos de Medicina, Enfermería, Gerencia, Administración, Familiar o Superadmin.

La lectura histórica presenta los tres datos por sesión y distingue desconocido de «No» y cero. Guardar otra sesión no modifica la anterior. La guía general conserva la escolaridad de admisión como referencia aparte. La corrección estructurada de una atención finalizada no se inventa: no se habilita edición destructiva ni se presenta esta entrega como un mecanismo de rectificación clínica.

## Migración y reversión

Migración aditiva nueva: `database/migrations/2026_10_28_010000_add_contexto_escolaridad_atenciones.php`. No se editan migraciones aplicadas. SQLite y PostgreSQL tienen nulabilidad, rango entero y tipos lógicos comprobados; la validación de entrada no sustituye esas restricciones. No se afirma que los guards Eloquent impidan SQL directo de sobrescritura.

`up` no rellena valores ni altera filas previas. `down` toma el bloqueo correspondiente y se niega a eliminar columnas si existe cualquier dato nuevo, incluido cero o falso. Sin datos, elimina exclusivamente las tres columnas. En instalación operativa se requiere destino configurado verificado, respaldo privado y comparación de originales antes/después; nunca `migrate:fresh`.

## Evidencia y pendientes

- SQLite en memoria: **34 pruebas/159 aserciones** en la repetición final de `php artisan test --compact tests/Feature/FichaPsicologicaResidenteTest.php`. Una primera ejecución usó en el test un método inexistente de QueryException; se corrigió la aserción y se reejecutó correctamente. No se debilitó ninguna regla.
- PostgreSQL18 desechable y runner sin privilegios globales: **28 pruebas/147 aserciones**, más **6 negativos/12 aserciones** añadidos después. Se incluyeron migración/reversión vacías, rechazo de rollback con falso conservado, restricciones con SQL directo y rollback ante fallo de auditoría. Los clusters propios se detuvieron y eliminaron; la base operativa no se utilizó para estas pruebas.
- Captura opcional sin backfill, cero/No/desconocido, autoría derivada, rechazo de autor enviado, cuenta/permiso/personal/rol/Familiar/área/residente inválidos, historia de dos sesiones y guard Eloquent comprobados. No se cambiaron permisos ni se probó una garantía de inmutabilidad de SQL directo.
- Navegador: **1 E2E correcto**, aplicación instrumental existente + entrevista con 0/No/NULL y consulta del guardado. Cinco anchos (1440/1280/1024/768/390), teclado y tema claro; sin desbordamiento horizontal ni errores JavaScript. Revisión visual **PASS acotada a la nueva sección y este recorrido**. Un primer fallo del test seleccionó «No se conoce» por coincidencia parcial de «No»; se corrigió la búsqueda exacta y pasó la repetición.
- Build Vite correcto, con aviso preexistente de chunks superiores a500kB.
- Instalación **local** en el destino PostgreSQL configurado `remembermind_dev`: únicamente esta migración, con respaldo custom privado de `atenciones` y registro técnico de migraciones verificado con `pg_restore --list`. Se mantuvo bloqueo de escritura durante la comparación e instalación. **28.107 atenciones previas, todos sus campos originales idénticos antes/después; contexto anterior NULL;122 tablas físicas sin cambio**. No se copiaron historias ni el dump al repositorio. Evidencia técnica local: `output/contexto-escolaridad-instalacion.json`; ubicación del respaldo en ese archivo privado/local, sin publicar datos.

**Gate BDD acotado: PASS** para grano/dependencias, columnas, tipos, nullability, restricciones, conservación y reversión en los motores comprobados. **Gate de seguridad acotado: PASS** para la Action y sus entradas examinadas; no equivale a una auditoría global. Las revisiones se realizaron como fase de lectura separada de la implementación; no se afirma revisión por un tercero.

No se enviaron correos, no se activaron reglas, instrumentos o diagnósticos, y no se introdujeron dependencias de dominio V1. `Atencion` conserva aliases históricos preexistentes ajenos a estos campos; su retirada no forma parte de esta ampliación. D02 se amplía con captura episódica de escolaridad y lectoescritura; su aplicabilidad a instrumentos sigue pendiente de perfiles y procedencia aprobados. D03/D06 no se cierran. El flujo no habilita rectificar destructivamente los datos de una atención finalizada.
