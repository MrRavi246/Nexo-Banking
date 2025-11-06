        <!-- page content ends here -->
    </main>
</div>

<!-- Quick Action Modal (shared) -->
<div id="quickActionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Quick Actions</h3>
            <button class="modal-close" onclick="closeQuickActionModal()"><i class="ri-close-line"></i></button>
        </div>
        <div class="modal-body">
            <div class="quick-action-grid">
                <button class="quick-action-btn" onclick="openUserModal()"><i class="ri-user-add-line"></i><span>Add New User</span></button>
                <button class="quick-action-btn" onclick="sendSystemAlert()"><i class="ri-notification-2-line"></i><span>Send System Alert</span></button>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/admin.js"></script>
<script>
    // Basic dropdown script shared across admin pages
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownButtons = document.querySelectorAll('[aria-expanded]');
        dropdownButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = button.nextElementSibling;
                if (dropdown) dropdown.classList.toggle('open');
            });
        });
        document.addEventListener('click', function() {
            document.querySelectorAll('.notification-dropdown, .nav-dropdown').forEach(d => d.classList.remove('open'));
        });
    });
</script>
</body>
</html>
