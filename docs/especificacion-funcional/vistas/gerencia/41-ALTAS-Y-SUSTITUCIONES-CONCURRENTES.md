---
title: "Gerencia: altas y sustituciones documentales concurrentes"
status: VERIFIED_LOCAL
version: "1.0"
last_reviewed: 2026-10-27
owner: RememberMind
source_of_truth: false
runtime_verified: true
verification_scope: [P03, P17, authorization_after_lock, disposable_sqlite_postgresql, livewire, browser]
---

# Cierre de los puntos 1 y 2 — 27/10/2026

La propietaria solicitó **«corrije e implementa el punto 1 y 2»** del listado
posterior al [informe40](40-INSTALACION-AUTORIZADA-Y-PRORROGA-EXPEDIENTE.md):
altas simultáneas y sustitución documental. Corresponden a **P03 y P17** de la
[aceptación de Personal/documentación](06-ACEPTACION-PERSONAL-DOCUMENTOS.md).
Quedan **implementados y verificados en el alcance de este informe**.
No se declara aceptado todo Gerencia ni los212 escenarios del rol.

## 1. Problemas reproducidos

| Flujo | Comportamiento observado antes | Consecuencia |
|---|---|---|
| Alta concurrente idéntica | Dos procesos superaban la validación previa; el segundo recibía `UniqueConstraintViolationException` por correo | SQL evitaba la duplicación, pero el conflicto esperado no se devolvía como validación útil |
| Sustitución desde un mismo original | Dos procesos que habían abierto el mismo documento podían confirmar ambos | El segundo sustituía el sucesor recién guardado por el primero sin advertir que su formulario estaba obsoleto |

La segunda situación ya tenía serialización por titular: no se atribuye al
defecto anterior una duplicidad SQL que la prueba no observó. El problema era
la falta de una referencia original explícita y del aviso de edición obsoleta.

Las reproducciones se ejecutaron en PostgreSQL desechable, con ambos procesos
esperando un bloqueo SQL comprobado antes de liberarlo. Evidencias conservadas:
`gerencia-concurrencia-12-reproduccion-alta.txt` y
`gerencia-concurrencia-12-reproduccion-documento.xml/.txt`, en
`output/auditoria-integral-20261025/`.

## 2. Punto1 — alta única, atómica y con conflicto comprensible

`GuardarPersonalInstitucional` conserva la transacción exterior y reutiliza
el coordinador existente de `usuarios`. Después de adquirirlo vuelve a
comprobar correo y documento de identidad antes de crear cuenta, personal,
rol o archivos. El bloqueo permanece hasta el commit exterior. Las
restricciones UNIQUE SQL existentes continúan siendo la última protección.

La cuenta y el personal se crean una sola vez. El perdedor recibe un error
de validación asociado a `correo` o `numero_documento`, sin exponer SQL o
sobrescribir el registro ganador. No se retorna éxito ni se considera un
reintento como una edición de otra cuenta. El correo se normaliza con las
reglas ya existentes; no se inventa otra equivalencia de identidad.

Si una colisión UNIQUE procede de un escritor que no utiliza este coordinador,
el catch primero compensa archivos nuevos después del rollback y revalida
exclusivamente la identidad. No ignora la PK de un User nuevo revertido. Un
fallo UNIQUE ajeno a esos campos sigue siendo un error reportable, no se
disfraza como conflicto de correo. La carrera normal del flujo usa la
validación bajo coordinador; no se afirma que una prueba del coordinador
ejercite todos los escritores externos posibles.

### Permisos después de la espera

Una lectura de permisos anterior al bloqueo no basta. Tras adquirirlo se
recupera el actor y se vuelven a exigir:

- Cuenta activa, alcance de Personal y exclusión de Familiar/preview.
- Facultad de alta de cuentas: permiso explícito correspondiente o alcance
  gerencial previamente aprobado.
- Facultad de carga documental conforme a los permisos o alcance ya vigentes.

Las carreras prueban suspensión de cuenta, cambio a Familiar y revocación
aislada de creación o carga mientras los dos procesos esperan. Ambas
operaciones deben denegarse sin filas, archivos, bienvenida ni auditoría de
alta. No se amplían permisos institucionales; las concesiones de las fixtures
existen únicamente en la base de pruebas.

La transacción exterior no se reintenta automáticamente con modelos y
archivos parcialmente usados. La sección del coordinador que puede
reintentarse solo contiene autorización y validación; no almacena archivos
ni registra correo. Las bienvenidas permanecen posteriores al commit. Un
fallo del envío no aparenta rollback de una cuenta efectivamente creada.

## 3. Punto2 — conservar el original que se pretende sustituir

`DocumentacionUsuarioService::referenciaVigente` obtiene una referencia
nominal con titular, tipo documental y documento original, incluida la
ausencia explícita de original. Ambos formularios la capturan al abrir la
carga y la conservan mediante una propiedad Livewire `Locked`.

Al guardar, el servicio autoriza de nuevo y bloquea al titular; consulta el
original vigente y compara la referencia **antes de almacenar el archivo**.
El orden continúa siendo titular → documentos → secuencia documental. No
se introduce un bloqueo de secuencia documental anterior al titular.

- Si coincide, crea el sucesor del mismo titular/tipo y conserva su FK al
  anterior, que pasa a REEMPLAZADO sin borrar sus bytes ni procedencia.
- Si cambió, rechaza la carga y conserva el formulario y sus entradas. El
  mensaje indica revisar la versión vigente y volver a abrir la carga.
- Si no había original y otro formulario realizó la primera carga, también
  rechaza la referencia de ausencia ahora obsoleta.
- Al reabrir expresamente la versión actual se permite una nueva corrección
  legítima. No se impide toda modificación posterior por haber una historia.
- Una referencia de otro titular, otro tipo o forma inválida se rechaza.
  Manipular la referencia desde el cliente también se rechaza por `Locked`.

Los callers de servicio que omiten la nueva referencia conservan
compatibilidad: el servicio captura el original antes de su transacción.
Los dos formularios interactivos siempre aportan su referencia del momento
de apertura; una referencia ausente no se sustituye silenciosamente al enviar.

### Contexto, claves y archivos

FOTO y FOTO_DOC se comprueban como un mismo requisito de consulta, conservando
la clave real del original. No se permite introducir otro vigente usando la
clave alternativa. Varios originales vigentes producen conflicto explícito;
no se elige arbitrariamente el primero. Los documentos de residente, contacto
o preadmisión quedan fuera de esta cadena laboral.

El escritor de Personal también evita elegir un documento arbitrario,
filtra esos contextos y conserva almacenamiento privado al sustituir un
original privado. Las entregas de una solicitud documental mantienen su
cadena por petición: varios respaldos legítimos de tipo OTROS no se fusionan
por compartir tipo. No se añadió una unicidad global documental nueva.

Un fallo posterior al almacenamiento revierte SQL y compensa solo el archivo
nuevo. Los bytes e identidades anteriores se conservan. Las pruebas comparan
rutas SQL con archivos existentes y sus hashes; no basta con contar filas.

## 4. Interfaz y revisión técnica

La ficha y el directorio reutilizan sus formularios. Los errores se asocian
al campo de archivo, con label, `aria-invalid`, descripción del error y
mensaje legible. Un conflicto no cierra el modal ni anuncia éxito.
Se sustituyó la dependencia ejecutable directamente tocada
`contactos.adultosMayores` por `contactos.residentes`; el resto del legacy
fuera de este recorrido no se declara limpiado.

Una revisión técnica separada, de solo lectura, identificó la necesidad de
reautorizar después de esperar el bloqueo y de repetir las puertas específicas
de creación/carga. Se corrigieron ambas y se añadieron sus carreras. La
revisión final no identificó otros bloqueantes en P03/P17. Es una revisión
técnica automatizada; no representa firma profesional clínica.

## 5. Trazabilidad requisito → archivo → prueba

| Requisito | Implementación | Evidencia |
|---|---|---|
| P03: correo/CI/doble envío sin duplicar | `GuardarPersonalInstitucional.php` | Tres datasets de alta y pruebas de identidad duplicada de `ContratoPersonalInstitucionalTest` |
| Autorización tras espera | Misma Action | Cuatro datasets de suspensión/rol/permiso de creación/permiso de carga |
| Atomicidad de alta | Misma Action, compensación existente | `test_fallo_final_revierte_cuenta_personal_roles_documentos_y_archivos`; correo posterior al commit |
| P17: un sucesor de un original | `DocumentacionUsuarioService.php` | Datasets `sustitucion_original` y `primera_carga`; FK, estados, archivos y hashes |
| Edición obsoleta en ficha/directorio | `UsuarioFichaPanel.php`, `UsuariosPanel.php` | Dos datasets Livewire: rechazo con inputs conservados y reapertura permitida |
| Referencia protegida y alias | Servicio y propiedades `Locked` | Referencia ajena/malformada, manipulación Livewire y rechazo de clave alternativa de fotografía |
| Fallo documental parcial | Servicio, compensación existente | `ContratoDocumentacionUsuarioTest::test_fallo_de_auditoria_revierte_sql_y_compensa_solo_archivo_nuevo` |
| Mensaje visible en dos pestañas | Dos Blade de carga | Navegador: primera carga guardada, segunda rechazada; cinco anchos, sin errores JS/HTTP |

### Archivos modificados en este hito

- `app/Modulos/Identidad/Aplicacion/Acciones/GuardarPersonalInstitucional.php`.
- `app/Modulos/Identidad/Aplicacion/Acciones/DocumentacionUsuarioService.php`.
- `app/Modulos/Identidad/Presentacion/Livewire/Paginas/UsuarioFichaPanel.php`.
- `app/Modulos/Identidad/Presentacion/Livewire/Paginas/UsuariosPanel.php`.
- `app/Modulos/Identidad/Presentacion/Vistas/livewire/identidad/usuario-ficha-panel.blade.php`.
- `app/Modulos/Identidad/Presentacion/Vistas/livewire/identidad/usuarios-panel/modales/subir-documento.blade.php`.
- `tests/Feature/GerenciaAltaSustitucionConcurrentePostgresTest.php` (nuevo),
  `tests/Feature/GerenciaConciliacionDocumentalTest.php`,
  `tests/Support/GerenciaConcurrenteWorker.php`.
- Fixture y prueba de navegador existentes:
  `tests/Support/ServidorGerenciaSintetico.php`,
  `tests/Frontend/gerencia-renovacion-e2e.test.js`.
- Este informe, enlaces de estado/aceptación y
  `scripts/auditar-aceptacion-gerencia.ps1`.

Las rutas anteriores son relativas al repositorio; los archivos ya modificados
por otras tareas conservan su trabajo previo. No se atribuye todo su diff a
este hito. No se modificaron migraciones, tablas, FK o permisos instalados:
**106 tablas de negocio +16 técnicas**, sin cambio estructural ni datos
sintéticos agregados a la base institucional por estas pruebas.

## 6. Resultados ejecutados

Entornos: SQLite en memoria con almacenamiento aislado; PostgreSQL18 en
clúster temporal propio con procesos independientes; navegador sobre SQLite
temporal y residentes/cuentas sintéticos. Correo simulado, sin envíos externos.

| Verificación | Resultado | Evidencia bajo `output/auditoria-integral-20261025/` |
|---|---|---|
| Regresión SQLite ampliada | **62 pruebas /446 aserciones, PASS** | `gerencia-concurrencia-12-aceptacion-sqlite.xml/.txt` |
| PostgreSQL: contratos, Livewire y nueve carreras | **52 pruebas /425 aserciones, PASS** | `gerencia-concurrencia-12-aceptacion-postgres.xml/.txt` |
| Última comprobación de formularios/errores | **15 pruebas /121 aserciones, PASS** | `gerencia-concurrencia-12-ui-final.xml/.txt` |
| Dos pestañas reales | **PASS**,27,1s; conflicto visible y formulario conservado | `gerencia-concurrencia-12-e2e.txt`; `gerencia-renovada/correccion-concurrente/resultado.json` |
| Responsive del conflicto | 1440/1280/1024/768/390px, sin desbordamiento horizontal | Cinco capturas en la misma carpeta; diagnóstico sin errores JS/HTTP |
| Build final | **PASS**,116 módulos; permanece aviso de bundle >500kB y tiempos del pluginCSS | `gerencia-concurrencia-12-build-final.txt` |

Los grupos se solapan: no sumarlos como pruebas únicas ni como212 requisitos
aceptados. La ejecución PostgreSQL final incorpora las comprobaciones finales
de permisos tras la espera. Los contratos SQLite también se ejecutaron; la
última comprobación de15 casos corresponde a los últimos cambios de Blade.

Los nueve datasets fuerzan a los dos procesos a esperar un bloqueo real
observado en `pg_stat_activity`. No se usa una espera temporal fija como
prueba de concurrencia. Tres casos de alta producen un confirmado y un
rechazado, una cuenta, un trabajador, tres documentos y una bienvenida
simulada; cuatro de revocación producen dos denegaciones y cero efectos;
dos documentales producen un confirmado y un rechazado, una versión vigente
y archivos originales intactos.

Los fallos iniciales no se ocultan: un archivo temporal de la fixture necesitó
conservar su objeto vivo para leerlo; después se reprodujo la violación
UNIQUE real. El caso documental inicial carecía de un permiso de la fixture;
corregida esta, reprodujo ambas cargas confirmadas sobre un mismo original.
El primer recorrido de navegador se bloqueó en una pestaña sin foco; se hizo
explícito el foco y se corrigió el selector del formulario que usa
`wire:submit.prevent`. El recorrido final pasó sin ampliar timeouts para
ocultar el fallo. La protección de negocio no se debilitó para pasar pruebas.

## 7. Comandos reproducibles

```powershell
./scripts/probar-sqlite.ps1 -Filtro 'GerenciaPersonalRenovacionTest|GerenciaConciliacionDocumentalTest|ContratoDocumentacionUsuarioTest|ContratoPersonalInstitucionalTest|GerenciaSolicitudesDocumentalesTest' -Informe output/auditoria-integral-20261025/gerencia-concurrencia-12-aceptacion-sqlite.xml -Log output/auditoria-integral-20261025/gerencia-concurrencia-12-aceptacion-sqlite.txt
./scripts/probar-postgres.ps1 -Filtro 'GerenciaAltaSustitucionConcurrentePostgresTest|GerenciaPersonalRenovacionTest|GerenciaConciliacionDocumentalTest|ContratoDocumentacionUsuarioTest|ContratoPersonalInstitucionalTest' -Informe output/auditoria-integral-20261025/gerencia-concurrencia-12-aceptacion-postgres.xml -Log output/auditoria-integral-20261025/gerencia-concurrencia-12-aceptacion-postgres.txt
./scripts/probar-sqlite.ps1 -Filtro GerenciaConciliacionDocumentalTest -Informe output/auditoria-integral-20261025/gerencia-concurrencia-12-ui-final.xml -Log output/auditoria-integral-20261025/gerencia-concurrencia-12-ui-final.txt
$env:RM_GERENCIA_QA_AMPLIACION='1'; $env:RM_GERENCIA_QA_CORRECCION='1'
node --test tests/Frontend/gerencia-renovacion-e2e.test.js
Remove-Item Env:RM_GERENCIA_QA_AMPLIACION,Env:RM_GERENCIA_QA_CORRECCION
npm run build
./scripts/auditar-aceptacion-gerencia.ps1
git diff --check
```

El modoCORRECCION del navegador ejercita la ficha con dos pestañas; no ejecuta
automáticamente todo el recorrido visual general de Gerencia. Los permisos de
esa fixture no alteran los permisos gerenciales operativos.

## 8. Captura y límites

![Carga obsoleta rechazada conservando archivo y formulario](../../../../output/auditoria-integral-20261025/gerencia-renovada/correccion-concurrente/conflicto-documental-390.png)

Dictamen: **P03/P17 cerrados en el recorrido y variantes descritos**. La
garantía entre escritores depende de utilizar estas Actions y servicios;
no se afirma una restricción SQL documental nueva ni se reparan antecedentes
duplicados automáticamente. La revisión visual del conflicto no es una
certificación WCAG de todos los modales antiguos.

Pendientes que continúan fuera de estos dos puntos: avisos de planificación
PC14/PC15, reportes RG-T13/T16/T17, historia de ocupación RO18 y la aceptación
restante de Gerencia. No se modificó el sistema experto ni se declara
validación clínica.

Git: rama `feature/apoyo-decision-informe-neurocognitivo`, HEAD `6d7b8c6d`.
Trabajo compartido preservado. **Sin staging, commit, push ni despliegue.**
Estado y diff estadístico: `gerencia-concurrencia-12-estado-final.txt` y
`gerencia-concurrencia-12-diff-final.txt` en el directorio de evidencia.

Verificación final documental: cinco documentos y119 enlaces locales sin
destinos faltantes (`gerencia-concurrencia-12-enlaces.json`); los nueve
datasets PostgreSQL se comprobaron contra JUnit sin fallos, errores u
omisiones (`gerencia-concurrencia-12-carreras.json`). El catálogo conserva212
casos únicos y enlaza P03/P17 con su evidencia específica sin aceptar
automáticamente los demás. `git diff --check` pasó; las advertencias de
normalización CRLF/LF del árbol compartido no son errores de whitespace.
