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
        Schema::create('travel_order_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('employee_name');
            $table->string('employee_id')->nullable();
            $table->string('position');
            $table->string('division_agency');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->text('special_requirements')->nullable();
            $table->timestamps();
            
            $table->index(['travel_order_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('travel_order_participants');
    }
};
