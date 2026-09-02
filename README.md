# ✚ Farmacias de Turno

<img
    width="1000"
    alt="Hero de Farmacias de Turno"
    src="https://raw.githubusercontent.com/MSolRico/farmacias-turnos/main/public/images/portada.png"
>

Aplicación web para consultar de forma rápida y precisa las farmacias de turno, visualizar su ubicación y reportar situaciones en las que una farmacia de turno se encuentre cerrada.

El sistema incluye un panel de administración para gestionar farmacias, turnos, importaciones y reportes, además de un proceso automatizado de extracción de información a partir de los documentos oficiales de turnos mediante la API de Gemini.

**🔗 Link al proyecto:**

https://github.com/MSolRico/farmacias-turnos

## 🚀 Funcionalidades

### 👤 Usuarios

* Registro e inicio de sesión.
* Gestión del perfil de usuario.
* Consulta de farmacias de turno.
* Búsqueda de turnos por fecha y ciudad.
* Visualización de farmacias en un mapa interactivo.
* Reporte de farmacias que se encuentren cerradas durante su turno.
* Consulta y gestión de los propios reportes.
* Soporte para modo claro y oscuro.
* Diseño responsive para dispositivos móviles y computadoras.

### 🛠️ Panel de administración

El sistema cuenta con un panel privado para usuarios con rol de administrador.

* Dashboard con información general del sistema.
* Gestión y edición de farmacias.
* Búsqueda y filtrado de farmacias por nombre, dirección y ciudad.
* Carga y edición manual de coordenadas geográficas.
* Consulta de turnos registrados.
* Búsqueda de turnos por fecha.
* Gestión de las farmacias asignadas a cada turno.
* Historial y detalle de las importaciones.
* Consulta, búsqueda y filtrado de reportes.
* Verificación o rechazo de reportes.
* Creación de administradores mediante comando Artisan.
* Navegación responsive para dispositivos móviles.

### 📄 Importación de turnos

El sistema automatiza la incorporación de los turnos publicados oficialmente:

1. Descarga del documento correspondiente.
2. Envío del documento a la API de Gemini para su procesamiento.
3. Extracción estructurada de farmacias, fechas y horarios.
4. Creación y actualización de registros.
5. Asociación de farmacias con sus respectivos turnos.
6. Registro del resultado de la importación.

## 📸 Capturas de pantalla

### 👤 Panel de usuario

<img
width="1000"
alt="Panel de usuario"
src="https://raw.githubusercontent.com/MSolRico/farmacias-turnos/main/public/images/panel-usuario.png"
>

### 🛠️ Panel de administración

<img
width="1000"
alt="Panel de administración"
src="https://raw.githubusercontent.com/MSolRico/farmacias-turnos/main/public/images/panel-administracion.png"
>

## 🧰 Tecnologías

| Componente                      | Tecnología            | Descripción                                                                           |
| :------------------------------ | :-------------------- | :------------------------------------------------------------------------------------ |
| **Backend**                     | **Laravel 12**        | Framework principal para la lógica de negocio, rutas, autenticación y administración. |
| **Lenguaje**                    | **PHP 8.2+**          | Lenguaje principal del backend.                                                       |
| **Base de datos**               | **MariaDB / MySQL**   | Almacenamiento de usuarios, farmacias, turnos, reportes e importaciones.              |
| **Frontend**                    | **Blade**             | Motor de plantillas utilizado para las vistas de la aplicación.                       |
| **Estilos**                     | **Tailwind CSS 4**    | Diseño responsive y sistema de estilos de la aplicación.                              |
| **Interactividad**              | **Alpine.js**         | Interacciones del frontend, menús, filtros y componentes dinámicos.                   |
| **Mapas**                       | **Leaflet**           | Visualización interactiva de las farmacias en el mapa.                                |
| **Cartografía**                 | **OpenStreetMap**     | Datos cartográficos utilizados para la visualización y localización.                  |
| **Procesamiento de documentos** | **Google Gemini API** | Extracción y estructuración de información desde los documentos de turnos.            |
| **Build tool**                  | **Vite**              | Compilación y desarrollo de los recursos frontend.                                    |
| **Entorno local**               | **XAMPP**             | Entorno utilizado para Apache, PHP y MariaDB durante el desarrollo.                   |
| **Dependencias PHP**            | **Composer**          | Gestión de dependencias del proyecto.                                                 |

## ⚙️ Instalación

### Prerrequisitos

Se requiere:

1. **PHP 8.2 o superior**
2. **Composer**
3. **Node.js y npm**
4. **MySQL o MariaDB**
5. **Git**
6. Una **API Key de Google Gemini** para utilizar el proceso de importación de turnos.

XAMPP puede utilizarse como entorno de desarrollo local.

### 1. Clonar el repositorio

```bash
git clone https://github.com/MSolRico/farmacias-turnos.git

cd farmacias-turnos
```

### 2. Instalar dependencias de PHP

```bash
composer install
```

### 3. Instalar dependencias de frontend

```bash
npm install
```

### 4. Configurar el entorno

Copiar el archivo de ejemplo:

```bash
cp .env.example .env
```

En Windows también puede crearse manualmente el archivo `.env` a partir de `.env.example`.

Configurar las variables correspondientes a la aplicación y la base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_turnos
DB_USERNAME=root
DB_PASSWORD=
```

Configurar también la API Key de Gemini:

```env
GEMINI_API_KEY=
GEMINI_VISION_MODEL=gemini-3.6-flash
```

Si el proyecto requiere indicar manualmente la ubicación de Poppler, configurar:

```env
POPPLER_PDFTOPPM_PATH=
```

> La API Key de Gemini debe mantenerse únicamente en el archivo `.env` y no debe subirse al repositorio.

### 5. Generar la clave de la aplicación

```bash
php artisan key:generate
```

### 6. Ejecutar las migraciones y seeders

```bash
php artisan migrate --seed
```

### 7. Compilar los recursos frontend

Para desarrollo:

```bash
npm run dev
```

Para producción:

```bash
npm run build
```

### 8. Iniciar la aplicación

Puede utilizarse el servidor de desarrollo de Laravel:

```bash
php artisan serve
```

La aplicación estará disponible normalmente en:

```text
http://localhost:8000
```

## 👑 Crear un administrador

Los usuarios registrados normalmente tienen el rol `usuario`.

Para crear un administrador de forma segura se utiliza el comando Artisan:

```bash
php artisan admin:create
```

El comando solicita:

* Nombre.
* Email.
* Contraseña.
* Confirmación de contraseña.

El administrador utiliza el mismo sistema de autenticación que los usuarios normales, pero tiene acceso al panel privado ubicado en:

```text
/admin
```

El acceso está protegido mediante autenticación y autorización por rol.

## ⏰ Importación automática

La aplicación cuenta con un proceso de importación de turnos que puede ejecutarse mediante Artisan y programarse mediante el scheduler de Laravel.

El proceso descarga los documentos oficiales y utiliza la API de Gemini para analizar su contenido y obtener los datos estructurados necesarios para actualizar la base de datos.

Cada importación registra información sobre:

* Farmacias nuevas.
* Farmacias actualizadas.
* Farmacias rechazadas.
* Turnos nuevos.
* Asignaciones creadas.
* Columnas con errores.
* Último intento de importación.
* Mensajes y advertencias del proceso.

## 🗺️ Geolocalización

Las farmacias almacenan sus coordenadas geográficas (`lat` y `lng`) para poder mostrarlas en el mapa mediante Leaflet.

Los datos cartográficos se visualizan utilizando OpenStreetMap.

Las coordenadas pueden ser gestionadas manualmente desde el panel de administración cuando es necesario corregir o completar la ubicación de una farmacia.

## 🔐 Roles y acceso

La aplicación utiliza dos roles:

* `usuario`: acceso a las funcionalidades públicas y de usuario.
* `admin`: acceso adicional al panel de administración.

El panel administrativo está protegido mediante autenticación y autorización por rol.

## 📁 Estructura general

El proyecto sigue la arquitectura de Laravel y separa las principales responsabilidades en:

* **Controllers** — lógica de las solicitudes.
* **Models** — interacción con la base de datos.
* **Services** — procesamiento e integración de funcionalidades específicas.
* **Commands** — procesos ejecutables mediante Artisan.
* **Views / Blade** — interfaz de usuario y panel administrativo.
* **Migrations** — estructura de la base de datos.
* **Middleware** — autenticación y control de acceso.

## 🤝 Contribuciones

Las contribuciones son bienvenidas, especialmente aquellas orientadas a mejorar:

* La precisión de la extracción de información mediante Gemini.
* La calidad de los datos de farmacias.
* La experiencia de usuario.
* La estabilidad y mantenibilidad del sistema.
* El proceso de importación y actualización de turnos.

---

**Farmacias de Turno**

Aplicación desarrollada con Laravel, Tailwind CSS, Alpine.js, Leaflet, Google Gemini y herramientas de código abierto.
