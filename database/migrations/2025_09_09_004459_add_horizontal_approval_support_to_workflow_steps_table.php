<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHorizontalApprovalSupportToWorkflowStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            // Add fields for horizontal/parallel approval support
            $table->enum('step_type', ['sequential', 'parallel'])->default('sequential')->after('sequence');
            $table->enum('completion_rule', ['all', 'majority', 'any', 'custom'])->default('all')->after('step_type');
            $table->integer('required_approvals')->nullable()->after('completion_rule'); // For custom completion rules
            $table->boolean('allows_multiple_approvers')->default(false)->after('required_approvals');
            
            // Add index for better performance
            $table->index(['step_type', 'completion_rule']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropIndex(['step_type', 'completion_rule']);
            $table->dropColumn([
                'step_type', 
                'completion_rule', 
                'required_approvals',
                'allows_multiple_approvers'
            ]);
        });
    }
}
