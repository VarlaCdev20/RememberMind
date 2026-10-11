---
title: "Desacoplamiento de fronteras de aplicación"
status: CURRENT
last_reviewed: 2026-10-11
owner: RememberMind
source_of_truth: false
verified_against_commit: 2818ff44e9325754dd3d42ea84d19ec7902baf71
verification_scope: FULL_SQLITE_SCOPED_POSTGRES_FRONTEND_BUILD
runtime_verified: false
---

# Desacoplamiento — 11/10/2026

Continuación sobre `9e5d3be`, en `C:\Users\CARLAENCINAS\Desktop\RememberMind`, rama `PODEMOS-SEGUIR`. Se conserva el [ADR híbrido](ADR_ARQUITECTURA_HIBRIDA.md): doce propietarios y cuatro capas para administración/operación; puertos y adaptadores para el núcleo experto. Aplicación conserva Eloquent y capacidades Laravel autorizadas por el ADR.

## Etapa 1: contexto y dirección de dependencias

`ContextoEjecucion` proporciona la identidad autenticada y la condición de lectura. Su adaptador resuelve el usuario y la petición vigentes en cada llamada; no retiene la petición o identidad del momento de construcción. La excepción de lectura de turno sigue siendo exclusivamente GET. El actor facilitado por un llamador no sustituye la identidad confiable del contexto.

La previsualización de rol usa `EstadoPrevisualizacion`, implementado en sesión. Conserva clave, limpieza de valores inválidos y restricciones existentes; no modifica los roles reales. Presentación proporciona explícitamente el contexto de ruta al encabezado y añade los parámetros HTTP a la paginación del explorador.

Aplicación experta depende de `ConocimientoVersionado`, `ExtraccionFuentesCOGMEM` y `EvaluadorExperto`; el provider compone sus adaptadores. La política de alcance técnico se trasladó desde persistencia a `AlcanceLecturaExperta`, conservando restricciones de entornos, versiones y lectura de prueba. Se retiraron construcciones directas de adaptadores desde casos de uso.

## Etapa 2: efectos externos y consultas de presentación

| Frontera | Puerto de Aplicación | Adaptación exterior |
|---|---|---|
| Archivos generales | `ArchivosAplicacion` | Storage y recepción de subidas |
| Documentos de usuario | `DocumentosUsuario` | Disco público, UUID, compensación y rutas restringidas existentes |
| PDF | `GeneradorPdf` | DomPDF |
| Correo de registro | `NotificacionesRegistro` | Mail y plantillas existentes |
| Enlaces | `EnlacesAplicacion` | UrlGenerator |

Las rutas, discos, destinatarios, datos de correo y transacciones existentes se conservan. La bienvenida de personal continúa después del commit. Una subida fallida lanza excepción antes de devolver una ruta persistible. Preadmisión comprueba la creación del directorio y la escritura del PDF dentro del manejo de errores existente: un fallo deja `todosGenerados=false`, no crea Documento y mantiene la solicitud PENDIENTE.

`PrepararFichaClinica` concentra las relaciones, filtros y límites de la ficha y vuelve a prepararlos tras la hidratación Livewire. `ConsultarPanelResidenteAlerta` comparte la autorización y preparación de gráficos/ubicación entre AlertasPanel y CampanaNotificaciones; valida actor activo, permiso y alcance de pacientes. El render revalida paneles abiertos. Se completaron las precargas necesarias de persona, ubicación y cuidados, conservando las listas y límites originales.

## Diseño de dominio y preservación

- Entidades y estados: no cambia esquema, modelos centrales, catálogos ni estados institucionales. Aprobación de preadmisión sigue sin crear residente.
- Permisos: los puertos no conceden permisos. Se conservan Policies y reglas de turno; las lecturas de la campana aplican el alcance compartido del panel de alertas.
- Aplicación: coordina operaciones y prepara proyecciones; infraestructura implementa los efectos externos. Presentación conserva HTTP, sesión y render.
- Transacciones y eventos: no se introduce trabajo clínico asíncrono ni nuevo consumidor; el límite DB/archivos conserva las compensaciones existentes.
- Concurrencia: la comprobación final incluye la carrera de cama real en PostgreSQL aislado, además de pruebas SQLite.

## Verificación

Las regresiones nuevas cubren cambio de petición/identidad después de resolver el contexto, actor distinto al autenticado sin escrituras, previsualización sin modificar roles, enlaces de paginación, ficha preparada después de perder relaciones, paneles después de otra interacción Livewire, denegación por alcance/estado y errores de almacenamiento. El render parcial de gráficos preparado verifica cero consultas; esta afirmación se limita al parcial ejercitado.

El gate AST `ApplicationBoundaryTest` impide importar Infraestructura/Presentación desde Aplicación, usar directamente HTTP/sesión de Illuminate, Mail/DomPDF/Storage/Auth y los helpers HTTP/sesión/render/enlaces auditados. Complementa los gates existentes de Dominio, núcleo experto, namespaces, transacciones y Blade. No detecta toda resolución dinámica mediante contenedor.

| Comprobación final | Resultado |
|---|---|
| PostgreSQL 18 desechable | 183 pruebas, 3.035 aserciones, cero errores/fallos/omisiones; 4m23.789s |
| Frontend | 151 pruebas, cero fallos |
| Vite | Build aprobado |
| Motor independiente | Cinco casos sin Laravel/Eloquent aprobados |
| Sintaxis del cambio | 71 fuentes comprobadas, cero errores PHP |
| Preservación | 50 archivos protegidos sin cambios de hash/tamaño |
| SQLite completa | 1.094 pruebas, 32.023 aserciones, cero errores/fallos, 15 omisiones; 17m25.825s |

Evidencia local en `C:\Users\CARLAENCINAS\Documents\Codex\2026-10-10\files-pasted-by-the-user-prompt\outputs`: archivos `DESACOPLAMIENTO_*`, JUnit, logs y hashes del código probado. Las ejecuciones iniciales se interrumpieron deliberadamente para corregir el fallo de escritura detectado en revisión; solo se acredita la reejecución final. Las cohortes se solapan y no deben sumarse. El cluster PostgreSQL final se detuvo y su wrapper terminó con salida cero.

Etapa 3 terminada: las 71 fuentes comprobadas conservaron sus hashes durante ambas suites y quedaron en el commit local `2818ff4`. Las 15 omisiones SQLite corresponden a dos casos propios de PostgreSQL y trece capacidades Jetstream/Fortify deshabilitadas. La cohorte PostgreSQL ejecutó los casos de concurrencia sin omisiones. No se cambiaron migraciones, modelos, reglas expertas ni locks de dependencias. La revisión independiente final no encontró bloqueos pendientes dentro de este corte. No se realizó push.

## Límites vigentes

Aplicación conserva Eloquent, DB, Gate, Validator, Cache, configuración, resolución Laravel y el tipo Symfony UploadedFile en gestión HTTP de usuarios. Esto no convierte toda administración en un núcleo independiente del framework. La coordinación entre módulos y las identidades `App\Models` siguen siendo excepciones explícitas del ADR.

No se certifica ausencia universal de SQL diferido en todas las vistas. Archivos y transacciones DB no son atómicos de forma universal: existen flujos heredados con riesgo de huérfanos ante fallos posteriores y hashes que pueden devolver `false`. La privacidad de documentos en disco público requiere su intervención específica. Es deuda separada, conservada explícitamente; no se afirma haberla resuelto mediante interfaces.

La validación clínica, la activación de inferencias, el advisory de desarrollo pendiente y el CI remoto conservan los límites del [informe de preparación](../sistema/PREPARACION_DESARROLLO_2026_10_11.md).
