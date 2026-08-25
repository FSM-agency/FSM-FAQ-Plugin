# FSM FAQ Update Broker (Cloudflare Worker)

Serves Plugin Update Checker metadata and the plugin zip so client sites never need a GitHub token.

## Endpoints

| Method | Path | Purpose |
|--------|------|---------|
| GET | `/fsm-faq.json` | PUC metadata |
| GET | `/fsm-faq.zip` | Plugin zip download |
| POST | `/admin/sync` | Upload a packaged zip (`Authorization: Bearer SYNC_SECRET`, header `X-Plugin-Version`) |
| POST | `/admin/sync-from-github` | Pull latest/tag release from GitHub (needs `GITHUB_TOKEN` when private) |
| GET | `/health` | Liveness |

Production URL (after DNS): `https://updates.fullspectrummarketing.com/fsm-faq.json`

## Deploy (FSM Cloudflare account)

```bash
cd update-broker
npm install
npx wrangler login   # or set CLOUDFLARE_API_TOKEN
# Create KV namespace and paste id into wrangler.jsonc:
npx wrangler kv namespace create STORE
npx wrangler secret put SYNC_SECRET
# Optional, for private-repo GitHub pulls:
npx wrangler secret put GITHUB_TOKEN
npx wrangler deploy
```

Map custom domain `updates.fullspectrummarketing.com` to this Worker in the Cloudflare dashboard (or add a `routes` / `domains` entry in `wrangler.jsonc`).

Set GitHub Actions secrets on this repo:

- `FSM_FAQ_BROKER_URL` — e.g. `https://updates.fullspectrummarketing.com`
- `FSM_FAQ_BROKER_SYNC_SECRET` — same value as Worker `SYNC_SECRET`

## Temporary preview (agent / no account yet)

```bash
cd update-broker
npm install
npx wrangler deploy --temporary --var SYNC_SECRET:local-dev-secret
```

Claim the preview within 60 minutes using the Claim URL Wrangler prints, then redeploy with a real account and KV namespace id.

## Manual sync

```bash
chmod +x scripts/package-plugin.sh
BROKER_URL=https://updates.fullspectrummarketing.com \
SYNC_SECRET=… \
node scripts/sync-release.mjs 1.1.0
```

## Cutover reminder

Do **not** make the GitHub repo private until sites are on the bridge plugin version that points at this broker.
