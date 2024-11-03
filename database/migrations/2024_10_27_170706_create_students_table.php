<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name');
            $table->timestamps();
        });
        
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->string('name');
            $table->integer('roll')->unique();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
            
            $table->foreign('class_id')
                ->references('id')
                ->on('classes');
        });
        
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->string('name');
            $table->string('short_name');
            $table->timestamps();
            
            $table->foreign('class_id')
                ->references('id')
                ->on('classes');
        });
        
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name');
            $table->timestamps();
        });
        
        Schema::create('exam_subject_distributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('subject_id');
            $table->integer('full_mark');
            $table->longText('criteria');
            $table->timestamps();
            $table->foreign('class_id')
                ->references('id')
                ->on('classes');
            $table->foreign('exam_id')
                ->references('id')
                ->on('exams');
            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects');
        });
        
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('subject_id');
            $table->integer('total_mark_obtain');
            $table->decimal('point');
            $table->string('grade');
            $table->string('status');
            $table->longText('result');
            $table->timestamps();
            
            $table->foreign('student_id')
                ->references('id')
                ->on('students');
            $table->foreign('class_id')
                ->references('id')
                ->on('classes');
            $table->foreign('exam_id')
                ->references('id')
                ->on('exams');
            $table->foreign('subject_id')
                ->references('id')
                ->on('classes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('classes');
        Schema::dropIfExists('students');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_subject_distributions');
        Schema::dropIfExists('results');
    }
};
