# RememberMind — Baseline congelado de Base de Datos Operativa

**Estado:** CONGELADO  
**Versión:** BDD Operativa V2  
**Ámbito:** RememberMind — sistema residencial/geriátrico con seguimiento clínico y cognitivo  
**Tablas operativas:** 69  
**Entidad central:** `residentes`  
**Fuera de este baseline:** tablas técnicas de Laravel/Jetstream/Sanctum/Spatie y tablas futuras del sistema experto.

---

## 1. Regla de congelamiento y control de cambios

Este documento y `REMEMBERMIND_BDD_69_TABLAS.md` constituyen la **fuente de verdad de la Base de Datos Operativa V2**.

### REGLA OBLIGATORIA

> **NO SE PUEDE AGREGAR, ELIMINAR, FUSIONAR, DIVIDIR, RENOMBRAR NI MODIFICAR una tabla, atributo, PK, FK, cardinalidad o regla estructural de esta BDD sin consultar previamente al responsable del proyecto y obtener aprobación explícita.**

Esto incluye cambios aparentemente pequeños como:
- agregar una columna;
- reemplazar una FK por texto;
- mover atributos entre tablas;
- crear una tabla “genérica”;
- cambiar `cod_*` por `id`;
- introducir JSON/EAV para evitar migraciones;
- fusionar dos entidades;
- crear una nueva tabla de catálogo;
- eliminar una relación por “simplificación”.

Si durante el desarrollo Codex o un desarrollador detecta un posible problema:
1. no modifica el baseline;
2. documenta el hallazgo;
3. explica impacto y alternativas;
4. solicita decisión;
5. solo después de aprobación aplica una migración/versionado correspondiente.

---

## 2. Principio central del modelo

`residentes` es la **tabla maestra principal y entidad central** de RememberMind.

Toda información de salud, cuidado, seguimiento, cognición, funcionalidad, medicación, instrumentos, actividades, visitas y alertas debe poder vincularse **directa o indirectamente** con `cod_residente`.

El sistema distingue terminológicamente:
- **adulto mayor / postulante:** persona que aún se encuentra en preadmisión;
- **residente:** persona formalmente admitida a la residencia;
- **usuario:** cuenta de acceso al sistema;
- **personal:** trabajador/profesional vinculado a una cuenta;
- **contacto:** familiar, responsable o persona relacionada con un residente.

No utilizar estos términos como sinónimos en modelos, vistas, permisos ni documentación.

---

## 3. Flujo institucional obligatorio

Flujo congelado:

`Preadmisión PENDIENTE → revisión → APROBADA/RECHAZADA → admisión formal con cama → residente ADMITIDO`

Reglas:

1. La aprobación de una preadmisión **NO crea un residente**.
2. El residente se crea únicamente al formalizar la admisión.
3. La formalización debe ejecutarse dentro de una **transacción de base de datos**.
4. La operación crea/relaciona, según corresponda:
   - `residentes`;
   - `admisiones`;
   - `residentes_contactos`;
   - `ocupaciones_cama`;
   - `historial_estados_residente`;
   - `consentimientos`;
   - documentos y datos iniciales que correspondan.
5. Una cama ocupada no puede asignarse a otro residente.
6. Un residente no puede tener más de una ocupación de cama activa.
7. No debe existir un CRUD que permita crear un residente saltándose este flujo institucional.

---

## 4. Clasificación de las 69 tablas

### I. Tablas maestras — 11

1. `usuarios`
2. `personal`
3. `areas`
4. `turnos`
5. `contactos`
6. `residentes` — **maestra principal**
7. `habitaciones`
8. `camas`
9. `tipos_estudio_clinico`
10. `medicamentos`
11. `instrumentos`

### II. Tablas transaccionales — 45

1. `jornadas`
2. `preadmisiones`
3. `admisiones`
4. `historial_estados_residente`
5. `documentos`
6. `consentimientos`
7. `atenciones`
8. `notas_clinicas`
9. `antecedentes_clinicos`
10. `diagnosticos`
11. `alergias`
12. `seguros_residente`
13. `dispositivos_clinicos`
14. `signos_vitales`
15. `valoraciones_dolor`
16. `mediciones_antropometricas`
17. `estudios_clinicos`
18. `informes_estudio`
19. `documentos_clinicos`
20. `derivaciones`
21. `incidentes`
22. `indicaciones_clinicas`
23. `controles_cognitivos`
24. `registros_conductuales`
25. `registros_sueno`
26. `registros_ingesta`
27. `registros_hidratacion`
28. `registros_eliminacion`
29. `registros_movilidad`
30. `heridas`
31. `curaciones_herida`
32. `pases_turno`
33. `planes_cuidado`
34. `ejecuciones_cuidado`
35. `prescripciones`
36. `administraciones_medicacion`
37. `aplicaciones_instrumento`
38. `valoraciones_psicologicas`
39. `valoraciones_nutricionales`
40. `valoraciones_funcionales`
41. `seguimientos_pedagogicos`
42. `actividades`
43. `visitas`
44. `alertas`
45. `eventos_alerta`

### III. Tablas intermedias — 7

1. `asignaciones_personal`
2. `residentes_contactos`
3. `ocupaciones_cama`
4. `resultados_estudio`
5. `asignaciones_residente_jornada`
6. `respuestas_instrumento`
7. `participantes_actividad`

### IV. Tablas auxiliares — 6

1. `componentes_estudio`
2. `intervenciones_cuidado`
3. `programaciones_cuidado`
4. `horarios_prescripcion`
5. `preguntas_instrumento`
6. `opciones_pregunta`

**Total: 11 + 45 + 7 + 6 = 69.**

---

## 5. Principios de normalización

La BDD operativa debe mantenerse principalmente en **Tercera Forma Normal (3FN)**.

Reglas:

- una tabla representa una entidad o hecho con significado claro;
- no crear tablas clínicas “cajón de sastre”;
- no usar EAV (`campo`, `valor`, `tipo`) como almacenamiento clínico principal;
- no usar JSON como reemplazo de relaciones normalizadas;
- no crear una columna booleana por cada enfermedad posible;
- los antecedentes y diagnósticos son filas, no columnas;
- cama/habitación no se duplican en `residentes`;
- familiares no se incrustan en `residentes`;
- medicamentos prescritos y administrados son hechos distintos;
- instrumentos estandarizados usan el dominio específico de instrumentos;
- datos históricos nunca deben sobrescribirse para representar el nuevo estado.

La escalabilidad se obtiene mediante:
- separación modular;
- FK e índices;
- migraciones versionadas cuando aparezca un concepto nuevo real;
- catálogos específicos solo cuando estén justificados;
- consultas/reportes optimizados;
- no mediante tablas universales.

---

## 6. Convenciones de nombres

### Tablas
- español;
- plural;
- `snake_case`;
- nombre semántico y directo.

### Claves
- PK: `cod_<entidad>`;
- FK: mismo nombre de la PK referenciada;
- códigos de negocio preferentemente `VARCHAR(20)` para mantener coherencia con el proyecto actual.

Ejemplos:
- `cod_residente`;
- `cod_personal`;
- `cod_atencion`;
- `cod_prescripcion`.

No utilizar en la BDD V2:
- `id` genérico;
- `usuario_id`;
- `cod_usu`;
- `cod_am`;
- mezclas entre `id_*` y `cod_*`.

### Actor responsable
Evitar nombres terminados en `_por`.

Usar:
- `cod_usuario_registro`;
- `cod_usuario_revision`;
- `cod_usuario_validacion`;
- `cod_personal`;
- `cod_personal_suspension`.

### Fechas
Usar nombres de negocio:
- `fecha_admision`;
- `fecha_solicitud`;
- `fecha_revision`;
- `fecha_hora_asignacion`;
- `fecha_hora_administracion`;
- `fecha_consentimiento`;
- `fecha_hora_apertura`;
- `fecha_hora_cierre`.

Evitar `fecha_inicio` / `fecha_fin` cuando existe un evento más específico.

---

## 7. Regla sobre `usuarios`

`usuarios` representa **cuentas de acceso**, no una segunda tabla de personas.

Atributos congelados:

- `cod_usuario`;
- `correo`;
- `contrasena`;
- `foto`;
- `estado`.

No agregar:
- `acceso_sistema`;
- `ultimo_acceso`.

La identidad de un profesional vive en `personal`; la identidad de un familiar/contacto vive en `contactos`.

Roles y permisos pertenecen a Spatie Permission y **no se duplican** en tablas de negocio.

---

## 8. Roles institucionales activos

Roles funcionales vigentes:

1. `SUPERADMINISTRADOR`
2. `ADMINISTRADOR`
3. `ENFERMEROS`
4. `MEDICO GENERAL/GERIATRA`
5. `PSICOLOGO/A`
6. `PEDAGOGO`
7. `NUTRICIONISTA`
8. `FISIOTERAPEUTA`
9. `FAMILIAR`

**VOLUNTARIO queda fuera del alcance actual.**

### Regla de SUPERADMINISTRADOR

El `SUPERADMINISTRADOR` **puede VER toda la información del sistema**, incluidas las 69 tablas operativas y la auditoría.

Esto **no significa** que, por ser superadministrador, tenga automáticamente competencia para crear o modificar registros clínicos. Las escrituras clínicas se controlan mediante permiso + Policy + competencia profesional + contexto del residente.

---

## 9. Matriz conceptual de visibilidad por rol

### SUPERADMINISTRADOR
- lectura: **TODO**;
- administración: usuarios, personal, configuración, áreas, turnos, seguridad;
- escritura clínica: solo con la competencia/permisos clínicos correspondientes.

### ADMINISTRADOR
Ve principalmente:
- usuarios/personal;
- áreas, turnos y jornadas;
- preadmisiones y admisiones;
- residentes;
- contactos;
- habitaciones/camas/ocupaciones;
- documentos administrativos;
- consentimientos;
- seguros;
- asignaciones operativas;
- actividades y visitas;
- alertas/incidentes en el nivel necesario para operar.

No debe tener escritura clínica por el solo hecho de ser administrador.

### MÉDICO GENERAL/GERIATRA
Ve el expediente clínico interdisciplinario prácticamente completo:
- antecedentes;
- diagnósticos;
- alergias;
- signos;
- dolor;
- estudios/resultados/informes/documentos clínicos;
- indicaciones;
- prescripciones;
- administraciones realizadas;
- seguimiento de enfermería;
- valoraciones profesionales;
- instrumentos;
- planes;
- incidentes y alertas.

Es el único rol ordinario autorizado a prescribir medicación.

### ENFERMERÍA
Ve lo necesario para cuidado continuo:
- identidad/ubicación;
- antecedentes, diagnósticos, alergias;
- indicaciones;
- prescripciones y horarios;
- signos/dolor;
- estudios pertinentes;
- planes;
- alertas;
- seguimiento interdisciplinario necesario.

Registra principalmente:
- signos;
- administración de medicación;
- controles cognitivos;
- conducta;
- sueño;
- ingesta;
- hidratación;
- eliminación;
- movilidad;
- heridas/curaciones;
- incidentes;
- pases de turno;
- ejecuciones de cuidado.

No prescribe.

### PSICOLOGÍA
Ve:
- contexto clínico pertinente;
- cognición;
- conducta;
- sueño;
- medicamentos relevantes;
- instrumentos;
- valoraciones funcionales;
- planes y alertas pertinentes.

Registra:
- atenciones/notas;
- valoraciones psicológicas;
- instrumentos autorizados;
- intervenciones y seguimiento de su ámbito.

### NUTRICIÓN
Ve:
- diagnósticos/alergias/indicaciones pertinentes;
- antropometría;
- ingesta;
- hidratación;
- eliminación;
- resultados clínicos pertinentes;
- medicación relevante.

Registra:
- valoración nutricional;
- antropometría;
- atención/notas;
- planes/intervenciones de nutrición.

### FISIOTERAPIA
Ve:
- diagnóstico y restricciones pertinentes;
- dispositivos;
- signos;
- dolor;
- movilidad;
- incidentes;
- instrumentos funcionales.

Registra:
- valoraciones funcionales;
- movilidad;
- dolor en su intervención;
- atención/notas;
- planes/intervenciones funcionales.

### PEDAGOGÍA
Ve solo la información necesaria para intervención:
- residente;
- indicaciones pertinentes;
- controles cognitivos permitidos;
- conducta;
- planes;
- actividades.

Registra:
- seguimiento pedagógico;
- actividades/participación;
- atención/notas;
- planes/intervenciones de su ámbito.

No diagnostica.

### FAMILIAR
Acceso restringido exclusivamente a residentes vinculados mediante `residentes_contactos` y a información expresamente autorizada.

Puede acceder a:
- perfil básico autorizado;
- su relación de contacto;
- documentos/consentimientos autorizados;
- actividades autorizadas;
- visitas.

No tiene acceso directo por defecto a:
- notas clínicas;
- valoraciones psicológicas;
- controles cognitivos;
- pases de turno;
- prescripciones detalladas;
- administraciones;
- resultados médicos;
- documentos clínicos sensibles.

---

## 10. Regla general de autorización

Toda operación sensible debe cumplir:

**sesión autenticada + cuenta activa + permiso explícito + Policy contextual + regla de negocio válida + alcance/relación/competencia**

Ejemplos:
- un familiar solo puede acceder al residente vinculado;
- una enfermera no modifica una prescripción;
- un administrador no crea un diagnóstico;
- un profesional no puede atribuir registros a otro profesional;
- una cama ocupada no puede asignarse;
- una nota clínica corregida conserva trazabilidad.

---

## 11. Auditoría y trazabilidad

### Auditoría técnica
Utilizar `spatie/laravel-activitylog`.

No crear una segunda tabla empresarial como `auditoria_eventos`.

Auditar especialmente:
- altas/bajas lógicas;
- cambios de estado;
- cambios de permisos;
- accesos/descargas sensibles cuando sea necesario;
- cambios en prescripciones;
- correcciones/anulaciones clínicas;
- validaciones de documentos.

### Trazabilidad clínica
La procedencia clínica debe vivir también en las propias tablas:
- `cod_residente`;
- `cod_personal`;
- `cod_atencion` cuando corresponda;
- `cod_jornada` cuando corresponda;
- fecha/hora de negocio;
- estado.

No duplicar textos clínicos completos dentro de la bitácora técnica.

---

## 12. Borrado y corrección

### Datos clínicos
**Prohibido el borrado físico ordinario.**

Utilizar:
- `estado`;
- anulación;
- suspensión;
- cierre;
- corrección enlazada;
- registro compensatorio según el dominio.

No sobrescribir historia clínica para ocultar errores.

### Maestros
El borrado físico debe ser excepcional. Preferir `estado = INACTIVO`.

### FK
Para datos históricos/clínicos preferir `RESTRICT` antes que cascadas destructivas.

---

## 13. Documentación administrativa vs clínica

### `documentos`
Documentos administrativos/institucionales:
- identificación;
- documentación de ingreso;
- autorizaciones;
- documentos del contacto;
- otros documentos administrativos.

Campos relevantes:
- `ruta_archivo`;
- `tipo_archivo`;
- `hash_archivo`;
- `fecha_vencimiento` nullable;
- `fecha_validacion` nullable.

`hash_archivo` es técnico y automático; no es un campo que el usuario deba escribir.

No existe `version`; la sucesión se representa mediante `cod_documento_anterior`.

### `documentos_clinicos`
Archivos del expediente médico:
- informes médicos;
- hemogramas PDF;
- tomografías;
- radiografías;
- resonancias;
- DICOM;
- documentos clínicos externos.

Los archivos grandes no deben almacenarse como BLOB dentro de la base por defecto. Guardar ruta/identificador en almacenamiento privado y metadatos en la BDD.

---

## 14. Estudios clínicos

Estructura congelada:

`tipos_estudio_clinico → componentes_estudio`

y para un residente:

`estudios_clinicos → resultados_estudio / informes_estudio / documentos_clinicos`

Ejemplo:
- Hemograma = tipo de estudio;
- hemoglobina = componente;
- 11.2 g/dL = resultado;
- PDF del laboratorio = documento clínico.

Para imagen:
- Tomografía = tipo de estudio;
- hallazgos/conclusión = informe;
- PDF/DICOM = documento clínico.

No crear tablas separadas `hemogramas`, `tomografias`, `radiografias` salvo aprobación futura explícita.

---

## 15. Consentimientos

Tabla congelada `consentimientos`:

- no incluye `aceptado`;
- no incluye `firmante`;
- no incluye `tipo_firmante`;
- utiliza `firma_residente` y, cuando no firma el residente, `cod_residente_contacto`.

Reglas:
- si `firma_residente = true`, `cod_residente_contacto` debe ser NULL;
- si `firma_residente = false`, `cod_residente_contacto` debe existir;
- el contacto debe pertenecer al mismo residente;
- un registro de consentimiento representa un consentimiento formalmente registrado;
- revocaciones/anulaciones se manejan con `estado`, no con `aceptado = false`.

---

## 16. Medicación

Separación obligatoria:

`medicamentos → prescripciones → horarios_prescripcion → administraciones_medicacion`

Significado:
- `medicamentos`: qué medicamento existe;
- `prescripciones`: qué indicó el médico;
- `horarios_prescripcion`: cuándo corresponde;
- `administraciones_medicacion`: qué ocurrió realmente.

Enfermería puede administrar/documentar, pero no modificar la orden médica.

---

## 17. Seguimiento longitudinal

RememberMind debe preservar evolución temporal.

Tablas clave:
- `signos_vitales`;
- `valoraciones_dolor`;
- `controles_cognitivos`;
- `registros_conductuales`;
- `registros_sueno`;
- `registros_ingesta`;
- `registros_hidratacion`;
- `registros_eliminacion`;
- `registros_movilidad`;
- `mediciones_antropometricas`;
- `aplicaciones_instrumento`;
- valoraciones profesionales.

Nunca convertirlas en “un estado actual” sobrescribible.

---

## 18. Instrumentos

Dominio específico y normalizado:

`instrumentos → preguntas_instrumento → opciones_pregunta`

Aplicación:

`aplicaciones_instrumento → respuestas_instrumento`

Puede soportar instrumentos como MoCA, MMSE, Pfeiffer, Barthel y Katz cuando esté legal y metodológicamente autorizado.

No cargar contenido protegido/licenciado sin verificar derechos de uso.

---

## 19. Planes de cuidado

Estructura congelada:

`planes_cuidado → intervenciones_cuidado → programaciones_cuidado / ejecuciones_cuidado`

No existe `tareas_cuidado`.

- el plan define el objetivo;
- la intervención define lo que debe hacerse;
- la programación define cuándo;
- la ejecución registra qué ocurrió.

---

## 20. Alertas

`alertas` representa la alerta.

`eventos_alerta` representa el ciclo de vida:
- creada;
- reconocida;
- asignada;
- atendida;
- cerrada/anulada.

No sobrescribir el historial de una alerta.

---

## 21. Restricciones excluidas del alcance

No crear actualmente:
- economía;
- facturación;
- pagos;
- cuentas de residentes;
- movimientos financieros;
- voluntariado;
- instituciones;
- sedes;
- tabla propia de roles;
- tabla propia de permisos;
- auditoría duplicada;
- tabla clínica universal de variables;
- tabla clínica universal de observaciones;
- tabla universal `parametros`.

Laravel/Spatie continúan proporcionando sus tablas técnicas correspondientes.

---

## 22. Índices mínimos

Crear índices en:
- todas las FK;
- identificadores/documentos usados para búsqueda;
- `correo` de `usuarios` (UNIQUE);
- códigos/identificadores naturales cuando corresponda;
- tablas históricas de alto volumen: `(cod_residente, fecha_hora)` o equivalente;
- administración de medicación: residente + fecha;
- alertas: residente + estado + fecha;
- estudios: residente + fecha;
- ocupaciones activas;
- asignaciones por jornada.

No agregar índices indiscriminadamente; deben responder a búsquedas y joins reales.

---

## 23. Restricciones de integridad críticas

- `usuarios.correo` único.
- No dos ocupaciones activas para la misma cama.
- No dos ocupaciones activas para el mismo residente.
- `residentes_contactos`: impedir duplicado activo del mismo par.
- consentimiento de contacto debe pertenecer al mismo residente.
- `respuestas_instrumento`: una respuesta por pregunta y aplicación salvo que el instrumento explícitamente permita múltiples.
- `participantes_actividad`: evitar duplicar residente en la misma actividad.
- `resultados_estudio`: evitar duplicar el mismo componente para el mismo estudio salvo justificación.
- una administración de medicación debe corresponder a una prescripción del mismo residente.
- una respuesta debe corresponder a una pregunta del instrumento aplicado.
- validaciones de estados/ciclos de vida se complementan con Services/Actions/Policies, no solo con la BDD.

---

## 24. Compatibilidad técnica

Repositorio RememberMind:
- PHP 8.3;
- Laravel 13;
- Jetstream 5.5;
- Sanctum 4;
- Livewire 4.3;
- Spatie Permission 7.3;
- Spatie Activitylog 4.12.

El proyecto soporta SQLite/MySQL/MariaDB/PostgreSQL según `config/database.php`; las migraciones deben utilizar APIs portables de Laravel siempre que sea posible.

---

## 25. Criterio de finalización de la refactorización

La BDD V2 solo se considera implementada cuando:

1. existen exactamente las 69 tablas operativas definidas en el documento de esquema;
2. las tablas técnicas necesarias de framework/paquetes funcionan;
3. `php artisan migrate:fresh --seed` termina sin error;
4. todas las FK e índices críticos existen;
5. roles activos excluyen `VOLUNTARIO`;
6. Superadmin conserva lectura total;
7. Médico y Enfermería tienen permisos diferenciados;
8. no existe creación directa de residente fuera de admisión;
9. modelos y relaciones Eloquent corresponden al nuevo esquema;
10. seeders/factories/tests ya no referencian tablas o columnas legacy;
11. código, Livewire, Actions, Services, Policies, Requests y reportes dejan de usar nombres legacy;
12. los tests del flujo institucional y clínico crítico pasan;
13. no quedan migraciones legacy de negocio coexistiendo con la nueva BDD.

---

# AVISO FINAL DE GOBERNANZA

**ESTA BDD ESTÁ CONGELADA.**

Toda propuesta posterior de cambio debe ser consultada antes de implementarse.  
El desarrollador o agente de IA puede detectar y documentar problemas, pero **NO está autorizado a cambiar el modelo congelado por iniciativa propia**.

