# Nombres naturales para los treinta residentes de entrenamiento

Fecha: 22/10/2026. Solicitud de la propietaria: reemplazar «Residente de entrenamiento» por nombres y apellidos naturales en las vistas.

Se actualizaron los treinta residentes locales existentes, sin crear otros. Los nombres son identidades ficticias generadas; no se atribuyeron datos clínicos a personas reales. [Correspondencia completa](matriz-casos.csv) y [resultados locales después del cambio](resultados-locales.json).

| Caso | Nombre mostrado | Perfil orientativo calculado |
|---|---|---|
| S01 | Carmen Salvatierra Rueda | Posible DCL amnésico de dominio único |
| S04 | Beatriz Montellano Ruiz | Posible DCL amnésico multidominio |
| S07 | Héctor Saavedra Molina | Posible DCL no amnésico de dominio único |
| S09 | Alfonso Bustamante Leiva | Posible DCL no amnésico de dominio único |
| S10 | Matilde Pacheco Luján | Posible DCL no amnésico multidominio |

## Cambio y preservación

- `ManifiestoCognitivo30::NOMBRES` conserva las treinta identidades ficticias, utilizadas también en las futuras cargas del seeder.
- `CohorteCognitivaLocal` reconoce la cohorte por documento nominal y marca original de admisión. Ya no depende de `nombres`; corregir un nombre no desconecta las mediciones. La habilitación de bindings continúa restringida al entorno/base local autorizados. La procedencia de entrenamiento permanece identificable aunque se deshabilite esa capacidad.
- La búsqueda «entrenamiento» en ambos listados consulta la pertenencia al lote; continúa devolviendo treinta residentes aunque sus nombres sean naturales. Las búsquedas ordinarias por nombre/apellidos se mantienen.
- `scripts/cargar-cohorte-local.php --nombrar` cambia exclusivamente nombres y ambos apellidos, dentro de una transacción y con bloqueo de los registros. Verifica que los nombres no coincidan con los residentes anteriores y guarda el mapeo previo en almacenamiento privado.
- Cada cambio usa Activitylog con evento `identidad_ficticia_renombrada`, origen `CLI_LOCAL_AUTORIZADA` y nombres anterior/nuevo. No atribuye el cambio a un profesional real ni inventa una firma clínica.
- Los registros anteriores de preadmisión/admisión se conservan como historia original del lote. No se reescribieron mediciones, juicios, fechas, diagnósticos contextuales, reglas, permisos ni resultados para adaptar los nombres.
- Se compararon huellas de los ochenta residentes anteriores y las fuentes clínicas de la cohorte. Se verificó también que los demás atributos de cada residente renombrado permanecieran idénticos.

Comando efectivamente ejecutado sobre la base local autorizada:

```powershell
php scripts/cargar-cohorte-local.php remembermind_dev --nombrar
```

Resultado: **30 nombres actualizados, 0 residentes creados, 110 residentes totales, 0 discrepancias** en el recálculo completo. Se conservan tres casos de cada uno de los cuatro perfiles, seis casos sin patrón y doce contrastes. La copia de identidades anterior está en `storage/app/private/auditorias-locales/cohorte30/nombres-antes-*.json`, excluida de Git.

## Verificación

| Motor | Pruebas | Aserciones | Fallos/errores | Evidencia |
|---|---:|---:|---|---|
| SQLite `:memory:` | 15 | 2.444 | 0 / 0 | [JUnit](sqlite.junit.xml) |
| PostgreSQL desechable | 15 | 2.444 | 0 / 0 | [JUnit](postgres.junit.xml), [log](postgres.log) |

Filtro ejecutado en ambos motores:

```text
CohorteInferencia30Test|ManifiestoCognitivo30Test|MisResidentesPsicologiaTest|NavegacionSistemaExpertoTest
```

Las pruebas mantienen el recorrido de treinta casos e incorporan la recuperación de perfiles después de modificar el nombre visible, unicidad de los treinta nombres y búsqueda del lote con nombres naturales. La operación local comparó después todos los resultados con el manifiesto, sin discrepancias.

Pint pasó en los nueve archivos PHP revisados. `php -l scripts/cargar-cohorte-local.php` y el diff check del alcance pasaron. No se modificaron Blade, CSS ni JavaScript en esta entrega; no se necesitó otro build. No se declara inspección visual automatizada del navegador.

No hubo cambio estructural, eliminación de historia, commit, push ni despliegue. Esta modificación administrativa no cambia el alcance clínico ni resuelve la conservación conjunta SQL pendiente.
