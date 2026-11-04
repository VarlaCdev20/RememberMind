# Formularios: composición compartida — 15/10/2026

## Petición y contrato

Mejorar los formularios siguiendo los últimos ajustes aprobados de tipografía, iconos, paleta cálida, glass suave y composición. Se usan remembermind-design-system, remembermind-clinical-form-ux, responsive-accessibility y QA visual. La mejora es de presentación; no cambia campos, validaciones, autorización, interpretación clínica ni persistencia.

## Implementado

- Secciones del componente form-section con títulos funcionales en Nunito Sans de 18 px y sin mayúsculas impuestas. Iconos de sección de 20 px; etiquetas de captura de 14 px y peso 700.
- Encabezados de captura con salvia suave sobre la superficie greige aprobada, relieve exterior existente y espaciado compartido. Se conservan los colores semánticos de mediciones y alertas.
- Barra común de historial/evolución diferenciada con fondo azul grisáceo leve, borde suave y botones juntos. Corrige el ancho completo heredado de «Ver evolución» en Conducta; mantiene las consultas laterales y su estado.
- Signos vitales: mediciones en grid adaptativo de ancho mínimo 260 px (limitado al contenedor), en lugar de tres columnas estrechas. El grid también se adapta cuando la ventana de evolución está abierta. Cards con padding 16 px y etiqueta legible; encabezado con soporte salvia e icono 24 px; «Ver gráfica» con texto 14 px, icono 20 px y área mínima de 44 px. No modifica valores ni estados.
- Opciones clínicas compartidas con tipografía consistente, manteniendo los colores propios de cada opción y sin cambios de geometría al seleccionar.
- Conducta: tinta seleccionada más oscura dentro de la misma familia cromática. La medición detectó contraste inferior a 4.5 en lavanda, cian y tierra; la corrección pasa la comprobación existente sin cambiar el fondo ni los estados.
- Cognición móvil: resumen más compacto, accesos cortos de una línea y primera respuesta visible antes del footer. Mantiene la consulta desplegable del contexto profesional y todos los campos.
- Se conserva la limpieza, confirmación de descarte, foco, botones de guardar y separación entre datos guardados y captura.

## Integración

Implementación en components/forms.css y los patterns compartidos care-surfaces.css / clinical-consultations.css, con ajustes responsables en Signos, Conducta y Cognición. No se crea otra familia de componentes ni se modifica backend/BDD. Se conserva el trabajo previo del árbol, incluidos archivos compartidos aún sin commit.

## Pruebas y QA

| Gate | Resultado |
|---|---|
| npm run build | PASS; advertencia existente de bundle mayor de 500 kB |
| npm test final | 194 PASS / 0 omitidas |
| PHP: OverlayComponents + captura Cognición + validación Conducta + presión incompleta Signos | 6 PASS / 52 assertions |
| Exportación actual de vistas sintéticas con Livewire | 1 PASS / 14 assertions |
| Doce formularios/consultas × cinco anchos, tema claro | 60 casos PASS |
| Doce formularios/consultas × dos anchos, tema oscuro | 24 casos PASS |
| Medicación: diez estados × cinco anchos | 50 casos PASS |
| Conducta: QA funcional/visual existente | PASS en cinco anchos |
| Cognición: QA funcional/visual existente | PASS en cinco anchos |
| git diff --check en los archivos modificados | PASS |

Anchos exactos: 1440, 1280, 1024, 768 y 390 CSS px; oscuro 1440/390. Navegador Chrome con movimiento reducido. Revisión visual de Signos, Conducta, Cognición móvil y formulario genérico. Se verificaron reflow, foco, consultas emergentes y conservación de captura en los harnesses existentes.

La fixture nueva de Heridas no contiene heridas activas: se comprueba el aviso de identificación inicial y la ausencia de un historial ficticio. El harness temporal distingue ese estado de una herida ya registrada; no se inventan filas para satisfacer la comprobación.

Evidencia: `storage/app/qa/formularios-composicion-2026-10-15/`. Son datos sintéticos y estilos/scripts del producto; el transporte Livewire se simula en QA visual. Las pruebas PHP verifican el comportamiento servidor indicado por separado. No se ha ejecutado la suite PHP completa ni una certificación clínica, WCAG o de producción para este lote visual.

## Git y pendientes

Sin commit ni push: los archivos compartidos contienen numerosos cambios previos mezclados. No se hizo stage. No hay cambios de esquema, permisos ni reglas clínicas. La aceptación cubre los formularios de Enfermería y los componentes compartidos indicados; no afirma revisión manual de todos los formularios internos de cada rol.
