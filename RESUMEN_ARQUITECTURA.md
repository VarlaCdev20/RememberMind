# Arquitectura y Estructura del Sistema RememberMind

Este documento es tu **mapa del tesoro**. Aquí se resumen todos los cambios profundos que hemos realizado en la arquitectura para lograr que Jetstream, Fortify, Spatie y tu base de datos personalizada se comuniquen perfectamente.

## 🌳 Árbol del Proyecto (¿Dónde está cada cosa?)

A continuación, tienes la estructura exacta de los archivos clave en tu proyecto. **Las carpetas marcadas con la estrella (⭐) son los lugares más importantes** que debes tener en cuenta para tu desarrollo futuro.

```text
c:\laragon\www\RememberMind_F1\
│
├── app/
│   ├── Actions/Fortify/ ⭐ (Lógica del núcleo de Autenticación)
│   │   ├── CreateNewUser.php                (Aquí definimos que el registro pide 'nombres' y 'ap_paterno')
│   │   └── UpdateUserProfileInformation.php (Aquí se guarda cuando editan su perfil)
│   │
│   ├── Models/ ⭐ (Tus Tablas de Base de Datos)
│   │   ├── User.php                         (El modelo principal, ahora adaptado a 'correo' y 'cod_usu')
│   │   ├── AdultoMayor.php
│   │   └── PersonalSalud.php
│   │
│   └── Providers/
│       └── FortifyServiceProvider.php       (🚨 CRÍTICO: Aquí validamos que la cuenta esté 'ACTIVA')
│
├── config/
│   └── fortify.php                          (Le dice a Laravel que el campo de login es 'correo')
│
├── database/ ⭐ (Datos iniciales y Estructura)
│   └── seeders/
│       ├── DatabaseSeeder.php               (El disparador general)
│       ├── AdminSeeder.php                  (Crea tu usuario: admincasaamandita@gmail.com)
│       └── RolesAndPermissionsSeeder.php    (Crea los 5 roles en el sistema)
│
├── resources/ ⭐ (Tu Frontend y Diseño visual)
│   ├── js/
│   │   ├── app.js                           (Inicializa AlpineJS)
│   │   └── bootstrap.js                     (Inicializa Axios para la seguridad de formularios)
│   │
│   └── views/
│       ├── auth/
│       │   ├── login.blade.php              (El nuevo diseño Glassmorphism con validaciones reactivas)
│       │   └── register.blade.php           (Formulario de registro adaptado a tu DB)
│       │
│       ├── dashboard.blade.php              (🚨 CRÍTICO: Tu Panel Central Inteligente con @role)
│       └── welcome.blade.php                (Tu Landing Page)
│
└── routes/
    └── web.php                              (⭐ Control de tráfico: Aquí están '/' y '/dashboard')
```

---

## 🛠️ Resumen de los Cambios Realizados

Hemos hecho un trabajo de **"cirugía profunda"** para no romper Laravel, pero forzarlo a usar tu estructura institucional.

### 1. El Sistema de Autenticación (Login)
- **Problema Inicial:** Laravel exigía una tabla con los campos `email` y `name`. Si no existían, lanzaba error.
- **La Solución (Lo que hicimos):** 
  - Fuimos a `config/fortify.php` y le dijimos a Laravel: *"Oye, el usuario no usa email, usa `correo`"*.
  - Fuimos a los archivos `CreateNewUser.php` y `UpdateUserProfileInformation.php` y cambiamos todas las reglas para que el sistema guarde `nombres`, `ap_paterno`, `ap_materno` y `correo`.
  - Fuimos a `app/Providers/FortifyServiceProvider.php` e inyectamos un filtro de seguridad: *"Si la persona pone la clave bien, pero su estado dice INACTIVO, no lo dejes entrar"*.

### 2. La Interfaz Visual (Frontend)
- **Lo que hicimos:** 
  - Creamos un `login.blade.php` **Premium**. Tiene fondo tipo "Ultra-Glassmorphism", un botón que dice *"Ingresando..."* cuando procesa, y animaciones suaves con AlpineJS.
  - Convertimos las típicas letras rojas feas de error en unas **tarjetas suaves color ámbar**, para dar una apariencia médica/cuidada (nada alarmista).
  - Enlazamos el logo del login para que al darle clic, el usuario pueda regresar a la Landing Page (`welcome.blade.php`).
  - Arreglamos un error de Vite restaurando tu archivo `bootstrap.js` e instalando `axios`.

### 3. El Dashboard (El Cerebro de las Vistas)
- **Problema Inicial:** Ibas a tener muchas rutas separadas (`/admin`, `/salud`, etc.), lo que te obligaba a duplicar menús, headers y layouts por cada rol.
- **La Solución:** 
  - Implementamos el **Dashboard Único**. Todos inician sesión y caen en `resources/views/dashboard.blade.php`.
  - *¿Cómo sabemos quién es quién?* Con directivas de Spatie. En ese archivo pusimos bloques como `@role('admin') ... @endrole`. Laravel se encarga de mostrarle a cada quien exclusivamente sus botones y formularios.

### 4. Los Roles y Administradores (Base de Datos)
- **Lo que hicimos:**
  - Creamos seeders para que tu base de datos no nazca vacía.
  - Creados los 5 roles: `admin`, `personal_salud`, `personal_admin`, `voluntario`, `familiar`.
  - Creado tu usuario maestro de pruebas: **`admincasaamandita@gmail.com`** (Clave: `CasaAmandita123`).

---

## 🎯 ¿Qué es lo más importante que debes saber hoy?

Si mañana quieres empezar a programar un formulario para el Médico, **¿a dónde vas?**

1. **Si quieres hacer la tabla del paciente:** Vas a `resources/views/dashboard.blade.php`, buscas donde dice `@role('personal_salud')` y ahí metes tu código (o llamas a tu componente Livewire).
2. **Si quieres hacer una nueva página pública:** Vas a `routes/web.php` y creas la ruta.
3. **Si el Login se rompe alguna vez:** Vas a `app/Providers/FortifyServiceProvider.php`. Ahí está el corazón de la puerta de entrada.
