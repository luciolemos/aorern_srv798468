(function () {
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof bootstrap === 'undefined') {
            return;
        }

        var toastElements = document.querySelectorAll('.toast-container .toast');
        if (!toastElements.length) {
            return;
        }

        toastElements.forEach(function (toastEl, index) {
            toastEl.style.setProperty('--toast-index', index.toString());
            var instance = bootstrap.Toast.getOrCreateInstance(toastEl);
            instance.show();
        });
    });
})();
