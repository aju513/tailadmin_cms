document.addEventListener("DOMContentLoaded", () => {
  // Reveal content without changing layout or overriding hover transforms.
  const motionPreference = window.matchMedia("(prefers-reduced-motion: reduce)");
  if ("IntersectionObserver" in window && Element.prototype.animate && !motionPreference.matches) {
    const cardSelector = [
      ".training-list__item", ".resource-card", ".team-card",
      ".capacity-report__card", ".whyus__item", ".service__item",
      ".package-list__item", ".homepage__news-card", ".homepage__video-card",
      ".homepage__moments-item", ".homepage__news-grid > *",
    ].join(", ");
    const selector = [
      cardSelector, "h1", "h2", ".section-title", ".section-title-sm",
      ".page-title", ".homepage__whyus-heading", ".welcome-content > p",
      ".homepage__about-media", ".homepage__hall-booking-image",
      ".homepage__hall-booking-copy > p", ".capacity-report__intro > p",
      ".footer__links", ".footer__links-contact", "[data-reveal]",
    ].join(", ");
    const animations = new Set();
    const delays = new WeakMap();
    const targets = [...document.querySelectorAll(selector)].filter(element => {
      if (element.closest(".header, .mob-nav, dialog, .swiper-slide-duplicate")) return false;
      // Cards reveal as a unit; don't animate their headings a second time.
      return !element.parentElement?.closest(cardSelector);
    });
    const groups = new Map();
    targets.forEach(element => {
      const group = element.closest(".swiper-slide")?.parentElement || element.parentElement;
      const index = groups.get(group) || 0;
      delays.set(element, Math.min(index % 4, 3) * 90);
      groups.set(group, index + 1);
    });
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting || entry.target.getClientRects().length === 0) return;
        observer.unobserve(entry.target);
        const animation = entry.target.animate([
          { opacity: 0, translate: "0 28px" },
          { opacity: 1, translate: "0 0" },
        ], {
          duration: 600,
          delay: delays.get(entry.target) || 0,
          easing: "cubic-bezier(0.22, 1, 0.36, 1)",
          fill: "backwards",
        });
        animations.add(animation);
        animation.finished.then(() => animations.delete(animation), () => animations.delete(animation));
      });
    }, { threshold: 0, rootMargin: "0px 0px -35px 0px" });
    targets.forEach(element => observer.observe(element));
    // Focused links and controls should be visible immediately.
    document.addEventListener("focusin", event => {
      animations.forEach(animation => {
        if (animation.effect.target.contains(event.target)) animation.finish();
      });
    });
    motionPreference.addEventListener("change", event => {
      if (!event.matches) return;
      observer.disconnect();
      animations.forEach(animation => animation.cancel());
      animations.clear();
    });
    window.addEventListener("beforeprint", () => {
      observer.disconnect();
      animations.forEach(animation => animation.cancel());
    });
  }

});
