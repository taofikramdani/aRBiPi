# aRBiPi - Rumah Belajar Pintar

Platform tryout dan pembelajaran online berbasis AI untuk mendukung SDG 4 Pendidikan Berkualitas. Dibangun dengan Laravel 12, Blade, Tailwind CSS, MySQL, Breeze, Spatie Permission, dan Hugging Face Inference Providers.

Fitur siswa mencakup **aRBi Assistant**, chatbot belajar kontekstual yang menyimpan riwayat percakapan dan menggunakan hasil tryout siswa sebagai konteks jawaban.

## Instalasi

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Atur koneksi MySQL dan Hugging Face pada `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=arbipi
DB_USERNAME=root
DB_PASSWORD=

HUGGINGFACE_TOKEN=hf_token_anda
HUGGINGFACE_MODEL=openai/gpt-oss-120b:novita
HUGGINGFACE_API_URL=https://router.huggingface.co/v1/chat/completions
```

Jalankan aplikasi:

```bash
php artisan migrate --seed
npm run build
composer run dev
```

## Akun Demo

- Admin: `admin@arbipi.id` / `password`
- Siswa: `siswa@arbipi.id` / `password`

Tanpa Hugging Face token, fitur rekomendasi otomatis menggunakan fallback lokal. ERD tersedia di [docs/ERD.md](docs/ERD.md).

## Docker Lokal

Jalankan Docker Desktop, lalu gunakan image lokal:

```bash
docker build -t arbipi:local .
docker compose -f docker-compose.yml -f docker-compose.local.yml up -d
docker exec arbipi_app php artisan migrate --force
```

Pastikan `.env` berisi `ECR_IMAGE=arbipi:local` dan `APP_PORT=8080`. Override lokal otomatis memakai `host.docker.internal` untuk mengakses MySQL XAMPP tanpa container MySQL.

## Azure Blob Storage

Untuk menyimpan upload materi ke Azure Blob dan URL-nya ke database:

```env
MATERIAL_FILESYSTEM_DISK=azure
AZURE_STORAGE_CONNECTION_STRING=
AZURE_STORAGE_CONTAINER=
AZURE_STORAGE_PREFIX=materials
AZURE_STORAGE_URL=https://nama-akun.blob.core.windows.net/nama-container
AZURE_STORAGE_PUBLIC=true
```
