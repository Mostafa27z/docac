<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - سياسة الخصوصية | Doc Academy (منصة دوكاك)</title>
    <meta name="description" content="Privacy Policy for Doc Academy (منصة دوكاك التعليمية). English and Arabic version.">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css" />
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/light/style.css" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 
                        sans: ['Inter', 'Cairo', 'sans-serif'],
                        arabic: ['Cairo', 'sans-serif']
                    },
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
        body { font-family: 'Inter', 'Cairo', sans-serif; }
        .arabic-text { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-bg-base text-dark min-h-screen flex flex-col">

    {{-- Navbar --}}
    <nav class="flex justify-between items-center px-6 md:px-12 py-4 bg-white/90 backdrop-blur-xl border-b border-border-clr sticky top-0 z-50">
        <a href="/" class="flex items-center gap-3">
            <img src="/logo.jfif" alt="Doc Academy" class="w-10 h-10 rounded-xl shadow-sm" />
            <span class="text-lg font-bold text-primary">Doc Academy</span>
        </a>
        <div class="flex items-center gap-4 text-sm font-semibold">
            <a href="/" class="text-dark hover:text-primary transition-colors">Home / الرئيسية</a>
            <a href="#arabic-section" class="bg-primary/10 text-primary px-3 py-1.5 rounded-lg hover:bg-primary/20 transition-colors">العربية ↓</a>
        </div>
    </nav>

    {{-- Header --}}
    <header class="bg-gradient-to-r from-primary via-primary-light to-accent text-white py-12 px-6">
        <div class="max-w-4xl mx-auto text-center space-y-3">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Privacy Policy & Policy</h1>
            <p class="text-lg font-semibold arabic-text">سياسة الخصوصية والاستخدام - منصة دوكاك التعليمية</p>
            <p class="text-xs opacity-80 pt-2">Last Updated: September 2026 | آخر تحديث: سبتمبر 2026</p>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-6 py-12 flex-grow space-y-16">

        <!-- ==================== ENGLISH SECTION ==================== -->
        <section dir="ltr" class="bg-white border border-border-clr rounded-2xl p-6 md:p-10 shadow-sm space-y-8">
            <div class="border-b border-border-clr pb-4">
                <span class="inline-block bg-primary/10 text-primary text-xs font-bold px-3 py-1 rounded-full uppercase mb-2">English Version</span>
                <h2 class="text-2xl font-bold text-dark">Privacy Policy - Doc Academy</h2>
                <p class="text-xs text-muted mt-1">This Privacy Policy governs the collection, use, security, and device binding mechanisms employed by the Doc Academy (منصة دوكاك) platform and mobile application.</p>
            </div>

            <!-- 1. Introduction -->
            <div class="space-y-3">
                <h3 class="text-lg font-bold text-primary flex items-center gap-2">
                    <i class="ph-bold ph-shield-check text-xl"></i>
                    1. Introduction
                </h3>
                <p class="text-sm text-muted leading-relaxed">
                    Doc Academy ("we", "our", or "us") is dedicated to providing high-quality medical education courses, live sessions, and resources to students and healthcare professionals. We respect your privacy and are committed to protecting the personal data and device security of all users interacting with our web platform and mobile application.
                </p>
            </div>

            <!-- 2. Information We Collect -->
            <div class="space-y-3">
                <h3 class="text-lg font-bold text-primary flex items-center gap-2">
                    <i class="ph-bold ph-database text-xl"></i>
                    2. Information We Collect
                </h3>
                <p class="text-sm text-muted leading-relaxed">
                    To deliver our educational services seamlessly, we collect the following categories of information:
                </p>
                <ul class="list-disc list-inside text-sm text-muted space-y-2 pl-2">
                    "><strong>Account Information:</strong> Full name, email address, phone number, password, and account role (Student or Instructor).</span></li>
                    "><strong>Device Identifiers & Binding:</strong> Unique Device ID (<code class="bg-bg-base px-1.5 py-0.5 rounded text-xs text-primary">X-Device-ID</code> / active device UUID) to enforce single-device authorization rules.</span></li>
                    "><strong>Push Notification Tokens:</strong> Firebase Cloud Messaging (FCM) device tokens and platform types (Android/iOS) for delivering course announcements, quiz reminders, and live session updates.</span></li>
                    "><strong>Course & Progress Data:</strong> Course enrollments, payment/installment records, activation code usage, watched lecture duration, quiz attempts, and completion statistics.</span></li>
                    "><strong>Chat & Interactions:</strong> Messages sent within student-instructor direct and course group channels.</span></li>
                </ul>
            </div>

            <!-- 3. Device Binding & Account Protection Policy -->
            <div class="space-y-3 bg-amber-50/50 border border-amber-200 rounded-xl p-5">
                <h3 class="text-lg font-bold text-amber-800 flex items-center gap-2">
                    <i class="ph-bold ph-device-mobile text-xl"></i>
                    3. Device Binding & Single-Device Policy (Strict)
                </h3>
                <p class="text-sm text-amber-900 leading-relaxed">
                    To prevent unauthorized account sharing and protect proprietary medical course material:
                </p>
                <ul class="list-disc list-inside text-sm text-amber-900 space-y-1.5 pl-2">
                    ">Each student account is locked to a single registered primary device identifier upon login.</span></li>
                    ">Attempting to log in or access protected course endpoints from a different device without administrator clearance will result in an immediate access block (<code class="bg-white/80 px-1.5 py-0.5 rounded text-xs font-mono">403 Forbidden</code>) and session termination.</span></li>
                    ">Device reset requests must be submitted to system administrators for approval.</span></li>
                </ul>
            </div>

            <!-- 4. Video Protection & Content Delivery -->
            <div class="space-y-3">
                <h3 class="text-lg font-bold text-primary flex items-center gap-2">
                    <i class="ph-bold ph-lock text-xl"></i>
                    4. Content Protection & Video Streaming (Bunny CDN)
                </h3>
                <p class="text-sm text-muted leading-relaxed">
                    Educational video lectures are delivered securely using <strong>Bunny CDN DRM Signed URLs</strong>. Video playback tokens are dynamic and time-restricted to prevent unauthorized downloads, screen grabbing, or link redistribution. All rights to course videos and downloadable PDF materials belong exclusively to Doc Academy and its instructors.
                </p>
            </div>

            <!-- 5. How We Use Your Information -->
            <div class="space-y-3">
                <h3 class="text-lg font-bold text-primary flex items-center gap-2">
                    <i class="ph-bold ph-gear text-xl"></i>
                    5. How We Use Your Information
                </h3>
                <p class="text-sm text-muted leading-relaxed">
                    Your personal information is used exclusively to:
                </p>
                <ul class="list-disc list-inside text-sm text-muted space-y-1.5 pl-2">
                    ">Provide access to medical courses, live sessions, quizzes, and attachments.</span></li>
                    ">Verify account credentials and maintain single-device security protection.</span></li>
                    ">Track lecture watch progress, quiz results, and calculate course completion rates.</span></li>
                    ">Manage course activation codes, payments, and installment records.</span></li>
                    ">Send critical system push notifications regarding live events and course updates.</span></li>
                </ul>
            </div>

            <!-- 6. Data Storage & Security -->
            <div class="space-y-3">
                <h3 class="text-lg font-bold text-primary flex items-center gap-2">
                    <i class="ph-bold ph-keyhole text-xl"></i>
                    6. Data Storage & Security
                </h3>
                <p class="text-sm text-muted leading-relaxed">
                    We employ industry-standard encryption protocols (HTTPS/TLS) for data in transit and secure hashing (Bcrypt) for passwords. Sanctum authentication tokens ensure stateful API communications. We do not sell, rent, or trade user personal data to third parties.
                </p>
            </div>

            <!-- 7. Contact Us -->
            <div class="space-y-3 border-t border-border-clr pt-4">
                <h3 class="text-lg font-bold text-primary flex items-center gap-2">
                    <i class="ph-bold ph-envelope-simple text-xl"></i>
                    7. Contact Us
                </h3>
                <p class="text-sm text-muted leading-relaxed">
                    If you have questions regarding this Privacy Policy or need help resetting your device binding, please contact support via the official academy channels.
                </p>
            </div>
        </section>


        <!-- ==================== ARABIC SECTION ==================== -->
        <section id="arabic-section" dir="rtl" class="arabic-text bg-white border border-border-clr rounded-2xl p-6 md:p-10 shadow-sm space-y-8">
            <div class="border-b border-border-clr pb-4">
                <span class="inline-block bg-accent/10 text-accent text-xs font-bold px-3 py-1 rounded-full uppercase mb-2">النسخة العربية</span>
                <h2 class="text-2xl font-bold text-dark">سياسة الخصوصية - منصة دوكاك التعليمية (Doc Academy)</h2>
                <p class="text-xs text-muted mt-1">توضح هذه السياسة كيفية جمع البيانات واستخدامها وحمايتها، وآلية ربط الأجهزة المعتمدة في منصة وتطبيق دوكاك.</p>
            </div>

            <!-- 1. مقدمة -->
            <div class="space-y-3">
                <h3 class="text-lg font-bold text-primary flex items-center gap-2">
                    <i class="ph-bold ph-shield-check text-xl"></i>
                    ١. مقدمة
                </h3>
                <p class="text-sm text-muted leading-relaxed">
                    تلتزم منصة <strong>دوكاك التعليمية (Doc Academy)</strong> بتقديم أعلى مستويات الجودة في التعليم الطبي عبر الكورسات المسجلة والبث المباشر والاختبارات التفاعلية للأطباء والطلاب. نحن نحترم خصوصيتك ونلتزم بحماية بياناتك الشخصية وأمان حسابك وجهازك أثناء استخدام الموقع أو تطبيق الهاتف المحمول.
                </p>
            </div>

            <!-- 2. البيانات التي نجمعها -->
            <div class="space-y-3">
                <h3 class="text-lg font-bold text-primary flex items-center gap-2">
                    <i class="ph-bold ph-database text-xl"></i>
                    ٢. البيانات التي نجمعها
                </h3>
                <p class="text-sm text-muted leading-relaxed">
                    لضمان تقديم خدماتنا التعليمية بكفاءة عالية، تقوم المنصة بجمع البيانات التالية:
                </p>
                <ul class="list-disc list-inside text-sm text-muted space-y-2 pr-2">
                    "><strong>بيانات الحساب الأساسية:</strong> الاسم الكامل، البريد الإلكتروني، رقم الهاتف، كلمة المرور المفروسة، ونوع الحساب (طالب / محاضر).</span></li>
                    "><strong>معرف الجهاز وربط الأجهزة (Device Binding):</strong> المعرف الفريد للجهاز (<code class="bg-bg-base px-1.5 py-0.5 rounded text-xs text-primary">X-Device-ID</code>) لضمان حماية الحسابات ومنع الدخول المتعدد.</span></li>
                    "><strong>رموز الإشعارات (FCM Tokens):</strong> رمز جهاز الإشعارات ونوع النظام (Android / iOS) لإرسال تنبيهات الدروس والبث المباشر والامتحانات.</span></li>
                    "><strong>بيانات الكورسات والتقدم:</strong> الكورسات المشترك بها، سجلات الأقساط والدفع، أكواد التفعيل المستعملة، نسبة مشاهدة المحاضرات، ونتائج الاختبارات.</span></li>
                    "><strong>المحادثات والتواصل:</strong> الرسائل والأسئلة المرسلة داخل قنوات المحادثة مع المحاضرين والمشرفين.</span></li>
                </ul>
            </div>

            <!-- 3. سياسة حماية الحساب وربط الجهاز -->
            <div class="space-y-3 bg-amber-50/50 border border-amber-200 rounded-xl p-5">
                <h3 class="text-lg font-bold text-amber-800 flex items-center gap-2">
                    <i class="ph-bold ph-device-mobile text-xl"></i>
                    ٣. سياسة ربط الجهاز والحساب (صارم)
                </h3>
                <p class="text-sm text-amber-900 leading-relaxed">
                    لحماية المحتوى الطبي وحقوق الملكية الفكرية ومنع مشاركة الحسابات:
                </p>
                <ul class="list-disc list-inside text-sm text-amber-900 space-y-1.5 pr-2">
                    ">يتم ربط كل حساب طالب بجهاز واحد محدد تلقائياً عند تسجيل الدخول الأول.</span></li>
                    ">في حال محاولة تسجيل الدخول أو مشاهدة المحاضرات من جهاز آخر بدون إذن الإدارة، يتم حظر الطلب فوراً برسالة (هذا الحساب مسجل على جهاز آخر) وإلغاء الجلسة.</span></li>
                    ">في حال تغيير جهاز الهاتف، يجب التواصل مع الدعم الفني لإدارة المنصة لتقديم طلب إعادة ضبط الجهاز (Device Reset).</span></li>
                </ul>
            </div>

            <!-- 4. حماية الفيديوهات والمحتوى -->
            <div class="space-y-3">
                <h3 class="text-lg font-bold text-primary flex items-center gap-2">
                    <i class="ph-bold ph-lock text-xl"></i>
                    ٤. حماية الفيديوهات والبث (Bunny CDN)
                </h3>
                <p class="text-sm text-muted leading-relaxed">
                    تتم حماية ملفات المحاضرات والفيديوهات الطبية باستخدام شبكة <strong>Bunny CDN مع الروابط الموقعة مشفرة (Signed URLs)</strong>. تكون روابط التشغيل مؤقتة وديناميكية لمنع تنزيل الفيديوهات أو تسريبها أو تسجيل الشاشة. جميع حقوق الطبع والنشر للمحتوى محفوظة حصرياً لمنصة دوكاك والمحاضرين.
                </p>
            </div>

            <!-- 5. كيف نستخدم بياناتك -->
            <div class="space-y-3">
                <h3 class="text-lg font-bold text-primary flex items-center gap-2">
                    <i class="ph-bold ph-gear text-xl"></i>
                    ٥. كيف نستخدم بياناتك
                </h3>
                <p class="text-sm text-muted leading-relaxed">
                    نستخدم البيانات المجمعة للأغراض التالية فقط:
                </p>
                <ul class="list-disc list-inside text-sm text-muted space-y-1.5 pr-2">
                    ">متابعة الكورسات، والوصول للدروس، والاشتراك في جلسات البث المباشر والاختبارات.</span></li>
                    ">التحقق من هوية المستخدم وأمان الجهاز المرتبط بالحساب.</span></li>
                    ">احتساب نسبة التقدم في المشاهدة واستخراج تقارير إتمام الدروس.</span></li>
                    ">إدارة أكواد التفعيل وتتبع الأقساط والدفعات المالية.</span></li>
                    ">إرسال إشعارات التذكير بالمواعيد والمحاضرات الجديدة.</span></li>
                </ul>
            </div>

            <!-- 6. تخزين البيانات والأمان -->
            <div class="space-y-3">
                <h3 class="text-lg font-bold text-primary flex items-center gap-2">
                    <i class="ph-bold ph-keyhole text-xl"></i>
                    ٦. تخزين البيانات وحمايتها
                </h3>
                <p class="text-sm text-muted leading-relaxed">
                    تستخدم المنصة بروتوكولات التشفير القياسية (HTTPS/TLS) لحماية الاتصالات، وتشفير كلمات المرور عبر (Bcrypt). نحن لا نبيع ولا نؤجر ولا نشارك البيانات الشخصية للمستخدمين مع أي أطراف خارجية.
                </p>
            </div>

            <!-- 7. التواصل معنا -->
            <div class="space-y-3 border-t border-border-clr pt-4">
                <h3 class="text-lg font-bold text-primary flex items-center gap-2">
                    <i class="ph-bold ph-envelope-simple text-xl"></i>
                    ٧. التواصل معنا
                </h3>
                <p class="text-sm text-muted leading-relaxed">
                    إذا كان لديك أي استفسار حول سياسة الخصوصية أو تحتاج لمساعدة في تغيير جهازك المرتبط، يرجى التواصل مع إدارة منصة دوكاك من خلال قنوات التواصل المعتمدة.
                </p>
            </div>
        </section>

    </main>

    <footer class="bg-white border-t border-border-clr py-6 text-center text-xs text-muted">
        <p>&copy; 2026 Doc Academy (منصة دوكاك التعليمية). All Rights Reserved.</p>
    </footer>

</body>
</html>
