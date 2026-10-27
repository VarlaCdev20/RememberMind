---
title: "Gerencia: instalación autorizada y prórroga única por expediente"
status: VERIFIED_LOCAL
version: "1.0"
last_reviewed: 2026-10-27
owner: RememberMind
source_of_truth: false
runtime_verified: true
verification_scope: [local_schema_installation, documentary_extension, disposable_sql_tests, browser]
---

# Instalación de los diseños 35 y 37

La propietaria respondió **«autorizo»** al pendiente específico de instalación.
La [decisión estructural](../../../base-de-datos/DECISION_GERENCIA_DOCUMENTACION_HISTORIA_2026_10_27.md)
delimita los dos DDL, sus hashes, diccionarios, relaciones, normalización y
reversión. Se instalaron en PostgreSQL local `remembermind_dev` el
**27/10/2026, a partir de las 09:47:45, America/La_Paz**.
No se ejecutaron seeders, resets, despliegues ni operaciones de Git.

## Resultado efectivo

| Comprobación | Resultado |
|---|---|
| Inventario anterior | 100 de negocio +16 técnicas =116 físicas |
| Inventario posterior | **81 operativas +25 expertas =106 de negocio**, +16 técnicas =122 físicas |
| Cambios exactos | Ocho columnas nullable, seis relaciones; mismos bytes de los DDL revisados |
| Datos previos de usuarios/documentos | Recuento e igualdad de todas las columnas originales, comprobados bajo bloqueo transaccional |
| Historia desconocida | Tablas nuevas vacías y ocho columnas históricas nuevas NULL; no se atribuyó autoría o fecha inexistente |
| Registro técnico | Dos transiciones registradas en `migrations` |
| Verificación posterior independiente de la instalación | 90 restricciones de las relaciones nuevas validadas; cuatro triggers históricos habilitados |
| Capacidades reconocidas | Documentación, historia de vínculos e historia de asignaciones disponibles |

Respaldo privado PostgreSQL anterior a la instalación: **6.644.835 bytes**,
SHA-256 `3fe110982e229f7e578bede8b768fbc32d20a600e07268ff63cfbd9c51929aff`.
Se comprobó lectura de su catálogo; **no se afirma haber restaurado todo el
respaldo**. El archivo permanece fuera del contenido público y del control de
versiones. No se publican datos, contraseñas o protocolos personales.

Evidencia agregada:
[instalación](../../../../output/auditoria-integral-20261025/gerencia-instalacion-20261027.json)
y [verificación posterior](../../../../output/auditoria-integral-20261025/gerencia-verificacion-instalacion-20261027.json).
El manifiesto anterior de propuesta conserva su corte y queda sucedido por
estos resultados; su estado original no se convierte retrospectivamente en
una aprobación recibida antes de tiempo.

## P19: una concesión por alta, sin duplicar el acto

La implementación anterior permitía representar una prórroga por destinatario.
Ahora la Action serializa emisión/concesión mediante el mismo coordinador y
verifica el expediente completo: persona, origen ALTA y plazo original
conservado. Guarda **una sola concesión** en la solicitud donde se otorgó.
El resto muestra el plazo vigente y la procedencia de esa concesión, manteniendo
sus fechas originales y campos de concesión NULL.

Lista, detalle, avisos y comprobación de entrega puntual utilizan la misma
proyección. Otra solicitud de esa alta emitida después recibe el mismo efecto
sin otra concesión. Solicitudes posteriores, otro límite de alta y otra persona
se mantienen separados. Una entrega dentro del plazo prorrogado que después
se observa no se transforma retrospectivamente en entrega tardía.

La unicidad entre solicitudes se garantiza en la **Action con serialización
transaccional**, probada con dos procesos PostgreSQL sobre filas distintas.
No se añadió una restricción SQL global inexistente al DDL aprobado. Si una
escritura fuera de la Action introduce varias concesiones, el lector muestra
contradicción, no elige fecha y oculta la acción de conceder otra. No se borra
la inconsistencia ni se selecciona un original por fecha o código.

| Requisito | Archivo principal | Prueba/evidencia |
|---|---|---|
| Concesión única y rollback | `GestionarSolicitudesDocumentales.php` | Solicitudes: segunda solicitud rechazada y fallo de auditoría revertido |
| Efecto compartido y procedencia real | `PlazoExpedienteDocumental.php` | Solicitudes: varias peticiones, posterior a concesión, exclusiones y contradicción |
| Avisos y entrega puntual | `ConsultarDocumentacionPersonal.php` | Solicitudes: aviso con fecha vigente y entrega dentro de prórroga heredada |
| Lista y seguimiento comprensibles | `DocumentacionPersonalPanel.php` y su Blade | Livewire + navegador real, concesión heredada sin botón de segunda prórroga |
| Carrera entre filas distintas | `GerenciaDocumentacionConcurrenciaPostgresTest.php`, worker | Dataset `prorrogar_expediente`: un confirmado, un rechazado y una concesión |
| Instalación conjunta atómica | `GerenciaInstalacionExtensionesTest.php` | Fallo después de35/después de37; esquema y filas originales recuperados |

Los archivos de aplicación pertenecen a `app/Modulos/Identidad/`; se conserva
la arquitectura y la autorización contextual existentes. No se alteraron
permisos clínicos, catálogos, remitente SMTP ni resultados del motor experto.

## Pruebas ejecutadas en este corte

No sumar grupos solapados como cobertura única ni como aceptación de todos los
212 casos del rol. Los informes anteriores mantienen sus resultados propios.

| Entorno | Resultado | Evidencia en `output/auditoria-integral-20261025/` |
|---|---|---|
| SQLite: Solicitudes, Estructura e Historia | **30 pruebas /229 aserciones, PASS** | `gerencia-prorroga-expediente-sqlite.xml/.txt` |
| PostgreSQL: mismos grupos y concurrencia | **36 pruebas /265 aserciones, PASS** | `gerencia-prorroga-expediente-postgres.xml/.txt` |
| Instalación atómica SQLite | **3 pruebas /22 aserciones, PASS** | `gerencia-instalacion-atomica-sqlite.xml/.txt` |
| Instalación atómica PostgreSQL | **3 pruebas /24 aserciones, PASS** | `gerencia-instalacion-atomica-postgres.xml/.txt` |
| Navegador real, base sintética desechable | **PASS**, 58 combinaciones registradas sin desbordamiento; sin errores JS/HTTP; recorrido277,7s | `gerencia-prorroga-expediente-e2e.txt`; `gerencia-renovada/aceptacion-accesibilidad/diagnostico.json` y `matriz.json` |
| Build | **PASS**, 116 módulos; aviso existente de paquete >500kB y diagnóstico de tiempos del plugin CSS | `gerencia-instalacion-build.txt` |

Se conservaron los resultados iniciales fallidos `*-inicial.xml/.txt`. La causa
fue un fixture nuevo que intentaba cambiar un plazo original inmutable; se
creó su historia independiente sin debilitar el guard del Model. La repetición
final pasó en los dos motores. SQLite y PostgreSQL utilizaron almacenamiento y
bases propios; la instalación local ocurrió después de estas comprobaciones.

Comandos de prueba e instalación:

```powershell
./scripts/probar-sqlite.ps1 -Filtro 'GerenciaSolicitudesDocumentalesTest|GerenciaDocumentacionEstructuraTest|GerenciaHistoriaNominalTest' -Informe output/auditoria-integral-20261025/gerencia-prorroga-expediente-sqlite.xml -Log output/auditoria-integral-20261025/gerencia-prorroga-expediente-sqlite.txt
./scripts/probar-postgres.ps1 -Filtro 'GerenciaSolicitudesDocumentalesTest|GerenciaDocumentacionEstructuraTest|GerenciaHistoriaNominalTest|GerenciaDocumentacionConcurrenciaPostgresTest' -Informe output/auditoria-integral-20261025/gerencia-prorroga-expediente-postgres.xml -Log output/auditoria-integral-20261025/gerencia-prorroga-expediente-postgres.txt
./scripts/probar-sqlite.ps1 -Filtro GerenciaInstalacionExtensionesTest -Informe output/auditoria-integral-20261025/gerencia-instalacion-atomica-sqlite.xml -Log output/auditoria-integral-20261025/gerencia-instalacion-atomica-sqlite.txt
./scripts/probar-postgres.ps1 -Filtro GerenciaInstalacionExtensionesTest -Informe output/auditoria-integral-20261025/gerencia-instalacion-atomica-postgres.xml -Log output/auditoria-integral-20261025/gerencia-instalacion-atomica-postgres.txt
$env:RM_GERENCIA_QA_AMPLIACION='1'; $env:RM_GERENCIA_QA_HISTORIA='1'; $env:RM_GERENCIA_QA_PRORROGA='1'; $env:RM_GERENCIA_QA_ACCESIBILIDAD='1'
node --test tests/Frontend/gerencia-renovacion-e2e.test.js
npm run build
php work/gerencia-instalar-extensiones-20261027.php inspeccionar
php work/gerencia-instalar-extensiones-20261027.php instalar AUTORIZACION-PROPIETARIA-35-37
php work/gerencia-verificar-extensiones-20261027.php
```

El comando `instalar` se ejecutó **una vez** y rechaza un destino ya instalado;
no repetirlo como comprobación. `inspeccionar` y `verificar` no modifican SQL.
La ruta y reversión de instalaciones nuevas están delimitadas en la decisión;
no se trasladaron ni reescribieron los DDL verificados.

## Evidencia visual

![Seguimiento móvil con plazo original, plazo vigente y concesión heredada](../../../../output/auditoria-integral-20261025/gerencia-renovada/aceptacion-accesibilidad/documentacion-prorroga-expediente-390.png)

Captura inspeccionada con datos sintéticos: nombre del respaldo y persona,
fechas legibles, procedencia y motivo de la concesión; sin identificadores
técnicos visibles ni botón que permita conceder otra prórroga.

## Alcance cerrado y aceptación restante

**Cierre posterior de puntos1/2 — 27/10/2026:** el [informe41](41-ALTAS-Y-SUSTITUCIONES-CONCURRENTES.md) implementa y verifica P03/P17: altas únicas, sustituciones con original explícito, reautorización tras espera y nueve carreras PostgreSQL. Las referencias pendientes a esos dos casos que siguen conservan el corte de instalación; PC14/PC15, reportes, RO18 y resto de aceptación continúan separados.

**Implementado, probado e instalado localmente:** diseños35/37 y conciliación
P19 por expediente. Esta instalación sustituye los pendientes1/2 del
[informe39](39-ACEPTACION-REGRESIONES-Y-CASOS.md); no declara todo Gerencia
aceptado ni afecta la validación clínica del sistema experto.

Siguen pendientes los casos específicos P03/P17 de concurrencia de alta y
sustitución documental; PC14/PC15 de avisos de planificación; RG-T13/T16/T17
de comparación exhaustiva PDF/CSV; RO18 y la aceptación restante por cada caso
de los212. No se atribuyen estos cierres a pruebas de otros flujos.

Git conserva la rama `feature/apoyo-decision-informe-neurocognitivo`,
HEAD `6d7b8c6d`, y el trabajo simultáneo. **Sin staging, commit, push ni despliegue.**
Estado y resumen del árbol compartido: `gerencia-instalacion-estado.txt` y
`gerencia-instalacion-diff-stat.txt`, en el directorio de evidencia. No se
atribuyen todos esos cambios a este hito. `git diff --check` pasó; mantiene
advertencias de normalización CRLF/LF ya presentes, sin errores de whitespace.
Se comprobaron11 documentos y191 enlaces locales sin destinos faltantes
(`gerencia-instalacion-enlaces.json`). El respaldo privado también se verificó
por hash y quedó excluido de Git.
