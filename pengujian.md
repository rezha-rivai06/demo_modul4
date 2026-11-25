# Panduan Pengujian Endpoint FlipPlay REST API (Demo Modul 4)

Dokumen ini disusun berurutan sesuai **Alur yang Diharapkan** dan **Pengujian** pada Modul 4 (halaman 29–30). Setiap langkah dirancang agar dapat langsung diikuti di **Postman** (atau [api_test.http](api_test.http)) secara berurutan dari atas ke bawah tanpa bentrok ID.

---

## 0. Persiapan Terminal (Jalankan Sebelum Mulai Menguji)

Buka Terminal Laragon di folder `demo_modul4`, lalu jalankan secara berurutan:

```cmd
php artisan migrate:fresh --seed
php artisan route:list --path=api
php artisan serve
```

> **Data Awal Hasil Seeder (`migrate:fresh --seed`):**
> - **Users:** `1` = `Alice` (sudah memiliki media `1` dan `4` di koleksinya), `2` = `Bob` (koleksi masih kosong)
> - **Media:**
>   - `1` = `Sousou no Frieren` (`anime`)
>   - `2` = `Shingeki no Kyojin` (`anime`)
>   - `3` = `Spirited Away` (`movie`)
>   - `4` = `Interstellar` (`movie`)

---

## Bagian 1: Pengujian Happy Path (Alur Sukses — Berurutan 1 s/d 10)

### 1. Menambahkan Media Baru — Tipe `movie`
* **Method:** `POST`
* **URL:** `http://127.0.0.1:8000/api/media`
* **Headers:**
  * `Accept`: `application/json`
  * `Content-Type`: `application/json`
* **Body (`raw` -> `JSON`):**
```json
{
    "title": "Inception",
    "type": "movie",
    "genre": "Sci-Fi, Action",
    "rating": 8.8,
    "release_year": 2010,
    "duration_minutes": 148,
    "director": "Christopher Nolan"
}
```
* **Output yang Diharapkan:** Status **`201 Created`**, data `Inception` tersimpan dengan **`"id": 5`**, serta field `duration_minutes` dan `director` tampil.

---

### 2. Menambahkan Media Baru — Tipe `anime`
* **Method:** `POST`
* **URL:** `http://127.0.0.1:8000/api/media`
* **Headers:**
  * `Accept`: `application/json`
  * `Content-Type`: `application/json`
* **Body (`raw` -> `JSON`):**
```json
{
    "title": "Fullmetal Alchemist: Brotherhood",
    "type": "anime",
    "genre": "Action, Adventure",
    "rating": 9.2,
    "release_year": 2009,
    "total_episodes": 64,
    "studio": "Bones"
}
```
* **Output yang Diharapkan:** Status **`201 Created`**, data tersimpan dengan **`"id": 6`**, serta field `total_episodes` dan `studio` tampil.

---

### 3. Menampilkan Daftar Seluruh Media di FlipPlay
* **Method:** `GET`
* **URL:** `http://127.0.0.1:8000/api/media`
* **Headers:**
  * `Accept`: `application/json`
* **Body:** `none`
* **Output yang Diharapkan:** Status **`200 OK`**, menampilkan 6 data media dalam array `data` lengkap dengan metadata pagination dan relasi `users` (eager loaded).

---

### 4. Menampilkan Detail 1 Media Beserta Pengguna yang Mengoleksinya
* **Method:** `GET`
* **URL:** `http://127.0.0.1:8000/api/media/1`
* **Headers:**
  * `Accept`: `application/json`
* **Body:** `none`
* **Output yang Diharapkan:** Status **`200 OK`**, menampilkan detail `Sousou no Frieren` beserta array `"users"` yang berisi `Alice`.

---

### 5. Mencari Media Berdasarkan Judul (Query Filter pada Endpoint `index`)
* **Method:** `GET`
* **URL:** `http://127.0.0.1:8000/api/media?search=Frieren` *(bisa juga memakai `?title=Frieren`)*
* **Headers:**
  * `Accept`: `application/json`
* **Body:** `none`
* **Output yang Diharapkan:** Status **`200 OK`**, hanya mengembalikan 1 media yang judulnya mengandung `"Frieren"`.

---

### 6. Memperbarui Data Media (`PATCH` Sebagian Field)
* **Method:** `PATCH`
* **URL:** `http://127.0.0.1:8000/api/media/5`
* **Headers:**
  * `Accept`: `application/json`
  * `Content-Type`: `application/json`
* **Body (`raw` -> `JSON`):**
```json
{
    "rating": 9.0,
    "genre": "Sci-Fi, Thriller"
}
```
* **Output yang Diharapkan:** Status **`200 OK`**, `rating` dan `genre` pada `Inception` (ID 5) berubah tanpa terkena error duplikasi judul.

---

### 7. Menambahkan Media ke Koleksi Pribadi Pengguna (`attach`)
* **Method:** `POST`
* **URL:** `http://127.0.0.1:8000/api/users/2/collection` *(User ID 2 = Bob)*
* **Headers:**
  * `Accept`: `application/json`
  * `Content-Type`: `application/json`
* **Body (`raw` -> `JSON`):**
```json
{
    "media_id": 5
}
```
* **Output yang Diharapkan:** Status **`201 Created`**, pesan `"Media berhasil ditambahkan ke koleksi"`, dan array `data.media` milik `Bob` berisi `Inception`.

---

### 8. Menampilkan Koleksi Media yang Dimiliki Seorang Pengguna
* **Method:** `GET`
* **URL:** `http://127.0.0.1:8000/api/users/2/collection` *(bisa juga `GET http://127.0.0.1:8000/api/users/2`)*
* **Headers:**
  * `Accept`: `application/json`
* **Body:** `none`
* **Output yang Diharapkan:** Status **`200 OK`**, menampilkan data `Bob` beserta array `"media"` berisi `Inception`.

---

### 9. Menghapus Media dari Koleksi Pengguna (`detach`)
* **Method:** `DELETE`
* **URL:** `http://127.0.0.1:8000/api/users/2/collection/5`
* **Headers:**
  * `Accept`: `application/json`
* **Body:** `none`
* **Output yang Diharapkan:** Status **`200 OK`**, pesan `"Media berhasil dihapus dari koleksi"`, dan array `data.media` milik `Bob` kembali kosong (`[]`).

---

### 10. Menghapus Data Media dari Sistem
* **Method:** `DELETE`
* **URL:** `http://127.0.0.1:8000/api/media/6`
* **Headers:**
  * `Accept`: `application/json`
* **Body:** `none`
* **Output yang Diharapkan:** Status **`200 OK`**, pesan `"Media berhasil dihapus"`.

---

## Bagian 2: Pengujian Failure Path (Validasi & Error Handling — Berurutan 11 s/d 16)

### 11. Gagal Validasi: Judul Media Kosong (`422`)
* **Method:** `POST`
* **URL:** `http://127.0.0.1:8000/api/media`
* **Headers:**
  * `Accept`: `application/json`
  * `Content-Type`: `application/json`
* **Body (`raw` -> `JSON`):**
```json
{
    "title": "",
    "type": "movie",
    "genre": "Drama",
    "rating": 8.0,
    "release_year": 2022,
    "duration_minutes": 110,
    "director": "Unknown"
}
```
* **Output yang Diharapkan:** Status **`422 Unprocessable Entity`**, pesan error `"Judul media wajib diisi."`.

---

### 12. Gagal Validasi: Judul Media Duplikat (`422`)
* **Method:** `POST`
* **URL:** `http://127.0.0.1:8000/api/media`
* **Headers:**
  * `Accept`: `application/json`
  * `Content-Type`: `application/json`
* **Body (`raw` -> `JSON`):**
```json
{
    "title": "Sousou no Frieren",
    "type": "anime",
    "genre": "Adventure",
    "rating": 9.1,
    "release_year": 2023,
    "total_episodes": 28,
    "studio": "Madhouse"
}
```
* **Output yang Diharapkan:** Status **`422 Unprocessable Entity`**, pesan error `"Judul media sudah terdaftar dan tidak boleh duplikat."`.

---

### 13. Gagal Validasi: Field Spesifik `movie` Tidak Diisi (`422`)
* **Method:** `POST`
* **URL:** `http://127.0.0.1:8000/api/media`
* **Headers:**
  * `Accept`: `application/json`
  * `Content-Type`: `application/json`
* **Body (`raw` -> `JSON`):**
```json
{
    "title": "Dune Part Two",
    "type": "movie",
    "genre": "Sci-Fi",
    "rating": 8.6,
    "release_year": 2024
}
```
* **Output yang Diharapkan:** Status **`422 Unprocessable Entity`**, error pada `duration_minutes` dan `director`.

---

### 14. Gagal Konflik: Menambahkan Media yang Sama 2x ke Koleksi User (`409`)
* **Method:** `POST`
* **URL:** `http://127.0.0.1:8000/api/users/1/collection` *(Alice sudah memiliki Media ID 1 dari seeder)*
* **Headers:**
  * `Accept`: `application/json`
  * `Content-Type`: `application/json`
* **Body (`raw` -> `JSON`):**
```json
{
    "media_id": 1
}
```
* **Output yang Diharapkan:** Status **`409 Conflict`**, pesan `"Media sudah ada di koleksi pengguna"`.

---

### 15. Gagal Relasi: Menghapus Media yang Tidak Ada di Koleksi User (`404`)
* **Method:** `DELETE`
* **URL:** `http://127.0.0.1:8000/api/users/2/collection/1` *(Bob belum pernah menambahkan Media ID 1)*
* **Headers:**
  * `Accept`: `application/json`
* **Body:** `none`
* **Output yang Diharapkan:** Status **`404 Not Found`**, pesan `"Media tidak ditemukan di koleksi pengguna ini"`.

---

### 16. Gagal Lookup: Mengakses Media ID Tidak Ditemukan (`404`)
* **Method:** `GET`
* **URL:** `http://127.0.0.1:8000/api/media/9999`
* **Headers:**
  * `Accept`: `application/json`
* **Body:** `none`
* **Output yang Diharapkan:** Status **`404 Not Found`**.

---

## 3. Penutup Setelah Pengujian Selesai

Jika kamu ingin mengembalikan database ke kondisi bersih setelah testing:

```cmd
php artisan migrate:fresh --seed
```
