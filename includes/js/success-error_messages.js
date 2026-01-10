document.addEventListener("DOMContentLoaded", function () {
  ["success-message", "error-message"].forEach(function(id) {
    const el = document.getElementById(id);
    if (el) {
      setTimeout(() => {
        el.style.transition = "opacity 0.5s ease";
        el.style.opacity = "0";
        setTimeout(() => {
          el.remove();
        }, 500);
      }, 5000);
    }
  });
});