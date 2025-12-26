<style>
.footer-wrapper {
  margin-top: 50px;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 22px 0;
  background: var(--nav-bg);
  border-top: 1px solid var(--nav-border);
  backdrop-filter: blur(10px);
  position: relative;
}

/* Bubble / Cute Accent */
.footer-wrapper:before {
  content: "✨";
  position: absolute;
  top: -12px;
  font-size: 18px;
  opacity: 0.8;
}

/* Text */
.footer-text {
  font-family: "Quicksand", sans-serif;
  font-size: 14px;
  font-weight: 600;
  color: var(--text);
  letter-spacing: 0.4px;
  display: flex;
  align-items: center;
  gap: 6px;
  opacity: 0.9;
}

/* Small decorative divider dot */
.footer-text::before,
.footer-text::after {
  content: "•";
  opacity: 0.5;
  font-size: 16px;
}

/* Hover effect */
.footer-wrapper:hover .footer-text {
  opacity: 1;
  transform: scale(1.02);
  transition: 0.25s ease;
}
</style>

<div class="footer-wrapper">
  <div class="footer-text">
    UTS - Cindy Bela Amelia - 221011401543 - 07TPLP020
  </div>
</div>
