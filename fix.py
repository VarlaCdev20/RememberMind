import re

with open('resources/views/livewire/admin/admisiones/preadmision-wizard.blade.php', 'r') as f:
    content = f.read()

# 1. Remove global error block
content = re.sub(
    r'@if \(\$errors->any\(\)\).*?@endif\n\n',
    '',
    content,
    flags=re.DOTALL
)

# 2. Improve dimensions
content = content.replace('max-w-4xl', 'max-w-3xl')

# 3. Handle inputs with @error class injection and message span
def process_field(match):
    full_match = match.group(0)
    
    # Extract the field name
    field_match = re.search(r'wire:model(?:\.blur|\.live)?="([^"]+)"', full_match)
    if not field_match:
        return full_match
        
    field = field_match.group(1)
    
    # Add error class handling
    if 'class="' in full_match:
        # Remove standard border classes to replace them conditionally
        new_match = re.sub(
            r'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus',
            f"@error('{field}') border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro @else border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus @enderror",
            full_match
        )
    else:
        new_match = full_match
        
    # We append the error message.
    # To append correctly, we need to add it AFTER the closing tag of input, select or textarea.
    # However, replacing it directly is easier if we match the closing div of the wrapper, but that's complex.
    # Let's just append it after the matched block (which is the opening tag for select/textarea, or self-closing input).
    # Wait, for select/textarea, the error message would end up inside the tag if we just append.
    # Let's just find the closing tag and append after it.
    
    return new_match

# Since regex parsing HTML is fragile, let's just do a simpler search/replace for the class string
# and then add the @error block before the closing </div> of each field container.

# Wait, each field is wrapped in a <div>.
def insert_errors(content):
    lines = content.split('\n')
    new_lines = []
    
    current_field = None
    
    for line in lines:
        # Detect field
        match = re.search(r'wire:model(?:\.blur|\.live)?="([^"]+)"', line)
        if match:
            current_field = match.group(1)
            
            # Replace class conditionally
            if 'border-input-borde' in line:
                line = line.replace(
                    'border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus',
                    f"@error('{current_field}') border-estado-peligro focus:border-estado-peligro focus:ring-estado-peligro @else border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus @enderror"
                )
            elif 'border-borde-suave' in line: # Used for file inputs
                line = line.replace(
                    'border-borde-suave',
                    f"@error('{current_field}') border-estado-peligro @else border-borde-suave @enderror"
                )
        
        # When we hit a closing div and we have a current_field, we insert the error message BEFORE the closing div.
        # But wait, selects have </select> and then </div>. 
        # So we can just detect "</div>" if we have a current_field and it's the matching level.
        # A simpler heuristic: if line matches "</div>" and we have a current field:
        if '</div>' in line and current_field:
            # We are closing the container div. Let's add the error span.
            spaces = len(line) - len(line.lstrip())
            error_span = " " * spaces + f"@error('{current_field}') <span class=\"mt-1 block text-[11px] font-bold text-estado-peligro\">{{{{ $message }}}}</span> @enderror"
            new_lines.append(error_span)
            current_field = None
            
        new_lines.append(line)
        
    return '\n'.join(new_lines)

content = insert_errors(content)

# Add similar handling for 'enfermero_id' radio buttons
# Since they are wrapped in a label, we should just put the error message at the bottom of the grid
content = re.sub(
    r'(<div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">.*?)(</div>\s*<div class="rounded-lg border border-borde bg-fondo-panel p-4">)',
    r'\1    @error("enfermero_id") <span class="mt-2 block text-sm font-bold text-estado-peligro">{{ $message }}</span> @enderror\n                \2',
    content,
    flags=re.DOTALL
)

with open('resources/views/livewire/admin/admisiones/preadmision-wizard.blade.php', 'w') as f:
    f.write(content)
