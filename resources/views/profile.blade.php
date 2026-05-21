<x-app-layout>
     <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <x-app-sidebar/>

        {{-- Contenido principal --}}
        <main class="flex-1">
            <div class="border-b border-gray-700 bg-gray-800 px-6 py-6">
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    {{ __('Perfil') }}
                </h2>
            </div>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <div class="max-w-xl">
                            <livewire:profile.update-profile-information-form />
                        </div>
                    </div>

                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <div class="max-w-xl">
                            <livewire:profile.update-password-form />
                        </div>
                    </div>

                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <div class="max-w-xl">
                            <livewire:profile.delete-user-form />
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>
