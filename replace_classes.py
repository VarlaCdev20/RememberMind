import os
import re
import glob

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content

    # Backgrounds
    content = re.sub(r'\bbg-white\b', 'bg-fondo-card', content)
    content = re.sub(r'bg-\[#F7F5F2\]', 'bg-fondo-panel', content)
    content = re.sub(r'bg-\[#F9F9F9\]', 'bg-fondo-app', content)
    content = re.sub(r'bg-\[#FAF7F3\]', 'bg-fondo-panel', content)
    content = re.sub(r'bg-\[#F6B08A\](?:/[0-9]+)?', 'bg-fondo-card-calido', content)
    content = re.sub(r'\bhofver:bg-terracota-dark\b', 'hover:bg-boton-acentoHover', content)
    content = re.sub(r'\bbg-terracota\b', 'bg-boton-acento', content)
    content = re.sub(r'\bbg-azul-profundo\b', 'bg-boton-principal', content)
    
    # Texts
    content = re.sub(r'\btext-white\b', 'text-inverso', content)
    content = re.sub(r'text-\[#2F3E5C\]/60', 'text-apoyo', content)
    content = re.sub(r'text-\[#2F3E5C\]/50', 'text-meta', content)
    content = re.sub(r'text-\[#2F3E5C\]/40', 'text-meta', content)
    content = re.sub(r'text-\[#2F3E5C\]/70', 'text-apoyo', content)
    content = re.sub(r'text-\[#2F3E5C\]', 'text-parrafo', content)
    content = re.sub(r'text-azul-profundo/60', 'text-apoyo', content)
    content = re.sub(r'text-azul-profundo/50', 'text-meta', content)
    content = re.sub(r'text-azul-profundo/40', 'text-meta', content)
    content = re.sub(r'text-azul-profundo/70', 'text-apoyo', content)
    content = re.sub(r'\btext-azul-profundo\b', 'text-titulo', content)
    content = re.sub(r'\btext-terracota\b', 'text-boton-acento', content)

    # Borders
    content = re.sub(r'border-\[#C7B5A3\]/50', 'border-borde-suave', content)
    content = re.sub(r'border-\[#C7B5A3\]/40', 'border-borde-suave', content)
    content = re.sub(r'border-\[#C7B5A3\]/30', 'border-borde-suave', content)
    content = re.sub(r'border-\[#C7B5A3\]/20', 'border-borde-suave', content)
    content = re.sub(r'border-\[#C7B5A3\]/60', 'border-borde', content)
    content = re.sub(r'border-\[#C7B5A3\]', 'border-borde', content)

    # Generic gray rules requested by user
    content = re.sub(r'\btext-gray-[0-9]+\b', 'text-apoyo', content)
    content = re.sub(r'\bbg-gray-[0-9]+\b', 'bg-fondo-panel', content)
    content = re.sub(r'\bborder-gray-[0-9]+\b', 'border-borde-suave', content)

    # Specific colors mapped to semantic states
    content = re.sub(r'\btext-emerald-[0-9]+\b', 'text-estado-exito', content)
    content = re.sub(r'\btext-amber-[0-9]+\b', 'text-estado-advertencia', content)
    content = re.sub(r'\btext-rose-[0-9]+\b', 'text-estado-peligro', content)
    content = re.sub(r'text-\[#5B5F97\]', 'text-estado-info', content)

    # Forms
    content = re.sub(r'focus:border-terracota', 'focus:border-borde-focus', content)
    content = re.sub(r'focus:ring-terracota', 'focus:ring-borde-focus', content)

    # Re-apply print rules
    content = re.sub(r'print:bg-fondo-card', 'print:bg-white', content)
    content = re.sub(r'print:text-titulo', 'print:text-black', content)

    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated: {filepath}")

patterns = [
    'resources/views/livewire/admin/adultos-mayores/reportes-institucionales-panel.blade.php',
    'resources/views/livewire/admin/salud-seguimiento/**/*.blade.php',
    'resources/views/livewire/admin/usuarios/**/*.blade.php',
    'resources/views/layouts/**/*.blade.php',
    'resources/views/navigation-menu.blade.php',
    'resources/views/components/**/*.blade.php',
]

for pattern in patterns:
    for filepath in glob.glob(pattern, recursive=True):
        if os.path.isfile(filepath):
            process_file(filepath)
