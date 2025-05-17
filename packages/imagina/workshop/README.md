# Workshop Package

The **Imagina Workshop** package is a Laravel 12 CLI toolkit designed to help developers scaffold modular packages.

---

## 🚀 Features

* Create new Laravel packages via `workshop:make-package`
* Scaffold models, controllers, repositories, permissions, migrations, translations, and bindings via `workshop:make-model`
* Uses customizable stub files for flexible code generation
* Encourages modular, DDD-style architecture
---

## 🛠 Commands

### `php artisan workshop:make-package {name}`

Creates a new Laravel package under `packages/imagina/{name}` with DDD folder structure:

```
src/
├── Config/
├── Database/
├── Entities/
├── Http/
├── Providers/
├── Repositories/
├── Transformers/
```

Includes:

* `composer.json`
* `config.php` from stub
* `routes/web.php` from stub
* `ModuleServiceProvider.php` from stub

---

### `php artisan workshop:make-model {package} {model}`

Adds a new model (e.g. `Post`) to an existing package (e.g. `iblog`).

Generated components:

* Entity: `Entities/Post.php`
* Translation: `Entities/PostTranslation.php`
* Controller: `Http/Controllers/PostController.php`
* Repository: interface + implementation
* Migration: `create_iblog_posts_table`
* Translation migration: `create_iblog_post_translations_table`
* Config permission entry
* ServiceProvider repository binding

All files use customizable stubs.

---

## 📁 Stubs

Stub templates are located in:

```
packages/imagina/workshop/src/stubs/
```

You can override or extend these to customize your generated files.

Example stub names:

* `4-entity-eloquent.stub`
* `8-repository-interface.stub`
* `3-create-table-migration.stub`
* `2-permissions-append.stub`

---

## 🧠 License

MIT License.

Crafted with ❤️ by Imagina Devs.
