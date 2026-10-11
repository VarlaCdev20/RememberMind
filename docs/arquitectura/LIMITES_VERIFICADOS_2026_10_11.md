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

Continuación: [desacoplamiento de fronteras](DESACOPLAMIENTO_2026_10_11.md). Contexto autenticado/GET, estado de previsualización, archivos, correo, PDF, enlaces y adaptadores expertos se resuelven por contratos. Aplicación ya no consulta directamente request/session: turno usa contexto y Presentación añade parámetros a la paginación. Las fichas y paneles de alertas comparten consultas preparadas con reautorización después de hidratación. Un gate AST comprueba las dependencias exteriores auditadas.

Límites: estos controles detectan contratos concretos, no toda dependencia dinámica ni toda carga diferida Eloquent mediante propiedades. Continúan cálculos y presentación heredados en fichas grandes; no se afirma pureza absoluta de todas las vistas. Eloquent y capacidades Laravel en Aplicación conservan la excepción del ADR; el tipo Symfony UploadedFile de gestión de usuarios permanece. Archivos/DB no tienen atomicidad universal y algunos hashes permiten false. La integración/validación clínica del experto no queda aprobada por los tests técnicos.
