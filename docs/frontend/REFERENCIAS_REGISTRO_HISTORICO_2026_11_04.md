# Referencias históricas de formularios de registro

Fecha: 04/11/2026. Alcance: localizar los diseños de adulto mayor y personal que recuerda la propietaria, antes de depurar vistas. Esta investigación no modifica rutas, permisos, persistencia ni formularios operativos.

## Originales localizados

| Referencia | Rasgos reconocibles | Procedencia |
|---|---|---|
| Registro de Admisión de adulto mayor, página independiente | Seis pasos: Identidad, Médicos, Ubicación, Contacto, Ingreso y Cierre. Fotografía, progreso con porcentaje, cabecera glass y campos en columnas. | `resources/views/pages/adultos-mayores/create.blade.php`, commit `ea7cca83`, padre de la eliminación `a6f909c8` del 18/09/2026. |
| Registro de Personal, página independiente | Cabecera «Registro de Personal», indicador de completitud y seis pasos: Identidad, Documento, Contacto, Rol, Seguridad y Confirmar. | `resources/views/pages/usuarios/create.blade.php`, commit `ea7cca83`. La variante actual sigue en Identidad. |
| Registrar Personal Institucional, modal antiguo | Cabecera compacta, progreso y siete pasos: Acceso, Datos, Dirección, Rol, Docs, Inst. y Resumen. Es distinto del modal Nuevo usuario de Superadmin. | `app/Modulos/Identidad/Presentacion/Vistas/livewire/identidad/personal-institucional-form.blade.php`, commit `155a1516`, anterior a `a4c61c97`. |

Se recuperaron copias exactas, sin ejecución, en:

- [Adulto mayor: página de seis pasos](../../output/referencias-formularios-20261104/adulto-mayor-pagina-6-pasos.blade.txt).
- [Personal: página de seis pasos](../../output/referencias-formularios-20261104/personal-pagina-6-pasos.blade.txt).
- [Personal: modal de siete pasos](../../output/referencias-formularios-20261104/personal-modal-7-pasos.blade.txt).
- [Procedencia y SHA-256](../../output/referencias-formularios-20261104/procedencia.json).

Son fuentes de referencia visual, no pantallas restauradas. Los archivos terminan en `.txt` y no se añadieron a la aplicación como Blade ejecutable. No se generaron capturas ni se afirma fidelidad visual renderizada de estos originales.

## Conexión actual y límites de la depuración

- La página histórica de alta directa de adulto mayor fue eliminada; no tiene hoy una ruta GET propia de creación. El flujo vigente conserva preadmisión, revisión y admisión formal con cama. Recuperar el diseño no autoriza restaurar el antiguo POST de alta directa.
- `adulto-mayor-form-modal.blade.php` aún se incluye desde `adultos-mayores-panel`; su componente actual abre con un identificador y establece edición. La rama visual «Admisión Nuevo Ingreso» no demuestra que exista un alta habilitada. No se puede borrar el formulario completo como huérfano sin revisar la edición y sus consumidores.
- `/admin/usuarios/create` existe y `UsuarioController::create` devuelve la vista independiente. Aunque no se muestre en el menú de Gerencia, no es una vista sin consumidores. No se ha validado funcionalmente su alta durante esta investigación.
- El modal de personal actual tiene cuatro pasos. La copia de siete pasos permite recuperar la referencia visual sin reintroducir sus reglas o campos históricos.
- Los modales de signos vitales y medicación tienen inclusiones y componentes registrados: estar cerrados no significa que sean inservibles.

## Estado y pendientes

Originales encontrados y preservados. Fuente vigente: instrucciones de la propietaria, AGENTS y flujo institucional; Git aporta evidencia histórica, no autorización funcional. Se verificó procedencia de archivos y rutas registradas con `route:list`; no se ejecutaron tests, build ni operaciones de datos porque no se cambió producto.

La depuración global sigue pendiente de clasificar cada consumidor y sus sustitutos vigentes. La referencia visual exacta que recuerda la propietaria sigue sin identificación definitiva entre estos candidatos. No se borraron vistas ni cambios previos.
