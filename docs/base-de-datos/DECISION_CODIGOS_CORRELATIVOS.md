# Códigos correlativos — decisión aprobada

## Compatibilidad con galería — 17/10/2026

La extensión de galería eleva el catálogo a 95 entidades con código (72 operativas y 23 expertas). La herramienta administrativa incluye su PK y FK; no se ha vuelto a ejecutar la conversión sobre datos reales durante esta entrega.

La FK de autor de galería conserva `ON UPDATE/DELETE RESTRICT`. Para el caso de conversión autorizado, PostgreSQL no permite diferir una acción UPDATE RESTRICT: bajo el bloqueo ACCESS EXCLUSIVE de todas las tablas y dentro de la misma transacción, la herramienta sustituye temporalmente esa restricción por una FK NO ACTION diferible y validada. Antes de confirmar restituye el nombre y la definición completos originales, verifica relaciones y huellas y compara el contrato relacional. El OID técnico de esa restricción recreada cambia en una conversión confirmada; no es una PK de negocio. Los OID de las demás FK siguen comparándose y el rollback conserva también los originales.

Pruebas PostgreSQL desechables verifican una fotografía sintética con autor renumerado, igualdad de las definiciones FK tras éxito y rollback con identidad/OID originales ante fallo en ambas fases. No cambia el modelo congelado final ni autoriza ejecutar esta herramienta sin las precauciones y el alcance de la decisión original que sigue.

Fecha: 2026-10-13. Estado: formato, renumeración y contador técnico aprobados.

La propietaria autorizó renumerar también los registros existentes. Para usuarios
aprobó `COD_USU_000001`, `COD_USU_000002`, etc. La columna conserva el nombre V2
`cod_usuario`. Las demás entidades usarán un prefijo propio y seis cifras como
anchura mínima; superar 999999 amplía la parte numérica dentro de string(20).

## Alcance comprobado

Inventario de la base local PostgreSQL 18.4: 109 tablas, 94 PK `cod_*`,
271 FK no diferibles. Las 94 comprenden 71 operativas y 23 del experto.
Las claves numéricas de tablas técnicas de Laravel/Spatie/Sanctum conservan su
contrato. Sus referencias a entidades sí deben traducirse.

## Contador aprobado

Una tabla exclusivamente técnica `secuencias_codigos`, fuera del inventario
operativo y experto:

| Columna | Tipo | Regla |
|---|---|---|
| entidad | varchar(64) | PK; nombre canónico de tabla |
| ultimo_valor | bigint | no nulo, comienza en cero |

Una fila por entidad. La asignación incrementa el contador en una transacción
con bloqueo de fila; SQLite usa su bloqueo de escritura con reintento acotado.
La restricción PK existente conserva la garantía final de unicidad. No se
modifican columnas, PK, FK ni catálogos operativos.

Una reserva confirmada para un formulario abandonado puede dejar un salto.
Los identificadores no se reutilizan para otra identidad. El objetivo es orden
y unicidad, no contabilidad sin huecos. No se usa MAX+1 sin bloqueo.

Alternativas: secuencias nativas PostgreSQL (requieren una adaptación diferente
para SQLite) o bloqueo global durante cada inserción (no resuelve reservas
duraderas de formularios). El contador técnico compartido evita esos contratos
divergentes. La propietaria aprobó esta extensión mediante «DALE» tras la
explicación de su alcance, concurrencia y posibles saltos.

## Migración y conservación

1. Respaldo privado de base y archivos; restauración comprobada en copia aislada.
2. Mapa biyectivo por tabla y código anterior; orden estable documentado.
3. Adaptar todos los escritores y las referencias constantes antes del cambio.
4. Traducir PK/FK simples, compuestas y autorreferencias, referencias
   polimórficas, alertas y evidencias del experto. Mantener las identidades
   semánticas/versiones del motor independientes de la PK de almacenamiento.
5. Copiar las fotografías cuyo acceso exige el código del residente en la ruta;
   comprobar hash y tamaño; conservar originales para reversión.
6. Actualizar referencias estructuradas de auditoría preservando procedencia
   y el mapa anterior/nuevo. No reemplazar indiscriminadamente texto clínico.
7. Pausar escrituras y workers durante la conversión; cerrar sesiones antiguas
   tras el cambio para evitar identidades serializadas obsoletas.
8. Comparar cantidad y contenido de todas las filas normalizando los códigos
   nuevos al mapa anterior; verificar todas las relaciones, permisos, archivos,
   autoría y fechas, cargas repetidas del experto y turno de Rosa de 72 horas.
9. Ensayar fallo intermedio, reversión y concurrencia antes de la base en uso.

Una copia restaurada no prueba por sí sola el cambio. No se declara completado
hasta comprobar conservación y nuevas inserciones en PostgreSQL.

La identidad de la cuenta principal se fija mediante `CUENTA_PRINCIPAL_CODIGO`
con la PK verificada del mapa, antes de reabrir escrituras. No se asigna esa
protección automáticamente al primer correlativo. Con configuración presente,
cambiar el correo conserva la protección y el correo anterior no la transfiere.
El reconocimiento inicial por correo queda limitado a instalaciones todavía
sin esa configuración; una instancia operativa debe fijar su identidad.

Las tablas técnicas conservan sus IDs propios. El código del usuario es un
valor `COD_USU_...`; la columna continúa siendo `cod_usuario`. No se crea una
columna V1 ni una dependencia `cod_usu`.

Resultado de aplicación y verificación: [CODIGOS_CORRELATIVOS_RESULTADO.md](CODIGOS_CORRELATIVOS_RESULTADO.md).
