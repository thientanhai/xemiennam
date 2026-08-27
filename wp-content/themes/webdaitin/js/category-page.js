document.addEventListener('DOMContentLoaded', function () {
    var archiveRoot = document.querySelector('[data-category-archive]');

    if (!archiveRoot) {
        return;
    }

    var tabs = document.querySelector('.webdaitin-category-tabs');
    var activeTab = tabs ? tabs.querySelector('.webdaitin-category-tab.is-active') : null;

    if (tabs && activeTab) {
        activeTab.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'center'
        });
    }

    var revealItems = archiveRoot.querySelectorAll('.webdaitin-card');

    if (!('IntersectionObserver' in window)) {
        revealItems.forEach(function (item) {
            item.classList.add('is-visible');
        });
        return;
    }

    var observer = new IntersectionObserver(function (entries, observerInstance) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observerInstance.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.12,
        rootMargin: '0px 0px -40px 0px'
    });

    revealItems.forEach(function (item) {
        observer.observe(item);
    });
});
