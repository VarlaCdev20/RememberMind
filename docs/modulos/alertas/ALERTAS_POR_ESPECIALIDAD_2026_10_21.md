# Alertas por especialidad — entrega local del 21/10/2026

Continuación y cierre técnico: 22/10/2026. La revisión visual permanece pendiente por la limitación de navegador aceptada por la propietaria.

La continuación que diferencia composición, consultas profesionales, grafos por área y campos bloqueados se documenta en [Flujos diferenciados del 22/10/2026](FLUJOS_DIFERENCIADOS_2026_10_22.md).

## Implementado

La ruta `/admin/alertas-clinicas/por-rol` compara los diez roles institucionales sin cargar residentes. Administración y Superadministración pueden consultar esta configuración; solo Superadministración puede abrir la previsualización existente. La comparación no cambia la identidad ni concede permisos profesionales.

El selector de tarjetas muestra el rol activo, su enfoque y sus campos. En escritorio se organiza junto al contenido; debajo de 1024 px pasa a dos columnas sobre el detalle y, al seleccionar un rol, dirige el foco al contenido. Los campos pasan a una columna en móvil. Usa tokens, botones, formularios y superficies del Design System; no añade paletas por rol, librerías ni desplegables nativos.

Los formularios reales y la comparación comparten `CamposRegistroEspecialidad`. Los grupos organizan campos existentes; no crean escalas, diagnósticos ni obligaciones clínicas.

| Rol profesional | Registro existente | Organización del formulario |
|---|---|---|
| Médico/Geriatra | `notas_clinicas` | Tipo de nota y hallazgos, valoración y conducta; ambos obligatorios según el registrador vigente. |
| Psicología | `valoraciones_psicologicas` | Ánimo/afectividad; conducta/relación; conclusión/continuidad. |
| Nutrición | `valoraciones_nutricionales` | Alimentación/estado nutricional; apoyo/hidratación/restricciones; conclusión/continuidad. Requerimiento hídrico en **mL por día**, unidad confirmada por la propietaria. |
| Fisioterapia | `valoraciones_funcionales` | Movilidad/capacidad; autonomía diaria; conclusión/continuidad. |
| Pedagogía | `seguimientos_pedagogicos` | Observación durante la actividad; cambio/continuidad. |

Enfermería conserva su intervención y seguimiento del cuidado. Gerencia, Administración y Superadministración muestran coordinación y supervisión según sus permisos, sin obtener escritura clínica por su rol administrativo. Familia conserva el portal vinculado; no se habilita la publicación automática de alertas clínicas.

## Integración y seguridad

- Los botones de cada alerta y su detalle abren el formulario correspondiente al rol autenticado. Las entradas de navegación profesional llevan a la misma bandeja canónica.
- La selección de una atención exige el mismo residente y el mismo profesional. Una atención nueva se crea con la valoración en una transacción, usando la asignación de área vigente. Pedagogía conserva el contrato de su registrador, que no exige atención clínica.
- El registro deriva la autoría del personal de la cuenta autenticada. Verifica coincidencia de actor, cuenta activa, permiso, ámbito del residente, rol y contexto institucional. El formulario muestra nombre y profesión de ese personal, sin confundir la cuenta de acceso con la identidad profesional.
- El guardado usa los registradores clínicos existentes, crea un evento de seguimiento y conserva prioridad y estado de la alerta. La referencia al registro se conserva internamente en la descripción del evento y en Activitylog; no se crea una FK ni una tabla. Las claves internas se eliminan del texto visible mediante el formateador compartido.
- La clave de solicitud y el bloqueo de caché evitan duplicación en reintentos sucesivos. No se acredita concurrencia real entre dos clientes.
- La captura se conserva al validar y se protege al cerrar con cambios pendientes. Los valores no observados quedan sin registrar; `false` no se confunde con ausencia de información.
- La previsualización permite exclusivamente consultas Livewire expresamente enumeradas, con firma y middleware de Livewire posteriores. Rechaza escrituras y paquetes que mezclan consulta y escritura. Abrir un formulario en previsualización muestra sus campos deshabilitados.
- La ruta histórica de Enfermería selecciona el layout por perfil, evitando que Administración reciba el shell de Enfermería por la URL. Los identificadores compartidos del sidebar siguen el contrato existente de esa ruta.
- Se retiró texto técnico duplicado que podía aparecer junto a los calendarios de filtros.

## Permisos oficiales y aplicación autorizada

La propietaria aprobó expresamente `alertas.ver` para **NUTRICIONISTA, FISIOTERAPEUTA y PEDAGOGO**, solicitando conservarlo en los seeders oficiales. Se incorporó a `RolesAndPermissionsSeeder`, ya invocado desde `DatabaseSeeder`; las cuentas que reciben esos roles heredan el permiso sin duplicar permisos individuales.

En la base institucional se añadieron exactamente tres relaciones rol–permiso en una transacción, mediante `ActualizarPermisosRol`, conservando los permisos anteriores de cada rol. La operación dejó auditoría en `Roles y Permisos`. Se comprobaron las matrices completas de permisos antes/después, las asignaciones de roles y permisos directos de las cuentas y los conteos de usuarios, personal, residentes, alertas, eventos, atenciones y registros clínicos relacionados. No hubo cambios fuera del permiso aprobado y su auditoría.

No se ejecutó `DatabaseSeeder`, el seeder completo de permisos, una migración ni una carga de personas sobre la base institucional. El seeder conserva sus demás políticas oficiales de sincronización; esta entrega no promete conservar concesiones personalizadas ajenas a ese contrato al ejecutar una resincronización completa.

## Investigación y límites clínicos

Primero se consultaron los campos y registradores de Clínica y Valoraciones, el contexto clínico, el ámbito de Alertas y las decisiones institucionales. La evidencia externa orienta la organización del formulario, sin sustituir el protocolo de la residencia:

- [OMS, ICOPE, segunda edición](https://www.who.int/publications/i/item/9789240103726): atención centrada en la persona y continuidad adaptada al contexto local.
- [GMC, registro de decisiones](https://www.gmc-uk.org/professional-standards/decision-making-and-consent/recording-decisions): decisiones y actuaciones acordadas en el registro profesional.
- [HCPC, Psicología](https://www.hcpc-uk.org/standards/standards-of-proficiency/practitioner-psychologists/), [Nutrición](https://www.hcpc-uk.org/standards/standards-of-proficiency/dietitians/) y [Fisioterapia](https://www.hcpc-uk.org/standards/standards-of-proficiency/physiotherapists/): valoración, documentación y seguimiento dentro de la competencia profesional. Son referencias internacionales, no nuevas obligaciones bolivianas.
- [UNESCO, GRALE 5](https://www.uil.unesco.org/sites/default/files/medias/fichiers/2022/06/GRALE5_EN_FullReport15June2022.pdf): participación y aprendizaje de personas adultas; no se deriva una escala clínica ni un umbral pedagógico.

La persistencia de estos campos no acredita que todos alimenten automáticamente el sistema experto. Nuevos hechos o reglas consumidoras requieren el contrato de procedencia e inferencia señalado en [la investigación de Enfermería](REGISTRO_ENFERMERIA_Y_ENTRADA_EXPERTA_2026_10_21.md).

## Pruebas y build

| Evidencia | Resultado |
|---|---|
| SQLite: especialidades, experiencia visual por roles y suite de previsualización | 13 pruebas, 251 aserciones; PASS. `output/alertas-20261021/roles-diseno-sqlite.xml`. |
| PostgreSQL desechable: guardado por especialidad y negativos | 3 pruebas, 89 aserciones; PASS. `especialidades-postgres.xml`. Clúster detenido al finalizar. |
| SQLite: comparación final, evento de selección y calendarios sin texto técnico | 1 prueba, 31 aserciones; PASS. `comparacion-final.xml`. |
| SQLite: seeder oficial ejecutado dos veces, sin perder datos ni duplicar relaciones | 1 prueba, 12 aserciones; PASS. `seeder-permisos.xml`. Compara filas completas de cuenta, personal, residente y alerta, además de vínculos y conteos de permisos. |
| JavaScript: interacción, trazabilidad y signos | 6 pruebas; PASS. |
| `npm run build` | PASS, 110 módulos; 11,28 s en esta ejecución. `build-roles.log`. |

Las pruebas de especialidad verifican las columnas persistidas, autoría, conservación de estado, rollback ante hidratación inválida, idempotencia y rechazo de una atención de otro residente/profesional. Los negativos rechazan otra identidad, cuenta inactiva y falta de permiso sin generar registros ni eventos. La regresión HTTP de previsualización usa un snapshot firmado real y verifica consultas permitidas y escritura denegada.

## QA visual, legacy y commit

**Dictamen visual del último corte: FAIL por evidencia incompleta, sin defecto visual confirmado.** El control del navegador devolvió que el uso local está desactivado en Work. No se verificaron los cinco anchos 1440/1280/1024/768/390, ambos temas, contraste, foco, scroll y formulario renderizado del último corte. Las capturas anteriores no acreditan estos cambios. La propietaria eligió continuar sin control del navegador; la apertura solicitada quedó en cola y no se confirmó sesión ni previsualización actuales.

No se añadieron dependencias, migraciones ni compatibilidad V1. No se realizaron staging, commits, push ni despliegue; se conserva el trabajo local preexistente. Los alias históricos de rutas del sistema siguen fuera de una limpieza global.

Pendiente: revisión visual y de interacción en navegador cuando esté disponible. La ausencia de QA visual no se presenta como aceptación clínica ni como cumplimiento global de accesibilidad.
