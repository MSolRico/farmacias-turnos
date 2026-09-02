@extends('layouts.app')

@section('title', 'Términos y condiciones')

@section('content')

<div class="bg-gray-50 dark:bg-slate-950 min-h-screen">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">

        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-3xl shadow-sm p-6 sm:p-10">

            {{-- ENCABEZADO --}}
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">
                    Términos y condiciones
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
                        1. Sobre Farmacias de Turno
                    </h2>

                    <p>
                        Farmacias de Turno es un proyecto personal e independiente
                        desarrollado con fines educativos y de portfolio.
                    </p>

                    <p class="mt-3">
                        El objetivo del proyecto es ofrecer una forma sencilla de
                        consultar información sobre las farmacias de turno y
                        visualizar su ubicación en el mapa.
                    </p>

                    <p class="mt-3">
                        El proyecto no pertenece ni está afiliado a las farmacias,
                        colegios profesionales, organismos públicos u otras
                        entidades mencionadas en la plataforma.
                    </p>
                </section>


                {{-- 2 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        2. Información publicada
                    </h2>

                    <p>
                        La información sobre farmacias de turno se obtiene y
                        procesa a partir de fuentes disponibles públicamente.
                        Se procura mantenerla actualizada, pero pueden existir
                        errores, cambios o demoras en la actualización.
                    </p>

                    <p class="mt-3">
                        Por este motivo, la información debe considerarse
                        orientativa. Ante cualquier duda, recomendamos verificar
                        directamente con la farmacia correspondiente.
                    </p>
                </section>


                {{-- 3 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        3. Reportes de usuarios
                    </h2>

                    <p>
                        Los usuarios registrados pueden informar si una farmacia
                        de turno se encuentra cerrada o presenta alguna situación
                        que pueda ser relevante para otros usuarios.
                    </p>

                    <p class="mt-3">
                        Los reportes son aportes realizados por usuarios y no
                        representan necesariamente información verificada por
                        Farmacias de Turno.
                    </p>

                    <p class="mt-3">
                        No está permitido utilizar esta funcionalidad para
                        publicar información falsa, ofensiva o malintencionada.
                    </p>
                </section>


                {{-- 4 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        4. Uso del sitio
                    </h2>

                    <p>
                        El sitio debe utilizarse de manera responsable y
                        exclusivamente para los fines para los que fue desarrollado.
                    </p>

                    <p class="mt-3">
                        No está permitido intentar alterar, dañar o interferir
                        con el funcionamiento de la plataforma.
                    </p>
                </section>


                {{-- 5 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        5. Disponibilidad
                    </h2>

                    <p>
                        Al tratarse de un proyecto personal, las funcionalidades
                        y la disponibilidad del sitio pueden modificarse,
                        suspenderse o dejar de estar disponibles en cualquier
                        momento.
                    </p>
                </section>


                {{-- 6 --}}
                <section>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                        6. Contacto
                    </h2>

                    <p>
                        Para consultas, sugerencias o comentarios sobre el
                        proyecto, podés utilizar los medios de contacto
                        disponibles en el sitio.
                    </p>
                </section>

            </div>

        </div>

    </div>

</div>

@endsection