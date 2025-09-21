</div> 

<script>
    // Script para controlar a expansão da sidebar
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.sidebar');
        const body = document.querySelector('body');

        if (sidebar && body) {
            sidebar.addEventListener('mouseenter', () => {
                body.classList.add('sidebar-expanded');
            });

            sidebar.addEventListener('mouseleave', () => {
                body.classList.remove('sidebar-expanded');
            });
        }
    });
</script>
</body>
</html>