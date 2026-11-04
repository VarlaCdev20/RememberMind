---
title: "Gerencia: instalación autorizada de documentación e historia nominal"
status: APPROVED_INSTALLED_LOCAL
version: "1.0"
last_reviewed: 2026-10-27
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [gerencia_designs_35_37]
---

# Decisión específica del 27/10/2026

La propietaria respondió **«autorizo»** al pendiente de instalación de los diseños
35 y 37 descritos en la entrega anterior. Autoriza instalar esos dos diseños en
la base local `remembermind_dev`, después de verificar sus pruebas y respaldo.
Esta decisión sustituye su estado pendiente de instalación; no aprueba otros
DDL, despliegues, nuevas responsabilidades clínicas o permisos adicionales.

## Alcance físico exacto

Diccionarios completos: [35, documentación y estado laboral](../especificacion-funcional/vistas/gerencia/35-PROPUESTA-ESTRUCTURAL-PERSONAL.md)
y [37, historia nominal](../especificacion-funcional/vistas/gerencia/37-PROPUESTA-HISTORIA-VINCULOS-ASIGNACIONES.md).
Se aprueban sus PK, FK restrictivas, índices, UNIQUE, CHECK y protecciones
históricas en los archivos siguientes, sin cambiar sus bytes:

| Diseño | DDL | SHA-256 |
|---|---|---|
| 35 | [Documentación y estado laboral](../../database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php) | `a1375286998233ae6f6532601b06abf6640aa1c90471b5585e7436779081e40f` |
| 37 | [Vínculos y asignaciones](../../database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php) | `5426559de9ef5a617f43015978b58eae4890a3147ed71ce433806d5216649da9` |

Ocho columnas nullable:

- `usuarios`: `fecha_hora_alta`, `cambio_password_pendiente`.
- `documentos`: `cod_usuario_carga`, `fecha_hora_carga`, `fecha_emision`,
  `cod_usuario_anulacion`, `fecha_hora_anulacion`, `motivo_anulacion`.

Seis relaciones nuevas, sin retiros ni fusiones:

1. `solicitudes_documentales_personal`: petición original.
2. `destinatarios_solicitud_documental`: membresía y plazo original.
3. `entregas_solicitud_documental`: documento vinculado expresamente a la petición.
4. `movimientos_laborales_personal`: acto laboral, separado del acceso de cuenta.
5. `versiones_vinculo_contacto`: versiones del vínculo estable de apoyo.
6. `eventos_asignacion_personal`: actos sobre asignaciones de jornadas.

Inventario previsto: **81 operativas + 25 expertas = 106 de negocio**,
además de **16 técnicas**; **122 físicas**. El máximo de 110 no cambia.
El núcleo de 70 y las extensiones anteriores conservan sus contratos.
No se añaden JSON/EAV, auditoría corporativa duplicada o contenido clínico.

## Prórroga de expediente: interpretación funcional conservada

La decisión P19 ya adoptada exige una sola concesión de siete días por
expediente de alta, no una por archivo. Su efecto se consulta para las
solicitudes ALTA con el mismo `cod_personal` y `fecha_limite_original`
conservada. La fecha original proviene del alta técnica conocida + siete días;
no se reconstruye desde ingreso laboral ni desde el reloj de la consulta.

La concesión permanece en su destinatario de origen con actor, fecha y motivo.
Las demás solicitudes muestran su efecto y procedencia sin copiar ese acto,
rellenar sus cuatro campos NULL ni modificar sus plazos originales. No se
propaga a otro trabajador, otro límite de alta o SOLICITUD_POSTERIOR. Una
solicitud de la misma alta emitida después conserva su original y consulta
el efecto de la concesión existente.

Es una proyección y validación de aplicación, **sin cambio al DDL aprobado**.
La Action comparte el coordinador transaccional de emisión y comprueba todo
el expediente antes de conceder. SQL protege composición, autoría y duración
por fila; no se afirma que exista UNIQUE SQL de concesión entre solicitudes.
Una escritura SQL ajena a la Action puede producir varias concesiones: la
consulta presenta contradicción y ningún límite elegido arbitrariamente.
No hay autosuspensión, extensión automática ni una segunda concesión.

Los seis granos separan colecciones independientes y conservan sus relaciones;
el efecto compartido se deriva, no duplica autorías ni plazos originales.
La justificación de normalización de 35/37 sigue aplicando. Los CHECK y tests
no constituyen por sí solos una demostración global de 4FN.

## Instalación, conservación y reversión

El [instalador acotado](../../work/gerencia-instalar-extensiones-20261027.php)
comprueba entorno local, destino loopback, inventario inicial y hashes.
Genera un respaldo privado PostgreSQL, verifica su catálogo y registra su hash;
esto no equivale a demostrar una restauración completa. No imprime claves ni
contenido de los expedientes. Instala ambos DDL dentro de una transacción,
con bloqueo de instalación y de las dos tablas ampliadas, y registra cada
migración. Verifica igualdad del contenido previo de `usuarios` y `documentos`,
tablas nuevas vacías y columnas históricas nuevas NULL. No importa seeders.

Los archivos conservan sus rutas explícitas fuera del descubrimiento automático
de migraciones. En una **base nueva autorizada**, después del núcleo y sus
extensiones previas, deben aplicarse en este orden mediante `artisan migrate
--path=database/propuestas/gerencia/<archivo> --force` (una ruta por comando).
No ejecutar el instalador local contra otros destinos ni `migrate:fresh` sobre
la base institucional. Las pruebas conservan una preparación explícita y
desechable de las dos extensiones.

Reversión: primero 37 y después 35, únicamente si sus relaciones y columnas
nuevas no contienen datos. Sus métodos `down()` rechazan historia poblada;
no borrarla para forzar rollback. Si ya existe actividad, detener la reversión
y diseñar una transición conservadora con autorización específica. Un fallo
de instalación revierte la transacción, no restaura indiscriminadamente el
respaldo sobre actividad concurrente.

El [informe de instalación y pruebas](../especificacion-funcional/vistas/gerencia/40-INSTALACION-AUTORIZADA-Y-PRORROGA-EXPEDIENTE.md)
registra el resultado efectivo: instalado y verificado el 27/10/2026 desde las
09:47:45, America/La_Paz. Inventario comprobado: 106 de negocio +16 técnicas;
contenido previo conservado, 90 restricciones validadas y cuatro triggers
históricos habilitados. La capacidad documental reconoce la instalación.
