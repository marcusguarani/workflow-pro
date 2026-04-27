        <footer class="footer-note mt-4">
            <div>Workflow Pro • PHP + MySQL • Bootstrap Dashboard</div>
            <div class="text-secondary">Protótipo profissional para validação visual e funcional</div>
        </footer>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const body = document.body;
    const openBtn = document.querySelector('[data-sidebar-open]');
    const closeBtns = document.querySelectorAll('[data-sidebar-close]');

    if (openBtn) {
        openBtn.addEventListener('click', function () {
            body.classList.add('sidebar-open');
        });
    }

    closeBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            body.classList.remove('sidebar-open');
        });
    });

    document.querySelectorAll('.sidebar-nav .nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 992) {
                body.classList.remove('sidebar-open');
            }
        });
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) {
            body.classList.remove('sidebar-open');
        }
    });
})();
</script>
</body>
</html>
