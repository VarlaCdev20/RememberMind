# Códigos correlativos: resultado

Fecha: 2026-10-13. Aplicado en la base local configurada; aplicación disponible.
Autorización: formato correlativo, conversión de existentes y contador técnico
aprobados por la propietaria. No constituye validación clínica del sistema experto.

## Implementado e integración

- 94 entidades con PK `cod_*`: 71 operativas y 23 del experto.
- 120.792 identidades convertidas a `COD_<PREFIJO>_000001` y siguientes.
- 94 contadores técnicos inicializados; cero PK fuera del formato aprobado.
- Generación central atómica, reservas de formulario e inmutabilidad de PK en
  ediciones ordinarias. Una reserva abandonada puede dejar un salto.
- Escritores, seeders, factories, alertas, auditoría estructurada, referencias
  polimórficas y cargas del experto adaptados. Los IDs técnicos numéricos permanecen.
- Identidad principal configurada por PK; no depende del orden del correlativo
  ni cambia de cuenta al editar su correo.

## Conservación y seguridad

Se verificaron las 271 FK y sus definiciones originales, incluidas 25 compuestas.
Se compararon huellas de contenido de las 106 tablas no transitorias contra las
filas esperadas tras traducir exclusivamente identidades y referencias.
Se preservaron autoría, fechas, estados, roles, permisos e historia clínica.
La asignación vigente de enfermería de 72 horas conserva sus seis jornadas y
cinco residentes. No se habilitó prescripción para enfermería.

42 archivos privados se respaldaron, copiaron cuando cambió su ruta y verificaron
por SHA-256 y tamaño; los originales se conservan. La carga repetida de conocimiento
no duplica registros ni consume nuevos correlativos. Las sesiones y cachés antiguas
se invalidaron: se requiere volver a iniciar sesión.

El respaldo PostgreSQL, mapas completos, resultados individuales y archivos
permanecen fuera de Git en almacenamiento privado local. Un evento de mantenimiento
de Activitylog registra la operación sin atribuirla a un profesional clínico.

## Pruebas y build

| Evidencia | Resultado |
|---|---|
| Suite general SQLite, excluyendo grupo PostgreSQL y el seeder histórico ejecutado aparte | 1.229 pruebas, 34.034 aserciones; 16 omisiones por requisitos PostgreSQL |
| Seeder histórico, rollback y repetición | 3 pruebas, 35 aserciones |
| Integración en PostgreSQL 18.4 desechable | 49 pruebas, 851 aserciones |
| Renumeración real PostgreSQL, conservación y fallos en ambas fases | 2 pruebas, 24 aserciones |
| Cuenta principal, seguridad y generador tras el último ajuste | 13 pruebas, 320 aserciones |
| Cierre del commit: arquitectura, admisión, experto, integridad e identidad | 84 pruebas, 5.326 aserciones |
| Cierre PostgreSQL: cuenta principal, generador y renumeración | 10 pruebas, 327 aserciones |
| Concurrencia en copia aislada | 8 procesos, 320 reservas, cero duplicados |
| Próximos códigos de las 94 entidades | Libres; reservas de comprobación revertidas |
| Build Vite del checkout operativo | PASS; advertencia existente de chunk mayor de 500 kB |

Comando general: `php vendor/bin/phpunit --exclude-group=postgresql --filter='^(?!.*Historical2026SeedIntegrityTest)'`.
Integración: `scripts/probar-postgres.ps1`, con filtros de la tarea y bases
desechables independientes. Los ensayos no usaron un reset de la base operativa.

## Git e integración de cambios en curso

La rama local `feature/identidad-codigos-correlativos` parte de `develop` y contiene
el cambio central, sus consumidores ya presentes en esa base, pruebas y documentos.
No incluye los rediseños y formularios previamente en curso de otros cambios.

Las adaptaciones propias para esos formularios **ya están aplicadas y verificadas
en el checkout operativo**. Sus diferencias exactas están versionadas en
[parches/codigos-cuidados-en-curso.patch](parches/codigos-cuidados-en-curso.patch),
sin incorporar sus implementaciones preexistentes completas a este commit.
El parche inverso se comprobó contra el checkout operativo.
Cuando se integren esas fuentes en `develop`, aplicar/reconciliar estos hunks
si no llegaron ya con ellas; no reaplicar sobre el checkout actual.

La integración remota requiere PR a `develop`; no se realizó push. El nombre
histórico de `AdultoMayorService` sigue siendo deuda ajena a esta conversión;
no se añadieron columnas ni dependencias V1 de persistencia.

## Recuperación administrada

Ante un fallo durante la operación, la transacción revierte PK, referencias,
contadores y alteraciones temporales de FK. Los ensayos probaron esa reversión.
Después del commit, no ejecutar nuevamente la conversión para recuperar.

Una recuperación posterior exige pausa de escrituras y workers, restauración
coordinada del respaldo privado y archivos previos, código compatible y ajuste
de `CUENTA_PRINCIPAL_CODIGO` a la identidad restaurada. Refrescar configuración,
invalidar sesiones y verificar relaciones antes de reabrir. Si existen escrituras
posteriores al corte, deben reconciliarse antes: restaurar el respaldo sin ello
perdería esas operaciones. No se ejecutó una restauración destructiva en la base
operativa ni se eliminó su historial.
