<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('grade_level')->nullable();
            $table->integer('students_count')->default(0);
            $table->integer('max_capacity')->default(30);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('groups');
    }
};
