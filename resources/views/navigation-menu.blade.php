 <!-- ========== HEADER ========== -->
 <header class="z-50 flex flex-wrap w-full bg-white border-b border-gray-200 md:justify-start md:flex-nowrap dark:bg-neutral-800 dark:border-neutral-700">
    <nav class="relative max-w-[85rem] w-full mx-auto md:flex md:items-center md:justify-between md:gap-3 py-2 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between gap-x-1">
        <a class="flex-none text-xl font-semibold text-black focus:outline-hidden focus:opacity-80 dark:text-white" href="{{ route('welcome') }}" aria-label="Brand">
            <div class="flex flex-row gap-x-2">
                <img src="{{ asset('imgs/csav-logo.png') }}" class="w-auto h-12" alt="{{ config('app.name') }}">
            </div>
        </a>

        <!-- Collapse Button -->
        <button type="button" class="relative flex items-center justify-center text-sm font-medium text-gray-800 border border-gray-200 rounded-lg hs-collapse-toggle md:hidden size-9 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:border-neutral-700 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" id="hs-header-base-collapse"  aria-expanded="false" aria-controls="hs-header-base" aria-label="Toggle navigation"  data-hs-collapse="#hs-header-base" >
        <svg class="hs-collapse-open:hidden size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="6" y2="6"/><line x1="3" x2="21" y1="12" y2="12"/><line x1="3" x2="21" y1="18" y2="18"/></svg>
        <svg class="hidden hs-collapse-open:block shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        <span class="sr-only">Toggle navigation</span>
        </button>
        <!-- End Collapse Button -->
    </div>

    <!-- Collapse -->
    <div id="hs-header-base" class="hidden overflow-hidden transition-all duration-300 hs-collapse basis-full grow md:block "  aria-labelledby="hs-header-base-collapse" >
        <div class="overflow-hidden overflow-y-auto max-h-[75vh] [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">
        <div class="py-2 md:py-0  flex flex-col md:flex-row md:items-center gap-0.5 md:gap-1">
            <div class="grow">
            <div class="flex flex-col md:flex-row md:justify-end md:items-center gap-0.5 md:gap-1">
                <a class="flex items-center p-2 text-sm text-gray-800 rounded-lg lg:mx-3 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 {{ request()->routeIs('welcome') ? 'dark:bg-neutral-700 bg-gray-200' : '' }} dark:text-neutral-200 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" href="{{ route('welcome') }}" aria-current="{{ request()->routeIs('welcome') ? 'page' : '' }}">
                <svg class="block shrink-0 size-4 me-3 md:me-2 md:hidden" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    {{ __('Home') }}
                </a>

                <a class="flex items-center p-2 text-sm text-gray-800 rounded-lg lg:mx-3 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-200 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700 {{ request()->routeIs('about') ? 'dark:bg-neutral-700 bg-gray-200' : '' }}" href="{{ route('about') }}" aria-current="{{ request()->routeIs('about') ? 'page' : '' }}">
                <svg class="block shrink-0 size-4 me-3 md:me-2 md:hidden" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>
                    {{ __('About') }}
                </a>
            </div>
            </div>

            <div class="my-2 md:my-0 md:mx-2">
                <div class="w-full h-px bg-gray-100 md:w-px md:h-4 md:bg-gray-300 dark:bg-neutral-700"></div>
            </div>

            <button type="button" class="block font-medium text-gray-800 rounded-full hs-dark-mode-active:hidden hs-dark-mode hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 dark:text-neutral-200 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800" data-hs-theme-click-value="dark">
                <span class="inline-flex items-center justify-center group shrink-0 size-9">
                  <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                  </svg>
                </span>
              </button>
              <button type="button" class="hidden font-medium text-gray-800 rounded-full hs-dark-mode-active:block hs-dark-mode hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 dark:text-neutral-200 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800" data-hs-theme-click-value="light">
                <span class="inline-flex items-center justify-center group shrink-0 size-9">
                  <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2"></path>
                    <path d="M12 20v2"></path>
                    <path d="m4.93 4.93 1.41 1.41"></path>
                    <path d="m17.66 17.66 1.41 1.41"></path>
                    <path d="M2 12h2"></path>
                    <path d="M20 12h2"></path>
                    <path d="m6.34 17.66-1.41 1.41"></path>
                    <path d="m19.07 4.93-1.41 1.41"></path>
                  </svg>
                </span>
            </button>

            <!-- Button Group -->
            <div class=" flex flex-wrap items-center gap-x-1.5">

                <a class="py-[7px] px-2.5 inline-flex items-center font-medium text-sm rounded-lg border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 focus:outline-hidden focus:bg-gray-100 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700" href="{{ route('filament.dashboard.auth.login') }} ">

                    <svg class="shrink-0 size-5 me-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                    </svg>

                   {{  __(' Sign in') }}

                  </a>

                <a class="py-2 px-2.5 inline-flex items-center font-medium text-sm rounded-lg bg-lime-600 text-white hover:bg-lime-700 focus:outline-hidden focus:bg-lime-700 disabled:opacity-50 disabled:pointer-events-none dark:bg-lime-500 dark:hover:bg-lime-600 dark:focus:bg-lime-600" href="{{ route('filament.dashboard.auth.register') }}">


                    <svg class="shrink-0 size-5 me-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>

                    {{ __('Sign Up') }}
                </a>
            </div>
            <!-- End Button Group -->
            </div>
        </div>
    </div>
    <!-- End Collapse -->
    </nav>
</header>
<!-- ========== END HEADER ========== -->
