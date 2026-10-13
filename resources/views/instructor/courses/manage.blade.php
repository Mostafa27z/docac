@extends('layouts.panel')

@section('title', 'إدارة الكورس - ' . $course->title)
@section('role_title', 'لوحة المحاضر')

@section('page_title')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full">
        <div class="flex items-center gap-3">
            <span>إدارة: {{ $course->title }}</span>
            @if($course->status === 'published')
                <x-badge variant="success">منشور</x-badge>
            @else
                <x-badge variant="warning">مسودة</x-badge>
            @endif
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('instructor.courses.analytics', $course->id) }}" class="inline-flex items-center gap-1.5 bg-[#00A896] hover:bg-[#00A896]/90 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all">
                <i class="ph-bold ph-chart-line-up text-sm"></i>
                نتائج وتقدم الطلاب
            </a>
            <button type="button" onclick="openEditCourseModal()" class="inline-flex items-center gap-1.5 bg-[#0088CC] hover:bg-[#0088CC]/90 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all">
                <i class="ph-bold ph-pencil-simple text-sm"></i>
                تعديل بيانات الكورس
            </button>
            <button type="button" onclick="openDeleteCourseModal()" class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all">
                <i class="ph-bold ph-trash text-sm"></i>
                حذف الكورس
            </button>
        </div>
    </div>
@endsection

@section('content')
    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border-r-4 border-red-500 rounded-xl text-sm text-red-600 font-medium">
            <ul class="list-disc mr-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Top Actions & Pricing --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2">
            <x-card class="flex items-center justify-between h-full">
                <div>
                    <h2 class="text-base font-bold text-[#1A202C]">حالة نشر الكورس</h2>
                    <p class="text-xs text-[#718096] mt-1">تحديد ما إذا كان الكورس مرئياً للطلاب على التطبيق أم لا.</p>
                </div>
                <form action="{{ route('instructor.courses.publish', $course->id) }}" method="POST">
                    @csrf
                    @if($course->status === 'published')
                        <button type="submit" class="inline-flex items-center gap-2 bg-amber-500 text-white font-semibold px-5 py-2.5 rounded-xl hover:bg-amber-600 transition-colors text-sm">
                            <i class="ph-bold ph-arrow-counter-clockwise text-lg"></i>
                            تحويل إلى مسودة
                        </button>
                    @else
                        <x-btn-primary icon="rocket-launch" type="submit">نشر الكورس للطلاب</x-btn-primary>
                    @endif
                </form>
            </x-card>
        </div>

        <div class="lg:col-span-1">
            <x-card class="h-full">
                <form action="{{ route('instructor.courses.price', $course->id) }}" method="POST" class="flex gap-2 items-end">
                    @csrf
                    <div class="flex-grow">
                        <x-form-input label="سعر الكورس الافتراضي" name="price" type="number" step="0.01" min="0" value="{{ $course->price }}" placeholder="سعر الكورس" />
                    </div>
                    <x-btn-primary icon="floppy-disk" type="submit">حفظ</x-btn-primary>
                </form>
            </x-card>
        </div>
    </div>

    {{-- 1. Sections & Lessons --}}
    <x-card class="mb-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="p-2.5 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                <i class="ph-bold ph-list-numbers text-xl"></i>
            </div>
            <h2 class="text-lg font-bold text-[#1A202C]">الأقسام والمحاضرات (Curriculum)</h2>
        </div>

        {{-- Add Section --}}
        <form action="{{ route('instructor.sections.store', $course->id) }}" method="POST" class="flex gap-4 items-end mb-6">
            @csrf
            <div class="flex-grow">
                <x-form-input label="إضافة قسم جديد (Section)" name="title" :required="true" placeholder="مثال: الفصل الأول - مقدمة تشريح الجهاز الدوري" />
            </div>
            <x-btn-primary icon="plus" type="submit">إضافة قسم</x-btn-primary>
        </form>

        {{-- Sections & Lessons List --}}
        @forelse($course->sections as $section)
            <div class="bg-[#F8F9FA] border border-[#E2E8F0] rounded-2xl p-5 mb-5">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2 pb-3 border-b border-[#E2E8F0]">
                    <h3 class="text-base font-bold text-[#0047AB] flex items-center gap-2">
                        <i class="ph-bold ph-folder-open text-lg"></i>
                        {{ $section->title }}
                    </h3>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="openEditSectionModal({{ $section->id }}, '{{ addslashes($section->title) }}')" class="inline-flex items-center gap-1.5 bg-[#0088CC]/10 hover:bg-[#0088CC] text-[#0088CC] hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                            <i class="ph-bold ph-pencil text-sm"></i>
                            تعديل القسم
                        </button>
                        <form action="{{ route('instructor.sections.destroy', $section->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا القسم بجميع دروسه ومحتوياته؟ لا يمكن التراجع عن هذا الإجراء.')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 bg-red-600/10 hover:bg-red-600 text-red-600 hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                <i class="ph-bold ph-trash text-sm"></i>
                                حذف القسم
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Lessons --}}
                <ul class="space-y-3 mb-4">
                    @forelse($section->lessons as $lesson)
                        <li class="p-4 bg-white border border-[#E2E8F0] rounded-xl flex justify-between items-center shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg {{ $lesson->type === 'video' ? ($lesson->is_youtube ? 'bg-red-50 text-red-600' : 'bg-[#0088CC]/10 text-[#0088CC]') : 'bg-[#00A896]/10 text-[#00A896]' }}">
                                    @if($lesson->type === 'video')
                                        <i class="ph-bold ph-{{ $lesson->is_youtube ? 'youtube-logo' : 'play-circle' }} text-lg"></i>
                                    @else
                                        <i class="ph-bold ph-clipboard-text text-lg"></i>
                                    @endif
                                </div>
                                <div>
                                    <strong class="text-[#1A202C] text-sm">{{ $lesson->title }}</strong>
                                    <span class="text-xs text-[#718096] mr-2">({{ $lesson->type === 'video' ? ($lesson->is_youtube ? 'فيديو YouTube' : 'فيديو Bunny') : 'امتحان MCQ' }})</span>
                                    @if($lesson->is_youtube)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded-lg">
                                            <i class="ph-bold ph-youtube-logo text-xs"></i> YouTube
                                        </span>
                                    @elseif($lesson->type === 'video' && $lesson->video_url)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#0088CC] bg-[#0088CC]/10 border border-[#0088CC]/20 px-2 py-0.5 rounded-lg">
                                            <i class="ph-bold ph-cloud-arrow-up text-xs"></i> Bunny
                                        </span>
                                    @endif
                                    @if($lesson->is_preview)
                                        <x-badge variant="success">معاينة مجانية</x-badge>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-2 items-center flex-wrap justify-end">
                                @if($lesson->type === 'video' && $lesson->video_url)
                                    <button onclick="previewLessonVideo('{{ $lesson->video_url }}', '{{ config('services.bunny.stream_library_id') }}')" class="inline-flex items-center gap-1.5 {{ $lesson->is_youtube ? 'bg-red-50 hover:bg-red-600 text-red-600 hover:text-white' : 'bg-[#2EC4B6]/10 hover:bg-[#2EC4B6] text-[#00A896] hover:text-white' }} text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                        <i class="ph-bold ph-play text-sm"></i>
                                        عرض الفيديو
                                    </button>
                                @endif
                                @if($lesson->type === 'quiz')
                                    <a href="#quiz-builder-{{ $lesson->id }}" class="inline-flex items-center gap-1.5 bg-[#0047AB]/10 hover:bg-[#0047AB] text-[#0047AB] hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                        <i class="ph-bold ph-pencil-simple text-sm"></i>
                                        إدارة الأسئلة
                                    </a>
                                @endif
                                <button type="button" onclick="openEditLessonModal({{ $lesson->id }}, '{{ addslashes($lesson->title) }}', '{{ addslashes($lesson->description ?? '') }}', {{ $lesson->is_preview ? 1 : 0 }}, {{ $lesson->video_duration_seconds ?? 0 }}, '{{ addslashes($lesson->video_url ?? '') }}')" class="inline-flex items-center gap-1.5 bg-[#0088CC]/10 hover:bg-[#0088CC] text-[#0088CC] hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                    <i class="ph-bold ph-pencil text-sm"></i>
                                    تعديل
                                </button>
                                <form action="{{ route('instructor.lessons.destroy', $lesson->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا الدرس؟ لا يمكن التراجع عن هذا الإجراء.')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 bg-red-600/10 hover:bg-red-600 text-red-600 hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                        <i class="ph-bold ph-trash text-sm"></i>
                                        حذف
                                    </button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="text-[#718096] text-sm py-2 px-1">لا توجد دروس مضافة بهذا القسم حتى الآن.</li>
                    @endforelse
                </ul>

                {{-- Add Lesson Form --}}
                <details class="bg-white border border-[#E2E8F0] rounded-xl p-4">
                    <summary class="cursor-pointer font-semibold text-[#1A202C] text-sm flex items-center gap-2 select-none">
                        <i class="ph-bold ph-plus-circle text-[#0047AB] text-lg"></i>
                        <span>إضافة درس أو محاضرة لهذا القسم (Bunny أو YouTube)</span>
                    </summary>
                    <form id="chunk-upload-form-{{ $section->id }}" onsubmit="uploadChunkedLesson(event, {{ $section->id }}, '{{ route('instructor.lessons.chunked', $section->id) }}')" class="mt-4 space-y-4">
                        @csrf
                        <x-form-input label="عنوان الدرس" :name="'title-' . $section->id" :required="true" placeholder="مثال: شرح رسم القلب الدورة الأذينية" />
                        <div>
                            <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">نوع المحتوى</label>
                            <select id="type-{{ $section->id }}" name="type" required onchange="toggleLessonType({{ $section->id }})" class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-4 py-2.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB] transition-all">
                                <option value="video">فيديو محاضرة مسجلة (Bunny أو YouTube)</option>
                                <option value="quiz">امتحان MCQ واختبار تقييمي</option>
                            </select>
                        </div>

                        {{-- Video Source Selector --}}
                        <div id="video-source-wrapper-{{ $section->id }}" class="space-y-3">
                            <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">طريقة إضافة الفيديو</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-center gap-2.5 p-3 bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl cursor-pointer hover:border-[#0047AB] transition-all">
                                    <input type="radio" name="video_source_{{ $section->id }}" value="bunny" checked onchange="toggleVideoSource({{ $section->id }}, 'bunny')" class="text-[#0047AB] focus:ring-[#0047AB]">
                                    <div class="flex items-center gap-2">
                                        <i class="ph-bold ph-cloud-arrow-up text-[#0088CC] text-lg"></i>
                                        <span class="text-sm font-medium text-[#1A202C]">رفع ملف (Bunny Stream)</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-2.5 p-3 bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl cursor-pointer hover:border-red-500 transition-all">
                                    <input type="radio" name="video_source_{{ $section->id }}" value="youtube" onchange="toggleVideoSource({{ $section->id }}, 'youtube')" class="text-red-600 focus:ring-red-500">
                                    <div class="flex items-center gap-2">
                                        <i class="ph-bold ph-youtube-logo text-red-600 text-lg"></i>
                                        <span class="text-sm font-medium text-[#1A202C]">رابط يوتيوب (YouTube Link)</span>
                                    </div>
                                </label>
                            </div>

                            {{-- File Upload Group for Bunny --}}
                            <div id="video-file-group-{{ $section->id }}">
                                <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">اختر ملف الفيديو (مهما كان الحجم - يدعم الرفع بالأجزاء والضغط التلقائي)</label>
                                <input type="file" id="video-file-{{ $section->id }}" accept="video/mp4,video/quicktime,video/mkv" onchange="handleVideoFileChange({{ $section->id }})" class="w-full text-sm text-[#718096] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#0047AB]/10 file:text-[#0047AB] hover:file:bg-[#0047AB]/20 transition-all">
                                
                                {{-- Compression Alert & Toggle --}}
                                <div id="compression-container-{{ $section->id }}" style="display: none;" class="mt-3 p-3 bg-indigo-50/80 border border-indigo-200 rounded-xl transition-all">
                                    <label class="flex items-start gap-2.5 cursor-pointer">
                                        <input type="checkbox" id="compress-video-{{ $section->id }}" value="1" class="mt-0.5 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                        <div class="flex-1">
                                            <span class="block text-xs font-bold text-indigo-950 flex items-center gap-1.5">
                                                <i class="ph-bold ph-lightning text-indigo-600 text-sm"></i>
                                                <span>ضغط وتقليص حجم الفيديو محلياً قبل الرفع (توفير حتى 75% من الحجم وتسريع الرفع)</span>
                                            </span>
                                            <p class="text-[11px] text-indigo-700 mt-0.5 leading-relaxed" id="compression-info-{{ $section->id }}">
                                                حجم الملف: <strong id="file-size-text-{{ $section->id }}">0 MB</strong>. يتم الضغط محلياً عبر المتصفح لتسريع الرفع وحفظ باقة الإنترنت.
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- YouTube Link Group --}}
                            <div id="youtube-url-group-{{ $section->id }}" style="display: none;">
                                <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">رابط فيديو YouTube</label>
                                <input type="url" id="youtube-url-{{ $section->id }}" placeholder="مثال: https://www.youtube.com/watch?v=xxxx أو https://youtu.be/xxxx" class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-4 py-2.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all text-left dir-ltr">
                                <p class="text-xs text-[#718096] mt-1">يدعم روابط يوتيوب المباشرة والقصيرة والفيديوهات القصيرة Shorts وقوائم التشغيل.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="is-preview-{{ $section->id }}" value="1" class="rounded border-[#E2E8F0] text-[#0047AB] focus:ring-[#0047AB]">
                            <label for="is-preview-{{ $section->id }}" class="text-sm font-medium text-[#4A5568]">السماح بالمعاينة المجانية للدرس قبل الاشتراك</label>
                        </div>

                        {{-- Progress Bar --}}
                        <div id="progress-container-{{ $section->id }}" class="hidden mt-4">
                            <div class="flex justify-between text-xs font-semibold mb-1.5">
                                <span id="progress-status-{{ $section->id }}" class="text-[#718096]">جاري تجهيز وبدء الرفع...</span>
                                <span id="progress-percent-{{ $section->id }}" class="text-[#0047AB] font-bold">0%</span>
                            </div>
                            <div class="w-full bg-[#E2E8F0] rounded-full h-2.5 overflow-hidden">
                                <div id="progress-bar-{{ $section->id }}" class="bg-gradient-to-r from-[#0047AB] to-[#00A896] h-full w-0 transition-all duration-200 rounded-full"></div>
                            </div>
                        </div>

                        <x-btn-primary icon="upload-simple" type="submit" :id="'submit-btn-' . $section->id">حفظ الدرس</x-btn-primary>
                    </form>
                </details>
            </div>
        @empty
            <p class="text-[#718096] text-sm">قم بإضافة أقسام تنظيمية للبدء في رفع المحاضرات.</p>
        @endforelse
    </x-card>

    {{-- 2. Quizzes & Questions --}}
    <x-card class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="p-2.5 rounded-xl bg-[#00A896]/10 text-[#00A896]">
                <i class="ph-bold ph-exam text-xl"></i>
            </div>
            <h2 class="text-lg font-bold text-[#1A202C]">الامتحانات والاختبارات التقييمية (Quizzes & MCQs)</h2>
        </div>
        <p class="text-[#718096] text-sm mb-6 mr-12">قم بإنشاء امتحانات للدروس وإضافة أسئلة اختيار من متعدد مع تحديد الإجابة الصحيحة.</p>

        {{-- Add Quiz to Existing Lesson --}}
        <details class="bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl p-4 mb-6 mr-12">
            <summary class="cursor-pointer font-bold text-[#1A202C] text-sm flex items-center gap-2 select-none">
                <i class="ph-bold ph-plus-circle text-[#00A896] text-lg"></i>
                <span>إنشاء امتحان جديد وربطه بدرس قائم</span>
            </summary>
            <form action="{{ route('instructor.quizzes.storeForCourse', $course->id) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">اختر الدرس المرتبط</label>
                    <select name="lesson_id" required class="w-full bg-white border border-[#E2E8F0] rounded-xl px-4 py-2.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB] transition-all">
                        <option value="">-- اختر درس من الكورس --</option>
                        @foreach($course->sections as $sec)
                            <optgroup label="{{ $sec->title }}">
                                @foreach($sec->lessons as $les)
                                    @if(!$les->quiz)
                                        <option value="{{ $les->id }}">{{ $les->title }} ({{ $les->type === 'video' ? 'فيديو' : 'امتحان' }})</option>
                                    @endif
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <x-form-input label="عنوان الامتحان" name="title" :required="true" placeholder="مثال: اختبار تحديد المستوى" />
                <div class="grid grid-cols-2 gap-4">
                    <x-form-input label="نسبة النجاح (%)" name="pass_percentage" type="number" :required="true" value="60" />
                    <x-form-input label="الحد الأقصى للمحاولات" name="attempts_allowed" type="number" value="3" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <x-form-input label="مدة الامتحان (بالدقائق - اختياري)" name="time_limit_minutes" type="number" placeholder="مثال: 30" />
                </div>
                <x-btn-primary icon="gear" type="submit">إنشاء الامتحان</x-btn-primary>
            </form>
        </details>

        @foreach($course->sections as $section)
            @foreach($section->lessons as $lesson)
                @if($lesson->type === 'quiz' || $lesson->quiz)
                    <div id="quiz-builder-{{ $lesson->id }}" class="bg-[#F8F9FA] border border-[#E2E8F0] rounded-2xl p-5 mb-5">
                        <h3 class="text-base font-bold text-[#0047AB] mb-4 flex items-center gap-2">
                            <i class="ph-bold ph-check-square text-lg"></i>
                            امتحان الدرس: {{ $lesson->title }} ({{ $lesson->type === 'video' ? 'درس فيديو' : 'درس امتحان' }})
                        </h3>

                        @if(!$lesson->quiz)
                            <form action="{{ route('instructor.quizzes.store', $lesson->id) }}" method="POST" class="space-y-4">
                                @csrf
                                <x-form-input label="عنوان الامتحان" name="title" :required="true" :value="'امتحان ' . $lesson->title" />
                                <div class="grid grid-cols-2 gap-4">
                                    <x-form-input label="نسبة النجاح (%)" name="pass_percentage" type="number" :required="true" value="60" />
                                    <x-form-input label="الحد الأقصى للمحاولات" name="attempts_allowed" type="number" value="3" />
                                </div>
                                <x-btn-primary icon="gear" type="submit">إنشاء إعدادات الامتحان</x-btn-primary>
                            </form>
                        @else
                            <div class="bg-white border border-[#E2E8F0] p-4 rounded-xl mb-4 text-sm text-[#718096] flex items-center justify-between flex-wrap gap-4">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <x-badge variant="info">نسبة النجاح: {{ $lesson->quiz->pass_percentage }}%</x-badge>
                                    <x-badge variant="neutral">عدد الأسئلة: {{ $lesson->quiz->questions->count() }}</x-badge>
                                    @if($lesson->quiz->time_limit_minutes)
                                        <x-badge variant="neutral">المدة: {{ $lesson->quiz->time_limit_minutes }} دقيقة</x-badge>
                                    @endif
                                    @if($lesson->quiz->attempts_allowed)
                                        <x-badge variant="neutral">المحاولات: {{ $lesson->quiz->attempts_allowed }}</x-badge>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <button type="button" onclick="openBulkQuestionsModal({{ $lesson->quiz->id }}, '{{ addslashes($lesson->quiz->title) }}')" class="inline-flex items-center gap-1.5 bg-[#00A896] hover:bg-[#00A896]/90 text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all shadow-sm">
                                        <i class="ph-bold ph-list-plus text-sm"></i>
                                        إضافة أسئلة دفعة واحدة (Bulk)
                                    </button>
                                    <button type="button" onclick="openEditQuizModal({{ $lesson->quiz->id }}, '{{ addslashes($lesson->quiz->title) }}', {{ $lesson->quiz->pass_percentage }}, {{ $lesson->quiz->time_limit_minutes ?? 'null' }}, {{ $lesson->quiz->attempts_allowed ?? 'null' }})" class="inline-flex items-center gap-1.5 bg-[#0088CC]/10 hover:bg-[#0088CC] text-[#0088CC] hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                        <i class="ph-bold ph-pencil text-sm"></i>
                                        تعديل بيانات الامتحان
                                    </button>
                                    <form action="{{ route('instructor.quizzes.destroy', $lesson->quiz->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الامتحان بالكامل وجميع أسئلته؟')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 bg-red-600/10 hover:bg-red-600 text-red-600 hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                            <i class="ph-bold ph-trash text-sm"></i>
                                            حذف الامتحان
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Existing Questions --}}
                            <div class="space-y-3 mb-6">
                                @foreach($lesson->quiz->questions as $qIndex => $question)
                                    <div class="bg-white border border-[#E2E8F0] p-4 rounded-xl shadow-sm">
                                        <div class="flex justify-between items-start gap-4">
                                            <div class="flex-grow">
                                                <strong class="text-[#1A202C] text-sm flex items-center gap-2">
                                                    <span class="w-6 h-6 rounded-full bg-[#0047AB]/10 text-[#0047AB] flex items-center justify-center text-xs font-bold">{{ $qIndex + 1 }}</span>
                                                    <span>{{ $question->question_text }}</span>
                                                    <span class="text-xs text-[#718096] font-normal">({{ $question->points }} {{ $question->points == 1 ? 'درجة' : 'درجات' }})</span>
                                                </strong>
                                                <ul class="mt-3 space-y-1.5 text-sm text-[#718096] mr-8">
                                                    @foreach($question->options as $opt)
                                                        <li class="flex items-center gap-2 {{ $opt->is_correct ? 'text-[#00A896] font-semibold' : '' }}">
                                                            <i class="ph-bold ph-{{ $opt->is_correct ? 'check-circle' : 'circle' }} text-sm"></i>
                                                            {{ $opt->option_text }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                @php
                                                    $optionsList = $question->options->pluck('option_text')->toArray();
                                                    $correctIdx = $question->options->values()->search(fn($o) => $o->is_correct);
                                                    if ($correctIdx === false) { $correctIdx = 0; }
                                                @endphp
                                                <button type="button" onclick='openEditQuestionModal({{ $question->id }}, @json($question->question_text), {{ $question->points }}, @json($optionsList), {{ $correctIdx }})' class="inline-flex items-center gap-1.5 bg-[#0088CC]/10 hover:bg-[#0088CC] text-[#0088CC] hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                                    <i class="ph-bold ph-pencil-simple text-sm"></i>
                                                    تعديل السؤال
                                                </button>
                                                <form action="{{ route('instructor.questions.destroy', $question->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا السؤال؟')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center gap-1.5 bg-red-600/10 hover:bg-red-600 text-red-600 hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                                        <i class="ph-bold ph-trash text-sm"></i>
                                                        حذف
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Add New Question --}}
                            <form action="{{ route('instructor.questions.store', $lesson->quiz->id) }}" method="POST" class="bg-white border border-[#E2E8F0] p-5 rounded-xl space-y-4">
                                @csrf
                                <h4 class="font-bold text-[#1A202C] flex items-center gap-2">
                                    <i class="ph-bold ph-plus text-[#0047AB]"></i>
                                    إضافة سؤال اختيار من متعدد جديد
                                </h4>
                                <x-form-input label="نص السؤال" name="question_text" :required="true" placeholder="مثال: ما هي الموجة المسئولة عن انقباض الأذينين؟" />
                                <x-form-input label="درجة السؤال" name="points" type="number" :required="true" value="1" />
                                <div>
                                    <label class="block text-sm font-semibold text-[#4A5568] mb-2">الخيارات المتاحة (حدد الإجابة الصحيحة)</label>
                                    <div class="space-y-2">
                                        @for($i = 0; $i < 3; $i++)
                                        <div class="flex items-center gap-2">
                                            <input type="radio" name="correct_option_index" value="{{ $i }}" {{ $i === 0 ? 'checked' : '' }} class="text-[#0047AB] focus:ring-[#0047AB]">
                                            <input type="text" name="options[{{ $i }}][text]" {{ $i < 2 ? 'required' : '' }} class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-4 py-1.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB]" placeholder="الخيار {{ $i === 0 ? 'الأول' : ($i === 1 ? 'الثاني' : 'الثالث (اختياري)') }}">
                                        </div>
                                        @endfor
                                    </div>
                                </div>
                                <x-btn-primary icon="floppy-disk" type="submit">حفظ السؤال</x-btn-primary>
                            </form>
                        @endif
                    </div>
                @endif
            @endforeach
        @endforeach
    </x-card>

    {{-- 3. Attachments --}}
    <x-card class="mb-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="p-2.5 rounded-xl bg-[#0088CC]/10 text-[#0088CC]">
                <i class="ph-bold ph-paperclip text-xl"></i>
            </div>
            <h2 class="text-lg font-bold text-[#1A202C]">المرفقات والملفات الدراسية (Bunny Storage)</h2>
        </div>

        <form action="{{ route('instructor.attachments.store', $course->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 mb-6">
            @csrf
            <x-form-input label="عنوان المستند/الملف" name="title" :required="true" placeholder="مثال: مذكرة الشرح الشاملة PDF" />
            <div>
                <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">اختر الملف (PDF, DOCX, ZIP)</label>
                <input type="file" name="attachment" required class="w-full text-sm text-[#718096] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#0047AB]/10 file:text-[#0047AB] hover:file:bg-[#0047AB]/20 transition-all">
            </div>
            <x-btn-primary icon="upload-simple" type="submit">رفع الملف إلى Bunny Storage</x-btn-primary>
        </form>

        <x-data-table :headers="['عنوان الملف', 'اسم الملف الأصلي', 'حجم الملف', 'تاريخ الرفع', 'التحكم']">
            @forelse($course->files as $file)
                <tr class="border-b border-[#E2E8F0] hover:bg-[#F8F9FA] transition-colors">
                    <td class="py-4 px-4 font-medium text-[#1A202C]">{{ $file->title }}</td>
                    <td class="py-4 px-4 text-[#718096] text-sm">{{ $file->file_name }}</td>
                    <td class="py-4 px-4 text-[#718096] text-sm">{{ round($file->file_size_bytes / 1024 / 1024, 2) }} MB</td>
                    <td class="py-4 px-4 text-[#718096] text-sm">{{ $file->created_at->format('Y-m-d') }}</td>
                    <td class="py-2 px-4">
                        <form action="{{ route('instructor.attachments.destroy', $file->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الملف؟')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 rounded-xl transition-all" title="حذف الملف">
                                <i class="ph-bold ph-trash text-sm"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-[#718096] text-sm">لا توجد ملفات مرفقة بهذا الكورس.</td>
                </tr>
            @endforelse
        </x-data-table>
    </x-card>

    {{-- 4. Live Sessions --}}
    <x-card class="mb-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="p-2.5 rounded-xl bg-purple-600/10 text-purple-600">
                <i class="ph-bold ph-video-camera text-xl"></i>
            </div>
            <h2 class="text-lg font-bold text-[#1A202C]">جلسات البث المباشر (Live Sessions)</h2>
        </div>

        {{-- Form to Schedule new live session --}}
        <form action="{{ route('instructor.live-sessions.store', $course->id) }}" method="POST" class="space-y-4 mb-8 bg-[#F8F9FA] p-5 rounded-2xl border border-[#E2E8F0]">
            @csrf
            <h3 class="font-bold text-sm text-[#1A202C] flex items-center gap-1.5 mb-2">
                <i class="ph-bold ph-plus-circle text-purple-600"></i>
                جدولة بث مباشر جديد
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-input label="عنوان الجلسة" name="title" :required="true" placeholder="مثال: مناقشة أسئلة الامتحان والرد على الاستفسارات" />
                <x-form-select label="مزود الخدمة" name="meeting_provider" :required="true">
                    <option value="zoom">Zoom Meetings</option>
                    <option value="google_meet">Google Meet</option>
                </x-form-select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-input label="موعد البدء" name="start_at" type="datetime-local" :required="true" />
                <x-form-input label="موعد الانتهاء المتوقع" name="end_at" type="datetime-local" :required="true" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-input label="رابط الاجتماع (Meeting URL)" name="meeting_url" type="url" :required="true" placeholder="https://zoom.us/j/..." />
                <x-form-input label="معرف الاجتماع (Meeting ID / اختياري)" name="meeting_id" placeholder="987 654 3210" />
            </div>

            <x-form-textarea label="وصف أو ملاحظات الجلسة (اختياري)" name="description" rows="2" placeholder="اكتب تفاصيل أو تنويهات للطلاب بخصوص هذا البث..." />

            <x-btn-primary icon="plus" type="submit">إضافة وجدولة الجلسة</x-btn-primary>
        </form>

        {{-- Sessions List --}}
        <x-data-table :headers="['عنوان الجلسة', 'الموعد والوقت', 'المنصة', 'الحالة', 'رابط الانضمام', 'الإجراءات']">
            @forelse($course->liveSessions as $session)
                <tr class="border-b border-[#E2E8F0] hover:bg-[#F8F9FA] transition-colors">
                    <td class="py-4 px-4 font-semibold text-[#1A202C]">{{ $session->title }}</td>
                    <td class="py-4 px-4 text-xs text-[#718096]">
                        <div>البدء: {{ $session->start_at->format('Y-m-d H:i') }}</div>
                        <div class="mt-0.5">الانتهاء: {{ $session->end_at->format('Y-m-d H:i') }}</div>
                    </td>
                    <td class="py-4 px-4 text-sm text-[#718096]">
                        @if($session->meeting_provider === 'zoom')
                            <x-badge variant="info">Zoom</x-badge>
                        @else
                            <x-badge variant="teal">Google Meet</x-badge>
                        @endif
                    </td>
                    <td class="py-4 px-4">
                        @if($session->status === 'scheduled')
                            <x-badge variant="warning">مجدول</x-badge>
                        @elseif($session->status === 'live')
                            <x-badge variant="success">بث مباشر الآن</x-badge>
                        @else
                            <x-badge variant="neutral">منتهي</x-badge>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-sm">
                        <a href="{{ $session->meeting_url }}" target="_blank" class="inline-flex items-center gap-1 text-[#0047AB] hover:underline font-semibold">
                            <i class="ph-bold ph-arrow-square-out"></i>
                            رابط الجلسة
                        </a>
                    </td>
                    <td class="py-4 px-4">
                        <form action="{{ route('instructor.live-sessions.destroy', $session->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في إلغاء وحذف جلسة البث هذه؟')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 bg-red-600/10 hover:bg-red-600 text-red-600 hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                <i class="ph-bold ph-trash"></i>
                                حذف الجلسة
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-[#718096] text-sm">لا توجد جلسات بث مباشر مجدولة لهذا الكورس.</td>
                </tr>
            @endforelse
        </x-data-table>
    </x-card>

    {{-- Chunked Upload JS & Video Handlers --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tus-js-client@2.3.1/dist/tus.min.js"></script>
    <script src="https://unpkg.com/@ffmpeg/ffmpeg@0.11.6/dist/ffmpeg.min.js"></script>
    <script>
    let ffmpegInstance = null;

    async function getFFmpeg() {
        if (!ffmpegInstance) {
            if (typeof FFmpeg === 'undefined' || !FFmpeg.createFFmpeg) {
                throw new Error('مكتبة FFmpeg غير متوفرة في المتصفح.');
            }
            ffmpegInstance = FFmpeg.createFFmpeg({ log: false });
            await ffmpegInstance.load();
        }
        return ffmpegInstance;
    }

    function formatBytes(bytes, decimals = 1) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    function handleVideoFileChange(sectionId) {
        const fileInput = document.getElementById(`video-file-${sectionId}`);
        const compressionContainer = document.getElementById(`compression-container-${sectionId}`);
        const compressCheckbox = document.getElementById(`compress-video-${sectionId}`);
        const sizeText = document.getElementById(`file-size-text-${sectionId}`);

        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            if (compressionContainer) compressionContainer.style.display = 'none';
            return;
        }

        const file = fileInput.files[0];
        const sizeBytes = file.size;
        const sizeFormatted = formatBytes(sizeBytes);

        const compressionInfo = document.getElementById(`compression-info-${sectionId}`);

        if (sizeText) sizeText.innerText = sizeFormatted;
        if (compressionContainer) compressionContainer.style.display = 'block';

        // NOTE: Large files (>= 250MB) should NOT be compressed in-browser via WebAssembly,
        // because 32-bit WASM memory will exhaust RAM and crash.
        // Direct TUS chunked upload to Bunny Stream handles large files natively.
        const isVeryLarge = sizeBytes >= (250 * 1024 * 1024);
        if (compressCheckbox) {
            if (isVeryLarge) {
                compressCheckbox.checked = false;
                if (compressionInfo) {
                    compressionInfo.innerHTML = `حجم الملف: <strong>${sizeFormatted}</strong>. <span class="text-amber-800 font-semibold block mt-1">💡 تنبيه: للملفات الكبيرة يُنصح بعدم الضغط محلياً والاعتماد على الرفع المباشر والسريع عبر السحابة (TUS) لتجنب استهلاك ذاكرة المتصفح.</span>`;
                }
            } else {
                compressCheckbox.checked = true;
                if (compressionInfo) {
                    compressionInfo.innerHTML = `حجم الملف: <strong>${sizeFormatted}</strong>. يتم الضغط محلياً عبر المتصفح لتسريع الرفع وحفظ باقة الإنترنت.`;
                }
            }
        }
    }

    async function compressVideoClientSide(file, onProgress) {
        const ffmpeg = await getFFmpeg();
        
        ffmpeg.setProgress(({ ratio }) => {
            if (onProgress && typeof onProgress === 'function') {
                const pct = Math.min(100, Math.max(0, Math.round(ratio * 100)));
                onProgress(pct);
            }
        });

        const timestamp = Date.now();
        const inputName = `input_${timestamp}.mp4`;
        const outputName = `output_${timestamp}.mp4`;

        const fileData = new Uint8Array(await file.arrayBuffer());
        ffmpeg.FS('writeFile', inputName, fileData);

        // Ultrafast H.264 compression with CRF 28 and AAC 128k audio
        await ffmpeg.run(
            '-i', inputName,
            '-vcodec', 'libx264',
            '-crf', '28',
            '-preset', 'ultrafast',
            '-acodec', 'aac',
            '-b:a', '128k',
            '-movflags', '+faststart',
            outputName
        );

        const compressedData = ffmpeg.FS('readFile', outputName);
        
        // Clean up virtual filesystem memory
        try {
            ffmpeg.FS('unlink', inputName);
            ffmpeg.FS('unlink', outputName);
        } catch (e) {}

        const compressedBlob = new Blob([compressedData.buffer], { type: 'video/mp4' });
        const cleanOriginalName = file.name.replace(/\.[^/.]+$/, "");
        const compressedFile = new File([compressedBlob], `${cleanOriginalName}_compressed.mp4`, { type: 'video/mp4' });

        return {
            file: compressedFile,
            originalSize: file.size,
            compressedSize: compressedFile.size,
            ratioPercent: Math.round((1 - (compressedFile.size / file.size)) * 100)
        };
    }

    function toggleLessonType(sectionId) {
        const typeSelect = document.getElementById(`type-${sectionId}`);
        const videoWrapper = document.getElementById(`video-source-wrapper-${sectionId}`);
        if (videoWrapper) {
            videoWrapper.style.display = typeSelect.value === 'quiz' ? 'none' : 'block';
        }
    }

    function toggleVideoSource(sectionId, source) {
        const fileGroup = document.getElementById(`video-file-group-${sectionId}`);
        const ytGroup = document.getElementById(`youtube-url-group-${sectionId}`);
        if (source === 'youtube') {
            if (fileGroup) fileGroup.style.display = 'none';
            if (ytGroup) ytGroup.style.display = 'block';
        } else {
            if (fileGroup) fileGroup.style.display = 'block';
            if (ytGroup) ytGroup.style.display = 'none';
        }
    }

    async function uploadChunkedLesson(event, sectionId, endpointUrl) {
        event.preventDefault();

        const titleInput = document.getElementById(`title-${sectionId}`);
        const typeSelect = document.getElementById(`type-${sectionId}`);
        const fileInput = document.getElementById(`video-file-${sectionId}`);
        const youtubeInput = document.getElementById(`youtube-url-${sectionId}`);
        const isPreviewInput = document.getElementById(`is-preview-${sectionId}`);

        const progressContainer = document.getElementById(`progress-container-${sectionId}`);
        const progressBar = document.getElementById(`progress-bar-${sectionId}`);
        const progressPercent = document.getElementById(`progress-percent-${sectionId}`);
        const progressStatus = document.getElementById(`progress-status-${sectionId}`);
        const submitBtn = document.getElementById(`submit-btn-${sectionId}`);

        const title = titleInput.value.trim();
        const type = typeSelect.value;
        const isPreview = isPreviewInput.checked ? 1 : 0;
        const csrfToken = document.querySelector('input[name="_token"]').value;

        const videoSourceRadio = document.querySelector(`input[name="video_source_${sectionId}"]:checked`);
        const videoSource = videoSourceRadio ? videoSourceRadio.value : 'bunny';

        if (!title) {
            alert('يرجى كتابة عنوان الدرس.');
            return;
        }

        // Handle YouTube video submission
        if (type === 'video' && videoSource === 'youtube') {
            const ytUrl = youtubeInput ? youtubeInput.value.trim() : '';
            if (!ytUrl) {
                alert('يرجى إدخال رابط فيديو يوتيوب أو معرّف الفيديو.');
                return;
            }

            submitBtn.disabled = true;
            try {
                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('title', title);
                formData.append('type', 'video');
                formData.append('video_source', 'youtube');
                formData.append('youtube_url', ytUrl);
                formData.append('is_preview', isPreview);

                const res = await fetch(endpointUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                const resType = res.headers.get("content-type");
                if (!res.ok || !resType || !resType.includes("application/json")) {
                    const txt = await res.text();
                    throw new Error(`استجابة غير صالحة من السيرفر (${res.status}): ${txt.substring(0, 100)}`);
                }
                const data = await res.json();

                if (data.success) {
                    alert('تم حفظ الدرس وإضافة رابط اليوتيوب بنجاح!');
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'حدث خطأ أثناء الحفظ');
                }
            } catch (err) {
                alert('خطأ أثناء الحفظ: ' + err.message);
                submitBtn.disabled = false;
            }
            return;
        }

        // Handle Bunny video upload
        if (type === 'video') {
            let file = fileInput ? fileInput.files[0] : null;
            if (!file) {
                alert('يرجى اختيار ملف الفيديو للرفع.');
                return;
            }

            submitBtn.disabled = true;
            progressContainer.classList.remove('hidden');

            const compressCheckbox = document.getElementById(`compress-video-${sectionId}`);
            const shouldCompress = compressCheckbox && compressCheckbox.checked;

            if (shouldCompress) {
                try {
                    progressStatus.innerText = `جاري ضغط الفيديو محلياً على جهازك لتسريع الرفع (0%)... [حجم الملف: ${formatBytes(file.size)}]`;
                    progressBar.style.width = '0%';
                    progressPercent.innerText = '0%';

                    const compressResult = await compressVideoClientSide(file, (pct) => {
                        progressBar.style.width = pct + '%';
                        progressPercent.innerText = pct + '%';
                        progressStatus.innerText = `جاري ضغط الفيديو محلياً: ${pct}%... (الحجم الأصلي: ${formatBytes(file.size)})`;
                    });

                    file = compressResult.file;
                    const savedPct = compressResult.ratioPercent > 0 ? compressResult.ratioPercent : 0;
                    progressStatus.innerText = `اكتمل الضغط بنجاح! تم تقليص الحجم من (${formatBytes(compressResult.originalSize)}) إلى (${formatBytes(compressResult.compressedSize)}) - توفير ${savedPct}%! جاري الرفع السحابي...`;
                    await new Promise(r => setTimeout(r, 1000));
                } catch (compressErr) {
                    console.warn('Compression skipped or failed on current browser, uploading original:', compressErr);
                    progressStatus.innerText = 'تنبيه: تم تخطي الضغط المحلي، جاري رفع الملف الأصلي مباشرة عبر Bunny TUS...';
                    await new Promise(r => setTimeout(r, 1000));
                }
            }

            let heartbeatInterval = null;
            let currentCsrfToken = csrfToken;
            let videoGuid = null;

            try {
                progressStatus.innerText = 'جاري إنشاء إدخال الفيديو وطلب صلاحيات الرفع...';

                const initFormData = new FormData();
                initFormData.append('_token', currentCsrfToken);
                initFormData.append('init', '1');
                initFormData.append('title', title);
                initFormData.append('type', type);
                initFormData.append('video_source', 'bunny');

                const initRes = await fetch(endpointUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: initFormData
                });

                const contentType = initRes.headers.get("content-type");
                if (!initRes.ok || !contentType || !contentType.includes("application/json")) {
                    throw new Error('فشل الحصول على تصريح الرفع من الخادم (رمز الخطأ: ' + initRes.status + ')');
                }
                const initData = await initRes.json();

                if (!initData.success || !initData.video_guid) {
                    throw new Error(initData.message || 'فشل إنشاء إدخال الفيديو على Bunny Stream.');
                }

                videoGuid = initData.video_guid;

                if (typeof tus === 'undefined' || !tus.Upload) {
                    throw new Error('مكتبة الرفع (tus) لم يتم تحميلها بشكل صحيح. يرجى تحديث الصفحة.');
                }

                // Heartbeat to keep the Laravel/Hostinger session active during long file uploads (e.g. 700MB+)
                const keepAliveUrl = "{{ route('instructor.keep-alive') }}";
                heartbeatInterval = setInterval(async () => {
                    try {
                        const pingRes = await fetch(keepAliveUrl, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            cache: 'no-store'
                        });
                        if (pingRes.ok) {
                            const pingData = await pingRes.json();
                            if (pingData.csrf_token) {
                                currentCsrfToken = pingData.csrf_token;
                            }
                        }
                    } catch (pingErr) {
                        console.warn('Keep-alive ping failed (connection busy uploading):', pingErr);
                    }
                }, 30000); // Ping every 30 seconds

                await new Promise((resolve, reject) => {
                    const upload = new tus.Upload(file, {
                        endpoint: "https://video.bunnycdn.com/tusupload",
                        retryDelays: [0, 3000, 5000, 10000],
                        headers: {
                            AuthorizationSignature: initData.signature,
                            AuthorizationExpire: initData.expiration_time.toString(),
                            LibraryId: initData.library_id.toString(),
                            VideoId: videoGuid,
                        },
                        metadata: { filename: file.name, filetype: file.type },
                        onError: (error) => reject(new Error('فشل الرفع: ' + error.message)),
                        onProgress: (bytesUploaded, bytesTotal) => {
                            const pct = Math.round((bytesUploaded / bytesTotal) * 95);
                            progressBar.style.width = pct + '%';
                            progressPercent.innerText = pct + '%';
                            progressStatus.innerText = `جاري الرفع إلى Bunny Stream: ${pct}% (${(bytesUploaded / 1024 / 1024).toFixed(1)}MB / ${(bytesTotal / 1024 / 1024).toFixed(1)}MB)`;
                        },
                        onSuccess: () => resolve()
                    });
                    upload.start();
                });

                if (heartbeatInterval) {
                    clearInterval(heartbeatInterval);
                    heartbeatInterval = null;
                }

                progressStatus.innerText = 'جاري حفظ بيانات المحاضرة في النظام...';
                progressBar.style.width = '100%';
                progressPercent.innerText = '100%';

                const finalFormData = new FormData();
                finalFormData.append('_token', currentCsrfToken);
                finalFormData.append('title', title);
                finalFormData.append('type', type);
                finalFormData.append('video_source', 'bunny');
                finalFormData.append('video_guid', videoGuid);
                finalFormData.append('is_preview', isPreview);

                const finalRes = await fetch(endpointUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': currentCsrfToken
                    },
                    body: finalFormData
                });

                const finalContentType = finalRes.headers.get("content-type");
                if (!finalRes.ok || !finalContentType || !finalContentType.includes("application/json")) {
                    if (finalRes.status === 419) {
                        throw new Error(`انتهت جلسة العمل بسبب طول مدة الرفع (CSRF 419). ملاحظة هامة: تم رفع الفيديو بنجاح على Bunny Stream بمعرّف [${videoGuid}].`);
                    }
                    const textErr = await finalRes.text();
                    throw new Error(`استجابة غير صالحة من السيرفر (${finalRes.status}): ${textErr.substring(0, 100)}`);
                }

                const finalData = await finalRes.json();

                if (finalData.success) {
                    alert('تم حفظ ورفع المحاضرة بنجاح!');
                    window.location.reload();
                } else {
                    throw new Error(finalData.message || 'حدث خطأ أثناء التخزين');
                }
            } catch (err) {
                if (heartbeatInterval) {
                    clearInterval(heartbeatInterval);
                    heartbeatInterval = null;
                }
                alert('خطأ أثناء الرفع أو الحفظ: ' + err.message);
                submitBtn.disabled = false;
                progressContainer.classList.add('hidden');
            }
            return;
        }

        // Quiz type
        submitBtn.disabled = true;
        try {
            const finalFormData = new FormData();
            finalFormData.append('_token', csrfToken);
            finalFormData.append('title', title);
            finalFormData.append('type', type);
            finalFormData.append('is_preview', isPreview);

            const finalRes = await fetch(endpointUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: finalFormData
            });
            const qType = finalRes.headers.get("content-type");
            if (!finalRes.ok || !qType || !qType.includes("application/json")) {
                const txt = await finalRes.text();
                throw new Error(`استجابة غير صالحة من السيرفر (${finalRes.status}): ${txt.substring(0, 100)}`);
            }
            const finalData = await finalRes.json();

            if (finalData.success) {
                alert('تم حفظ الدرس بنجاح!');
                window.location.reload();
            } else {
                throw new Error(finalData.message || 'حدث خطأ أثناء الحفظ');
            }
        } catch (err) {
            alert('خطأ: ' + err.message);
            submitBtn.disabled = false;
        }
    }

    function extractYouTubeId(url) {
        if (!url) return null;
        const regExp = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts|live)\/|.*[?&]v=)|youtu\.be\/|youtube-nocookie\.com\/embed\/)([a-zA-Z0-9_-]{11})/i;
        const match = url.match(regExp);
        if (match && match[1]) return match[1];
        if (/^[a-zA-Z0-9_-]{11}$/.test(url)) return url;
        return null;
    }

    function previewLessonVideo(videoUrl, libraryId) {
        const modal = document.getElementById('bunny-video-modal');
        const container = document.getElementById('bunny-video-container');

        const ytId = extractYouTubeId(videoUrl);
        if (ytId) {
            const embedUrl = `https://www.youtube.com/embed/${ytId}?autoplay=1&rel=0`;
            container.innerHTML = `
                <div style="position: relative; padding-top: 56.25%; border-radius: 12px; overflow: hidden; background: #000;">
                    <iframe src="${embedUrl}" loading="lazy" style="border: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                </div>`;
            modal.style.display = 'flex';
            return;
        }

        // Check if Bunny Stream GUID (UUID)
        const isBunnyGuid = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(videoUrl);
        if (isBunnyGuid) {
            const iframeUrl = `https://iframe.mediadelivery.net/embed/${libraryId}/${videoUrl}?autoplay=true&loop=false&muted=false&preload=true&responsive=true`;
            container.innerHTML = `
                <div style="position: relative; padding-top: 56.25%; border-radius: 12px; overflow: hidden; background: #000;">
                    <iframe src="${iframeUrl}" loading="lazy" style="border: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%;" allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;" allowfullscreen="true"></iframe>
                </div>`;
            modal.style.display = 'flex';
            return;
        }

        // Direct URL or Signed Bunny CDN Path
        const videoSrc = videoUrl.startsWith('http') ? videoUrl : videoUrl;
        container.innerHTML = `
            <div style="position: relative; padding-top: 56.25%; border-radius: 12px; overflow: hidden; background: #000;">
                <video controls autoplay style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" src="${videoSrc}"></video>
            </div>`;
        modal.style.display = 'flex';
    }

    function previewBunnyVideo(libraryId, videoGuid) {
        previewLessonVideo(videoGuid, libraryId);
    }

    function closeVideoModal() {
        const modal = document.getElementById('bunny-video-modal');
        document.getElementById('bunny-video-container').innerHTML = '';
        modal.style.display = 'none';
    }

    // Section Edit Modal Functions
    function openEditSectionModal(id, title) {
        document.getElementById('edit-section-form').action = `/instructor/sections/${id}`;
        document.getElementById('edit-section-title').value = title;
        document.getElementById('edit-section-modal').style.display = 'flex';
    }
    function closeEditSectionModal() {
        document.getElementById('edit-section-modal').style.display = 'none';
    }

    // Course Edit & Delete Modal Functions
    function openEditCourseModal() {
        document.getElementById('edit-course-modal').style.display = 'flex';
    }
    function closeEditCourseModal() {
        document.getElementById('edit-course-modal').style.display = 'none';
    }
    function openDeleteCourseModal() {
        document.getElementById('delete-course-modal').style.display = 'flex';
    }
    function closeDeleteCourseModal() {
        document.getElementById('delete-course-modal').style.display = 'none';
    }

    // Lesson Edit Modal Function
    function openEditLessonModal(id, title, description, isPreview, duration, videoUrl = '') {
        document.getElementById('edit-lesson-form').action = `/instructor/lessons/${id}`;
        document.getElementById('edit-lesson-title').value = title;
        document.getElementById('edit-lesson-description').value = description;
        document.getElementById('edit-lesson-preview').checked = isPreview == 1;
        document.getElementById('edit-lesson-duration').value = duration;
        
        const videoUrlInput = document.getElementById('edit-lesson-video-url');
        if (videoUrlInput) {
            videoUrlInput.value = videoUrl;
        }
        document.getElementById('edit-lesson-modal').style.display = 'flex';
    }
    function closeEditLessonModal() {
        document.getElementById('edit-lesson-modal').style.display = 'none';
    }

    // Quiz & Question Edit Modal Functions
    function openEditQuizModal(id, title, passPercentage, timeLimit, attemptsAllowed) {
        document.getElementById('edit-quiz-form').action = `/instructor/quizzes/${id}`;
        document.getElementById('edit-quiz-title').value = title;
        document.getElementById('edit-quiz-pass-percentage').value = passPercentage;
        document.getElementById('edit-quiz-time-limit').value = (timeLimit !== null && timeLimit !== 'null') ? timeLimit : '';
        document.getElementById('edit-quiz-attempts-allowed').value = (attemptsAllowed !== null && attemptsAllowed !== 'null') ? attemptsAllowed : '';
        document.getElementById('edit-quiz-modal').style.display = 'flex';
    }
    function closeEditQuizModal() {
        document.getElementById('edit-quiz-modal').style.display = 'none';
    }

    function openEditQuestionModal(id, text, points, options, correctIndex) {
        document.getElementById('edit-question-form').action = `/instructor/questions/${id}`;
        document.getElementById('edit-question-text').value = text;
        document.getElementById('edit-question-points').value = points;

        for (let i = 0; i < 4; i++) {
            const textInput = document.getElementById(`edit-option-text-${i}`);
            const radioInput = document.getElementById(`edit-correct-option-${i}`);
            if (textInput) {
                textInput.value = (options && options[i]) ? options[i] : '';
            }
            if (radioInput) {
                radioInput.checked = (i === correctIndex);
            }
        }
        document.getElementById('edit-question-modal').style.display = 'flex';
    }
    function closeEditQuestionModal() {
        document.getElementById('edit-question-modal').style.display = 'none';
    }

    // Bulk Questions Modal Functions & State
    let parsedQuestionsList = [];

    function openBulkQuestionsModal(quizId, quizTitle) {
        document.getElementById('bulk-questions-form').action = `/instructor/quizzes/${quizId}/questions/bulk`;
        document.getElementById('bulk-quiz-title').innerText = quizTitle;
        document.getElementById('bulk-raw-text').value = '';
        parsedQuestionsList = [];
        renderBulkPreview();
        document.getElementById('bulk-questions-modal').style.display = 'flex';
    }

    function closeBulkQuestionsModal() {
        document.getElementById('bulk-questions-modal').style.display = 'none';
    }

    function fillArabicSampleBulkQuestions() {
        const sample = `1. ما هي الخلية المسؤولة عن نقل الأكسجين في الدم؟
أ) كرات الدم البيضاء
*ب) كرات الدم الحمراء
ج) الصفائح الدموية
د) بلازما الدم

2. ما هو العضو المسؤول عن ضخ الدم إلى جميع أنحاء الجسم؟
*أ) القلب
ب) الرئتين
ج) الكبد
د) الكلى

3. ما هي الوحدة الوظيفية الأساسية في الكلية؟
أ) الحويصلة الهوائية
ب) النيورون
*ج) النفرون
د) الخلية الكبدية`;
        document.getElementById('bulk-raw-text').value = sample;
        parseBulkQuestionsClientSide();
    }

    function fillEnglishSampleBulkQuestions() {
        const sample = `1. What is the normal resting heart rate for a healthy adult?
A) 40-50 bpm
*B) 60-100 bpm
C) 110-130 bpm
D) 140-160 bpm

2. Which organ is primarily responsible for detoxifying chemicals in the body?
*A) Liver
B) Kidneys
C) Pancreas
D) Spleen

3. Which vitamin is synthesized when the skin is exposed to sunlight?
A) Vitamin A
B) Vitamin C
*C) Vitamin D
D) Vitamin K`;
        document.getElementById('bulk-raw-text').value = sample;
        parseBulkQuestionsClientSide();
    }

    function clearBulkQuestionsText() {
        document.getElementById('bulk-raw-text').value = '';
        parsedQuestionsList = [];
        renderBulkPreview();
    }

    function parseBulkQuestionsClientSide() {
        const rawText = document.getElementById('bulk-raw-text').value.trim();
        const defaultPoints = parseInt(document.getElementById('bulk-default-points').value) || 1;

        if (!rawText) {
            parsedQuestionsList = [];
            renderBulkPreview();
            return;
        }

        const lines = rawText.split(/\r\n|\r|\n/);
        const questions = [];
        let currentQuestion = null;

        const arabicMap = { 'أ': 0, 'ا': 0, 'ب': 1, 'ج': 2, 'د': 3, 'ه': 4, 'و': 5 };
        const latinMap = { 'a': 0, 'b': 1, 'c': 2, 'd': 3, 'e': 4, 'f': 5 };

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i].trim();
            if (!line) continue;

            // 1. Check for Answer line: e.g. Answer: B, الجواب: ب, الإجابة: 2
            const ansMatch = line.match(/^(?:answer|ans|الجواب|الإجابة|الاجابة|الصحيح)\s*[:=\-]\s*([a-zA-Z0-9\u0621-\u064A]+)/i);
            if (ansMatch) {
                if (currentQuestion && currentQuestion.options.length > 0) {
                    const key = ansMatch[1].toLowerCase();
                    let ansIdx = null;
                    if (arabicMap[key] !== undefined) ansIdx = arabicMap[key];
                    else if (latinMap[key] !== undefined) ansIdx = latinMap[key];
                    else if (!isNaN(parseInt(key))) {
                        const num = parseInt(key);
                        if (num >= 1 && num <= currentQuestion.options.length) ansIdx = num - 1;
                    }
                    if (ansIdx === null) {
                        ansIdx = currentQuestion.options.findIndex(o => o.key === key);
                    }
                    if (ansIdx !== null && ansIdx >= 0 && ansIdx < currentQuestion.options.length) {
                        currentQuestion.correct_option_index = ansIdx;
                        currentQuestion.options.forEach((opt, idx) => opt.is_correct = (idx === ansIdx));
                    }
                }
                continue;
            }

            // 2. Check for Question start: e.g. "1. Question", "1) Question", "Q1: Question", "س1: ..."
            const qMatch = line.match(/^(?:Q\s*\d+|\d+|س\s*\d+|سؤال\s*\d+)[\.\)\-\:\s]\s*(.+)$/i);
            if (qMatch) {
                if (currentQuestion && currentQuestion.options.length > 0) {
                    questions.push(currentQuestion);
                }
                currentQuestion = {
                    question_text: qMatch[1].trim(),
                    points: defaultPoints,
                    options: [],
                    correct_option_index: null
                };
                continue;
            }

            // 3. Check for Option: e.g. *A) ..., [x] A) ..., A) ..., أ) ..., *ب) ...
            const optMatch = line.match(/^(\*|\[x\]|\(x\))?\s*([A-Za-z\u0621-\u064A])[\.\)\-\:\/\]]\s*(.+)$/);
            if (optMatch && currentQuestion) {
                const isCorrect = Boolean(optMatch[1]);
                const optText = optMatch[3].trim();
                const optionObj = {
                    key: optMatch[2].toLowerCase(),
                    text: optText,
                    is_correct: isCorrect
                };
                currentQuestion.options.push(optionObj);
                if (isCorrect) {
                    currentQuestion.correct_option_index = currentQuestion.options.length - 1;
                }
                continue;
            }

            // 4. Default Line Handler
            if (!currentQuestion) {
                currentQuestion = {
                    question_text: line,
                    points: defaultPoints,
                    options: [],
                    correct_option_index: null
                };
            } else {
                if (currentQuestion.options.length === 0) {
                    currentQuestion.question_text += ' ' + line;
                } else {
                    questions.push(currentQuestion);
                    currentQuestion = {
                        question_text: line,
                        points: defaultPoints,
                        options: [],
                        correct_option_index: null
                    };
                }
            }
        }

        if (currentQuestion && currentQuestion.options.length > 0) {
            questions.push(currentQuestion);
        }

        // Ensure each question has a correct option fallback if none was flagged
        questions.forEach(q => {
            if (q.options.length > 0 && !q.options.some(o => o.is_correct)) {
                q.options[0].is_correct = true;
                q.correct_option_index = 0;
            }
        });

        parsedQuestionsList = questions;
        renderBulkPreview();
    }

    function removeParsedQuestion(index) {
        if (index >= 0 && index < parsedQuestionsList.length) {
            parsedQuestionsList.splice(index, 1);
            renderBulkPreview();
        }
    }

    function setParsedCorrectOption(qIdx, optIdx) {
        if (parsedQuestionsList[qIdx] && parsedQuestionsList[qIdx].options[optIdx]) {
            parsedQuestionsList[qIdx].options.forEach((o, i) => o.is_correct = (i === optIdx));
            parsedQuestionsList[qIdx].correct_option_index = optIdx;
            renderBulkPreview();
        }
    }

    function renderBulkPreview() {
        const previewContainer = document.getElementById('bulk-parsed-preview');
        const jsonInput = document.getElementById('bulk-questions-json');
        const countBadge = document.getElementById('bulk-questions-count-badge');
        const footerCount = document.getElementById('bulk-footer-summary');
        const submitBtn = document.getElementById('bulk-submit-btn');

        if (!parsedQuestionsList || parsedQuestionsList.length === 0) {
            previewContainer.innerHTML = `
                <div class="flex flex-col items-center justify-center py-10 text-center px-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl mb-3 shadow-inner">
                        <i class="ph-bold ph-magic-wand"></i>
                    </div>
                    <p class="text-[#1A202C] text-sm font-bold">بانتظار لصق الأسئلة</p>
                    <p class="text-[#718096] text-xs mt-1 max-w-sm">الصق نص الأسئلة أو اضغط على أحد النماذج التجريبية بالأعلى لمعاينتها وتحليلها فورياً هنا.</p>
                </div>
            `;
            jsonInput.value = '';
            countBadge.innerText = '0 أسئلة مكتشفة';
            if (footerCount) footerCount.innerText = 'لم يتم اكتشاف أسئلة بعد';
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<i class="ph-bold ph-check-circle text-base"></i> حفظ وإضافة جميع الأسئلة`;
            return;
        }

        jsonInput.value = JSON.stringify(parsedQuestionsList);
        const totalPoints = parsedQuestionsList.reduce((acc, q) => acc + (parseInt(q.points) || 1), 0);
        countBadge.innerText = `${parsedQuestionsList.length} أسئلة مكتشفة (${totalPoints} درجة)`;
        if (footerCount) footerCount.innerText = `${parsedQuestionsList.length} سؤال جاهز للحفظ • إجمالي الدرجات: ${totalPoints}`;
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<i class="ph-bold ph-check-circle text-base"></i> حفظ وإضافة (${parsedQuestionsList.length}) سؤال بنجاح`;

        let html = `<div class="space-y-3.5 max-h-[360px] overflow-y-auto pr-1">`;
        parsedQuestionsList.forEach((q, qIdx) => {
            html += `
                <div class="bg-white border border-[#E2E8F0] hover:border-[#0047AB]/30 rounded-2xl p-4 shadow-sm transition-all relative group">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-start gap-2.5 flex-1">
                            <span class="w-6 h-6 rounded-full bg-[#0047AB] text-white flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5 shadow-sm">${qIdx + 1}</span>
                            <span class="font-bold text-sm text-[#1A202C] leading-snug" dir="auto">${escapeHtml(q.question_text)}</span>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <span class="text-xs bg-indigo-50 text-indigo-700 font-semibold px-2.5 py-0.5 rounded-lg border border-indigo-100">${q.points} درجة</span>
                            <button type="button" onclick="removeParsedQuestion(${qIdx})" title="حذف هذا السؤال" class="p-1 text-[#718096] hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <i class="ph-bold ph-trash text-sm"></i>
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mr-8">
            `;
            q.options.forEach((opt, oIdx) => {
                const isCorrect = opt.is_correct;
                html += `
                    <div onclick="setParsedCorrectOption(${qIdx}, ${oIdx})" class="flex items-center justify-between p-2.5 rounded-xl cursor-pointer transition-all border ${isCorrect ? 'bg-emerald-50/90 border-emerald-300 text-emerald-950 font-bold shadow-sm ring-1 ring-emerald-400/20' : 'bg-[#F8F9FA] border-[#E2E8F0] text-[#4A5568] hover:bg-gray-100'}">
                        <div class="flex items-center gap-2 flex-1" dir="auto">
                            <i class="ph-bold ph-${isCorrect ? 'check-circle text-emerald-600' : 'circle text-gray-400'} text-base"></i>
                            <span class="text-xs">${escapeHtml(opt.text)}</span>
                        </div>
                        ${isCorrect ? '<span class="text-[10px] bg-emerald-600 text-white font-semibold px-2 py-0.5 rounded-md flex-shrink-0 mr-1 shadow-sm">صحيح</span>' : ''}
                    </div>
                `;
            });
            html += `</div></div>`;
        });
        html += `</div>`;
        previewContainer.innerHTML = html;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    </script>
    @endpush

    {{-- Video Preview Modal --}}
    <div id="bunny-video-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.85); z-index: 1000; align-items: center; justify-content: center;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-4xl p-6 relative shadow-2xl">
            <button onclick="closeVideoModal()" class="absolute top-4 left-4 inline-flex items-center gap-1.5 bg-red-500 text-white font-semibold px-4 py-2 rounded-xl hover:bg-red-600 transition-colors text-sm">
                <i class="ph-bold ph-x"></i>
                إغلاق
            </button>
            <h3 class="text-lg font-bold mb-4 text-right text-[#1A202C] flex items-center gap-2">
                <i class="ph-bold ph-play-circle text-[#0047AB]"></i>
                معاينة الفيديو
            </h3>
            <div id="bunny-video-container"></div>
        </div>
    </div>

    {{-- Edit Course Modal --}}
    <div id="edit-course-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-lg p-6 relative shadow-2xl my-8">
            <h3 class="text-lg font-bold mb-4 text-right text-[#1A202C] flex items-center gap-2 pb-3 border-b border-[#E2E8F0]">
                <i class="ph-bold ph-pencil-simple text-[#0047AB]"></i>
                تعديل بيانات الكورس
            </h3>
            <form action="{{ route('instructor.courses.update', $course->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <x-form-input label="عنوان الكورس" name="title" :required="true" value="{{ $course->title }}" />
                <x-form-select label="نوع الكورس" name="type" :required="true">
                    <option value="recorded" {{ $course->type === 'recorded' ? 'selected' : '' }}>محاضرات مسجلة فقط</option>
                    <option value="live" {{ $course->type === 'live' ? 'selected' : '' }}>جلسات بث مباشر فقط</option>
                    <option value="mixed" {{ $course->type === 'mixed' ? 'selected' : '' }}>هجين (مسجل + بث مباشر)</option>
                </x-form-select>
                <x-form-input label="سعر الكورس" name="price" type="number" step="0.01" min="0" value="{{ $course->price }}" :required="true" />
                <x-form-textarea label="وصف الكورس" name="description" :required="true" rows="4">{{ $course->description }}</x-form-textarea>
                
                <div>
                    <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">صورة الغلاف (Thumbnail)</label>
                    <input type="file" name="thumbnail_file" accept="image/*" class="w-full text-sm text-[#718096] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#0047AB]/10 file:text-[#0047AB] hover:file:bg-[#0047AB]/20 transition-all">
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeEditCourseModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <x-btn-primary icon="floppy-disk" type="submit">حفظ التغييرات</x-btn-primary>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Course Modal --}}
    <div id="delete-course-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-md p-6 relative shadow-2xl">
            <h3 class="text-lg font-bold mb-2 text-right text-red-600 flex items-center gap-2">
                <i class="ph-bold ph-warning"></i>
                حذف الكورس نهائياً
            </h3>
            <p class="text-sm text-[#718096] mb-4">إن حذف الكورس سيؤدي إلى مسح جميع الأقسام، الدروس، المرفقات، والامتحانات المرتبطة به. لا يمكن استعادة هذه البيانات بعد الحذف.</p>
            <form action="{{ route('instructor.courses.destroy', $course->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')
                <x-form-input label="يرجى إدخال كلمة المرور الخاصة بك لتأكيد الحذف" name="password" type="password" :required="true" placeholder="كلمة المرور الحالية" />

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeDeleteCourseModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <button type="submit" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">
                        <i class="ph-bold ph-trash"></i>
                        حذف الكورس نهائياً
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Lesson Modal --}}
    <div id="edit-lesson-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-md p-6 relative shadow-2xl">
            <h3 class="text-lg font-bold mb-4 text-right text-[#1A202C] flex items-center gap-2 pb-3 border-b border-[#E2E8F0]">
                <i class="ph-bold ph-pencil-simple text-[#0088CC]"></i>
                تعديل بيانات الدرس
            </h3>
            <form id="edit-lesson-form" action="" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <x-form-input label="عنوان الدرس" name="title" id="edit-lesson-title" :required="true" />
                <x-form-textarea label="الوصف" name="description" id="edit-lesson-description" rows="3"></x-form-textarea>
                
                <div>
                    <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">رابط الفيديو أو معرّف Bunny (YouTube Link / Video GUID)</label>
                    <input type="text" name="video_url" id="edit-lesson-video-url" placeholder="رابط YouTube أو معرّف Bunny Stream" class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-4 py-2.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB] transition-all text-left dir-ltr">
                </div>

                <x-form-input label="مدة الفيديو (بالثواني)" name="video_duration_seconds" id="edit-lesson-duration" type="number" min="0" />
                
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_preview" id="edit-lesson-preview" value="1" class="rounded border-[#E2E8F0] text-[#0047AB] focus:ring-[#0047AB]">
                    <label for="edit-lesson-preview" class="text-sm font-medium text-[#4A5568]">السماح بالمعاينة المجانية للدرس قبل الاشتراك</label>
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeEditLessonModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <x-btn-primary icon="floppy-disk" type="submit">حفظ التغييرات</x-btn-primary>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Quiz Modal --}}
    <div id="edit-quiz-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-md p-6 relative shadow-2xl">
            <h3 class="text-lg font-bold mb-4 text-right text-[#1A202C] flex items-center gap-2 pb-3 border-b border-[#E2E8F0]">
                <i class="ph-bold ph-pencil-simple text-[#0047AB]"></i>
                تعديل إعدادات الامتحان
            </h3>
            <form id="edit-quiz-form" action="" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <x-form-input label="عنوان الامتحان" name="title" id="edit-quiz-title" :required="true" />
                <div class="grid grid-cols-2 gap-4">
                    <x-form-input label="نسبة النجاح (%)" name="pass_percentage" id="edit-quiz-pass-percentage" type="number" step="0.01" :required="true" />
                    <x-form-input label="الحد الأقصى للمحاولات" name="attempts_allowed" id="edit-quiz-attempts-allowed" type="number" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <x-form-input label="مدة الامتحان (بالدقائق - اختياري)" name="time_limit_minutes" id="edit-quiz-time-limit" type="number" />
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeEditQuizModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <x-btn-primary icon="floppy-disk" type="submit">حفظ التغييرات</x-btn-primary>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Question Modal --}}
    <div id="edit-question-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-lg p-6 relative shadow-2xl my-8">
            <h3 class="text-lg font-bold mb-4 text-right text-[#1A202C] flex items-center gap-2 pb-3 border-b border-[#E2E8F0]">
                <i class="ph-bold ph-pencil-simple text-[#0047AB]"></i>
                تعديل السؤال والخيارات
            </h3>
            <form id="edit-question-form" action="" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <x-form-input label="نص السؤال" name="question_text" id="edit-question-text" :required="true" />
                <x-form-input label="درجة السؤال" name="points" id="edit-question-points" type="number" :required="true" min="1" />
                
                <div>
                    <label class="block text-sm font-semibold text-[#4A5568] mb-2">الخيارات المتاحة (حدد الإجابة الصحيحة)</label>
                    <div class="space-y-2">
                        @for($i = 0; $i < 4; $i++)
                        <div class="flex items-center gap-2">
                            <input type="radio" name="correct_option_index" id="edit-correct-option-{{ $i }}" value="{{ $i }}" class="text-[#0047AB] focus:ring-[#0047AB]">
                            <input type="text" name="options[{{ $i }}][text]" id="edit-option-text-{{ $i }}" {{ $i < 2 ? 'required' : '' }} class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-4 py-1.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB]" placeholder="الخيار {{ $i === 0 ? 'الأول' : ($i === 1 ? 'الثاني' : ($i === 2 ? 'الثالث (اختياري)' : 'الرابع (اختياري)')) }}">
                        </div>
                        @endfor
                    </div>
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeEditQuestionModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <x-btn-primary icon="floppy-disk" type="submit">حفظ التعديلات</x-btn-primary>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Section Modal --}}
    <div id="edit-section-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-md p-6 relative shadow-2xl">
            <h3 class="text-lg font-bold mb-4 text-right text-[#1A202C] flex items-center gap-2 pb-3 border-b border-[#E2E8F0]">
                <i class="ph-bold ph-pencil-simple text-[#0047AB]"></i>
                تعديل عنوان القسم
            </h3>
            <form id="edit-section-form" action="" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <x-form-input label="عنوان القسم (Section)" name="title" id="edit-section-title" :required="true" placeholder="مثال: الفصل الأول - مقدمة تشريح الجهاز الدوري" />
                
                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeEditSectionModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <x-btn-primary icon="floppy-disk" type="submit">حفظ التغييرات</x-btn-primary>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk Add Questions Modal --}}
    <div id="bulk-questions-modal" style="display: none; position: fixed; inset: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px); z-index: 1000; align-items: center; justify-content: center; padding: 1.25rem;">
        <div class="bg-white border border-[#E2E8F0] rounded-3xl w-full max-w-4xl max-h-[92vh] flex flex-col shadow-2xl relative overflow-hidden">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-[#E2E8F0] bg-gradient-to-r from-slate-50 to-white flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#0047AB] to-[#00A896] text-white flex items-center justify-center text-xl shadow-md">
                        <i class="ph-bold ph-lightning"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-[#1A202C]">استيراد وإضافة أسئلة دفعة واحدة (Bulk AI Import)</h3>
                            <span class="text-[11px] font-semibold bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full border border-emerald-200">تحليل فوري ومعاينة مباشرة</span>
                        </div>
                        <p class="text-xs text-[#718096] mt-0.5">امتحان: <span id="bulk-quiz-title" class="font-bold text-[#0047AB]"></span></p>
                    </div>
                </div>
                <button type="button" onclick="closeBulkQuestionsModal()" class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-600 flex items-center justify-center transition-colors">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            {{-- Modal Scrollable Body --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                
                {{-- Quick Sample Fillers & Toolbar --}}
                <div class="flex items-center justify-between gap-2 flex-wrap bg-gradient-to-r from-indigo-50/70 via-blue-50/50 to-emerald-50/50 border border-indigo-100/80 rounded-2xl p-3.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold text-[#1A202C] flex items-center gap-1">
                            <i class="ph-bold ph-sparkle text-indigo-600"></i>
                            نماذج سريعة للتجربة:
                        </span>
                        <button type="button" onclick="fillArabicSampleBulkQuestions()" class="text-xs font-semibold bg-white hover:bg-indigo-600 hover:text-white text-indigo-700 px-3 py-1.5 rounded-xl border border-indigo-200 shadow-sm transition-all flex items-center gap-1">
                            <span>🇸🇦 نموذج عربي</span>
                        </button>
                        <button type="button" onclick="fillEnglishSampleBulkQuestions()" class="text-xs font-semibold bg-white hover:bg-indigo-600 hover:text-white text-indigo-700 px-3 py-1.5 rounded-xl border border-indigo-200 shadow-sm transition-all flex items-center gap-1">
                            <span>🇬🇧 English Sample</span>
                        </button>
                    </div>
                    <button type="button" onclick="clearBulkQuestionsText()" class="text-xs text-red-600 hover:text-red-700 font-semibold px-2.5 py-1 rounded-lg hover:bg-red-50 transition-colors flex items-center gap-1 mr-auto">
                        <i class="ph-bold ph-trash text-sm"></i>
                        مسح النص
                    </button>
                </div>

                {{-- Guide / Format hint --}}
                <details class="bg-[#F8F9FA] border border-[#E2E8F0] rounded-2xl p-3.5 text-xs">
                    <summary class="cursor-pointer font-bold text-[#0047AB] flex items-center justify-between select-none">
                        <div class="flex items-center gap-1.5">
                            <i class="ph-bold ph-info text-sm text-[#0088CC]"></i>
                            <span>دليل وطريقة كتابة الخيارات لتحديد الإجابة الصحيحة تلقائياً</span>
                        </div>
                        <span class="text-[11px] text-[#718096] underline">عرض الأمثلة والصيغ</span>
                    </summary>
                    <div class="mt-3 pt-3 border-t border-[#E2E8F0] grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="bg-white p-3 rounded-xl border border-[#E2E8F0] text-[11px] leading-relaxed" dir="rtl">
                            <span class="text-emerald-700 font-bold block mb-1">✅ الطريقة الأولى (نجمة * قبل الخيار الصحيح):</span>
                            1. ما هو العضو المسؤول عن ضخ الدم؟<br>
                            أ) الرئتين<br>
                            *ب) القلب<br>
                            ج) الكبد<br>
                            د) الكلى
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-[#E2E8F0] text-[11px] leading-relaxed" dir="ltr">
                            <span class="text-emerald-700 font-bold block mb-1">✅ Option 2 (Answer line at bottom):</span>
                            1. What is the powerhouse of the cell?<br>
                            A) Mitochondria<br>
                            B) Ribosome<br>
                            C) Nucleus<br>
                            Answer: A
                        </div>
                    </div>
                </details>

                <form id="bulk-questions-form" action="" method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="questions_json" id="bulk-questions-json">

                    {{-- Textarea Area --}}
                    <div>
                        <div class="flex items-center justify-between gap-4 mb-2">
                            <label class="text-xs font-bold text-[#1A202C] flex items-center gap-1.5">
                                <i class="ph-bold ph-pencil-simple text-[#0047AB]"></i>
                                الصق نص الأسئلة والخيارات هنا:
                            </label>
                            <div class="flex items-center gap-2 bg-[#F8F9FA] px-2.5 py-1 rounded-xl border border-[#E2E8F0]">
                                <label for="bulk-default-points" class="text-[11px] font-semibold text-[#718096]">الدرجة الافتراضية لكل سؤال:</label>
                                <input type="number" id="bulk-default-points" value="1" min="1" onchange="parseBulkQuestionsClientSide()" class="w-12 bg-white border border-[#E2E8F0] rounded-lg px-1.5 py-0.5 text-center text-xs font-bold text-[#0047AB] focus:ring-1 focus:ring-[#0047AB] outline-none">
                            </div>
                        </div>

                        <textarea name="raw_text" id="bulk-raw-text" rows="7" dir="auto" oninput="parseBulkQuestionsClientSide()" placeholder="1. ما هي الخلية المسؤولة عن نقل الأكسجين في الدم؟
أ) كرات الدم البيضاء
*ب) كرات الدم الحمراء
ج) الصفائح الدموية
د) بلازما الدم

2. Which organ is responsible for pumping blood?
*A) Heart
B) Lungs
C) Liver" class="w-full bg-[#F8F9FA] hover:bg-white focus:bg-white border border-[#E2E8F0] focus:border-[#0047AB] rounded-2xl p-4 text-[#1A202C] text-sm focus:outline-none focus:ring-4 focus:ring-[#0047AB]/10 leading-relaxed font-sans transition-all resize-y"></textarea>
                    </div>

                    {{-- Actions & Counter Badge --}}
                    <div class="flex items-center justify-between gap-3 flex-wrap pt-1">
                        <button type="button" onclick="parseBulkQuestionsClientSide()" class="inline-flex items-center gap-2 bg-gradient-to-r from-[#0047AB] to-[#0088CC] hover:from-[#0047AB]/90 hover:to-[#0088CC]/90 text-white font-bold px-5 py-2.5 rounded-xl transition-all text-xs shadow-md shadow-blue-500/10">
                            <i class="ph-bold ph-magic-wand text-sm"></i>
                            إعادة تحليل وتحديث المعاينة
                        </button>
                        <span id="bulk-questions-count-badge" class="text-xs font-bold px-3.5 py-1.5 bg-indigo-50 text-indigo-800 rounded-xl border border-indigo-200 shadow-sm">0 أسئلة مكتشفة</span>
                    </div>

                    {{-- Live Preview Box --}}
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-[#718096] flex items-center gap-1.5">
                            <i class="ph-bold ph-eye text-emerald-600"></i>
                            المعاينة المباشرة للأسئلة المكتشفة (يمكنك الضغط على أي خيار لتغيير الإجابة الصحيحة):
                        </label>
                        <div id="bulk-parsed-preview" class="p-4 bg-[#F8F9FA] border border-[#E2E8F0] rounded-2xl min-h-[120px]">
                            {{-- Content loaded by JS --}}
                        </div>
                    </div>
                </form>
            </div>

            {{-- Sticky Modal Footer --}}
            <div class="flex items-center justify-between px-6 py-4 border-t border-[#E2E8F0] bg-gradient-to-r from-slate-50 to-white flex-shrink-0">
                <div class="text-xs text-[#718096] font-medium hidden sm:block" id="bulk-footer-summary">
                    لم يتم اكتشاف أسئلة بعد
                </div>
                <div class="flex gap-2.5 items-center mr-auto sm:mr-0">
                    <button type="button" onclick="closeBulkQuestionsModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-bold px-5 py-2.5 rounded-xl transition-colors text-xs">إلغاء</button>
                    <button type="button" onclick="document.getElementById('bulk-questions-form').submit()" id="bulk-submit-btn" disabled class="inline-flex items-center gap-2 bg-gradient-to-r from-[#00A896] to-[#028090] hover:from-[#00A896]/90 hover:to-[#028090]/90 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold px-6 py-2.5 rounded-xl transition-all text-xs shadow-lg shadow-teal-500/20">
                        <i class="ph-bold ph-check-circle text-base"></i>
                        حفظ وإضافة جميع الأسئلة
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
