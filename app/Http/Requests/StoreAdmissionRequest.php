<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // أو حسب نظام الصلاحيات لديك
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'day'                => 'required|string',
            'application_date'   => 'required|date',
            'application_number' => 'nullable|string|size:4',
            'student_name'       => 'required|string|max:255',
            'student_id'         => 'required|string|size:9',
            'birth_date'         => 'required|date',
            'grade'              => 'required|string',
            'academic_level'     => 'required|string',
            'parent_name'        => 'required|string|max:255',
            'parent_id'          => 'required|string|size:9',
            'parent_job'         => 'required|string|max:255',
            'father_phone'       => 'required|string|regex:/^05\d{8}$/',
            'mother_phone'       => 'nullable|string|regex:/^05\d{8}$/',
            'address'            => 'required|string',
            'monthly_fee'        => 'required|numeric|min:0',
            'study_start_date'   => 'required|date',
            'payment_due_from'   => 'required|date',
            'payment_due_to'     => 'required|date',

        ];

        // إضافة قواعد إضافية عند التحديث
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['student_id'] = [
                'required',
                'digits:9',
                Rule::unique('admissions', 'student_id')->ignore($this->route('admission')),
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // بيانات الطلب
            'day.required'                     => 'يرجى اختيار اليوم',
            'day.in'                           => 'اليوم المختار غير صحيح',
            'application_date.required'        => 'يرجى اختيار تاريخ تقديم الطلب',
            'application_date.date'            => 'تاريخ تقديم الطلب غير صحيح',
            'application_date.before_or_equal' => 'تاريخ تقديم الطلب لا يمكن أن يكون في المستقبل',

            // بيانات الطالب
            'student_name.required'            => 'يرجى إدخال اسم الطالب',
            'student_name.regex'               => 'اسم الطالب يجب أن يحتوي على أحرف عربية فقط',
            'student_id.required'              => 'يرجى إدخال رقم هوية الطالب',
            'student_id.digits'                => 'رقم الهوية يجب أن يكون 9 أرقام فقط',
            'student_id.unique'                => 'رقم الهوية مسجل مسبقاً',
            'birth_date.required'              => 'يرجى اختيار تاريخ الميلاد',
            'birth_date.before'                => 'تاريخ الميلاد لا يمكن أن يكون اليوم أو في المستقبل',
            'birth_date.after'                 => 'عمر الطالب كبير جداً',
            'grade.required'                   => 'يرجى اختيار المرحلة الدراسية',
            'grade.in'                         => 'المرحلة الدراسية المختارة غير صحيحة',
            'academic_level.required'          => 'يرجى اختيار المستوى الأكاديمي',
            'academic_level.in'                => 'المستوى الأكاديمي المختار غير صحيح',

            // بيانات ولي الأمر
            'parent_name.required'             => 'يرجى إدخال اسم ولي الأمر',
            'parent_name.regex'                => 'اسم ولي الأمر يجب أن يحتوي على أحرف عربية فقط',
            'parent_id.required'               => 'يرجى إدخال رقم هوية ولي الأمر',
            'parent_id.digits'                 => 'رقم هوية ولي الأمر يجب أن يكون 9 أرقام فقط',
            'parent_job.required'              => 'يرجى إدخال مهنة ولي الأمر',

            // بيانات التواصل
            'father_phone.required'            => 'يرجى إدخال رقم جوال الأب',
            'father_phone.regex'               => 'رقم جوال الأب يجب أن يبدأ بـ 05 ويتكون من 10 أرقام',
            'father_phone.size'                => 'رقم جوال الأب يجب أن يتكون من 10 أرقام',
            'mother_phone.regex'               => 'رقم جوال الأم يجب أن يبدأ بـ 05 ويتكون من 10 أرقام',
            'mother_phone.size'                => 'رقم جوال الأم يجب أن يتكون من 10 أرقام',
            'address.required'                 => 'يرجى إدخال عنوان السكن',
            'address.min'                      => 'عنوان السكن قصير جداً (10 أحرف على الأقل)',
            'address.max'                      => 'عنوان السكن طويل جداً (500 حرف كحد أقصى)',

            // المعلومات المالية
            'monthly_fee.required'             => 'يرجى إدخال قيمة الرسوم الشهرية',
            'monthly_fee.numeric'              => 'قيمة الرسوم الشهرية يجب أن تكون رقماً',
            'monthly_fee.min'                  => 'قيمة الرسوم الشهرية لا يمكن أن تكون سالبة',
            'monthly_fee.max'                  => 'قيمة الرسوم الشهرية كبيرة جداً',
            'study_start_date.required'        => 'يرجى اختيار تاريخ بدء الدراسة',
            'study_start_date.after_or_equal'  => 'تاريخ بدء الدراسة لا يمكن أن يكون في الماضي',
            'payment_due_from.required'        => 'يرجى اختيار بداية فترة استحقاق الدفعة',
            'payment_due_to.required'          => 'يرجى اختيار نهاية فترة استحقاق الدفعة',
            'payment_due_to.after'             => 'نهاية فترة الاستحقاق يجب أن تكون بعد البداية',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // التحقق من الاسم الرباعي للطالب
            $studentName = $this->input('student_name');
            if ($studentName) {
                $nameParts = explode(' ', trim($studentName));
                $nameParts = array_filter($nameParts); // إزالة المسافات الفارغة

                if (count($nameParts) < 4) {
                    $validator->errors()->add('student_name', 'يرجى إدخال الاسم الرباعي كاملاً (4 أسماء على الأقل)');
                }
            }

            // التحقق من الاسم الثلاثي لولي الأمر
            $parentName = $this->input('parent_name');
            if ($parentName) {
                $nameParts = explode(' ', trim($parentName));
                $nameParts = array_filter($nameParts); // إزالة المسافات الفارغة

                if (count($nameParts) < 3) {
                    $validator->errors()->add('parent_name', 'يرجى إدخال الاسم الثلاثي كاملاً (3 أسماء على الأقل)');
                }
            }

            // التحقق من أن تاريخ بدء الدراسة ضمن فترة الاستحقاق
            $studyStartDate = $this->input('study_start_date');
            $paymentDueFrom = $this->input('payment_due_from');
            $paymentDueTo   = $this->input('payment_due_to');

            if ($studyStartDate && $paymentDueFrom && $paymentDueTo) {
                $studyStart = \Carbon\Carbon::parse($studyStartDate);
                $dueFrom    = \Carbon\Carbon::parse($paymentDueFrom);
                $dueTo      = \Carbon\Carbon::parse($paymentDueTo);

                if ($studyStart->lt($dueFrom) || $studyStart->gt($dueTo)) {
                    $validator->errors()->add('study_start_date', 'تاريخ بدء الدراسة يجب أن يكون ضمن فترة استحقاق الدفعة');
                }
            }

            // التحقق من عدم تشابه أرقام هوية الطالب وولي الأمر
            $studentId = $this->input('student_id');
            $parentId  = $this->input('parent_id');

            if ($studentId && $parentId && $studentId === $parentId) {
                $validator->errors()->add('parent_id', 'رقم هوية ولي الأمر لا يمكن أن يكون نفس رقم هوية الطالب');
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'day'                => 'اليوم',
            'application_date'   => 'تاريخ تقديم الطلب',
            'application_number' => 'رقم الطلب',
            'student_name'       => 'اسم الطالب',
            'student_id'         => 'رقم هوية الطالب',
            'birth_date'         => 'تاريخ الميلاد',
            'grade'              => 'المرحلة الدراسية',
            'academic_level'     => 'المستوى الأكاديمي',
            'parent_name'        => 'اسم ولي الأمر',
            'parent_id'          => 'رقم هوية ولي الأمر',
            'parent_job'         => 'مهنة ولي الأمر',
            'father_phone'       => 'رقم جوال الأب',
            'mother_phone'       => 'رقم جوال الأم',
            'address'            => 'عنوان السكن',
            'monthly_fee'        => 'الرسوم الشهرية',
            'study_start_date'   => 'تاريخ بدء الدراسة',
            'payment_due_from'   => 'بداية فترة الاستحقاق',
            'payment_due_to'     => 'نهاية فترة الاستحقاق',
        ];
    }
}