// Chart data will be fetched from server
const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

// Current state
const currentDate = new Date();
let currentMonth = currentDate.getMonth();
let currentYear = currentDate.getFullYear();

// Chart instance
let chart = null;

// Fetch user statistics from server
async function fetchUserStats() {
    try {
        const response = await fetch('api/get_user_stats.php');
        
        // First, get the response as text to see what's actually coming back
        const responseText = await response.text();

        
        // Then try to parse it as JSON
        const data = JSON.parse(responseText);
        
        if (data.success) {
            const stats = data.stats;
            
            // Update the DOM with real data
            document.getElementById('quizzes-created').textContent = stats.quizzes_created;
            document.getElementById('quizzes-taken').textContent = stats.quizzes_taken;
            document.getElementById('files-uploaded').textContent = stats.files_uploaded;
            document.getElementById('notes-created').textContent = stats.notes_created;
            document.getElementById('quiz-accuracy').textContent = stats.quiz_accuracy + '%';
        } else {
            console.error('Failed to fetch stats:', data.message);
            // Fallback to showing zeros if there's an error
            setFallbackValues();
        }
    } catch (error) {
        console.error('Error fetching user stats:', error);
        console.error('Error details:', error.message);
        // Fallback to showing zeros if there's an error
        setFallbackValues();
    }
}

// Helper function to set fallback values
function setFallbackValues() {
    document.getElementById('quizzes-created').textContent = '0';
    document.getElementById('quizzes-taken').textContent = '0';
    document.getElementById('files-uploaded').textContent = '0';
    document.getElementById('notes-created').textContent = '0';
    document.getElementById('quiz-accuracy').textContent = '0%';
}

// Show loading state
function showLoadingState() {
    const elements = [
        'quizzes-created',
        'quizzes-taken',
        'files-uploaded',
        'notes-created',
        'quiz-accuracy'
    ];
    
    elements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = '...';
        }
    });
}

// Fetch chart data from server
async function fetchChartData(month, year) {
    try {
        const response = await fetch(`api/get_chart_data.php?month=${month + 1}&year=${year}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const responseText = await response.text();
        const data = JSON.parse(responseText);
        
        if (data.success) {
            return {
                weeks: data.weeks,
                accuracy: data.accuracy
            };
        } else {
            console.error('Failed to fetch chart data:', data.message);
            return null;
        }
    } catch (error) {
        console.error('Error fetching chart data:', error);
        return null;
    }
}

// Initialize chart with real data
async function initChart() {
    const ctx = document.getElementById('accuracyChart').getContext('2d');
    
    // Fetch real data for current month
    const chartData = await fetchChartData(currentMonth, currentYear);
    
    // Use real data or fallback to empty data
    const data = chartData || { 
        weeks: ['Week 1', 'Week 2', 'Week 3', 'Week 4'], 
        accuracy: [0, 0, 0, 0] 
    };
    
    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.weeks,
            datasets: [{
                label: 'Accuracy (%)',
                data: data.accuracy,
                borderColor: 'hsl(258, 90%, 42%)',
                backgroundColor: 'rgba(109, 40, 217, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: 'hsl(258, 90%, 42%)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: 'hsl(258, 90%, 42%)',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'hsl(0, 0%, 100%)',
                    titleColor: 'hsl(240, 10%, 15%)',
                    bodyColor: 'hsl(240, 10%, 15%)',
                    borderColor: 'hsl(240, 10%, 90%)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Accuracy: ' + context.parsed.y + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        stepSize: 10,
                        callback: function(value) {
                            return value + '%';
                        },
                        color: 'hsl(240, 5%, 45%)',
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'hsl(240, 10%, 90%)',
                        drawBorder: false
                    },
                    title: {
                        display: true,
                        text: 'Accuracy (%)',
                        color: 'hsl(240, 5%, 45%)',
                        font: {
                            size: 13,
                            weight: '500'
                        }
                    }
                },
                x: {
                    ticks: {
                        color: 'hsl(240, 5%, 45%)',
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'hsl(240, 10%, 90%)',
                        drawBorder: false
                    }
                }
            }
        }
    });
}

// Update chart with new data
async function updateChart() {
    const chartData = await fetchChartData(currentMonth, currentYear);
    
    if (chartData && chart) {
        chart.data.labels = chartData.weeks;
        chart.data.datasets[0].data = chartData.accuracy;
        chart.update();
    }
    
    // Update month display
    document.getElementById('current-month').textContent = `${monthNames[currentMonth]} ${currentYear}`;
}

// Navigate to previous month
async function previousMonth() {
    currentMonth--;
    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    await updateChart();
}

// Navigate to next month
async function nextMonth() {
    currentMonth++;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }
    await updateChart();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Show loading state immediately
    showLoadingState();
    
    // Fetch real user statistics
    fetchUserStats();
    
    // Initialize chart with real data
    initChart();
    
    // Add event listeners for chart navigation
    document.getElementById('prev-month').addEventListener('click', previousMonth);
    document.getElementById('next-month').addEventListener('click', nextMonth);
    
    // Update month display
    document.getElementById('current-month').textContent = `${monthNames[currentMonth]} ${currentYear}`;
});

// Utility function to format numbers with commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Function to refresh stats (can be called if you want to refresh data)
function refreshStats() {
    showLoadingState();
    fetchUserStats();
}

// Export functions for potential use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        fetchUserStats,
        refreshStats,
        formatNumber,
        previousMonth,
        nextMonth
    };
}