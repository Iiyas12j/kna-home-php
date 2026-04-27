<section id="clinic-search" class="section clinicSearch" aria-label="Clinic search">
  <div class="container">
    <div class="section__head">
      <h2 class="section__title">ค้นหาคลินิกใกล้คุณ</h2>
    </div>

    <div class="clinicSearch__panel" role="group" aria-label="Clinic search controls">
      <div class="clinicSearch__row">
        <div class="clinicSearch__label">Product</div>
        <div class="clinicSearch__pills" aria-label="Product selector">
          <!-- TODO: Replace with real labels -->
          <button type="button" class="pill is-active">HYABELL</button>
          <button type="button" class="pill">Product B</button>
          <button type="button" class="pill">Product C</button>
        </div>
      </div>

      <div class="clinicSearch__row clinicSearch__row--grid">
        <label class="field">
          <span class="field__label">Province</span>
          <select class="field__control">
            <option value="" selected disabled>เลือกจังหวัด (Placeholder)</option>
            <option value="bangkok">Bangkok</option>
            <option value="chiangmai">Chiang Mai</option>
            <option value="phuket">Phuket</option>
          </select>
        </label>

        <div class="clinicSearch__actions">
          <button type="button" class="btn btn--primary btn--full">Search</button>
        </div>
      </div>

      <div class="clinicSearch__results" aria-label="Search results placeholder">
        <div class="placeholder-list" aria-hidden="true">Results area (placeholder)</div>
      </div>
    </div>
  </div>
</section>
