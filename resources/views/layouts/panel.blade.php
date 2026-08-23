<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Doc Academy - اللوحة الإدارية')</title>
    
    {{-- Fonts & Icons --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    {{-- Tailwind CSS CDN & Tus Upload Client --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/tus-js-client@2.3.1/dist/tus.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            blue: "#0047AB",
                            cyan: "#00A896",
                            mint: "#2EC4B6",
                            ocean: "#0088CC"
                        },
                        dark: "#1A202C",
                        muted: "#718096",
                        light: "#E2E8F0",
                        "bg-base": "#F8F9FA"
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
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E0; border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: #A0AEC0; }
    </style>
    @stack('head')
</head>
<body class="bg-bg-base text-dark h-screen overflow-hidden">

    {{-- Mobile Overlay Backdrop --}}
    <div id="mobile-backdrop" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden transition-opacity"></div>

    {{-- Sidebar Navigation --}}
    <nav id="sidebar" class="fixed right-0 top-0 h-screen w-[272px] bg-gradient-to-b from-[#0047AB] to-[#003380] flex flex-col z-50 transition-transform duration-300 transform max-lg:translate-x-full lg:translate-x-0">
        {{-- Brand Area --}}
        <div class="px-6 pt-6 pb-5 flex items-center justify-between border-b border-white/10">
            <div class="flex items-center gap-3">
                <img src="/logo.jfif" alt="Doc Academy" class="w-10 h-10 rounded-xl shadow-lg" />
                <div>
                    <span class="text-white font-bold text-base tracking-tight">Doc Academy</span>
                    <span class="block text-[#7EB8FF] text-[11px] font-medium">@yield('role_title', 'لوحة التحكم')</span>
                </div>
            </div>
            <button onclick="toggleMobileSidebar()" class="lg:hidden text-white/70 hover:text-white">
                <i class="ph-bold ph-x text-xl"></i>
            </button>
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
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.categories.index') ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/8' }}" href="{{ route('admin.categories.index') }}">
                        <i class="ph-bold ph-folder text-lg"></i>
                        <span>التصنيفات والتخصصات</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.courses.*') ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/8' }}" href="{{ route('admin.courses.index') }}">
                        <i class="ph-bold ph-book-open-text text-lg"></i>
                        <span>إدارة الكورسات</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.banners.*') ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/8' }}" href="{{ route('admin.banners.index') }}">
                        <i class="ph-bold ph-image text-lg"></i>
                        <span>البنرات والإعلانات</span>
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

            {{-- Common Link for both Admin & Instructor --}}
            <li class="pt-2 border-t border-white/10 mt-2">
                <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('profile.edit') ? 'bg-white/15 text-white shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/8' }}" href="{{ route('profile.edit') }}">
                    <i class="ph-bold ph-user-circle text-lg"></i>
                    <span>الملف الشخصي</span>
                </a>
            </li>
        </ul>

        {{-- User & Logout --}}
        <div class="px-3 pb-4 mt-auto border-t border-white/10 pt-4">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 mb-3 hover:bg-white/10 p-2 rounded-xl transition-colors">
                @if(auth()->check() && auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 rounded-full object-cover border border-white/20">
                @else
                    <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center text-white font-bold text-sm">
                        {{ mb_substr(auth()->user()->name ?? '', 0, 1) }}
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-semibold truncate">{{ auth()->user()->name ?? '' }}</p>
                    <p class="text-[#7EB8FF] text-[11px] truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
            </a>
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
    <header class="fixed top-0 right-0 lg:right-[272px] w-full lg:w-[calc(100%-272px)] h-[72px] bg-white border-b border-[#E2E8F0] flex justify-between items-center px-4 lg:px-8 z-10">
        <div class="flex items-center gap-3 text-[#1A202C] text-sm lg:text-lg font-bold">
            <button onclick="toggleMobileSidebar()" class="lg:hidden p-2 rounded-xl text-[#718096] hover:bg-[#F8F9FA]">
                <i class="ph-bold ph-list text-2xl"></i>
            </button>
            @yield('page_title')
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('profile.edit') }}" title="الملف الشخصي" class="flex items-center gap-2 text-xs font-semibold text-[#4A5568] hover:text-[#0047AB] transition-colors p-1.5 rounded-xl hover:bg-[#F8F9FA]">
                @if(auth()->check() && auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 lg:w-9 lg:h-9 rounded-full object-cover border border-[#0047AB]/20 shadow-sm">
                @else
                    <div class="w-8 h-8 lg:w-9 lg:h-9 rounded-full bg-gradient-to-br from-[#0047AB] to-[#00A896] flex items-center justify-center text-white font-bold text-xs lg:text-sm shadow-sm">
                        {{ mb_substr(auth()->user()->name ?? '', 0, 1) }}
                    </div>
                @endif
                <span class="hidden sm:inline">{{ auth()->user()->name ?? '' }}</span>
            </a>
        </div>
    </header>

    {{-- Main Content Canvas --}}
    <main class="absolute top-[72px] right-0 lg:right-[272px] w-full lg:w-[calc(100%-272px)] h-[calc(100vh-72px)] overflow-y-auto bg-bg-base p-4 lg:p-8">
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

    {{-- Global JS & Mobile Toggle --}}
    <script>
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('mobile-backdrop');
            if (sidebar.classList.contains('max-lg:translate-x-full')) {
                sidebar.classList.remove('max-lg:translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('max-lg:translate-x-full');
                backdrop.classList.add('hidden');
            }
        }

        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'block';
            }
        }
        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
