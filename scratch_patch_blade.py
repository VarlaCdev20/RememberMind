import os

file_path = "resources/views/livewire/admin/salud-seguimiento/salud-signos-panel.blade.php"
with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

# 1. space-y-6 to space-y-4
content = content.replace('<div class="space-y-6">', '<div class="space-y-4">')

# 2. grid lg:grid-cols-[1fr_1.1fr_auto] to lg:grid-cols-[1fr_1fr_auto]
content = content.replace('lg:grid-cols-[1fr_1.1fr_auto]', 'md:grid-cols-[1fr_1fr_auto]')

# 3. gap-4 in section B p-5 to p-4
content = content.replace('p-5 sm:flex-row', 'p-4 sm:flex-row')
content = content.replace('p-5 shadow-sm backdrop-blur-xl">\\n        <label', 'p-4 shadow-sm backdrop-blur-xl">\\n        <label')
content = content.replace('p-5 shadow-sm backdrop-blur-xl relative', 'p-4 shadow-sm backdrop-blur-xl relative')

# 4. grid for cards (Section D)
content = content.replace('<section class="grid grid-cols-2 gap-4 lg:grid-cols-4">', '<section class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">')
content = content.replace('p-4 shadow-sm backdrop-blur-md">', 'p-3 shadow-sm backdrop-blur-md">')

# 5. Graph and Report section
content = content.replace('<section class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">', '<section class="grid gap-4 lg:grid-cols-[1.8fr_1fr]">')
content = content.replace('p-5 shadow-sm flex flex-col', 'p-4 shadow-sm flex flex-col')
content = content.replace('bg-white p-5 shadow-sm"', 'bg-white p-4 shadow-sm"')
content = content.replace('h-64 w-full', 'h-56 w-full')

# 6. Tabla section
content = content.replace('px-5 py-4 flex flex-col', 'px-4 py-3 flex flex-col')
content = content.replace('px-5 py-4 text-right', 'px-4 py-3 text-right')
content = content.replace('px-5 py-4"', 'px-4 py-3"')
content = content.replace('px-5 py-3', 'px-4 py-2.5')

# 7. Modals sizes
content = content.replace('maxWidth="3xl"', 'maxWidth="2xl"')
content = content.replace('maxWidth="lg"', 'maxWidth="md"')
content = content.replace('p-5 shadow-sm backdrop-blur-xl">\\n                    <h4', 'p-4 shadow-sm backdrop-blur-xl">\\n                    <h4')


with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Blade spacing patched")
