# Lectura IADL para preparar Petersen

19/10/2026. **PROPUESTA de permiso de fuente, pendiente de decisión**.

La captura Petersen y sus tres permisos ya están aprobados. Su autorización no
concede automáticamente lectura de cada fuente vinculada. El permiso
`controles_funcionales_iadl.ver` se comprueba en el lector descriptivo existente,
pero todavía no se provisiona a Medicina/Psicología en el seeder vigente.
El código mantiene esa comprobación; no la sustituye por el permiso de Petersen.

Se propone provisionar exclusivamente `controles_funcionales_iadl.ver` a los
roles MEDICO GENERAL/GERIATRA y PSICOLOGO/A, además de la consulta técnica de
Superadministración, con cuenta activa y Policy contextual del residente.
No autoriza crear, rectificar, borrar IADL, otros expedientes ni escribir por
Superadministración. No añade tablas, columnas ni otros permisos.

Esto permite seleccionar y consultar los originales IADL para C03 cuando
existan; el permiso por sí solo no acredita independencia instrumental.
Sin la decisión, la pantalla omite la fuente y el motor conserva C03 pendiente.
