import os
import re
import glob

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content

    # 1. Exact strings requested by user
    content = re.sub(r'\bbg-white\b', 'bg-fondo-card', content)
    content = re.sub(r'\bbg-terracota-dark\b', 'bg-boton-acentoHover', content)
    content = re.sub(r'hover:bg-terracota-dark', 'hover:bg-boton-acentoHover', content)
    content = re.sub(r'\bbg-terracota\b', 'bg-boton-acento', content)
    content = re.sub(r'\bbg-azul-profundo\b', 'bg-boton-principal', content)
    content = re.sub(r'\btext-azul-profundo\b', 'text-titulo', content)

    # 2. Text colors with hex
    def replace_text_hex(match):
        hex_code = match.group(1).upper()
        opacity = match.group(2)
        if hex_code == '2F3E5C':
            if opacity: return 'text-apoyo'
            return 'text-titulo'
        elif hex_code == 'E27D60':
            return 'text-boton-acento'
        elif hex_code in ['8DA280', '63775B']:
            return 'text-estado-exito'
        elif hex_code in ['D9A05B', '9A6B2E']:
            return 'text-estado-advertencia'
        elif hex_code in ['D5C7B9', '7C7168', 'C7B5A3']:
            return 'text-meta'
        elif hex_code == 'FFFDF9' or hex_code == 'FFFFFF':
            if opacity: return 'text-inverso opacity-70'
            return 'text-inverso'
        else:
            if opacity: return 'text-apoyo'
            return 'text-parrafo'
    
    content = re.sub(r'text-\[#([a-fA-F0-9]{6})\](?:/([0-9]+))?', replace_text_hex, content)

    # 3. Background colors with hex
    def replace_bg_hex(match):
        hex_code = match.group(1).upper()
        opacity = match.group(2)
        if hex_code == '2F3E5C':
            if opacity: return 'bg-fondo-panel'
            return 'bg-boton-principal'
        elif hex_code == 'E27D60':
            if opacity: return 'bg-estado-peligroBg'
            return 'bg-boton-acento'
        elif hex_code == '8DA280':
            if opacity: return 'bg-estado-exitoBg'
            return 'bg-estado-exitoBg'
        elif hex_code == 'D9A05B':
            if opacity: return 'bg-estado-advertenciaBg'
            return 'bg-estado-advertenciaBg'
        elif hex_code in ['F8F3ED', 'E6DDD3', 'F3ECE4', 'D5C7B9', 'FAF6EF', 'F7F5F2', 'F9F9F9']:
            if opacity: return 'bg-fondo-panel'
            return 'bg-fondo-app'
        elif hex_code == 'FFFDF9':
            return 'bg-fondo-card'
        else:
            return 'bg-fondo-panel'

    content = re.sub(r'bg-\[#([a-fA-F0-9]{6})\](?:/([0-9]+))?', replace_bg_hex, content)

    # 4. Border colors with hex
    def replace_border_hex(match):
        hex_code = match.group(1).upper()
        if hex_code == 'E27D60':
            return 'border-borde-focus'
        elif hex_code == '2F3E5C':
            return 'border-borde-fuerte'
        elif hex_code in ['8DA280', '63775B']:
            return 'border-estado-exitoBorde'
        elif hex_code in ['D9A05B', '9A6B2E']:
            return 'border-estado-advertenciaBorde'
        elif hex_code in ['D5C7B9', 'C7B5A3', 'E6DDD3']:
            return 'border-borde-suave'
        else:
            return 'border-borde'

    content = re.sub(r'border-\[#([a-fA-F0-9]{6})\](?:/[0-9]+)?', replace_border_hex, content)

    # 5. Generic gray rules
    content = re.sub(r'\btext-gray-[0-9]+\b', 'text-apoyo', content)
    content = re.sub(r'\bbg-gray-[0-9]+\b', 'bg-fondo-panel', content)
    content = re.sub(r'\bborder-gray-[0-9]+\b', 'border-borde-suave', content)

    # Forms focus
    content = re.sub(r'focus:border-terracota', 'focus:border-borde-focus', content)
    content = re.sub(r'focus:ring-terracota', 'focus:ring-borde-focus', content)

    # 6. Some leftovers like text-white (but careful not to break text-white inside buttons unless it's text-inverso)
    content = re.sub(r'\btext-white\b', 'text-inverso', content)

    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated: {filepath}")

for filepath in glob.glob('resources/views/**/*.blade.php', recursive=True):
    if os.path.isfile(filepath):
        process_file(filepath)
