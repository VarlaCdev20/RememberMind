# Reparación de conflictos guardados en la integración — 23/10/2026

## Incidente y alcance

El commit local `6d7b8c6d` había guardado 178 bloques de conflicto en 42 archivos. Git no mostraba una fusión pendiente: los marcadores formaban parte del contenido confirmado. El primer fallo reportado era `PersonalInstitucionalPanel.php:289`. Al cargar el registro de rutas, PHP encontraba `<<<<<<< HEAD` y abortaba incluso las solicitudes a Enfermería.

Se respaldó el contenido original de los 42 archivos en `output/resolucion-integracion-20261023/originales/`. El inventario de decisiones está en `output/resolucion-integracion-20261023/decisiones.json`. Son artefactos locales de reparación, no páginas del producto. No incluyen expedientes ni cookies del error adjunto.

## Decisiones de integración

- Enfermería: se conservan el perfil Petersen completo, el alcance autorizado por residente asignado, la presentación compartida con Psicología y la distinción entre consulta actual e historia conservada. Se conservan las pruebas de denegación y de seguimiento observacional.
- Psicología: se integra el explorador y las guías junto con el acceso local a la valoración Petersen. La ficha, su clase y sus pruebas usan la versión remota coherente entre capas, que valida competencia, permisos de cada fuente, autoría y registro afectivo sin hallazgos precargados. Los parciales locales anteriores siguen en el árbol; no se eliminan archivos ajenos al incidente.
- Conocimiento y grafos: se conservan el recorrido, la paginación de fuentes y los detalles navegables de la versión remota.
- Dashboard: se conserva la distribución compacta local y se integra la consulta remota de alertas.
- Componentes compartidos: el modal conserva tanto `surface` como `alpineBusy`; el drawer conserva su comportamiento local de consulta y foco.
- Personal: se conserva la relación `asignacionesTurno.jornada.turno`.
- El encabezado médico mantiene el nombre del residente y recupera la etiqueta visible «Evaluación cognitiva y funcional».

No se modificaron reglas clínicas, migraciones, PK/FK ni datos institucionales. No se cambiaron ramas, ni se hizo commit o push.

## Verificación

| Comprobación | Resultado |
|---|---|
| Búsqueda de marcadores en fuentes y documentación | Ninguno pendiente en los archivos del producto |
| `php -l` en app, rutas, configuración, bootstrap, database, motor y tests | 1.377 archivos sin errores; prueba nueva revisada por separado |
| `php artisan view:cache` y lint de su salida | 691 plantillas compiladas sin errores de sintaxis |
| `node --check` en recursos y pruebas frontend | 111 archivos JavaScript sin errores de sintaxis |
| `php artisan route:list --path=admin/enfermeria` | 15 rutas registradas |
| `EnfermeriaPerfilPetersenTest` | PASS, 19 aserciones |
| `ExploradorValoracionesTest` | PASS, 10 pruebas / 48 aserciones |
| `FichaPsicologicaResidenteTest` | PASS, 13 pruebas |
| `CicloObservacionalConservadoTest` | PASS, ambas secuencias de revisión profesional |
| `NavegacionSistemaExpertoTest` | PASS, 5 pruebas |
| `InformeCognitivoTest` + `FuentesSinConflictosIntegracionTest` | PASS, 33 pruebas / 288 aserciones |
| `ExploradorOrionTest`, `PersonalInstitucionalResponsiveTest`, `SidebarServiceTest` | PASS, 26 pruebas / 203 aserciones |
| `ConsolaPerfilExperimentalTest`, `GestionConocimientoExperimentalTest`, `GobiernoReglasExpertasTest` | PASS, 38 pruebas / 177 aserciones |
| Pruebas frontend seleccionadas | PASS, 18 pruebas: mapa, recorridos, paginación, dashboard, resultados responsive, avatar y guías |
| `npm run build` | PASS; advertencia de tamaño de bundle preexistente |

Los primeros fallos del informe tras resolver los marcadores eran expectativas del título médico; se corrigió la identificación visible de la pantalla y se volvió a ejecutar la suite completa del informe satisfactoriamente.

La instalación npm inicial encontró una DLL de Vite ocupada. Se detuvo exclusivamente el proceso Vite de este repositorio, se sincronizaron las dependencias y se restauraron las versiones del lock (Vite 8.3.1), sin cambios en package.json/package-lock.json. Vite se reinició en 5173 y devuelve HTTP 200.

El servidor 8000 devuelve HTTP 200 en login. La URL del resultado de Enfermería sin sesión redirige correctamente a login y deja de fallar al registrar rutas. La lectura autenticada y sus permisos están probados con residentes sintéticos mediante Livewire; esta comprobación HTTP no acredita una inspección visual de la sesión real del usuario. No se reutilizaron las cookies incluidas en el adjunto.

No se ejecutó la suite completa del repositorio ni una validación clínica. La reparación acredita la sintaxis global y los recorridos probados, no ausencia de cualquier defecto ajeno a esta integración.
