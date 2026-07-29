/**
 * Barba.js page transitions (fade via GSAP).
 *
 * Starter default (site-wide): vendored like theme CSS, enqueued from
 * `inc/enqueue.php` — not a per-page opt-in.
 *
 * - Swaps [data-barba="container"] only (header/footer stay mounted).
 * - Loads missing theme scripts found in the next document.
 * - Dispatches `starter:pageview` so feature scripts can re-init.
 *
 * Note: Barba keeps the current container in the DOM until after `enter`.
 * After fade-out we pull it out of document flow so the next page isn't
 * stacked below an invisible full-height block (which looks like a pop-in).
 *
 * After Paso 0 rename: change themeScriptMarker, stylesheet selector, and
 * CustomEvent name to `{slug}:pageview`.
 */
(function () {
  if (typeof barba === "undefined") {
    return;
  }

  var reduceMotion =
    typeof window.matchMedia === "function" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  var themeScriptMarker = "/themes/starter/assets/js/";

  function toDocument(html) {
    if (!html) return null;
    if (typeof html === "object" && html.nodeType === 9) {
      return html;
    }
    if (typeof html === "string") {
      return new DOMParser().parseFromString(html, "text/html");
    }
    return null;
  }

  function closeMobileMenuIfOpen() {
    var closeBtn = document.querySelector("[data-mobile-menu-close]");
    var menuEl = document.getElementById("mobile-menu");
    if (!closeBtn || !menuEl) return;
    if (menuEl.getAttribute("aria-hidden") === "false") {
      closeBtn.click();
    }
  }

  function syncBodyClass(doc) {
    if (!doc || !doc.body) return;
    document.body.className = doc.body.className;
  }

  function syncDocumentTitle(doc) {
    if (!doc) return;
    var titleEl = doc.querySelector("title");
    if (titleEl && titleEl.textContent) {
      document.title = titleEl.textContent;
    }
  }

  function loadScript(src) {
    return new Promise(function (resolve, reject) {
      if (document.querySelector('script[src="' + src + '"]')) {
        resolve();
        return;
      }
      var el = document.createElement("script");
      el.src = src;
      el.async = false;
      el.onload = function () {
        resolve();
      };
      el.onerror = reject;
      document.body.appendChild(el);
    });
  }

  function loadStylesheet(href) {
    if (document.querySelector('link[href="' + href + '"]')) {
      return;
    }
    var link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = href;
    document.head.appendChild(link);
  }

  function loadAssetsFromDoc(doc) {
    if (!doc) return Promise.resolve();

    doc
      .querySelectorAll('link[rel="stylesheet"][href*="/themes/starter/"]')
      .forEach(function (link) {
        var href = link.getAttribute("href");
        if (href) loadStylesheet(href);
      });

    var scripts = Array.prototype.slice.call(
      doc.querySelectorAll('script[src*="' + themeScriptMarker + '"]')
    );

    var chain = Promise.resolve();
    scripts.forEach(function (script) {
      var src = script.getAttribute("src");
      if (!src) return;
      if (/\/(barba\.umd|page-transitions|gsap\.min|ScrollTrigger\.min)\.js/.test(src)) {
        return;
      }
      chain = chain.then(function () {
        return loadScript(src);
      });
    });

    return chain;
  }

  function dispatchPageview(namespace) {
    document.dispatchEvent(
      new CustomEvent("starter:pageview", {
        bubbles: true,
        detail: { namespace: namespace || "" },
      })
    );
  }

  function hideContainer(container) {
    if (!container) return;
    container.style.opacity = "0";
    container.style.visibility = "hidden";
    if (!reduceMotion && typeof gsap !== "undefined") {
      gsap.set(container, { autoAlpha: 0 });
    }
  }

  function collapseContainer(container) {
    if (!container) return;
    container.style.display = "none";
    container.style.pointerEvents = "none";
  }

  function fadeOut(container) {
    if (!container) return Promise.resolve();
    if (reduceMotion || typeof gsap === "undefined") {
      container.style.opacity = "0";
      container.style.visibility = "hidden";
      return Promise.resolve();
    }
    return gsap.to(container, {
      autoAlpha: 0,
      duration: 0.28,
      ease: "power2.out",
    });
  }

  function fadeIn(container) {
    if (!container) return Promise.resolve();
    if (reduceMotion || typeof gsap === "undefined") {
      container.style.opacity = "";
      container.style.visibility = "";
      return Promise.resolve();
    }
    return gsap.fromTo(
      container,
      { autoAlpha: 0 },
      {
        autoAlpha: 1,
        duration: 0.45,
        ease: "power2.out",
        clearProps: "opacity,visibility",
        onComplete: function () {
          container.style.opacity = "";
          container.style.visibility = "";
        },
      }
    );
  }

  barba.init({
    preventRunning: true,
    transitions: [
      {
        name: "fade",
        async leave(data) {
          closeMobileMenuIfOpen();
          await fadeOut(data.current.container);
          collapseContainer(data.current.container);
        },
        async beforeEnter(data) {
          hideContainer(data.next.container);
          window.scrollTo(0, 0);
          var nextDoc = toDocument(data.next.html);
          syncDocumentTitle(nextDoc);
          syncBodyClass(nextDoc);
          await loadAssetsFromDoc(nextDoc);
        },
        async enter(data) {
          await new Promise(function (resolve) {
            requestAnimationFrame(function () {
              requestAnimationFrame(resolve);
            });
          });
          await fadeIn(data.next.container);
          var ns =
            (data.next.container &&
              data.next.container.getAttribute("data-barba-namespace")) ||
            "";
          dispatchPageview(ns);
        },
        async once() {
          // First paint: page scripts boot themselves; avoid double-init.
        },
      },
    ],
    prevent: function (_ref) {
      var el = _ref.el;
      if (!el) return false;
      if (el.closest("#wpadminbar")) return true;
      if (el.hasAttribute("download")) return true;
      if (el.target && el.target !== "" && el.target !== "_self") return true;
      if (el.href && el.getAttribute("href") && el.getAttribute("href").indexOf("#") === 0) {
        return true;
      }
      var href = el.href || "";
      if (/\/wp-admin|\/wp-login|wp-json|\/feed/.test(href)) return true;
      return false;
    },
  });
})();
