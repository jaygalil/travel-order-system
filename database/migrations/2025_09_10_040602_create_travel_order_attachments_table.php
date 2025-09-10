<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelOrderAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travel_order_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by_user_id')->constrained('users')->onDelete('cascade');
            
            // File information
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path');
            $table->string('file_extension');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->string('file_hash')->nullable(); // for duplicate detection
            
            // Categorization
            $table->enum('attachment_type', [
                'supporting_document',
                'authorization_letter', 
                'itinerary',
                'budget_estimate',
                'identification',
                'medical_certificate',
                'other'
            ])->default('supporting_document');
            $table->text('description')->nullable();
            
            // Security and validation
            $table->boolean('is_scanned')->default(false);
            $table->boolean('is_safe')->default(true);
            $table->text('scan_results')->nullable();
            
            // Access control
            $table->boolean('is_public')->default(false); // visible to all approvers
            $table->boolean('is_required')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['travel_order_id', 'attachment_type']);
            $table->index(['uploaded_by_user_id']);
            $table->index(['file_hash']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('travel_order_attachments');
    }
}
