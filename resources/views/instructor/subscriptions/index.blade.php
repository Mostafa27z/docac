@extends('layouts.panel')

@section('title', 'المشتركين والأقساط - Doc Academy')
@section('role_title', 'لوحة المحاضر')
@section('page_title', 'المشتركين والتفعيلات')

@section('content')
    {{-- Enrolled Students & Installments --}}
    <x-card>
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[#E2E8F0]">
            <div class="p-2 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                <i class="ph-bold ph-student text-lg"></i>
            </div>
            <h2 class="text-lg font-bold text-[#1A202C]">الطلاب المشتركين والأقساط المستحقة</h2>
        </div>

        <x-data-table :headers="['الطالب والمعلومات', 'الكورس المنضم إليه', 'سعر الكورس المستحق', 'إجمالي المدفوع', 'المتبقي على الطالب', 'حالة الدفع', 'الإجراءات']">
            @forelse($enrollments as $enrollment)
                @php
                    $remaining = $enrollment->total_price - $enrollment->paid_amount;
                @endphp
                <tr class="border-b border-[#E2E8F0] hover:bg-[#F8F9FA] transition-colors">
                    <td class="py-4 px-4">
                        <div class="flex flex-col">
                            <span class="font-semibold text-[#1A202C]">{{ $enrollment->student->name }}</span>
                            <span class="text-xs text-[#718096]">{{ $enrollment->student->email }}</span>
                        </div>
                    </td>
                    <td class="py-4 px-4 text-sm text-[#1A202C] font-semibold">{{ $enrollment->course->title }}</td>
                    <td class="py-4 px-4 text-sm font-mono font-bold">{{ $enrollment->total_price }}</td>
                    <td class="py-4 px-4 text-sm font-mono font-bold text-[#00A896]">{{ $enrollment->paid_amount }}</td>
                    <td class="py-4 px-4 text-sm font-mono font-bold text-red-600">{{ $remaining }}</td>
                    <td class="py-4 px-4">
                        @if($enrollment->payment_status === 'fully_paid')
                            <x-badge variant="success">خالص تماماً</x-badge>
                        @elseif($enrollment->payment_status === 'partially_paid')
                            <x-badge variant="warning">مدفوع جزئياً</x-badge>
                        @else
                            <x-badge variant="error">غير مدفوع</x-badge>
                        @endif
                    </td>
                    <td class="py-4 px-4">
                        <div class="flex gap-2">
                            @if($enrollment->payment_status !== 'fully_paid')
                                <button onclick="openInstallmentModal({{ $enrollment->id }}, '{{ $enrollment->student->name }}', {{ $remaining }})" 
                                        class="inline-flex items-center gap-1 bg-[#0047AB]/10 hover:bg-[#0047AB] text-[#0047AB] hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all duration-200">
                                    <i class="ph-bold ph-plus-circle"></i>
                                    تسجيل دفعة
                                </button>
                            @endif
                            <button onclick="openHistoryModal('{{ $enrollment->student->name }}', '{{ $enrollment->course->title }}', '{{ json_encode($enrollment->payments) }}')" 
                                    class="inline-flex items-center gap-1 bg-[#F8F9FA] hover:bg-[#E2E8F0] border border-[#E2E8F0] text-[#4A5568] text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                <i class="ph-bold ph-clock"></i>
                                كشف الدفعات
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-[#718096] text-sm">لا يوجد طلاب مسجلين في كورساتك حالياً.</td>
                </tr>
            @endforelse
        </x-data-table>
    </x-card>

    {{-- Modal: Add Installment --}}
    <x-modal id="installmentModal" title="تسجيل دفعة مالية جديدة" icon="plus-circle">
        <form id="installment-form" action="" method="POST" class="space-y-4">
            @csrf
            <div>
                <p class="text-sm text-[#1A202C] mb-2 font-medium">
                    الطالب: <span id="installment-student-name" class="text-[#0047AB] font-bold"></span>
                </p>
                <p class="text-xs text-red-600 mb-4 font-semibold">
                    المبلغ المتبقي المستحق: <span id="installment-remaining-amount"></span>
                </p>
            </div>
            
            <x-form-input label="قيمة الدفعة / القسط" name="amount" type="number" step="0.01" min="0.01" :required="true" placeholder="ادخل القيمة" />
            <x-form-input label="ملاحظات الدفعة" name="notes" placeholder="مثال: القسط الأول، دفعة نقدية بالعيادة" />

            <div class="flex justify-end gap-2 pt-4 border-t border-[#E2E8F0]">
                <x-btn-secondary onclick="closeModal('installmentModal')">إلغاء</x-btn-secondary>
                <x-btn-primary icon="plus-circle" type="submit">تسجيل الدفعة</x-btn-primary>
            </div>
        </form>
    </x-modal>

    {{-- Modal: Payments History --}}
    <x-modal id="historyModal" title="سجل الدفعات المستلمة" icon="clock">
        <div class="mb-4">
            <p class="text-sm text-[#1A202C] font-medium">
                الطالب: <span id="history-student-name" class="text-[#0047AB] font-bold"></span>
            </p>
            <p class="text-xs text-[#718096] mt-1">
                الكورس: <span id="history-course-title" class="font-semibold text-dark"></span>
            </p>
        </div>

        <div class="border border-[#E2E8F0] rounded-xl overflow-hidden bg-[#F8F9FA]">
            <x-data-table :headers="['قيمة الدفعة', 'التاريخ والوقت', 'ملاحظات']">
                <tbody id="history-table-body" class="text-xs">
                    {{-- Dynamically populated --}}
                </tbody>
            </x-data-table>
        </div>

        <div class="flex justify-end gap-2 pt-5 mt-4 border-t border-[#E2E8F0]">
            <x-btn-secondary onclick="closeModal('historyModal')">إغلاق</x-btn-secondary>
        </div>
    </x-modal>

    @push('scripts')
    <script>
        function openInstallmentModal(enrollmentId, studentName, remainingAmount) {
            document.getElementById('installment-student-name').innerText = studentName;
            document.getElementById('installment-remaining-amount').innerText = remainingAmount;
            
            // Set input max limit
            const amountInput = document.querySelector('#installmentModal input[name="amount"]');
            amountInput.max = remainingAmount;
            amountInput.value = '';

            // Set form action url
            const form = document.getElementById('installment-form');
            form.action = `/instructor/enrollments/${enrollmentId}/installments`;

            openModal('installmentModal');
        }

        function openHistoryModal(studentName, courseTitle, paymentsJson) {
            document.getElementById('history-student-name').innerText = studentName;
            document.getElementById('history-course-title').innerText = courseTitle;

            const payments = JSON.parse(paymentsJson);
            const tbody = document.getElementById('history-table-body');
            tbody.innerHTML = '';

            if (payments.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="3" class="py-4 text-center text-[#718096]">لا يوجد أي دفعات مسجلة لهذا الطالب حتى الآن.</td>
                    </tr>
                `;
            } else {
                payments.forEach(payment => {
                    const date = new Date(payment.created_at).toLocaleDateString('ar-EG', {
                        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                    });
                    tbody.innerHTML += `
                        <tr class="border-b border-[#E2E8F0] bg-white hover:bg-[#F8F9FA] transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-[#00A896]">${payment.amount}</td>
                            <td class="py-3 px-4 text-[#718096]">${date}</td>
                            <td class="py-3 px-4 text-[#1A202C]">${payment.notes || '-'}</td>
                        </tr>
                    `;
                });
            }

            openModal('historyModal');
        }
    </script>
    @endpush
@endsection
