@extends('layouts.panel')

@section('title', 'إدارة الكورس - ' . $course->title)
@section('role_title', 'لوحة المحاضر')


@section('page_title')
    <div class="flex items-center space-x-reverse space-x-4">
        <span>إدارة الكورس: {{ $course->title }}</span>
        <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $course->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
            {{ $course->status === 'published' ? 'منشور' : 'مسودة' }}
        </span>
    </div>
@endsection

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-on-surface">محتويات ومنهج الكورس</h1>
        <form action="{{ route('instructor.courses.publish', $course->id) }}" method="POST">
            @csrf
            <button type="submit" class="font-bold px-6 py-3 rounded-lg text-white transition-colors duration-150 {{ $course->status === 'published' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-primary hover:bg-primary-container' }}">
                {{ $course->status === 'published' ? 'تحويل إلى مسودة' : 'نشر الكورس للطلاب 🚀' }}
            </button>
        </form>
    </div>

    <!-- 1. الأقسام والمحاضرات -->
    <div class="bg-surface-container-lowest border border-surface-container-highest rounded-xl p-gutter shadow-sm mb-6" id="sections-lessons">
        <h2 class="text-xl font-bold text-primary mb-4">1. الأقسام والمحاضرات (Curriculum)</h2>

        <!-- إضافة قسم جديد -->
        <form action="{{ route('instructor.sections.store', $course->id) }}" method="POST" class="flex gap-4 items-end mb-6">
            @csrf
            <div class="flex-grow">
                <label class="block text-sm font-semibold text-on-surface-variant mb-1">إضافة قسم جديد (Section)</label>
                <input type="text" name="title" required class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary" placeholder="مثال: الفصل الأول - مقدمة تشريح الجهاز الدوري">
            </div>
            <button type="submit" class="bg-primary text-white font-bold px-6 py-2 rounded-lg hover:bg-primary-container transition-colors h-10">إضافة قسم</button>
        </form>

        <!-- قائمة الأقسام والدروس -->
        @forelse($course->sections as $section)
            <div class="bg-surface border border-surface-container-highest rounded-xl p-stack-lg mb-6">
                <h3 class="text-lg font-bold text-primary mb-4 flex items-center space-x-reverse space-x-2">
                    <span class="material-symbols-outlined text-primary">folder_open</span>
                    <span>{{ $section->title }}</span>
                </h3>

                <!-- قائمة الدروس الحالية داخل القسم -->
                <ul class="space-y-3 mb-4">
                    @forelse($section->lessons as $lesson)
                        <li class="p-4 bg-surface-container-lowest border border-surface-container-high rounded-xl flex justify-between items-center shadow-sm">
                            <div class="flex items-center space-x-reverse space-x-3">
                                <span class="material-symbols-outlined text-tertiary">
                                    {{ $lesson->type === 'video' ? 'play_circle' : 'assignment' }}
                                </span>
                                <div>
                                    <strong class="text-on-surface text-sm">{{ $lesson->title }}</strong>
                                    <span class="text-xs text-on-surface-variant mr-2">
                                        ({{ $lesson->type === 'video' ? 'فيديو مسجل' : 'امتحان MCQ' }})
                                    </span>
                                    @if($lesson->is_preview)
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded-full mr-2">معاينة مجانية</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-2 items-center">
                                @if($lesson->type === 'video' && $lesson->video_url)
                                    <button onclick="previewBunnyVideo('{{ config('services.bunny.stream_library_id') }}', '{{ $lesson->video_url }}')" class="bg-green-50 hover:bg-green-100 text-green-700 text-xs font-bold px-3 py-1.5 rounded-lg border border-green-200 transition-colors">عرض الفيديو 🎬</button>
                                @endif
                                @if($lesson->type === 'quiz')
                                    <a href="#quiz-builder-{{ $lesson->id }}" class="bg-primary-fixed hover:bg-primary-fixed-dim text-on-primary-fixed-variant text-xs font-bold px-3 py-1.5 rounded-lg transition-colors">إدارة الأسئلة 📝</a>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="text-on-surface-variant text-sm py-2">لا توجد دروس مضافة بهذا القسم حتى الآن.</li>
                    @endforelse
                </ul>

                <!-- نموذج إضافة درس/فيديو إلى هذا القسم -->
                <details class="bg-surface-container-low border border-surface-container-high rounded-xl p-4">
                    <summary class="cursor-pointer font-bold text-on-surface text-sm flex items-center space-x-reverse space-x-2">
                        <span class="material-symbols-outlined text-primary">add_circle</span>
                        <span>إضافة درس أو فيديو لهذا القسم (يدعم أحجام ملفات كبيرة 🚀)</span>
                    </summary>
                    <form id="chunk-upload-form-{{ $section->id }}" onsubmit="uploadChunkedLesson(event, {{ $section->id }}, '{{ route('instructor.lessons.chunked', $section->id) }}')" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-on-surface-variant mb-1">عنوان الدرس</label>
                            <input type="text" id="title-{{ $section->id }}" name="title" required class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary" placeholder="مثال: شرح رسم القلب الدورة الأذينية">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-on-surface-variant mb-1">نوع المحتوى</label>
                            <select id="type-{{ $section->id }}" name="type" required class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary">
                                <option value="video">فيديو مسجل (رفع بالأجزاء Chunked Upload - حجم كبير)</option>
                                <option value="quiz">امتحان MCQ واختبار تقييمي</option>
                            </select>
                        </div>
                        <div id="video-file-group-{{ $section->id }}">
                            <label class="block text-sm font-semibold text-on-surface-variant mb-1">اختر ملف الفيديو (مهما كان الحجم)</label>
                            <input type="file" id="video-file-{{ $section->id }}" accept="video/mp4,video/quicktime,video/mkv" class="w-full text-sm text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-primary-fixed file:text-on-primary-fixed-variant hover:file:bg-primary-fixed-dim">
                        </div>
                        <div class="flex items-center space-x-reverse space-x-2">
                            <input type="checkbox" id="is-preview-{{ $section->id }}" value="1" class="rounded border-outline-variant text-primary focus:ring-primary">
                            <label for="is-preview-{{ $section->id }}" class="text-sm font-medium text-on-surface-variant">السماح بالمعاينة المجانية للدرس قبل الاشتراك</label>
                        </div>

                        <script>
                            document.getElementById('type-{{ $section->id }}').addEventListener('change', function() {
                                const videoGroup = document.getElementById('video-file-group-{{ $section->id }}');
                                if (this.value === 'quiz') {
                                    videoGroup.style.display = 'none';
                                } else {
                                    videoGroup.style.display = 'block';
                                }
                            });
                        </script>

                        <!-- Progress Bar Container -->
                        <div id="progress-container-{{ $section->id }}" class="hidden mt-4">
                            <div class="flex justify-between text-xs font-semibold mb-1">
                                <span id="progress-status-{{ $section->id }}" class="text-on-surface-variant">جاري تجهيز وبدء الرفع...</span>
                                <span id="progress-percent-{{ $section->id }}" class="text-primary font-bold">0%</span>
                            </div>
                            <div class="w-full bg-surface-container-high rounded-full h-2.5 overflow-hidden">
                                <div id="progress-bar-{{ $section->id }}" class="bg-primary h-full w-0 transition-all duration-200"></div>
                            </div>
                        </div>

                        <button type="submit" id="submit-btn-{{ $section->id }}" class="bg-primary text-white font-bold px-6 py-2 rounded-lg hover:bg-primary-container transition-colors">رفع وحفظ الدرس</button>
                    </form>
                </details>
            </div>
        @empty
            <p class="text-on-surface-variant text-sm">قم بإضافة أقسام تنظيمية للبدء في رفع المحاضرات.</p>
        @endforelse
    </div>

    <!-- 2. الامتحانات والأسئلة -->
    <div class="bg-surface-container-lowest border border-surface-container-highest rounded-xl p-gutter shadow-sm mb-6" id="quizzes-assignments">
        <h2 class="text-xl font-bold text-primary mb-2">2. الامتحانات والاختبارات التقييمية (Quizzes & MCQs)</h2>
        <p class="text-on-surface-variant text-sm mb-6">قم بإنشاء امتحانات للدروس وإضافة أسئلة اختيار من متعدد مع تحديد الإجابة الصحيحة.</p>

        @foreach($course->sections as $section)
            @foreach($section->lessons as $lesson)
                @if($lesson->type === 'quiz')
                    <div id="quiz-builder-{{ $lesson->id }}" class="bg-surface border border-surface-container-highest rounded-xl p-stack-lg mb-6">
                        <h3 class="text-lg font-bold text-primary mb-4 flex items-center space-x-reverse space-x-2">
                            <span class="material-symbols-outlined text-primary">assignment_turned_in</span>
                            <span>امتحان الدرس: {{ $lesson->title }}</span>
                        </h3>

                        @if(!$lesson->quiz)
                            <!-- إنشاء نموذج الامتحان لأول مرة -->
                            <form action="{{ route('instructor.quizzes.store', $lesson->id) }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">عنوان الامتحان</label>
                                    <input type="text" name="title" value="امتحان {{ $lesson->title }}" required class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-on-surface-variant mb-1">نسبة النجاح (%)</label>
                                        <input type="number" name="pass_percentage" value="60" required class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-on-surface-variant mb-1">الحد الأقصى للمحاولات</label>
                                        <input type="number" name="attempts_allowed" value="3" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary">
                                    </div>
                                </div>
                                <button type="submit" class="bg-primary text-white font-bold px-6 py-2 rounded-lg hover:bg-primary-container transition-colors">إنشاء إعدادات الامتحان</button>
                            </form>
                        @else
                            <!-- إظهار الأسئلة ونموذج إضافة سؤال جديدة -->
                            <div class="bg-surface-container-lowest border border-surface-container-high p-4 rounded-xl mb-4 text-sm text-on-surface-variant">
                                نسبة النجاح المطلوب: <strong class="text-on-surface">{{ $lesson->quiz->pass_percentage }}%</strong> | عدد الأسئلة الحالية: <strong class="text-on-surface">{{ $lesson->quiz->questions->count() }}</strong>
                            </div>

                            <!-- قائمة الأسئلة المضافة -->
                            <div class="space-y-3 mb-6">
                                @foreach($lesson->quiz->questions as $qIndex => $question)
                                    <div class="bg-surface-container-lowest border border-surface-container-high p-4 rounded-xl shadow-sm">
                                        <strong class="text-on-surface text-sm">س{{ $qIndex + 1 }}: {{ $question->question_text }}</strong>
                                        <ul class="mt-2 space-y-1 text-sm text-on-surface-variant mr-4">
                                            @foreach($question->options as $opt)
                                                <li class="flex items-center space-x-reverse space-x-2 {{ $opt->is_correct ? 'text-green-600 font-bold' : '' }}">
                                                    <span class="material-symbols-outlined text-xs">
                                                        {{ $opt->is_correct ? 'check_circle' : 'radio_button_unchecked' }}
                                                    </span>
                                                    <span>{{ $opt->option_text }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>

                            <!-- نموذج إضافة سؤال جديد MCQ -->
                            <form action="{{ route('instructor.questions.store', $lesson->quiz->id) }}" method="POST" class="bg-surface-container-low border border-surface-container-high p-4 rounded-xl space-y-4">
                                @csrf
                                <h4 class="font-bold text-on-surface">+ إضافة سؤال اختيار من متعدد جديد</h4>
                                <div>
                                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">نص السؤال</label>
                                    <input type="text" name="question_text" required class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary" placeholder="مثال: ما هي الموجة المسئولة عن انقباض الأذينين؟">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">درجة السؤال</label>
                                    <input type="number" name="points" value="1" required class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-on-surface-variant mb-2">الخيارات المتاحة (حدد الإجابة الصحيحة)</label>
                                    <div class="space-y-2">
                                        <div class="flex items-center space-x-reverse space-x-2">
                                            <input type="radio" name="correct_option_index" value="0" checked class="text-primary focus:ring-primary">
                                            <input type="text" name="options[0][text]" required class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-1.5 text-on-surface focus:outline-none focus:border-primary" placeholder="الخيار الأول">
                                        </div>
                                        <div class="flex items-center space-x-reverse space-x-2">
                                            <input type="radio" name="correct_option_index" value="1" class="text-primary focus:ring-primary">
                                            <input type="text" name="options[1][text]" required class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-1.5 text-on-surface focus:outline-none focus:border-primary" placeholder="الخيار الثاني">
                                        </div>
                                        <div class="flex items-center space-x-reverse space-x-2">
                                            <input type="radio" name="correct_option_index" value="2" class="text-primary focus:ring-primary">
                                            <input type="text" name="options[2][text]" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-1.5 text-on-surface focus:outline-none focus:border-primary" placeholder="الخيار الثالث (اختياري)">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="bg-primary text-white font-bold px-6 py-2 rounded-lg hover:bg-primary-container transition-colors">حفظ السؤال</button>
                            </form>
                        @endif
                    </div>
                @endif
            @endforeach
        @endforeach
    </div>

    <!-- 4. المرفقات والملفات -->
    <div class="bg-surface-container-lowest border border-surface-container-highest rounded-xl p-gutter shadow-sm mb-6" id="attachments">
        <h2 class="text-xl font-bold text-primary mb-4">3. المرفقات والملفات الدراسية (Bunny Storage)</h2>
        
        <form action="{{ route('instructor.attachments.store', $course->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 mb-6">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-on-surface-variant mb-1">عنوان المستند/الملف</label>
                <input type="text" name="title" required class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary" placeholder="مثال: مذكرة الشرح الشاملة PDF">
            </div>
            <div>
                <label class="block text-sm font-semibold text-on-surface-variant mb-1">اختر الملف (PDF, DOCX, ZIP)</label>
                <input type="file" name="attachment" required class="w-full text-sm text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-primary-fixed file:text-on-primary-fixed-variant hover:file:bg-primary-fixed-dim">
            </div>
            <button type="submit" class="bg-primary text-white font-bold px-6 py-2 rounded-lg hover:bg-primary-container transition-colors">رفع الملف إلى Bunny Storage</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-surface-container-highest text-right">
                        <th class="py-3 text-on-surface-variant font-semibold text-sm">عنوان الملف</th>
                        <th class="py-3 text-on-surface-variant font-semibold text-sm">اسم الملف الأصلي</th>
                        <th class="py-3 text-on-surface-variant font-semibold text-sm">حجم الملف</th>
                        <th class="py-3 text-on-surface-variant font-semibold text-sm">تاريخ الرفع</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($course->files as $file)
                        <tr class="border-b border-surface-container-low hover:bg-surface-container-lowest">
                            <td class="py-4 text-on-surface font-medium">{{ $file->title }}</td>
                            <td class="py-4 text-on-surface-variant text-sm">{{ $file->file_name }}</td>
                            <td class="py-4 text-on-surface-variant text-sm">{{ round($file->file_size_bytes / 1024 / 1024, 2) }} MB</td>
                            <td class="py-4 text-on-surface-variant text-sm">{{ $file->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-on-surface-variant text-sm">لا توجد ملفات مرفقة بهذا الكورس.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

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

            // If it's a video, chunk and stream to Bunny via TUS Protocol
            if (type === 'video') {
                progressStatus.innerText = 'جاري إنشاء إدخال الفيديو وطلب صلاحيات الرفع...';
                
                // 1. Fetch TUS Credentials from server
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
                    throw new Error('مكتبة الرفع (tus) لم يتم تحميلها بشكل صحيح في المتصفح. يرجى تحديث الصفحة وإعادة المحاولة.');
                }

                // 2. Perform Direct TUS Resumable Upload to Bunny Stream
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
                        metadata: {
                            filename: file.name,
                            filetype: file.type
                        },
                        onError: (error) => {
                            reject(new Error('فشل الرفع إلى Bunny Stream: ' + error.message));
                        },
                        onProgress: (bytesUploaded, bytesTotal) => {
                            const percentage = Math.round((bytesUploaded / bytesTotal) * 95);
                            progressBar.style.width = percentage + '%';
                            progressPercent.innerText = percentage + '%';
                            progressStatus.innerText = `جاري الرفع المباشر: ${percentage}% (${(bytesUploaded / 1024 / 1024).toFixed(1)}MB / ${(bytesTotal / 1024 / 1024).toFixed(1)}MB)`;
                        },
                        onSuccess: () => {
                            resolve();
                        }
                    });

                    upload.start();
                });
            }

            // Finalize Database Entry on Server
            progressStatus.innerText = 'جاري حفظ بيانات المحاضرة بقاعدة البيانات...';
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
                alert('تم حفظ ورفع المحاضرة بنجاح! 🚀');
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
                <iframe src="${iframeUrl}" 
                        loading="lazy" 
                        style="border: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%;" 
                        allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;" 
                        allowfullscreen="true">
                </iframe>
            </div>
        `;
        
        modal.style.display = 'flex';
    }

    function closeVideoModal() {
        const modal = document.getElementById('bunny-video-modal');
        const container = document.getElementById('bunny-video-container');
        container.innerHTML = '';
        modal.style.display = 'none';
    }
    </script>

    <!-- Modal Box Container -->
    <div id="bunny-video-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 1000; align-items: center; justify-content: center;">
        <div class="bg-surface-container-lowest border border-outline rounded-2xl w-11/12 max-w-4xl p-6 relative">
            <button onclick="closeVideoModal()" class="absolute top-4 left-4 bg-error text-white font-bold px-4 py-2 rounded-lg">إغلاق X</button>
            <h3 class="text-lg font-bold mb-4 text-right text-on-surface">معاينة الفيديو المشغل 🎬</h3>
            <div id="bunny-video-container"></div>
        </div>
    </div>
@endsection
