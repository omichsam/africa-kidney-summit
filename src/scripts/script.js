const menuToggle = document.querySelector('.menu-toggle');
const nav = document.querySelector('#site-nav');
menuToggle?.addEventListener('click', () => {
  const open = nav.classList.toggle('open');
  menuToggle.setAttribute('aria-expanded', String(open));
  menuToggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
});
document.querySelectorAll('#site-nav a').forEach(link => link.addEventListener('click', () => nav.classList.remove('open')));

// Header sits transparent over the hero photo; once the page scrolls past it, switch to
// the normal solid header so nav links stay legible over the lighter sections below.
const siteHeader = document.querySelector('.site-header');
function updateHeaderScrollState(){
  siteHeader?.classList.toggle('is-scrolled', window.scrollY > 24);
}
updateHeaderScrollState();
window.addEventListener('scroll', updateHeaderScrollState, { passive: true });

// Back-to-top button: appears once scrolled past the hero, scrolls to the header's own
// #top anchor on click (same destination the logo link uses).
const backToTop = document.querySelector('#backToTop');
function updateBackToTopVisibility(){
  backToTop?.classList.toggle('is-visible', window.scrollY > 600);
}
updateBackToTopVisibility();
window.addEventListener('scroll', updateBackToTopVisibility, { passive: true });
backToTop?.addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});

// The logo links to #top, but #top is the sticky header itself — since a sticky element
// stays visually pinned regardless of scroll position, the browser's anchor-scroll logic
// sees it as "already there" and never actually scrolls. Scroll explicitly instead.
document.querySelectorAll('a[href="#top"]').forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
});

// Fade + rise elements into view the first time they scroll into the viewport.
const revealEls = document.querySelectorAll('.reveal');
const revealObserver = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('is-visible');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.15 });
revealEls.forEach(el => revealObserver.observe(el));

// COUNTDOWN — summit opens 9 March 2027 at 09:00 EAT (UTC+03:00).
const EVENT_DATE = new Date('2027-03-09T09:00:00+03:00');
(function(){
  const el = {d:document.getElementById('cd-days'),h:document.getElementById('cd-hours'),m:document.getElementById('cd-mins'),s:document.getElementById('cd-secs')};
  const wrap = document.getElementById('countdown');
  if (!wrap || !el.d || !el.h || !el.m || !el.s) return;
  const pad = (n,l) => String(n).padStart(l,'0');
  function tick(){
    const diff = EVENT_DATE - new Date();
    if(diff <= 0){ wrap.innerHTML = '<div style="grid-column:1/-1"><strong>We are live</strong><span>Happening now</span></div>'; return; }
    const sec = Math.floor(diff/1000);
    el.d.textContent = pad(Math.floor(sec/86400),3);
    el.h.textContent = pad(Math.floor((sec%86400)/3600),2);
    el.m.textContent = pad(Math.floor((sec%3600)/60),2);
    el.s.textContent = pad(sec%60,2);
    // Re-trigger the tick fade on the seconds digit so the widget visibly "lives"
    el.s.classList.remove('tick');
    void el.s.offsetWidth;
    el.s.classList.add('tick');
  }
  tick(); setInterval(tick,1000);
})();

const sections = [...document.querySelectorAll('main section[id]')];
const navLinks = [...document.querySelectorAll('.site-nav a[href^="#"]')];
const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if(entry.isIntersecting){
      navLinks.forEach(a => a.classList.toggle('active', a.getAttribute('href') === '#' + entry.target.id));
    }
  });
},{rootMargin:'-25% 0px -65% 0px',threshold:0});
sections.forEach(s => observer.observe(s));


// Lightweight touch-friendly carousels
document.querySelectorAll('.carousel-controls button').forEach((button) => {
  button.addEventListener('click', () => {
    const track = document.querySelector(button.dataset.target);
    if (!track) return;
    const direction = button.classList.contains('carousel-next') ? 1 : -1;
    const amount = track.clientWidth * 0.78;
    track.parentElement.scrollBy({ left: direction * amount, behavior: 'smooth' });
  });
});

// Hero background: crossfades through .hero-bg-slide elements on a timer (the floating
// media card was removed in favour of letting the background itself change).
(() => {
  const slides = [...document.querySelectorAll('.hero-bg-slide')];
  if (slides.length < 2) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  let index = slides.findIndex(s => s.classList.contains('is-active'));
  if (index === -1) index = 0;
  setInterval(() => {
    slides[index].classList.remove('is-active');
    index = (index + 1) % slides.length;
    slides[index].classList.add('is-active');
  }, 6000);
})();

// Accessible content carousel controls: keep the next item visibly discoverable on mobile.
(() => {
  const getItems = (track) => [...track.children];
  const updateProgress = (wrapper, track) => {
    const items = getItems(track);
    if (!items.length) return;
    const current = Math.min(items.length, Math.max(1, Math.round(track.scrollLeft / Math.max(1, items[0].getBoundingClientRect().width + 10)) + 1));
    const progress = wrapper.querySelector('.carousel-progress');
    if (progress) {
      const currentEl = progress.querySelector('b');
      const totalEl = progress.querySelector('span');
      if (currentEl) currentEl.textContent = String(current).padStart(2,'0');
      if (totalEl) totalEl.textContent = String(items.length).padStart(2,'0');
      const bar = progress.querySelector('i');
      if (bar) bar.style.setProperty('--progress', `${(current / items.length) * 100}%`);
    }
  };

  document.querySelectorAll('.content-carousel, .commitments-carousel').forEach(wrapper => {
    const track = wrapper.querySelector('.programme-track, .audience-track, .registration-track, .commitments-track, .partner-roles, .speaker-track');
    if (!track) return;
    const items = getItems(track);
    const prev = wrapper.querySelector('.carousel-prev');
    const next = wrapper.querySelector('.carousel-next');
    const move = (direction) => {
      const amount = Math.max(track.clientWidth * .82, items[0]?.getBoundingClientRect().width || 0);
      track.scrollBy({left: direction * (amount + 10), behavior:'smooth'});
    };
    prev?.addEventListener('click', () => move(-1));
    next?.addEventListener('click', () => move(1));
    track.addEventListener('scroll', () => updateProgress(wrapper, track), {passive:true});
    window.addEventListener('resize', () => updateProgress(wrapper, track));
    updateProgress(wrapper, track);
  });

  // Programme: show detail bullets only when requested on compact screens.
  document.querySelectorAll('.card-more').forEach(button => {
    button.addEventListener('click', () => {
      const card = button.closest('.day-card');
      const open = card.classList.toggle('is-expanded');
      button.innerHTML = open ? 'Show less <span>−</span>' : 'Read more <span>+</span>';
    });
  });

  // Audience: mobile detail layer; desktop gets the same information on hover/focus.
  document.querySelectorAll('.audience-more').forEach(button => {
    button.addEventListener('click', () => {
      const card = button.closest('.audience-card');
      const open = card.classList.toggle('is-expanded');
      button.innerHTML = open ? 'Close details <span>−</span>' : 'View details <span>+</span>';
    });
  });

  // Registration: reveal supporting text/actions without making the first view text-heavy.
  document.querySelectorAll('.registration-more').forEach(button => {
    button.addEventListener('click', () => {
      const card = button.closest('.registration-card');
      const open = card.classList.toggle('is-expanded');
      button.innerHTML = open ? 'Hide details <span>−</span>' : 'View details <span>+</span>';
    });
  });
})();

// =========================================================
// REQUEST FORMS — submitted to send-request.php (see that file at the repo root), which
// emails the submission server-side. Only works on the cPanel-hosted domain, since GitHub
// Pages can't run PHP. Three modals (exhibit/rate/letter). PHP mangles POST keys containing
// spaces/dots into underscores (e.g. "Email address" -> Email_address), so field labels are
// flattened into one "summary" field rather than relied on as literal $_POST keys.
// =========================================================
(function initRequestForms(){
  const exhibitModal = document.getElementById('exhibitModal');
  const rateModal = document.getElementById('rateModal');
  const letterModal = document.getElementById('letterModal');
  if (!exhibitModal || !rateModal || !letterModal) return;

  const modalFormView = document.getElementById('modalFormView');
  const modalSuccessView = document.getElementById('modalSuccessView');
  const exhibitForm = document.getElementById('exhibitForm');
  const exhibitError = document.getElementById('exhibitError');
  let lastFocusedBeforeModal = null;
  let activeModal = null;

  function getFocusable(container) {
    return Array.from(container.querySelectorAll(
      'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])'
    )).filter(el => el.offsetParent !== null);
  }

  function openModal(modalEl, trigger) {
    lastFocusedBeforeModal = trigger || document.activeElement;
    activeModal = modalEl;
    modalEl.classList.add('open');
    modalEl.removeAttribute('aria-hidden');
    document.body.style.overflow = 'hidden';
    const focusables = getFocusable(modalEl.querySelector('.t-modal__panel'));
    (focusables[0] || modalEl).focus();
  }

  function resetModalState(modalEl) {
    const formView = modalEl.querySelector('[id$="FormView"]');
    const successView = modalEl.querySelector('.t-modal__success');
    const form = modalEl.querySelector('form');
    const errorEl = modalEl.querySelector('.t-form-error');
    if (formView) formView.style.display = '';
    if (successView) successView.classList.remove('show');
    if (form) form.reset();
    if (errorEl) { errorEl.textContent = ''; errorEl.classList.remove('show'); }
  }

  function closeModal() {
    if (!activeModal) return;
    activeModal.classList.remove('open');
    activeModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    resetModalState(activeModal);
    if (lastFocusedBeforeModal) lastFocusedBeforeModal.focus();
    activeModal = null;
  }

  document.querySelectorAll('.js-open-exhibit-modal').forEach(btn => {
    btn.addEventListener('click', (e) => {
      openModal(exhibitModal, e.currentTarget);
      if (btn.dataset.package) document.getElementById('epackage').value = btn.dataset.package;
    });
  });
  document.getElementById('closeExhibitModal').addEventListener('click', closeModal);
  exhibitModal.addEventListener('click', (e) => { if (e.target === exhibitModal) closeModal(); });

  document.addEventListener('keydown', (e) => {
    if (!activeModal) return;
    if (e.key === 'Escape') { closeModal(); return; }
    if (e.key === 'Tab') {
      const focusables = getFocusable(activeModal.querySelector('.t-modal__panel'));
      if (!focusables.length) return;
      const first = focusables[0], last = focusables[focusables.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });

  const exhibitSubmitBtn = document.getElementById('exhibitSubmitBtn');

  exhibitForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    exhibitError.classList.remove('show'); exhibitError.textContent = '';

    const org = document.getElementById('eorg').value.trim();
    const name = document.getElementById('ename').value.trim();
    const position = document.getElementById('eposition').value.trim();
    const email = document.getElementById('eemail').value.trim();
    const country = document.getElementById('ecountry').value;
    const phoneCode = document.getElementById('ephoneCode').value.trim();
    const phone = document.getElementById('ephone').value.trim();
    const pkg = document.getElementById('epackage').value;
    const message = document.getElementById('emessage').value.trim();

    let error = '';
    if (!org) error = 'Please provide your company or organisation name.';
    else if (!name) error = 'Please provide a contact person.';
    else if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) error = 'Please provide a valid email address.';
    else if (!country) error = 'Please select your country.';
    else if (!phone) error = 'Please provide a contact telephone number.';
    else if (!pkg) error = 'Please select a package of interest.';

    if (error) {
      exhibitError.textContent = error; exhibitError.classList.add('show');
      return;
    }

    const lines = [
      `Company / Organisation: ${org}`,
      `Contact person: ${name}`,
      `Position: ${position || '—'}`,
      `Email: ${email}`,
      `Country: ${country}`,
      `Country code: ${phoneCode}`,
      `Phone number: ${phone}`,
      `Package of interest: ${pkg}`,
      `Message: ${message || '—'}`,
    ];
    const payload = new URLSearchParams();
    payload.set('form_type', 'exhibit');
    payload.set('email', email);
    payload.set('summary', lines.join('\n'));
    payload.set('botcheck', '');

    const label = exhibitSubmitBtn.querySelector('.btn-label');
    exhibitSubmitBtn.disabled = true; label.textContent = 'Sending…';
    try {
      const res = await fetch('send-request.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', Accept: 'application/json' },
        body: payload,
      });
      const result = await res.json();
      if (!result.success) throw new Error(result.message || 'Submission failed');
      modalFormView.style.display = 'none';
      modalSuccessView.classList.add('show');
    } catch (err) {
      exhibitError.textContent = 'Something went wrong sending your enquiry. Please try again or email admin@kidneyhealth.africa directly.';
      exhibitError.classList.add('show');
    } finally {
      exhibitSubmitBtn.disabled = false; label.textContent = 'Send enquiry';
    }
  });

  async function submitRequestForm(form, formType) {
    const data = new FormData(form);
    const email = (form.querySelector('input[type="email"]')?.value || '').trim();
    const lines = [];
    for (const [key, value] of data.entries()) {
      if (key === 'botcheck') continue;
      lines.push(`${key}: ${value}`);
    }
    const payload = new URLSearchParams();
    payload.set('form_type', formType);
    payload.set('email', email);
    payload.set('summary', lines.join('\n'));
    payload.set('botcheck', data.get('botcheck') || '');
    const res = await fetch('send-request.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', Accept: 'application/json' },
      body: payload,
    });
    return res.json();
  }

  function wireRequestForm({ form, errorEl, formView, successView, submitBtn, formType, requiredFields, fallbackEmail }) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      errorEl.classList.remove('show'); errorEl.textContent = '';

      for (const { id, message } of requiredFields) {
        if (!document.getElementById(id).value.trim()) {
          errorEl.textContent = message; errorEl.classList.add('show');
          return;
        }
      }
      const emailField = form.querySelector('input[type="email"]');
      if (emailField && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value.trim())) {
        errorEl.textContent = 'Please provide a valid email address.'; errorEl.classList.add('show');
        return;
      }

      const label = submitBtn.querySelector('.btn-label');
      submitBtn.disabled = true; label.textContent = 'Sending…';
      try {
        const result = await submitRequestForm(form, formType);
        if (!result.success) throw new Error(result.message || 'Submission failed');
        formView.style.display = 'none';
        successView.classList.add('show');
      } catch (err) {
        errorEl.textContent = `Something went wrong sending your request. Please try again or email ${fallbackEmail} directly.`;
        errorEl.classList.add('show');
      } finally {
        submitBtn.disabled = false; label.textContent = 'Send request';
      }
    });
  }

  const COUNTRIES = [
    ['Algeria','213'],['Angola','244'],['Benin','229'],['Botswana','267'],['Burkina Faso','226'],
    ['Burundi','257'],['Cabo Verde','238'],['Cameroon','237'],['Central African Republic','236'],
    ['Chad','235'],['Comoros','269'],['Congo (Republic)','242'],['Congo (DRC)','243'],
    ["Côte d'Ivoire",'225'],['Djibouti','253'],['Egypt','20'],['Equatorial Guinea','240'],
    ['Eritrea','291'],['Eswatini','268'],['Ethiopia','251'],['Gabon','241'],['Gambia','220'],
    ['Ghana','233'],['Guinea','224'],['Guinea-Bissau','245'],['Kenya','254'],['Lesotho','266'],
    ['Liberia','231'],['Libya','218'],['Madagascar','261'],['Malawi','265'],['Mali','223'],
    ['Mauritania','222'],['Mauritius','230'],['Morocco','212'],['Mozambique','258'],['Namibia','264'],
    ['Niger','227'],['Nigeria','234'],['Rwanda','250'],['São Tomé and Príncipe','239'],
    ['Senegal','221'],['Seychelles','248'],['Sierra Leone','232'],['Somalia','252'],
    ['South Africa','27'],['South Sudan','211'],['Sudan','249'],['Tanzania','255'],['Togo','228'],
    ['Tunisia','216'],['Uganda','256'],['Zambia','260'],['Zimbabwe','263'],
    ['United Kingdom','44'],['United States / Canada','1'],['France','33'],['Germany','49'],
    ['India','91'],['United Arab Emirates','971'],['China','86'],['Other','']
  ];

  function populateCountrySelect(select){
    COUNTRIES.forEach(([name, code]) => {
      const opt = document.createElement('option');
      opt.value = name;
      opt.dataset.code = code;
      opt.textContent = name;
      select.appendChild(opt);
    });
  }

  function wireCountryPhoneCode(countryId, codeId){
    const select = document.getElementById(countryId);
    const codeField = document.getElementById(codeId);
    if (!select || !codeField) return;
    populateCountrySelect(select);
    select.addEventListener('change', () => {
      const opt = select.options[select.selectedIndex];
      const code = opt && opt.dataset.code;
      codeField.value = code ? '+' + code : '+';
    });
  }

  wireCountryPhoneCode('rcountry', 'rphoneCode');
  wireCountryPhoneCode('lcountry', 'lphoneCode');
  wireCountryPhoneCode('ecountry', 'ephoneCode');

  document.querySelectorAll('.t-number-stepper').forEach((stepper) => {
    const input = stepper.querySelector('input[type="number"]');
    const buttons = stepper.querySelectorAll('.t-number-stepper__btn');
    if (!input) return;

    function updateDisabledState() {
      const min = input.min !== '' ? Number(input.min) : -Infinity;
      const current = input.value === '' ? min : Number(input.value);
      buttons.forEach((btn) => {
        const step = Number(btn.dataset.step);
        btn.disabled = step < 0 && current <= min;
      });
    }

    buttons.forEach((btn) => {
      btn.addEventListener('click', () => {
        const step = Number(btn.dataset.step);
        const min = input.min !== '' ? Number(input.min) : -Infinity;
        const current = input.value === '' ? (min === -Infinity ? 0 : min) : Number(input.value);
        const next = Math.max(min, current + step);
        input.value = next;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        updateDisabledState();
      });
    });

    input.addEventListener('input', updateDisabledState);
    updateDisabledState();
  });

  document.getElementById('openRateModalCta').addEventListener('click', (e) => openModal(rateModal, e.currentTarget));
  document.getElementById('closeRateModal').addEventListener('click', closeModal);
  rateModal.addEventListener('click', (e) => { if (e.target === rateModal) closeModal(); });
  wireRequestForm({
    form: document.getElementById('rateForm'),
    errorEl: document.getElementById('rateError'),
    formView: document.getElementById('rateFormView'),
    successView: document.getElementById('rateSuccessView'),
    submitBtn: document.getElementById('rateSubmitBtn'),
    formType: 'rate',
    fallbackEmail: 'admin@kidneyhealth.africa',
    requiredFields: [
      { id: 'rfname', message: 'Please provide your first name.' },
      { id: 'rlname', message: 'Please provide your last name.' },
      { id: 'remail', message: 'Please provide your email address.' },
      { id: 'rorg', message: 'Please provide your organization.' },
      { id: 'rcountry', message: 'Please provide your country.' },
      { id: 'rphone', message: 'Please provide a contact phone number.' },
      { id: 'rpasses', message: 'Please tell us how many delegate passes you need.' },
      { id: 'rgroup', message: 'Please select a group description.' },
    ],
  });

  document.getElementById('openLetterModalCta').addEventListener('click', (e) => openModal(letterModal, e.currentTarget));
  document.getElementById('closeLetterModal').addEventListener('click', closeModal);
  letterModal.addEventListener('click', (e) => { if (e.target === letterModal) closeModal(); });
  wireRequestForm({
    form: document.getElementById('letterForm'),
    errorEl: document.getElementById('letterError'),
    formView: document.getElementById('letterFormView'),
    successView: document.getElementById('letterSuccessView'),
    submitBtn: document.getElementById('letterSubmitBtn'),
    formType: 'letter',
    fallbackEmail: 'secretariat@kidneyhealth.africa',
    requiredFields: [
      { id: 'lfname', message: 'Please provide your first name.' },
      { id: 'llname', message: 'Please provide your last name.' },
      { id: 'lemail', message: 'Please provide your email address.' },
      { id: 'lorg', message: 'Please provide your organization.' },
      { id: 'lcountry', message: 'Please provide your country.' },
      { id: 'lphone', message: 'Please provide a contact phone number.' },
      { id: 'lpasses', message: 'Please tell us how many delegate passes you need.' },
      { id: 'lneed', message: 'Please select what you need.' },
    ],
  });
})();
