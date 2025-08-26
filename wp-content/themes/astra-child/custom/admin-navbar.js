// Admin Navbar interactions (vanilla JS, Bootstrap 5 compatible)
(function () {
  function moveSelector() {
    var selector = document.querySelector('.hori-selector');
    var active = document.querySelector('#navbarSupportedContent .nav-item.active') ||
                 document.querySelector('#navbarSupportedContent .nav-item .nav-link.active')?.closest('.nav-item');
    if (!selector) return;

    // If no active found, fallback to first item
    if (!active) {
      active = document.querySelector('#navbarSupportedContent .nav-item');
      if (!active) return;
    }

    // Set size and position relative to its immediate positioned ancestor
    selector.style.top = 0 + 'px';
    selector.style.left = (active.offsetLeft) + 'px';
    selector.style.height = active.offsetHeight + 'px';
    selector.style.width = active.offsetWidth + 'px';
  }

  function clearActive(nav) {
    nav.querySelectorAll('.nav-item').forEach(function (li) {
      li.classList.remove('active');
      var link = li.querySelector('.nav-link');
      if (link) link.classList.remove('active');
    });
  }

  function activateLink(linkEl) {
    if (!linkEl) return false;
    var li = linkEl.closest('li');
    if (!li) return false;
    li.classList.add('active');
    linkEl.classList.add('active');
    return true;
  }

  function setActiveFromUrl() {
    var nav = document.getElementById('navbarSupportedContent');
    if (!nav) return false;

    var hash = window.location.hash || '';
    var matched = false;

    if (hash && hash.length > 1) {
      var linkByHash = nav.querySelector('a.nav-link[href="' + hash + '"]');
      if (linkByHash) {
        clearActive(nav);
        matched = activateLink(linkByHash);
      }
    }

    // If still not matched, try by path (for non-hash links)
    if (!matched) {
      var path = window.location.pathname.split('/').filter(Boolean).pop() || 'index.php';
      var links = nav.querySelectorAll('ul li a[href]');
      links.forEach(function (a) {
        var href = a.getAttribute('href') || '';
        if (href.startsWith('#') || href === '#' || href.startsWith('javascript:')) return;
        var hrefSeg = href.split('/').filter(Boolean).pop();
        if (!matched && (hrefSeg === path || window.location.pathname.endsWith(href))) {
          clearActive(nav);
          matched = activateLink(a);
        }
      });
    }

    // If nothing matched, ensure first item is active
    if (!matched) {
      var first = nav.querySelector('.nav-item .nav-link');
      if (first) {
        clearActive(nav);
        matched = activateLink(first);
      }
    }

    return matched;
  }

  function updateSectionsFromHash(explicitHash) {
    var hash = typeof explicitHash === 'string' ? explicitHash : (window.location.hash || '');
    var id = (hash && hash.startsWith('#')) ? hash.substring(1) : '';
    var sections = document.querySelectorAll('main#admin-blank section[id]');
    if (!sections.length) return;

    var target = id ? document.getElementById(id) : null;
    var hasMatch = !!target;

    sections.forEach(function (sec) {
      if (hasMatch) {
        sec.hidden = (sec !== target);
      } else {
        // Fallback: show the first section, hide others
        sec.hidden = (sec !== sections[0]);
      }
    });

    // Optional: focus heading inside shown section for a11y
    if (hasMatch) {
      var h = target.querySelector('h1, h2, h3');
      if (h && typeof h.focus === 'function') {
        // allow painting before focusing
        setTimeout(function(){ h.setAttribute('tabindex', '-1'); h.focus(); }, 0);
      }
    }
  }

  function setActiveOnClick(e) {
    var link = e.target.closest('.nav-link');
    if (!link) return;
    var nav = document.getElementById('navbarSupportedContent');
    if (!nav) return;

    // If it's a hash link, smooth scroll and manage active state locally
    var href = link.getAttribute('href') || '';
    if (href.startsWith('#') && href.length > 1) {
      e.preventDefault();
      // Toggle sections visibility
      updateSectionsFromHash(href);
      clearActive(nav);
      activateLink(link);
      history.replaceState(null, '', href);
      moveSelector();
    }
  }

  function onReady() {
    var nav = document.getElementById('navbarSupportedContent');
    if (!nav) return;

    // Initialize active state and selector
    setActiveFromUrl();
    moveSelector();
    updateSectionsFromHash();

    // Click handlers
    nav.addEventListener('click', setActiveOnClick);

    // Recalculate on resize
    window.addEventListener('resize', function () {
      // slight delay to allow layout to settle
      setTimeout(moveSelector, 250);
    });

    // When bootstrap collapsible menu toggles (mobile), recalc
    var collapseEl = document.getElementById('navbarSupportedContent');
    if (collapseEl) {
      collapseEl.addEventListener('shown.bs.collapse', moveSelector);
      collapseEl.addEventListener('hidden.bs.collapse', moveSelector);
    }

    // Update on hash changes (e.g., back/forward navigation)
    window.addEventListener('hashchange', function () {
      setActiveFromUrl();
      updateSectionsFromHash();
      moveSelector();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', onReady);
  } else {
    onReady();
  }
})();
