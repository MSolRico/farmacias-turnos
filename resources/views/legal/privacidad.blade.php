@extends('layouts.app')

@section('title', 'Política de privacidad')

@section('content')

<div class="bg-gray-50 dark:bg-slate-950 min-h-screen">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">

        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-3xl shadow-sm p-6 sm:p-10">

            {{-- ENCABEZADO --}}
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">
                    Política de privacidad
                </h1>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Última actualización: 29/08/2026
                </p>
            </div>


            {{-- CONTENIDO --}}
            <div class="mt-8 space-y-8 text-sm leading-relaxed text-slate-600 dark:text-slate-300">


                {{-- 1 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        1. Sobre esta política
                    </h2>

                    <p>
                        Esta política explica qué información puede recopilar
                        Farmacias de Turno cuando utilizás las funcionalidades
                        que requieren una cuenta y cómo se utiliza dicha
                        información.
                    </p>

                    <p class="mt-3">
                        Farmacias de Turno es un proyecto personal e independiente
                        desarrollado con fines educativos y de portfolio.
                    </p>
                </section>


                {{-- 2 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        2. Información que recopilamos
                    </h2>

                    <p>
                        Cuando creás una cuenta, la plataforma puede solicitar
                        información básica necesaria para gestionar tu usuario,
                        como:
                    </p>

                    <ul class="mt-3 list-disc pl-5 space-y-1">
                        <li>Nombre.</li>
                        <li>Dirección de correo electrónico.</li>
                        <li>Contraseña, almacenada de forma segura.</li>
                    </ul>

                    <p class="mt-3">
                        Si utilizás la funcionalidad de reportes, también podemos
                        almacenar la información necesaria para registrar y
                        gestionar dichos reportes.
                    </p>
                </section>


                {{-- 3 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        3. Para qué utilizamos la información
                    </h2>

                    <p>
                        La información recopilada se utiliza principalmente para:
                    </p>

                    <ul class="mt-3 list-disc pl-5 space-y-1">
                        <li>Crear y administrar las cuentas de usuario.</li>
                        <li>Permitir el acceso a las funcionalidades que requieren autenticación.</li>
                        <li>Gestionar los reportes realizados desde una cuenta.</li>
                        <li>Mantener la seguridad y el funcionamiento de la plataforma.</li>
                    </ul>
                </section>


                {{-- 4 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        4. Protección de la información
                    </h2>

                    <p>
                        Se aplican medidas técnicas destinadas a proteger la
                        información almacenada y evitar accesos no autorizados.
                    </p>

                    <p class="mt-3">
                        Las contraseñas de las cuentas no se almacenan en texto
                        plano, sino utilizando mecanismos de hash proporcionados
                        por el sistema de autenticación.
                    </p>
                </section>


                {{-- 5 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        5. Compartición de información
                    </h2>

                    <p>
                        La información personal de los usuarios no se comercializa
                        ni se vende a terceros.
                    </p>

                    <p class="mt-3">
                        Los reportes realizados por los usuarios pueden utilizarse
                        dentro de la plataforma para mostrar información relacionada
                        con el estado de las farmacias. La información personal
                        asociada a una cuenta no se muestra públicamente salvo que
                        sea necesario para el funcionamiento de una funcionalidad.
                    </p>
                </section>


                {{-- 6 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        6. Cookies y almacenamiento local
                    </h2>

                    <p>
                        La plataforma puede utilizar mecanismos de almacenamiento
                        del navegador para conservar determinadas preferencias,
                        como la configuración del tema claro u oscuro.
                    </p>

                    <p class="mt-3">
                        Estas preferencias se almacenan localmente en el navegador
                        y no se utilizan para identificar personalmente al usuario.
                    </p>
                </section>


                {{-- 7 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        7. Servicios de terceros
                    </h2>

                    <p>
                        La aplicación utiliza determinados servicios de terceros para
                        proporcionar algunas de sus funcionalidades.
                    </p>

                    <ul class="mt-3 list-disc pl-5 space-y-1">
                        <li>
                            <strong>OpenStreetMap:</strong>
                            utilizado como fuente de datos cartográficos para mostrar la
                            ubicación de las farmacias en el mapa.
                        </li>

                        <li>
                            <strong>Google Gemini:</strong>
                            utilizado para tareas automatizadas de procesamiento y extracción
                            de información relacionada con los turnos de las farmacias.
                            Los datos personales de los usuarios no se envían a este servicio.
                        </li>

                        <li>
                            <strong>Google Fonts:</strong>
                            utilizado para cargar la tipografía empleada en la interfaz
                            del sitio.
                        </li>
                    </ul>

                    <p class="mt-3">
                        Cada uno de estos servicios puede estar sujeto a sus propias
                        políticas de privacidad y condiciones de uso.
                    </p>
                </section>



                {{-- 8 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        8. Conservación de los datos
                    </h2>

                    <p>
                        La información asociada a una cuenta se conserva mientras
                        sea necesaria para mantener el funcionamiento de las
                        funcionalidades correspondientes.
                    </p>

                    <p class="mt-3">
                        Si eliminás tu cuenta, la información asociada a ella
                        podrá ser eliminada de acuerdo con el funcionamiento
                        de la aplicación.
                    </p>
                </section>


                {{-- 9 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        9. Derechos del usuario
                    </h2>

                    <p>
                        Podés solicitar información sobre los datos personales
                        asociados a tu cuenta y solicitar su modificación o
                        eliminación cuando corresponda.
                    </p>
                </section>


                {{-- 10 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        10. Cambios en esta política
                    </h2>

                    <p>
                        Esta política puede actualizarse cuando se incorporen
                        nuevas funcionalidades o cambie la forma en que se
                        gestionan los datos. Cualquier modificación será
                        publicada en esta misma página.
                    </p>
                </section>


                {{-- 11 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        11. Contacto
                    </h2>

                    <p>
                        Si tenés alguna consulta relacionada con el tratamiento
                        de tus datos o con esta política de privacidad, podés
                        utilizar los medios de contacto disponibles en el sitio.
                    </p>
                </section>

            </div>

        </div>

    </div>

</div>

@endsection