<a href="{{ route('auth.google.redirect') }}"
    {{ $attributes->merge(['class' => 'inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800']) }}>
    <svg class="me-2 h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
        <path fill="#4285F4" d="M21.805 10.023h-9.58v3.955h5.515c-.238 1.273-.964 2.351-2.051 3.078v2.557h3.322c1.944-1.773 3.064-4.386 3.064-7.481 0-.71-.064-1.396-.27-2.109z" />
        <path fill="#34A853" d="M12.225 22c2.775 0 5.103-.91 6.804-2.467l-3.322-2.557c-.923.613-2.101.975-3.482.975-2.674 0-4.939-1.792-5.749-4.201H3.038v2.64C4.729 19.729 8.213 22 12.225 22z" />
        <path fill="#FBBC05" d="M6.476 13.75a5.873 5.873 0 0 1 0-3.75V7.36H3.038a9.93 9.93 0 0 0 0 9.03l3.438-2.64z" />
        <path fill="#EA4335" d="M12.225 5.949c1.51 0 2.867.515 3.934 1.527l2.945-2.923C17.322 2.912 14.996 2 12.225 2 8.213 2 4.729 4.271 3.038 7.61l3.438 2.64c.81-2.409 3.075-4.301 5.749-4.301z" />
    </svg>
    {{ __('Continuar con Google') }}
</a>