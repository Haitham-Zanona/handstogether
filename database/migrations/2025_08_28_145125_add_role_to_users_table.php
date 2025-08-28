<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // إضافة حقل role بعد حقل email مباشرة
            // enum يعني قيم محددة فقط يمكن إدخالها
            $table->enum('role', ['admin', 'teacher', 'parent', 'student'])->after('email');

            // إضافة حقل phone بعد role، nullable يعني اختياري
            $table->string('phone')->nullable()->after('role');
        });
    }

    // في حال أردنا التراجع عن هذا Migration
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone']);
        });
    }
};
