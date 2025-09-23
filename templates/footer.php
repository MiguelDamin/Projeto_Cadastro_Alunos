</div> </div> <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;

            // --- 1. LÓGICA PARA EXPANDIR SIDEBAR COM HOVER ---
            if (sidebar) {
                sidebar.addEventListener('mouseover', function() {
                    body.classList.add('sidebar-expanded');
                    body.classList.remove('sidebar-collapsed');
                });

                sidebar.addEventListener('mouseout', function() {
                    body.classList.add('sidebar-collapsed');
                    body.classList.remove('sidebar-expanded');
                });
            }

            // --- 2. LÓGICA PARA O MENU ACCORDION ---
            const submenuToggles = document.querySelectorAll('.submenu-toggle');

            submenuToggles.forEach(function(toggle) {
                toggle.addEventListener('click', function(event) {
                    event.preventDefault(); // Impede que o link '#' navegue
                    
                    // Pega o elemento pai 'li'
                    const parentLi = this.parentElement;
                    
                    // Alterna a classe 'is-open' no pai
                    parentLi.classList.toggle('is-open');
                });
            });
        });
    </script>
</body>
</html>