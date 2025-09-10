<div class="card shadow-sm" id="attachments-section">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-paperclip me-2"></i>
            Attachments
        </h6>
        <small class="text-muted">Max {{ number_format(\App\Models\TravelOrderAttachment::MAX_FILE_SIZE / 1024 / 1024, 0) }}MB per file</small>
    </div>
    
    <div class="card-body">
        @if($travelOrder->status === 'draft' || $travelOrder->user_id === auth()->id() || $travelOrder->prepared_by_user_id === auth()->id() || auth()->user()->hasRole('admin'))
        <!-- File Upload Area -->
        <div class="file-upload-area mb-4">
            <div id="drop-zone" class="drop-zone border-2 border-dashed border-primary rounded p-4 text-center">
                <div class="upload-content">
                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                    <h5 class="text-primary mb-2">Drag & Drop Files Here</h5>
                    <p class="text-muted mb-3">or <button type="button" class="btn btn-link p-0" id="browse-files">browse files</button></p>
                    <small class="text-muted">
                        Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, GIF, TXT, CSV
                    </small>
                </div>
                <div class="upload-progress d-none">
                    <div class="progress mb-2">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"></div>
                    </div>
                    <small class="upload-status"></small>
                </div>
            </div>
            
            <!-- Hidden file input -->
            <input type="file" id="file-input" multiple accept="{{ implode(',', array_map(fn($ext) => '.' . $ext, \App\Models\TravelOrderAttachment::ALLOWED_EXTENSIONS)) }}" class="d-none">
            
            <!-- File Details Modal -->
            <div class="modal fade" id="fileDetailsModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">File Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="file-details-form">
                                <div class="mb-3">
                                    <label class="form-label">File Name</label>
                                    <input type="text" class="form-control" id="file-name" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="attachment_type" class="form-label">Document Type *</label>
                                    <select class="form-select" id="attachment_type" required>
                                        <option value="">Select document type</option>
                                        @foreach(\App\Models\TravelOrderAttachment::ATTACHMENT_TYPES as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" rows="3" placeholder="Optional description of the document"></textarea>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_public" checked>
                                    <label class="form-check-label" for="is_public">
                                        <i class="fas fa-eye me-1"></i>
                                        Visible to approvers
                                        <small class="text-muted d-block">Allow approvers to view and download this document</small>
                                    </label>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="upload-file-btn">
                                <i class="fas fa-upload me-1"></i>
                                Upload File
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Attachments List -->
        <div id="attachments-list">
            <div class="d-flex justify-content-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading attachments...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.drop-zone {
    transition: all 0.3s ease;
    cursor: pointer;
    min-height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.drop-zone:hover {
    background-color: rgba(13, 110, 253, 0.05);
    transform: translateY(-2px);
}

.drop-zone.drag-over {
    background-color: rgba(13, 110, 253, 0.1);
    border-color: #0d6efd !important;
    transform: scale(1.02);
}

.attachment-item {
    transition: all 0.3s ease;
    border-left: 4px solid #e9ecef;
}

.attachment-item:hover {
    background-color: #f8f9fa;
    border-left-color: #0d6efd;
    transform: translateX(5px);
}

.attachment-actions {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.attachment-item:hover .attachment-actions {
    opacity: 1;
}

.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const browseButton = document.getElementById('browse-files');
    const attachmentsList = document.getElementById('attachments-list');
    const fileDetailsModal = new bootstrap.Modal(document.getElementById('fileDetailsModal'));
    
    let currentFile = null;
    let travelOrderId = {{ $travelOrder->id }};
    
    // Load existing attachments
    loadAttachments();
    
    // Browse button click
    browseButton.addEventListener('click', () => {
        fileInput.click();
    });
    
    // Drop zone click
    dropZone.addEventListener('click', (e) => {
        if (e.target === dropZone || e.target.closest('.upload-content')) {
            fileInput.click();
        }
    });
    
    // File input change
    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });
    
    // Drag and drop handlers
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    
    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        handleFiles(e.dataTransfer.files);
    });
    
    // Handle file selection
    function handleFiles(files) {
        if (files.length === 0) return;
        
        // For now, handle one file at a time
        const file = files[0];
        
        // Validate file
        if (!validateFile(file)) return;
        
        currentFile = file;
        
        // Populate modal with file details
        document.getElementById('file-name').value = file.name;
        document.getElementById('attachment_type').value = '';
        document.getElementById('description').value = '';
        document.getElementById('is_public').checked = true;
        
        // Show modal
        fileDetailsModal.show();
    }
    
    // Validate file
    function validateFile(file) {
        const maxSize = {{ \App\Models\TravelOrderAttachment::MAX_FILE_SIZE }};
        const allowedExtensions = {!! json_encode(\App\Models\TravelOrderAttachment::ALLOWED_EXTENSIONS) !!};
        
        // Check file size
        if (file.size > maxSize) {
            showAlert('error', `File size exceeds maximum limit of ${Math.round(maxSize / 1024 / 1024)}MB`);
            return false;
        }
        
        // Check file extension
        const extension = file.name.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(extension)) {
            showAlert('error', `File type not allowed. Supported formats: ${allowedExtensions.join(', ')}`);
            return false;
        }
        
        return true;
    }
    
    // Upload file
    document.getElementById('upload-file-btn').addEventListener('click', async () => {
        if (!currentFile) return;
        
        const attachmentType = document.getElementById('attachment_type').value;
        const description = document.getElementById('description').value;
        const isPublic = document.getElementById('is_public').checked;
        
        if (!attachmentType) {
            showAlert('error', 'Please select a document type');
            return;
        }
        
        const formData = new FormData();
        formData.append('file', currentFile);
        formData.append('attachment_type', attachmentType);
        formData.append('description', description);
        formData.append('is_public', isPublic ? '1' : '0');
        
        // Show progress
        showUploadProgress(0);
        
        try {
            const response = await fetch(`/travel-orders/${travelOrderId}/attachments`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const result = await response.json();
            
            if (response.ok) {
                fileDetailsModal.hide();
                showAlert('success', 'File uploaded successfully');
                loadAttachments();
                resetUploadArea();
            } else {
                showAlert('error', result.error || 'Upload failed');
            }
        } catch (error) {
            showAlert('error', 'Upload failed: ' + error.message);
        } finally {
            hideUploadProgress();
        }
    });
    
    // Load attachments
    async function loadAttachments() {
        try {
            const response = await fetch(`/travel-orders/${travelOrderId}/attachments`);
            const attachments = await response.json();
            
            renderAttachments(attachments);
        } catch (error) {
            console.error('Failed to load attachments:', error);
            attachmentsList.innerHTML = '<div class="alert alert-warning">Failed to load attachments</div>';
        }
    }
    
    // Render attachments list
    function renderAttachments(attachments) {
        if (attachments.length === 0) {
            attachmentsList.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                    <p class="mb-0">No attachments yet</p>
                    @if($travelOrder->status === 'draft' || auth()->user()->hasRole('admin'))
                        <small>Upload supporting documents above</small>
                    @endif
                </div>
            `;
            return;
        }
        
        const html = attachments.map(attachment => `
            <div class="attachment-item border rounded p-3 mb-2">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="d-flex align-items-center">
                            <i class="${attachment.file_icon} fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-1">${attachment.original_filename}</h6>
                                <small class="text-muted">
                                    ${attachment.attachment_type_label} • ${attachment.file_size_human} • 
                                    Uploaded by ${attachment.uploaded_by} on ${attachment.created_at}
                                </small>
                                ${attachment.description ? `<div class="mt-1"><small class="text-info">${attachment.description}</small></div>` : ''}
                                <div class="mt-1">
                                    ${attachment.is_public ? '<span class="badge bg-success">Public</span>' : '<span class="badge bg-warning">Private</span>'}
                                    ${attachment.is_safe ? '<span class="badge bg-info ms-1">Safe</span>' : '<span class="badge bg-danger ms-1">Unsafe</span>'}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto attachment-actions">
                        <div class="btn-group">
                            <a href="${attachment.download_url}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-download"></i>
                            </a>
                            ${attachment.can_delete ? `
                                <button class="btn btn-outline-danger btn-sm" onclick="deleteAttachment(${attachment.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        
        attachmentsList.innerHTML = html;
    }
    
    // Delete attachment
    window.deleteAttachment = async function(attachmentId) {
        if (!confirm('Are you sure you want to delete this attachment?')) return;
        
        try {
            const response = await fetch(`/travel-orders/${travelOrderId}/attachments/${attachmentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const result = await response.json();
            
            if (response.ok) {
                showAlert('success', 'Attachment deleted successfully');
                loadAttachments();
            } else {
                showAlert('error', result.error || 'Delete failed');
            }
        } catch (error) {
            showAlert('error', 'Delete failed: ' + error.message);
        }
    }
    
    // Show upload progress
    function showUploadProgress(percent) {
        const uploadContent = dropZone.querySelector('.upload-content');
        const uploadProgress = dropZone.querySelector('.upload-progress');
        const progressBar = uploadProgress.querySelector('.progress-bar');
        
        uploadContent.classList.add('d-none');
        uploadProgress.classList.remove('d-none');
        progressBar.style.width = percent + '%';
        progressBar.textContent = Math.round(percent) + '%';
    }
    
    // Hide upload progress
    function hideUploadProgress() {
        const uploadContent = dropZone.querySelector('.upload-content');
        const uploadProgress = dropZone.querySelector('.upload-progress');
        
        uploadContent.classList.remove('d-none');
        uploadProgress.classList.add('d-none');
    }
    
    // Reset upload area
    function resetUploadArea() {
        fileInput.value = '';
        currentFile = null;
        hideUploadProgress();
    }
    
    // Show alert
    function showAlert(type, message) {
        // Create alert element
        const alertElement = document.createElement('div');
        alertElement.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        alertElement.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert at top of attachments section
        const attachmentsSection = document.getElementById('attachments-section');
        attachmentsSection.insertBefore(alertElement, attachmentsSection.firstChild);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertElement.parentNode) {
                alertElement.remove();
            }
        }, 5000);
    }
});
</script>
