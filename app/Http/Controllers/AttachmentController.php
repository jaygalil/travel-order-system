<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelOrder;
use App\Models\TravelOrderAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttachmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Upload attachment to travel order
     */
    public function upload(Request $request, TravelOrder $travelOrder)
    {
        $this->authorize('update', $travelOrder);
        
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:' . (TravelOrderAttachment::MAX_FILE_SIZE / 1024), // Convert to KB for validation
                'mimes:' . implode(',', TravelOrderAttachment::ALLOWED_EXTENSIONS)
            ],
            'attachment_type' => [
                'required',
                Rule::in(array_keys(TravelOrderAttachment::ATTACHMENT_TYPES))
            ],
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean'
        ]);
        
        try {
            $file = $request->file('file');
            
            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $storedFilename = Str::uuid() . '.' . $extension;
            
            // Store file in travel-orders directory
            $filePath = $file->storeAs(
                'travel-orders/' . $travelOrder->id . '/attachments',
                $storedFilename,
                'private'
            );
            
            // Calculate file hash for duplicate detection
            $fileHash = md5_file($file->getPathname());
            
            // Check for duplicates
            $existingAttachment = TravelOrderAttachment::where('travel_order_id', $travelOrder->id)
                ->where('file_hash', $fileHash)
                ->first();
                
            if ($existingAttachment) {
                Storage::disk('private')->delete($filePath);
                return response()->json([
                    'error' => 'This file has already been uploaded.'
                ], 422);
            }
            
            // Create attachment record
            $attachment = TravelOrderAttachment::create([
                'travel_order_id' => $travelOrder->id,
                'uploaded_by_user_id' => Auth::id(),
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFilename,
                'file_path' => $filePath,
                'file_extension' => strtolower($extension),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'file_hash' => $fileHash,
                'attachment_type' => $request->attachment_type,
                'description' => $request->description,
                'is_public' => $request->boolean('is_public', true),
                'is_safe' => true, // Will be updated by virus scanning if implemented
                'is_scanned' => false
            ]);
            
            $attachment->load('uploadedBy');
            
            return response()->json([
                'success' => true,
                'attachment' => [
                    'id' => $attachment->id,
                    'original_filename' => $attachment->original_filename,
                    'file_size_human' => $attachment->file_size_human,
                    'attachment_type_label' => $attachment->attachment_type_label,
                    'file_icon' => $attachment->file_icon,
                    'uploaded_by' => $attachment->uploadedBy->name,
                    'created_at' => $attachment->created_at->format('M d, Y g:i A'),
                    'download_url' => $attachment->download_url,
                    'is_public' => $attachment->is_public,
                    'description' => $attachment->description
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'File upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Download attachment
     */
    public function download(TravelOrder $travelOrder, TravelOrderAttachment $attachment)
    {
        if ($attachment->travel_order_id !== $travelOrder->id) {
            abort(404);
        }
        
        if (!$attachment->canBeDownloadedBy(Auth::user())) {
            abort(403, 'You do not have permission to download this file.');
        }
        
        if (!$attachment->is_safe) {
            abort(403, 'This file has been flagged as potentially unsafe and cannot be downloaded.');
        }
        
        if (!Storage::disk('private')->exists($attachment->file_path)) {
            abort(404, 'File not found.');
        }
        
        return Storage::disk('private')->download(
            $attachment->file_path,
            $attachment->original_filename
        );
    }
    
    /**
     * Delete attachment
     */
    public function destroy(TravelOrder $travelOrder, TravelOrderAttachment $attachment)
    {
        if ($attachment->travel_order_id !== $travelOrder->id) {
            abort(404);
        }
        
        // Only the uploader or travel order owner can delete
        if ($attachment->uploaded_by_user_id !== Auth::id() && 
            $travelOrder->user_id !== Auth::id() && 
            $travelOrder->prepared_by_user_id !== Auth::id() &&
            !Auth::user()->hasRole('admin')) {
            abort(403, 'You do not have permission to delete this file.');
        }
        
        // Cannot delete attachments if travel order is submitted (unless admin)
        if ($travelOrder->status !== 'draft' && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'error' => 'Cannot delete attachments after travel order submission.'
            ], 422);
        }
        
        $attachment->delete(); // This will also delete the physical file via model boot method
        
        return response()->json([
            'success' => true,
            'message' => 'Attachment deleted successfully.'
        ]);
    }
    
    /**
     * List attachments for travel order
     */
    public function index(TravelOrder $travelOrder)
    {
        $this->authorize('view', $travelOrder);
        
        $user = Auth::user();
        $query = $travelOrder->attachments()->with('uploadedBy');
        
        // Filter based on user permissions
        if (!$user->hasRole('admin') && 
            $travelOrder->user_id !== $user->id && 
            $travelOrder->prepared_by_user_id !== $user->id) {
            // Non-owners can only see public attachments
            $query->where('is_public', true);
        }
        
        $attachments = $query->get()->map(function($attachment) {
            return [
                'id' => $attachment->id,
                'original_filename' => $attachment->original_filename,
                'file_size_human' => $attachment->file_size_human,
                'attachment_type_label' => $attachment->attachment_type_label,
                'file_icon' => $attachment->file_icon,
                'uploaded_by' => $attachment->uploadedBy->name,
                'created_at' => $attachment->created_at->format('M d, Y g:i A'),
                'download_url' => $attachment->download_url,
                'is_public' => $attachment->is_public,
                'is_safe' => $attachment->is_safe,
                'description' => $attachment->description,
                'can_delete' => $attachment->uploaded_by_user_id === Auth::id() || 
                               $travelOrder->user_id === Auth::id() || 
                               $travelOrder->prepared_by_user_id === Auth::id() ||
                               Auth::user()->hasRole('admin')
            ];
        });
        
        return response()->json($attachments);
    }
    
    /**
     * Update attachment details
     */
    public function update(Request $request, TravelOrder $travelOrder, TravelOrderAttachment $attachment)
    {
        if ($attachment->travel_order_id !== $travelOrder->id) {
            abort(404);
        }
        
        // Only the uploader or travel order owner can update
        if ($attachment->uploaded_by_user_id !== Auth::id() && 
            $travelOrder->user_id !== Auth::id() && 
            $travelOrder->prepared_by_user_id !== Auth::id() &&
            !Auth::user()->hasRole('admin')) {
            abort(403);
        }
        
        $request->validate([
            'attachment_type' => [
                'required',
                Rule::in(array_keys(TravelOrderAttachment::ATTACHMENT_TYPES))
            ],
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean'
        ]);
        
        $attachment->update([
            'attachment_type' => $request->attachment_type,
            'description' => $request->description,
            'is_public' => $request->boolean('is_public')
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Attachment updated successfully.'
        ]);
    }
}
