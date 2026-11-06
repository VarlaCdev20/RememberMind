# Documentación: tabla de Personal y carpetas — 06/11/2026

La propietaria pidió reutilizar el diseño de la tabla de Personal, añadir miniaturas laterales y poder alternar entre Tabla y Carpetas, con aspecto de carpetas físicas y colores suaves. Se integra la biblioteca de archivos que ya estaba trabajada localmente; no se rehace su consulta ni se amplían sus permisos.

## Resultado

- Selector compartido `collection-view-switch`: Tabla / Carpetas. Carpetas es la presentación inicial. La selección se mantiene durante la interacción y el filtrado; no representa un estado documental ni escribe en la BDD.
- Tabla con las clases y superficie de Personal: documento y miniatura, trabajador, estado, fecha de carga y acciones. Se reutiliza el paginador compacto del sistema con el paginador nombrado `archivosPage`. En ancho reducido las filas pasan a fichas para evitar columnas ilegibles.
- Carpetas con pestaña, borde y relieve discretos. Azul para identidad, salvia para trayectoria, lavanda para formación, rosa para domicilio y tono neutro para antecedentes. Se usan tokens existentes; no se crean catálogos ni reglas de significado por color. Cada carpeta representa un documento, no un expediente físico nuevo ni una relación SQL.
- Miniatura lateral real para JPG/PNG/WebP a través de la ruta privada existente, con carga diferida y alternativa ante imagen no disponible. PDF muestra un distintivo de formato sobre una hoja; no se afirma que sea una imagen de su primera página. El original se consulta en el visor autorizado existente.
- Los distintivos de estado conservan texto y semántica independiente del color de carpeta. Cargado no significa Validado. Anulados/reemplazados conservan su historia. Los tipos opcionales ausentes no se convierten en atrasos.
- Acciones comunes Ver archivo y Abrir original. El cierre de la vista previa devuelve el foco al botón visible de la presentación activa. Nunito y tamaños compartidos con el sistema.

## Reutilización y límites

Se conectan el lector y panel de Documentación ya presentes en el espacio de trabajo, su vista previa, filtros por persona/rol/formato/estado y pestaña Solicitudes y entregas. Se incluye su cobertura relacionada para que la entrega no dependa de archivos sin versionar. La prueba previa de biblioteca se adapta a Carpetas; los hooks de datos sintéticos quedan exclusivamente en el servidor de pruebas desechable.

No se cambian Policy, ruta de archivos privados, remitente de correo, estructura BDD, permisos, tareas clínicas ni el motor experto. Se conserva la separación entre documentos laborales y residentes/contactos/preadmisiones. La Policy y el controlador revalidan permiso/estado/alcance al servir cada original, también cuando se usa como miniatura.

El archivo local anterior `documentacion-personal-archivos.blade.php` deja de ser el include de la pantalla; se conserva fuera del commit como trabajo previo, sin borrarlo. La nueva colección compartida utiliza miniatura y acciones extraídas, evitando duplicar controles entre las presentaciones. Se excluyen otros cambios de Sidebar, avisos, mapas, Personal, estilos globales y sistema experto.

## Verificación

- Suite de archivos: SQLite y PostgreSQL desechable, **9 pruebas / 110 aserciones aprobadas** por motor.
- Integración con solicitudes documentales: SQLite y PostgreSQL desechable: **28 pruebas / 271 aserciones aprobadas en cada motor**.
- E2E de ambas presentaciones con cuatro documentos sintéticos y miniatura PNG real: aprobado en 1440,1280,1024,768 y390px. Comprueba cambio de presentación, tabla compartida, colores por tipo, ausencia de overflow, búsqueda y limpieza, imágenes autorizadas, apertura/cierre de visor y foco al control visible. Cero errores JavaScript.
- E2E previo adaptado de filtros y vista previa: aprobado en cinco anchos, incluidos filtros de persona, estado y formato, y acceso a Solicitudes. Cero errores JavaScript.
- Build Vite aprobado. Inspección visual de escritorio y móvil con la misma paleta y tabla de Personal. No se afirma fidelidad de contenido de primera página PDF ni auditoría global de accesibilidad. No se ejecutó CI completa.

Evidencias: `output/documentacion-carpetas-*20261106*`, `output/documentacion-carpetas-20261106/`. Solo datos sintéticos en las pruebas; no se modificaron documentos institucionales ni se enviaron correos reales.
