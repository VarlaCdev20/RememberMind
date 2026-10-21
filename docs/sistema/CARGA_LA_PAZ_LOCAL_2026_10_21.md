# Integración y carga local de la cohorte La Paz

Fecha: 21/10/2026, America/La_Paz. Alcance: incorporar los dos commits de
`origin/SEEDERS` y cargar su cohorte sintética en `remembermind_dev`.
Estado: **INSTALADA Y VERIFICADA en `remembermind_dev`**.

## Integración

Se descargó `SEEDERS` y se aplicaron ambos commits mediante cherry-pick en
`feature/apoyo-decision-informe-neurocognitivo`, conservando el trabajo local:

| Origen | Commit local | Contenido |
|---|---|---|
| `99291ce3` | `7190a776` | Seeder optativo, datos sintéticos, fuentes y pruebas. |
| `3fcd9d7e` | `80cafad9` | Correcciones del expediente documental e historiales. |

No se cambió de rama, no se mezcló trabajo local ajeno y no se hizo push.
Los commits conservan su autor original. La corrección de compatibilidad de
este incremento se registra por separado.

## Destino y conservación

Antes de la carga se comprobó entorno `local`, conexión PostgreSQL en
`127.0.0.1`, base efectiva `remembermind_dev` y esquema `public`.
Existían **32 residentes**, **6 evaluaciones expertas**, **32 reglas** y
**120 tablas físicas**: 104 de negocio y 16 técnicas. Ninguna de las 48
preadmisiones de la cohorte estaba instalada.

El comando crea un respaldo privado custom mediante PostgreSQL 18 antes de
la transacción. No se ejecutan migraciones, fresh, borrados o restauraciones.
La carga no incorpora resultados, reglas ni etiquetas del motor experto.

## Incompatibilidades corregidas

1. El límite inicial de 128 MB se agotó al calcular huellas antes de insertar.
   Se repitió con `-d memory_limit=1024M`, aplicado exclusivamente al proceso.
2. La base ya tenía los dos turnos del conjunto con otras PK. Se reutilizan
   por nombre exacto solo si coinciden horarios, orden y estado; se conservan
   sus observaciones y todas sus filas. Las FK nuevas usan la identidad real.
3. Ya existía el tipo «Glucemia de laboratorio». Se conserva si coinciden
   categoría, estado y exigencia de componentes/informe. No se altera su
   descripción. Los componentes y estudios nuevos usan su FK real.
4. La admisión formal avanza los contadores de códigos. La conservación
   distingue estas reservas técnicas de la historia: prohíbe desaparición,
   retroceso, entidad ajena y avance sin cada código nuevo correspondiente.
   Las filas preexistentes de las demás tablas se comparan estrictamente por
   huella. No se desactiva el generador ni se crean residentes por otra vía.

Los intentos fallidos revirtieron las inserciones. Se comprobó después que
seguían existiendo 32 residentes y cero preadmisiones de esta cohorte.

La ejecución definitiva terminó con código 0 y confirmó la transacción:

| Resultado local | Total |
|---|---:|
| Residentes anteriores → actuales | 32 → 80 |
| Nuevos residentes/admisiones/ocupaciones/contactos | 48 por relación |
| Filas nuevas totales | 430.532 |
| Filas anteriores conservadas por comparación estricta | 131.893 |
| Usuarios / personal nuevos | 71 / 23 |
| Aplicaciones instrumentales / respuestas nuevas | 503 / 5.030 |
| Controles cognitivos descriptivos nuevos | 2.726 |
| Evaluaciones expertas / reglas anteriores y posteriores | 6 / 32, sin cambios |
| Tablas físicas anteriores y posteriores | 120, sin cambios |

El total de filas conservadas excluye las tablas técnicas volátiles declaradas
por el comprobador y los contadores de códigos, verificados separadamente.
Los turnos y el tipo de estudio reutilizados explican las tres filas menos
frente a las 430.535 de la instalación remota documentada. No se omitieron
residentes ni registros asistenciales para ajustar el total.

Respaldo definitivo privado:
`storage/app/private/la-paz-2025-2026/respaldo-20261021-082159-467b9ba1/anterior.dump`,
1.912.267 bytes, SHA-256
`04806ccb4f21180e12bc224f42be1bade352b49c4e51e0342f1d20d8bd0e072c`.
`pg_restore --list` comprobó que es legible. No se ejecutó una restauración
completa ni se afirma que la enumeración la sustituya.

## Verificación

- SQLite aislado: 12 pruebas y 61 aserciones, PASS.
- PostgreSQL desechable: 12 pruebas y 61 aserciones, PASS; cluster detenido y
  eliminado por el runner.
- Pint: PASS en seeders y prueba modificada.
- `npm run build`: PASS con Vite 8.3.1, después de integrar las dos vistas.
- El primer ensayo SQLite compartido falló por un documento ausente/alterado;
  al repetir con almacenamiento aislado propio pasó la selección completa.
  No se debilitaron las comprobaciones documentales para resolverlo.

La selección cubre carga completa, catálogos existentes, idempotencia,
conservación, roles, documentación, rollback y rechazo de entorno/base.
Los negativos nuevos incluyen turno incompatible, contador sin registro
nuevo y contador que retrocede. No se presenta como validación clínica ni
como QA visual integral de las pantallas.

La evidencia JUnit está en `storage/app/private/lapaz-integracion-sqlite-definitivo.xml`
y `storage/app/private/lapaz-integracion-postgres-definitivo.xml`. La selección
fue `LaPazSeedIntegrityTest|ExpedienteDocumentalVistaTest`. Para SQLite se usó
`LARAVEL_STORAGE_PATH` en un directorio privado propio; PostgreSQL ya aísla su
almacenamiento mediante `scripts/probar-postgres.ps1`. Los casos usan datos y
archivos sintéticos, sin apuntar las pruebas a la base instalada.

## Comandos de carga y comprobación

```powershell
php -d memory_limit=1024M database/seeders/LaPaz/ejecutar.php --base=remembermind_dev
php -d memory_limit=1024M database/seeders/LaPaz/ejecutar.php --base=remembermind_dev --verificar
```

La reejecución debe verificar sin insertar ni cambiar contraseñas. Los
archivos, respaldo, resultado, manifiesto y credenciales quedan en
`storage/app/private/la-paz-2025-2026/`, fuera de Git. No copiar contraseñas,
expedientes o dumps al informe ni a la salida pública.

Se ejecutaron tanto `--verificar` como una nueva llamada normal después de
la confirmación. Ambas terminaron con código 0 y el mensaje «Cohorte existente
verificada. No se insertaron ni sobrescribieron filas». La lectura final
confirmó 80 residentes, 48 preadmisiones de la cohorte, 120 tablas, seis
evaluaciones y 32 reglas. La carga queda cerrada en esta base local; no se
realizó push ni despliegue. Los historiales tienen corte al 20/10/2026 a las
20:00; no se extendieron automáticamente al día de instalación.
