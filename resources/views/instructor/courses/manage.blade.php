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
                <h3 class="text-base font-bold text-[#0047AB] mb-4 flex items-center gap-2">
                    <i class="ph-bold ph-folder-open text-lg"></i>
                    {{ $section->title }}
                </h3>

                {{-- Lessons --}}
                <ul class="space-y-3 mb-4">
                    @forelse($section->lessons as $lesson)
                        <li class="p-4 bg-white border border-[#E2E8F0] rounded-xl flex justify-between items-center shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg {{ $lesson->type === 'video' ? 'bg-[#0088CC]/10 text-[#0088CC]' : 'bg-[#00A896]/10 text-[#00A896]' }}">
                                    <i class="ph-bold ph-{{ $lesson->type === 'video' ? 'play-circle' : 'clipboard-text' }} text-lg"></i>
                                </div>
                                <div>
                                    <strong class="text-[#1A202C] text-sm">{{ $lesson->title }}</strong>
                                    <span class="text-xs text-[#718096] mr-2">({{ $lesson->type === 'video' ? 'فيديو مسجل' : 'امتحان MCQ' }})</span>
                                    @if($lesson->is_preview)
                                        <x-badge variant="success">معاينة مجانية</x-badge>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-2 items-center flex-wrap justify-end">
                                @if($lesson->type === 'video' && $lesson->video_url)
                                    <button onclick="previewBunnyVideo('{{ config('services.bunny.stream_library_id') }}', '{{ $lesson->video_url }}')" class="inline-flex items-center gap-1.5 bg-[#2EC4B6]/10 hover:bg-[#2EC4B6] text-[#00A896] hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
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
                                <button type="button" onclick="openEditLessonModal({{ $lesson->id }}, '{{ addslashes($lesson->title) }}', '{{ addslashes($lesson->description ?? '') }}', {{ $lesson->is_preview ? 1 : 0 }}, {{ $lesson->video_duration_seconds ?? 0 }})" class="inline-flex items-center gap-1.5 bg-[#0088CC]/10 hover:bg-[#0088CC] text-[#0088CC] hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
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
                        <span>إضافة درس أو فيديو لهذا القسم (يدعم أحجام ملفات كبيرة)</span>
                    </summary>
                    <form id="chunk-upload-form-{{ $section->id }}" onsubmit="uploadChunkedLesson(event, {{ $section->id }}, '{{ route('instructor.lessons.chunked', $section->id) }}')" class="mt-4 space-y-4">
                        @csrf
                        <x-form-input label="عنوان الدرس" :name="'title-' . $section->id" :required="true" placeholder="مثال: شرح رسم القلب الدورة الأذينية" />
                        <div>
                            <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">نوع المحتوى</label>
                            <select id="type-{{ $section->id }}" name="type" required class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-4 py-2.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB] transition-all">
                                <option value="video">فيديو مسجل (رفع بالأجزاء Chunked Upload - حجم كبير)</option>
                                <option value="quiz">امتحان MCQ واختبار تقييمي</option>
                            </select>
                        </div>
                        <div id="video-file-group-{{ $section->id }}">
                            <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">اختر ملف الفيديو (مهما كان الحجم)</label>
                            <input type="file" id="video-file-{{ $section->id }}" accept="video/mp4,video/quicktime,video/mkv" class="w-full text-sm text-[#718096] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#0047AB]/10 file:text-[#0047AB] hover:file:bg-[#0047AB]/20 transition-all">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="is-preview-{{ $section->id }}" value="1" class="rounded border-[#E2E8F0] text-[#0047AB] focus:ring-[#0047AB]">
                            <label for="is-preview-{{ $section->id }}" class="text-sm font-medium text-[#4A5568]">السماح بالمعاينة المجانية للدرس قبل الاشتراك</label>
                        </div>

                        <script>
                            document.getElementById('type-{{ $section->id }}').addEventListener('change', function() {
                                document.getElementById('video-file-group-{{ $section->id }}').style.display = this.value === 'quiz' ? 'none' : 'block';
                            });
                        </script>

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

                        <x-btn-primary icon="upload-simple" type="submit" :id="'submit-btn-' . $section->id">رفع وحفظ الدرس</x-btn-primary>
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
                            <div class="bg-white border border-[#E2E8F0] p-4 rounded-xl mb-4 text-sm text-[#718096] flex items-center gap-4">
                                <x-badge variant="info">نسبة النجاح: {{ $lesson->quiz->pass_percentage }}%</x-badge>
                                <x-badge variant="neutral">عدد الأسئلة: {{ $lesson->quiz->questions->count() }}</x-badge>
                            </div>

                            {{-- Existing Questions --}}
                            <div class="space-y-3 mb-6">
                                @foreach($lesson->quiz->questions as $qIndex => $question)
                                    <div class="bg-white border border-[#E2E8F0] p-4 rounded-xl shadow-sm">
                                        <strong class="text-[#1A202C] text-sm flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full bg-[#0047AB]/10 text-[#0047AB] flex items-center justify-center text-xs font-bold">{{ $qIndex + 1 }}</span>
                                            {{ $question->question_text }}
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

    {{-- Chunked Upload JS --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tus-js-client@2.3.1/dist/tus.min.js"></script>
    <script>
    async function uploadChunkedLesson(event, sectionId, endpointUrl) {
        event.preventDefault();

        const titleInput = document.getElementById(`title-${sectionId}`);
        const typeSelect = document.getElementById(`type-${sectionId}`);
        const fileInput = document.getElementById(`video-file-${sectionId}`);
        const isPreviewInput = document.getElementById(`is-preview-${sectionId}`);

        const progressContainer = document.getElementById(`progress-container-${sectionId}`);
        const progressBar = document.getElementById(`progress-bar-${sectionId}`);
        const progressPercent = document.getElementById(`progress-percent-${sectionId}`);
        const progressStatus = document.getElementById(`progress-status-${sectionId}`);
        const submitBtn = document.getElementById(`submit-btn-${sectionId}`);

        const title = titleInput.value;
        const type = typeSelect.value;
        const file = fileInput.files[0];
        const isPreview = isPreviewInput.checked ? 1 : 0;
        const csrfToken = document.querySelector('input[name="_token"]').value;

        if (type === 'video' && !file) {
            alert('يرجى اختيار ملف الفيديو للرفع.');
            return;
        }

        submitBtn.disabled = true;
        progressContainer.classList.remove('hidden');

        try {
            let videoGuid = null;

            if (type === 'video') {
                progressStatus.innerText = 'جاري إنشاء إدخال الفيديو وطلب صلاحيات الرفع...';

                const initFormData = new FormData();
                initFormData.append('_token', csrfToken);
                initFormData.append('init', '1');
                initFormData.append('title', title);
                initFormData.append('type', type);

                const initRes = await fetch(endpointUrl, { method: 'POST', body: initFormData });
                const contentType = initRes.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error('فشل الحصول على تصريح الرفع من الخادم.');
                }
                const initData = await initRes.json();

                if (!initData.success || !initData.video_guid) {
                    throw new Error(initData.message || 'فشل إنشاء إدخال الفيديو على Bunny Stream.');
                }

                videoGuid = initData.video_guid;

                if (typeof tus === 'undefined' || !tus.Upload) {
                    throw new Error('مكتبة الرفع (tus) لم يتم تحميلها بشكل صحيح. يرجى تحديث الصفحة.');
                }

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
                            progressStatus.innerText = `جاري الرفع: ${pct}% (${(bytesUploaded / 1024 / 1024).toFixed(1)}MB / ${(bytesTotal / 1024 / 1024).toFixed(1)}MB)`;
                        },
                        onSuccess: () => resolve()
                    });
                    upload.start();
                });
            }

            progressStatus.innerText = 'جاري حفظ بيانات المحاضرة...';
            progressBar.style.width = '100%';
            progressPercent.innerText = '100%';

            const finalFormData = new FormData();
            finalFormData.append('_token', csrfToken);
            finalFormData.append('title', title);
            finalFormData.append('type', type);
            if (videoGuid) finalFormData.append('video_guid', videoGuid);
            finalFormData.append('is_preview', isPreview);

            const finalRes = await fetch(endpointUrl, { method: 'POST', body: finalFormData });
            const finalData = await finalRes.json();

            if (finalData.success) {
                alert('تم حفظ ورفع المحاضرة بنجاح!');
                window.location.reload();
            } else {
                throw new Error(finalData.message || 'حدث خطأ أثناء التخزين');
            }

        } catch (err) {
            alert('خطأ أثناء الرفع: ' + err.message);
            submitBtn.disabled = false;
            progressContainer.classList.add('hidden');
        }
    }

    function previewBunnyVideo(libraryId, videoGuid) {
        const modal = document.getElementById('bunny-video-modal');
        const container = document.getElementById('bunny-video-container');

        const iframeUrl = `https://iframe.mediadelivery.net/embed/${libraryId}/${videoGuid}?autoplay=true&loop=false&muted=false&preload=true&responsive=true`;
        container.innerHTML = `
            <div style="position: relative; padding-top: 56.25%;">
                <iframe src="${iframeUrl}" loading="lazy" style="border: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%;" allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;" allowfullscreen="true"></iframe>
            </div>`;
        modal.style.display = 'flex';
    }

    function closeVideoModal() {
        const modal = document.getElementById('bunny-video-modal');
        document.getElementById('bunny-video-container').innerHTML = '';
        modal.style.display = 'none';
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
    function openEditLessonModal(id, title, description, isPreview, duration) {
        document.getElementById('edit-lesson-form').action = `/instructor/lessons/${id}`;
        document.getElementById('edit-lesson-title').value = title;
        document.getElementById('edit-lesson-description').value = description;
        document.getElementById('edit-lesson-preview').checked = isPreview == 1;
        document.getElementById('edit-lesson-duration').value = duration;
        document.getElementById('edit-lesson-modal').style.display = 'flex';
    }
    function closeEditLessonModal() {
        document.getElementById('edit-lesson-modal').style.display = 'none';
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
                <x-form-input label="مدة الفيديو (بالثواني)" name="video_duration_seconds" id="edit-lesson-duration" type="number" min="0" />
                
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_preview" id="edit-lesson-preview" value="1" class="rounded border-[#E2E8F0] text-[#0047AB] focus:ring-[#0047AB]">
                    <label for="edit-lesson-preview" class="text-sm font-medium text-[#4A5568]">السماح بالمعاينة المجانية للدرس قبل الاشتراك</label>
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeEditLessonModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <x-btn-primary icon="floppy-disk" type="submit">حفظ</x-btn-primary>
                </div>
            </form>
        </div>
    </div>
@endsection
