            </div> <!-- End content wrapper -->
        </main>
    </div> <!-- End dashboard container -->
    
    <script>
        // Simple Sidebar Toggle Script
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebar-toggle');
            const sidebar = document.querySelector('.sidebar');
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                });
            }
        });
    </script>
</body>
</html>
