# 🚀 Imagina Backend — Local Development

This project is a modular Laravel backend powered by Docker.

## 🧰 Requirements

- [Docker](https://www.docker.com/get-started) installed and running
- No need for Composer, PHP, or Node on your host machine

---

## 🔧 First-Time Setup

If you're running the project for the first time on a new device:

1. Make sure the init scripts are executable:

```bash
chmod +x scripts/init.sh
chmod +x scripts/bash.sh
```

2. Then run the setup script:

```bash
./scripts/init.sh
```

This will:

- Copy `.env.example` to `.env` if it doesn't exist
- Build and start Docker containers
- Install Composer dependencies inside the container
- Generate the Laravel app key
- Run database migrations
- Link the Laravel `storage` folder
- Drop you into the container for development

---

## 💻 Daily Workflow

To start working after setup:

```bash
./scripts/bash.sh
```

This opens a terminal inside the running Laravel container.

If your containers are not running, start them first:

```bash
docker-compose up -d
```

---

## 🐳 Common Docker Commands

| Action              | Command                              |
|---------------------|---------------------------------------|
| Start containers    | `docker-compose up -d`               |
| Stop containers     | `docker-compose down`                |
| Rebuild containers  | `docker-compose up -d --build`       |
| Access app shell    | `docker exec -it base_backend_app bash` |

---

## ✅ Tips

- Add more scripts (e.g. `migrate.sh`, `seed.sh`) in the `scripts/` folder for shared workflows.
- Keep `.env` values in sync with your Docker setup (DB host = `mysql`, port = `3306`).
- Run `php artisan` commands inside the container.

---

Happy building! 🚀
