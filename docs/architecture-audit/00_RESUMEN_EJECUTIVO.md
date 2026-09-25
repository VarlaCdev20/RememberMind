# Auditoría arquitectónica de RememberMind

Fecha: 9 de septiembre de 2026. Estado: propuesta para aprobación; no autoriza implementación.

## Dictamen

Conservar Laravel y evolucionar hacia un monolito modular pragmático, con MVC en la interfaz y Actions para los procesos importantes. El problema principal no es Livewire: son las fuentes de verdad duplicadas, la autorización desigual y la lógica de procesos dentro de componentes de interfaz. Cambiar solamente a Vue trasladaría esos problemas.

La base conectada es **PostgreSQL 18.4**, con **61 tablas** en el esquema public y 60 migraciones registradas. Esto contradice la referencia a MySQL en AGENTS.md. Se consultaron metadatos y cuatro comprobaciones agregadas mediante transacciones READ ONLY; no se extrajeron fichas personales ni se modificaron datos. Una comprobación encontró **una cama con más de una asignación ACTIVO**. No demuestra por sí sola dos ocupaciones físicas: sí demuestra inconsistencia en la representación de asignaciones vigentes.

## Primero, integridad y autorización

1. P0: cerrar el alta HTTP de usuarios que admite asignación de rol bajo usuarios.ver; controlar también qué roles puede conceder cada actor.
2. P0: autorización por residente y por acción; el familiar recibe adultos.ver en el seeder y el expediente general no delimita vínculos en el controlador.
3. P0: conciliar asignaciones de cama y proteger unicidad/concurrencia.
4. P0: proteger documentos hoy escritos en disco public y sustituir contraseñas iniciales predecibles/enviadas por correo.
5. P0: corregir identificadores y pertenencia en administración de medicación antes de modernizar su interfaz.

Evidencia y criterios de cierre: [03_GAP_ANALYSIS.md](03_GAP_ANALYSIS.md), [09_BD_AS_IS.md](09_BD_AS_IS.md), [17_SEGURIDAD.md](17_SEGURIDAD.md).

## Respuestas a las 27 preguntas

| # | Pregunta | Respuesta concreta |
|---|---|---|
| 1 | Arquitectura real | Monolito Laravel organizado principalmente por tipo de clase, con subcarpetas funcionales Livewire y servicios parciales; no un monolito modular con límites explícitos. |
| 2 | Qué cumple MVC | Rutas → Controllers → Eloquent/Services → Blade; componentes Livewire también son una interfaz válida. |
| 3 | Qué rompe la separación | Procesos, consultas, documentos y correo en una misma interfaz; no es una infracción por usar Livewire, sino por acumular responsabilidades. |
| 4 | Lógica de negocio | Principalmente app/Livewire/Admin, Controllers, Services y eventos/mutadores Eloquent. |
| 5 | God Models | No hay evidencia para etiquetar un modelo como God Model absoluto. User y AdultoMayor concentran dominios; EvaluacionGeriatrica es el foco de mutadores de compatibilidad. Los mayores objetos multifunción son componentes/servicios. |
| 6 | Livewire excesivo | UsuariosPanel (2584 líneas), SaludSignosPanel (1304), TurnosAsignacionesPanel (1253), PersonalInstitucionalPanel (1006), PreadmisionWizard (957); además PersonalInstitucionalForm (1539). |
| 7 | Monolito modular | Sí; reduce duplicación conservando un despliegue, repositorio y sesión. |
| 8 | Estructura exacta | app/Modules/{Identity,Institution,Residents,Admissions,Clinical,Medication,Assessment,Care,Social,Safety,ExpertSystem,Reports}; árbol y reglas en 04/05. |
| 9 | Módulos técnicos | 12 límites de responsabilidad; crear físicamente cada uno cuando se migre. |
| 10 | Perfiles funcionales | 11 solicitados; voluntariado es un actor complementario existente que conserva acceso limitado si se valida su alcance. |
| 11 | Roles de seguridad | 6 roles base propuestos, con permisos explícitos mediante plantillas revisables y alcance por Policy; no convertir profesión en autorización automática. Alternativa de roles por capacidad si las excepciones crecen. |
| 12 | Profesión | Medicina, enfermería, psicología, fisioterapia, nutrición y trabajo social; geriatría es especialidad; dirección es responsabilidad/cargo y perfil, no profesión. |
| 13 | Dashboards | 6 composiciones: dirección, operaciones, clínico parametrizado, turno/cuidados, familiar y técnico. Enfermería puede preferir turno sin perder perfil clínico. |
| 14 | Vistas compartidas | Expediente 360, directorio, instrumentos, timeline, planes, alertas, documentos y reportes con datos filtrados en servidor. |
| 15 | Tablas TO-BE | Modelo completo propuesto: 51 tablas de dominio/soporte funcional, incluyendo users, más 15 técnicas = 66. Piloto reducido: 57. No hay beneficio en forzar 50 mezclando prescripción, dosis y ejecución o eliminando historial. Catálogo exacto en 10. |
| 16 | Conservar tablas | Conceptos de users/RBAC, áreas, habitaciones/camas, signos, notas, planes/tareas, alertas/acciones y soporte Laravel; varios requieren ampliar estructura y migrar claves. |
| 17 | Reemplazar | Identidad repetida por persons; horarios duplicados por staff_schedules; ficha de booleanos por diagnósticos/alergias/notas; campos espejo de evaluación por assessments versionadas; ubicación duplicada por bed_assignments. Mapa completo en 09. |
| 18 | Fuentes de verdad | Identidad: persons; acceso: users/Spatie; ocupación: bed_assignments; estancia: admissions; clínica: registros versionados del dominio; resultados: assessments; inferencia: expert_runs. |
| 19 | Laravel | Mantener 13; composer.lock fija v13.9.0. |
| 20 | Livewire | Mantener durante la transición, incluyendo autenticación/perfil hasta su fase específica. |
| 21 | Vue/TS/Inertia | Conveniente para la presentación longitudinal compleja si se acepta su costo. Es una propuesta, no una necesidad para corregir seguridad o dominio. |
| 22 | Costo | Estimación inicial 32–48 semanas-persona para base y migración prioritaria; 10–18 adicionales para módulos futuros/experto y validación ampliada. Reestimar tras dos cortes verticales. |
| 23 | Reutilizar frontend | Tokens semánticos, modo oscuro, decisiones de UX, Chart.js y plantillas PDF/correo; Blade/Alpine no se reutilizan directamente como Vue. |
| 24 | Migrar sin paralizar | Una pantalla canónica por flujo, Actions compartidas temporalmente, conmutación por ruta, aceptación y retiro de la anterior en el mismo corte. |
| 25 | Experto | Puerto ExpertEngineInterface, adaptador local/fake primero y HTTP Python después, snapshots/versiones, Jobs e intervención humana. |
| 26 | Implementar primero | Tras aprobación: baseline restaurable, pruebas de autorización y de concurrencia, correcciones P0 y fuentes de verdad. |
| 27 | No cambiar | No reemplazar Laravel, ni reescribir todo, ni migrar BD/backend/frontend simultáneamente; conservar sesión, tokens de diseño, reportes útiles e historia existente. |

## Cómo leer la entrega

Leer 00 → 03 → 04 → 07 → 10 → 19 → 20 para decidir. Los restantes documentos contienen inventarios, responsabilidades, estrategia de pruebas y contratos de migración. Todos los ADR distinguen propuestas de hechos; «ACEPTADA POR EVIDENCIA» valida una conclusión técnica, no sustituye la aprobación de la autora.

## Alcance y límites

Auditoría estática del repositorio, inventario completo de archivos de aplicación/frontend y esquema PostgreSQL conectado. Revisión profunda de flujos representativos y puntos críticos; no equivale a prueba exhaustiva de todos los métodos ni a certificación clínica, de accesibilidad o seguridad. No se ejecutaron tests, migraciones, builds, envíos, commits ni cambios de código. La validación funcional e interacción real quedan definidas como puertas de salida en 16/19. Se preservaron los cambios previos del usuario en .gitignore, composer.json, resources/js/app.js, welcome.blade.php, vite.config.js y el test no versionado CasosPreadmisionTest.php.
