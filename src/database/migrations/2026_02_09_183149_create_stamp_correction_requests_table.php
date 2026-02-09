<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampCorrectionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stamp_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('status')->default(0)->comment('0:承認待ち, 1:承認済み');
            $table->time('clock_in_before')->nullable();
            $table->time('clock_in_after')->nullable();
            $table->time('break_start_before')->nullable();
            $table->time('break_start_after')->nullable();
            $table->time('break_end_before')->nullable();
            $table->time('break_end_after')->nullable();
            $table->time('clock_out_before')->nullable();
            $table->time('clock_out_after')->nullable();
            $table->text('reason');
            $table->timestamp('approved_at')->nullable();
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
        Schema::dropIfExists('stamp_correction_requests');
    }
}
