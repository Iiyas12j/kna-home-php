<section id="news-events" class="section newsEvents" aria-label="News and events">
  <div class="container">
    <div class="section__head section__head--split">
      <div>
        <h2 class="section__title">News &amp; Events</h2>
        <p class="section__subtitle">Latest updates and activities (Placeholder)</p>
      </div>
      <a class="link" href="#all-news">View all</a>
    </div>

    <div class="newsEvents__list" aria-label="News list">
      <?php for ($i=0; $i<5; $i++): ?>
        <article class="newsCard" aria-label="News card placeholder <?php echo $i+1; ?>">
          <div class="newsCard__date">DD MMM YYYY</div>
          <div class="newsCard__title">News title placeholder goes here</div>
        </article>
      <?php endfor; ?>
    </div>
  </div>
</section>
