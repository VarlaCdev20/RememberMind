# Corrección de navegación al detalle cognitivo — 22/10/2026

## Síntoma y causa comprobada

En los resultados de Psicología, las acciones «Ver fundamento y límites»,
«Ver el detalle de las áreas», «Ver cómo se obtuvo», «Ver qué falta» y
«Ver criterios y soportes» envían `orion-petersen-detalle` a `irASeccion`.
Ese destino existe como un elemento `details` en `petersen-institucional.blade.php`,
pero faltaba en la lista de destinos permitidos del componente Livewire.
El backend abortaba con HTTP 422 antes de emitir el evento de navegación.

La prueba de regresión reprodujo el HTTP 422 con el código anterior, después
de comprobar que el destino y el botón se renderizaban realmente.

## Implementación

- `app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/ResultadosExpertoResidente.php`:
  se incluye el destino existente en la lista explícita de `irASeccion`.
- `tests/Feature/NavegacionDetallePetersenTest.php`: comprueba la navegación
  Livewire, el cierre de la ventana anterior, la existencia del destino en el HTML,
  el rechazo de destinos desconocidos y la reautorización ante cuenta desactivada
  o permiso revocado mientras la pantalla permanece abierta.

Se reutiliza el manejador Alpine existente: abre el `details`, abre sus
antecesores plegados, desplaza hasta el destino y enfoca su encabezado.
La comprobación de autorización continúa ejecutándose antes de navegar;
los rechazos de cuenta/permisos conservan la respuesta 404 de la Policy.
La operación no crea evaluaciones ni diagnósticos.

## Verificación ejecutada

```powershell
php artisan test --filter='NavegacionDetallePetersenTest|InformeCognitivoTest|NavegacionSistemaExpertoTest' --compact
```

SQLite `:memory:`: **40 pruebas, 344 aserciones, satisfactorias**.
Esta ejecución incluía las primeras tres regresiones del archivo nuevo.
Después se añadió y comprobó la revocación explícita de permisos:

```powershell
php artisan test --filter='NavegacionDetallePetersenTest::test_permiso_revocado' --compact
```

SQLite `:memory:`: **1 prueba, 2 aserciones, satisfactoria**.
Total de pruebas distintas comprobadas en SQLite: **41, con 346 aserciones**.

```powershell
powershell -NoProfile -File scripts/probar-postgres.ps1 -Filtro NavegacionDetallePetersenTest -Informe storage/logs/navegacion-detalle-petersen-pg.junit.xml -Log storage/logs/navegacion-detalle-petersen-pg.log
```

PostgreSQL en clúster y base desechables: **4 pruebas, 14 aserciones,
satisfactorias**. El servidor temporal se detuvo al finalizar.
También pasaron `php -l` del componente y `git diff --check` en el alcance.
Los logs de ejecución se conservan localmente bajo `storage/logs/`, ignorados
por Git; no incluyen el archivo adjunto ni sus datos de sesión.

## Alcance y límites

Implementado y probado mediante solicitudes Livewire y HTML renderizado.
No se efectuó comprobación visual en un navegador en esta corrección.
No se modificaron Blade, CSS, JavaScript, esquema, registros institucionales,
reglas clínicas ni permisos. Build de assets no requerido para este ajuste PHP.
Se preservó el trabajo previo del árbol compartido. Sin commit, push o despliegue.
