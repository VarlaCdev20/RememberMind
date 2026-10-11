# Límites de capas verificados — 2026-10-11

Administración y operación conservan las cuatro capas en doce propietarios, con modelos Eloquent centrales y providers Laravel como excepciones explícitas del ADR. Aplicación puede usar Eloquent en el corte pragmático actual; Dominio sigue puro.

Se extrajeron las consultas explícitas auditadas de Blade:
- Checklist/avance documental preparado en UsuariosPanel sin doble consulta.
- Cuidados/pase/conteos preparados por ConsultarCuidadosFicha según pestaña; conserva límites y estados previos, reautoriza actor activo y alcance vigente.
- Encabezado con componente de clase y proyección de persona/rol antes del render. La vista preparada no consulta la base en la regresión ejecutada.

El motor experto conserva sus fronteras:
- MotorExperto y EvaluadorExperto permanecen independientes de Laravel.
- DemostracionCOGMEM vive en adaptadores/entrada: compone fixtures/adaptadores y consume Aplicación. No hay wrapper interior hacia el adaptador.
- Consulta administrativa Laravel delega persistencia a Aplicación y reautoriza consultar mediante la Policy existente.
- FuenteConocimientoOrion es puerto del módulo, ArchivoConocimientoOrion su adaptador JSON; el provider compone la dependencia y el caso de uso no conoce rutas físicas.

Controles automáticos: relaciones Eloquent tipadas detectadas desde los modelos no pueden invocarse como métodos en Blade; núcleo experto no importa adaptadores/Laravel/Models; se mantienen los gates previos de namespaces/dirección/transacciones. La demostración ejecuta cinco casos técnicos sin bootstrap de Laravel.

Límites: estos controles detectan contratos concretos, no toda dependencia dinámica ni toda carga diferida Eloquent mediante propiedades. Continúan cálculos y presentación heredados en fichas grandes; no se afirma pureza absoluta de todas las vistas. Aplicación aún contiene acoplamientos a request/session en consultas de turno/exploración; deben refactorizarse por flujo con sus permisos y estados, sin sustituir el comportamiento por una regla inventada. La integración/validación clínica del experto no queda aprobada por los tests técnicos.
