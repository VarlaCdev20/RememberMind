# Informe cognitivo y funcional: adaptación de la referencia

La referencia visual compartida el 21/10/2026 corresponde a un **informe de un residente**, no al formulario de captura. Se aplica a las rutas de resultados de Psicología y Medicina que reutilizan `resultados-experto-residente.blade.php`. El contenido de Enfermería conserva su contrato propio. El sidebar, la barra superior, notificaciones y menú de usuario ya pertenecen al shell de RememberMind; no se replican ni se usa la marca, paciente o profesional de la imagen.

## Análisis de la composición

| Región de la imagen | Ubicación implementada | Comportamiento y fuente |
|---|---|---|
| Sidebar oscuro con navegación y marca | Shell compartido, fuera de la vista | Conserva módulos y permisos reales; no se copia «VidaPlena». |
| Barra superior con búsqueda, alertas y usuario | Shell compartido | No se duplican controles. |
| Migas, título y subtítulo | Inicio del informe | Psicología/ficha → evaluación cognitiva → nombre; título «Evaluación cognitiva y funcional». |
| Botones de la esquina superior derecha | Grupo de acciones junto al título, con reflujo bajo el título en ancho estrecho | «Ver evidencias» abre el drawer autorizado; «Ver historial» abre la evaluación histórica; «Revisión profesional» aparece solo si la evaluación conservada admite revisiones; «Consultar reglas» respeta Policy; «Volver» retorna al módulo de origen. |
| «Exportar informe» | No se muestra | No hay exportación clínica verificada para este contrato; se evita descargar un resultado incompleto o transitorio. |
| Retrato, nombre y metadatos del residente | Tarjeta horizontal de contexto | Avatar iconográfico cuando no hay foto autorizada; nombre, edad, fecha, versión, solicitante y estado desde la consulta. No se exponen códigos como dato visual principal. |
| Tarjeta dominante de resultado y aviso lateral | Tarjeta «Resultado cognitivo», inmediatamente después del residente | El patrón o abstención se lee en tipografía grande. La explicación breve, el primer motivo de abstención y la advertencia de alcance quedan visibles antes del detalle técnico. No se reproduce el DCL mostrado como ejemplo en la imagen. |
| Cinco casillas C01–C05 | Fila de cinco estados bajo la síntesis, que pasa a 3, 2 y 1 columnas | Muestra código, nombre y estado S/N/U/X reales. No convierte U en cumplimiento. C02 sigue derivado. |
| Radar de cinco ejes | Lista de cinco dominios en la columna izquierda | Las observaciones y medidas disponibles no forman una escala normalizada común: un radar o rango esperado sería engañoso. Cada dominio muestra A/P/I/U/X y abre sus soportes. |
| Confusores e IADL con barras | Columna derecha de criterios C01–C05 y panel «Contexto y autonomía» | Se muestran justificaciones y soportes existentes. No se crean barras, porcentajes ni «deterioro leve» a partir de NULL. |
| Cinco filas inferiores de dominios | «Desglose por dominios», acordeón | Cada fila abre conclusión, estado, componentes disponibles y acceso a evidencias. |
| Tabla de fundamento | Panel derecho inferior | Vista breve de fuente, evidencia, uso y fecha de la evaluación seleccionada; enlace al drawer de detalle. Ausencia de filas se explica. |
| Historial, razonamiento y explicaciones | Secciones siguientes del mismo informe | Mantienen selección histórica, grafo, reglas y fuentes con permisos actuales. |

## Estilo e interacción

La estructura reproduce tarjetas claras, separación estrecha, bordes finos, radios suaves y dos columnas en escritorio. Se usan tokens del Design System: superficies crema/neutras, sidebar capuchino ya existente, salvia para acciones, ámbar para advertencias y texto marrón/gris contrastado. Nunito Sans identifica títulos; Inter presenta lectura y datos. Los iconos de Phosphor son decorativos junto a etiquetas visibles. No se incorpora fotografía de stock ni una segunda paleta.

Los botones tienen altura mínima de 44 px, hover, foco visible y deshabilitado durante cargas de Livewire. Los acordeones nativos exponen expansión por teclado. El drawer conserva cierre, foco y controles del componente compartido. Las transiciones se reducen con `prefers-reduced-motion`. El contenido se reordena a una columna antes de comprimirse; la tabla permite desplazamiento horizontal local. El color nunca es el único indicador de estado.

La lectura comienza por el resultado actual, sigue con «Qué falta para clasificar» o «Cómo leer este resultado» y ofrece «Entender los criterios» como enlace interno a C01–C05. El análisis de fuentes y la evaluación histórica seleccionada aparecen después, identificados como consultas distintas. El acceso a criterios usa un enlace local, sin ampliar la lista de destinos permitidos por Livewire.

La imagen es estática: no prescribe una animación específica. Las puntuaciones de ejemplo, los instrumentos RAVLT/TMT-B/Stroop/Digit Span, las comparaciones percentilares, los indicadores funcionales 3/3 y el perfil DCL ilustrativo no son datos de RememberMind y no se incorporan. El resultado histórico y el cálculo Petersen al corte se etiquetan por separado para no confundir sus tiempos ni su conservación.
