# piGardenWeb — updated fork

Web control panel to manage your **piGarden** irrigation system (solenoid valves driven by a Raspberry Pi).

This is a fork of **[lejubila/piGardenWeb](https://github.com/lejubila/piGardenWeb)**, kept functionally the same but moved off an end-of-life stack and made deployable with Docker. All the original design and features are the upstream author's work — see [Credits](#credits).

## What's different from upstream

| | upstream | this fork |
|---|---|---|
| Laravel | 5.8 *(EOL 2019)* | **11** |
| Backpack | 3.6 | **6** |
| PHP | 7.1 | **8.2+** (developed/tested on 8.3) |
| PHPUnit | 7 | 11 |
| Deployment | on the Pi (nginx + php-fpm) | **Docker** (any host/NAS) — or still on the Pi |

Also included:

- **Security fixes** — closed a mass-assignment hole in the api-token form, added validation of zone names before they reach piGarden's line-based socket protocol, input validation on the log API (and removal of a state-changing `GET` route), globally-unique API tokens.
- **Reliability fixes** — the socket client no longer leaks connections on error paths, has real connect/read timeouts (an unreachable Pi used to block every page for ~60s), and fails fast on repeat calls within a request. Two crashes that only triggered in production (dev-only `Debugbar` calls on the error path) were removed.
- **A small test suite** — characterization tests pinning the cron parsing and the socket command protocol, so future upgrades have a safety net.

Full details are in the commit history.

## Architecture

The panel talks to piGarden over its TCP socket server, so **it does not have to run on the Raspberry Pi**. The common setup with this fork is to keep piGarden on the Pi (it drives the GPIO/valves) and run the web panel elsewhere — a NAS, a home server, a mini PC:

```
┌─────────── NAS / server ───────────┐          ┌──────── Raspberry Pi ────────┐
│  Docker: piGardenWeb (Laravel 11)  │   TCP    │  piGarden socket server       │
│  http://<host>:8080                │─────────▶│  <pi-ip>:8084 → solenoids     │
└────────────────────────────────────┘          └───────────────────────────────┘
```

Running everything on the Pi still works — it just needs PHP 8.2+.

## Quick start (Docker — recommended)

Full instructions, including the **Portainer** flow and the required Raspberry Pi changes, are in **[docker/README.md](docker/README.md)**.

```bash
git clone https://github.com/valeriob1/piGardenWeb.git
cd piGardenWeb
# edit docker-compose.yml (or set the env vars in your Docker UI):
#   PIGARDEN_SOCKET_CLIENT_IP / _PORT / _USER / _PWD, APP_URL, WEB_PORT, TZ
docker compose up -d --build
```

Open `http://<host>:8080`. Migrations, the app key and the permission rows are created automatically on first start; data lives in the `pigardenweb_storage` and `pigardenweb_uploads` volumes.

### On the Raspberry Pi

piGarden's socket server listens on `127.0.0.1` by default, so a remote panel can't reach it. In `/etc/piGarden.conf`:

```sh
TCPSERVER_IP="0.0.0.0"
TCPSERVER_USER="pigarden"          # strongly recommended once it's on the LAN
TCPSERVER_PWD="a-strong-password"  # must match PIGARDEN_SOCKET_CLIENT_* 
```

then `./piGarden.sh start_socket_server force`.

> ⚠️ Anyone who can reach `pi-ip:8084` can open and close your valves. Set the credentials and keep that port on your LAN — never port-forward it.

### First admin user

Registration is closed by default. From the container shell:

```php
php artisan tinker
>>> $u = App\Models\BackpackUser::firstOrCreate(
        ['email' => 'you@example.com'],
        ['name' => 'Admin', 'password' => bcrypt('your-password')]
    );
>>> $u->givePermissionTo(
        Spatie\Permission\Models\Permission::where('guard_name', backpack_guard_name())->get()
    );
```

Pass the permission **objects** as shown — passing their names as strings resolves them against the wrong guard and fails.

## Manual install (without Docker)

Requires **PHP 8.2+** (with `gd`, `zip`, `pdo_sqlite`, `intl`, `mbstring`), a web server pointing at `public/`, and Composer.

```bash
git clone https://github.com/valeriob1/piGardenWeb.git
cd piGardenWeb
composer install --no-dev --optimize-autoloader   # vendor/ is committed, so this is optional
cp .env.example .env
php artisan key:generate
touch database/database.sqlite      # then set DB_DATABASE to its absolute path in .env
php artisan migrate
php artisan db:seed --class=PermissionsSeeder
php artisan storage:link
```

Point `PIGARDEN_SOCKET_CLIENT_IP/_PORT/_USER/_PWD` in `.env` at your piGarden, make `storage/` and `bootstrap/cache/` writable by the web user, then create the admin user as shown above.

Sample nginx vhosts are in [virtualhost/](virtualhost/) (written for older PHP versions — adjust the `fastcgi_pass` socket).

## Known limitations vs upstream

Backpack moved several features into its paid **Pro** package between v3 and v6. Rather than add a paid dependency, they were removed:

- **Log page filters** (date range, type, level, client IP)
- **Bulk delete** on the log list

The log list itself still works (ordering, pagination, the refresh button). Both come back if you add `backpack/pro`, or they can be reimplemented by hand.

## Status & caveats

This fork runs a real garden, but be aware:

- The automated tests cover the socket/cron logic and that pages render — **not** the full admin flows. Valve control, scheduling and the hardware side were verified by hand.
- Always test valve open/close and your schedules after deploying, ideally while you can watch the system.
- Screenshots below are from the upstream project; the current UI is Backpack 6 and looks slightly different.

## Screenshots

![piGardenWeb Dashboard](https://www.lejubila.net/wp-content/uploads/2021/10/piGardeWeb_620_dashboard.png)

![piGardenWeb Zone setting](https://www.lejubila.net/wp-content/uploads/2020/09/piGardenWeb_600_zone.png)

![piGardenWeb Customize icons](https://www.lejubila.net/wp-content/uploads/2020/09/piGardenWeb_600_icons.png)

![piGardenWeb Manage users and permissions](https://www.lejubila.net/wp-content/uploads/2020/09/piGardenWeb_600_user_and_permission.png)

## Credits

- Original project and design: **Davide Bigagli** — [lejubila/piGardenWeb](https://github.com/lejubila/piGardenWeb)
- The piGarden controller itself: [lejubila/piGarden](https://github.com/lejubila/piGarden)
- Documentation and build guides for the whole irrigation system: [www.lejubila.net/tag/pigarden/](http://www.lejubila.net/tag/pigarden/)

## License

GNU General Public License v3.0, as the original project.
