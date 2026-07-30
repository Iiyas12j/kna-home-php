# Deploy to a DigitalOcean Droplet

## 1. Create the Droplet (DigitalOcean dashboard — do this yourself)

1. **Create → Droplets**
2. Image: **Ubuntu 24.04 (LTS) x64**
3. Plan: **Basic → Regular SSD, 1 GB / 1 vCPU ($6/mo)** — enough for testing
4. Region: **Singapore** (closest to Thailand)
5. Authentication: **SSH Key** → add your existing key (`~/.ssh/id_ed25519.pub`)
6. Hostname: `kna-home-php`
7. Create Droplet, note the **public IP**

## 2. Bootstrap the server

From your Mac:

```bash
# copy the bootstrap script up and run it
scp deploy/setup-droplet.sh root@<DROPLET_IP>:/root/
ssh root@<DROPLET_IP> "bash /root/setup-droplet.sh"
```

This installs Docker, sets up the firewall (ufw: 22/80/443 only), and clones the repo to
`/opt/kna-home-php`. It will stop and tell you to provide `.env` — that's expected, do step 3 next.

## 3. Ship the environment file and seed data

```bash
# fill in DB_PASS and BASE_URL first
cp deploy/.env.production.example deploy/.env.production
# edit deploy/.env.production locally, then:

scp deploy/.env.production root@<DROPLET_IP>:/opt/kna-home-php/.env
scp deploy/seed-data.sql    root@<DROPLET_IP>:/opt/kna-home-php/deploy/seed-data.sql
```

`deploy/seed-data.sql` is a dump of the current local DB (22 doctors, 486 clinics, 4 products,
597 clinic↔product links). It's gitignored — never committed — because it's real business data.

## 4. Start the stack

```bash
ssh root@<DROPLET_IP> "cd /opt/kna-home-php && bash deploy/setup-droplet.sh"
```

Re-running is safe (idempotent). It builds the app image, starts MySQL, waits for it to be
healthy, and imports the seed data on first run.

Visit `http://<DROPLET_IP>/` to confirm.

## 5. Admin login

Same as local dev: `admin@example.com` / `Kna@Admin2026` — **change this password immediately**
once the site is reachable publicly (Admin → Users, or via `/admin/login.php`).

## Known gaps for this test deployment

- **No HTTPS yet** — fine for internal testing via IP. Once you point a real domain at the
  Droplet, add Caddy or `certbot` in front for automatic TLS before sharing the link widely.
- **DB user is `root`** inside the MySQL container — acceptable since it's isolated in its own
  container/network, but create a scoped `kna_app` user before this becomes real production.
- **Outbound email** — the app still uses PHP's `mail()`, which needs a working MTA. Not
  configured by this script; forgot-password emails won't send until that's set up (SMTP
  relay recommended, e.g. Mailgun/SES).
- **Uploads** persist in a Docker named volume (`uploads`) — survives `docker compose restart`
  and rebuilds, but back it up separately (it's not covered by DB backups).

## Updating the site later

```bash
ssh root@<DROPLET_IP> "cd /opt/kna-home-php && git pull && docker compose up -d --build"
```
