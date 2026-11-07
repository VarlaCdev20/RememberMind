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
