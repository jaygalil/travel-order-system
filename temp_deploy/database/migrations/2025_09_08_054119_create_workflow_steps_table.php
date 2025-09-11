<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_id')->constrained()->onDelete('cascade');
            $table->integer('sequence');
            $table->string('step_name');
            $table->text('step_description')->nullable();
            $table->enum('approver_type', ['user', 'role', 'position', 'department'])->default('user');
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('approver_role')->nullable(); // Role name from Spatie
            $table->string('approver_position')->nullable(); // Job position
            $table->string('approver_department')->nullable(); // Department
            $table->string('approver_name'); // Display name for approval
            $table->string('approver_title'); // Title shown in approvals
            $table->enum('action_type', ['approve', 'review', 'acknowledge', 'verify'])->default('approve');
            $table->boolean('is_required')->default(true);
            $table->boolean('can_delegate')->default(false);
            $table->json('conditions')->nullable(); // Conditional logic
            $table->timestamps();
            
            $table->index(['workflow_template_id', 'sequence']);
            $table->index(['approver_type', 'approver_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_steps');
    }
}
