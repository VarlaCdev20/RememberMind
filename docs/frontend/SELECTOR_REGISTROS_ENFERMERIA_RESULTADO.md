# Selector de registros de enfermería

Fecha: 2026-10-13. Alcance: presentación y navegación del modal de Mis residentes.

## Antes y flujo aprobado

El selector mezclaba diez controles, cinco acciones y una consulta al sistema experto.
Para ENFERMEROS se aplica el orden solicitado: cuatro acciones prioritarias,
nueve controles y observaciones, y un pase de turno. Las tarjetas siguen sujetas
a sus permisos existentes; catorce es el total con permisos completos.
El título conserva el nombre del residente. El formulario interno de cada control,
la autoría, la persistencia y el bloqueo por alertas críticas pendientes se conservan.

## Implementación

- `quick-register-selector.blade.php`: nueva agrupación solo para enfermería.
  Retira los accesos Procedimiento y Sistema experto del modal, y cambia Cognición
  por Observación cognitiva. Los otros roles mantienen su presentación anterior.
- `quick-register-grid.blade.php`: admite atributos de variante y una sección
  de una columna, reutilizando las tarjetas existentes.
- `resident-directory.css`: variante acotada al selector, texto sin truncamiento,
  iconos de 24 px, alturas uniformes por sección y acento suave de advertencia
  únicamente en Registrar incidente.
- `MisPacientesRedisenadaTest.php`: orden exacto, catorce acciones, destinos,
  permisos, continuidad crítica, apertura/cierre y presentación de otro rol.
- `ResultadosExpertoResidenteTest.php`: acceso al experto desde resumen y ficha,
  fuera del selector de registros.

## Integración y seguridad

| Acceso | Destino conservado o reutilizado |
| --- | --- |
| Medicación | Formulario existente cuando hay dosis pendiente; programación cuando no hay dosis pendiente |
| Ejecución de cuidado | `admin.enfermeria.tareas`, con contexto del residente |
| Signos, dolor, ingesta, hidratación, eliminación y movilidad | Acciones existentes de `abrirFormularioRegistro` |
| Observación cognitiva | `admin.enfermeria.seguimiento`, conservando `cuidado=cognicion` |
| Conducta, sueño y heridas | Metadatos existentes de `NavegacionCuidadosService`; se preservan los formularios incorporados en el checkout compartido |
| Incidente | `admin.enfermeria.incidentes` |
| Pase de turno | `admin.enfermeria.pase-turno`, protegido por `pases_turno.ver` |
| Sistema experto | Accesos existentes del resumen y ficha; ruta y Policy intactas |

El pase abre la pantalla existente de transferencia de guardia, donde se elige
el residente de la jornada autorizada. No se renombra el registro diario para
hacerlo pasar por un pase, ni se agrega una selección automática que el panel
actual no implementa. Procedimiento permanece funcional en el backend.
No se modificaron permisos, Policies, reglas clínicas, formularios ni base de datos.
No se introdujeron dependencias ejecutables V1.

## Verificación

- Suite relacionada: `php vendor/bin/phpunit tests/Feature/MisPacientesRedisenadaTest.php tests/Feature/SistemaExperto/ResultadosExpertoResidenteTest.php`:
  192 pruebas y 1933 aserciones aprobadas en el checkout aislado.
- Verificación final del selector, incluida la nueva prueba de otro rol:
  `php vendor/bin/phpunit tests/Feature/MisPacientesRedisenadaTest.php --filter='selector|ficha_rapida'`:
  7 pruebas y 143 aserciones aprobadas tanto en el checkout compartido como en el aislado.
  Esta selección se solapa con la suite anterior.
- `npm run build`: aprobado en ambos checkouts. El compartido conserva el aviso
  de bundle JavaScript superior a 500 kB; no impide el build.
- QA visual: PASS en 1440, 1280, 1024, 768 y 390 px CSS. HTML real de Livewire
  con datos sintéticos y permisos completos: 4/9/1 tarjetas, sin desbordamiento
  horizontal, sin etiquetas cortadas y con alturas consistentes por sección.
- Aplicación local: apertura real, foco visible de 2 px por teclado y navegación
  del acceso Pase de turno a la pantalla real de transferencia de guardia.
  La cuenta local inicialmente mostraba trece tarjetas: faltaba aplicar la
  asignación de Conducta ya aprobada en el contrato del módulo.
- Contraste de subtítulos: 5.53:1 en tarjetas normales y 4.66:1 en incidente.
- Capturas sintéticas: `modal-registros-14.jpg` y `modal-registros-mobile.jpg`
  en el directorio de visualizaciones de este chat.

## Límites

No hay dependencia funcional pendiente para esta reorganización. La captura
sintética verifica geometría y texto; la interacción de Livewire se comprobó
en la aplicación y mediante sus pruebas. No se ejecutó PostgreSQL: el cambio
no modifica persistencia ni concurrencia. Los cambios paralelos del checkout
compartido quedan fuera del commit aislado. Sin push ni despliegue.

## Corrección del acceso a Conducta

Se detectó que `ENFERMEROS` tenía `registros_conductuales.ver`, pero no
`registros_conductuales.crear` en la base local. La asignación ya está definida
en `RolesAndPermissionsSeeder` y su aprobación explícita del 13/10/2026 está
documentada en `CONDUCTA_V2_REFERENCE_ANALYSIS.md`.
Se aplicó únicamente ese permiso existente al rol en entorno `local`, de forma
transaccional, con Activitylog y limpieza de la caché de permisos. No se retiró
ningún permiso ni se alteraron otros roles o las restricciones contextuales.
La aplicación ahora muestra las catorce tarjetas y Conducta abre su formulario
con el residente seleccionado. No se guardaron registros clínicos durante QA.
Pruebas enfocadas de `RegistroConductaTest` (popup, consulta y contexto):
8 pruebas y 21 aserciones aprobadas. La corrección es de configuración local;
no requirió cambios del formulario ni otro build.
