# Medicación: imagen, distribución y acciones

## UX FLOW — revisión solicitada el 14/10/2026

**Objetivo:** identificar la dosis y consultar la imagen asociada al medicamento, documentar el resultado y continuar con la siguiente dosis sin perder contexto.

**Usuario y entrada:** Enfermería autorizada, residente asignado y jornada vigente; Mis residentes → Registrar → Administración de medicación.

**Antes:** icono pequeño en lugar de una zona para imagen; consultas al final; campos de administración dentro de cajas repetidas; contexto móvil demasiado alto; botones secundarios con peso parecido al principal; confirmación vacía después de guardar.

**Flujo:** (1) contexto breve y consultas visibles; (2) dosis exacta; (3) medicamento con imagen y datos esenciales, prescripción ampliada bajo demanda; (4) resultado y campos correspondientes; (5) confirmación del servidor; (6) elegir otra dosis o salir. En móvil el orden del medicamento precede a la captura.

**Excepciones:** fotografía ausente o fallida, ninguna dosis, falta de permiso, validación, transporte, descarte y registro confirmado. Las consultas y la ampliación de imagen no se convierten en cambios de captura. Los filtros tampoco deben activar falsamente el aviso de pérdida de datos.

| Información | Fuente | Prioridad | Móvil |
|---|---|---|---|
| Residente/cama | Relaciones autorizadas | Permanente | Identidad visible |
| Profesional/jornada | Sesión y asignación | Contexto | Resumen desplegable |
| Dosis/resultado | Agenda y captura explícita | Principal | Orden vertical |
| Foto del medicamento | Archivo institucional asociado al código | Apoyo visual | Imagen contenida, sin recorte |
| Prescripción detallada | Orden del mismo residente | Consulta | Modal existente |
| Historial/evolución | Eventos persistidos | Consulta frecuente | Barra visible |

## Dirección y componentes

Superficies neutrales cálidas y acentos existentes del Design System. Una acción principal verde; cancelación discreta; limpieza secundaria; consultas con icono y texto, de 44 px o más. Relieve y estados hover/focus/press del botón compartido. Sin una paleta nueva, sin cambiar botones de otros formularios.

Se conserva el reparto 60/40 en escritorio, reduciendo cajas anidadas y trasladando datos secundarios de la orden a disclosure. En móvil: agenda → medicamento/imagen → resultado → seguridad → resumen. El contexto se compacta sin truncar la identidad; el pie mantiene la acción principal diferenciada.

Componentes reutilizados: `ui.action-button`, `ui.modal-livewire`, `ui.form-section`, `ui.field` y ventanas compartidas. La presentación de imagen se comparte con la ficha existente del medicamento para retirar el lookup de archivos de Blade y sus nombres de medicamentos ficticios.

## Contrato de imagen

La tabla congelada `medicamentos` no contiene fotografía. La ficha existente ya utiliza `storage/imagenes/medicamentos/{cod_medicamento}.webp`. Se reutiliza esa convención en el disco público y se admiten también PNG/JPG/JPEG, comprobando el formato real. No se agregan tablas ni columnas, búsquedas externas, uploads ni asociaciones inventadas.

El directorio inspeccionado no tiene fotografías de medicamentos. Si falta una foto, la interfaz muestra una ilustración farmacéutica genérica identificada expresamente como tal, sin atribuirle marca o producto. Una fotografía presente se puede ampliar en modo consulta; no sustituye la comprobación del envase y la orden.

**Referencia:** el adjunto anterior solo contenía texto. Esta revisión responde a la corrección explícita del propietario, sin afirmar fidelidad a una imagen de diseño no disponible.

## Verificación

**PASS del alcance solicitado**, con fotografías sintéticas en pruebas y ausencia real de fotos en el catálogo inspeccionado. No equivale a una certificación clínica ni a validar la identidad física de un producto.

- **61 pruebas PASS:** 741 aserciones en el directorio principal y 731 en la rama aislada, cuya revisión de arquitectura incluye menos archivos previos. Cubren formulario, permisos, ficha de catálogo, autoría, omisión, consultas, integración con Mis residentes y arquitectura. Se prueba foto existente/ausente/archivo inválido, protección del código de archivo y conservación de captura al ampliar/cerrar.
- **PostgreSQL: 41 pruebas ejecutadas / 289 aserciones, PASS.** El reporte contiene 42 entradas: una se omite expresamente porque es la prueba HTTP con copia SQLite, ejecutada por separado.
- **Livewire HTTP real: 1 prueba / 9 aserciones del servidor, PASS**, más comprobaciones del navegador: foto cargada, ampliar/cerrar, hora actual, cancelar limpieza, conservar dosis, guardar, historial/gráfica simultáneos, continuar sin repetir escritura y proteger la salida móvil. Se verificó una sola administración, con dosis 49.125 y autor propios, y una sola auditoría. Usa una copia de fixtures en un directorio temporal propio y servidor en loopback; no accede a datos operativos.
- **JavaScript de medicación: 5 pruebas PASS.** Incluye foco sobre el campo inválido visible antes de una alerta oculta, transporte fallido, doble envío y visibilidad de la confirmación del servidor.
- **Visual: 50 combinaciones de estado/ancho en modo claro**, más capturas de ambas consultas y de la imagen; 8 combinaciones representativas en oscuro, en 1440 y 390 px. Se comprueban orden de lectura/tabulación, consultas visibles, espacio del cuerpo, ausencia de scroll horizontal, foco móvil y ventanas independientes. Sin errores JavaScript.
- **Contraste compuesto medido:** botón primario activo claro 4,76:1; campos y consultas aproximadamente 9:1. Las muestras oscuras superan 10:1. Se incluye el highlight del botón en el cálculo; no se afirma conformidad de todos los colores del sistema.
- **Build principal y de la rama: PASS.** El bundle principal mantiene su aviso de tamaño mayor de 500 kB.
- **Suite frontend general: 188/189 PASS.** Permanece el fallo previo de consistencia de superficies del dashboard de Enfermería (`shell-visual-consistency.test.js:131`), ajeno a esta revisión y también registrado antes de ella.

Evidencia local fuera de Git: `C:/Users/CARLAENCINAS/.codex/medicacion-imagen-20261014/`. Logs `tests-final.log`, `postgres.log`, `browser.log`, `visual-final.log`, `visual-dark.log`, `frontend.log` y `build.log`; capturas/JSON en `qa/evidence`, `qa/evidence-dark` y `qa/browser`. Las primeras son estados renderizados de Livewire con conexión de UI sintética; `qa/browser` procede de clics y solicitudes HTTP reales.

## Implementación y límites de la entrega

Se añadieron `Medicacion/Infraestructura/Archivos/ImagenMedicamento`, el componente `ui.medication-image`, su CSS compartido y los helpers de navegador aislado. Se modificaron el DTO de la orden, modal/consultas de administración, ficha anterior del medicamento, patrón CSS, módulo Alpine y pruebas.

El botón maestro incorpora el token `--rm-button-primary-highlight` con el valor anterior de 8% como default, por lo que los demás consumidores conservan su apariencia. Medicación usa 5% para corregir el contraste observado. Los botones siguen utilizando el componente y los estados compartidos.

Se eliminaron los datos de ejemplo que podían sustituir un medicamento ausente y se corrigió la presentación de “control especial” en la ficha. El acceso de Enfermería a esa ficha requiere una prescripción del mismo medicamento en su ámbito de lectura autorizado; el permiso independiente de catálogo conserva su alcance existente. El payload es de solo lectura y queda bloqueado frente a modificaciones del cliente. No se cambian permisos concedidos, reglas médicas, tablas ni columnas.

Una foto presente se sirve desde la convención institucional ya existente, sin búsquedas remotas. **Pendiente de contenido:** cargar fotografías reales del catálogo para sustituir la ilustración en los medicamentos que todavía no tienen archivo asociado. No se generan imágenes que aparenten ser un producto real ni se escriben datos de demostración en la instalación operativa.

La rama conserva los cambios previos y agrega un commit local de esta revisión. No se realiza push ni despliegue.
