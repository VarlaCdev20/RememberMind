# RememberMind — Matriz funcional de roles y permisos

## 1. Propósito

Este documento define la frontera funcional y de autorización de los **10 roles vigentes** del sistema operativo normal de RememberMind.

Debe leerse junto con:

- `docs/REMEMBERMIND_MAPA_MAESTRO.md`;
- `REMEMBERMIND_BDD_BASELINE_CONGELADO.md`;
- `AGENTS.md`;
- `app/AGENTS.md`.

Los roles y permisos se implementan con **Spatie Permission** y no modifican el conteo de las 69 tablas operativas.

Este documento no define permisos del futuro sistema experto. La red semántica, árboles de decisión, inferencia, predicción y recomendaciones expertas se diseñarán por separado.

---

# 2. Principio de autorización

Una operación sensible no se autoriza solo por el nombre del rol.

Debe cumplirse, según corresponda:

```text
sesión autenticada
+
cuenta activa
+
permiso explícito
+
Policy contextual
+
regla de negocio válida
+
relación / alcance / competencia profesional
```

Ocultar una opción en la interfaz no reemplaza la autorización del backend.

---

# 3. Roles vigentes

1. `SUPERADMINISTRADOR`
2. `GERENTE`
3. `ADMINISTRADOR`
4. `ENFERMEROS`
5. `MEDICO GENERAL/GERIATRA`
6. `PSICOLOGO/A`
7. `PEDAGOGO`
8. `NUTRICIONISTA`
9. `FISIOTERAPEUTA`
10. `FAMILIAR`

`VOLUNTARIO` permanece fuera del alcance actual.

---

# 4. Separación crítica: Gerente vs Administrador

## GERENTE

Responsable de la **gestión del personal y planificación laboral/institucional de alto nivel** dentro de la estructura vigente.

Debe poder:

- consultar las cuentas necesarias para vinculación institucional cuando estén autorizadas;
- consultar y gestionar personal;
- registrar/actualizar la información laboral que permita la BDD vigente;
- gestionar áreas;
- gestionar la definición de turnos;
- consultar jornadas y asignaciones para evaluar cobertura;
- supervisar disponibilidad y distribución general de personal.

No debe:

- abrir/cerrar jornadas operativas por el solo hecho de ser Gerente;
- realizar la asignación operativa diaria de personal por el solo hecho de ser Gerente;
- gestionar preadmisiones/admisiones por el solo hecho de ser Gerente;
- escribir información clínica;
- prescribir;
- administrar medicación;
- acceder a información clínica detallada sin otra autorización explícita.

Si contratación futura requiere contratos, salario, expediente RR. HH. u otros campos inexistentes en las 69 tablas, se documentará como ampliación estructural antes de implementarse.

## ADMINISTRADOR

Responsable de la **operación diaria de la residencia**.

Debe poder, según permisos:

- consultar personal, áreas y turnos necesarios para operar;
- abrir/gestionar jornadas;
- asignar personal a jornada/área;
- gestionar preadmisiones;
- revisar preadmisiones;
- formalizar admisiones;
- consultar residentes;
- gestionar contactos y relaciones administrativas;
- gestionar habitaciones y camas;
- gestionar documentación administrativa y consentimientos;
- gestionar actividades y visitas;
- consultar alertas/incidentes en el nivel necesario para coordinar la operación.

No debe:

- gestionar la ficha laboral del personal por el solo hecho de ser Administrador;
- redefinir áreas/turnos estructurales por el solo hecho de ser Administrador;
- diagnosticar;
- prescribir;
- modificar órdenes médicas;
- escribir registros clínicos por el solo hecho de ser Administrador.

---

# 5. Matriz funcional resumida

| Dominio | Superadmin | Gerente | Administrador | Médico | Enfermería | Psicología | Nutrición | Fisioterapia | Pedagogía | Familiar |
|---|---|---|---|---|---|---|---|---|---|---|
| Seguridad/roles | Gestiona | No | No | No | No | No | No | No | No | No |
| Personal | Gestiona | Gestiona | Consulta | Consulta contextual | Consulta contextual | Consulta contextual | Consulta contextual | Consulta contextual | Consulta contextual | No |
| Áreas/turnos | Gestiona | Gestiona | Consulta | Consulta necesaria | Consulta necesaria | Consulta necesaria | Consulta necesaria | Consulta necesaria | Consulta necesaria | No |
| Jornadas/asignación diaria | Gestiona | Consulta | Gestiona | Consulta según trabajo | Trabaja en jornada | Según trabajo | Según trabajo | Según trabajo | Según trabajo | No |
| Preadmisión/admisión | Supervisa/gestiona | No por rol | Gestiona | Consulta pertinente | Consulta pertinente | No por rol | No por rol | No por rol | No por rol | No |
| Residentes | Lee todo | No por rol | Consulta operativa | Consulta clínica | Consulta cuidado | Consulta pertinente | Consulta pertinente | Consulta pertinente | Consulta pertinente | Solo vinculados |
| Historia clínica | Lee | No | No escritura | Gestiona ámbito médico | Registra cuidado | Su ámbito | Su ámbito | Su ámbito | Su ámbito | No por defecto |
| Diagnóstico | Lee | No | No | Crea/gestiona según flujo | No | No diagnóstico médico | No | No | No | No |
| Prescripción | Lee | No | No | Crea/edita/suspende | Consulta | Consulta pertinente | Consulta pertinente | Consulta pertinente | No por rol | No |
| Administración medicación | Lee | No | No | Consulta | Registra | Consulta pertinente | Consulta pertinente | Consulta pertinente | No | No |
| Planes de cuidado | Lee | No | Consulta operativa cuando proceda | Su competencia | Ejecuta/registra | Su competencia | Su competencia | Su competencia | Su competencia | No por defecto |
| Alertas | Lee/gestión autorizada | Solo las necesarias para personal si se define | Consulta/coordinación operativa | Clínicas pertinentes | Clínicas/cuidado pertinentes | Pertinentes | Pertinentes | Pertinentes | Pertinentes | No por defecto |
| Actividades/visitas | Lee/gestiona | No por rol | Gestiona | Consulta si pertinente | Consulta si pertinente | Consulta si pertinente | Consulta si pertinente | Consulta si pertinente | Gestiona su ámbito | Acceso autorizado |
| Auditoría | Lee | No por defecto | Lectura operativa autorizada | No por defecto | No por defecto | No por defecto | No por defecto | No por defecto | No por defecto | No |

La tabla anterior describe intención funcional. El permiso concreto y la Policy siguen siendo la autoridad técnica en cada operación.

---

# 6. Permisos Spatie base para Gerente

En la fase actual, el rol `GERENTE` debe iniciar con un conjunto deliberadamente limitado:

```text
usuarios.ver
personal.ver
personal.gestionar
areas.ver
areas.gestionar
turnos.ver
turnos.gestionar
jornadas.ver
asignaciones_personal.ver
```

No se concede inicialmente:

```text
usuarios.gestionar
jornadas.gestionar
preadmisiones.*
admisiones.*
residentes.* clínico
prescripciones.*
administraciones_medicacion.*
diagnosticos.*
alertas.gestionar
```

La creación/asignación de cuentas y roles permanece en Superadministración hasta construir un flujo de contratación seguro que limite qué roles puede asignar Gerencia.

---

# 7. Permisos Spatie base para Administrador

La fase actual debe separar lectura institucional de gestión laboral.

El Administrador puede usar:

```text
personal.ver
areas.ver
turnos.ver
jornadas.ver
jornadas.gestionar
asignaciones_personal.ver
preadmisiones.ver
preadmisiones.crear
preadmisiones.revisar
admisiones.ver
admisiones.formalizar
residentes.ver
contactos.ver
contactos.gestionar
residentes_contactos.ver
habitaciones.ver
habitaciones.gestionar
camas.ver
camas.gestionar
ocupaciones_cama.ver
documentos.ver
documentos.gestionar
consentimientos.ver
consentimientos.gestionar
seguros_residente.ver
actividades.ver
actividades.gestionar
visitas.ver
visitas.gestionar
alertas.ver
incidentes.ver
auditoria.ver
```

No debe recibir por prefijo genérico permisos como:

```text
personal.gestionar
areas.gestionar
turnos.gestionar
usuarios.gestionar
```

salvo decisión funcional futura explícita.

---

# 8. Superadministrador

El Superadministrador:

- puede leer todas las tablas operativas;
- gestiona cuentas, permisos y seguridad;
- puede gestionar configuración institucional no clínica;
- no adquiere competencia clínica automática.

Por tanto:

```text
SUPERADMINISTRADOR + prescripciones.ver = permitido
SUPERADMINISTRADOR + prescripciones.crear = no por rol
SUPERADMINISTRADOR + diagnosticos.crear = no por rol
```

Si la misma persona además es profesional clínico, esa competencia debe representarse mediante autorización explícita y contexto profesional, no mediante un bypass global del rol Superadmin.

---

# 9. Médico general / geriatra

Es el rol ordinario responsable de la decisión médica.

Puede, según Policy y contexto:

- consultar expediente interdisciplinario;
- crear atenciones/notas médicas;
- registrar antecedentes/diagnósticos/alergias según el flujo;
- solicitar/interpretar estudios dentro del diseño aprobado;
- emitir indicaciones;
- prescribir, editar y suspender prescripciones;
- consultar administraciones realizadas;
- revisar signos, dolor, evolución y alertas;
- trabajar con instrumentos autorizados cuando corresponda.

No debe poder atribuir registros a otro profesional manipulando `cod_personal`.

---

# 10. Enfermería

Enfermería trabaja en cuidado continuo.

Puede, según Policy/contexto:

- consultar residente, ubicación y datos clínicos necesarios;
- registrar signos vitales y dolor;
- registrar conducta, sueño, ingesta, hidratación, eliminación y movilidad;
- registrar heridas/curaciones dentro de competencia;
- administrar/documentar medicación prescrita;
- registrar incidentes;
- ejecutar cuidados;
- realizar pases de turno;
- trabajar con alertas pertinentes.

No puede:

- crear/modificar una prescripción;
- diagnosticar por rol;
- atribuir la administración a otro profesional.

---

# 11. Psicología

Puede trabajar, dentro de competencia, con:

- atenciones/notas;
- valoraciones psicológicas;
- conducta;
- sueño;
- cognición pertinente;
- instrumentos autorizados;
- planes/intervenciones de su ámbito;
- alertas pertinentes.

No obtiene facultades médicas de diagnóstico/prescripción.

---

# 12. Nutrición

Puede trabajar, dentro de competencia, con:

- antropometría;
- ingesta;
- hidratación;
- eliminación;
- valoración nutricional;
- atención/notas;
- planes/intervenciones nutricionales;
- diagnósticos/alergias/indicaciones/resultados pertinentes en lectura.

No prescribe medicación por rol.

---

# 13. Fisioterapia

Puede trabajar, dentro de competencia, con:

- movilidad;
- valoración funcional;
- dolor relacionado con intervención;
- dispositivos/restricciones pertinentes;
- atenciones/notas;
- planes/intervenciones funcionales.

No prescribe medicación por rol.

---

# 14. Pedagogía

Puede trabajar, dentro de competencia, con:

- seguimiento pedagógico;
- actividades y participación;
- conducta/cognición permitida en lectura;
- atenciones/notas;
- planes/intervenciones de su ámbito.

No diagnostica.

---

# 15. Familiar

El rol `FAMILIAR` no obtiene acceso a todos los residentes por tener el rol.

Debe cumplirse la relación real mediante `residentes_contactos` y la autorización correspondiente.

Puede consultar únicamente información expresamente habilitada del residente vinculado, por ejemplo:

- perfil básico autorizado;
- relación de contacto;
- actividades autorizadas;
- visitas;
- documentos/consentimientos autorizados.

Por defecto no accede directamente a:

- notas clínicas;
- controles cognitivos;
- pases de turno;
- prescripciones detalladas;
- administraciones de medicación;
- resultados médicos;
- documentos clínicos sensibles.

---

# 16. Alertas

El permiso `alertas.ver` no implica automáticamente capacidad para modificar cualquier alerta.

La evolución futura del módulo debe distinguir, mediante permisos/Policies/contexto cuando corresponda:

- crear alerta manual;
- reconocer;
- asignar;
- atender;
- cerrar/anular;
- consultar historial.

La severidad/tipo de alerta no debe habilitar acciones fuera de la competencia del profesional.

---

# 17. Sistema experto

No se crean en esta matriz permisos de:

```text
predicciones.generar
inferencia.ejecutar
recomendaciones_clinicas.generar
red_semantica.gestionar
```

hasta que exista el documento y diseño aprobado del sistema experto.

El sistema operativo normal debe seguir funcionando sin dichos permisos.

---

# 18. Regla para nuevas funcionalidades

Cuando se incorpore una funcionalidad nueva:

1. identificar actor responsable;
2. definir quién consulta y quién modifica;
3. definir competencia profesional/contextual;
4. crear el permiso mínimo necesario;
5. implementar Policy cuando exista alcance por residente/recurso;
6. probar roles positivos y negativos;
7. actualizar esta matriz si cambia la frontera funcional.

No conceder un prefijo amplio como solución rápida si eso entrega acciones no relacionadas.
