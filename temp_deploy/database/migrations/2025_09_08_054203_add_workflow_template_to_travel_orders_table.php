<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkflowTemplateToTravelOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->foreignId('workflow_template_id')->nullable()->constrained()->onDelete('set null');
            $table->index('workflow_template_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->dropForeign(['workflow_template_id']);
            $table->dropColumn('workflow_template_id');
        });
    }
}
