# Flujo Git de RememberMind

Decisión del propietario, 11/10/2026: conservar los doce módulos de la arquitectura híbrida como base técnica; mantener visibles las capacidades pendientes. Main no acredita por sí sola aceptación institucional, producción ni validación clínica.

## Ramas y recorrido

| Rama | Uso | Origen y destino |
|---|---|---|
| `main` | Base técnica aceptada y entregas revisadas | Recibe PR de release/hotfix, con CI aprobado |
| `develop` | Integración del siguiente trabajo | Recibe PR de feature y sincronización desde main/release/hotfix |
| `feature/<modulo>-<objetivo>` | Una tarea concreta, incluidos refactor/docs/tests | Nace de develop y vuelve a develop por PR |
| `release/<identificador>` | Preparación y correcciones de una entrega | Nace de develop; PR a main; sincronizar main hacia develop |
| `hotfix/<problema>` | Corrección urgente de una entrega | Nace de main; PR a main; sincronizar hacia develop |

`PODEMOS-SEGUIR` conserva el checkpoint publicado de la preparación y el desacoplamiento; el trabajo nuevo parte de develop. Las ramas antiguas se archivan antes de retirarlas. No se hace force push ni se reescribe el historial compartido.

```bash
git switch develop
git pull --ff-only origin develop
git switch -c feature/identidad-documentos
# implementar y verificar el flujo
git add <archivos-del-cambio>
git commit -m "fix(identidad): rechazar documentos de cuentas inactivas"
git push -u origin feature/identidad-documentos
# abrir PR hacia develop y esperar comprobaciones
```

Commits nuevos: `feat`, `fix`, `refactor`, `perf`, `test`, `docs`, `build`, `ci`, `chore` o `revert`; alcance opcional, descripción concreta y máximo 100 caracteres en el asunto. Un commit debe representar un cambio coherente y verificable. No mezclar limpieza, cambios de negocio y dependencias sin una razón documentada. No modificar mensajes históricos para adaptarlos a esta norma.

Las tareas se integran con squash y un título convencional. Las releases y hotfix usan merge para preservar continuidad y trazabilidad entre main/develop. Después de cada entrega, abrir PR main -> develop; no dejar la corrección solo en main. Las ramas de trabajo se eliminan al integrar; los checkpoints y archivos históricos se conservan según su propósito.

## Controles de integración

`quality` ejecuta sintaxis, auditoría de dependencias, frontend, build, motor independiente y suite SQLite completa. `postgres-integration` ejecuta contratos, roles, concurrencia y regresiones de desacoplamiento en una base desechable. `git-flow` comprueba el recorrido de ramas y mensajes nuevos en PR de trabajo/hotfix. La implementación de la política tiene pruebas propias en scripts.

Main y develop requieren PR, una aprobación independiente, checks aprobados y rama actualizada; no permiten force push ni eliminación, también para administradores. GitHub registra al propietario y otro colaborador con acceso write, por lo que puede existir revisión por otra persona. Durante la transición inicial se integra exclusivamente la base autorizada por el propietario con checks obligatorios y cero aprobaciones externas; al terminar se activa una aprobación para el trabajo posterior. No se suplanta al revisor ni se aprueba un PR propio.

## Doce módulos conservados y pendientes

Se conservan Identidad, Institucion, Residentes, Admisiones, Clinica, Medicacion, Valoraciones, Cuidados, Social, Alertas, Reportes y ApoyoDecision. Los módulos comparten relaciones y flujos institucionales: no se eliminan sus piezas por una clasificación visual o por tener trabajo futuro.

El alcance aceptado es la base técnica probada. El [estado por área](../ESTADO_ACTUAL.md), la [deuda técnica](../DEUDA_TECNICA.md), las [decisiones pendientes](../DECISIONES_PENDIENTES.md) y el [informe del desacoplamiento](../arquitectura/DESACOPLAMIENTO_2026_10_11.md) gobiernan el desarrollo posterior. Siguen pendientes privacidad documental, atomicidad universal de archivos, advisory de desarrollo y validación institucional/clínica donde corresponda.

No se publican .env, credenciales, dumps, almacenamiento persistente, archivos de editor ni datos reales. La modernización de main sustituye su árbol antiguo por el árbol modular ya validado; no purga el historial ni modifica datos instalados. El respaldo previo incluye todas las referencias en un bundle fuera del repositorio y la rama histórica respaldo/main-2026-10-10; ramas retiradas conservan sus puntas mediante tags archivo/2026-10-11/.
