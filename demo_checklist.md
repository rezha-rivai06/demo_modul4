# Peta Demo & Audit Rubrik Modul 4 — FlipPlay REST API (Target 80% + 10% Pemahaman)

Dokumen ini adalah **peta penunjuk langsung ("Ini filenya, ini barisnya, ini Postman-nya")** untuk memastikan **80% poin Demo + 10% poin Pemahaman** aman tanpa ada satu pun ketentuan di Modul 4 (halaman 29–31) yang terlewat saat demo pukul **16:55**.

---

## 0. Checklist Persiapan Jam 16:50 (5 Menit Sebelum Dipanggil Aslab)

1. Pastikan **Laragon (MySQL)** aktif hijau.
2. Reset database agar data bersih & ID urut mulai dari awal (`Alice=1`, `Bob=2`, `Media 1..4`):
   ```cmd
   php artisan migrate:fresh --seed
   ```
3. Cek semua route terdaftar (siapkan terminalnya untuk ditunjukkan ke aslab):
   ```cmd
   php artisan route:list --path=api
   ```
4. Jalankan server Laravel:
   ```cmd
   php artisan serve
   ```
5. Buka **Postman** di collection **`FlipPlay REST API - Modul 4`** (folder `berhasil` `01`–`10` dan `gagal` `11`–`16`).

---

## 1. Peta Audit Rubrik Penilaian Demo (**Total 80%**)

Gunakan tabel ini saat aslab mengecek poin demi poin pada **Rubrik Penilaian (Halaman 31)**:

| No | Aspek Penilaian Rubrik | Bobot | File & Baris Kode yang Ditunjukkan | Bukti Uji di Postman | Status |
| :---: | :--- | :---: | :--- | :--- | :---: |
| **1** | **CRUD API lengkap** (`index`, `show`, `store`, `update`, `destroy`) dengan status code tepat | **20%** | • `app/Http/Controllers/MediaController.php`: `index()` (L18–28), `show()` (L33–36), `store()` (L41–48 -> `201`), `update()` (L53–58 -> `200`), `destroy()` (L63–70 -> `200`)<br>• `routes/api.php` (L8–10) | Folder `berhasil`: **`01`, `02`, `03`, `04`, `05`, `06`, `10`** & `gagal`: **`16`** (`404`) | `[x]` |
| **2** | **Eloquent Relationship** (migration, relasi antar entitas, eager loading) | **20%** | • Migrations:<br>  - `0001_01_01_000000_create_users_table.php` (L14–22)<br>  - `2026_11_25_155447_create_media_table.php` (L14–31)<br>  - `2026_11_25_155457_create_media_user_table.php` (L14–22 `cascadeOnDelete()` & `unique`)<br>• Models:<br>  - `app/Models/Media.php` (L15 `$table`, L47–50 `users(): BelongsToMany`)<br>  - `app/Models/User.php` (L37–40 `media(): BelongsToMany`)<br>• Eager Loading:<br>  - `MediaController.php` (L22 `with('users')`, L35 `load('users')`, L57 `fresh('users')`)<br>  - `UserController.php` (L19 `with('media')`, L29 `load('media')`) | **`03`** (list + `users`), **`04`** (detail + `Alice`), **`08`** (koleksi `Bob`) | `[x]` |
| **3** | **Form Request Validation & penanganan error 422** | **15%** | • `app/Http/Requests/StoreMediaRequest.php` (L24–84 `rules()`: `POST` vs `PATCH`, `unique:media,title`, `Rule::unique()->ignore($mediaId)`, `required_if:type`; L91–110 `messages()`)<br>• `app/Http/Requests/AddToCollectionRequest.php` (L22–41 `media_id` `required\|integer\|exists:media,id`) | Folder `gagal`: **`11`** (judul kosong `422`), **`12`** (judul duplikat `422`), **`13`** (field Movie kurang `422`) | `[x]` |
| **4** | **API Resource** (format response konsisten, termasuk `whenLoaded`) | **10%** | • `app/Http/Resources/MediaResource.php` (L17–31: `$this->when()` Movie/Anime, L28 `whenLoaded('users')`)<br>• `app/Http/Resources/UserResource.php` (L17–22: L21 `whenLoaded('media')`) | **`01`–`08`** (JSON konsisten di wrapper `"data"`, atribut Movie/Anime bersih, relasi tampil hanya saat loaded) | `[x]` |
| **5** | **Custom action di luar CRUD standar** (`attach`/`detach` relasi) | **10%** | • `routes/api.php` (L13–17)<br>• `app/Http/Controllers/UserController.php`:<br>  - `addToCollection()` (L36–52 `attach()` + cek duplikat **`409 Conflict`** L40–44)<br>  - `removeFromCollection()` (L57–71 `detach()` + cek **`404`** L59–63) | **`07`** (`attach` `201`), **`08`** (lihat koleksi `200`), **`09`** (`detach` `200`), **`14`** (duplikat `409`), **`15`** (detach kosong `404`) | `[x]` |
| **6** | **Pengujian endpoint — happy path & failure path (Postman)** | **5%** | • Postman Collection **`FlipPlay REST API - Modul 4`**<br>• Panduan urutan: `pengujian.md`<br>• Automated Test: `tests/Feature/FlipPlayApiTest.php` (`6 tests, 73 assertions passed`) | Jalankan urut **`01` s/d `16`** di Postman | `[x]` |

---

## 2. Peta Audit Teks Modul: Ketentuan Demo & Alur yang Diharapkan (Hal. 29–30)

### A. Evolusi FlipPlay Modul 3 $\rightarrow$ Modul 4
* **Asal OOP CLI (`demo_modul3`):** `Media`, `Movie`, `Anime`, `User`, dan `FlipPlayEngine`.
* **Wujud Laravel 13 REST API (`demo_modul4`):**
  * Atribut `Media`, `Movie` (`duration_minutes`, `director`), dan `Anime` (`total_episodes`, `studio`) disatukan rapi di migration `create_media_table` dan dipilah kembali secara bersih di `MediaResource` memakai `$this->when($this->type === ...)`.
  * `FlipPlayEngine::searchByTitle()` berevolusi menjadi `Media::scopeSearch()` (L55–62 di `Media.php`) yang dipanggil di `MediaController::index()` (L20–25).
  * `User::$watchlist` + `addToWatchlist()` berevolusi menjadi relasi Many-to-Many `belongsToMany` lewat pivot `media_user` + `UserController::addToCollection()` & `removeFromCollection()`.

### B. 8 Ketentuan Demo
| No | Ketentuan Demo di Modul | File / Bukti yang Ditunjukkan | Status |
| :---: | :--- | :--- | :---: |
| 1 | Buat satu repositori GitHub dan buat project Laravel di repo tersebut | Repo `rezha-rivai06/demo_modul4` (commit `71952c5` sudah ter-push) | `[x]` |
| 2 | Minimal dua entitas dengan relasi Eloquent `belongsToMany` | `app/Models/Media.php` (L47–50) & `app/Models/User.php` (L37–40) | `[x]` |
| 3 | Migration mendefinisikan struktur tabel + foreign key/pivot table M:N | `create_users_table.php`, `create_media_table.php`, `create_media_user_table.php` | `[x]` |
| 4 | Controller CRUD standar (`index`, `show`, `store`, `update`, `destroy`) untuk Media | `app/Http/Controllers/MediaController.php` (L18–70) | `[x]` |
| 5 | Form Request Validation pada endpoint input (`POST`/`PATCH`), judul wajib & anti-duplikat | `StoreMediaRequest.php` (L32–37) & `AddToCollectionRequest.php` (L22–27) | `[x]` |
| 6 | API Resource konsisten termasuk `whenLoaded` | `MediaResource.php` (L28) & `UserResource.php` (L21) | `[x]` |
| 7 | Seluruh endpoint terdaftar di `routes/api.php` & terbaca di `php artisan route:list` | `routes/api.php` (L8–17) + jalankan `php artisan route:list --path=api` (10 routes) | `[x]` |
| 8 | Minimal satu custom action di luar CRUD standar untuk ubah relasi antartabel | `UserController::addToCollection()` (`attach`) & `removeFromCollection()` (`detach`) | `[x]` |

### C. 7 Alur yang Diharapkan & Pengujian Postman
| Urutan Modul | Skenario | Endpoint & Method | Request Postman | Status |
| :--- | :--- | :--- | :---: | :---: |
| Tambah media baru (judul, jenis, genre, dll.) | `Movie` & `Anime` | `POST /api/media` $\rightarrow$ `201` | **`01` & `02`** | `[x]` |
| Tampilkan daftar seluruh media | Index + Eager Load | `GET /api/media` $\rightarrow$ `200` | **`03`** | `[x]` |
| Tampilkan detail 1 media + pengguna kolektor | Show + `load('users')` | `GET /api/media/1` $\rightarrow$ `200` | **`04`** | `[x]` |
| Cari media berdasarkan judul | Query Filter | `GET /api/media?search=Frieren` $\rightarrow$ `200` | **`05`** | `[x]` |
| *(Tambahan CRUD lengkap)* Update & Hapus media | Update & Delete | `PATCH /api/media/5` & `DELETE /api/media/6` $\rightarrow$ `200` | **`06` & `10`** | `[x]` |
| Tambah media ke koleksi pribadi pengguna | Custom `attach()` | `POST /api/users/2/collection` $\rightarrow$ `201` | **`07`** | `[x]` |
| Tampilkan koleksi media milik pengguna | Show Collection | `GET /api/users/2/collection` $\rightarrow$ `200` | **`08`** | `[x]` |
| Hapus media dari koleksi pengguna | Custom `detach()` | `DELETE /api/users/2/collection/5` $\rightarrow$ `200` | **`09`** | `[x]` |
| **Pengujian Failure Path** | Validasi kosong/duplikat/missing fields (`422`), Duplikat koleksi (`409`), Data tidak ada (`404`) | `POST /api/media`, `POST /api/users/1/collection`, `DELETE`, `GET` | **`11` s/d `16`** | `[x]` |

---

## 3. Contekan Jawaban Pertanyaan Aslab (**Bobot Pemahaman 10%**)

| Pertanyaan yang Sering Muncul | Jawaban Tepat & Singkat |
| :--- | :--- |
| **Kenapa nama tabel pivot-nya `media_user`, bukan `user_media`?** | Karena konvensi Laravel menggabungkan nama singular kedua model secara alfabetis (`m` sebelum `u`), sehingga Eloquent otomatis mengenalinya tanpa definisi manual nama tabel di `belongsToMany`. |
| **Apa fungsi `whenLoaded()` di API Resource?** | Memastikan relasi hanya diserialisasi ke JSON jika controller sudah memanggil eager loading (`with()` / `load()`). Ini mencegah *N+1 query problem* dan mencegah *infinite loop* antara `MediaResource` dan `UserResource`. |
| **Kenapa di `routes/api.php` pakai `->parameters(['media' => 'media'])`?** | Karena pluralizer bahasa Inggris Laravel menganggap `medium` sebagai bentuk singular dari `media`. Tanpa `->parameters()`, parameter route default menjadi `{medium}` sementara controller memakai `Media $media`. |
| **Kenapa `PATCH` pakai `Rule::unique()->ignore($mediaId)`?** | Agar saat mengupdate field lain pada media tanpa mengganti judul (atau mengirim judul yang sama), validasi `unique` tidak menganggap record tersebut menduplikasi dirinya sendiri. |
| **Apa bedanya error `422` dan `409` di API ini?** | `422 Unprocessable Entity` untuk input yang gagal aturan validasi `FormRequest` (judul kosong/duplikat). `409 Conflict` untuk input yang valid secara format, tapi bertentangan dengan state relasi yang sudah ada (menambah media yang sama 2x ke koleksi user). |
| **Apa bedanya CRUD standar dengan custom action `attach()` / `detach()`?** | CRUD standar memanipulasi row di tabel entitas utama (`media`), sedangkan `attach()` / `detach()` hanya menambah atau menghapus row relasi pada pivot table `media_user` tanpa menghapus data `Media` maupun `User`. |
