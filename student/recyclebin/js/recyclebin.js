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