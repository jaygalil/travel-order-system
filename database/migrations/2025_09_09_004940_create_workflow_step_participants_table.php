<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowStepParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_step_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_step_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('participant_name'); // Display name
            $table->string('participant_title'); // Job title
            $table->string('participant_role')->nullable(); // Role in this approval
            $table->boolean('is_primary')->default(false); // Primary approver in this step
            $table->boolean('can_delegate')->default(false);
            $table->integer('weight')->default(1); // For weighted voting systems
            $table->json('conditions')->nullable(); // Individual conditions
            $table->timestamps();
            
            // Unique constraint - one user per step
            $table->unique(['workflow_step_id', 'user_id']);
            
            // Indexes for performance
            $table->index(['workflow_step_id', 'is_primary']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_step_participants');
    }
}
