        </main>
    </div>
    <script>
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-collapsed');
            document.getElementById('toggleArrow').innerHTML =
                document.body.classList.contains('sidebar-collapsed') ? '&rsaquo;' : '&lsaquo;';
        }
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeToggleText = document.getElementById('themeToggleText');
        const themeToggleIcon = document.getElementById('themeToggleIcon');
        themeToggleBtn.addEventListener('click', function () {
            const light = document.documentElement.classList.contains('dark');
            document.documentElement.classList.toggle('dark', !light);
            document.documentElement.classList.toggle('light', light);
            themeToggleText.innerHTML = light
                ? '<i class="fa-solid fa-sun mr-2"></i> Light Mode'
                : '<i class="fa-solid fa-moon mr-2"></i> Dark Mode';
            themeToggleIcon.classList.toggle('fa-toggle-on', !light);
            themeToggleIcon.classList.toggle('fa-toggle-off', light);
        });
    </script>
</body>
</html>
