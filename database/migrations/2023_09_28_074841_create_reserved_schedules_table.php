<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reserved_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('event_id');
            $table->date('date');
            $table->string('employee_name');
            $table->timestamp('time_start');
            $table->timestamp('time_end')->nullable();
            $table->string('email');
            $table->string('department', 100);
            $table->string('purpose');
            $table->string('equipment');
            $table->string('ct_arrangement');
            $table->string('status');
            $table->string('conference_room', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserved_schedules');
    }
};
