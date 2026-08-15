<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منصة دوكاك التعليمية - Docac LMS</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#904d00",
                        "primary-container": "#ff8c00",
                        "on-primary-container": "#623200",
                        "background": "#f7fafc",
                        "surface": "#f7fafc",
                        "on-surface": "#181c1e",
                        "surface-container": "#ebeef0",
                        "outline-variant": "#ddc1ae",
                        "secondary": "#585e6c",
                        "tertiary": "#555f71",
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Be Vietnam Pro', sans-serif; }
    </style>
</head>
<body class="bg-background text-on-surface min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="flex justify-between items-center px-8 py-4 bg-white/80 backdrop-blur-md border-b border-outline-variant sticky top-0 z-50">
        <div class="flex items-center space-x-reverse space-x-3">
            <span class="material-symbols-outlined text-primary text-3xl">school</span>
            <span class="text-xl font-bold text-primary">Doc Academy</span>
        </div>
        <div class="flex items-center space-x-reverse space-x-4">
            <a href="#courses" class="text-sm font-semibold text-on-surface hover:text-primary transition-colors">الكورسات</a>
            <a href="/api_docs.html" target="_blank" class="text-sm font-semibold text-on-surface hover:text-primary transition-colors">توثيق API</a>
        </div>
    </nav>

    <!-- Hero / Login Section -->
    <section class="max-w-6xl mx-auto px-6 py-12 flex-grow grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <!-- Hero Details -->
        <div>
            <div class="inline-flex items-center space-x-reverse space-x-2 bg-primary/10 text-primary px-3 py-1.5 rounded-full text-xs font-semibold mb-4">
                <span class="material-symbols-outlined text-sm">rocket_launch</span>
                <span>منصة تعليمية متكاملة للويب وتطبيقات الجوال</span>
            </div>
            <h1 class="text-4xl font-extrabold text-on-surface leading-tight mb-4">
                منظومة <span class="text-primary">التعليم الطبي الحديثة</span> في مكان واحد
            </h1>
            <p class="text-sm text-secondary leading-relaxed mb-6">
                منصة متكاملة تسمح للطلاب بمتابعة المحاضرات الطبية عبر تطبيق الهاتف مع حماية الفيديو عن طريق Bunny CDN وإمكانية إدارة الكورسات والامتحانات للمعلمين والمشرفين.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="#courses" class="bg-primary text-white font-bold px-6 py-3 rounded-xl hover:bg-primary-container transition-colors shadow-md">استكشف الكورسات</a>
                <a href="/api_docs.html" target="_blank" class="bg-surface-container border border-outline-variant text-on-surface font-bold px-6 py-3 rounded-xl hover:bg-outline-variant/20 transition-colors">دليل الـ API</a>
            </div>
        </div>

        <!-- Login Box -->
        <div class="bg-white border border-outline-variant rounded-2xl p-8 shadow-sm">
            <h2 class="text-xl font-bold text-primary mb-1">تسجيل الدخول للوحات التحكم</h2>
            <p class="text-xs text-secondary mb-6">خاص بالمشرفين والمعلمين لإدارة المحتوى والأكواد</p>

            @if ($errors->any())
                <div class="bg-red-50 text-red-800 border border-red-200 text-sm p-3 rounded-xl mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            @auth
                <div class="text-center py-4 space-y-4">
                    <p class="text-sm font-semibold">مرحباً بك <strong class="text-primary">{{ auth()->user()->name }}</strong> ({{ auth()->user()->role === 'admin' ? 'مدير النظام' : 'محاضر' }})</p>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="block bg-primary text-white font-bold py-3 rounded-xl hover:bg-primary-container transition-colors shadow-md text-center text-sm">الانتقال إلى لوحة المشرف</a>
                    @elseif(auth()->user()->role === 'instructor')
                        <a href="{{ route('instructor.dashboard') }}" class="block bg-primary text-white font-bold py-3 rounded-xl hover:bg-primary-container transition-colors shadow-md text-center text-sm">الانتقال إلى لوحة المحاضر</a>
                    @endif
                    <form action="{{ route('web.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-surface-container border border-outline-variant text-on-surface font-bold py-3 rounded-xl hover:bg-outline-variant/20 transition-colors text-sm">تسجيل الخروج</button>
                    </form>
                </div>
            @else
                <form action="{{ route('web.login') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-secondary mb-1">البريد الإلكتروني</label>
                        <input type="email" name="email" required placeholder="instructor@lms.com" class="w-full bg-surface border border-outline-variant rounded-xl px-4 py-2.5 text-on-surface focus:outline-none focus:border-primary text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-secondary mb-1">كلمة المرور</label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full bg-surface border border-outline-variant rounded-xl px-4 py-2.5 text-on-surface focus:outline-none focus:border-primary text-sm">
                    </div>
                    <button type="submit" class="w-full bg-primary text-white font-bold py-3 rounded-xl hover:bg-primary-container transition-colors shadow-md text-sm">تسجيل الدخول</button>
                </form>
            @endauth
        </div>
    </section>

    <!-- Stats Section -->
    <section class="bg-primary/5 py-8 border-y border-outline-variant">
        <div class="max-w-6xl mx-auto px-6 grid grid-cols-3 gap-6 text-center">
            <div>
                <h3 class="text-3xl font-extrabold text-primary mb-1">+{{ $stats['courses_count'] }}</h3>
                <p class="text-xs text-secondary">كورسات طبية متاحة</p>
            </div>
            <div>
                <h3 class="text-3xl font-extrabold text-primary mb-1">+{{ $stats['students_count'] }}</h3>
                <p class="text-xs text-secondary">طلاب مسجلين بالمنصة</p>
            </div>
            <div>
                <h3 class="text-3xl font-extrabold text-primary mb-1">+{{ $stats['instructors_count'] }}</h3>
                <p class="text-xs text-secondary">أطباء ومحاضرين نخبة</p>
            </div>
        </div>
    </section>

    <!-- Courses Catalog Section -->
    <section class="max-w-6xl mx-auto px-6 py-12" id="courses">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-primary mb-1">أحدث الكورسات المتاحة</h2>
            <p class="text-xs text-secondary">تصفح الدروس المسجلة والجلسات المباشرة التفاعلية</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($courses as $course)
                <div class="bg-white border border-outline-variant rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="bg-primary/10 h-32 flex items-center justify-center text-4xl">
                        🩺
                    </div>
                    <div class="p-6">
                        <span class="inline-block bg-primary/10 text-primary text-xs font-semibold px-2 py-0.5 rounded-full mb-3">
                            @if($course->type === 'recorded') دروس مسجلة @elseif($course->type === 'live') بث مباشر @else هجين @endif
                        </span>
                        <h3 class="text-lg font-bold text-on-surface mb-2">{{ $course->title }}</h3>
                        <p class="text-xs text-secondary mb-4 line-clamp-2">{{ $course->description }}</p>

                        <div class="flex items-center space-x-reverse space-x-3 border-t border-outline-variant/30 pt-3">
                            <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold text-xs">
                                {{ mb_substr($course->instructor->name ?? 'د', 0, 1, 'UTF-8') }}
                            </div>
                            <div class="text-xs font-semibold text-on-surface">
                                {{ $course->instructor->name ?? 'دكتور' }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-secondary text-sm py-12">
                    لا توجد كورسات متاحة حالياً. قم بإضافة الكورسات من لوحة المحاضر!
                </div>
            @endforelse
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-outline-variant py-6 text-center text-xs text-secondary">
        <p>&copy; {{ date('Y') }} جميع الحقوق محفوظة لـ منصة دوكاك التعليمية.</p>
    </footer>

</body>
</html>
