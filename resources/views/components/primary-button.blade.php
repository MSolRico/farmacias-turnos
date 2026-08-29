<button {{ $attributes->merge(['type' => 'submit', 'class' => 'px-5 py-2.5 bg-[#0d8a55] hover:bg-[#0b7548] text-white font-medium text-md rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900']) }}>
    {{ $slot }}
</button>