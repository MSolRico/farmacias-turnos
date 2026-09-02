<footer
    id="contacto"
    class="bg-emerald-950 text-white text-xs py-5 border-t border-emerald-900 dark:border-emerald-800">

<div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">

    <div class="text-center md:text-left">

        <p class="text-emerald-200/80 mb-1">
            © {{ date('Y') }} Farmacias de Turno.
        </p>

        <p class="text-emerald-200/50 mb-0">
            Proyecto personal desarrollado con fines educativos y de portfolio.
        </p>

    </div>

    <div class="flex items-center gap-4 text-emerald-200/80 font-medium">

        <a
            href="{{ route('terminos') }}"
            class="hover:text-white transition no-underline text-inherit">
            Términos y condiciones
        </a>

        <span class="text-emerald-800">|</span>

        <a
            href="{{ route('privacidad') }}"
            class="hover:text-white transition no-underline text-inherit">
            Política de privacidad
        </a>

    </div>

    <div class="text-center md:text-right text-emerald-200/60">

        <span>Fuente de información:</span>

        <a
            href="https://colfarsfe.org.ar/"
            target="_blank"
            rel="noopener noreferrer"
            class="ml-1 text-emerald-200/90 hover:text-white transition no-underline">
            Colegio de Farmacéuticos
        </a>

    </div>

</div>

</footer>