import re

# Read original
with open('resources/views/livewire/admin/admisiones/preadmision-wizard.blade.php', 'r') as f:
    content = f.read()

# Revert the {{ $field }} errors if any
content = re.sub(r"\s*@error\('\{\{ \$field \}\}'\).*?@enderror", '', content)
# Revert other added errors to start fresh
content = re.sub(r"\s*@error\('[^']+'\).*?@enderror", '', content)

# Remove the general error block if it's there (we already did, but just in case)
content = re.sub(
    r'@if \(\$errors->any\(\)\).*?@endif\n\n',
    '',
    content,
    flags=re.DOTALL
)

# Function to replace inputs with proper error classes and append error blocks
def process_element(match):
    before = match.group(1)
    field_expr = match.group(2) # e.g. "{{ $field }}" or "nombres"
    classes = match.group(3)
    after = match.group(4)
    
    # Is it a blade expression or static?
    if field_expr.startswith('{{') and field_expr.endswith('}}'):
        blade_field = field_expr[2:-2].strip() # "$field"
        error_directive = f"@error({blade_field})"
    else:
        error_directive = f"@error('{field_expr}')"
        
    # Replace the border/focus classes
    # Original usually has: "border border-input-borde bg-input-bg px-3 py-2 text-sm text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus"
    # We want to remove 'border-input-borde', 'focus:border-input-bordeFocus', 'focus:ring-input-ringFocus', 'border-borde-suave'
    new_classes = re.sub(r'\bborder-input-borde\b|\bfocus:border-input-bordeFocus\b|\bfocus:ring-input-ringFocus\b|\bborder-borde-suave\b', '', classes)
    new_classes = re.sub(r'\s+', ' ', new_classes).strip()
    
    # Add dynamic blade error classes
    error_classes = f"{error_directive} border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro @else border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus @enderror"
    
    # Combine
    final_classes = f"{new_classes} {error_classes}"
    
    return f'{before}wire:model="{field_expr}" class="{final_classes}"{after}'

# Wait, `wire:model` might have `.blur` or `.live`.
def process_element_robust(match):
    full_match = match.group(0)
    tag = match.group(1)
    
    # Extract field name
    field_match = re.search(r'wire:model(?:\.blur|\.live)?="([^"]+)"', full_match)
    if not field_match:
        return full_match
    
    field_expr = field_match.group(1)
    if field_expr.startswith('{{') and field_expr.endswith('}}'):
        blade_field = field_expr[2:-2].strip() # "$field"
        error_directive = f"@error({blade_field})"
    else:
        error_directive = f"@error('{field_expr}')"
        
    # Extract class attribute
    class_match = re.search(r'class="([^"]+)"', full_match)
    if not class_match:
        return full_match
        
    classes = class_match.group(1)
    
    new_classes = re.sub(r'\bborder-input-borde\b|\bfocus:border-input-bordeFocus\b|\bfocus:ring-input-ringFocus\b|\bborder-borde-suave\b', '', classes)
    new_classes = re.sub(r'\s+', ' ', new_classes).strip()
    
    # Some inputs don't have focus rings (like files), but we can just apply the standard if it had border-input-borde
    if 'border-borde-suave' in classes:
        error_classes = f"{error_directive} border-estado-peligro @else border-borde-suave @enderror"
    else:
        error_classes = f"{error_directive} border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro @else border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus @enderror"
        
    final_classes = f"{new_classes} {error_classes}"
    
    # Replace class in full_match
    # We use a simple replace because we know the class string
    new_match = full_match.replace(f'class="{classes}"', f'class="{final_classes}"')
    
    return new_match

# Process all inputs, selects, textareas
pattern = r'<(input|select|textarea)[^>]+wire:model[^>]+>'
content = re.sub(pattern, process_element_robust, content)

# Now insert the error spans. We can do this line by line like before, but correctly handling field_expr
lines = content.split('\n')
new_lines = []
current_field = None

for line in lines:
    match = re.search(r'wire:model(?:\.blur|\.live)?="([^"]+)"', line)
    if match:
        current_field = match.group(1)
        
    if '</div>' in line and current_field:
        # Check if field_expr is blade or string
        if current_field.startswith('{{') and current_field.endswith('}}'):
            blade_field = current_field[2:-2].strip()
            error_span = f"                            @error({blade_field}) <span class=\"mt-1 block text-[11px] font-bold text-estado-peligro\">{{{{ $message }}}}</span> @enderror"
        else:
            error_span = f"                            @error('{current_field}') <span class=\"mt-1 block text-[11px] font-bold text-estado-peligro\">{{{{ $message }}}}</span> @enderror"
            
        new_lines.append(error_span)
        current_field = None
        
    new_lines.append(line)

content = '\n'.join(new_lines)

# Enfenmero_id radio buttons special case:
content = re.sub(
    r'(<div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">.*?)(</div>\s*<div class="rounded-lg border border-borde bg-fondo-panel p-4">)',
    r'\1    @error("enfermero_id") <span class="mt-2 block text-sm font-bold text-estado-peligro">{{ $message }}</span> @enderror\n                \2',
    content,
    flags=re.DOTALL
)

with open('resources/views/livewire/admin/admisiones/preadmision-wizard.blade.php', 'w') as f:
    f.write(content)
