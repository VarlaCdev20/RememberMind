import os
import re
import glob

def audit_files():
    base_dir = r"c:\laragon\www\RememberMind_F1"
    files_to_audit = [
        "resources/views/admin/adultos-mayores/show/_modales-existentes.blade.php",
        "resources/views/admin/adultos-mayores/create.blade.php",
        "resources/views/admin/adultos-mayores/edit.blade.php"
    ]
    
    # Adding some show files
    files_to_audit += glob.glob(os.path.join(base_dir, "resources/views/admin/adultos-mayores/show/*.blade.php"))
    
    # Adding a couple of livewire components as examples
    files_to_audit += glob.glob(os.path.join(base_dir, "resources/views/livewire/admin/adultos-mayores/*.blade.php"))
    
    files_to_audit = list(set(files_to_audit)) # deduplicate
    
    patterns = [
        "bg-fondo-panel",
        "bg-fondo-card",
        "border border-borde",
        "border-borde-suave",
        "bg-slate",
        "backdrop-blur",
        "shadow-sm",
        "shadow-xl"
    ]
    
    print("| Archivo | Línea | Fragmento encontrado | Problema | Clasificación | Recomendación |")
    print("|---|---|---|---|---|---|")
    
    for relative_path in files_to_audit:
        if not os.path.isabs(relative_path):
            path = os.path.join(base_dir, relative_path)
        else:
            path = relative_path
            relative_path = os.path.relpath(path, base_dir)
            
        if not os.path.exists(path):
            continue
            
        with open(path, "r", encoding="utf-8") as f:
            lines = f.readlines()
            
        matches_found = 0
        for i, line in enumerate(lines):
            line_str = line.strip()
            # We don't want to list 400 lines, just max 2 per file for the audit summary
            if matches_found >= 2:
                break
                
            for p in patterns:
                if p in line_str:
                    # Classify based on some heuristics
                    clasificacion = "Suavizar / Reemplazar"
                    problema = "Uso de variable estática pesada"
                    if "bg-fondo-panel" in line_str and "fixed" in line_str and "inset-0" in line_str:
                        clasificacion = "D) Reemplazar (rm-modal-overlay)"
                        problema = "Overlay sólido"
                    elif "bg-fondo-card" in line_str and "p-6" in line_str:
                        clasificacion = "B) Convertir a transparente o D) rm-modal-panel"
                        problema = "Modal o panel pesado"
                    elif "bg-fondo-panel" in line_str and "flex-1" in line_str:
                        clasificacion = "B) Convertir a transparente"
                        problema = "Cuerpo interno sólido"
                    elif "<input" in line_str or "<select" in line_str:
                        clasificacion = "D) Reemplazar (rm-input)"
                        problema = "Clases largas en input"
                    
                    # escape pipes for markdown table
                    safe_line = line_str.replace("|", "&#124;").replace("<", "&lt;").replace(">", "&gt;")
                    if len(safe_line) > 60:
                        safe_line = safe_line[:57] + "..."
                        
                    print(f"| `{os.path.basename(relative_path)}` | {i+1} | `{safe_line}` | {problema} | {clasificacion} | Reemplazar según diseño |")
                    matches_found += 1
                    break

if __name__ == "__main__":
    audit_files()
