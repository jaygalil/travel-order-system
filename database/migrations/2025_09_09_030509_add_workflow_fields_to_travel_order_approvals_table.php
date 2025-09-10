<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkflowFieldsToTravelOrderApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('travel_order_approvals', function (Blueprint $table) {
            // Add approval hierarchy fields
            $table->integer('approval_level')->nullable()->after('sequence');
            $table->integer('step_group')->nullable()->after('approval_level');
            $table->integer('group_order')->default(1)->after('step_group');
            
            // Add approver position field
            $table->string('approver_position')->nullable()->after('approver_title');
            
            // Add workflow behavior fields
            $table->string('action_type')->default('approve')->after('approver_user_id');
            $table->boolean('is_required')->default(true)->after('action_type');
            $table->boolean('can_delegate')->default(false)->after('is_required');
            $table->text('step_description')->nullable()->after('can_delegate');
            
            // Add advanced workflow fields
            $table->string('step_type')->default('sequential')->after('step_description');
            $table->string('completion_rule')->default('all')->after('step_type');
            $table->integer('required_approvals')->nullable()->after('completion_rule');
            
            // Add delegation fields
            $table->unsignedBigInteger('delegated_to_user_id')->nullable()->after('required_approvals');
            $table->timestamp('delegated_at')->nullable()->after('delegated_to_user_id');
            $table->text('delegated_reason')->nullable()->after('delegated_at');
            
            // Add foreign key for delegation
            $table->foreign('delegated_to_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('travel_order_approvals', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['delegated_to_user_id']);
            
            // Drop added columns in reverse order
            $table->dropColumn([
                'delegated_reason',
                'delegated_at',
                'delegated_to_user_id',
                'required_approvals',
                'completion_rule',
                'step_type',
                'step_description',
                'can_delegate',
                'is_required',
                'action_type',
                'approver_position',
                'group_order',
                'step_group',
                'approval_level'
            ]);
        });
    }
}
