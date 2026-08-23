@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-slate-700 bg-white focus:outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100 transition-all']) }}>