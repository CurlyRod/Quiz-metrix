document.addEventListener('DOMContentLoaded', function() {
   
    
    // Load tasks
    loadTasks();
    
    // Add task event
    document.getElementById('addTodoBtn').addEventListener('click', function() {
        addTask();
    });
    
    // Add task on Enter key
    document.getElementById('newTodoInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            addTask();
        }
    });
    
    // Finish all tasks
    document.getElementById('finishAllBtn').addEventListener('click', function() {
        finishAllTasks();
    });
    
    function loadTasks() {
    fetch('api/todos.php?action=getTasks')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to load tasks');
            }
            
            const todoList = document.getElementById('todoList');
            todoList.innerHTML = '';
            
            if (!data.data || data.data.length === 0) {
                todoList.innerHTML = '<li class="todo-item text-muted">No tasks yet</li>';
                return;
            }
            
            data.data.forEach(task => {
                const todoItem = createTodoItem(task);
                todoList.appendChild(todoItem);
            });
        })
        .catch(error => {
            console.error('Error loading tasks:', error);
            const todoList = document.getElementById('todoList');
            todoList.innerHTML = '<li class="todo-item text-muted">Error loading tasks</li>';
        });
}
    
    // Function to create a todo item
    function createTodoItem(task) {
        const todoItem = document.createElement('li');
        todoItem.className = 'todo-item';
        todoItem.dataset.taskId = task.task_id;
        
        const isCompleted = task.is_completed == 1;
        
        todoItem.innerHTML = `
            <div class="todo-checkbox ${isCompleted ? 'checked' : ''}">
                <i class="fas ${isCompleted ? 'fa-check-circle' : 'fa-circle'}"></i>
            </div>
            <span class="todo-text ${isCompleted ? 'completed' : ''}">${task.content}</span>
            <button class="todo-delete">
                <i class="fas fa-trash"></i>
            </button>
        `;
        
        // Add event listeners
        todoItem.querySelector('.todo-checkbox').addEventListener('click', function() {
            toggleTaskStatus(task.task_id, !isCompleted);
        });
        
        todoItem.querySelector('.todo-delete').addEventListener('click', function() {
            deleteTask(task.task_id);
        });
        
        return todoItem;
    }
    
    // Function to add a task
    function addTask() {
        const newTaskInput = document.getElementById('newTodoInput');
        const taskContent = newTaskInput.value.trim();
        const user_id = document.getElementById("user-current-id").value // Assuming user_id is set in a hidden input
        
        if (!taskContent) {
            return;
        }
        
        const taskData = {
            content: taskContent,
            user_id: user_id // Include user_id in the task data
        };
        
        fetch('api/todos.php?action=addTask', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(taskData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                newTaskInput.value = '';
                loadTasks();
            } else {
                alert('Error: ' + data.message);
            }
        })
        // .catch(error => console.error('Error adding task:', error));
    }
    
    // Function to toggle task status
    function toggleTaskStatus(taskId, isCompleted) {
        const taskData = {
            task_id: taskId,
            is_completed: isCompleted ? 1 : 0
        };
        
        fetch('api/todos.php?action=updateTask', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(taskData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const todoItem = document.querySelector(`li[data-task-id="${taskId}"]`);
                const todoCheckbox = todoItem.querySelector('.todo-checkbox');
                const todoText = todoItem.querySelector('.todo-text');
                
                if (isCompleted) {
                    todoCheckbox.classList.add('checked');
                    todoCheckbox.innerHTML = '<i class="fas fa-check-circle"></i>';
                    todoText.classList.add('completed');
                } else {
                    todoCheckbox.classList.remove('checked');
                    todoCheckbox.innerHTML = '<i class="fas fa-circle"></i>';
                    todoText.classList.remove('completed');
                }
            } else {
                alert('Error: ' + data.message);
                loadTasks(); // Reload to ensure UI is in sync
            }
        })
        .catch(error => console.error('Error updating task:', error));
    }
    
    // Function to delete a task
    function deleteTask(taskId) {
        fetch(`api/todos.php?action=deleteTask&id=${taskId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove task from UI
                const todoItem = document.querySelector(`li[data-task-id="${taskId}"]`);
                todoItem.remove();
                
                // Check if list is empty
                const todoList = document.getElementById('todoList');
                if (todoList.children.length === 0) {
                    todoList.innerHTML = '<li class="todo-item text-muted">No tasks yet</li>';
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error deleting task:', error));
    }
    
    // Function to finish all completed tasks
    function finishAllTasks() {
        fetch('api/todos.php?action=deleteCompletedTasks', {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadTasks();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error deleting completed tasks:', error));
    }
});