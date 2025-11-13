// State management
let deletedItems = [];
let selectedTypes = [];
let dateFilter = 'all';
let pendingDelete = null;

// Helper functions
function getTypeIcon(type) {
    const icons = {
        'Files': 'bi-file-earmark',
        'Flashcards': 'bi-card-list',
        'Quiz': 'bi-ui-checks',
        'Notes': 'bi-journal-text'
    };
    return icons[type] || 'bi-file-earmark';
}

function getTypeClass(type) {
    const classes = {
        'Files': 'icon-files',
        'Flashcards': 'icon-flashcards',
        'Quiz': 'icon-quiz',
        'Notes': 'icon-notes'
    };
    return classes[type] || 'icon-files';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const daysDiff = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60 * 24));
    
    if (daysDiff === 0) return "Today";
    if (daysDiff === 1) return "Yesterday";
    if (daysDiff < 7) return `${daysDiff} days ago`;
    return date.toLocaleDateString();
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'x-circle-fill'}"></i>
        <span>${message}</span>
    `;
    
    document.getElementById('toastContainer').appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Modal functions
function showDeleteModal(item) {
    const modal = document.getElementById('deleteModal');
    const modalTitle = document.getElementById('deleteModalTitle');
    const modalBody = document.getElementById('deleteModalBody');
    
    modalTitle.textContent = `Permanently Delete ${item.type}`;
    modalBody.textContent = `Are you sure you want to permanently delete "${item.name}"? This action cannot be undone.`;
    
    modal.classList.add('show');
    pendingDelete = item;
}

function hideDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('show');
    pendingDelete = null;
}

// API functions
async function fetchDeletedItems() {
    try {
        showLoading(true);
        
        // Fetch all deleted items (quizzes, files, and notes) from single endpoint
        const response = await fetch('api/get_deleted.php');
        const data = await response.json();
        
        let items = [];
        
        // Process quizzes
        if (data.success && data.quizzes) {
            items = items.concat(data.quizzes.map(quiz => ({
                id: `quiz_${quiz.quiz_id}`,
                name: quiz.title,
                type: 'Quiz',
                deletedDate: quiz.updated_at,
                originalId: quiz.quiz_id,
                itemType: 'quiz'
            })));
        }
        
        // Process files
        if (data.success && data.files) {
            items = items.concat(data.files.map(file => ({
                id: `file_${file.id}`,
                name: file.name,
                type: 'Files',
                deletedDate: file.deleted_at,
                size: file.size ? `${Math.round(file.size / 1024)} KB` : 'Unknown size',
                originalId: file.id,
                itemType: 'file'
            })));
        }
        
        // Process notes
        if (data.success && data.notes) {
            items = items.concat(data.notes.map(note => ({
                id: `note_${note.id}`,
                name: note.title,
                type: 'Notes',
                deletedDate: note.updated_at || note.created_at, 
                originalId: note.id,
                itemType: 'note'
            })));
        }

        
        // Process flashcards
        if (data.success && data.flashcards) {
            items = items.concat(data.flashcards.map(flashcard => ({
                id: `flashcard_${flashcard.deck_id}`,  
                name: flashcard.title,
                type: 'Flashcards',
                deletedDate: flashcard.updated_at || flashcard.created_at,
                originalId: flashcard.deck_id,  
                itemType: 'flashcard'
            })));
        }
        
        return items;
    } catch (error) {
        console.error('Error fetching deleted items:', error);
        showToast('Error loading deleted items', 'error');
        return [];
    } finally {
        showLoading(false);
    }
}

function showLoading(show) {
    const spinner = document.getElementById('loadingSpinner');
    const container = document.getElementById('itemsGrid');
    
    if (show) {
        spinner.style.display = 'block';
        container.innerHTML = '';
    } else {
        spinner.style.display = 'none';
    }
}

// Filter logic
function filterItems() {
    let filtered = deletedItems;

    // Filter by study tool type
    if (selectedTypes.length > 0) {
        filtered = filtered.filter(item => selectedTypes.includes(item.type));
    }

    // Filter by date
    const now = new Date();
    if (dateFilter !== 'all') {
        filtered = filtered.filter(item => {
            const itemDate = new Date(item.deletedDate);
            const daysDiff = Math.floor((now.getTime() - itemDate.getTime()) / (1000 * 60 * 60 * 24));
            
            switch (dateFilter) {
                case 'today': return daysDiff === 0;
                case 'week': return daysDiff <= 7;
                case 'month': return daysDiff <= 30;
                default: return true;
            }
        });
    }

    return filtered;
}

// Render functions
function renderItems() {
    const filtered = filterItems();
    const container = document.getElementById('itemsGrid');
    const countElement = document.getElementById('itemsCount');
    
    countElement.textContent = `Showing ${filtered.length} of ${deletedItems.length} deleted items`;

    if (filtered.length === 0) {
        container.innerHTML = `
            <div class="col-12">
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-trash3"></i>
                    </div>
                    <h3 class="mb-2">No items found</h3>
                    <p class="text-muted">
                        ${deletedItems.length === 0 ? 'Your recycle bin is empty' : 'Try adjusting your filters'}
                    </p>
                </div>
            </div>
        `;
        return;
    }

    container.innerHTML = filtered.map(item => `
        <div class="col-md-6 col-lg-4">
            <div class="item-card">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="item-icon ${getTypeClass(item.type)}">
                        <i class="bi ${getTypeIcon(item.type)}"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div class="item-title text-truncate">${item.name}</div>
                        <div class="item-meta">
                            ${formatDate(item.deletedDate)}
                            ${item.size ? ` • ${item.size}` : ''}
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn-preview flex-fill" onclick="showPreviewModal(${JSON.stringify(item).replace(/"/g, '&quot;')})">
                        <i class="bi bi-eye"></i> Preview
                    </button>
                    <button class="btn-restore flex-fill" onclick="handleRestore('${item.id}', '${item.name.replace(/'/g, "\\'")}', '${item.itemType}', ${item.originalId})">
                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                    </button>
                    <button class="btn-delete flex-fill" onclick="handleDeleteClick('${item.id}', '${item.name.replace(/'/g, "\\'")}', '${item.itemType}', ${item.originalId})">
                        <i class="bi bi-trash3"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// Action handlers
async function handleRestore(id, name, itemType, originalId) {
    try {
        const endpoint = `api/restore_deleted.php?id=${originalId}&type=${itemType}`;
        
        const response = await fetch(endpoint);
        const result = await response.json();
        
        if (result.success) {
            // Remove from local state
            deletedItems = deletedItems.filter(item => item.id !== id);
            renderItems();
            showToast(`"${name}" has been restored`, 'success');
        } else {
            throw new Error(result.message || 'Restoration failed');
        }
    } catch (error) {
        console.error('Error restoring item:', error);
        showToast(`Error restoring "${name}"`, 'error');
    }
}

function handleDeleteClick(id, name, itemType, originalId) {
    const item = {
        id: id,
        name: name,
        type: itemType,
        originalId: originalId
    };
    showDeleteModal(item);
}

async function handleDelete() {
    if (!pendingDelete) return;
    
    const { id, name, type, originalId } = pendingDelete;
    
    try {
        const endpoint = `api/permanent_delete.php?id=${originalId}&type=${type}`;
        
        const response = await fetch(endpoint);
        const result = await response.json();
        
        if (result.success) {
            // Remove from local state
            deletedItems = deletedItems.filter(item => item.id !== id);
            renderItems();
            showToast(`"${name}" has been permanently deleted`, 'error');
        } else {
            throw new Error(result.message || 'Deletion failed'); 
        }
    } catch (error) {
        console.error('Error deleting item:', error);
        showToast(`Error deleting "${name}"`, 'error');
    } finally {
        hideDeleteModal();
    }
}

// Preview functions
function showPreviewModal(item) {
    const modal = document.getElementById('previewModal');
    const modalTitle = document.getElementById('previewModalTitle');
    const previewContent = document.getElementById('previewContent');
    
    modalTitle.textContent = `Preview ${item.type}: ${item.name}`;
    previewContent.innerHTML = `
        <div class="preview-loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading preview...</p>
        </div>
    `;
    
    modal.classList.add('show');
    
    // Load preview content based on item type
    loadPreviewContent(item);
}

function hidePreviewModal() {
    const modal = document.getElementById('previewModal');
    modal.classList.remove('show');
}

async function loadFilePreview(fileId, previewContent) {
    try {
        const response = await fetch(`api/get_deleted_file.php?id=${fileId}`);
        const data = await response.json();
        
        
        if (data.success && data.file) {
            const file = data.file;
            
            const fileType = file.type || getFileExtension(file.name);
            const fileSize = file.size ? formatFileSize(file.size) : 'Unknown size';
            const uploadDate = file.upload_date || file.deleted_at;
            
            let html = `
                <div class="preview-file-info">
                    <div class="preview-file-header">
                        <div class="preview-file-icon ${getFileIconClass(fileType)}">
                            <i class="${getFileIcon(fileType)}"></i>
                        </div>
                        <div class="preview-file-details">
                            <h4 class="preview-file-name">${file.name || 'Untitled File'}</h4>
                            <div class="preview-file-meta">
                                <div><strong>Type:</strong> ${fileType.toUpperCase()}</div>
                                <div><strong>Size:</strong> ${fileSize}</div>
                                <div><strong>Uploaded:</strong> ${new Date(uploadDate).toLocaleDateString()}</div>
                                <div><strong>Deleted:</strong> ${formatDate(file.deleted_at)}</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add content preview based on file type
            html += `<div class="preview-file-content">`;
            
            if (fileType === 'txt') {
                html += await loadTextFilePreview(fileId);
            } else if (fileType === 'pdf') {
                html += `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <p><strong>PDF File</strong></p>
                        <p>This is a PDF document. You can restore it to access the content.</p>
                        <p class="text-muted small">File: ${file.name}</p>
                    </div>
                `;
            } else if (fileType === 'docx') {
                html += `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <p><strong>Word Document</strong></p>
                        <p>This is a Microsoft Word document. You can restore it to access the content.</p>
                        <p class="text-muted small">File: ${file.name}</p>
                    </div>
                `;
            } else {
                html += `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <p><strong>${fileType.toUpperCase()} File</strong></p>
                        <p>This file can be restored to access its content.</p>
                        <p class="text-muted small">File: ${file.name}</p>
                    </div>
                `;
            }
            
            html += `</div>`;
            previewContent.innerHTML = html;
        } else {
            console.error("Failed to load file:", data.message);
            throw new Error(data.message || 'Failed to load file');
        }
    } catch (error) {
        console.error("Error loading file preview:", error);
        throw new Error(`Could not load file preview: ${error.message}`);
    }
}

async function loadQuizPreview(quizId, previewContent) {
    try {
        const response = await fetch(`api/get_deleted_quiz.php?id=${quizId}`);
        const data = await response.json();
        
        if (data.success && data.quiz) {
            const quiz = data.quiz;
            let html = `
                <div class="preview-quiz">
                    <h4 class="preview-quiz-title">${quiz.title || 'Untitled Quiz'}</h4>
                    <p class="preview-quiz-description">${quiz.description || 'No description'}</p>
                    <div class="preview-quiz-meta mb-3">
                        <small class="text-muted">
                            ${quiz.questions ? quiz.questions.length : 0} questions • 
                            Created: ${new Date(quiz.created_at).toLocaleDateString()}
                        </small>
                    </div>
                    <hr>
            `;
            
            if (quiz.questions && quiz.questions.length > 0) {
                quiz.questions.forEach((question, index) => {
                    html += `
                        <div class="preview-question">
                            <div class="preview-question-header">
                                <span class="preview-question-number">Question ${index + 1}</span>
                            </div>
                            <div class="preview-question-text">${question.description || 'No question provided'}</div>
                            <div class="preview-answer-text"><strong>Answer:</strong> ${question.term || 'No answer provided'}</div>
                        </div>
                    `;
                });
            } else {
                html += `
                    <div class="preview-empty-state">
                        <i class="bi bi-question-circle" style="font-size: 2rem;"></i>
                        <p class="mt-2">No questions found in this quiz</p>
                    </div>
                `;
            }
            
            html += `</div>`;
            previewContent.innerHTML = html;
        } else {
            throw new Error(data.message || 'Failed to load quiz');
        }
    } catch (error) {
        throw new Error(`Could not load quiz preview: ${error.message}`);
    }
}


async function loadFlashcardPreview(deckId, previewContent) {
    try {
        const response = await fetch(`api/get_deleted_deck.php?id=${deckId}`);
        const data = await response.json();
        
        
        if (data.success && data.deck) {
            const deck = data.deck;
            
            
            let html = `
                <div class="preview-flashcard-deck">
                    <h4 class="preview-quiz-title">${deck.title || 'Untitled Deck'}</h4>
                    <p class="preview-quiz-description">${deck.description || 'No description'}</p>
                    <div class="preview-quiz-meta mb-3">
                        <small class="text-muted">
                            ${deck.flashcards ? deck.flashcards.length : 0} flashcards • 
                            Created: ${new Date(deck.created_at).toLocaleDateString()}
                        </small>
                    </div>
                    <hr>
            `;
            
            if (deck.flashcards && deck.flashcards.length > 0) {
              
                
                // Show first 10 flashcards in full detail, rest in compact view
                const showFullCount = Math.min(deck.flashcards.length, 10);
                const remainingCount = deck.flashcards.length - showFullCount;
                
                // Full detail flashcards
                deck.flashcards.slice(0, showFullCount).forEach((card, index) => {
                    const frontContent = card.front ? card.front.replace(/\n/g, '<br>') : 'No content';
                    const backContent = card.back ? card.back.replace(/\n/g, '<br>') : 'No content';
                    
                    html += `
                        <div class="preview-flashcard">
                            <div class="preview-flashcard-header">
                                <span class="preview-flashcard-number">Card ${index + 1}</span>
                            </div>
                            <div class="preview-flashcard-content">
                                <div class="preview-flashcard-side front">
                                    <span class="preview-flashcard-label">Front</span>
                                    <div class="preview-flashcard-text">${frontContent}</div>
                                </div>
                                <div class="preview-flashcard-side back">
                                    <span class="preview-flashcard-label">Back</span>
                                    <div class="preview-flashcard-text">${backContent}</div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                // Compact view for remaining flashcards
                if (remainingCount > 0) {
                    html += `<div class="mt-3"><h6>+ ${remainingCount} more flashcards:</h6></div>`;
                    deck.flashcards.slice(showFullCount).forEach((card, index) => {
                        const cardNumber = showFullCount + index + 1;
                        const frontPreview = card.front ? (card.front.length > 50 ? card.front.substring(0, 50) + '...' : card.front) : 'No content';
                        const backPreview = card.back ? (card.back.length > 50 ? card.back.substring(0, 50) + '...' : card.back) : 'No content';
                        
                        html += `
                            <div class="preview-flashcard preview-flashcard-compact">
                                <div class="preview-flashcard-compact-side">
                                    <span class="preview-flashcard-label">Front</span>
                                    <div class="preview-flashcard-text">${frontPreview}</div>
                                </div>
                                <div class="preview-flashcard-compact-side">
                                    <span class="preview-flashcard-label">Back</span>
                                    <div class="preview-flashcard-text">${backPreview}</div>
                                </div>
                            </div>
                        `;
                    });
                }
            } else {
               
                html += `
                    <div class="preview-empty-state">
                        <i class="bi bi-card-text" style="font-size: 2rem;"></i>
                        <p class="mt-2">No flashcards found in this deck</p>
                    </div>
                `;
            }
            
            html += `</div>`;
            previewContent.innerHTML = html;
        } else {
            console.error("Failed to load deck:", data.message);
            throw new Error(data.message || 'Failed to load flashcard deck');
        }
    } catch (error) {
        console.error("Error loading flashcard preview:", error);
        throw new Error(`Could not load flashcard preview: ${error.message}`);
    }
}

async function loadNotePreview(noteId, previewContent) {
    try {
        const response = await fetch(`api/get_deleted_note.php?id=${noteId}`);
        const data = await response.json();
        
        
        if (data.success && data.note) {
            const note = data.note;
            
            const noteColor = note.color || 'default';
            const formattedDate = new Date(note.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            let html = `
                <div class="preview-note note-color-preview-${noteColor}">
                    <div class="preview-note-header">
                        <h4 class="preview-note-title">${note.title || 'Untitled Note'}</h4>
                        <div class="preview-note-meta">
                            <span class="preview-note-date">${formattedDate}</span>
                            <span class="preview-note-color note-color-preview-${noteColor}" title="${noteColor} color"></span>
                        </div>
                    </div>
            `;
            
            if (note.content && note.content.trim()) {
                const formattedContent = note.content.replace(/\n/g, '<br>');
                html += `
                    <div class="preview-note-content">${formattedContent}</div>
                `;
            } else {
                html += `
                    <div class="preview-note-content preview-note-content-empty">
                        No content in this note
                    </div>
                `;
            }
            
            html += `</div>`;
            previewContent.innerHTML = html;
        } else {
            console.error("Failed to load note:", data.message);
            throw new Error(data.message || 'Failed to load note');
        }
    } catch (error) {
        console.error("Error loading note preview:", error);
        throw new Error(`Could not load note preview: ${error.message}`);
    }
}

async function loadPreviewContent(item) {
    const previewContent = document.getElementById('previewContent');
    
    try {
        switch (item.itemType) {
            case 'quiz':
                await loadQuizPreview(item.originalId, previewContent);
                break;
            case 'flashcard':
                await loadFlashcardPreview(item.originalId, previewContent);
                break;
            case 'note':
                await loadNotePreview(item.originalId, previewContent);
                break;
            case 'file':
                await loadFilePreview(item.originalId, previewContent);
                break;
            default:
                previewContent.innerHTML = `
                    <div class="preview-empty-state">
                        <i class="bi bi-question-circle" style="font-size: 3rem;"></i>
                        <p class="mt-2">Preview not available for this item type</p>
                    </div>
                `;
        }
    } catch (error) {
        console.error('Error loading preview:', error);
        previewContent.innerHTML = `
            <div class="preview-empty-state">
                <i class="bi bi-exclamation-triangle" style="font-size: 3rem;"></i>
                <p class="mt-2">Error loading preview</p>
                <small class="text-muted">${error.message}</small>
            </div>
        `;
    }
}

async function loadTextFilePreview(fileId) {
    try {
        // Get file info for display
        const response = await fetch(`api/get_deleted_file.php?id=${fileId}`);
        const data = await response.json();
        
        if (data.success && data.file) {
            const file = data.file;
            const fileType = file.type || getFileExtension(file.name);
            const fileSize = file.size ? formatFileSize(file.size) : 'Unknown size';
            const uploadDate = file.upload_date || file.deleted_at;
            
            return `
                
                <div class="preview-file-content">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <p><strong>Text Document</strong></p>
                        <p>This is a text document. You can restore it to access the content.</p>
                        <p class="text-muted small">File: ${file.name}</p>
                    </div>
                </div>
            `;
        } else {
            throw new Error(data.message || 'Failed to load file information');
        }
    } catch (error) {
        console.error("Error loading text file preview:", error);
        return `
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                <p>Unable to load file information.</p>
                <p class="text-muted small">Error: ${error.message}</p>
            </div>
        `;
    }
}

// Helper functions for files
function getFileExtension(filename) {
    return filename.split('.').pop().toLowerCase();
}

function getFileIcon(fileType) {
    const icons = {
        'pdf': 'bi-file-earmark-pdf',
        'docx': 'bi-file-earmark-word',
        'txt': 'bi-file-earmark-text',
        'default': 'bi-file-earmark'
    };
    return icons[fileType] || icons['default'];
}

function getFileIconClass(fileType) {
    const classes = {
        'pdf': 'file-icon-pdf',
        'docx': 'file-icon-docx', 
        'txt': 'file-icon-txt',
        'default': 'file-icon-other'
    };
    return classes[fileType] || classes['default'];
}

function formatFileSize(bytes) {
    if (!bytes) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function openPdfInNewTab(fileId) {
    window.open(`api/preview_file.php?id=${fileId}`, '_blank');
}

// Event listeners
document.addEventListener('DOMContentLoaded', async function() {
    // Date filter buttons
    document.querySelectorAll('[data-date-filter]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('[data-date-filter]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            dateFilter = this.dataset.dateFilter;
            renderItems();
        });
    });

    // Type filter badges
    document.querySelectorAll('[data-type-filter]').forEach(badge => {
        badge.addEventListener('click', function() {
            const type = this.dataset.typeFilter;
            this.classList.toggle('active');
            
            if (selectedTypes.includes(type)) {
                selectedTypes = selectedTypes.filter(t => t !== type);
            } else {
                selectedTypes.push(type);
            }
            
            renderItems();
        });
    });

    document.querySelectorAll('#previewModal [data-dismiss="modal"]').forEach(btn => {
    btn.addEventListener('click', hidePreviewModal);
});

document.querySelector('#previewModal .modal-overlay').addEventListener('click', hidePreviewModal);

// Close preview modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hidePreviewModal();
        hideDeleteModal();
    }
});

    // Modal event listeners
    document.querySelectorAll('[data-dismiss="modal"]').forEach(btn => {
        btn.addEventListener('click', hideDeleteModal);
    });

    document.querySelector('.modal-overlay').addEventListener('click', hideDeleteModal);

    document.getElementById('confirmDeleteBtn').addEventListener('click', handleDelete);

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideDeleteModal();
        }
    });

    // Load initial data
    deletedItems = await fetchDeletedItems();
    renderItems();
});