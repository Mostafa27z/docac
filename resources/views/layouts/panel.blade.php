<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Doc Academy')</title>

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    {{-- Inter Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>

    {{-- Phosphor Icons --}}
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css" />
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/light/style.css" />

    {{-- TUS Upload Client --}}
    <script src="https://unpkg.com/tus-js-client@3.1.1/dist/tus.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        "primary":       "#0047AB",
                        "primary-light": "#0088CC",
                        "accent":        "#00A896",
                        "accent-light":  "#2EC4B6",
                        "bg-base":       "#F8F9FA",
                        "dark":          "#1A202C",
                        "muted":         "#718096",
                        "border-clr":    "#E2E8F0",
                        "surface":       "#FFFFFF",
                    },
                    spacing: {
                        "sidebar_w": "272px",
                        "topbar_h":  "72px",
                    },
                    borderRadius: {
                        "2xl": "1rem",
                        "3xl": "1.25rem",
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E0; border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: #A0AEC0; }
    </style>
    @stack('head')
</head>
<body class="bg-bg-base text-dark h-screen overflow-hidden">

    {{-- Sidebar Navigation --}}
    <nav class="fixed right-0 top-0 h-screen w-[272px] bg-gradient-to-b from-[#0047AB] to-[#003380] flex flex-col z-20">
        {{-- Brand Area --}}
        <div class="px-6 pt-6 pb-5 flex items-center gap-3 border-b border-white/10">
            <img src="/logo.jfif" alt="Doc Academy" class="w-11 h-11 rounded-xl shadow-lg" />
            <div>
                <span class="text-white font-bold text-base tracking-tight">Doc Academy</span>
                <span class="block text-[#7EB8FF] text-[11px] font-medium">@yield('role_title', 'لوحة التحكم')</span>
            </div>
        </div>

        {{-- Navigation Links --}}
        <ul class="flex flex-col gap-1 flex-grow px-3 py-4 overflow-y-auto">
            @if(auth()->check() && auth()->user()->role === 'admin')
                <li class="text-[#7EB8FF] text-[10px] font-semibold uppercase tracking-widest px-3 mb-2">الإدارة العامة</li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/8' }}" href="{{ route('admin.dashboard') }}">
                        <i class="ph-bold ph-squares-four text-lg"></i>
                        <span>نظرة عامة</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.teachers.index') ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/8' }}" href="{{ route('admin.teachers.index') }}">
                        <i class="ph-bold ph-chalkboard-teacher text-lg"></i>
                        <span>إدارة المدرسين</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.students.index') ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/8' }}" href="{{ route('admin.students.index') }}">
                        <i class="ph-bold ph-student text-lg"></i>
                        <span>إدارة الطلاب</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.codes.index') ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/8' }}" href="{{ route('admin.codes.index') }}">
                        <i class="ph-bold ph-key text-lg"></i>
                        <span>أكواد التفعيل</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.chats.*') ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/8' }}" href="{{ route('admin.chats.index') }}">
                        <i class="ph-bold ph-chat-circle-dots text-lg"></i>
                        <span>مراقبة المحادثات</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.settings.index') ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/8' }}" href="{{ route('admin.settings.index') }}">
                        <i class="ph-bold ph-gear text-lg"></i>
                        <span>بيانات الاتصال</span>
                    </a>
                </li>
            @elseif(auth()->check() && auth()->user()->role === 'instructor')
                <li class="text-[#7EB8FF] text-[10px] font-semibold uppercase tracking-widest px-3 mb-2">لوحة المحاضر</li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('instructor.dashboard') ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/8' }}" href="{{ route('instructor.dashboard') }}">
                        <i class="ph-bold ph-squares-four text-lg"></i>
                        <span>لوحة القيادة</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('instructor.courses.*') ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/8' }}" href="{{ route('instructor.courses.index') }}">
                        <i class="ph-bold ph-book-open-text text-lg"></i>
                        <span>إدارة الكورسات</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('instructor.subscriptions.*') ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/8' }}" href="{{ route('instructor.subscriptions.index') }}">
                        <i class="ph-bold ph-key text-lg"></i>
                        <span>المشتركين والأكواد</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('instructor.quizzes.*') ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/8' }}" href="{{ route('instructor.quizzes.index') }}">
                        <i class="ph-bold ph-exam text-lg"></i>
                        <span>إدارة الامتحانات</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('instructor.chats.*') ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/8' }}" href="{{ route('instructor.chats.index') }}">
                        <i class="ph-bold ph-chat-circle-dots text-lg"></i>
                        <span>المحادثات والرسائل</span>
                    </a>
                </li>
            @endif
        </ul>

        {{-- User & Logout --}}
        <div class="px-3 pb-4 mt-auto border-t border-white/10 pt-4">
            <div class="flex items-center gap-3 px-3 mb-3">
                <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center text-white font-bold text-sm">
                    {{ mb_substr(auth()->user()->name ?? '', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-semibold truncate">{{ auth()->user()->name ?? '' }}</p>
                    <p class="text-[#7EB8FF] text-[11px] truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
            </div>
            <form action="{{ route('web.logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-red-300 hover:bg-red-500/15 hover:text-red-200 transition-all duration-200 text-sm font-medium">
                    <i class="ph-bold ph-sign-out text-lg"></i>
                    <span>تسجيل الخروج</span>
                </button>
            </form>
        </div>
    </nav>

    {{-- Top App Bar --}}
    <header class="fixed top-0 right-[272px] w-[calc(100%-272px)] h-[72px] bg-white border-b border-[#E2E8F0] flex justify-between items-center px-8 z-10">
        <div class="flex items-center text-[#1A202C] text-lg font-bold">
            @yield('page_title')
        </div>
        <div class="flex items-center gap-4">
            <button class="p-2 rounded-xl text-[#718096] hover:bg-[#F8F9FA] hover:text-[#0047AB] transition-colors duration-150">
                <i class="ph-bold ph-bell text-xl"></i>
            </button>
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#0047AB] to-[#00A896] flex items-center justify-center text-white font-bold text-sm shadow-sm">
                {{ mb_substr(auth()->user()->name ?? '', 0, 1) }}
            </div>
        </div>
    </header>

    {{-- Main Content Canvas --}}
    <main class="absolute top-[72px] right-[272px] w-[calc(100%-272px)] h-[calc(100vh-72px)] overflow-y-auto bg-bg-base p-8">
        @if(session('success'))
            <div class="bg-[#2EC4B6]/10 text-[#00A896] border border-[#2EC4B6]/30 p-4 rounded-2xl mb-6 flex items-center gap-3">
                <i class="ph-bold ph-check-circle text-xl"></i>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 text-red-700 border border-red-200 p-4 rounded-2xl mb-6 flex items-center gap-3">
                <i class="ph-bold ph-warning-circle text-xl"></i>
                <span class="font-medium text-sm">{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Global Modal JS --}}
    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>
    @stack('scripts')
</body>
</html>
