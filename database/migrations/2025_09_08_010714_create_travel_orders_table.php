<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travel_orders', function (Blueprint $table) {
            $table->id();
            $table->string('local_travel_order_no')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date_of_travel_from');
            $table->date('date_of_travel_to');
            $table->foreignId('prepared_by_user_id')->constrained('users')->onDelete('cascade');
            
            // Employee details
            $table->string('employee_name');
            $table->string('position');
            $table->string('division_agency');
            
            // Travel details
            $table->string('source_of_fund');
            $table->string('official_vehicle')->nullable();
            $table->text('purpose');
            $table->text('destination');
            $table->string('farthest_destination');
            $table->decimal('approx_distance', 8, 2);
            
            // Status and tracking
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected', 'completed'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            
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
        Schema::dropIfExists('travel_orders');
    }
}
