// Vdesiconnect — Rakhi Festival — site JS

document.addEventListener('DOMContentLoaded', () => {
  AOS.init({ duration: 700, once: true, offset: 60 });

  // Hero slider — Brother & Sister / Rakhi themed slides
  if (document.querySelector('.hero-swiper')) {
    new Swiper('.hero-swiper', {
      loop: true,
      effect: 'fade',
      autoplay: { delay: 4500, disableOnInteraction: false },
      pagination: { el: '.hero-swiper .swiper-pagination', clickable: true },
      navigation: { nextEl: '.hero-swiper .swiper-button-next', prevEl: '.hero-swiper .swiper-button-prev' },
    });
  }

  // Rakhi Gifts carousel (home page)
  if (document.querySelector('.gift-swiper')) {
    new Swiper('.gift-swiper', {
      slidesPerView: 2,
      spaceBetween: 16,
      loop: true,
      autoplay: { delay: 3000, disableOnInteraction: false },
      breakpoints: {
        576: { slidesPerView: 3 },
        992: { slidesPerView: 4 },
      },
    });
  }

  // Related products carousel (product detail page)
  if (document.querySelector('.related-swiper')) {
    new Swiper('.related-swiper', {
      slidesPerView: 2,
      spaceBetween: 16,
      pagination: { el: '.related-swiper .swiper-pagination', clickable: true },
      breakpoints: {
        576: { slidesPerView: 3 },
        992: { slidesPerView: 4 },
      },
    });
  }

  initProductGrid();
  initCheckoutPage();
});

// Live quantity → subtotal/total preview and shipping-address toggle on checkout.php.
// The actual charged amount is always recomputed server-side on submit.
function initCheckoutPage() {
  const qtyInput = document.getElementById('qty');
  if (qtyInput) {
    const unitPrice = parseFloat(qtyInput.dataset.unitPrice);
    const shipping = parseFloat(qtyInput.dataset.shipping);
    const qtyDisplay = document.getElementById('qtyDisplay');
    const lineTotal = document.getElementById('lineTotal');
    const subtotalDisplay = document.getElementById('subtotalDisplay');
    const totalDisplay = document.getElementById('totalDisplay');

    qtyInput.addEventListener('input', () => {
      const qty = Math.max(1, Math.min(10, parseInt(qtyInput.value, 10) || 1));
      const subtotal = unitPrice * qty;
      if (qtyDisplay) qtyDisplay.textContent = qty;
      if (lineTotal) lineTotal.textContent = '₹' + subtotal.toLocaleString('en-IN');
      if (subtotalDisplay) subtotalDisplay.textContent = '₹' + subtotal.toLocaleString('en-IN');
      if (totalDisplay) totalDisplay.textContent = '₹' + (subtotal + shipping).toLocaleString('en-IN');
    });
  }

  const shipCheckbox = document.getElementById('ship_different');
  const shippingFields = document.getElementById('shippingFields');
  if (shipCheckbox && shippingFields) {
    shipCheckbox.addEventListener('change', () => {
      shippingFields.classList.toggle('d-none', !shipCheckbox.checked);
    });
  }
}

// Client-side category filter + live search for product-list.php,
// rendered from the JSON catalog served by api/products.php
function initProductGrid() {
  const grid = document.getElementById('productGrid');
  if (!grid) return;

  const searchInput = document.getElementById('productSearch');
  const filterBtns = document.querySelectorAll('.filter-btn');
  const params = new URLSearchParams(window.location.search);
  let activeCategory = params.get('category') || 'all';
  let products = [];

  const card = (p) => `
    <div class="col-6 col-md-4 col-lg-3" data-aos="fade-up">
      <div class="product-card">
        <a href="product-detail.php?id=${p.id}" class="thumb">
          ${p.badge ? `<span class="badge-tag">${p.badge}</span>` : ''}
          <img src="${p.image}" alt="${p.name}" loading="lazy">
        </a>
        <div class="body">
          <span class="cat">${p.category_label}</span>
          <h3><a href="product-detail.php?id=${p.id}" class="text-decoration-none text-dark">${p.name}</a></h3>
          <div class="price-row">
            <span class="price-old">₹${p.price}</span>
            <span class="price-offer">₹${p.offer_price}</span>
          </div>
          <a class="btn btn-maroon buy-btn" href="checkout.php?id=${p.id}">Buy Now</a>
        </div>
      </div>
    </div>`;

  function render() {
    const term = (searchInput?.value || '').trim().toLowerCase();
    const filtered = products.filter(p =>
      (activeCategory === 'all' || p.category === activeCategory) &&
      (term === '' || p.name.toLowerCase().includes(term))
    );
    grid.innerHTML = filtered.length
      ? filtered.map(card).join('')
      : '<p class="text-center text-muted py-5">No Rakhis match your search.</p>';
    AOS.refreshHard();
  }

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeCategory = btn.dataset.category;
      render();
    });
    if (btn.dataset.category === activeCategory) btn.classList.add('active');
  });

  searchInput?.addEventListener('input', render);

  fetch('api/products.php')
    .then(res => res.json())
    .then(data => { products = data; render(); })
    .catch(() => { grid.innerHTML = '<p class="text-center text-muted py-5">Could not load products.</p>'; });
}
