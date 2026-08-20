@props([
    'name',
    'show' => false,
    'maxWidth' => 'md',
])

@php
    $maxWidth = match ($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-md',
    };
@endphp

<div
    x-data="{
        show: @js($show),

        focusables() {
            const selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])';

            return [...$el.querySelectorAll(selector)]
                .filter(element => !element.hasAttribute('disabled'));
        },

        firstFocusable() {
            return this.focusables()[0];
        },

        lastFocusable() {
            return this.focusables().slice(-1)[0];
        },

        nextFocusable() {
            return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable();
        },

        prevFocusable() {
            return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable();
        },

        nextFocusableIndex() {
            const currentIndex = this.focusables().indexOf(document.activeElement);

            return (currentIndex + 1) % this.focusables().length;
        },

        prevFocusableIndex() {
            const currentIndex = this.focusables().indexOf(document.activeElement);

            return currentIndex <= 0
                ? this.focusables().length - 1
                : currentIndex - 1;
        },
    }"

    x-init="
        $watch('show', value => {
            document.body.classList.toggle('overflow-hidden', value);

            if (value) {
                setTimeout(() => {
                    if (firstFocusable()) {
                        firstFocusable().focus();
                    }
                }, 100);
            }
        });
    "

    x-on:open-modal.window="
        if ($event.detail === '{{ $name }}') {
            show = true;
        }
    "

    x-on:close-modal.window="
        if ($event.detail === '{{ $name }}') {
            show = false;
        }
    "

    x-on:close.stop="show = false"

    x-on:keydown.escape.window="
        if (show) {
            show = false;
        }
    "

    x-on:keydown.tab.prevent="
        if (show) {
            $event.shiftKey
                ? prevFocusable()?.focus()
                : nextFocusable()?.focus()
        }
    "

    x-show="show"
    x-cloak

    class="fixed inset-0 z-[1000] overflow-y-auto"
    role="dialog"
    aria-modal="true"
>
    {{-- Fondo oscuro --}}
    <div
        x-show="show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"

        class="fixed inset-0 bg-black/50"

        @click="show = false"
    ></div>


    {{-- Contenedor --}}
    <div class="relative flex min-h-full items-center justify-center p-4">

        {{-- Modal --}}
        <div
            x-show="show"

            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"

            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"

            class="relative w-full {{ $maxWidth }} overflow-hidden rounded-2xl bg-white shadow-2xl"

            @click.stop
        >
            {{ $slot }}
        </div>

    </div>

</div>


<style>
    [x-cloak] {
        display: none !important;
    }
</style>