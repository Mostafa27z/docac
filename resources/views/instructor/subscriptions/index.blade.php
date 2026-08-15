@extends('layouts.panel')

@section('title', 'المشتركين وأكواد التفعيل - Doc Academy')
@section('role_title', 'لوحة المحاضر')

@section('page_title', 'المشتركين والتفعيلات')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">
        <!-- Generate Activation Code Form -->
        <div class="lg:col-span-1 bg-surface-container-lowest border border-surface-container-highest rounded-xl p-gutter shadow-sm h-fit">
            <h2 class="text-xl font-bold text-primary mb-4">توليد أكواد تفعيل جديدة</h2>
            <form id="generate-codes-form" action="" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">اختر الكورس</label>
                    <select id="course-select" required onchange="updateFormAction(this.value)" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary">
                        <option value="">-- اختر الكورس --</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">الكمية المطلوبة لتوليد الأكواد</label>
                    <input type="number" name="quantity" min="1" max="50" value="10" required class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary">
                </div>
                <button type="submit" class="w-full bg-primary text-white font-bold px-6 py-3 rounded-lg hover:bg-primary-container transition-colors duration-150">توليد الأكواد الآن</button>
            </form>
        </div>

        <!-- Activation Codes Table -->
        <div class="lg:col-span-2 bg-surface-container-lowest border border-surface-container-highest rounded-xl p-gutter shadow-sm">
            <h2 class="text-xl font-bold text-primary mb-4">كشف أكواد تفعيل المسارات</h2>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-surface-container-highest text-right">
                            <th class="py-3 text-on-surface-variant font-semibold text-sm">الكورس</th>
                            <th class="py-3 text-on-surface-variant font-semibold text-sm">كود التفعيل</th>
                            <th class="py-3 text-on-surface-variant font-semibold text-sm">الحالة</th>
                            <th class="py-3 text-on-surface-variant font-semibold text-sm">المستخدم</th>
                            <th class="py-3 text-on-surface-variant font-semibold text-sm">تاريخ التفعيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activationCodes as $code)
                            <tr class="border-b border-surface-container-low hover:bg-surface-container-lowest">
                                <td class="py-4 text-on-surface text-sm font-semibold">{{ $code->course->title }}</td>
                                <td class="py-4 font-mono font-bold text-primary">{{ $code->code }}</td>
                                <td class="py-4 text-sm">
                                    @if($code->is_used)
                                        <span class="text-error font-medium">مُستخدم</span>
                                    @else
                                        <span class="text-green-600 font-medium">متاح</span>
                                    @endif
                                </td>
                                <td class="py-4 text-on-surface text-sm">{{ $code->student->name ?? '-' }}</td>
                                <td class="py-4 text-on-surface-variant text-sm">{{ $code->used_at ? $code->used_at->format('Y-m-d H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-on-surface-variant text-sm">لا توجد أكواد تفعيل مولدة حتى الآن.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function updateFormAction(courseId) {
            const form = document.getElementById('generate-codes-form');
            if (courseId) {
                form.action = `/instructor/courses/${courseId}/activation-codes`;
            } else {
                form.action = '';
            }
        }
    </script>
@endsection
