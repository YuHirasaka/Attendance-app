<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceCorrectionBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_correction_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_correction_id')->constrained()->cascadeOnDelete();
            $table->time('requested_break_start');
            $table->time('requested_break_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_correction_breaks');
    }
}
