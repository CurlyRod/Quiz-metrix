// Disclaimer icon interaction
document.getElementById('disclaimerIcon').addEventListener('click', function(e) {
    const tooltip = document.getElementById('disclaimerTooltip');
    tooltip.classList.toggle('show');
    e.stopPropagation();
});

document.addEventListener('click', function() {
    document.getElementById('disclaimerTooltip').classList.remove('show');
});

// File upload handling
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('pdfFile');
const uploadForm = document.getElementById('uploadForm');
const fileInfo = document.getElementById('fileInfo');
let currentFileName = '';

// File size limit
const MAX_FILE_SIZE_MB = 5;
const MAX_FILE_SIZE = MAX_FILE_SIZE_MB * 1024 * 1024; // bytes

// Toast function
function showToast(message, type = 'info') {
    const toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) return;
    
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">${type === 'danger' ? 'Error' : 'Success'}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
    toast.show();
    
    // Remove from DOM after hide
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

// Check user's extraction limit
async function checkExtractionLimit() {
    try {
        const response = await fetch('api/user_extraction_limit.php?action=check_limit');
        const data = await response.json();
        
        if (data.success) {
            // Update the display with remaining extractions
            document.getElementById('count').textContent = `${data.remaining}/${data.limit}`;
            
            // Disable button if limit reached
            const processBtn = document.getElementById('processBtn');
            if (!data.allowed) {
                processBtn.disabled = true;
                processBtn.textContent = 'Limit Reached';
                processBtn.classList.add('limit-reached');
            } else {
                processBtn.disabled = false;
                processBtn.textContent = 'Extract Terms';
                processBtn.classList.remove('limit-reached');
            }
            
            return data;
        } else {
            throw new Error(data.message || 'Failed to check limit');
        }
    } catch (error) {
        console.error('Error checking extraction limit:', error);
        showToast('Error checking extraction limit', 'danger');
        return null;
    }
}

// Increment extraction count
async function incrementExtractionCount() {
    try {
        const response = await fetch('api/user_extraction_limit.php?action=increment');
        const data = await response.json();
        
        if (data.success) {
            // Update the display
            document.getElementById('count').textContent = `${data.remaining}/${data.limit}`;
            return data;
        } else {
            throw new Error(data.message || 'Failed to increment count');
        }
    } catch (error) {
        console.error('Error incrementing extraction count:', error);
        throw error;
    }
}

uploadArea.addEventListener('click', function(e) {
    if (e.target !== fileInput && !e.target.closest('.remove-file')) {
        fileInput.click();
    }
});

uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    uploadArea.classList.add('highlight');
});

uploadArea.addEventListener('dragleave', function() {
    uploadArea.classList.remove('highlight');
});

uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('highlight');
    
    const droppedFiles = Array.from(e.dataTransfer.files);
    
    if (droppedFiles.length === 0) return;
    
    // Only allow one file
    if (droppedFiles.length > 1) {
        showToast('Please upload only one PDF file at a time.', 'danger');
        return;
    }
    
    const file = droppedFiles[0];
    const ext = file.name.split('.').pop().toLowerCase();
    
    // Validate file type
    if (ext !== 'pdf') {
        showToast('❌ Please upload only PDF files.', 'danger');
        return;
    }
    
    // Validate file size
    if (file.size > MAX_FILE_SIZE) {
        showToast(`❌ File exceeds ${MAX_FILE_SIZE_MB} MB limit.`, 'danger');
        return;
    }
    
    fileInput.files = e.dataTransfer.files;
    updateFileDisplay();
});

fileInput.addEventListener('change', function(e) {
    if (e.target.files.length === 0) return;
    
    // Only allow one file
    if (e.target.files.length > 1) {
        showToast('Please upload only one PDF file at a time.', 'danger');
        fileInput.value = ''; // Clear the input
        return;
    }
    
    const file = e.target.files[0];
    const ext = file.name.split('.').pop().toLowerCase();
    
    // Validate file type
    if (ext !== 'pdf') {
        showToast('❌ Please upload only PDF files.', 'danger');
        fileInput.value = ''; // Clear the input
        return;
    }
    
    // Validate file size
    if (file.size > MAX_FILE_SIZE) {
        showToast(`❌ File exceeds ${MAX_FILE_SIZE_MB} MB limit.`, 'danger');
        fileInput.value = ''; // Clear the input
        return;
    }
    
    updateFileDisplay();
});

function updateFileDisplay() {
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        fileInfo.innerHTML = `
            <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745; position: relative;">
                <button type="button" class="remove-file btn-close" style="position: absolute; top: 10px; right: 10px;" aria-label="Remove file"></button>
                <strong>✅ ${file.name}</strong><br>
                <small>Size: ${(file.size / 1024 / 1024).toFixed(2)} MB</small>
            </div>
        `;
        
        // Add event listener to remove button
        const removeBtn = fileInfo.querySelector('.remove-file');
        removeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            removeUploadedFile();
        });
        
        showToast('PDF file uploaded successfully!', 'success');
    } else {
        fileInfo.innerHTML = '';
    }
}

function removeUploadedFile() {
    fileInput.value = ''; // Clear the file input
    fileInfo.innerHTML = '';
    currentFileName = '';
    showToast('File removed successfully.', 'info');
}

uploadForm.addEventListener('submit', function(e) {
    e.preventDefault();
    processPDF();
});

async function processPDF() {
    const processBtn = document.getElementById('processBtn');
    const error = document.getElementById('error');
    
    // First check if user has extraction limit available
    const limitInfo = await checkExtractionLimit();
    if (!limitInfo || !limitInfo.allowed) {
        showError('You have reached your extraction limit. Please try again in 7 days.');
        return;
    }
    
    if (!fileInput.files[0]) {
        showError('Please select a PDF file first');
        return;
    }
    
    // Double-check file type validation
    const file = fileInput.files[0];
    const ext = file.name.split('.').pop().toLowerCase();
    if (ext !== 'pdf') {
        showError('Please upload only PDF files.');
        return;
    }
    
    // Double-check file size validation
    if (file.size > MAX_FILE_SIZE) {
        showError(`File size must be less than ${MAX_FILE_SIZE_MB} MB.`);
        return;
    }
    
    currentFileName = fileInput.files[0].name.replace('.pdf', '');
    processBtn.disabled = true;
    processBtn.textContent = 'Processing...';
    error.style.display = 'none';
    
    // Store file info and redirect to loader immediately
    const fileData = {
        name: file.name,
        size: file.size,
        type: file.type
    };
    
    // Store file data temporarily for processing
    sessionStorage.setItem('pendingPdfFile', JSON.stringify(fileData));
    sessionStorage.setItem('pendingPdfFileName', currentFileName);
    
    // Redirect to loader.php immediately
    window.location.href = 'loader.php';
}

function showError(message) {
    const error = document.getElementById('error');
    error.innerHTML = `<strong>Error:</strong> ${message}`;
    error.style.display = 'block';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check extraction limit when page loads
    checkExtractionLimit();
});