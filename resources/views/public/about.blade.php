@extends('layouts.public')

@section('content')
<!-- Page Header -->
<section class="py-20 text-white bg-primary">
    <div class="px-4 mx-auto text-center max-w-7xl sm:px-6 lg:px-8">
        <h1 class="mb-4 text-4xl font-bold md:text-5xl">عن الأكاديمية</h1>
        <p class="max-w-2xl mx-auto text-xl">تعرف على قصتنا ورؤيتنا وقيمنا التي تحرك عملنا التعليمي</p>
    </div>
</section>

<!-- Our Story -->
<section class="py-20 bg-white">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid items-center grid-cols-1 gap-12 lg:grid-cols-2">
            <div>
                <h2 class="mb-6 text-3xl font-bold text-gray-900">قصتنا</h2>
                <div class="space-y-4 leading-relaxed text-gray-600">
                    <p>
                        تأسست الأكاديمية التعليمية عام 2015 بهدف سد الفجوة في التعليم الجودة العالية في منطقتنا.
                        بدأنا بفصول قليلة ومجموعة من المعلمين المتفانين الذين آمنوا برؤيتنا.
                    </p>
                    <p>
                        على مدار السنوات، نمت الأكاديمية لتصبح واحدة من المؤسسات التعليمية الرائدة،
                        حيث نخدم أكثر من 500 طالب ونوظف أكثر من 50 معلم مؤهل.
                    </p>
                    <p>
                        نفخر بكوننا لا نركز فقط على الأكاديميات، بل أيضاً على بناء شخصيات قوية
                        ومواطنين فاعلين في المجتمع.
                    </p>
                </div>
            </div>
            <div class="p-8 bg-gray-100 rounded-lg">
                <h3 class="mb-6 text-2xl font-bold text-primary">إنجازاتنا</h3>
                <div class="grid grid-cols-2 gap-6">
                    <div class="text-center">
                        <div class="mb-2 text-3xl font-bold text-primary">500+</div>
                        <div class="text-gray-600">طالب</div>
                    </div>
                    <div class="text-center">
                        <div class="mb-2 text-3xl font-bold text-primary">50+</div>
                        <div class="text-gray-600">معلم</div>
                    </div>
                    <div class="text-center">
                        <div class="mb-2 text-3xl font-bold text-primary">95%</div>
                        <div class="text-gray-600">معدل النجاح</div>
                    </div>
                    <div class="text-center">
                        <div class="mb-2 text-3xl font-bold text-primary">8</div>
                        <div class="text-gray-600">سنوات خبرة</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-20 bg-gray-50">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-16 text-center">
            <h2 class="mb-4 text-3xl font-bold text-gray-900 md:text-4xl">قيمنا ومبادئنا</h2>
            <p class="max-w-2xl mx-auto text-lg text-gray-600">
                نسترشد بمجموعة من القيم الأساسية التي تشكل أساس كل ما نقوم به
            </p>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
            <!-- Value 1 -->
            <div class="p-6 text-center">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-white rounded-full bg-primary">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <h3 class="mb-2 text-xl font-semibold text-gray-900">الابتكار</h3>
                <p class="text-gray-600">نسعى دائماً لتطوير أساليب التعليم وتبني التقنيات الحديثة</p>
            </div>

            <!-- Value 2 -->
            <div class="p-6 text-center">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-white rounded-full bg-primary">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <h3 class="mb-2 text-xl font-semibold text-gray-900">الاهتمام</h3>
                <p class="text-gray-600">نؤمن بأن كل طالب فريد ويستحق اهتماماً شخصياً متميزاً</p>
            </div>

            <!-- Value 3 -->
            <div class="p-6 text-center">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-white rounded-full bg-primary">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h3 class="mb-2 text-xl font-semibold text-gray-900">الشفافية</h3>
                <p class="text-gray-600">نتواصل بوضوح مع الطلاب وأولياء الأمور حول التقدم والتحديات</p>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-20 bg-white">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-16 text-center">
            <h2 class="mb-4 text-3xl font-bold text-gray-900 md:text-4xl">فريق القيادة</h2>
            <p class="max-w-2xl mx-auto text-lg text-gray-600">
                تعرف على الفريق الذي يقود الأكاديمية نحو التميز التعليمي
            </p>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
            <!-- Team Member 1 -->
            <div class="text-center">
                <div class="flex items-center justify-center w-32 h-32 mx-auto mb-4 bg-gray-200 rounded-full">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h3 class="mb-1 text-xl font-semibold text-gray-900">د. أحمد محمد</h3>
                <p class="mb-2 text-gray-600">مدير الأكاديمية</p>
                <p class="text-sm text-gray-500">دكتوراه في التربية وعلم النفس التعليمي</p>
            </div>

            <!-- Team Member 2 -->
            <div class="text-center">
                <div class="flex items-center justify-center w-32 h-32 mx-auto mb-4 bg-gray-200 rounded-full">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h3 class="mb-1 text-xl font-semibold text-gray-900">أ. فاطمة علي</h3>
                <p class="mb-2 text-gray-600">نائب المدير الأكاديمي</p>
                <p class="text-sm text-gray-500">ماجستير في المناهج وطرق التدريس</p>
            </div>

            <!-- Team Member 3 -->
            <div class="text-center">
                <div class="flex items-center justify-center w-32 h-32 mx-auto mb-4 bg-gray-200 rounded-full">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h3 class="mb-1 text-xl font-semibold text-gray-900">م. خالد أحمد</h3>
                <p class="mb-2 text-gray-600">مدير التقنيات التعليمية</p>
                <p class="text-sm text-gray-500">بكالوريوس هندسة الحاسوب</p>
            </div>
        </div>
    </div>
</section>
@endsection
