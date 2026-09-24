# Product Management REST API

REST API Laravel untuk manajemen produk dan autentikasi user.

## Fitur

- REST API produk: list, detail, create, update, delete.
- Filter produk berdasarkan `search`, `category`, `limit`, dan `page`.
- REST API auth: register dan login.
- Protected endpoint menggunakan Bearer token.
- Rate limit:
  - Auth register/login: maksimal 3 request per 60 detik.
  - Product create/update/delete: maksimal 1 request per 5 detik.
- CORS aktif untuk semua origin agar aplikasi web dari berbagai domain dapat mengakses API.
- Docker support dengan auto migrate dan seed.
- Dokumentasi API tersedia via OpenAPI/Swagger file `openapi.yaml`.

## Menjalankan Dengan Docker

Pastikan Docker Desktop sudah berjalan, lalu jalankan perintah berikut dari root project:

```bash
docker compose up --build
```

Saat container start, aplikasi otomatis menjalankan:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan serve --host=0.0.0.0 --port=8000
```

Aplikasi dapat diakses di:

```text
http://localhost:8000
```

## Data Seeder Untuk Testing

Seeder membuat data berikut:

```text
Username: jhon_doe
Password: supersecret
Bearer token: dev-auth-token
```

Produk sample:

- Awesome T-Shirt
- Wireless Mouse
- Clean Code Book

## Contoh Penggunaan API

### List Produk

```bash
curl http://localhost:8000/api/products
```

### Filter Produk

```bash
curl "http://localhost:8000/api/products?search=shirt&category=Clothes&limit=10&page=1"
```

### Detail Produk

```bash
curl http://localhost:8000/api/products/1
```

### Register

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"username":"new_user","password":"supersecret","password_confirmation":"supersecret"}'
```

### Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"jhon_doe","password":"supersecret"}'
```

### Create Produk

```bash
curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer dev-auth-token" \
  -d '{"title":"New Product","price":120000,"description":"Test product","category":"Clothes","images":["https://placehold.co/640x480"]}'
```

### Update Produk

```bash
curl -X PUT http://localhost:8000/api/products/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer dev-auth-token" \
  -d '{"title":"Updated Product","price":150000,"category":"Clothes","images":["https://placehold.co/640x480"]}'
```

### Delete Produk

```bash
curl -X DELETE http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer dev-auth-token"
```

## Endpoint API

| Method | Endpoint | Auth | Deskripsi |
| --- | --- | --- | --- |
| GET | `/api/products` | Tidak | Mengambil semua produk, mendukung filter dan pagination. |
| GET | `/api/products/{id}` | Tidak | Mengambil detail produk. |
| POST | `/api/products` | Ya | Membuat produk baru. |
| PUT | `/api/products/{id}` | Ya | Mengubah produk. |
| DELETE | `/api/products/{id}` | Ya | Menghapus produk. |
| POST | `/api/auth/register` | Tidak | Register user baru. |
| POST | `/api/auth/login` | Tidak | Login dan mendapatkan token. |

## CORS

CORS dikonfigurasi di `config/cors.php` dengan:

```php
'allowed_origins' => ['*'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

Dengan konfigurasi ini, aplikasi web dari berbagai domain dapat berinteraksi dengan API.

## Dokumentasi API Swagger/Postman

Dokumentasi API tersedia di file:

```text
openapi.yaml
```

Cara menggunakan:

- Swagger Editor: buka https://editor.swagger.io lalu import file `openapi.yaml`.
- Postman: pilih `Import` lalu masukkan file `openapi.yaml`.

## Testing

Jalankan test suite:

```bash
php artisan test --compact
```

## Reset Database Docker

Jika ingin mengulang database dari awal:

```bash
docker compose down
del database\database.sqlite
docker compose up --build
```

Untuk macOS/Linux, gunakan:

```bash
rm database/database.sqlite
```

## URL Public Remote Repository

Isi URL repository publik setelah project di-push ke GitHub/GitLab/Bitbucket:

```text
https://github.com/username/library-api
```

Catatan: folder project saat ini belum terdeteksi sebagai Git repository, jadi URL remote publik belum tersedia dari environment lokal ini.
