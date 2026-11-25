# Panduan Presentasi & Daftar Pamer Demo Modul 4 (Target Skor Maksimal 90%)

Dokumen ini adalah **skrip presentasi demo** agar saat berhadapan dengan asisten laboratorium, kamu tampil terstruktur layaknya *Fullstack Engineer* dan seluruh poin penilaian (Demo **80%** + Pemahaman **10%**) langsung terlihat jelas tanpa ada yang terlewat.

---

## 0. Persiapan 1 Menit Sebelum Demo Dimulai

1. **Pastikan Laragon (MySQL) menyala** dan database bersih dari sisa uji coba sebelumnya:
   ```cmd
   php artisan migrate:fresh --seed
   ```
2. **Jalankan server Laravel:**
   ```cmd
   php artisan serve
   ```
3. **Buka tab berikut di VS Code** agar tinggal klik tanpa cari-cari folder:
   - `routes/api.php`
   - `database/migrations/2026_11_25_155447_create_media_table.php`
   - `database/migrations/2026_11_25_155457_create_media_user_table.php`
   - `app/Models/Media.php` & `app/Models/User.php`
   - `app/Http/Requests/StoreMediaRequest.php` & `AddToCollectionRequest.php`
   - `app/Http/Resources/MediaResource.php` & `UserResource.php`
   - `app/Http/Controllers/MediaController.php` & `UserController.php`
4. **Buka Postman** yang sudah meng-import `postman_collection.json` (16 request sudah tersusun rapi dalam folder `1. Happy Path` & `2. Failure Path`).

---

## 1. Urutan Hal yang Harus Diperlihatkan Saat Demo

### A. Bukti Repo GitHub & Routing (`Ketentuan Demo #1 & #7`)
1. Perlihatkan folder project `demo_modul4` dan repository GitHub `rezha-rivai06/demo_modul4` (atau `git status` / `git remote -v`).
2. Di terminal, jalankan:
   ```cmd
   php artisan route:list --path=api
   ```
   * **Poin yang diucapkan:** *"Semua 10 endpoint sudah didaftarkan di `routes/api.php`, termasuk `apiResource('media')` dengan parameter eksplisit `{media}` dan custom route koleksi user."*

### B. Struktur Database, Migration & Relasi Eloquent (**Bobot 20%**)
1. Buka `create_media_table.php` dan `create_media_user_table.php`:
   * **Poin yang diucapkan:** *"Struktur FlipPlay Modul 3 (Movie & Anime) kami petakan ke tabel `media` dengan constraint `title->unique()`, dan relasi Many-to-Many dihubungkan lewat pivot table `media_user` (penamaan alfabetis Laravel) dengan `cascadeOnDelete()` dan composite unique `[media_id, user_id]`."*
2. Buka `Media.php` dan `User.php`:
   * **Poin yang diucapkan:** *"Di kedua model didefinisikan relasi `belongsToMany`, plus `$table = 'media'` di `Media` serta query scope `scopeSearch()` untuk filter judul."*

### C. Form Request Validation (**Bobot 15%**)
1. Buka `StoreMediaRequest.php` dan `AddToCollectionRequest.php`:
   * **Poin yang diucapkan:** *"Semua endpoint `POST` dan `PATCH` memakai Form Request Validation. Aturan `POST` menggunakan `required` + `unique:media,title` + `required_if:type`, sedangkan `PATCH` memakai `sometimes` dan `Rule::unique()->ignore($mediaId)` agar update sebagian tidak bentrok dengan judulnya sendiri."*

### D. API Resource & Eager Loading (**Bobot 10%**)
1. Buka `MediaResource.php` dan `UserResource.php`:
   * **Poin yang diucapkan:** *"Response JSON distandarkan lewat API Resource. Relasi `users` dan `media` dibungkus `whenLoaded()` untuk mencegah N+1 query dan circular reference, sedangkan atribut spesifik Movie/Anime memakai `$this->when()` agar JSON bersih sesuai tipenya."*

### E. Controller CRUD & Custom Action (**Bobot 20% + 10% = 30%**)
1. Buka `MediaController.php`:
   * Tunjukkan 5 method standar (`index`, `show`, `store`, `update`, `destroy`), penggunaan `with('users')` / `load('users')`, filter `?search=` / `?title=`, serta status `201 Created` di `store()`.
2. Buka `UserController.php`:
   * Tunjukkan `addToCollection()` (`attach()` + pengecekan duplikat **`409 Conflict`**) dan `removeFromCollection()` (`detach()` + pengecekan **`404 Not Found`**).

### F. Live Testing di Postman (**Bobot 5%**)
Tunjukkan alur **Happy Path** lalu **Failure Path** secara berurutan:
1. `POST /api/media` (Movie *Inception*) $\rightarrow$ **`201 Created`**
2. `GET /api/media` $\rightarrow$ **`200 OK`** (daftar media + `users`)
3. `GET /api/media?search=Frieren` $\rightarrow$ **`200 OK`** (filter judul)
4. `GET /api/media/1` $\rightarrow$ **`200 OK`** (detail + `Alice`)
5. `PATCH /api/media/5` $\rightarrow$ **`200 OK`** (update rating tanpa error `unique`)
6. `POST /api/users/2/collection` (`media_id: 5`) $\rightarrow$ **`201 Created`** (`attach`)
7. `GET /api/users/2/collection` $\rightarrow$ **`200 OK`** (lihat koleksi Bob)
8. `DELETE /api/users/2/collection/5` $\rightarrow$ **`200 OK`** (`detach`)
9. **Failure Path:**
   - `POST /api/media` judul kosong / duplikat $\rightarrow$ **`422 Unprocessable Entity`**
   - `POST /api/users/1/collection` (`media_id: 1` yang sudah dimiliki Alice) $\rightarrow$ **`409 Conflict`**
   - `GET /api/media/9999` $\rightarrow$ **`404 Not Found`**

*(Tips cepat: Kamu juga bisa klik `...` di Collection Postman $\rightarrow$ **Run collection** untuk menunjukkan 16/16 test script hijau otomatis dalam 1 detik!)*

---

## 2. Contekan Jawaban Pertanyaan Aslab (**Bobot Pemahaman 10%**)

| Pertanyaan yang Sering Muncul | Jawaban Tepat & Singkat |
| :--- | :--- |
| **Kenapa nama tabel pivot-nya `media_user`, bukan `user_media`?** | Karena konvensi Laravel menggabungkan nama singular kedua model secara alfabetis (`m` sebelum `u`), sehingga Eloquent otomatis mengenalinya tanpa perlu definisi manual nama tabel di `belongsToMany`. |
| **Apa fungsi `whenLoaded()` di API Resource?** | Untuk memastikan relasi hanya diserialisasi ke JSON jika controller memang sudah memanggil eager loading (`with()` / `load()`). Ini mencegah *N+1 query problem* dan sekaligus mencegah *infinite loop* antara `MediaResource` dan `UserResource`. |
| **Kenapa di `routes/api.php` pakai `->parameters(['media' => 'media'])`?** | Karena pluralizer bahasa Inggris di Laravel menganggap `medium` sebagai bentuk singular dari `media`. Tanpa `->parameters()`, Route Model Binding bisa gagal karena parameter default-nya menjadi `{medium}` sementara controller menerima `Media $media`. |
| **Kenapa `PATCH` pakai `Rule::unique()->ignore($mediaId)`?** | Agar saat mengupdate data media tanpa mengganti judulnya (atau mengirim ulang judul yang sama), database tidak menganggap record tersebut menduplikasi dirinya sendiri. |
| **Apa bedanya error `422` dan `409` di API ini?** | `422 Unprocessable Entity` dipakai saat validasi format/aturan input gagal (misal judul kosong atau duplikat di `FormRequest`). Sedangkan `409 Conflict` dipakai saat input secara format valid, tetapi bertentangan dengan state relasi yang sudah ada (menambahkan media yang sama 2x ke koleksi user). |
| **Apa bedanya CRUD standar dengan custom action `attach()` / `detach()`?** | CRUD standar memanipulasi baris pada tabel entitas utama (`media`), sedangkan custom action `attach()` dan `detach()` hanya menambah atau menghapus baris relasi pada tabel pivot `media_user` tanpa menghapus data `Media` maupun `User`-nya. |
