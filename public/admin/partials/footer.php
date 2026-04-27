            </div>
        </main>
    </div>

    <script>
        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-modal-open]');
            if (trigger) {
                var modalId = trigger.getAttribute('data-modal-open');
                var modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('is-open');
                }
                return;
            }

            var closeBtn = event.target.closest('[data-modal-close]');
            if (closeBtn) {
                var closeId = closeBtn.getAttribute('data-modal-close');
                var closeModal = document.getElementById(closeId);
                if (closeModal) {
                    closeModal.classList.remove('is-open');
                }
                return;
            }

            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('is-open');
            }
        });
    </script>
</body>
</html>
