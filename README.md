# AI Asset Manager untuk Adobe Stock

Aplikasi Laravel + SQLite untuk membuat, mengelola, dan mengotomasi pembuatan gambar dan video menggunakan AI, yang hasilnya siap dijual ke Adobe Stock.

## Fitur Utama

### 1. AI Prompt Generator
- Generate prompt otomatis untuk gambar & video berdasarkan kategori (bisnis, teknologi, lifestyle, dll)
- Custom prompt manual
- Multi bahasa (default: Inggris untuk stock)

### 2. AI Image Generator Integration
- Simpan hasil gambar (path lokal)
- Support batch generate

### 3. AI Video Generator
- Generate video dari prompt atau kumpulan gambar
- Simpan ke lokal

### 4. Automation System
- Auto generate prompt harian
- Auto generate image/video berdasarkan schedule
- Auto rename file sesuai standar Adobe Stock (SEO friendly)
- Generate metadata: title, description, keyword

### 5. Adobe Stock Optimization
- Generate judul (SEO optimized)
- Generate deskripsi
- Generate 49 keyword relevan
- Format sesuai standar Adobe Stock

### 6. Manajemen Asset
- List gambar/video
- Status: draft, ready, uploaded
- Preview asset

### 7. Export System
- Export CSV metadata (untuk upload Adobe Stock)
- Export file terstruktur (ZIP)

## Requirements

- PHP 8.2+
- Composer
- SQLite (terintegrasi)
- FFmpeg (untuk video generation - optional)

## Installasi

```bash
# Clone repository
git clone <repo-url> ai-asset-manager
cd ai-asset-manager

# Install dependencies
composer install

# Setup environment
cp .env.example .env

# Generate key
php artisan key:generate

# Setup SQLite di .env:
DB_CONNECTION=sqlite

# Migrate database
php artisan migrate

# Seed data kategori
php artisan db:seed

# Jalankan server
php artisan serve
```

## API Endpoints

### Categories
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/categories` | List semua kategori |
| POST | `/api/categories` | Tambah kategori |
| GET | `/api/categories/{id}` | Detail kategori |
| PUT | `/api/categories/{id}` | Update kategori |
| DELETE | `/api/categories/{id}` | Hapus kategori |

### Prompts
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/prompts` | List semua prompt |
| POST | `/api/prompts/generate` | Generate prompt otomatis |
| POST | `/api/prompts/generate-batch` | Generate multiple prompts |
| POST | `/api/prompts` | Buat manual prompt |
| POST | `/api/prompts/categories` | Get available categories |

### Assets
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/assets` | List semua asset |
| POST | `/api/assets/generate/image` | Generate gambar |
| POST | `/api/assets/generate/video` | Generate video |
| POST | `/api/assets/generate/batch` | Batch generate |
| POST | `/api/assets/upload` | Upload file |
| POST | `/api/assets/{id}/optimize` | Optimasi Adobe Stock |
| GET | `/api/assets/{id}/metadata` | Get metadata |
| PUT | `/api/assets/{id}/status` | Update status |
| DELETE | `/api/assets/{id}` | Hapus asset |

### Automation
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/automation/schedules` | List schedule |
| POST | `/api/automation/schedules` | Buat schedule |
| POST | `/api/automation/schedules/{id}/run` | Jalankan manual |
| POST | `/api/automation/schedules/{id}/toggle` | Toggle active |
| POST | `/api/automation/run-all` | Jalankan semua |

### Export
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/export/preview` | Preview export |
| GET | `/api/export/csv` | Export CSV |
| GET | `/api/export/json` | Export JSON |
| GET | `/api/export/zip` | Export ZIP |

## Contoh Penggunaan

### Generate Prompt
```bash
curl -X POST http://localhost:8000/api/prompts/generate \
  -H "Content-Type: application/json" \
  -d '{"category":"bisnis","type":"image"}'
```

### Generate Image
```bash
curl -X POST http://localhost:8000/api/assets/generate/image \
  -H "Content-Type: application/json" \
  -d '{"prompt_id":1}'
```

### Optimize untuk Adobe Stock
```bash
curl -X POST http://localhost:8000/api/assets/1/optimize
```

### Export CSV
```bash
curl -X GET http://localhost:8000/api/export/csv -o export.csv
```

## Lisensi

MIT