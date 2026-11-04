---
title: "Extensión aprobada de usuarios para Recordarme"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [usuarios_remember_token, persistent_login_duration]
---

# Decisión expresa — 26/10/2026

La propietaria respondió **«Sí, autorizo la columna y los 30 días»** a la
solicitud específica de agregar `usuarios.remember_token VARCHAR(100) NULL`,
sin tablas nuevas ni cambios de PK/FK, e instalar y activar Recordarme con
una cookie de 30 días. La aprobación sustituye el estado pendiente de la
[propuesta](../sistema/RECORDARME_2026_10_26.md) exclusivamente para este alcance.

- Columna nullable de autenticación, sin índice ni FK; no modifica identidad,
  correo, hash de contraseña, estado, relaciones ni tablas clínicas.
- No se añaden tablas; no cambia el inventario por esta extensión.
- Migración: `database/migrations/2026_10_26_000001_add_remember_token_to_usuarios.php`.
- Cookie persistente de 43.200 minutos; activación explícita por configuración.
- Token oculto en serialización y auditoría. Logout, cambio/restablecimiento
  de contraseña y cambios de estado lo invalidan. Las sesiones ya abiertas
  conservan sus controles propios de autorización.
- Reversión: apagar el flag antes de retirar exclusivamente esta columna;
  el retiro invalida la persistencia recordada y conserva las cuentas.

Las pruebas y la evidencia de instalación se registran en el informe de la
función. Esta decisión no aprueba otras modificaciones estructurales.
