@extends('layouts.dashboard')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@php
$sidebarTitle    = 'البوابة الإدارية';
$pageTitle       = 'إدارة العاملين';
$pageDescription = 'إنشاء وإدارة حسابات المدرسين';
@endphp

@section('content')

<div x-data="staffManager()" x-init="init()" class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-end">
        <button @click="openCreate()"
            class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            إنشاء حساب مدرس
        </button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-primary" x-text="teachers.length"></p>
            <p class="text-xs text-gray-400 mt-1">إجمالي المدرسين</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-green-600" x-text="teachers.filter(t=>t.is_active).length"></p>
            <p class="text-xs text-gray-400 mt-1">نشط</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-orange-500" x-text="groups.length"></p>
            <p class="text-xs text-gray-400 mt-1">المجموعات</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-purple-600"
               x-text="teachers.reduce((s,t)=>s+t.groups.length,0)"></p>
            <p class="text-xs text-gray-400 mt-1">إجمالي التكليفات</p>
        </div>
    </div>

    {{-- Teachers Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">

        {{-- Loading --}}
        <div x-show="loading" class="p-10 text-center text-gray-400">
            <svg class="animate-spin h-8 w-8 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            جار التحميل...
        </div>

        {{-- Empty --}}
        <div x-show="!loading && teachers.length === 0" class="p-10 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-gray-400 text-sm">لا يوجد مدرسون مسجلون بعد</p>
        </div>

        {{-- Table --}}
        <div x-show="!loading && teachers.length > 0" class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المدرس</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">رقم الهوية</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">التخصصات</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المجموعات</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">الحساب المالي</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">الحالة</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <template x-for="t in teachers" :key="t.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                        <span class="text-primary font-bold text-sm" x-text="t.name.charAt(0)"></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm" x-text="t.name"></p>
                                        <p class="text-xs text-gray-400" x-text="t.birth_date"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600 font-mono text-sm" x-text="t.national_id"></td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="s in (t.specializations || [])" :key="s">
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-xs rounded-full" x-text="s"></span>
                                    </template>
                                    <span x-show="!t.specializations || t.specializations.length===0" class="text-gray-400 text-xs">—</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="g in t.groups" :key="g.id">
                                        <span class="px-2 py-0.5 bg-orange-50 text-orange-700 text-xs rounded-full" x-text="g.name"></span>
                                    </template>
                                    <span x-show="t.groups.length===0" class="text-gray-400 text-xs">—</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                <span x-show="t.account_type" x-text="accountTypeLabel(t.account_type)"></span>
                                <span x-show="t.account_number" class="block text-gray-400 font-mono" x-text="t.account_number"></span>
                                <span x-show="!t.account_type" class="text-gray-400">—</span>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="t.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                    class="px-2 py-1 rounded-full text-xs font-medium"
                                    x-text="t.is_active ? 'نشط' : 'موقوف'">
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <button @click="openEdit(t)"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="تعديل">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button @click="deleteTeacher(t)"
                                        class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== MODAL: إنشاء / تعديل ===================== --}}
    <div x-show="showModal" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @keydown.escape.window="showModal = false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b sticky top-0 bg-white z-10">
                <h3 class="text-base font-bold text-gray-800"
                    x-text="editingTeacher ? 'تعديل بيانات المدرس' : 'إنشاء حساب مدرس جديد'">
                </h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-6">

                {{-- ① البيانات الشخصية --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">البيانات الشخصية</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">اسم المدرس *</label>
                            <input type="text" x-model="form.name" placeholder="الاسم الرباعي"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">رقم الهوية *</label>
                            <input type="text" x-model="form.national_id" maxlength="9" inputmode="numeric"
                                placeholder="9 أرقام"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">تاريخ الميلاد *</label>
                            <input type="date" x-model="form.birth_date"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <p class="mt-1 text-xs text-gray-400">سيُستخدم كلمةَ المرور (DDMMYYYY)</p>
                        </div>
                    </div>
                </div>

                {{-- ② التخصصات --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">التخصصات *</h4>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="spec in availableSpecializations" :key="spec">
                            <button type="button" @click="toggleSpec(spec)"
                                :class="form.specializations.includes(spec)
                                    ? 'bg-primary text-white border-primary'
                                    : 'bg-white text-gray-600 border-gray-300 hover:border-primary hover:text-primary'"
                                class="px-3 py-1.5 rounded-full text-sm border transition">
                                <span x-text="spec"></span>
                            </button>
                        </template>
                    </div>
                    <p x-show="form.specializations.length === 0" class="mt-2 text-xs text-red-500">يرجى اختيار تخصص واحد على الأقل</p>
                </div>

                {{-- ③ المجموعات --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">المجموعات المُكلَّفة</h4>
                    <div x-show="groups.length === 0" class="text-xs text-gray-400">لا توجد مجموعات نشطة</div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <template x-for="g in groups" :key="g.id">
                            <label :class="form.groups.includes(g.id)
                                    ? 'bg-primary/10 border-primary/40 text-primary'
                                    : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300'"
                                class="flex items-center gap-2 p-2.5 rounded-lg cursor-pointer border transition text-sm">
                                <input type="checkbox" :value="g.id"
                                    :checked="form.groups.includes(g.id)"
                                    @change="toggleGroup(g.id)"
                                    class="text-primary rounded">
                                <span x-text="g.name"></span>
                            </label>
                        </template>
                    </div>
                </div>

                {{-- ④ بيانات الحساب المالي --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">بيانات الحساب المالي</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">نوع الحساب</label>
                            <select x-model="form.account_type"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">— اختر —</option>
                                <option value="bank_of_palestine">بنك فلسطين</option>
                                <option value="pal_pay">محفظة بال باي</option>
                                <option value="jawwal_pay">محفظة جوال باي</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">رقم الحساب أو الهاتف</label>
                            <input type="text" x-model="form.account_number"
                                placeholder="رقم الحساب أو رقم الهاتف"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                </div>

                {{-- Info box: login credentials --}}
                <div x-show="!editingTeacher && form.national_id && form.birth_date"
                    class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-700">
                    <p class="font-semibold mb-1">بيانات تسجيل الدخول التي ستُنشأ تلقائياً:</p>
                    <p>اسم المستخدم: <span class="font-mono font-bold" x-text="form.national_id"></span></p>
                    <p>كلمة المرور: <span class="font-mono font-bold" x-text="formatBirthAsPassword(form.birth_date)"></span></p>
                </div>

            </div>

            {{-- Footer --}}
            <div class="flex gap-3 px-6 pb-6">
                <button @click="saveTeacher()" :disabled="saving"
                    class="flex-1 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
                    <span x-show="!saving" x-text="editingTeacher ? 'حفظ التعديلات' : 'إنشاء الحساب'"></span>
                    <span x-show="saving">جار الحفظ...</span>
                </button>
                <button @click="showModal = false"
                    class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition">
                    إلغاء
                </button>
            </div>
        </div>
    </div>

</div>{{-- end x-data --}}

@push('scripts')
<script>
window.staffRoutes = {
    data:    '{{ route("admin.staff.data") }}',
    groups:  '{{ route("admin.staff.groups") }}',
    store:   '{{ route("admin.staff.store") }}',
    update:  '{{ url("/admin/staff") }}',
    destroy: '{{ url("/admin/staff") }}',
};

function staffManager() {
    return {
        teachers: [],
        groups:   [],
        loading:  false,

        showModal:      false,
        editingTeacher: null,
        saving:         false,

        form: {
            name: '', national_id: '', birth_date: '',
            specializations: [], groups: [],
            account_type: '', account_number: '',
        },

        availableSpecializations: [
            'لغة عربية', 'لغة إنجليزية', 'رياضيات',
            'علوم', 'كيمياء', 'فيزياء', 'أحياء',
        ],

        async init() {
            this.loading = true;
            await Promise.all([this.loadTeachers(), this.loadGroups()]);
            this.loading = false;
        },

        async loadTeachers() {
            try {
                const r = await fetch(window.staffRoutes.data);
                const d = await r.json();
                if (d.success) this.teachers = d.teachers;
            } catch (_) {}
        },

        async loadGroups() {
            try {
                const r = await fetch(window.staffRoutes.groups);
                const d = await r.json();
                if (d.success) this.groups = d.groups;
            } catch (_) {}
        },

        openCreate() {
            this.editingTeacher = null;
            this.form = {
                name: '', national_id: '', birth_date: '',
                specializations: [], groups: [],
                account_type: '', account_number: '',
            };
            this.showModal = true;
        },

        openEdit(teacher) {
            this.editingTeacher = teacher;
            this.form = {
                name:            teacher.name,
                national_id:     teacher.national_id,
                birth_date:      teacher.birth_date,
                specializations: [...(teacher.specializations || [])],
                groups:          teacher.groups.map(g => g.id),
                account_type:    teacher.account_type || '',
                account_number:  teacher.account_number || '',
            };
            this.showModal = true;
        },

        toggleSpec(spec) {
            const i = this.form.specializations.indexOf(spec);
            if (i > -1) this.form.specializations.splice(i, 1);
            else this.form.specializations.push(spec);
        },

        toggleGroup(id) {
            const i = this.form.groups.indexOf(id);
            if (i > -1) this.form.groups.splice(i, 1);
            else this.form.groups.push(id);
        },

        formatBirthAsPassword(dateStr) {
            if (!dateStr) return '';
            const [y, m, d] = dateStr.split('-');
            return `${d}${m}${y}`;
        },

        accountTypeLabel(t) {
            return { bank_of_palestine: 'بنك فلسطين', pal_pay: 'محفظة بال باي', jawwal_pay: 'محفظة جوال باي' }[t] || t;
        },

        async saveTeacher() {
            if (!this.form.name.trim() || !this.form.national_id.trim() || !this.form.birth_date) {
                alert('يرجى تعبئة الاسم ورقم الهوية وتاريخ الميلاد');
                return;
            }
            if (this.form.specializations.length === 0) {
                alert('يرجى اختيار تخصص واحد على الأقل');
                return;
            }

            this.saving = true;
            try {
                const url = this.editingTeacher
                    ? `${window.staffRoutes.update}/${this.editingTeacher.id}`
                    : window.staffRoutes.store;
                const method = this.editingTeacher ? 'PUT' : 'POST';

                const r = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.form),
                });
                const d = await r.json();

                if (d.success) {
                    this.showModal = false;
                    await this.loadTeachers();
                    alert(d.message);
                } else {
                    const errors = d.errors ? Object.values(d.errors).flat().join('\n') : (d.message || 'حدث خطأ');
                    alert(errors);
                }
            } catch (_) { alert('حدث خطأ في الاتصال'); }
            finally { this.saving = false; }
        },

        async deleteTeacher(teacher) {
            if (!confirm(`حذف حساب المدرس "${teacher.name}"؟ لا يمكن التراجع عن هذا الإجراء.`)) return;
            try {
                const r = await fetch(`${window.staffRoutes.destroy}/${teacher.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                const d = await r.json();
                if (d.success) {
                    this.teachers = this.teachers.filter(t => t.id !== teacher.id);
                    alert(d.message);
                } else alert(d.message || 'حدث خطأ في الحذف');
            } catch (_) { alert('حدث خطأ في الاتصال'); }
        },
    };
}
</script>
@endpush

@endsection
