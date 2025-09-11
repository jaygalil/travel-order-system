<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelOrderApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travel_order_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_order_id')->constrained()->onDelete('cascade');
            $table->integer('sequence'); // Order of approval (1=Provincial Officer, 2=HR, etc.)
            $table->string('approver_role'); // Role name (provincial_officer, human_resources, etc.)
            $table->string('approver_name');
            $table->string('approver_title'); // Position title
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'forwarded', 'endorsed', 'verified', 'approved', 'rejected'])->default('pending');
            $table->timestamp('action_date')->nullable();
            $table->text('comments')->nullable();
            $table->string('email_token')->unique()->nullable(); // For email-based approvals
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();
            
            $table->unique(['travel_order_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('travel_order_approvals');
    }
}
