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

**Live at <https://kidneyhealth.africa/>.** [deploy-cpanel.yml](.github/workflows/deploy-cpanel.yml)
pushes the site to cPanel over FTP/FTPS. It's **manual-only** (`workflow_dispatch`)
— pushing to `main` does *not* auto-deploy, so changes can be reviewed first.
To actually go live: **Actions tab → "Deploy to Namecheap cPanel" → Run workflow**
(or `gh workflow run deploy-cpanel.yml`). Uses these repo secrets — **Settings →
Secrets and variables → Actions**:

| Secret | Value | Required |
| --- | --- | --- |
| `FTP_SERVER` | cPanel server hostname, e.g. `premium356.web-hosting.com` (check cPanel's Server Information panel — `ftp.yourdomain.com` can silently fail if DNS isn't set up for it) | yes |
| `FTP_USERNAME` | A dedicated FTP account's login (cPanel → Files → FTP Accounts), e.g. `deploy@yourdomain.com` | yes |
| `FTP_PASSWORD` | That FTP account's password | yes |
| `FTP_PROTOCOL` | `ftps` (default) or `ftp` if your host doesn't support FTPS | no |
| `FTP_PORT` | `21` (default) | no |
| `FTP_SERVER_DIR` | Where the site lives, relative to what that FTP account is scoped to. If the account's own "Directory" field in cPanel already points straight at the live document root, use `/` here | yes |

**Important:** create the FTP account's "Directory" pointed at your domain's
actual **document root** — check cPanel → **Domains** for the exact path
first. A domain that looks like an addon domain can still have its account's
home folder as the real root (e.g. `/home/user/public_html`, not
`/home/user/yourdomain.com/public_html`) — using the wrong one silently
uploads the site somewhere nobody visits.

An SSH/rsync-based pipeline was tried first but abandoned — this host's SSH
Access page only manages keys for cPanel's own Git feature, not a real
external SSH/SFTP login, so authentication never succeeded.

## SEO

Canonical domain is `https://kidneyhealth.africa/` (no `www`) — consistent across
the `<link rel="canonical">`, Open Graph/Twitter tags, the JSON-LD `Event`
schema, `robots.txt`'s `Sitemap:` line and `sitemap.xml`'s `<loc>`.
[.htaccess](.htaccess) 301-redirects `www.kidneyhealth.africa` to the bare
domain so both never get indexed as separate pages. If the canonical domain
ever changes, update all of those in one pass — they're required to match.
