# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project state

`dashboard-toefl` is a **Dashboard Hasil TOEFL Pegawai** for Poltekkes Banjarmasin, built on the Laravel Svelte
starter kit (`laravel/blank-svelte-starter-kit`). **This project does not use Ollama/AI** — it is a data & reporting
dashboard only (any earlier mention of Ollama in project notes is stale).

**Read `docs/analisis-kebutuhan.md` first** — it is the source of truth for business rules, database design, UI
decisions, and a progress checklist. Keep it updated as work continues; do not rely on chat history alone.

Current state (see the doc's checklist for detail): database migrations exist for `pegawai`, `users` (modified —
no email, NIP-based `username`/`role`), `jenis_tes`, `periode_tes`, `import_batches`, `hasil_tes`, `target_skor`.
A design-preview `Dashboard` page exists at route `/dashboard` (`resources/js/pages/Dashboard.svelte`) using sample
data and hand-built UI components (`resources/js/layouts/AppLayout.svelte`, `resources/js/components/*`) — not yet
wired to the database, and not yet using real shadcn-svelte components. No auth, models, seeders, or other pages
have been built yet.

Not a git repository, and there is no README.

## Commands

```powershell
composer setup           # first-time: install, .env, key, migrate, npm install, npm run build
composer dev             # all-in-one dev: artisan serve + queue:listen + pail + npm run dev
composer test            # config:clear -> pint --test -> phpstan -> artisan test  (full local gate)
composer ci:check        # what GitHub Actions runs: vp check, svelte-check, then composer test
composer lint            # pint --parallel (fix PHP style)
composer types:check     # phpstan analyse (larastan, level 7)
npm run check            # vite-plus lint + format check
npm run check:fix        # auto-fix JS/TS/Svelte lint + formatting
npm run types:check      # svelte-check
```

Single test: `php artisan test --filter=SomeTest` or `vendor/bin/pest tests/Feature/ExampleTest.php`.

`composer test` fails on style/static-analysis before it ever runs the suite — run `composer lint` and `npm run check:fix` first when iterating.

## Architecture

**Stack:** Laravel 13 (PHP 8.3) · Inertia 3 · Svelte 5 (runes) · Tailwind 4 · Vite 8 through **vite-plus** (`vp`) · Wayfinder.

**Request flow:** `routes/web.php` → controller returns `Inertia::render('PageName')` → `HandleInertiaRequests` merges shared props (`name`, `auth.user`) → `resources/views/app.blade.php` boots `resources/js/app.ts` → Svelte page resolved from `resources/js/pages/` (paths/extensions configured in `config/inertia.php`).

**Wayfinder is the routing bridge.** The Vite plugin generates typed route/action helpers into `resources/js/routes/`, `resources/js/actions/`, and `resources/js/wayfinder/`. These are **generated — never edit them by hand** (they are excluded from lint and format). Import route helpers from `@/routes` instead of hardcoding URLs; they regenerate when the dev server runs or on build, so add the PHP route first and let the helper appear.

**Conventions:**
- `@/*` maps to `resources/js/*` (tsconfig paths).
- Svelte 5 runes only (`$props`, `$derived`); see `resources/js/components/AppHead.svelte` for the page-title/head pattern used by every page.
- Merge Tailwind classes with `cn()` from `@/lib/utils`.
- Tailwind 4 is CSS-configured in `resources/css/app.css` (`@theme inline`) — there is no `tailwind.config.js`.
- Shared Inertia prop types are declared by augmenting `@inertiajs/core` in `resources/js/types/global.d.ts`; keep it in sync with `HandleInertiaRequests::share()`.
- SSR is enabled in `config/inertia.php`; the bundle is built by `npm run build:ssr` into `bootstrap/ssr/`.

**Tooling config lives in `vite.config.ts`**, not in separate dotfiles: the `lint` block (type-aware, warnings are errors) and the `fmt` block (4-space, single quotes, 80 cols, Tailwind class sorting) drive `vp check`. PHP style is Pint's `laravel` preset (`pint.json`); static analysis is `phpstan.neon` at level 7 over `app/`, `config/`, `database/`, `routes/`, `bootstrap/app.php`.

## Database

The committed `.env` points at **MySQL** (`dashboard_toefl` on 127.0.0.1, user `root`, empty password), while `.env.example` and CI use SQLite. `database/database.sqlite` also exists. Check which connection is live before assuming; `phpunit.xml` forces tests onto in-memory SQLite regardless.

Pest applies `RefreshDatabase` to the **Feature** suite only (`tests/Pest.php`).

## Environment

Windows. The Bash tool here has no coreutils on PATH (`ls`, `find`, `head` all fail) — use PowerShell or the dedicated file tools for shell work.
