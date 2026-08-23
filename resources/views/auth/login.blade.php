<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="mb-1.5 text-gray-700 font-medium" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" class="mb-1.5 text-gray-700 font-medium" />

            <x-text-input id="password" class="block w-full"
                type="password"
                name="password"
                required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-5">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-[#0d8a55] shadow-sm focus:ring-[#0d8a55]" name="remember">
                <span class="ms-2.5 text-sm text-gray-600 font-normal">{{ __('Recuerdeme') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
            <a class="text-sm text-[#0d8a55] hover:underline font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500" href="{{ route('password.request') }}">
                {{ __('Olvido su contraseña?') }}
            </a>
            @endif

            <x-primary-button>
                {{ __('Iniciar sesión') }}
            </x-primary-button>
        </div>
    </form>

    <!-- Divider -->
    <div class="relative flex py-5 items-center my-2">
        <div class="flex-grow border-t border-gray-200"></div>
        <span class="flex-shrink mx-4 text-gray-400 text-sm font-normal">o</span>
        <div class="flex-grow border-t border-gray-200"></div>
    </div>

    <!-- Register -->
    @if (Route::has('register'))
    <div class="flex items-center justify-center gap-3 text-sm">
        <span class="text-gray-700 font-normal">¿No tenés una cuenta?</span>
        <a href="{{ route('register') }}" 
           class="px-4 py-1.5 border border-gray-200 text-[#0d8a55] font-medium rounded-lg hover:bg-gray-50 transition-colors">
            {{ __('Registrate aquí') }}
        </a>
    </div>
    @endif

</x-guest-layout>