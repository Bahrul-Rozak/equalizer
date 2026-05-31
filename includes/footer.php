<?php
// includes/footer.php
?>
    <!-- Footer -->
    <footer class="border-t border-gray-700 mt-12 py-6 text-center text-sm text-gray-400">
        <p><?= getSettings()['footer_text'] ?? '© 2026 Warrior Produktif - No Refund. No Mercy. No Bullsh*t.' ?></p>
        <p class="mt-1 text-xs">Disiplin adalah jembatan antara tujuan dan pencapaian.</p>
    </footer>
    
    <!-- Simple toast notification -->
    <div id="toast" class="fixed bottom-4 right-4 px-4 py-2 rounded shadow-lg hidden transition-all duration-300 z-50">
        <span id="toast-message"></span>
    </div>
    
    <script>
        // Simple toast function
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            const msg = document.getElementById('toast-message');
            
            msg.textContent = message;
            toast.className = `fixed bottom-4 right-4 px-4 py-2 rounded shadow-lg transition-all duration-300 z-50 ${
                type === 'success' ? 'bg-green-600' : 
                type === 'error' ? 'bg-red-600' : 'bg-gray-700'
            } text-white`;
            
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }
    </script>
</body>
</html>