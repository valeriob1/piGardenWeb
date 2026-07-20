# Running piGardenWeb in Docker (on a NAS)

This runs the **web control panel** in a container on your NAS, while
**piGarden stays on the Raspberry Pi** driving the solenoid valves. The
container talks to piGarden over its TCP socket server.

```
┌─────────────── NAS ───────────────┐        ┌──────── Raspberry Pi ────────┐
│  Docker: pigardenweb (Laravel 11)  │  TCP   │  piGarden socket server       │
│  http://<nas-ip>:8080              │───────▶│  <pi-ip>:8084 (elettrovalvole)│
└────────────────────────────────────┘        └───────────────────────────────┘
```

## 1. Prepare the Raspberry Pi (piGarden side)

By default piGarden's socket server listens on `127.0.0.1`, so the NAS can't
reach it. In **`/etc/piGarden.conf`** on the Pi:

```sh
TCPSERVER_IP="0.0.0.0"          # or the Pi's LAN IP, so the NAS can connect
TCPSERVER_PORT="8084"
# STRONGLY RECOMMENDED now that the socket is reachable over the LAN:
TCPSERVER_USER="pigarden"
TCPSERVER_PWD="choose-a-strong-password"
```

Then restart the socket server on the Pi:

```sh
./piGarden.sh stop_socket_server
./piGarden.sh start_socket_server force
```

> ⚠️ Anyone who can reach `pi-ip:8084` can open/close valves. Set
> `TCPSERVER_USER`/`TCPSERVER_PWD` and keep the port on your LAN only (don't
> port-forward it from the internet).

## 2. Configure the container

Edit **`docker-compose.yml`** and set at least:

| Variable | Set to |
|---|---|
| `APP_URL` | how you reach the panel, e.g. `http://<nas-ip>:8080` |
| `PIGARDEN_SOCKET_CLIENT_IP` | the Raspberry Pi's LAN IP |
| `PIGARDEN_SOCKET_CLIENT_PORT` | `8084` (or your port) |
| `PIGARDEN_SOCKET_CLIENT_USER` / `_PWD` | must match `TCPSERVER_USER`/`_PWD` on the Pi |
| `TIMEZONE` / `PIGARDEN_TZ` | your timezone |

## 3. Build & start (on the NAS)

The NAS needs Docker + Docker Compose. From the project folder:

```sh
docker compose up -d --build
```

Open **`http://<nas-ip>:8080`**. First start runs the DB migrations and
generates an app key automatically (persisted in the `pigardenweb_storage`
volume). Watch the logs the first time:

```sh
docker compose logs -f web
```

### Create the first admin user

Registration is closed by default (`BACKPACK_REGISTRATION_OPEN=false`). Either
set it to `true` temporarily to register through the UI, or create a user via
tinker:

```sh
docker compose exec web php artisan tinker
>>> $u = new App\Models\BackpackUser(); $u->name='Admin'; $u->email='you@example.com'; $u->password=bcrypt('your-password'); $u->save();
```

Then assign roles/permissions from the admin area (Users / Roles).

## Updating

```sh
git pull
docker compose up -d --build
```

Your data (SQLite DB, uploaded icons, sessions, app key) lives in the named
volumes `pigardenweb_storage` and `pigardenweb_uploads` and survives rebuilds.

## Notes & limitations

- **Architecture:** the image is based on the official multi-arch
  `php:8.3-apache`, so it builds on both x86-64 and ARM NAS boxes. Build on the
  NAS (as above) to match its architecture.
- **Persistence:** the SQLite database is stored at
  `storage/app/database.sqlite` inside the `pigardenweb_storage` volume. Back up
  that volume to keep your logs/icons/users.
- **HTTPS:** this serves plain HTTP on port 80 (mapped to 8080). Put it behind
  your NAS reverse proxy (Synology/QNAP) if you want HTTPS, and set `APP_URL`
  accordingly.
- **Log-page filters** were removed during the Backpack 6 upgrade (they became a
  paid "Pro" feature); the log list itself still works.
