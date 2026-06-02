import os
import re

base_dir = r'c:\laragon\www\RememberMind_F1\resources\views\admin\adultos-mayores\show'

def strip_tab_wrapping(filename):
    filepath = os.path.join(base_dir, filename)
    if not os.path.exists(filepath): return
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Look for `<section \n x-show="tab === '...'"` or similar
    pattern = r'<section[^>]*x-show="tab === [^>]+>'
    
    def replacer(match):
        # We just return a simple <div class="space-y-6"> or similar, or just <section class="space-y-6">
        # wait, let's just strip x-show, x-transition, style="display: none;"
        s = match.group(0)
        s = re.sub(r'x-show="tab === \'[^\']+\'"', '', s)
        s = re.sub(r'style="display:\s*none;?"', '', s)
        s = re.sub(r'x-transition\.opacity\.duration\.\d+ms', '', s)
        s = re.sub(r'x-transition', '', s)
        return s

    new_content = re.sub(pattern, replacer, content)
    
    if new_content != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Stripped tab wrapping from {filename}")

strip_tab_wrapping('_seguimiento-observaciones.blade.php')
strip_tab_wrapping('_actividades.blade.php')
strip_tab_wrapping('_atenciones.blade.php')
strip_tab_wrapping('_salud-medica.blade.php')
strip_tab_wrapping('_evaluaciones-cognitivas.blade.php')
strip_tab_wrapping('_resumen.blade.php')
strip_tab_wrapping('_familiares.blade.php')
strip_tab_wrapping('_documentos.blade.php')
strip_tab_wrapping('_reportes.blade.php')

# For show.blade.php, include all 3 in the seguimiento tab
show_path = r'c:\laragon\www\RememberMind_F1\resources\views\admin\adultos-mayores\show.blade.php'
with open(show_path, 'r', encoding='utf-8') as f:
    show_content = f.read()

seguimiento_block = """ <div x-show="tab === 'seguimiento'" x-transition style="display: none;" class="space-y-6">
 @include('admin.adultos-mayores.show._seguimiento-observaciones')
 @include('admin.adultos-mayores.show._atenciones')
 @include('admin.adultos-mayores.show._actividades')
 </div>"""

# Replace the old seguimiento block
old_seguimiento = r"<div x-show=\"tab === 'seguimiento'\" x-transition style=\"display: none;\">\s*@include\('admin\.adultos-mayores\.show\._seguimiento-observaciones'\)\s*</div>"
show_content = re.sub(old_seguimiento, seguimiento_block, show_content)

with open(show_path, 'w', encoding='utf-8') as f:
    f.write(show_content)
print("Updated show.blade.php to include atenciones and actividades in seguimiento tab")
