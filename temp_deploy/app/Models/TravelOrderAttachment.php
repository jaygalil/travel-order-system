<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TravelOrderAttachment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'travel_order_id',
        'uploaded_by_user_id',
        'original_filename',
        'stored_filename',
        'file_path',
        'file_extension',
        'mime_type',
        'file_size',
        'file_hash',
        'attachment_type',
        'description',
        'is_scanned',
        'is_safe',
        'scan_results',
        'is_public',
        'is_required'
    ];
    
    protected $casts = [
        'is_scanned' => 'boolean',
        'is_safe' => 'boolean',
        'is_public' => 'boolean',
        'is_required' => 'boolean',
        'file_size' => 'integer'
    ];
    
    // Attachment types with labels
    const ATTACHMENT_TYPES = [
        'supporting_document' => 'Supporting Document',
        'authorization_letter' => 'Authorization Letter',
        'itinerary' => 'Travel Itinerary',
        'budget_estimate' => 'Budget Estimate',
        'identification' => 'ID/Passport Copy',
        'medical_certificate' => 'Medical Certificate',
        'other' => 'Other Document'
    ];
    
    // Allowed file types
    const ALLOWED_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'jpg', 'jpeg', 'png', 'gif', 'bmp',
        'txt', 'rtf', 'csv'
    ];
    
    // Maximum file size (10MB)
    const MAX_FILE_SIZE = 10 * 1024 * 1024;
    
    /**
     * Travel order this attachment belongs to
     */
    public function travelOrder()
    {
        return $this->belongsTo(TravelOrder::class);
    }
    
    /**
     * User who uploaded this attachment
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
    
    /**
     * Get the human-readable file size
     */
    public function getFileSizeHumanAttribute()
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Get the attachment type label
     */
    public function getAttachmentTypeLabelAttribute()
    {
        return self::ATTACHMENT_TYPES[$this->attachment_type] ?? 'Unknown';
    }
    
    /**
     * Get the file icon class based on extension
     */
    public function getFileIconAttribute()
    {
        $iconMap = [
            'pdf' => 'fas fa-file-pdf text-danger',
            'doc' => 'fas fa-file-word text-primary',
            'docx' => 'fas fa-file-word text-primary',
            'xls' => 'fas fa-file-excel text-success',
            'xlsx' => 'fas fa-file-excel text-success',
            'ppt' => 'fas fa-file-powerpoint text-warning',
            'pptx' => 'fas fa-file-powerpoint text-warning',
            'jpg' => 'fas fa-file-image text-info',
            'jpeg' => 'fas fa-file-image text-info',
            'png' => 'fas fa-file-image text-info',
            'gif' => 'fas fa-file-image text-info',
            'txt' => 'fas fa-file-alt text-secondary',
            'csv' => 'fas fa-file-csv text-success',
        ];
        
        return $iconMap[$this->file_extension] ?? 'fas fa-file text-muted';
    }
    
    /**
     * Check if file is an image
     */
    public function isImage()
    {
        return in_array($this->file_extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp']);
    }
    
    /**
     * Get the full file path
     */
    public function getFullPath()
    {
        return Storage::path($this->file_path);
    }
    
    /**
     * Get download URL
     */
    public function getDownloadUrlAttribute()
    {
        return route('travel-orders.attachments.download', [$this->travel_order_id, $this->id]);
    }
    
    /**
     * Check if user can download this attachment
     */
    public function canBeDownloadedBy(User $user)
    {
        // Owner can always download
        if ($this->uploaded_by_user_id === $user->id) {
            return true;
        }
        
        // Travel order owner can download
        if ($this->travelOrder->user_id === $user->id || $this->travelOrder->prepared_by_user_id === $user->id) {
            return true;
        }
        
        // Approvers can download if public or if they're assigned to approve
        if ($this->is_public || $user->hasRole(['admin', 'approver'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete the physical file when model is deleted
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($attachment) {
            if (Storage::exists($attachment->file_path)) {
                Storage::delete($attachment->file_path);
            }
        });
    }
    
    /**
     * Scope for public attachments
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
    
    /**
     * Scope for safe attachments only
     */
    public function scopeSafe($query)
    {
        return $query->where('is_safe', true);
    }
    
    /**
     * Scope by attachment type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('attachment_type', $type);
    }
}
