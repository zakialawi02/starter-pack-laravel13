<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.bunny.net" rel="preconnect">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>

            </style>
        @endif
    </head>

    <body class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <header class="not-has-[nav]:hidden mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
            @if (Route::has('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                        <a class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]" href="{{ url('/dashboard') }}">
                            Dashboard
                        </a>
                    @else
                        <a class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]" href="{{ route('login') }}">
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]" href="{{ route('register') }}">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <div class="duration-750 starting:opacity-0 flex w-full items-center justify-center opacity-100 transition-opacity lg:grow">
            <main class="flex w-full max-w-[335px] flex-col lg:max-w-6xl">

                <div class="py-4">
                    <x-button-primary type="button">
                        <i class="ri-refresh-line"></i>
                        primary
                    </x-button-primary>
                    <x-button-secondary type="button">
                        <i class="ri-refresh-line"> </i>
                        secondary
                    </x-button-secondary>
                    <x-button-light type="button">
                        <i class="ri-refresh-line"></i>
                        light
                    </x-button-light>
                    <x-button-danger type="button">
                        <i class="ri-refresh-line"></i>
                        error
                    </x-button-danger>
                </div>

                <div class="description my-5">
                    <p>
                        This table lists the routes defined by the application.
                        Each row shows the HTTP method, the URL path, the route
                        name, the middleware applied to the route, and the
                        controller action that handles it. Routes are grouped by
                        who can reach them: guests, authenticated users,
                        verified users, superadmins (user management), and API
                        clients using Sanctum bearer tokens. Run
                        <code class="rounded bg-gray-100 px-1 font-mono text-sm">php artisan route:list</code>
                        for the authoritative list.
                    </p>
                </div>
                <div class="mt-10 overflow-x-auto">
                    <div class="overflow-x-auto rounded-lg shadow-xl">
                        <table class="min-w-full border-collapse divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">URI</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Middleware</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Action</th>
                                </tr>
                            </thead>

                            @php
                                $methodColors = [
                                    'GET' => 'bg-green-100 text-green-800',
                                    'POST' => 'bg-yellow-100 text-yellow-800',
                                    'PUT' => 'bg-blue-100 text-blue-800',
                                    'PATCH' => 'bg-blue-100 text-blue-800',
                                    'PUT/PATCH' => 'bg-blue-100 text-blue-800',
                                    'DELETE' => 'bg-red-100 text-red-800',
                                ];

                                $routeGroups = [
                                    'Guest Routes' => [
                                        ['GET', '/register', 'register', 'guest', 'RegisteredUserController@create'],
                                        ['POST', '/register', '-', 'guest', 'RegisteredUserController@store'],
                                        ['GET', '/login', 'login', 'guest', 'AuthenticatedSessionController@create'],
                                        ['POST', '/login', '-', 'guest', 'AuthenticatedSessionController@store'],
                                        ['GET', '/forgot-password', 'password.request', 'guest', 'PasswordResetLinkController@create'],
                                        ['POST', '/forgot-password', 'password.email', 'guest', 'PasswordResetLinkController@store'],
                                        ['GET', '/reset-password/{token}', 'password.reset', 'guest', 'NewPasswordController@create'],
                                        ['POST', '/reset-password', 'password.store', 'guest', 'NewPasswordController@store'],
                                    ],
                                    'Social Login' => [
                                        ['GET', '/auth/{provider}/redirect', 'auth.redirect', '-', 'Socialite\ProviderRedirectController'],
                                        ['GET', '/auth/{provider}/callback', 'auth.callback', '-', 'Socialite\ProviderCallbackController'],
                                    ],
                                    'Authenticated Routes' => [
                                        ['GET', '/verify-email', 'verification.notice', 'auth', 'EmailVerificationPromptController'],
                                        ['GET', '/verify-email/{id}/{hash}', 'verification.verify', 'auth, signed, throttle:6,1', 'VerifyEmailController'],
                                        ['POST', '/email/verification-notification', 'verification.send', 'auth, throttle:6,1', 'EmailVerificationNotificationController@store'],
                                        ['GET', '/confirm-password', 'password.confirm', 'auth', 'ConfirmablePasswordController@show'],
                                        ['POST', '/confirm-password', '-', 'auth', 'ConfirmablePasswordController@store'],
                                        ['PUT', '/password', 'password.update', 'auth', 'PasswordController@update'],
                                        ['POST', '/logout', 'logout', 'auth', 'AuthenticatedSessionController@destroy'],
                                        ['GET', '/dashboard/profile', 'admin.profile.edit', 'auth', 'ProfileController@edit'],
                                        ['PATCH', '/dashboard/profile', 'admin.profile.update', 'auth', 'ProfileController@update'],
                                        ['PATCH', '/dashboard/photo-profile', 'admin.profile.photo-update', 'auth', 'ProfileController@updatePhoto'],
                                        ['DELETE', '/dashboard/profile', 'admin.profile.destroy', 'auth', 'ProfileController@destroy'],
                                    ],
                                    'Dashboard (verified users)' => [
                                        ['GET', '/dashboard', 'admin.dashboard', 'auth, verified', 'DashboardController@index'],
                                        ['GET', '/dashboard/empty', 'admin.dashboard.empty', 'auth, verified', 'DashboardController@empty'],
                                    ],
                                    'User Management (superadmin)' => [
                                        ['GET', '/dashboard/users', 'admin.users.index', 'auth, verified, role:superadmin', 'UserController@index'],
                                        ['POST', '/dashboard/users', 'admin.users.store', 'auth, verified, role:superadmin', 'UserController@store'],
                                        ['GET', '/dashboard/users/{user}', 'admin.users.show', 'auth, verified, role:superadmin', 'UserController@show'],
                                        ['PUT/PATCH', '/dashboard/users/{user}', 'admin.users.update', 'auth, verified, role:superadmin', 'UserController@update'],
                                        ['DELETE', '/dashboard/users/{user}', 'admin.users.destroy', 'auth, verified, role:superadmin', 'UserController@destroy'],
                                        ['DELETE', '/dashboard/users/batch', 'admin.users.batch-destroy', 'auth, verified, role:superadmin', 'UserController@batchDestroy'],
                                        ['GET', '/dashboard/users/export', 'admin.users.export', 'auth, verified, role:superadmin', 'UserController@export'],
                                        ['GET', '/dashboard/users/template', 'admin.users.template', 'auth, verified, role:superadmin', 'UserController@downloadTemplate'],
                                        ['POST', '/dashboard/users/import-preview', 'admin.users.import-preview', 'auth, verified, role:superadmin', 'UserController@importPreview'],
                                        ['POST', '/dashboard/users/import-confirm', 'admin.users.import-confirm', 'auth, verified, role:superadmin', 'UserController@importConfirm'],
                                    ],
                                    'API: Authentication (/api/auth)' => [
                                        ['POST', '/api/auth/register', 'api.auth.register', '-', 'Api\Auth\RegisterController@register'],
                                        ['GET', '/api/auth/login', 'api.auth.', '-', 'Closure (usage hint)'],
                                        ['POST', '/api/auth/login', 'api.auth.login', '-', 'Api\Auth\LoginController@login'],
                                        ['POST', '/api/auth/logout', 'api.auth.logout', 'auth:sanctum', 'Api\Auth\LogoutController@logout'],
                                        ['POST', '/api/auth/forgot-password', 'api.auth.password.email', 'throttle:6,1', 'Api\Auth\AuthController@passwordResetLink'],
                                        ['GET', '/api/auth/reset-password/{token}', 'api.auth.password.reset', '-', 'Closure (usage hint)'],
                                        ['POST', '/api/auth/reset-password', 'api.auth.password.store', '-', 'Api\Auth\AuthController@newPassword'],
                                        ['GET', '/api/auth/verify-email', 'api.auth.verification.notice', 'throttle:6,1', 'Closure (usage hint)'],
                                        ['POST', '/api/auth/email/verification-notification', 'api.auth.verification.send', 'auth:sanctum, throttle:6,1', 'Api\Auth\AuthController@emailVerificationNotification'],
                                        ['GET', '/api/auth/verify-email/{id}/{hash}', 'api.auth.verification.verify', 'auth:sanctum, signed, throttle:6,1', 'Api\Auth\AuthController@verifyEmail'],
                                    ],
                                    'API: Current User (/api/v1/user)' => [
                                        ['GET', '/api/v1/user', 'api.user.me', 'auth:sanctum', 'Api\UserController@me'],
                                        ['PATCH', '/api/v1/user', 'api.user.me', 'auth:sanctum', 'Api\UserController@updateMyProfile'],
                                        ['PATCH', '/api/v1/user/photo', 'api.user.photo', 'auth:sanctum', 'Api\UserController@updateMyPhotoProfile'],
                                        ['DELETE', '/api/v1/user/destroy/me', 'api.user.destroy.me', 'auth:sanctum', 'Api\UserController@destroyMyAccount'],
                                    ],
                                    'API: User Management (superadmin)' => [
                                        ['GET', '/api/v1/users', 'api.users.index', 'auth:sanctum, verified, role:superadmin', 'Api\UserController@index'],
                                        ['POST', '/api/v1/users', 'api.users.store', 'auth:sanctum, verified, role:superadmin', 'Api\UserController@store'],
                                        ['GET', '/api/v1/users/{user}', 'api.users.show', 'auth:sanctum, verified, role:superadmin', 'Api\UserController@show'],
                                        ['PUT/PATCH', '/api/v1/users/{user}', 'api.users.update', 'auth:sanctum, verified, role:superadmin', 'Api\UserController@update'],
                                        ['DELETE', '/api/v1/users/{user}', 'api.users.destroy', 'auth:sanctum, verified, role:superadmin', 'Api\UserController@destroy'],
                                    ],
                                    'General Routes' => [
                                        ['GET', '/', 'home', '-', 'HomeController@index'],
                                        ['GET', '/admin', '-', '-', 'Redirect to /dashboard'],
                                        ['GET', '/docs', 'docs', '-', 'Closure'],
                                        ['GET', '/up', '-', '-', 'Health check'],
                                    ],
                                ];
                            @endphp

                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($routeGroups as $group => $routes)
                                    <tr class="bg-blue-50">
                                        <td class="px-6 py-3 text-lg font-semibold text-blue-700" colspan="5">{{ $group }}</td>
                                    </tr>

                                    @foreach ($routes as [$method, $uri, $name, $middleware, $action])
                                        <tr>
                                            <td class="px-6 py-4">
                                                <span class="{{ $methodColors[$method] }} rounded px-2 py-1 text-xs font-semibold">{{ $method }}</span>
                                            </td>
                                            <td class="px-6 py-4">{{ $uri }}</td>
                                            <td class="px-6 py-4 font-mono text-sm">{{ $name }}</td>
                                            <td class="px-6 py-4">{{ $middleware }}</td>
                                            <td class="px-6 py-4 font-mono text-sm">{{ $action }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 rounded-lg bg-white p-4 shadow">
                        <h2 class="mb-2 text-lg font-semibold">Legenda:</h2>
                        <div class="flex flex-wrap gap-4">
                            <div class="flex items-center">
                                <span class="mr-2 h-4 w-12 bg-green-100"></span>
                                <span>GET</span>
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2 h-4 w-12 bg-yellow-100"></span>
                                <span>POST</span>
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2 h-4 w-12 bg-blue-100"></span>
                                <span>PUT/PATCH</span>
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2 h-4 w-12 bg-red-100"></span>
                                <span>DELETE</span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif
    </body>

</html>
