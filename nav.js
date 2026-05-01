(function () {
    var btn = document.querySelector('.nav-toggle');
    var nav = document.getElementById('primary-nav');
    if (!btn || !nav) return;
    btn.addEventListener('click', function () {
        var open = nav.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        btn.setAttribute('aria-label', open ? 'Menü schliessen' : 'Menü öffnen');
    });
})();
