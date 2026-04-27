<footer id="footer" class="footer" aria-label="Footer">
  <div class="container footer__inner">
    <div class="footer__top">
      <div class="footer__brand">
        <img class="footer__logoImg" src="/logo/logo.kna.png" alt="KNA Interpharma" />

        <div class="footer__company">
          <!-- TODO: Replace with real company info -->
          <div class="footer__companyName">KNA Interpharma (Placeholder)</div>
          <div class="footer__companyInfo">Address / phone / email placeholders</div>
        </div>
      </div>

      <div class="footer__subscribe" aria-label="Subscribe form">
        <div class="footer__subscribeTitle">Subscribe</div>
        <form class="footer__form" onsubmit="event.preventDefault()">
          <label class="field field--inline">
            <span class="srOnly">Email</span>
            <input class="field__control" type="email" placeholder="Email (Placeholder)" />
          </label>
          <button type="submit" class="btn btn--primary">Submit</button>
        </form>
      </div>
    </div>

    <div class="footer__bottom">
      <small class="footer__copy">© <?php echo date("Y"); ?> KNA Interpharma. All rights reserved. (Placeholder)</small>
    </div>
  </div>
</footer>
