<?php
function showAlert() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert-popup success" id="alertPopup">
                <div class="alert-content">
                    <i class="fas fa-check-circle"></i>
                    <span>' . htmlspecialchars($_SESSION['success']) . '</span>
                </div>
              </div>';
        unset($_SESSION['success']);
    }
    
    if (isset($_SESSION['error'])) {
        echo '<div class="alert-popup error" id="alertPopup">
                <div class="alert-content">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>' . htmlspecialchars($_SESSION['error']) . '</span>
                </div>
              </div>';
        unset($_SESSION['error']);
    }
    
    if (isset($_SESSION['warning'])) {
        echo '<div class="alert-popup warning" id="alertPopup">
                <div class="alert-content">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>' . htmlspecialchars($_SESSION['warning']) . '</span>
                </div>
              </div>';
        unset($_SESSION['warning']);
    }
    
    if (isset($_SESSION['info'])) {
        echo '<div class="alert-popup info" id="alertPopup">
                <div class="alert-content">
                    <i class="fas fa-info-circle"></i>
                    <span>' . htmlspecialchars($_SESSION['info']) . '</span>
                </div>
              </div>';
        unset($_SESSION['info']);
    }
}
?>

<style>
.alert-popup {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
    max-width: 400px;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateX(120%);
    transition: transform 0.3s ease-in-out;
    animation: slideIn 0.5s forwards;
}

.alert-popup.success {
    background: #d4edda;
    border-left: 4px solid #28a745;
    color: #155724;
}

.alert-popup.error {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
    color: #721c24;
}

.alert-popup.warning {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    color: #856404;
}

.alert-popup.info {
    background: #cce5ff;
    border-left: 4px solid #0dcaf0;
    color: #004085;
}

.alert-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-content i {
    font-size: 1.2em;
}

@keyframes slideIn {
    0% {
        transform: translateX(120%);
    }
    100% {
        transform: translateX(0);
    }
}

@keyframes slideOut {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(120%);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const alertPopup = document.getElementById('alertPopup');
    if (alertPopup) {
        // Show the alert
        alertPopup.style.display = 'block';
        
        // Hide after 5 seconds
        setTimeout(() => {
            alertPopup.style.animation = 'slideOut 0.5s forwards';
            setTimeout(() => {
                alertPopup.remove();
            }, 500);
        }, 5000);
    }
});
</script> 