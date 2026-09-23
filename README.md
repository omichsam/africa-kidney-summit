# Africa Kidney Health Summit 2027 — Official Registration Site

A single-page marketing and delegate-registration site for the Africa Kidney Health
Summit, held **9–11 March 2027** at the KICC, Nairobi, Kenya.

**Live site:** <https://omichsam.github.io/africa-kidney-summit/>

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
in `<head>` for use in any new markup, under a **`tw-` prefix** (e.g. `tw-flex`, `tw-gap-4`,
`tw-container`). The prefix is required, not optional — `src/styles/styles.css` already defines
classes named `.container` and `.sr-only`, and Tailwind ships utilities with those exact names, so
an unprefixed setup silently breaks the existing layout (Tailwind's `.container` overrides the
site's centred one). Preflight (Tailwind's base-style reset) is also turned off so it doesn't
alter the existing hand-built design. The Play CDN is fine for this kind of static site but isn't
meant for heavy production use (it compiles in the browser on every load) — if Tailwind usage
grows, switch to the Tailwind CLI or PostCSS build instead.

## Project structure

The folders below mirror a typical React/Vite layout (`src/` for source, static SEO/server
files at the root) even though there's no framework or build step — `index.html` just loads
`src/` files by their plain relative path, and a browser resolves that the same way regardless
of the folder name.

```text
index.html                    Entry point — all page markup and content (must stay at the
                               repo root; this is what GitHub Pages/any static host serves)
src/
  styles/styles.css           Design tokens + styles for every section/component
  scripts/script.js           Countdown, nav scroll-spy, carousel, forms, modals,
                               stat count-up, scroll-reveal animations
  assets/images/               Logo, favicons, venue photo
robots.txt, sitemap.xml       Must stay at the root — that's the URL search engines expect
                               (e.g. /robots.txt), and nothing here builds/copies them there
.htaccess, nginx.conf.example Must stay at the root too — Apache/Nginx only read .htaccess
                               from the directory it's serving, which is the repo root
```

## Running it locally

No build step — just serve the folder:

```bash
python -m http.server 8080
# then open http://localhost:8080/
```

Any static file server works equally well (`npx serve`, VS Code Live Server, etc.).

## Forms

Every "Book a delegate place" / "Book" link on the page (nav, hero, Who Should
Attend, the CTA banner and footer) scrolls to the Official Booking section
(`#booking-form`). Only the "Continue to registration" button inside that
section links out to the official external ticketing platform:
<https://apps.little.africa/events/africa-kidney-health-summit>, opened in a
new tab. There is no in-page booking form or mock confirmation anymore.

The exhibition-enquiry form is still **front-end only**: it validates input
and shows a confirmation panel, but no data is actually sent anywhere. Wire
`src/scripts/script.js`'s `submit` handler up to a real backend/email service before
going live.

## Deployment & clean URLs

The site is currently deployed on **GitHub Pages**, which serves `index.html` at
the root with no file extension in the URL automatically — nothing extra needed.

Two config files are included for other hosting options (both are inert/ignored on
GitHub Pages):

- [.htaccess](.htaccess) — for **Apache** hosts (this includes Namecheap cPanel),
  hides `.html` from any URL and redirects `/index.html` → `/`. Requires
  `mod_rewrite` enabled and `AllowOverride` permitting `.htaccess` on the host —
  both are on by default on Namecheap shared hosting.
- [nginx.conf.example](nginx.conf.example) — the same behaviour as a server block,
  for hosts running **Nginx** instead.

### CI/CD to Namecheap cPanel

**Primary: [deploy-cpanel-ssh.yml](.github/workflows/deploy-cpanel-ssh.yml)** —
rsyncs the site to a cPanel host over SSH on every push to `main` (or manually
via the Actions tab). Requires SSH Access enabled on the hosting plan (cPanel →
Security → SSH Access) and these repo secrets — **Settings → Secrets and
variables → Actions → New repository secret**:

| Secret | Value | Required |
| --- | --- | --- |
| `SSH_HOST` | Server hostname, e.g. `premium356.web-hosting.com` | yes |
| `SSH_PORT` | SSH port — Namecheap shared hosting is typically `21098`, not 22 | yes |
| `SSH_USERNAME` | cPanel username | yes |
| `SSH_PRIVATE_KEY` | Full contents of the private key generated in cPanel → SSH Access → Manage SSH Keys (must be **authorized** there first, and generated with no passphrase) | yes |
| `SSH_TARGET_DIR` | Absolute path to the site's document root, e.g. `/home/username/yourdomain.com/public_html/` | yes |

**Fallback: [deploy-cpanel.yml](.github/workflows/deploy-cpanel.yml)** — the
same deploy over FTP/FTPS, manual-only (`workflow_dispatch`) so it doesn't also
fire on every push. Useful if SSH access ever stops working. Needs its own
secrets — `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`, and optionally
`FTP_PROTOCOL` (default `ftps`), `FTP_PORT` (default `21`), `FTP_SERVER_DIR`
(default `/public_html/`) — found in cPanel under **Files → FTP Accounts**.

Both workflows are inert until their secrets are set. Once set, push to `main`
and check the **Actions** tab on GitHub for the deploy run.

## SEO

[robots.txt](robots.txt) and [sitemap.xml](sitemap.xml) both currently reference
`https://www.kidneyhealth.africa/` as the canonical domain — update these (and the
`<meta>`/Open Graph tags in `index.html`) if the site ends up living at a
different URL long-term.
