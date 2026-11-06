# Documentación compacta y formal — 06/11/2026

La propietaria sustituyó la indicación de carpetas de colores por archivos más pequeños, neutros y formales. Se conservan las presentaciones Tabla / Carpetas y sus acciones.

Se elimina la asignación decorativa de colores por tipo; el tipo sigue escrito en la pestaña. Las carpetas utilizan una única superficie neutra y borde compartido. En escritorio caben cuatro por fila, con pestaña de25px, miniatura48×62px, título15px, metadata12px, padding13px y espacios reducidos. Los distintivos de las carpetas también son neutros y conservan los nombres de sus estados. Los controles mantienen44px para interacción accesible. La tabla y sus colores semánticos existentes no se alteran.

Cambios limitados a Blade de presentación, CSS y las aserciones del E2E (fondo uniforme y tamaño compacto). Sin backend, BDD ni permisos. Build Vite aprobado; E2E en1440,1280,1024,768 y390px comprueba tabla/carpetas, miniaturas privadas, filtros y retorno de foco. Inspección visual de carpetas de escritorio y móvil. Evidencia: output/documentacion-compacta-*-20261106.txt y output/documentacion-carpetas-20261106/. No se repiten suites de dominio porque no se modifican sus operaciones. No se ejecuta CI completa.
