# 💊 Farmacias de Turno

Aplicación web/móvil enfocada en la localización eficiente y precisa de farmacias de guardia.
Este repositorio aloja el código backend y la lógica de negocio, incluyendo el proceso de ingesta y geolocalización de datos.

**Link al Proyecto:** [https://github.com/ricardo-lechman/farmacias-turnos](https://github.com/ricardo-lechman/farmacias-turnos)

## 🚀 Tecnologías Clave

Este proyecto utiliza un stack de desarrollo moderno y herramientas de código abierto para la geolocalización y el procesamiento de datos.

| Componente | Tecnología | Versión | Descripción |
| :--- | :--- | :--- | :--- |
| **Backend Framework** | **Laravel** | 11 | Utilizado para la robustez del código, enrutamiento y la API REST. |
| **Lenguaje de Programación** | **PHP** | 8.2 | Lenguaje principal del backend. |
| **Base de Datos** | **MySQL** | - | Almacenamiento de datos de farmacias, turnos y coordenadas geográficas. |
| **Entorno Local** | **XAMPP** | - | Utilizado para facilitar el entorno de desarrollo local (Apache, MySQL, PHP). |
| **Vistas** | **Blade** | - | Motor de plantillas de Laravel para el frontend web (si aplica) o vistas de administración. |
| **Geolocalización** | **OpenStreetMap** | - | Utilizado para el mapeo y la geocodificación de direcciones de farmacias. |
| **Procesamiento de Datos** | **Tesseract OCR** | - | Herramienta crítica para extraer automáticamente los horarios de guardia desde documentos (imágenes, PDFs). |

## ⚙️ Configuración e Instalación

### Prerrequisitos
1.  **XAMPP** (o un entorno equivalente con Apache, PHP 8.2+ y MySQL).
2.  **Composer** (gestor de dependencias de PHP).
3.  **Git**.

### Pasos
1.  **Clonar el repositorio:**
    ```bash
    git clone [https://github.com/ricardo-lechman/farmacias-turnos.git](https://github.com/ricardo-lechman/farmacias-turnos.git)
    cd farmacias-turnos
    ```

2.  **Instalar dependencias de PHP:**
    ```bash
    composer install
    ```

3.  **Configurar el entorno (.env):**
    Cree el archivo `.env` y configure la conexión a la base de datos MySQL (creada previamente), y las claves de OpenStreetMap/Tesseract si son necesarias.

    ```
    DB_DATABASE=farmacias_turnos
    # Otras configuraciones de entorno...
    ```

4.  **Ejecutar migraciones y seeders (si aplica):**
    ```bash
    php artisan migrate --seed
    ```

5.  **Servir la aplicación (si no usa XAMPP directamente):**
    ```bash
    php artisan serve
    ```

## 🤝 Contribuciones

Agradecemos cualquier contribución que mejore la precisión del OCR, la velocidad de geocodificación con OpenStreetMap, o la estabilidad general del framework Laravel 11.

