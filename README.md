# Africa Kidney Health Summit 2027 — Official Registration Site

A single-page marketing and delegate-registration site for the Africa Kidney Health
Summit, held **9–11 March 2027** at the KICC, Nairobi, Kenya.

**Live site:** https://omichsam.github.io/africa-kidney-summit/

## What's on the page

- Hero with a live countdown to the summit
- About / partners / focus-area / programme / speakers sections
- "Who should attend" grid and a 3-step "how it works" explainer
- Delegate booking form (client-side validation, no backend — see below)
- Exhibition & partnership enquiry form, opened in a modal
- Venue and contact details, plus a full footer

## Tech stack

Plain **HTML + CSS + vanilla JavaScript** — no framework, no build step, no
dependencies to install. Open [index.html](index.html) in a browser and it works.

**Tailwind CSS** is loaded via the [Play CDN](https://tailwindcss.com/docs/installation/play-cdn)
in `<head>` for use in any new markup, with Preflight (its base-style reset) turned off so it
doesn't alter the existing hand-built design in `css/styles.css`. The Play CDN is fine for this
kind of static site but isn't meant for heavy production use (it compiles in the browser on every
load) — if Tailwind usage grows, switch to the Tailwind CLI or PostCSS build instead.

```
index.html          All page markup and content
css/styles.css       Design tokens + styles for every section/component
js/script.js         Countdown, nav scroll-spy, carousel, forms, modals,
                      stat count-up, scroll-reveal animations
images/               Logo, favicons, venue photo
```

## Running it locally

No build step — just serve the folder:

```bash
python -m http.server 8080
# then open http://localhost:8080/
```

Any static file server works equally well (`npx serve`, VS Code Live Server, etc.).

## Forms

The booking and exhibition-enquiry forms are **front-end only** right now: they
validate input, generate a mock registration reference, and show a confirmation
panel — no data is actually sent anywhere. Wire `js/script.js`'s two `submit`
handlers up to a real backend/email service before going live.

## Deployment & clean URLs

The site is currently deployed on **GitHub Pages**, which serves `index.html` at
the root with no file extension in the URL automatically — nothing extra needed.

Two config files are included for other hosting options (both are inert/ignored on
GitHub Pages):

- [.htaccess](.htaccess) — for **Apache** hosts, hides `.html` from any URL and
  redirects `/index.html` → `/`. Requires `mod_rewrite` enabled and
  `AllowOverride` permitting `.htaccess` on the host.
- [nginx.conf.example](nginx.conf.example) — the same behaviour as a server block,
  for hosts running **Nginx** instead.

## SEO

[robots.txt](robots.txt) and [sitemap.xml](sitemap.xml) both currently reference
`https://www.kidneyhealth.africa/` as the canonical domain — update these (and the
`<meta>`/Open Graph tags in `index.html`) if the site ends up living at a
different URL long-term.
