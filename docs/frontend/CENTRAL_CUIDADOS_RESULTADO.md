# Centro de cuidados de Enfermería

Implementado el 13/10/2026 por solicitud de la propietaria. El sidebar conserva siete entradas: Mi turno, Mis residentes, Medicación, Alertas, Incidentes, Cuidados y Pase de turno. Cuidados abre una pantalla central; sus diez tipos ya no despliegan formularios en el menú.

## Contrato y alcance

- Entrada: `admin.enfermeria.cuidados` (`/admin/enfermeria/cuidados`).
- Resumen general: conteos y fecha del último registro por tipo en el período seleccionado. Los registros anulados se identifican por separado dentro de la historia. No son indicadores de gravedad ni de cumplimiento.
- Registros: veinte filas por página, ordenadas por fecha, con tipo, residente, profesional y estado. El detalle presenta una proyección explícita de los valores guardados.
- Filtros: tipo de cuidado, residente autorizado, fecha inicial y final. El período inicial abarca siete días naturales hasta hoy. Sueño conserva su fecha sin atribuir hora; heridas identificadas y curaciones conservan su grano independiente.
- Registrar: selector de tipo y residente que conduce a los formularios existentes. Cognición, conducta y sueño continúan en el seguimiento diario completo. El selector no guarda información clínica.

Fuentes vigentes: instrucción de la propietaria, baseline BDD y diccionario físico con extensiones aprobadas, baseline de roles y arquitectura híbrida. Los documentos históricos no gobiernan la nueva navegación. No se modifica estructura, catálogo, permisos ni interpretación clínica.

## Integración y seguridad

`ConsultaCentralCuidados` pertenece a Aplicacion/Consultas; Livewire maneja filtros y navegación en Presentacion. Se reutilizan Models y resúmenes operacionales de Eliminación y Movilidad. La relación mínima CuracionHerida → Herida permite resolver el residente sin depender del trabajo previo de fotografías.

Cada lectura exige cuenta activa, permiso general de entrada, rol clínico/supervisión autorizado, permiso específico y alcance actual de `TurnoEnfermeriaService`. Las fechas no alteran el alcance. Filtro y detalle rechazan residentes ajenos; el detalle abierto se reautoriza en cada render. Curaciones requieren lectura de heridas y de curaciones. Registrar comprueba además permiso del destino, personal, turno, competencia y asignación; los escritores existentes siguen siendo autoritativos. Superadministrador conserva lectura sin recibir registro clínico por su solo rol.

No se cargan galerías ni URLs de fotografías en la consulta general. Los datos de la página se proyectan y las relaciones de residente/profesional se cargan por lote.

## Verificación

- Rama aislada: PHPUnit, 49 pruebas / 3465 aserciones aprobadas (`CentralCuidadosTest`, `SidebarEnfermeroTest`, `SidebarServiceTest`, `MisResidentesNavegacionTest`, `FolderOrganizationTest`).
- Frontend: cuatro pruebas de sidebar aprobadas; siete accesos directos, adaptación, teclado, geometría y contraste compartido.
- `npm run build`: aprobado. Pint de clases nuevas y test: aprobado.
- Revisión independiente de solo lectura: sin hallazgos pendientes tras corregir contexto de curación, campos, nulabilidad, anulación, reautorización y acceso al destino.
- Interfaz local: resumen, lista paginada, detalle, selector de registro/cancelación y sidebar activo comprobados. Sin desbordamiento horizontal en 1440, 1280, 1024, 768 y 390 px. Controles de la pantalla central con altura mínima de 44 px y jerarquía tipográfica canónica.
- Pruebas usan SQLite `:memory:` y datos sintéticos. La integración PostgreSQL de esta consulta no se ejecutó; no se afirma equivalencia por lectura SQL ni por SQLite.

El commit excluye cambios previos de heridas/fotografías. No se ejecutan migraciones ni se modifica información clínica real. No se realiza push.
