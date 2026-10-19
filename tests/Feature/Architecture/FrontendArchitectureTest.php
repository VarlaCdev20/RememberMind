<?php

namespace Tests\Feature\Architecture;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FrontendArchitectureTest extends TestCase
{
    /**
     * Protege que los componentes genéricos en components/ui no conozcan modelos Eloquent ni ejecuten consultas.
     */
    public function test_components_ui_no_importan_modelos(): void
    {
        $uiPath = resource_path('views/components/ui');
        $this->assertDirectoryExists($uiPath);

        $violaciones = [];

        foreach (File::files($uiPath) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contenido = File::get($file->getPathname());

            if (preg_match('/App\\\\Models\\\\\w+/i', $contenido)
                || preg_match('/(?:Residente|User|Residente|Alerta|Turno|Atencion)::(?:query|where|find|all)/i', $contenido)) {
                $violaciones[] = $file->getFilename();
            }
        }

        $this->assertEmpty(
            $violaciones,
            'Los siguientes componentes en components/ui no deben importar ni consultar modelos Eloquent: '.implode(', ', $violaciones)
        );
    }

    /**
     * Protege que los patrones visuales en components/patterns no conozcan modelos Eloquent ni ejecuten consultas.
     */
    public function test_components_patterns_no_importan_modelos(): void
    {
        $patternsPath = resource_path('views/components/patterns');
        $this->assertDirectoryExists($patternsPath, 'El directorio components/patterns debe existir.');

        $violaciones = [];

        foreach (File::files($patternsPath) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contenido = File::get($file->getPathname());

            if (preg_match('/App\\\\Models\\\\\w+/i', $contenido)
                || preg_match('/(?:Residente|User|Residente|Alerta|Turno|Atencion)::(?:query|where|find|all)/i', $contenido)) {
                $violaciones[] = $file->getFilename();
            }
        }

        $this->assertEmpty(
            $violaciones,
            'Los siguientes componentes en components/patterns no deben importar ni consultar modelos Eloquent: '.implode(', ', $violaciones)
        );
    }

    public function test_livewire_resuelve_vistas_del_modulo_propietario(): void
    {
        $componentes = 0;
        foreach (File::directories(app_path('Modulos')) as $modulo) {
            foreach (File::allFiles($modulo.'/Presentacion/Livewire') as $archivo) {
                if ($archivo->getExtension() !== 'php') {
                    continue;
                }
                $codigo = File::get($archivo->getPathname());
                preg_match('/namespace ([^;]+);/', $codigo, $namespace);
                preg_match('/class (\w+)/', $codigo, $clase);
                $reflexion = new \ReflectionClass($namespace[1].'\\'.$clase[1]);
                if ($reflexion->isAbstract()) {
                    continue;
                }
                $componentes++;
                $render = $reflexion->getMethod('render');
                $lineas = explode("\n", File::get($render->getFileName()));
                $codigoRender = implode("\n", array_slice($lineas, $render->getStartLine() - 1, $render->getEndLine() - $render->getStartLine() + 1));
                if (preg_match("/view\\(['\"]([^'\"]+)['\"]/", $codigoRender, $coincidencia)) {
                    $resuelta = view()->getFinder()->find($coincidencia[1]);
                    $this->assertFileExists($resuelta);
                    $this->assertStringStartsWith(str_replace('\\', '/', $modulo).'/Presentacion/Vistas/', str_replace('\\', '/', $resuelta));
                }
            }
        }
        // Incluye el nuevo panel de gobierno de reglas de ApoyoDecision.
        $this->assertSame(76, $componentes);
    }

    /**
     * Protege que el Design System canónico mantenga sus tokens y entrypoint importado en app.css.
     */
    public function test_design_system_mantiene_entrypoint_y_tokens_validos(): void
    {
        $dsIndex = resource_path('frontend/styles/design-system/index.css');
        $appCss = resource_path('frontend/styles/app.css');

        $this->assertFileExists($dsIndex, 'El entrypoint canónico design-system/index.css debe existir.');
        $this->assertFileExists($appCss, 'El archivo app.css debe existir.');

        $this->assertStringContainsString("@import '../../css/app.css'", File::get($appCss));
        $appCssContent = File::get(resource_path('css/app.css'));
        $this->assertStringContainsString(
            'design-system/index.css',
            $appCssContent,
            'app.css debe importar ./design-system/index.css como parte de la cascada oficial.'
        );

        $tokensRequeridos = [
            'colors.css',
            'typography.css',
            'spacing.css',
            'radius.css',
            'shadows.css',
            'states.css',
            'motion.css',
            'accessibility.css',
            'chart-colors.css',
        ];

        foreach ($tokensRequeridos as $tokenFile) {
            $tokenPath = resource_path("frontend/styles/design-system/tokens/{$tokenFile}");
            $this->assertFileExists($tokenPath, "El token canónico {$tokenFile} debe existir en el Design System.");
        }
    }

    public function test_paleta_oficial_se_centraliza_y_las_series_no_inventan_alertas(): void
    {
        $graficas = File::get(resource_path('frontend/styles/design-system/tokens/chart-colors.css'));
        $colores = File::get(resource_path('frontend/styles/design-system/tokens/colors.css'));
        $tailwind = File::get(base_path('tailwind.config.js'));

        foreach ([
            '--rm-palette-page: #D8D2CD',
            '--rm-earth-900: #5B514B',
            '--rm-earth-800: #6E625A',
            '--rm-earth-700: #7D6F65',
            '--rm-earth-600: #8F8075',
            '--rm-earth-500: #A49589',
            '--rm-earth-400: #B7AAA0',
            '--rm-earth-300: #A69E96',
            '--rm-palette-capuchino: var(--rm-palette-page)',
            '--rm-palette-header: var(--rm-palette-surface)',
            '--rm-selected-bg: var(--rm-primary-soft)',
            '--rm-icon-default: var(--rm-earth-600)',
            '--rm-palette-sidebar: var(--rm-palette-capuchino)',
            '--rm-palette-surface: #ECE9E6',
            '--rm-palette-surface-soft: #DFD9D4',
            '--rm-palette-ink: #4F4742',
            '--rm-palette-ink-secondary: #7D6F65',
            '--rm-palette-sage-900: var(--rm-palette-primary-strong)',
            '--rm-palette-primary: #7D9282',
            '--rm-palette-primary-ink: #354C3D',
            '--rm-palette-primary-soft: #C4D3C7',
            '--rm-palette-info: #7D94A8',
            '--rm-palette-psychology: #9B83BF',
            '--rm-palette-nutrition: var(--rm-palette-gold)',
            '--rm-palette-warning: #C7A56B',
            '--rm-palette-danger: #C97C73',
            '--rm-action-primary: var(--rm-primary)',
            '--rm-sidebar-open: var(--rm-sage-soft)',
            '--rm-selected-text: var(--rm-primary-ink)',
        ] as $token) {
            $this->assertStringContainsString($token, $colores);
        }

        $this->assertStringContainsString('--rm-chart-care-500: var(--rm-palette-primary)', $graficas);
        $this->assertStringContainsString('--rm-chart-clinical-500: var(--rm-palette-info)', $graficas);
        $this->assertStringContainsString('--rm-chart-alert-500: var(--rm-palette-coral)', $graficas);
        $this->assertStringContainsString('--rm-chart-1: var(--rm-chart-sage)', $graficas);
        $this->assertStringContainsString('--rm-chart-danger:       var(--rm-chart-alert-500)', $graficas);
        $this->assertStringContainsString('--rm-chart-token-medication-primary: var(--rm-chart-care-500)', $graficas);
        $this->assertStringContainsString('--rm-line-stroke-width: 3px', $graficas);
        $this->assertStringContainsString('--rm-donut-ring-width: 22px', $graficas);
        $this->assertDoesNotMatchRegularExpression('/--rm-chart-\\d+:.*var\\(--rm-(danger|warning)\\)/', $graficas);
        $this->assertStringContainsString("'primary-action': token('primary')", $tailwind);
        $this->assertDoesNotMatchRegularExpression('/#[a-fA-F0-9]{3,8}\\b/', $tailwind, 'La configuración debe consumir la paleta CSS, sin repetir HEX.');

        $puente = File::get(resource_path('frontend/styles/design-system/components/legacy-palette-bridge.css'));
        $this->assertStringContainsString('var(--rm-clinical-strong)', $puente);
        $this->assertStringContainsString('var(--rm-action-primary)', $puente);
        $this->assertStringContainsString('var(--rm-danger)', $puente);
    }

    public function test_escala_visual_global_respeta_el_contrato_compacto(): void
    {
        $layout = File::get(resource_path('frontend/styles/design-system/tokens/layout.css'));
        $spacing = File::get(resource_path('frontend/styles/design-system/tokens/spacing.css'));
        $typography = File::get(resource_path('frontend/styles/design-system/tokens/typography.css'));
        $sizing = File::get(resource_path('frontend/styles/design-system/tokens/sizing.css'));
        $density = File::get(resource_path('frontend/styles/design-system/patterns/system-density.css'));

        foreach ([
            '--rm-sidebar-width: 232px',
            '--rm-sidebar-collapsed: 76px',
            '--rm-topbar-height: 60px',
            '--rm-page-max-width: 1360px',
            '--rm-page-padding-x: 20px',
            '--rm-page-padding-y: 16px',
            '--rm-grid-gap: 12px',
            '--rm-page-header-height: 76px',
            '--rm-dashboard-hero-height: 172px',
            '--rm-panel-max-height: 300px',
        ] as $token) {
            $this->assertStringContainsString($token, $layout);
        }

        $this->assertStringContainsString('--rm-page-gap: var(--rm-space-5)', $spacing);
        $this->assertStringContainsString('--rm-card-padding: var(--rm-space-card)', $spacing);
        $this->assertStringContainsString('--rm-font-size-dashboard: 28px', $typography);
        $this->assertStringContainsString('--rm-font-size-section: 17px', $typography);
        $this->assertStringContainsString('--rm-font-size-card-title: 15px', $typography);
        $this->assertStringContainsString('--rm-font-size-body: 14px', $typography);
        $this->assertStringContainsString('--rm-font-size-meta: 12px', $typography);
        $this->assertStringContainsString('--rm-font-size-kpi: 25px', $typography);
        $this->assertStringContainsString('--rm-control-md: 40px', $sizing);
        $this->assertStringContainsString('[class~="text-5xl"]', $density);
        $this->assertStringContainsString('[class~="min-h-[400px]"]', $density);
        $this->assertStringContainsString('[class~="bg-white"]', $density);
    }

    /**
     * Protege que las primitivas UI normalizadas no contengan colores HEX hardcodeados.
     */
    public function test_componentes_ui_canonicos_no_contienen_colores_hex_hardcodeados(): void
    {
        $componentes = [
            'action-button.blade.php',
            'callout.blade.php',
            'choice-card.blade.php',
            'field.blade.php',
            'form-section.blade.php',
            'status-badge.blade.php',
            'section-card.blade.php',
            'filter-bar.blade.php',
            'page-header.blade.php',
        ];

        foreach ($componentes as $componente) {
            $path = resource_path("views/components/ui/{$componente}");
            $this->assertFileExists($path, "El componente UI {$componente} debe existir.");

            $contenido = File::get($path);
            $this->assertDoesNotMatchRegularExpression(
                '/#[0-9a-fA-F]{3,8}\b/',
                $contenido,
                "El componente UI {$componente} no debe contener colores HEX hardcodeados."
            );
        }
    }

    /**
     * Los formularios clínicos de referencia deben componer la interfaz desde
     * primitivas canónicas, sin reintroducir paletas particulares por módulo.
     */
    public function test_formularios_clinicos_de_referencia_consumen_componentes_canonicos(): void
    {
        $vistas = [
            'livewire/alertas/modales/crear.blade.php',
            'livewire/alertas/modales/atender.blade.php',
            'livewire/alertas/modales/cerrar.blade.php',
            'livewire/medicacion/administracion-medicacion-modal.blade.php',
            'livewire/clinica/registro-signos-vitales-modal.blade.php',
        ];

        foreach ($vistas as $vista) {
            $path = $this->rutaVistaArchivo($vista);
            $this->assertFileExists($path);

            $contenido = File::get($path);
            $this->assertStringContainsString('<x-ui.modal-livewire', $contenido, "{$vista} debe usar el modal canónico.");
            $this->assertStringContainsString('<x-ui.form-section', $contenido, "{$vista} debe usar secciones de formulario canónicas.");
            $this->assertStringContainsString('<x-ui.action-button', $contenido, "{$vista} debe usar acciones canónicas.");
            $this->assertDoesNotMatchRegularExpression(
                '/#[0-9a-fA-F]{3,8}\b/',
                $contenido,
                "{$vista} no debe contener colores HEX hardcodeados."
            );
        }
    }

    /**
     * Las tres pantallas maestras fijan el contrato visual para las futuras
     * migraciones: cabecera y acciones compartidas, sin colores locales.
     */
    public function test_pantallas_maestras_consumen_cabecera_y_acciones_canonicas(): void
    {
        $vistas = [
            'livewire/alertas/alertas-panel.blade.php',
            'livewire/medicacion/salud-medicacion.blade.php',
            'livewire/cuidados/ficha/cabecera.blade.php',
            'livewire/clinica/salud-signos-panel.blade.php',
            'livewire/clinica/salud-seguimiento-list-panel.blade.php',
        ];

        foreach ($vistas as $vista) {
            $path = $this->rutaVistaArchivo($vista);
            $contenido = File::get($path);

            $this->assertStringContainsString('<x-ui.page-header', $contenido, "{$vista} debe usar la cabecera canónica.");
            $this->assertStringContainsString('<x-ui.action-button', $contenido, "{$vista} debe usar acciones canónicas.");
            $this->assertDoesNotMatchRegularExpression(
                '/#[0-9a-fA-F]{3,8}\b/',
                $contenido,
                "{$vista} no debe contener colores HEX hardcodeados."
            );
        }

        $signos = File::get(base_path('app/Modulos/Clinica/Presentacion/Vistas/livewire/clinica/salud-signos-panel.blade.php'));
        $seguimiento = File::get(base_path('app/Modulos/Clinica/Presentacion/Vistas/livewire/clinica/salud-seguimiento-list-panel.blade.php'));
        $resumenFicha = $this->contenidoVistaConParciales('livewire.cuidados.ficha.tab-resumen');
        $ficha = File::get(base_path('app/Modulos/Clinica/Presentacion/Vistas/livewire/cuidados/ficha-paciente.blade.php'));
        $cabeceraFicha = File::get(base_path('app/Modulos/Clinica/Presentacion/Vistas/livewire/cuidados/ficha/cabecera.blade.php'));
        $this->assertStringContainsString('<x-ui.filter-bar', $signos, 'Signos vitales debe usar la barra de filtros canónica.');
        $this->assertStringContainsString("['card' =>", $signos, 'Los estados de signos vitales deben conservar clases de tarjeta y texto separadas.');
        $this->assertStringContainsString('rm-drawer-wide', $seguimiento, 'El expediente de seguimiento debe usar el drawer flotante ancho.');
        $this->assertStringContainsString('rm-clinical-summary-grid', $resumenFicha, 'El resumen de ficha debe usar la composición clínica canónica.');
        $this->assertStringContainsString('rm-clinical-card', $resumenFicha, 'El resumen de ficha debe usar cards clínicas canónicas.');
        $this->assertStringContainsString('rm-data-tile', $resumenFicha, 'Los datos de la ficha deben usar subpaneles canónicos.');
        $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b/', $resumenFicha, 'El resumen de ficha no debe contener colores HEX locales.');
        $this->assertStringNotContainsString('</a>', $resumenFicha, 'El resumen de ficha no debe conservar cierres de enlace inválidos.');
        $this->assertStringContainsString('$diagnosticosActivos', $ficha, 'La ficha debe preparar diagnósticos legibles para presentación.');
        $this->assertStringContainsString('$alergiasTexto', $ficha, 'La ficha debe preparar alergias legibles para presentación.');
        $this->assertStringNotContainsString('{{ $adultoMayor->diagnosticos }}', $resumenFicha, 'La ficha no debe serializar relaciones clínicas en la interfaz.');
        $this->assertStringNotContainsString('{{ $adultoMayor->alergias }}', $cabeceraFicha, 'La cabecera no debe serializar relaciones clínicas en la interfaz.');
    }

    public function test_layouts_y_overlays_comparten_profundidad_y_validacion_canonicas(): void
    {
        foreach (['layouts/sistema.blade.php', 'layouts/enfermeria.blade.php'] as $vista) {
            $contenido = File::get($this->rutaVistaArchivo($vista));
            $this->assertStringContainsString('rm-depth-canvas', $contenido, "{$vista} debe activar la profundidad ambiental canónica.");
        }

        $layoutEnfermeria = File::get(resource_path('views/layouts/enfermeria.blade.php'));
        $this->assertSame(1, substr_count($layoutEnfermeria, '@livewireStyles'), 'El layout de Enfermería debe cargar los estilos Livewire una sola vez.');
        $this->assertSame(1, substr_count($layoutEnfermeria, '<x-layout.livewire-runtime />'), 'El layout de Enfermería debe cargar el runtime Livewire una sola vez.');
        $this->assertSame(1, substr_count(File::get(resource_path('views/components/layout/livewire-runtime.blade.php')), '@livewireScripts'), 'El runtime compartido debe cargar los scripts Livewire una sola vez.');

        $modal = File::get(resource_path('views/components/ui/modal-livewire.blade.php'));
        $drawer = File::get(resource_path('views/components/ui/drawer-livewire.blade.php'));
        $validation = File::get(resource_path('views/components/validation-errors.blade.php'));
        $detalleAlerta = File::get(base_path('app/Modulos/Alertas/Presentacion/Vistas/livewire/alertas/modales/detalle.blade.php'));
        $panelAlertas = File::get(base_path('app/Modulos/Alertas/Presentacion/Vistas/livewire/alertas/alertas-panel.blade.php'));

        $this->assertStringContainsString('<x-validation-errors', $modal);
        $this->assertStringContainsString('rm-drawer', $drawer);
        $this->assertStringContainsString('aria-live="assertive"', $validation);
        $this->assertStringContainsString('rm-alert-danger', $validation);
        $this->assertStringContainsString('<x-ui.drawer-livewire', $detalleAlerta);
        $this->assertStringContainsString('<x-ui.status-badge', $panelAlertas);
        $this->assertStringContainsString('rm-alert-board__list', $panelAlertas);
        $this->assertStringContainsString('Detalle e historial', $panelAlertas);
        $this->assertStringNotContainsString('min-w-[980px]', $panelAlertas);
    }

    public function test_dashboards_por_rol_comparten_hero_editorial_y_fotografia_institucional(): void
    {
        $componente = File::get(resource_path('views/components/ui/role-dashboard-hero.blade.php'));
        $encabezadoAdmin = File::get(resource_path('views/components/ui/encabezado-dashboard.blade.php'));
        $enfermeria = $this->contenidoVistaConParciales('livewire.cuidados.dashboard-turno');
        $medicina = File::get(base_path('app/Modulos/Clinica/Presentacion/Vistas/livewire/clinica/dashboard-medico.blade.php'));
        $psicologia = File::get(base_path('app/Modulos/Valoraciones/Presentacion/Vistas/livewire/valoraciones/dashboard-psicologo.blade.php'));
        $workspaceRol = File::get(resource_path('views/components/ui/role-workspace-dashboard.blade.php'));

        $this->assertStringContainsString('<x-ui.dashboard-header', $componente);
        $this->assertStringContainsString('<x-ui.role-dashboard-hero', $encabezadoAdmin);
        $this->assertStringContainsString('<x-ui.dashboard-welcome-header', $enfermeria);
        $this->assertStringNotContainsString('rm-role-hero', $enfermeria);
        $this->assertStringContainsString('<x-ui.role-dashboard-hero', $medicina);
        $this->assertStringContainsString('<x-ui.role-dashboard-hero', $psicologia);
        $this->assertStringContainsString('<x-ui.role-dashboard-hero', $workspaceRol);
        $this->assertStringContainsString('<x-ui.dashboard-header', File::get(resource_path('views/components/ui/dashboard-welcome-header.blade.php')));
        $this->assertStringContainsString('<x-ui.dashboard-header', File::get(base_path('app/Modulos/Institucion/Presentacion/Vistas/paginas/admin/administracion/dashboard.blade.php')));
        $this->assertStringContainsString('<x-ui.dashboard-data-panel', $workspaceRol);

        $this->assertStringNotContainsString(':compact="true"', $enfermeria);
        $this->assertStringContainsString('<x-ui.dashboard-divider />', $enfermeria);
        foreach ([$workspaceRol, $medicina, $psicologia, File::get(base_path('app/Modulos/Institucion/Presentacion/Vistas/paginas/admin/administracion/dashboard.blade.php'))] as $dashboard) {
            $this->assertStringContainsString('<x-ui.dashboard-divider />', $dashboard);
        }

        foreach ([$encabezadoAdmin, $medicina, $psicologia] as $dashboard) {
            $this->assertStringContainsString('images/FOTOS CENTRO DE ADULTOS MAYORES/', $dashboard);
        }
        $this->assertStringContainsString('image="images/FOTOS CENTRO DE ADULTOS MAYORES/489963938_1158744422930145_8442506970304201426_n.jpg"', $enfermeria);
        $this->assertStringContainsString('rotation-context=', $medicina);
        $this->assertStringContainsString('rotation-context=', $psicologia);
        $this->assertStringContainsString('rotation-context=', $workspaceRol);
        $this->assertStringContainsString('images/FOTOS CENTRO DE ADULTOS MAYORES/', File::get(app_path('Modulos/Reportes/Presentacion/DashboardPhotoRotation.php')));

        $this->assertStringContainsString('<x-ui.metric-card', $medicina);
        $this->assertStringContainsString('<x-ui.metric-card', $psicologia);

        $servicio = File::get(app_path('Modulos/Reportes/Aplicacion/Consultas/DashboardService.php'));
        foreach (['NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO', 'FAMILIAR'] as $rol) {
            $this->assertStringContainsString("'{$rol}' => [", $servicio);
        }
    }

    public function test_graficas_del_sistema_comparten_tema_tokens_y_contenedor_canonico(): void
    {
        $app = File::get(resource_path('frontend/scripts/app.js'));
        $tema = File::get(resource_path('frontend/styles/design-system/charts/chart-theme.js'));
        $estilos = File::get(resource_path('frontend/styles/design-system/components/charts.css'));
        $colores = File::get(resource_path('frontend/styles/design-system/tokens/chart-colors.css'));

        $this->assertStringContainsString('rmInstallGlobalChartTheme(Chart)', $app);
        $this->assertStringContainsString('export function rmInstallGlobalChartTheme', $tema);
        $this->assertStringContainsString("rmGetCss(meta.token) || rmGetCss('--rm-info')", $tema);
        $this->assertStringContainsString('.rm-chart-card', $estilos);
        $this->assertStringContainsString('.rm-chart-header', $estilos);
        $this->assertStringContainsString('.rm-chart-kpi-badge', $estilos);
        $this->assertStringContainsString('.rm-chart-empty', $estilos);
        $this->assertStringContainsString("id: 'rmSoftChartGlow'", $tema);
        $this->assertStringContainsString('Chart.register(rmSoftChartGlowPlugin, rmReducedMotionChartPlugin, rmSemanticMotionPlugin)', $tema);
        $this->assertStringContainsString('export function rmChartColor', $tema);
        $this->assertStringContainsString('export function rmChartNumber', $tema);
        $this->assertStringContainsString('--rm-chart-card-bg', $colores);
        $this->assertStringContainsString('--rm-chart-tooltip-bg', $colores);
        $this->assertStringContainsString('background: linear-gradient(180deg, var(--rm-chart-card-glass), var(--rm-chart-card-bg))', $estilos);
        $this->assertStringContainsString('[data-theme="dark"]', $estilos);

        $vistasConGraficas = [
            'livewire/alertas/alertas-panel.blade.php',
            'livewire/alertas/modales/drawer-graficos.blade.php',
            'livewire/clinica/salud-signos-panel.blade.php',
            'livewire/clinica/signos-vitales-panel.blade.php',
            'livewire/cuidados/dashboard-turno.blade.php',
            'livewire/cuidados/ficha/tab-estudios.blade.php',
            'livewire/cuidados/ficha/tab-eventos.blade.php',
            'livewire/cuidados/ficha/tab-seguimiento.blade.php',
            'livewire/cuidados/ficha/tab-signos.blade.php',
            'livewire/identidad/areas-institucionales-panel.blade.php',
            'livewire/identidad/personal-institucional-panel.blade.php',
            'livewire/reportes/reportes-adulto-panel.blade.php',
            'livewire/reportes/reportes-institucionales-panel.blade.php',
            'pages/adultos-mayores/show/carpetas/_reportes.blade.php',
            'pages/familia-social/resumen.blade.php',
            'pages/reportes/adultos/index.blade.php',
        ];

        foreach ($vistasConGraficas as $vista) {
            $contenido = $this->contenidoVistaConParciales($vista);
            $this->assertStringContainsString(
                'rm-chart-card',
                $contenido,
                "{$vista} debe usar el contenedor canónico de gráficas."
            );
        }
    }

    public function test_landing_prioriza_identidad_geriatrica_e_iconografia_medica(): void
    {
        $vista = File::get(resource_path('views/pages/welcome.blade.php'));
        $estilos = File::get(resource_path('frontend/styles/modules/pages-welcome.css'));

        $this->assertStringContainsString('Centro geriátrico · Atención integral', $vista);
        $this->assertStringContainsString('Centro Geriátrico Los Almendros', $vista);
        $this->assertStringNotContainsString('Jardín de los Recuerdos', $vista);
        $this->assertStringContainsString('storage/imagenes/LOGO.png', $vista);
        $this->assertStringContainsString('ph-stethoscope', $vista);
        $this->assertStringContainsString('ph-heartbeat', $vista);
        $this->assertStringContainsString('ph-first-aid', $vista);
        $this->assertStringContainsString('ph-shield-check', $vista);
        $this->assertStringNotContainsString('ph-leaf', $vista);
        $this->assertStringNotContainsString('ph-flower', $vista);
        $this->assertDoesNotMatchRegularExpression('/[🌿🌱🌸🍃🪴]/u', $vista);
        $this->assertStringContainsString('--garden-coral:', $estilos);
        $this->assertStringContainsString('--garden-coral-soft:', $estilos);
        $this->assertStringContainsString('data-cursor-light', $vista);
        $this->assertStringContainsString('.welcome-cursor-light', $estilos);
        $this->assertStringContainsString('filter: blur(20px)', $estilos);

        $interacciones = File::get(resource_path('frontend/scripts/modules/pages-welcome-3.js'));
        $this->assertStringContainsString("window.addEventListener('pointermove'", $interacciones);
        $this->assertStringContainsString("matchMedia('(hover: hover) and (pointer: fine)')", $interacciones);
    }

    public function test_enfermeria_comparte_workspace_clinico_y_formularios_completos(): void
    {
        $indice = File::get(resource_path('frontend/styles/design-system/index.css'));
        $workspace = File::get(resource_path('frontend/styles/design-system/patterns/nursing-module.css'));
        $layout = File::get(resource_path('views/layouts/enfermeria.blade.php'));
        $seguimiento = File::get(base_path('app/Modulos/Cuidados/Presentacion/Vistas/livewire/cuidados/seguimiento-diario-panel.blade.php'));
        $medicacion = File::get(base_path('app/Modulos/Medicacion/Presentacion/Vistas/livewire/medicacion/administracion-medicacion-modal.blade.php'));

        $this->assertStringContainsString("@import './patterns/nursing-module.css'", $indice);
        $this->assertStringContainsString('rm-nursing-shell', $layout);
        $this->assertStringContainsString('.rm-clinical-resident-context', $workspace);
        $this->assertStringContainsString('.rm-clinical-timeline', $workspace);
        $this->assertStringContainsString('.rm-clinical-readonly-order', $workspace);
        $this->assertStringContainsString('wire:model="modalForm"', $seguimiento);
        $this->assertStringContainsString('Control cognitivo observacional', $seguimiento);
        $this->assertStringContainsString('No constituye diagnóstico cognitivo', $seguimiento);
        $this->assertStringContainsString('Orden médica · solo lectura', $medicacion);
        $this->assertStringContainsString('wire:model.live.debounce.300ms="dosis_administrada"', $medicacion);
        $this->assertStringContainsString('wire:model.live.debounce.400ms="reaccion_adversa"', $medicacion);

        foreach (File::files(base_path('app/Modulos/Cuidados/Presentacion/Vistas/livewire/cuidados')) as $vista) {
            $this->assertStringNotContainsString('[[var(', File::get($vista->getPathname()), $vista->getFilename().' contiene un token Tailwind mal formado.');
        }
    }

    private function contenidoVistaConParciales(string $vista): string
    {
        $contenido = File::get($this->rutaVistaArchivo($vista));
        preg_match_all("/@include\\('([^']+)'/", $contenido, $parciales);
        foreach ($parciales[1] as $parcial) {
            $contenido .= $this->contenidoVistaConParciales($parcial);
        }

        return $contenido;
    }
}
