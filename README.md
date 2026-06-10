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
