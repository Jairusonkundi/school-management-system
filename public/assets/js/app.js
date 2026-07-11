document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    var app = document.querySelector('.app-container');
    var search = document.getElementById('moduleSearch');
    var nav = document.getElementById('sidebarNav');
    var mobileQuery = window.matchMedia('(max-width: 768px)');

    if (toggle && sidebar && app) {
        toggle.addEventListener('click', function() {
            if (mobileQuery.matches) {
                sidebar.classList.toggle('open');
            } else {
                app.classList.toggle('sidebar-collapsed');
            }
        });

        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('open') &&
                !sidebar.contains(e.target) &&
                !toggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });

        mobileQuery.addEventListener('change', function() {
            sidebar.classList.remove('open');
            app.classList.remove('sidebar-collapsed');
        });
    }

    if (search && nav) {
        search.addEventListener('input', function() {
            var q = this.value.toLowerCase().trim();
            var links = nav.querySelectorAll('.nav-link');
            for (var i = 0; i < links.length; i++) {
                var text = links[i].textContent.toLowerCase();
                links[i].style.display = (!q || text.indexOf(q) !== -1) ? '' : 'none';
            }
        });
    }
});

document.addEventListener('change', function(event) {
    if (event.target.name === 'student_id') {
        var selected = event.target.selectedOptions[0];
        var classInput = document.querySelector('input[name="class_id"]');
        if (classInput && selected && selected.dataset.class) {
            classInput.value = selected.dataset.class;
        }
    }
});
