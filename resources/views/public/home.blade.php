@extends('layouts.public')

@section('content')
<!-- Hero Section -->
<section class="flex items-center min-h-screen hero-bg" dir="rtl">
    <div class="w-full px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="text-center text-white">
            <h1 class="mb-6 text-4xl font-bold leading-tight md:text-6xl">
                مرحباً بكم في الأكاديمية التعليمية
            </h1>
            <p class="max-w-4xl mx-auto mb-8 text-lg leading-relaxed md:text-xl lg:text-2xl">
                نحن نقدم تعليماً متميزاً يساعد الطلاب على تحقيق أهدافهم الأكاديمية والشخصية في بيئة تعليمية محفزة
                ومبتكرة
            </p>
            <div class="flex flex-col items-center justify-center gap-4 mt-8 md:flex-row">
                <a href="{{ route('portal.student') }}"
                    class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white transition duration-300 transform rounded-lg shadow-lg bg-secondary hover:bg-orange-600 hover:shadow-xl hover:scale-105">
                    <span>دخول الطلبة</span>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="#about"
                    class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white transition duration-300 transform border-2 border-white rounded-lg hover:bg-white hover:text-blue-500 hover:scale-105">
                    <span>تعرف علينا أكثر</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-20 bg-white" dir="rtl">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-16 text-center">
            <h2 class="mb-6 text-3xl font-bold text-gray-900 md:text-4xl lg:text-5xl">لماذا تختار أكاديميتنا؟</h2>
            <p class="max-w-3xl mx-auto text-lg leading-relaxed text-gray-600 md:text-xl">
                نوفر بيئة تعليمية شاملة تجمع بين التقنيات الحديثة والأساليب التعليمية المتطورة
            </p>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            <!-- Feature 1 -->
            <div
                class="p-8 text-center transition duration-300 transform bg-white shadow-lg rounded-xl hover:shadow-xl hover:scale-105">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 text-white rounded-full bg-primary">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="mb-4 text-xl font-semibold text-gray-900">مناهج متطورة</h3>
                <p class="leading-relaxed text-gray-600">مناهج حديثة تواكب التطور العلمي والتكنولوجي لإعداد جيل مبدع</p>
            </div>

            <!-- Feature 2 -->
            <div
                class="p-8 text-center transition duration-300 transform bg-white shadow-lg rounded-xl hover:shadow-xl hover:scale-105">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 text-white rounded-full bg-primary">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="mb-4 text-xl font-semibold text-gray-900">كادر مؤهل</h3>
                <p class="leading-relaxed text-gray-600">مدرسون ذوو خبرة وكفاءة عالية ملتزمون بتحقيق أفضل النتائج</p>
            </div>

            <!-- Feature 3 -->
            <div
                class="p-8 text-center transition duration-300 transform bg-white shadow-lg rounded-xl hover:shadow-xl hover:scale-105">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 text-white rounded-full bg-primary">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 class="mb-4 text-xl font-semibold text-gray-900">متابعة مستمرة</h3>
                <p class="leading-relaxed text-gray-600">نظام متابعة دقيق للحضور والأداء الأكاديمي لضمان التقدم المستمر
                </p>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="py-20 bg-gray-200" dir="rtl">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid items-center grid-cols-1 gap-12 lg:gap-16 lg:grid-cols-2">
            <div>
                <h2 class="mb-6 text-3xl font-bold text-gray-900 md:text-4xl lg:text-5xl">عن الأكاديمية التعليمية</h2>
                <p class="mb-6 text-lg leading-relaxed text-gray-600">
                    تأسست الأكاديمية التعليمية لتكون منارة علم وتميز في المجال التعليمي. نسعى لتطوير قدرات الطلاب
                    وإكسابهم المهارات اللازمة للنجاح في حياتهم الأكاديمية والمهنية.
                </p>
                <p class="mb-8 text-lg leading-relaxed text-gray-600">
                    من خلال استخدام أحدث التقنيات التعليمية وتطبيق أفضل الممارسات التربوية، نضمن بيئة تعليمية
                    محفزة تساعد كل طالب على تحقيق إمكاناته الكاملة.
                </p>
                <div class="grid grid-cols-2 gap-8">
                    <div class="text-center">
                        <div class="mb-2 text-4xl font-bold text-primary">500+</div>
                        <div class="text-lg font-medium text-gray-600">طالب</div>
                    </div>
                    <div class="text-center">
                        <div class="mb-2 text-4xl font-bold text-primary">50+</div>
                        <div class="text-lg font-medium text-gray-600">مدرس</div>
                    </div>
                </div>
            </div>
            <div class="relative">
                <div class="p-8 text-white shadow-2xl rounded-xl bg-gradient-to-br from-blue-500 to-blue-700">
                    <h3 class="mb-4 text-2xl font-bold">رؤيتنا</h3>
                    <p class="mb-6 leading-relaxed">أن نكون الأكاديمية التعليمية الرائدة في المنطقة في تقديم تعليم عالي
                        الجودة يواكب
                        التطورات العالمية.</p>

                    <h3 class="mb-4 text-2xl font-bold">رسالتنا</h3>
                    <p class="leading-relaxed">تقديم تعليم متميز يركز على بناء شخصية الطالب وتنمية قدراته العلمية
                        والإبداعية في بيئة تعليمية
                        آمنة ومحفزة.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Portals Section -->
<section class="py-20 bg-white" dir="rtl">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-16 text-center">
            <h2 class="mb-6 text-3xl font-bold text-gray-900 md:text-4xl lg:text-5xl">بوابات الأكاديمية</h2>
            <p class="max-w-3xl mx-auto text-lg leading-relaxed text-gray-600">
                اختر البوابة المناسبة لك للدخول إلى النظام والاستفادة من الخدمات المتاحة
            </p>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <!-- Student Portal -->
            <a href="{{ route('portal.student') }}"
                class="block p-8 text-white transition-all duration-300 transform group rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 hover:shadow-2xl hover:scale-105">
                <div class="text-center">
                    <div
                        class="flex items-center justify-center w-16 h-16 mx-auto mb-6 transition-all duration-300 bg-white rounded-full bg-opacity-20 group-hover:bg-opacity-30">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold">بوابة الطلبة</h3>
                    <p class="text-sm leading-relaxed opacity-90">الجداول والمحاضرات والتقارير الأكاديمية</p>
                </div>
            </a>

            <!-- Teacher Portal -->
            <a href="{{ route('portal.teacher') }}"
                class="block p-8 text-white transition-all duration-300 transform group rounded-xl bg-gradient-to-br from-green-500 to-green-700 hover:shadow-2xl hover:scale-105">
                <div class="text-center">
                    <div
                        class="flex items-center justify-center w-16 h-16 mx-auto mb-6 transition-all duration-300 bg-white rounded-full bg-opacity-20 group-hover:bg-opacity-30">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold">بوابة المدرسين</h3>
                    <p class="text-sm leading-relaxed opacity-90">إدارة الصفوف والدرجات والحضور</p>
                </div>
            </a>

            <!-- Admin Portal -->
            <a href="{{ route('portal.admin') }}"
                class="block p-8 text-white transition-all duration-300 transform group rounded-xl bg-gradient-to-br from-purple-500 to-purple-700 hover:shadow-2xl hover:scale-105">
                <div class="text-center">
                    <div
                        class="flex items-center justify-center w-16 h-16 mx-auto mb-6 transition-all duration-300 bg-white rounded-full bg-opacity-20 group-hover:bg-opacity-30">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold">بوابة الإدارة</h3>
                    <p class="text-sm leading-relaxed opacity-90">إدارة النظام والمستخدمين والتقارير</p>
                </div>
            </a>

            <!-- Parent Portal -->
            <a href="{{ route('portal.parent') }}"
                class="block p-8 text-white transition-all duration-300 transform group rounded-xl bg-gradient-to-br from-orange-500 to-red-600 hover:shadow-2xl hover:scale-105">
                <div class="text-center">
                    <div
                        class="flex items-center justify-center w-16 h-16 mx-auto mb-6 transition-all duration-300 bg-white rounded-full bg-opacity-20 group-hover:bg-opacity-30">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold">بوابة أولياء الأمور</h3>
                    <p class="text-sm leading-relaxed opacity-90">متابعة أداء الأبناء والتواصل مع المدرسة</p>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-20 text-white bg-gradient-to-r from-blue-500 to-blue-700" dir="rtl">
    <div class="px-4 mx-auto text-center max-w-7xl sm:px-6 lg:px-8">
        <h2 class="mb-6 text-3xl font-bold md:text-4xl lg:text-5xl">انضم إلى عائلة الأكاديمية</h2>
        <p class="max-w-3xl mx-auto mb-8 text-lg leading-relaxed md:text-xl">
            ابدأ رحلتك التعليمية معنا واستفد من أفضل البرامج والخدمات التعليمية المتطورة
        </p>
        <a href="{{ route('contact') }}"
            class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white transition duration-300 transform rounded-lg shadow-lg bg-secondary hover:bg-orange-600 hover:shadow-xl hover:scale-105">
            <span>تواصل معنا الآن</span>
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
        </a>
    </div>
</section>
@endsection