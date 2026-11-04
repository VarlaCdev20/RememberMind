# Nueva herida — rediseño V2

## Alcance y resultado

Implementado el rediseño del modal «Registrar nueva herida» en el módulo Cuidados. Comparte el componente `x-ui.quick-register-modal` con Signos vitales y conserva los servicios, reglas, autorización y almacenamiento privado existentes. No se añadieron migraciones en esta etapa.

Dictamen de aceptación integral: **FAIL por evidencia incompleta**, no por un defecto observado en los criterios visuales revisados. La presentación y las pruebas automatizadas descritas abajo aprobaron; falta verificar el recorrido HTTP completo con una sesión real de enfermería y una fotografía de 8 MB. La sesión disponible en el navegador pertenece a supervisión y no ofrece Registrar en el directorio. No se cambiaron roles ni asignaciones para obtener cobertura.

## Antes, implementación y decisiones

La captura anterior presentaba campos verticales extensos, contexto demasiado grande y un selector de archivo nativo visible. La nueva composición utiliza cabecera compacta, contexto clínico separado, dos columnas en escritorio, observación a todo el ancho y carga fotográfica horizontal. El archivo nativo queda visualmente recortado y conserva etiqueta, foco y acceso por teclado.

El modal nuevo mide 1040 px en escritorio; la variante de curación utiliza 1160 px para mantener la diferencia de anchura solicitada. En ambas se limita la altura y se desplaza el cuerpo, conservando las acciones visibles. El ajuste de anchura de curación no cambia sus campos ni su persistencia.

Tipo y clasificación siguen siendo texto según el contrato vigente (`string|max:60`). No existe un catálogo aprobado para convertirlos en selectores. Se conservan los nombres, límites y reglas originales, con etiquetas, iconos y ayudas visibles. La ubicación acepta 120 caracteres y la observación 1000.

El profesional procede de la sesión autenticada, la ubicación del residente de su relación real y el reloj del servidor. El éxito muestra el resultado devuelto tras persistir, permanece abierto y permite consultar detalles, volver al residente o preparar la primera curación cuando existe permiso. Preparar una curación no escribe un registro clínico.

La fotografía utiliza el transporte Livewire existente: progreso recibido del transporte, error, reintento, retirada y reemplazo. Las miniaturas aparecen después de la aceptación del lote; no anticipan un registro persistido. Cancelar, volver, cerrar y Escape conservan la confirmación de descarte. Las descripciones y selecciones fotográficas también activan el estado de cambios pendientes.

## Archivos de esta etapa

- `app/Modulos/Cuidados/Presentacion/Livewire/Paginas/RegistrosEnfermeria.php`: resultado persistido, etiquetas de validación y preparación autorizada de primera curación.
- `app/Modulos/Cuidados/Presentacion/Livewire/Paginas/MisPacientes.php` y `app/Modulos/Clinica/Presentacion/Livewire/Paginas/FichaPaciente.php`: retorno al residente.
- `app/Modulos/Clinica/Presentacion/Vistas/livewire/cuidados/ficha-paciente.blade.php`: coordinación del cierre del modal hijo.
- `app/Modulos/Cuidados/Presentacion/Vistas/livewire/cuidados/partials/heridas-curaciones.blade.php`: composición, contexto, captura inicial, éxito y acciones.
- `app/Modulos/Cuidados/Presentacion/Vistas/livewire/cuidados/partials/fotografias-heridas-upload.blade.php`: selección accesible y tarjetas fotográficas.
- `resources/frontend/scripts/modules/wound-evidence.js`: éxito abierto y acciones posteriores.
- `resources/frontend/styles/design-system/patterns/wounds.css` e `index.css`: patrón de heridas e importación. Las reglas de heridas se separaron de `clinical-forms.css`.
- `tests/Feature/FotografiasHeridasTest.php` y `tests/Frontend/wound-evidence.test.js`: cobertura de los nuevos comportamientos.

## Matriz visual

Tema claro, contexto de estilos de enfermería, HTML real renderizado mediante Livewire/PHPUnit con datos sintéticos y SQLite en memoria. CSS compilado y módulos JavaScript reales. La conexión de persistencia se reemplazó únicamente en las páginas desechables de QA, con `connect-src 'none'` y `form-action 'none'`. Se retiraron esas páginas públicas al terminar.

Se utilizó un iframe con viewport CSS exacto porque el control de viewport del navegador integrado no modificaba su tamaño efectivo. Las capturas muestran una representación escalada para caber en el panel; no son exportaciones nativas de 1440 píxeles. Las mediciones proceden del documento interior.

| Viewport CSS | Captura inicial | Miniaturas | Desbordamiento horizontal | Resultado visual |
|---|---|---|---|---|
| 1440 × 900 | 2 columnas | 4 columnas | No | PASS |
| 1280 × 800 | 2 columnas | 4 columnas | No | PASS |
| 1024 × 768 | 2 columnas | 3 columnas | No | PASS |
| 768 × 1024 | 1 columna | 2 columnas | No | PASS |
| 390 × 844 | 1 columna | 2 columnas | No | PASS |

Cabecera, contexto, grid, fotografías y pie: PASS visual en esta superficie sintética. Cabecera de escritorio de 70 px; en móvil crece para conservar el texto. Botón principal con texto claro sobre el token bosque existente: contraste medido **5,72:1**. Fotografías en proporción 4:3 y controles de retirada de 44 px. No se observaron los literales `null`, `NaN`, `undefined`, `Infinity` ni `[object Object]` en las inspecciones de texto realizadas.

| Criterio/estado | Evidencia | Resultado |
|---|---|---|
| Etiquetas y campo obligatorio | DOM y atributos del formulario | PASS |
| Foco inicial y ciclo Tab | Campo de tipo; 16 pasos dentro del diálogo con retorno a la cabecera | PASS |
| Archivo accesible | Input recortado a 1 × 1 px; recorrido Tab y apertura del selector múltiple desde botón propio | PASS |
| Archivo nativo visible | Capturas y `clip-path: inset(50%)` | NO |
| Cancelar, Escape, X y Volver con cambios | Confirmación; seguir editando conserva el valor; descartar limpia y cierra | PASS |
| Retirar imagen y editar descripción | Tres tarjetas pasan a dos; regreso activa confirmación | PASS en fixture |
| Error de validación | Respuesta real de Livewire: ubicación obligatoria, tipo conservado | PASS |
| Fallo/reintento de carga | Transporte simulado con fallo, archivo sintético; se mantiene aviso y opción de quitar | PASS del estado visual |
| Éxito y detalles | HTML de resultado persistido en SQLite de prueba, diálogo abierto y detalles desplegables | PASS |
| Progreso HTTP real y bloqueo durante carga | Pruebas de lógica frontend; sin sesión clínica de navegador | Pendiente de aceptación E2E |
| Recorrido de directorio/ficha con enfermería | Pruebas Livewire; sesión de navegador en supervisión | Pendiente de aceptación E2E |
| Transporte HTTP de imagen de 8 MB | No comprobado contra la configuración PHP del servidor | Pendiente |
| Warning/high/critical y gráficos clínicos | No forman parte de la identificación inicial de herida | N/A |

## Verificación automatizada

- `php artisan test --compact tests/Feature/FotografiasHeridasTest.php tests/Feature/MisResidentesNavegacionTest.php`: **55 PASS, 324 aserciones**.
- Suite frontend: **164 PASS, 0 FAIL**. Incluye conservación del modal después de guardar y transición a primera curación tras respuesta del servidor.
- Exportación visual aislada: **1 PASS, 7 aserciones**; creación, validación, resultado y preparación de primera curación en SQLite de prueba.
- `npm run build`: **PASS**, última compilación 2,57 s.
- Pint sobre los cuatro archivos PHP afectados: **PASS**.
- `git diff --check`: **PASS**.

La primera corrida amplia detectó una expectativa incorrecta de una prueba nueva que montaba la sección general en vez de Heridas. Se corrigió el contexto del test y se repitió el grupo completo de fotografías/navegación con los resultados indicados. Las pruebas acreditan creación, relaciones de fotografías, acceso privado y autorización a nivel de servidor; no sustituyen el transporte HTTP real.

Las capturas y logs se conservan en `C:/Users/CARLAENCINAS/Documents/Codex/2026-10-10/files-pasted-by-the-user-prompt/outputs/`: `nueva-herida-final.jpg`, `nueva-herida-fotografias.jpg`, `nueva-herida-fotografias-390.jpg`, `nueva-herida-success.jpg`, `nueva-herida-error.jpg`, cinco capturas de viewport, mediciones JSON y logs de pruebas/build/Pint.

## Entrega

Repositorio: `C:/Users/CARLAENCINAS/Desktop/RememberMind`. Rama conservada: `feature/institucion-asignaciones-planificadas`; HEAD `190f6ad7`. Los cambios anteriores del árbol de trabajo se preservaron.

**Commit: NONE. Push: NONE. STOP.** No se inició otro formulario. Para aceptación integral queda el recorrido con enfermería autorizada, incluido el transporte de 8 MB y los estados de carga reales.
