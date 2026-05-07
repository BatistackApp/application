<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Accès rapides
        </x-slot>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($links as $link)

                <a href="{{ $link['url'] }}"
                class="flex flex-col items-center justify-center p-6 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                >
                <x-filament::icon
                    :icon="$link['icon']"
                    class="w-8 h-8 mb-2 text-{{ $link['color'] }}-500"
                />
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200 text-center">
                        {{ $link['label'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
