// COPYRIGHT YEAR
document.getElementById('copyrightYear').textContent = new Date().getFullYear();

// COUNTDOWN
const EVENT_DATE = new Date('2027-03-09T09:00:00+03:00');
(function(){
  const el = {d:document.getElementById('cd-days'),h:document.getElementById('cd-hours'),m:document.getElementById('cd-mins'),s:document.getElementById('cd-secs')};
  const wrap = document.getElementById('countdown');
  const pad = (n,l) => String(n).padStart(l,'0');
  function tick(){
    const diff = EVENT_DATE - new Date();
    if(diff <= 0){ wrap.innerHTML = '<div class="cd" style="min-width:auto;padding:16px 28px"><span class="cd__num" style="font-size:1.2rem">We are live</span><span class="cd__lbl">Happening now</span></div>'; return; }
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

// HEADER
const siteHeader = document.getElementById('siteHeader');
const navToggle = document.getElementById('navToggle');
const navLinks = document.getElementById('navLinks');
const backToTop = document.getElementById('backToTop');
window.addEventListener('scroll', () => {
  siteHeader.classList.toggle('scrolled', window.scrollY > 40);
  backToTop.classList.toggle('show', window.scrollY > 500);
}, { passive:true });
backToTop.addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
  // Move focus to the skip target so keyboard/screen-reader users land at the top of the page too
  document.getElementById('main-content').setAttribute('tabindex', '-1');
  document.getElementById('main-content').focus({ preventScroll: true });
});
const navBackdrop = document.getElementById('navBackdrop');
navBackdrop.hidden = false;
function setNavOpen(open){
  navLinks.classList.toggle('open', open);
  navToggle.classList.toggle('active', open);
  navToggle.setAttribute('aria-expanded', open);
  navBackdrop.classList.toggle('open', open);
  document.body.style.overflow = open ? 'hidden' : '';
}
navToggle.addEventListener('click', () => setNavOpen(!navLinks.classList.contains('open')));
navBackdrop.addEventListener('click', () => setNavOpen(false));
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && navLinks.classList.contains('open')) setNavOpen(false);
});
navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => setNavOpen(false)));

// SCROLL SPY — underline the nav link for whichever section is currently in view
(function initScrollSpy(){
  const spyLinks = Array.from(navLinks.querySelectorAll('a[href^="#"]'));
  const sectionMap = new Map();
  spyLinks.forEach(link => {
    const section = document.getElementById(link.getAttribute('href').slice(1));
    if (section) sectionMap.set(section, link);
  });
  if (!sectionMap.size) return;

  function setActive(link){
    spyLinks.forEach(a => a.classList.toggle('active', a === link));
  }

  const spy = new IntersectionObserver((entries) => {
    // Prefer the entry closest to the top-of-viewport activation line; avoids flicker
    // when a short section and its neighbour are both technically intersecting.
    const visible = entries.filter(e => e.isIntersecting);
    if (!visible.length) return;
    visible.sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
    setActive(sectionMap.get(visible[0].target));
  }, {
    // Activation band sits just under the fixed header; bottom cut-off keeps only
    // the section currently owning the top of the viewport as "active".
    rootMargin: '-104px 0px -55% 0px',
    threshold: 0
  });
  sectionMap.forEach((link, section) => spy.observe(section));
})();

// PARTNERS MARQUEE — pure CSS animation (see .partners__marquee), no JS needed.

// MODALS (shared open/close/focus-trap across the exhibit, rate and letter modals)
const exhibitModal = document.getElementById('exhibitModal');
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
  const focusables = getFocusable(modalEl.querySelector('.modal__panel'));
  (focusables[0] || modalEl).focus();
}
function closeModal() {
  if (!activeModal) return;
  activeModal.classList.remove('open');
  activeModal.setAttribute('aria-hidden', 'true');
  document.body.style.overflow = '';
  if (lastFocusedBeforeModal) lastFocusedBeforeModal.focus();
  activeModal = null;
}

document.getElementById('openExhibitModalCta').addEventListener('click', (e) => openModal(exhibitModal, e.currentTarget));
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
  // Trap Tab focus inside the open modal
  if (e.key === 'Tab') {
    const focusables = getFocusable(activeModal.querySelector('.modal__panel'));
    if (!focusables.length) return;
    const first = focusables[0], last = focusables[focusables.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  }
});

exhibitForm.addEventListener('submit', (e) => {
  e.preventDefault();
  exhibitError.classList.remove('show'); exhibitError.textContent = '';

  const org = document.getElementById('eorg').value.trim();
  const name = document.getElementById('ename').value.trim();
  const email = document.getElementById('eemail').value.trim();
  const phone = document.getElementById('ephone').value.trim();
  const pkg = document.getElementById('epackage').value;

  let error = '';
  if (!org) error = 'Please provide your company or organisation name.';
  else if (!name) error = 'Please provide a contact person.';
  else if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) error = 'Please provide a valid email address.';
  else if (!phone) error = 'Please provide a contact telephone number.';
  else if (!pkg) error = 'Please select a package of interest.';

  if (error) {
    exhibitError.textContent = error; exhibitError.classList.add('show');
    return;
  }

  modalFormView.style.display = 'none';
  modalSuccessView.classList.add('show');
});

// RATE & LETTER REQUEST FORMS — submitted straight to the secretariat's inbox via Web3Forms
// (a free form-to-email relay), since this static site has no backend of its own.
// Replace each placeholder access key with a real one from https://web3forms.com,
// created against the matching destination inbox.
const WEB3FORMS_ENDPOINT = 'https://api.web3forms.com/submit';

async function submitToWeb3Forms(form, accessKey) {
  const data = new FormData(form);
  data.append('access_key', accessKey);
  const res = await fetch(WEB3FORMS_ENDPOINT, { method: 'POST', headers: { Accept: 'application/json' }, body: data });
  return res.json();
}

function wireRequestForm({ form, errorEl, formView, successView, submitBtn, accessKey, requiredFields, fallbackEmail }) {
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
      const result = await submitToWeb3Forms(form, accessKey);
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

// RATE MODAL
const rateModal = document.getElementById('rateModal');
document.getElementById('openRateModalCta').addEventListener('click', (e) => openModal(rateModal, e.currentTarget));
document.getElementById('closeRateModal').addEventListener('click', closeModal);
rateModal.addEventListener('click', (e) => { if (e.target === rateModal) closeModal(); });
wireRequestForm({
  form: document.getElementById('rateForm'),
  errorEl: document.getElementById('rateError'),
  formView: document.getElementById('rateFormView'),
  successView: document.getElementById('rateSuccessView'),
  submitBtn: document.getElementById('rateSubmitBtn'),
  accessKey: 'YOUR_WEB3FORMS_ACCESS_KEY_RATE',
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

// LETTER / INVOICE MODAL
const letterModal = document.getElementById('letterModal');
document.getElementById('openLetterModalCta').addEventListener('click', (e) => openModal(letterModal, e.currentTarget));
document.getElementById('closeLetterModal').addEventListener('click', closeModal);
letterModal.addEventListener('click', (e) => { if (e.target === letterModal) closeModal(); });
wireRequestForm({
  form: document.getElementById('letterForm'),
  errorEl: document.getElementById('letterError'),
  formView: document.getElementById('letterFormView'),
  successView: document.getElementById('letterSuccessView'),
  submitBtn: document.getElementById('letterSubmitBtn'),
  accessKey: 'YOUR_WEB3FORMS_ACCESS_KEY_LETTER',
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

// SCROLL REVEAL
const io = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
}, { threshold:0.12, rootMargin:'0px 0px -60px 0px' });
document.querySelectorAll('.reveal').forEach(el => io.observe(el));

// VIEW MORE SPEAKERS
(function initViewMoreSpeakers(){
  const btn = document.getElementById('viewMoreSpeakers');
  if (!btn) return;
  const moreCard = btn.closest('.speaker--more');
  const hiddenSpeakers = Array.from(document.querySelectorAll('.speaker--hidden'));
  btn.addEventListener('click', () => {
    hiddenSpeakers.forEach((el, i) => {
      el.classList.remove('speaker--hidden');
      el.classList.add('reveal');
      // Stagger the reveal so the new cards fade/slide in one after another rather than
      // popping in all at once.
      requestAnimationFrame(() => setTimeout(() => el.classList.add('in'), i * 90));
    });
    btn.setAttribute('aria-expanded', 'true');
    moreCard.style.display = 'none';
  });
})();

// STAT COUNT-UP — numbers animate from 0 to their target once the strip scrolls into view
(function initStatCounters(){
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const counters = Array.from(document.querySelectorAll('.stat__num[data-count]'));
  if (!counters.length) return;

  function animateCount(el){
    const target = parseInt(el.dataset.count, 10) || 0;
    const valEl = el.querySelector('.stat__num-val') || el;
    if (reduceMotion) { valEl.textContent = target; return; }
    const duration = 1200;
    const start = performance.now();
    function tick(now){
      const p = Math.min(1, (now - start) / duration);
      const eased = 1 - Math.pow(1 - p, 3); // ease-out cubic
      valEl.textContent = Math.round(target * eased);
      if (p < 1) requestAnimationFrame(tick);
      else valEl.textContent = target;
    }
    requestAnimationFrame(tick);
  }

  const counterIo = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) { animateCount(entry.target); counterIo.unobserve(entry.target); }
    });
  }, { threshold: 0.4 });
  counters.forEach(el => counterIo.observe(el));
})();
