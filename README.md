## Analisis Sentimen Code-Mixed (Indonesia–Inggris)

Aplikasi ini menggabungkan Laravel (PHP) sebagai antarmuka web dan backend aplikasi dengan layanan FastAPI (Python) untuk melakukan analisis sentimen pada teks code-mixed Indonesia–Inggris. Proyek ini cocok untuk eksplorasi NLP ringan, demo analisis sentimen, maupun proof-of-concept integrasi lintas bahasa pemrograman.

### Tujuan
- Mengelola sampel teks dan menjalankan analisis sentimen melalui UI Laravel.
- Mendukung analisis satuan maupun batch (bulk) pada teks.
- Menyediakan layanan REST API (FastAPI) untuk inferensi model Transformer.

### Fitur Utama
- Antarmuka web Laravel: dashboard, halaman analitik, dan modul CRUD untuk sampel teks.
- Analisis sentimen via FastAPI menggunakan model Transformer (Hugging Face), dengan penyesuaian untuk teks faktual/neutral.
- Analisis batch: unggah/beberapa input untuk diproses sekaligus.
- Health check API untuk memantau ketersediaan layanan model.
- Basis data SQLite bawaan dan migrasi siap pakai.

## Arsitektur Singkat
- Laravel (PHP 8.2): routing, UI Blade, controller bisnis, penyimpanan hasil analisis.
  - Rute utama (lihat `routes/web.php`):
    - `GET /` → Dashboard
    - `GET /analytics` → Halaman analitik
    - `resource /sentiment-analysis` → CRUD `SentimentAnalysis`
    - `POST /sentiment-analysis/bulk-analyze` → Analisis batch
- FastAPI (Python): layanan inference model (`fastapi_bert_service.py`).
  - Endpoint:
    - `GET /` dan `GET /health` → Health check
    - `POST /analyze` → Analisis sentimen

Komunikasi dilakukan melalui permintaan HTTP dari Laravel ke FastAPI. Secara default, FastAPI berjalan pada `http://localhost:8001`.

## Prasyarat
- PHP ≥ 8.2, Composer
- Node.js ≥ 18 (untuk Vite dev server dan asset build)
- Python ≥ 3.10 (disarankan venv)
- Git (opsional, untuk cloning)

## Instalasi
1) Clone repositori
```bash
git clone <url-repo-anda>
cd Code_Mixed
```

2) Backend Laravel
```bash
composer install
cp .env.example .env
php artisan key:generate

# Siapkan database SQLite (jika belum ada)
mkdir -p database
type NUL > database\database.sqlite   # Di Windows PowerShell

php artisan migrate
```

3) Frontend assets
```bash
npm install
```

4) Layanan FastAPI (Python)
```bash
python -m venv venv
venv\Scripts\activate  # Windows
pip install -r requirements.txt
```

Catatan: Unduhan model Transformer dari Hugging Face akan terjadi saat pertama kali layanan dijalankan.

## Cara Menjalankan

Anda dapat menjalankan tiap layanan secara terpisah atau menggunakan skrip pengembangan.

1) Jalankan FastAPI (port 8001)
```bash
venv\Scripts\activate
uvicorn fastapi_bert_service:app --host 0.0.0.0 --port 8001 --reload
```

2) Jalankan Laravel (HTTP server) dan Vite (dev)
```bash
php artisan serve
npm run dev
```

Atau gunakan skrip dev Composer (jika Node tersedia) untuk beberapa proses sekaligus:
```bash
composer run dev
```
Skrip ini akan mencoba menjalankan: server Laravel, queue listener, log pail, dan Vite.

## Contoh Penggunaan

### Melalui UI Laravel
- Buka `http://localhost:8000` untuk Dashboard.
- Navigasi ke modul `Sentiment Analysis` untuk membuat sampel teks, menjalankan analisis satuan, atau mengunggah input untuk analisis batch.
- Halaman `Analytics` tersedia di `http://localhost:8000/analytics`.

### Melalui API FastAPI
Endpoint utama:
- `GET http://localhost:8001/` atau `/health` → Health check.
- `POST http://localhost:8001/analyze` → Analisis sentimen.

Contoh permintaan `curl`:
```bash
curl -X POST http://localhost:8001/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "text": "Aku happy banget today, even though the weather is bad",
    "source_type": "user_input"
  }'
```

Respons contoh (diringkas):
```json
{
  "processed_text": "aku happy banget today even though the weather is bad",
  "sentiment": "positive",
  "confidence_score": 0.91,
  "sentiment_scores": {"positive": 0.91, "negative": 0.03, "neutral": 0.06},
  "language_breakdown": {"indonesian": 0.42, "english": 0.58, "mixed": 0.0},
  "analysis_method": "sentiment_analysis_nlptown_bert-base-multilingual-uncased-sentiment",
  "analysis_timestamp": "2025-10-14T12:34:56",
  "source_type": "user_input"
}
```

Catatan:
- Model yang digunakan saat ini: `nlptown/bert-base-multilingual-uncased-sentiment` (fallback akan menjaga aplikasi tetap responsif bila model tidak tersedia).
- Logika aplikasi menyesuaikan skor untuk pernyataan faktual agar cenderung netral ketika layak.

## Dependensi

### PHP (Composer)
Tertentu pada `composer.json`:
- `laravel/framework` (12.x)
- `laravel/tinker`

Pengembangan:
- `phpunit/phpunit`, `laravel/pint`, `laravel/sail`, `nunomaduro/collision`, dll.

### Python (`requirements.txt`)
- `transformers`, `torch`, `numpy`, `scikit-learn`, `sentencepiece`, `protobuf`
- `fastapi`, `uvicorn[standard]`, `pydantic`

## Basis Data
Proyek dikonfigurasi untuk SQLite secara default. File DB berada di `database/database.sqlite`. Migrasi tersedia di `database/migrations`. Seeder contoh: `database/seeders/SentimentAnalysisSeeder.php` (opsional dijalankan sesuai kebutuhan).

## Rute Laravel Ringkas
Lihat `routes/web.php` dan controller terkait:
- `GET /` → `DashboardController@index`
- `GET /analytics` → `DashboardController@analytics`
- `resource /sentiment-analysis` → `SentimentAnalysisController`
- `POST /sentiment-analysis/bulk-analyze` → `SentimentAnalysisController@bulkAnalyze`

## Lisensi
Proyek ini berada di bawah lisensi MIT sebagaimana tercantum di `composer.json`.

---

Jika Anda mengalami masalah saat menjalankan model (mis. dependensi Transformer tidak tersedia), layanan akan memberikan fallback/health info. Pastikan versi Python cocok dan ruang disk/akses internet cukup untuk mengunduh model.

