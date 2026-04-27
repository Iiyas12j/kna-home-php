<section id="video-channel" class="section videoChannel" aria-label="Video channel">
  <div class="container">
    <div class="section__head section__head--split">
      <div>
        <h2 class="section__title">VIDEO CHANNEL</h2>
      </div>
      <a class="btn btn--ghost" href="#all-videos">View all (xx videos)</a>
    </div>

    <div class="videoChannel__grid" aria-label="Video grid">
      <?php for ($i=0; $i<6; $i++): ?>
        <article class="videoCard" aria-label="Video placeholder <?php echo $i+1; ?>">
          <div class="videoCard__embed" aria-hidden="true">
            <div class="videoFrame">VIDEO</div>
          </div>
          <div class="videoCard__caption">Caption/Title (Placeholder)</div>
        </article>
      <?php endfor; ?>
    </div>
  </div>
</section>
