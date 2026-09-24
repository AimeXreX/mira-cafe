(() => {
  const input = document.querySelector('#menu-search');
  const clear = document.querySelector('.search-clear');
  const chips = [...document.querySelectorAll('.chip')];
  const sections = [...document.querySelectorAll('.menu-section')];
  const cards = [...document.querySelectorAll('.product-card')];
  const noResults = document.querySelector('.no-results');
  const resultCount = document.querySelector('#result-count');
  if (!input) return;

  const track = payload => fetch('api/track.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload),keepalive:true}).catch(()=>{});
  track({type:'view'});
  document.querySelectorAll('.ingredients__toggle').forEach(button => button.addEventListener('click', () => {
    const content = button.nextElementSibling;
    const open = button.getAttribute('aria-expanded') === 'true';
    button.setAttribute('aria-expanded', String(!open)); button.querySelector('i').textContent = open ? '+' : '−'; content.hidden = open;
    if (!open) track({type:'product', product_id:Number(button.closest('.product-card').dataset.productId)});
  }));
  document.querySelectorAll('[data-product-link]').forEach(link => link.addEventListener('click', () => track({type:'product',product_id:Number(link.dataset.productLink)})));

  let active = 'all';
  const fa = value => String(value).replace(/\d/g, n => '۰۱۲۳۴۵۶۷۸۹'[n]);
  const normalize = value => value.trim().toLocaleLowerCase('fa').replace(/ي/g, 'ی').replace(/ك/g, 'ک');

  function filter() {
    const query = normalize(input.value);
    let count = 0;
    sections.forEach(section => {
      const categoryMatch = active === 'all' || section.dataset.category === active;
      let sectionCount = 0;
      section.querySelectorAll('.product-card').forEach(card => {
        const matches = categoryMatch && normalize(card.dataset.search || '').includes(query);
        card.hidden = !matches;
        if (matches) { count++; sectionCount++; }
      });
      section.hidden = sectionCount === 0;
    });
    clear.hidden = input.value.length === 0;
    noResults.hidden = count !== 0;
    resultCount.textContent = fa(count);
  }

  input.addEventListener('input', filter);
  clear.addEventListener('click', () => { input.value = ''; input.focus(); filter(); });
  chips.forEach(chip => chip.addEventListener('click', () => {
    active = chip.dataset.filter;
    chips.forEach(item => { const on = item === chip; item.classList.toggle('is-active', on); item.setAttribute('aria-pressed', String(on)); });
    filter();
    document.querySelector('.menu-tools').scrollIntoView({behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'});
  }));
})();
