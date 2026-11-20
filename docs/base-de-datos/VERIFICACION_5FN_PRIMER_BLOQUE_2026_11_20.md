# Verificación del primer bloque de normalización · 20/11/2026

Alcance: migraciones `2026_11_20_000001` y `000002`, y sus lectores,
escritores y consumidores. Rama `feature/bdd-normalizacion-5fn`, checkout
aislado. La autorización vigente permite 100 tablas de negocio más las
técnicas. Este bloque no termina la normalización de las 84 relaciones de
partida ni acredita 5FN global.

## Cambio comprobado

- Consentimientos: el residente se obtiene de la admisión; se conserva el
  firmante y se impide trasladar el consentimiento a otro residente.
- Ejecuciones de cuidado: el residente se obtiene de la intervención y su
  plan; se protege el contexto frente a cambios del hijo o de sus padres.
- Días semanales: patrón y miembros ISO relacionados sustituyen la lista
  almacenada. La lectura conserva los nombres de los días para las vistas.
  Los patrones no pueden cambiar silenciosamente las órdenes referenciadas.
- La ficha administrativa, las consultas institucionales, los vínculos de
  alertas y la entrada de seeders utilizan las proyecciones correspondientes.
  El script de auditoría distingue una referencia derivada de una comparación
  entre dos columnas físicas.

No se cambiaron permisos, roles, reglas de inferencia, interpretación clínica,
fecha de las órdenes ni el flujo institucional de ingreso.

## Evidencia

| Comprobación | Resultado |
| --- | --- |
| Primer ensayo de contexto en PostgreSQL | 10 pruebas, 76 aserciones; sin errores ni fallos |
| Integración inicial de admisión, atomicidad, consultas y contexto en SQLite | 55 pruebas, 541 aserciones; sin errores ni fallos |
| Concurrencia: dos conexiones PostgreSQL independientes | 1 prueba, 8 aserciones; bloqueo del padre y rechazo posterior al confirmar el hijo |
| Selección semanal y reversión poblada SQLite, tras corregir índices | 6 pruebas, 47 aserciones; sin errores ni fallos |
| Selección semanal y ficha administrativa PostgreSQL | 7 pruebas, 50 aserciones; sin errores ni fallos |
| Ficha administrativa: aislamiento entre residentes | 1 prueba, 3 aserciones en SQLite; incluida también en el lote PostgreSQL |
| Regresión enfocada final de agenda, detalle y selección semanal | 6 casos aprobados; 54 aserciones; el caso navegador se verificó por separado |
| Navegador real con Laravel/Livewire y copia SQLite desechable | 1 prueba, 9 aserciones; sin errores ni fallos |
| Auditoría de esquema después de los cambios, PostgreSQL de pruebas | Completada en modo de solo lectura |
| Compilación frontend | `npm run build` aprobado; permanece el aviso de tamaño de paquetes |

Las evidencias de ejecución están bajo `storage/app/private/5fn-*` del
checkout, fuera de Git. No se suman los lotes como si fueran casos únicos.
Parte de las pruebas iniciales precede a las correcciones descritas abajo;
los casos afectados se repitieron específicamente.

El lote amplio de agenda ejecutó 47 casos y detectó dos fallos y un error:
una aserción antigua comprobaba el texto de una excepción en lugar del campo
rechazado; una vista mostraba los días numéricos; y el fixture HTTP intentaba
insertar columnas generadas al copiar SQLite. Se corrigieron conservando y
reforzando las comprobaciones de efectos. El primer reintento del navegador
detectó que faltaban las dependencias del checkout; tras `npm ci`, la prueba
HTTP real aprobó. No se presenta el lote amplio inicial como aprobado.

## Ensayo sobre copia poblada

Se obtuvo un respaldo local privado y se restauró en un servidor PostgreSQL
temporal con usuario sin privilegios de superusuario. La base de uso no fue
modificada por el ensayo.

Las **84 tablas de negocio y sus 15.297 filas** se copiaron a relaciones
temporales de contraste. Después de subir las dos migraciones se reconstruyó
la proyección anterior completa; `EXCEPT ALL` en ambos sentidos devolvió
**cero pérdidas y cero filas adicionales** para cada tabla. La misma
comprobación aprobó después de revertir y de reaplicar ambos cambios.

El resultado de ese checkout es **86 tablas de negocio + 16 técnicas = 102
físicas**. Es un conteo del ensayo, no el inventario instalado de la aplicación.
No había selecciones semanales explícitas en la copia original; las pruebas
sintéticas pobladas complementan ese caso para diferentes conjuntos de días.

## Revisión y límites

La revisión independiente encontró cuatro defectos iniciales y uno posterior:
un lector SQL de consentimientos, el script de auditoría, dos índices de FK,
la invalidación de una relación cargada y la retirada de índices en rollback
SQLite. Se corrigieron y se repitieron las comprobaciones afectadas. Dictamen
de revisión: **PASS limitado a este bloque**.

Siguen pendientes los contextos clínicos con atención opcional, las
declaraciones semánticas, IADL conservada, ocupación y los vínculos
opción/pregunta y horario/prescripción. La base operativa permanece en su
esquema anterior mientras se implementan los bloques restantes. No se
ejecutó un reinicio de datos ni se publicó esta rama.
