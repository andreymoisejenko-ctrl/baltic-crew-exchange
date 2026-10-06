(() => {
  const body = document.body;
  const listingModal = document.getElementById('listingModal');
  const interestModal = document.getElementById('interestModal');
  const listingType = document.getElementById('listingType');

  const copy = {
    crew: {
      kicker: 'Provider submission', title: 'List available crew',
      description: 'Required information becomes an anonymous draft profile. Nothing is published before review.',
      count: 'Available people*', skills: 'Specialisations*', from: 'Available from*', until: 'Available until',
      mobility: 'Countries the crew can work in', experience: 'Relevant experience'
    },
    project: {
      kicker: 'Project company submission', title: 'Submit project requirement',
      description: 'Describe the project need once. The system creates an anonymous request for review and matching.',
      count: 'People required*', skills: 'Required specialisations*', from: 'Planned start*', until: 'Expected end date',
      mobility: 'Project location / site access notes', experience: 'Scope of work and required experience'
    }
  };

  function openDialog(dialog) {
    if (!dialog) return;
    dialog.showModal();
    body.classList.add('modal-open');
  }
  document.querySelectorAll('[data-open-form]').forEach(button => button.addEventListener('click', () => {
    const type = button.dataset.openForm;
    const c = copy[type];
    listingType.value = type;
    document.getElementById('formKicker').textContent = c.kicker;
    document.getElementById('formTitle').textContent = c.title;
    document.getElementById('formDescription').textContent = c.description;
    document.getElementById('countLabel').textContent = c.count;
    document.getElementById('skillsLabel').textContent = c.skills;
    document.getElementById('fromLabel').textContent = c.from;
    document.getElementById('untilLabel').textContent = c.until;
    document.getElementById('mobilityLabel').textContent = c.mobility;
    document.getElementById('experienceLabel').textContent = c.experience;
    openDialog(listingModal);
  }));

  document.querySelectorAll('[data-interest-id]').forEach(button => button.addEventListener('click', () => {
    document.getElementById('interestListingId').value = button.dataset.interestId;
    document.getElementById('interestTitle').textContent = `${button.dataset.interestType === 'crew' ? 'Request introduction' : 'Offer your crew'} · ${button.dataset.interestCode}`;
    openDialog(interestModal);
  }));

  document.querySelectorAll('dialog').forEach(dialog => {
    dialog.addEventListener('close', () => body.classList.remove('modal-open'));
    dialog.addEventListener('click', event => {
      if (event.target === dialog) dialog.close();
    });
  });

  document.getElementById('menuButton')?.addEventListener('click', () => document.getElementById('navLinks')?.classList.toggle('open'));

  ['crew', 'project'].forEach(type => {
    const search = document.querySelector(`[data-filter-search="${type}"]`);
    const industry = document.querySelector(`[data-filter-industry="${type}"]`);
    const country = document.querySelector(`[data-filter-country="${type}"]`);
    const cards = [...document.querySelectorAll(`[data-listing-grid="${type}"] .listing-card`)];
    const noMatch = document.querySelector(`[data-no-match="${type}"]`);
    function filter() {
      let visible = 0;
      cards.forEach(card => {
        const okSearch = !search.value || card.dataset.search.includes(search.value.toLowerCase());
        const okIndustry = !industry.value || card.dataset.industry === industry.value.toLowerCase();
        const okCountry = !country.value || card.dataset.country === country.value.toLowerCase();
        const show = okSearch && okIndustry && okCountry;
        card.hidden = !show;
        if (show) visible++;
      });
      if (noMatch) noMatch.style.display = cards.length && !visible ? 'block' : 'none';
    }
    [search, industry, country].forEach(input => input?.addEventListener('input', filter));
  });

  const params = new URLSearchParams(location.search);
  if (params.get('form') === 'crew' || params.get('form') === 'project') {
    document.querySelector(`[data-open-form="${params.get('form')}"]`)?.click();
  }
})();
