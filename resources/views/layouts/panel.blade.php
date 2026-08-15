<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Doc Academy')</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <script src="https://unpkg.com/tus-js-client@3.1.1/dist/tus.min.js"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface": "#f7fafc",
                        "on-surface": "#181c1e",
                        "on-tertiary-container": "#353f50",
                        "secondary-container": "#dde2f3",
                        "surface-container-highest": "#e0e3e5",
                        "error-container": "#ffdad6",
                        "on-secondary-fixed-variant": "#414754",
                        "outline": "#897362",
                        "primary": "#904d00",
                        "tertiary": "#555f71",
                        "on-primary-fixed-variant": "#6e3900",
                        "surface-container": "#ebeef0",
                        "inverse-primary": "#ffb77d",
                        "primary-fixed": "#ffdcc3",
                        "on-tertiary-fixed-variant": "#3d4759",
                        "secondary-fixed-dim": "#c1c6d7",
                        "outline-variant": "#ddc1ae",
                        "primary-fixed-dim": "#ffb77d",
                        "on-background": "#181c1e",
                        "on-secondary-fixed": "#161c27",
                        "surface-tint": "#904d00",
                        "error": "#ba1a1a",
                        "primary-container": "#ff8c00",
                        "on-primary-container": "#623200",
                        "on-error-container": "#93000a",
                        "on-secondary-container": "#5e6473",
                        "on-surface-variant": "#564334",
                        "on-primary-fixed": "#2f1500",
                        "secondary-fixed": "#dde2f3",
                        "tertiary-fixed": "#d9e3f9",
                        "on-secondary": "#ffffff",
                        "tertiary-container": "#a0aabf",
                        "on-tertiary-fixed": "#121c2c",
                        "on-error": "#ffffff",
                        "tertiary-fixed-dim": "#bdc7dc",
                        "on-tertiary": "#ffffff",
                        "surface-variant": "#e0e3e5",
                        "surface-container-lowest": "#ffffff",
                        "inverse-surface": "#2d3133",
                        "secondary": "#585e6c",
                        "surface-container-low": "#f1f4f6",
                        "surface-dim": "#d7dadc",
                        "surface-container-high": "#e5e9eb",
                        "background": "#f7fafc",
                        "inverse-on-surface": "#eef1f3",
                        "surface-bright": "#f7fafc",
                        "on-primary": "#ffffff"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "stack-sm": "8px",
                        "sidebar_width": "260px",
                        "gutter": "24px",
                        "stack-md": "16px",
                        "stack-lg": "24px",
                        "margin-page": "32px",
                        "topbar_height": "72px"
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Be Vietnam Pro', sans-serif; }
    </style>
</head>
<body class="bg-background text-on-surface h-screen overflow-hidden">

    <!-- SideNavBar -->
    <nav class="fixed right-0 top-0 h-screen w-[260px] bg-on-background flex flex-col py-stack-lg z-20">
        <div class="px-gutter mb-stack-lg flex flex-col items-start">
            <span class="text-headline-md font-bold text-primary mb-1">Doc Academy</span>
            <span class="text-label-sm text-surface-variant">@yield('role_title', 'Control Panel')</span>
        </div>
        <ul class="flex flex-col space-y-2 flex-grow px-stack-sm">
            @if(auth()->check() && auth()->user()->role === 'admin')
                <li>
                    <a class="flex items-center space-x-reverse space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'text-primary font-bold border-l-4 border-primary bg-on-secondary-fixed-variant/10' : 'text-surface-variant hover:text-white hover:bg-on-secondary-fixed-variant/5' }} transition-all duration-200" href="{{ route('admin.dashboard') }}">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">dashboard</span>
                        <span class="font-medium text-sm">نظرة عامة</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center space-x-reverse space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.teachers.index') ? 'text-primary font-bold border-l-4 border-primary bg-on-secondary-fixed-variant/10' : 'text-surface-variant hover:text-white hover:bg-on-secondary-fixed-variant/5' }} transition-all duration-200" href="{{ route('admin.teachers.index') }}">
                        <span class="material-symbols-outlined">school</span>
                        <span class="font-medium text-sm">إدارة المدرسين</span>
                    </a>
                </li>
            @elseif(auth()->check() && auth()->user()->role === 'instructor')
                <li>
                    <a class="flex items-center space-x-reverse space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('instructor.dashboard') ? 'text-primary font-bold border-l-4 border-primary bg-on-secondary-fixed-variant/10' : 'text-surface-variant hover:text-white hover:bg-on-secondary-fixed-variant/5' }} transition-all duration-200" href="{{ route('instructor.dashboard') }}">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">dashboard</span>
                        <span class="font-medium text-sm">لوحة القيادة</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center space-x-reverse space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('instructor.courses.*') ? 'text-primary font-bold border-l-4 border-primary bg-on-secondary-fixed-variant/10' : 'text-surface-variant hover:text-white hover:bg-on-secondary-fixed-variant/5' }} transition-all duration-200" href="{{ route('instructor.courses.index') }}">
                        <span class="material-symbols-outlined">menu_book</span>
                        <span class="font-medium text-sm">إدارة الكورسات</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center space-x-reverse space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('instructor.subscriptions.*') ? 'text-primary font-bold border-l-4 border-primary bg-on-secondary-fixed-variant/10' : 'text-surface-variant hover:text-white hover:bg-on-secondary-fixed-variant/5' }} transition-all duration-200" href="{{ route('instructor.subscriptions.index') }}">
                        <span class="material-symbols-outlined">vpn_key</span>
                        <span class="font-medium text-sm">المشتركين والأكواد</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center space-x-reverse space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('instructor.quizzes.*') ? 'text-primary font-bold border-l-4 border-primary bg-on-secondary-fixed-variant/10' : 'text-surface-variant hover:text-white hover:bg-on-secondary-fixed-variant/5' }} transition-all duration-200" href="{{ route('instructor.quizzes.index') }}">
                        <span class="material-symbols-outlined">quiz</span>
                        <span class="font-medium text-sm">إدارة الامتحانات</span>
                    </a>
                </li>
            @endif
        </ul>
        <div class="px-stack-sm mt-auto">
            <form action="{{ route('web.logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center space-x-reverse space-x-3 px-4 py-3 rounded-lg text-error hover:bg-error-container/10 transition-colors duration-200">
                    <span class="material-symbols-outlined">logout</span>
                    <span class="font-semibold text-sm">تسجيل الخروج</span>
                </button>
            </form>
        </div>
    </nav>

    <!-- TopAppBar -->
    <header class="fixed top-0 right-[260px] w-[calc(100%-260px)] h-[72px] bg-surface border-b border-outline-variant flex justify-between items-center px-margin-page z-10">
        <div class="flex items-center text-primary text-xl font-bold">
            @yield('page_title')
        </div>
        <div class="flex items-center space-x-reverse space-x-4">
            <button class="p-2 rounded-full text-on-surface-variant hover:bg-surface-container-high transition-colors duration-150 flex items-center justify-center">
                <span class="material-symbols-outlined">notifications</span>
            </button>
            <div class="w-10 h-10 rounded-full overflow-hidden border border-outline-variant ml-2">
                <img alt="User Profile" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDSV0FNA2xZhyq1Uz3fXhELtpTUqGQscZYIttVVKZK00eXqctZIG-qrzBDGoN5-GlzBu30aH4tdERQKCXI2-sCzTk_JW-MoTtraVys48C1ZlQeEPhn52TDH-WQysEpFeIWEXgiupZWcpEivao_3Twt3w1qW8dWFin1QJU22LjgH4qYqCvNdqWKWfSW1XxgoYDOrFbtiIksQYgzrsJGskJTDCsUTPkqGr7oex4MBlhlOI3mDPFtZII5u"/>
            </div>
        </div>
    </header>

    <!-- Main Content Canvas -->
    <main class="absolute top-[72px] right-[260px] w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface-container-lowest p-margin-page">
        @if(session('success'))
            <div class="bg-success-container text-success border border-success/30 p-4 rounded-xl mb-6 flex items-center space-x-reverse space-x-3">
                <span class="material-symbols-outlined text-green-600">check_circle</span>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

</body>
</html>
