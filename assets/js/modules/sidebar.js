document.addEventListener('DOMContentLoaded', function () {
    var btn     = document.getElementById('btn-hamburger');
    var sidebar = document.getElementById('erp-sidebar');
    var overlay = document.getElementById('sidebar-overlay');
    if (!btn || !sidebar || !overlay) return;

    function setSidebar(open) {
        sidebar.classList.toggle('open', open);
        overlay.classList.toggle('open', open);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    btn.addEventListener('click', function () { setSidebar(true); });
    overlay.addEventListener('click', function () { setSidebar(false); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') setSidebar(false);
    });
});
