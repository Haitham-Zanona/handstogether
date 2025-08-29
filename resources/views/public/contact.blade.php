@extends('layouts.public')

@section('content')
<!-- Page Header -->
<section class="py-20 text-white bg-primary">
    <div class="px-4 mx-auto text-center max-w-7xl sm:px-6 lg:px-8">
        <h1 class="mb-4 text-4xl font-bold md:text-5xl">تواصل معنا</h1>
        <p class="max-w-2xl mx-auto text-xl">نحن هنا للإجابة على استفساراتكم ومساعدتكم</p>
    </div>
</section>

<!-- Contact Information -->
<section class="py-20 bg-white">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">
            <!-- Contact Form -->
            <div>
                <h2 class="mb-6 text-3xl font-bold text-gray-900">أرسل لنا رسالة</h2>

                @if(session('success'))
                <div class="px-4 py-3 mb-6 text-green-700 border border-green-200 rounded-lg bg-green-50">
                    {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="px-4 py-3 mb-6 text-red-700 border border-red-200 rounded-lg bg-red-50">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('contact.send') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-700">الاسم الكامل
                                *</label>
                            <input type="text" id="name" name="name" required value="{{ old('name') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary @error('name') border-red-500 @enderror">
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="phone" class="block mb-2 text-sm font-medium text-gray-700">رقم الهاتف *</label>
                            <input type="tel" id="phone" name="phone" required value="{{ old('phone') }}"
                                placeholder="+970-599-123-456"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary @error('phone') border-red-500 @enderror">
                            @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-700">البريد الإلكتروني
                            *</label>
                        <input type="email" id="email" name="email" required value="{{ old('email') }}"
                            placeholder="example@email.com"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary @error('email') border-red-500 @enderror">
                        @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="subject" class="block mb-2 text-sm font-medium text-gray-700">الموضوع *</label>
                        <select id="subject" name="subject" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary @error('subject') border-red-500 @enderror">
                            <option value="">اختر الموضوع</option>
                            <option value="general" {{ old('subject')=='general' ? 'selected' : '' }}>استفسار عام
                            </option>
                            <option value="admission" {{ old('subject')=='admission' ? 'selected' : '' }}>طلب انتساب
                            </option>
                            <option value="complaint" {{ old('subject')=='complaint' ? 'selected' : '' }}>شكوى</option>
                            <option value="suggestion" {{ old('subject')=='suggestion' ? 'selected' : '' }}>اقتراح
                            </option>
                            <option value="technical" {{ old('subject')=='technical' ? 'selected' : '' }}>مشكلة تقنية
                            </option>
                            <option value="other" {{ old('subject')=='other' ? 'selected' : '' }}>أخرى</option>
                        </select>
                        @error('subject')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="message" class="block mb-2 text-sm font-medium text-gray-700">الرسالة *</label>
                        <textarea id="message" name="message" rows="6" required placeholder="اكتب رسالتك هنا..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                        @error('message')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- reCAPTCHA (اختياري) -->
                    <div class="flex items-center">
                        <input type="checkbox" id="agree" name="agree" required
                            class="w-4 h-4 border-gray-300 rounded text-primary focus:ring-primary">
                        <label for="agree" class="block mr-2 text-sm text-gray-700">
                            أوافق على <a href="#" class="text-primary hover:underline">سياسة الخصوصية</a> و
                            <a href="#" class="text-primary hover:underline">شروط الاستخدام</a>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full px-8 py-3 font-semibold text-white transition duration-300 rounded-md md:w-auto bg-primary hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        <svg class="inline-block w-5 h-5 ml-2" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        إرسال الرسالة
                    </button>
                </form>
            </div>

            <!-- Contact Information -->
            <div>
                <h2 class="mb-6 text-3xl font-bold text-gray-900">معلومات التواصل</h2>
                <div class="space-y-6">
                    <!-- Address -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0 p-3 ml-4 text-white rounded-full bg-primary">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="mb-1 text-lg font-semibold text-gray-900">العنوان</h3>
                            <p class="text-gray-600">شارع المدارس، مجمع التعليم<br>نابلس، فلسطين</p>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0 p-3 ml-4 text-white rounded-full bg-primary">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="mb-1 text-lg font-semibold text-gray-900">الهاتف</h3>
                            <p class="text-gray-600">
                                <a href="tel:+970-9-123-4567"
                                    class="transition duration-300 hover:text-primary">+970-9-123-4567</a><br>
                                <a href="tel:+970-599-123-456"
                                    class="transition duration-300 hover:text-primary">+970-599-123-456</a>
                            </p>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0 p-3 ml-4 text-white rounded-full bg-primary">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M3 8l7.89 7.89a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="mb-1 text-lg font-semibold text-gray-900">البريد الإلكتروني</h3>
                            <p class="text-gray-600">
                                <a href="mailto:info@academy.edu"
                                    class="transition duration-300 hover:text-primary">info@academy.edu</a><br>
                                <a href="mailto:admin@academy.edu"
                                    class="transition duration-300 hover:text-primary">admin@academy.edu</a>
                            </p>
                        </div>
                    </div>

                    <!-- Working Hours -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0 p-3 ml-4 text-white rounded-full bg-primary">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="mb-1 text-lg font-semibold text-gray-900">ساعات العمل</h3>
                            <p class="text-gray-600">
                                الأحد - الخميس: 8:00 ص - 4:00 م<br>
                                السبت: 9:00 ص - 12:00 م<br>
                                <span class="text-sm text-red-600">الجمعة: مغلق</span>
                            </p>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0 p-3 ml-4 text-white rounded-full bg-primary">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="mb-1 text-lg font-semibold text-gray-900">وسائل التواصل الاجتماعي</h3>
                            <div class="flex mt-2 space-x-4 space-x-reverse">
                                <a href="#" class="transition duration-300 text-primary hover:text-blue-700">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                                    </svg>
                                </a>
                                <a href="#" class="transition duration-300 text-primary hover:text-blue-700">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z" />
                                    </svg>
                                </a>
                                <a href="#" class="transition duration-300 text-primary hover:text-blue-700">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                                    </svg>
                                </a>
                                <a href="#" class="transition duration-300 text-primary hover:text-blue-700">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.347-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.746-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001 12.017.001z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map placeholder -->
                <div class="flex items-center justify-center h-64 mt-8 overflow-hidden bg-gray-200 rounded-lg">
                    <div class="text-center">
                        <svg class="w-16 h-16 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <p class="text-gray-500">خريطة الموقع</p>
                        <p class="mt-1 text-sm text-gray-400">نابلس، فلسطين</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section (اختياري) -->
<section class="py-20 bg-gray-50">
    <div class="max-w-4xl px-4 mx-auto sm:px-6 lg:px-8">
        <div class="mb-12 text-center">
            <h2 class="mb-4 text-3xl font-bold text-gray-900 md:text-4xl">الأسئلة الشائعة</h2>
            <p class="text-lg text-gray-600">إجابات للأسئلة الأكثر شيوعاً</p>
        </div>

        <div class="space-y-6">
            <div class="p-6 bg-white rounded-lg shadow">
                <h3 class="mb-2 text-lg font-semibold text-gray-900">كيف يمكنني تقديم طلب انتساب؟</h3>
                <p class="text-gray-600">يمكنكم تقديم طلب الانتساب عبر زيارة الأكاديمية مباشرة أو التواصل معنا هاتفياً
                    لحجز موعد.</p>
            </div>

            <div class="p-6 bg-white rounded-lg shadow">
                <h3 class="mb-2 text-lg font-semibold text-gray-900">ما هي الرسوم الشهرية؟</h3>
                <p class="text-gray-600">تختلف الرسوم حسب المرحلة الدراسية والبرنامج المختار. يرجى التواصل معنا للحصول
                    على التفاصيل الكاملة.</p>
            </div>

            <div class="p-6 bg-white rounded-lg shadow">
                <h3 class="mb-2 text-lg font-semibold text-gray-900">هل توفرون نقل للطلاب؟</h3>
                <p class="text-gray-600">نعم، نوفر خدمة النقل المدرسي لمناطق محددة في نابلس. يرجى الاستفسار عن التفاصيل
                    والمناطق المشمولة.</p>
            </div>

            <div class="p-6 bg-white rounded-lg shadow">
                <h3 class="mb-2 text-lg font-semibold text-gray-900">كيف يمكنني متابعة أداء طفلي؟</h3>
                <p class="text-gray-600">عبر بوابة أولياء الأمور الإلكترونية، يمكنكم متابعة الحضور والغياب والدرجات
                    والأنشطة بشكل يومي.</p>
            </div>
        </div>
    </div>
</section>
@endsection
