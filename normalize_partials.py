import os
import re

base_dir = r'c:\laragon\www\RememberMind_F1\resources\views\admin\adultos-mayores\show'
files = [os.path.join(base_dir, f) for f in os.listdir(base_dir) if f.endswith('.blade.php')]
files.append(r'c:\laragon\www\RememberMind_F1\resources\views\admin\adultos-mayores\show.blade.php')

# Also include the files that are directly in show folder
files_in_show = [f for f in os.listdir(r'c:\laragon\www\RememberMind_F1\resources\views\admin\adultos-mayores\show') if f.endswith('.blade.php')]

replacements = {
    # Variable fallbacks for collections
    r'\$familiaresActivos': r'($familiaresLista ?? collect())',
    r'\$familiaresInactivos': r'($familiaresInactivosLista ?? collect())',
    r'\$observacionesActivas': r'($observacionesLista ?? collect())',
    r'\$observacionesAnuladas': r'($observacionesAnuladasLista ?? collect())',
    r'\$atencionesActivas': r'($atencionesLista ?? collect())',
    r'\$atencionesAnuladas': r'($atencionesAnuladasLista ?? collect())',
    r'\$actividadesActivas': r'($actividadesLista ?? collect())',
    r'\$actividadesAnuladas': r'($actividadesAnuladasLista ?? collect())',
    r'\$documentosActivos': r'($documentosLista ?? collect())',
    r'\$documentosArchivados': r'($documentosArchivadosLista ?? collect())',
    r'\$evaluacionesActivas': r'($evaluacionesLista ?? collect())',
    r'\$evaluacionesAnuladas': r'($evaluacionesAnuladasLista ?? collect())',
    r'\$asignaciones': r'($asignacionesLista ?? collect())',
    r'\$fichasMedicas': r'($fichasMedicasLista ?? collect())',
    r'\$medicaciones': r'($medicacionesLista ?? collect())',
    r'\$administracionesMedicacion': r'($administracionesMedicacionLista ?? collect())',
    r'\$signosVitales': r'($signosVitalesLista ?? collect())',
    r'\$valoracionesFuncionales': r'($valoracionesFuncionalesLista ?? collect())',
    r'\$historialEstados': r'($historialEstadosLista ?? collect())',
    r'\$bitacora': r'($bitacoraLista ?? collect())',
    r'\$eventosFiltro': r'($eventosFiltroLista ?? collect())',
}

# Routes to protect with Route::has
routes_to_protect = [
    'admin.adultos-mayores.familiares',
    'admin.adultos-mayores.observaciones',
    'admin.adultos-mayores.atenciones',
    'admin.adultos-mayores.evaluaciones',
    'admin.adultos-mayores.actividades',
    'admin.adultos-mayores.documentos',
    'admin.adultos-mayores.ficha-medica',
    'admin.adultos-mayores.medicacion',
    'admin.adultos-mayores.administracion-medicacion',
    'admin.adultos-mayores.signos-vitales',
    'admin.adultos-mayores.valoracion-funcional',
    'admin.adultos-mayores.reporte-individual',
    'admin.adultos-mayores.reportes.especifico'
]

def protect_routes(content):
    # This regex finds all route('...') and wraps the entire enclosing tag or href if it's not protected?
    # Actually, the user asked to use Route::has() for any route that might not exist.
    # It's safer to just replace route('...') with Route::has('...') ? route('...') : '#' for the specific routes.
    
    for route_prefix in routes_to_protect:
        # Match route('prefix.something', ...)
        # It's easier to do this safely by matching exactly route('admin.adultos-mayores...')
        pattern = r"(route\(['\"](" + route_prefix.replace('.', r'\.') + r"[^'\"]*)['\"](?:,\s*[^)]+)?\))"
        # We need to be careful not to double protect.
        # If it's already protected like Route::has(...) ? route(...) : '#' we skip
        
        def replace_route(match):
            full_call = match.group(1)
            route_name = match.group(2)
            return f"(Route::has('{route_name}') ? {full_call} : '#')"

        # Only replace if not preceded by `?` or `Route::has`
        # Simple string replacement iteration:
        matches = list(re.finditer(pattern, content))
        # reverse to not mess up indices
        for match in reversed(matches):
            start = match.start(1)
            # check if previously has `? ` or `has(`
            context_before = content[max(0, start-20):start]
            if "Route::has" not in context_before and "? " not in context_before:
                full_call = match.group(1)
                route_name = match.group(2)
                replacement = f"(Route::has('{route_name}') ? {full_call} : '#')"
                content = content[:start] + replacement + content[match.end(1):]

    return content

for filepath in files:
    if os.path.isfile(filepath):
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        # We don't want to replace variables where they are being defined in show.blade.php
        # e.g., $familiaresLista = ... collect($familiaresActivos)
        if 'show.blade.php' in filepath:
            # Only protect routes in show.blade.php
            new_content = protect_routes(content)
            # Also, change `evaluacionesGeriatricasActivas` safely.
            new_content = new_content.replace('$evaluacionesGeriatricasActivas', '($evaluacionesGeriatricasActivas ?? collect())')
        else:
            new_content = content
            for old_var, new_var in replacements.items():
                # Avoid double replacements if we already ran it
                if new_var not in new_content:
                    # We only replace if it's not the left side of an assignment, but blade files usually just read.
                    # Wait, in _resumen.blade.php, there's `foreach ($familiaresActivos as $fam)`
                    # If we replace $familiaresActivos with ($familiaresLista ?? collect()), it becomes `foreach (($familiaresLista ?? collect()) as $fam)` which is valid PHP.
                    new_content = re.sub(old_var + r'(?!\s*=)', new_var, new_content)
            
            new_content = protect_routes(new_content)
            
            # Additional check: $evaluacionesGeriatricasActivas
            if '$evaluacionesGeriatricasActivas' in new_content and '($evaluacionesGeriatricasActivas ?? collect())' not in new_content:
                 new_content = re.sub(r'\$evaluacionesGeriatricasActivas(?!\s*=)', r'($evaluacionesGeriatricasActivas ?? collect())', new_content)

        if new_content != content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f"Updated {filepath}")

print("Done.")
