# Familiares y contactos: red visual de apoyo

## Solicitud y contrato

La propietaria solicita mejorar una vista saturada y presentar los contactos como un árbol. Trabajo iniciado el 06/11 y cerrado el 07/11, America/La_Paz. Se conserva la identidad cálida, Nunito, superficies café grisáceo y acentos salvia.

Fuentes aplicadas: instrucciones vigentes; contrato de contactos y privacidad en las fichas Gerencia09/10 y su integración38; implementación `RedApoyoPanel`, `GestionarRedApoyo` y `ConservarVersionVinculo`; pruebas `GerenciaContactosTest` y `GerenciaAccesoFamiliarTest`. Las fichas de diseño conservan su estado/fecha y no aprueban nuevos permisos.

La fuente persistida relaciona contactos con residentes. La representación muestra exclusivamente esos vínculos y su parentesco registrado; no deduce parentescos entre contactos ni generaciones familiares. No se amplían tablas, relaciones, campos, catálogos o permisos.

## Recorrido y presentación

1. Buscar por nombre y seleccionar un residente admitido. No se selecciona ni crea un residente como sustituto de una elección.
2. Consultar «Red de apoyo» por defecto: residente como nodo central, contactos en tarjetas conectadas, parentesco, teléfono y funciones registradas. Los activos aparecen primero, priorizando responsable principal y contacto de emergencia; los inactivos permanecen visibles y señalados.
3. Abrir «Gestionar contacto y vínculo» cuando el actor tenga autorización. Se reutiliza exactamente el mismo parcial de acciones en tarjetas y tabla; los formularios y mecanismos de corrección/vigencia siguen siendo los existentes.
4. Alternar a «Tabla» para comparar. «Por persona» mantiene el directorio y las operaciones de cuenta separadas. Las tablas principales se convierten en fichas en móvil sin alterar las tablas de historia de los modales.

Se reúnen búsqueda y selector en una barra, evitando repetir el selector dentro del panel. La red usa una lista semántica, encabezados, iniciales e indicadores textuales; las líneas son decoración de los vínculos existentes. No se incorporan librerías de grafos ni imágenes externas. Los estados vacíos distinguen selección pendiente y residente sin vínculos.

## Seguridad e integridad

Las consultas, Actions, Policies, rutas y almacenamiento no cambian. La lectura del residente mantiene su autorización y las acciones siguen protegidas en backend. Las condiciones `@can` y el bloqueo de corrección/vigencia sin historia se reutilizan en el parcial compartido.

El correo de contacto se muestra en la red solo con `contactos.ver`. El lector que únicamente consulta vínculos no recibe ese dato adicional ni controles de gestión. La cuenta conserva la frontera de Gerencia/Superadministración más `contactos.gestionar`: un lector sin ese alcance ve «Acceso de cuenta restringido», sin confundir datos restringidos con ausencia de cuenta.

Contacto, vínculo y cuenta mantienen estados distintos. Información/salidas se describen como autorizaciones registradas, sin conceder acceso clínico o autorizar una salida. Consultar y cambiar de presentación no crea cuentas, contactos, vínculos ni avisos; no envía correos ni modifica historia.

## Verificación y límites

- Grupo funcional y de seguridad: 16 pruebas / 81 aserciones aprobadas (`GerenciaContactosTest|GerenciaAccesoFamiliarTest`), incluyendo lector sin `contactos.ver`, privacidad de cuentas/vínculos ajenos y denegación de mutaciones sin historia. Prueba adicional de cuenta restringida: 1 prueba / 4 aserciones aprobadas. Total localizado: **17 pruebas / 85 aserciones**.
- E2E de cierre aprobado en 1440/1280/1024/768/390px: tres vínculos sintéticos con parentescos distintos, responsable/emergencia, relación inactiva, residente sin vínculos, alternancia red/tabla, apertura y cancelación de edición, directorio por persona. Comprueba ausencia de errores JavaScript, desbordamiento horizontal y filas cuyo contenido sobrepase su altura.
- Capturas de red en los cinco tamaños inspeccionadas; tabla/directorio y modal revisados en móvil. Se corrigió la altura heredada de filas móviles y se limitó esa adaptación a las dos tablas principales, sin convertir las tablas históricas de los modales.
- Build Vite aprobado. QA visual/funcional acotado: PASS para el recorrido descrito. Revisión de autorización acotada sin hallazgos pendientes en las nuevas superficies. No se ejecutó CI completa, PostgreSQL ni una auditoría global de accesibilidad para este cambio de presentación.

Evidencia: `output/red-apoyo-visual-seguridad-20261107.txt`, `output/red-apoyo-visual-cuenta-restringida-20261107.txt`, `output/red-apoyo-visual-build-20261107.txt`, `output/red-apoyo-visual-e2e-20261107.txt`; capturas sintéticas en `output/red-apoyo-visual-20261106/` (directorio conservado desde el inicio de la tarea).

El router privado de pruebas solo incorpora una inclusión condicionada de la nueva muestra, manteniendo sus guardas de entorno, token y base desechable. Sus cambios previos de otras tareas quedan fuera de este commit. No se añade compatibilidad V1 ni un módulo paralelo. El árbol no representa una genealogía multigeneracional que la fuente actual no conserva.

## Ajuste del acceso y densidad — 07/11/2026

Se destaca «Ver red de apoyo» en cada tarjeta con el estilo compartido de botón secundario salvia y una superficie de al menos 44px, conservando un único enlace semántico por tarjeta. La identidad y el acceso se distribuyen horizontalmente. Dentro de la red se añade «Cambiar residente» junto a la acción existente de vinculación y se reducen los espacios del nodo central, conectores y tarjetas. No cambian datos, consultas, permisos ni persistencia.

Verificación del ajuste: build Vite aprobado; E2E aprobado en 1440/1280/1024/768/390px, incluyendo regreso a la lista, reapertura del residente, acceso táctil, red/tabla, edición cancelada y estados vacíos. Inspección visual de las capturas de escritorio y móvil sin superposición o desbordamiento observado. Evidencia: `output/red-apoyo-acceso-build-20261107.txt`, `output/red-apoyo-acceso-e2e-20261107.txt` y capturas sintéticas en `output/gerencia-residentes-conexion-20261107/`. No se ejecutó CI completa ni PostgreSQL para este ajuste de presentación y navegación.

## Tabla menos saturada — 07/11/2026

Se reduce la saturación de las tablas «Por residente» y «Por persona» reutilizando `rm-table`, `status-badge`, botones existentes y el patrón desplegable nativo de gestión de la red. Cada fila presenta «Gestionar» y revela las mismas acciones autorizadas dentro de su propia altura, evitando menús flotantes recortados y filas llenas de botones. Se equilibran anchos, jerarquía del nombre/teléfono y separación de filas. Las autorizaciones se expresan brevemente por fila y conservan una aclaración común sobre permisos y proceso de salida. No se incorporan dependencias, paleta, permisos o cambios de persistencia.

La prueba detectó la reducción global de controles de `rm-depth-canvas` a 40px; se conserva una excepción acotada para que los botones de estos desplegables alcancen 44px. El resto del sistema mantiene su tamaño existente. En móvil se conserva la conversión de filas en fichas con etiquetas.

Pruebas funcionales: **18 aprobadas / 94 aserciones** (`GerenciaContactosTest|GerenciaAccesoFamiliarTest`, SQLite desechable). Build Vite aprobado. E2E de red, tabla y personas aprobado en 1440/1280/1024/768/390px, con comprobación adicional de acciones recogidas/desplegadas, altura táctil y ausencia de superposición en 1440/390px. Revisión visual acotada de escritorio/móvil: PASS. Evidencia: `output/red-apoyo-tabla-feature-20261107.txt`, `output/red-apoyo-tabla-build-20261107.txt`, `output/red-apoyo-tabla-e2e-20261107.txt`; capturas sintéticas en `output/gerencia-residentes-conexion-20261107/`. No se ejecutó PostgreSQL ni CI completa para este ajuste de presentación; no se añade legacy ejecutable.

## Panel con un contacto — 07/11/2026

A partir de la captura de la propietaria se corrige el caso de una sola relación: la tarjeta ya no ocupa todo el ancho ni mantiene el marco lateral de ramas. Se centra con ancho máximo de 560px y una conexión vertical corta de 24px bajo el residente. Se distribuyen los canales de contacto según espacio disponible y se conserva el acceso desplegable a las acciones. La red con varios contactos, la tabla, permisos, contenido y persistencia mantienen sus contratos existentes; se reutilizan componentes y tokens salvia/café grisáceo/Nunito.

Se amplía la muestra sintética protegida con un residente que tiene exactamente un vínculo, sin crear residentes o relaciones en la base operativa. El E2E verifica en cinco anchos que esa tarjeta permanece centrada, acotada y sin desbordamiento, además de los recorridos existentes de red múltiple, tabla, gestión, personas y red vacía.

Verificación del panel: build aprobado; E2E aprobado en 1440/1280/1024/768/390px. Capturas del caso de un contacto inspeccionadas en escritorio y móvil: PASS acotado al cambio. Evidencia: `output/red-apoyo-un-contacto-build-20261107.txt`, `output/red-apoyo-un-contacto-e2e-20261107.txt` y `output/gerencia-residentes-conexion-20261107/red-un-contacto-*.png`. No se ejecutó CI completa ni PostgreSQL para este ajuste exclusivamente visual.
