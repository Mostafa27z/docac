<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منصة دوكاك التعليمية - Docac LMS</title>
    <meta name="description" content="منصة تعليمية طبية متكاملة للأطباء والمحاضرين الطبيين - كورسات مسجلة وبث مباشر">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css" />
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/light/style.css" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        "primary": "#0047AB",
                        "primary-light": "#0088CC",
                        "accent": "#00A896",
                        "accent-light": "#2EC4B6",
                        "bg-base": "#F8F9FA",
                        "dark": "#1A202C",
                        "muted": "#718096",
                        "border-clr": "#E2E8F0",
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-gradient { background: linear-gradient(135deg, #0047AB 0%, #003380 40%, #00A896 100%); }
    </style>
</head>
<body class="bg-bg-base text-dark min-h-screen flex flex-col">

    {{-- Navbar --}}
    <nav class="flex justify-between items-center px-8 py-4 bg-white/90 backdrop-blur-xl border-b border-border-clr sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <img src="/logo.jfif" alt="Doc Academy" class="w-10 h-10 rounded-xl shadow-sm" />
            <span class="text-lg font-bold text-primary">Doc Academy</span>
        </div>
        <div class="flex items-center gap-6">
            <a href="#courses" class="text-sm font-semibold text-dark hover:text-primary transition-colors">الكورسات</a>
            <a href="/api_docs.html" target="_blank" class="text-sm font-semibold text-dark hover:text-primary transition-colors">توثيق API</a>
        </div>
    </nav>

    {{-- Hero / Login Section --}}
    <section class="max-w-6xl mx-auto px-6 py-16 flex-grow grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
        {{-- Hero Details --}}
        <div>
            <div class="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-xs font-semibold mb-6">
                <i class="ph-bold ph-rocket-launch text-sm"></i>
                <span>منصة تعليمية متكاملة للويب وتطبيقات الجوال</span>
            </div>
            <h1 class="text-4xl font-extrabold text-dark leading-tight mb-5">
                منظومة <span class="text-primary">التعليم الطبي الحديثة</span> في مكان واحد
            </h1>
            <p class="text-sm text-muted leading-relaxed mb-8">
                منصة متكاملة تسمح للطلاب بمتابعة المحاضرات الطبية عبر تطبيق الهاتف مع حماية الفيديو عن طريق Bunny CDN وإمكانية إدارة الكورسات والامتحانات للمعلمين والمشرفين.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="#courses" class="inline-flex items-center gap-2 bg-primary text-white font-semibold px-7 py-3 rounded-xl hover:bg-primary-light transition-colors shadow-md text-sm">
                    <i class="ph-bold ph-book-open-text"></i>
                    استكشف الكورسات
                </a>
                <a href="/api_docs.html" target="_blank" class="inline-flex items-center gap-2 bg-white border border-border-clr text-dark font-semibold px-7 py-3 rounded-xl hover:border-primary/30 hover:text-primary transition-all text-sm">
                    <i class="ph-bold ph-code"></i>
                    دليل الـ API
                </a>
            </div>
        </div>

        {{-- Login Box --}}
        <div class="bg-white border border-border-clr rounded-2xl p-8 shadow-lg">
            <div class="flex items-center gap-3 mb-1">
                <div class="p-2 rounded-xl bg-primary/10 text-primary">
                    <i class="ph-bold ph-sign-in text-xl"></i>
                </div>
                <h2 class="text-xl font-bold text-dark">تسجيل الدخول للوحات التحكم</h2>
            </div>
            <p class="text-xs text-muted mb-6 mr-12">خاص بالمشرفين والمعلمين لإدارة المحتوى والأكواد</p>

            @if ($errors->any())
                <div class="bg-red-50 text-red-700 border border-red-200 text-sm p-3 rounded-xl mb-4 flex items-center gap-2">
                    <i class="ph-bold ph-warning-circle"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @auth
                <div class="text-center py-4 space-y-4">
                    <p class="text-sm font-semibold">مرحباً بك <strong class="text-primary">{{ auth()->user()->name }}</strong> ({{ auth()->user()->role === 'admin' ? 'مدير النظام' : 'محاضر' }})</p>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-center gap-2 bg-primary text-white font-semibold py-3 rounded-xl hover:bg-primary-light transition-colors shadow-md text-sm">
                            <i class="ph-bold ph-squares-four"></i>
                            الانتقال إلى لوحة المشرف
                        </a>
                    @elseif(auth()->user()->role === 'instructor')
                        <a href="{{ route('instructor.dashboard') }}" class="flex items-center justify-center gap-2 bg-primary text-white font-semibold py-3 rounded-xl hover:bg-primary-light transition-colors shadow-md text-sm">
                            <i class="ph-bold ph-squares-four"></i>
                            الانتقال إلى لوحة المحاضر
                        </a>
                    @endif
                    <form action="{{ route('web.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 bg-bg-base border border-border-clr text-dark font-semibold py-3 rounded-xl hover:border-red-300 hover:text-red-600 transition-all text-sm">
                            <i class="ph-bold ph-sign-out"></i>
                            تسجيل الخروج
                        </button>
                    </form>
                </div>
            @else
                <form action="{{ route('web.login') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-muted mb-1.5">البريد الإلكتروني</label>
                        <input type="email" name="email" required placeholder="instructor@lms.com" class="w-full bg-bg-base border border-border-clr rounded-xl px-4 py-2.5 text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-muted mb-1.5">كلمة المرور</label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full bg-bg-base border border-border-clr rounded-xl px-4 py-2.5 text-dark focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm transition-all">
                    </div>
                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" name="remember" id="remember" class="rounded border-border-clr text-primary focus:ring-primary">
                        <label for="remember" class="text-xs font-semibold text-muted select-none">تذكرني / البقاء متصلاً</label>
                    </div>
                    <button type="submit" class="w-full flex items-center justify-center gap-2 bg-primary text-white font-semibold py-3 rounded-xl hover:bg-primary-light transition-colors shadow-md text-sm">
                        <i class="ph-bold ph-sign-in"></i>
                        تسجيل الدخول
                    </button>
                </form>
            @endauth
        </div>
    </section>

    {{-- Stats Section --}}
    <section class="bg-gradient-to-r from-primary/5 to-accent/5 py-10 border-y border-border-clr">
        <div class="max-w-6xl mx-auto px-6 grid grid-cols-3 gap-8 text-center">
            <div>
                <h3 class="text-3xl font-extrabold text-primary mb-1">+{{ $stats['courses_count'] }}</h3>
                <p class="text-xs text-muted font-medium">كورسات طبية متاحة</p>
            </div>
            <div>
                <h3 class="text-3xl font-extrabold text-accent mb-1">+{{ $stats['students_count'] }}</h3>
                <p class="text-xs text-muted font-medium">طلاب مسجلين بالمنصة</p>
            </div>
            <div>
                <h3 class="text-3xl font-extrabold text-primary-light mb-1">+{{ $stats['instructors_count'] }}</h3>
                <p class="text-xs text-muted font-medium">أطباء ومحاضرين نخبة</p>
            </div>
        </div>
    </section>

    {{-- Courses Catalog --}}
    <section class="max-w-6xl mx-auto px-6 py-14" id="courses">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-bold text-dark mb-2">أحدث الكورسات المتاحة</h2>
            <p class="text-sm text-muted">تصفح الدروس المسجلة والجلسات المباشرة التفاعلية</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($courses as $course)
                <div class="bg-white border border-border-clr rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300 group">
                    <div class="bg-gradient-to-br from-primary/10 to-accent/10 h-36 flex items-center justify-center">
                        <i class="ph-light ph-stethoscope text-5xl text-primary/40 group-hover:text-primary/60 transition-colors"></i>
                    </div>
                    <div class="p-6">
                        <span class="inline-flex items-center gap-1 bg-primary/8 text-primary text-xs font-semibold px-2.5 py-1 rounded-full mb-3">
                            @if($course->type === 'recorded')
                                <i class="ph-bold ph-video-camera text-xs"></i> دروس مسجلة
                            @elseif($course->type === 'live')
                                <i class="ph-bold ph-broadcast text-xs"></i> بث مباشر
                            @else
                                <i class="ph-bold ph-arrows-merge text-xs"></i> هجين
                            @endif
                        </span>
                        <h3 class="text-lg font-bold text-dark mb-2">{{ $course->title }}</h3>
                        <p class="text-xs text-muted mb-4 line-clamp-2 leading-relaxed">{{ $course->description }}</p>

                        <div class="flex items-center gap-3 border-t border-border-clr pt-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-accent flex items-center justify-center text-white font-bold text-xs">
                                {{ mb_substr($course->instructor->name ?? 'د', 0, 1, 'UTF-8') }}
                            </div>
                            <span class="text-sm font-semibold text-dark">{{ $course->instructor->name ?? 'دكتور' }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-muted text-sm py-16">
                    <i class="ph-light ph-book-open-text text-5xl text-muted/40 block mb-3"></i>
                    لا توجد كورسات متاحة حالياً. قم بإضافة الكورسات من لوحة المحاضر!
                </div>
            @endforelse
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-white border-t border-border-clr py-6 text-center text-xs text-muted">
        <p>&copy; {{ date('Y') }} جميع الحقوق محفوظة لـ منصة دوكاك التعليمية.</p>
    </footer>

</body>
</html>
