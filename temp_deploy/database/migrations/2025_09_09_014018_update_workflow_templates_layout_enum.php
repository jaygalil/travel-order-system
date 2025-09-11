<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateWorkflowTemplatesLayoutEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE workflow_templates MODIFY COLUMN layout ENUM('vertical', 'horizontal', 'combo') DEFAULT 'vertical'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE workflow_templates MODIFY COLUMN layout ENUM('vertical', 'horizontal') DEFAULT 'vertical'");
    }
}
