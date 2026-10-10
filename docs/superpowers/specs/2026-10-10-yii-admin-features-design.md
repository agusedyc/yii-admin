# Yii Admin Features Design Spec

## 1. User Management (Loosely Coupled)

- **Interface:** `Agusedyc\Admin\Repository\UserRepositoryInterface`
  - `findAll(): array`
  - `findById(string $id): ?object`
  - `save(object $user): void`
  - `delete(string $id): void`
- **Default Implementation:** `Agusedyc\Admin\Repository\DefaultUserRepository` & ActiveRecord `User`.
- **Database:** Migration untuk tabel `user` (jika menggunakan default).
- **Controller:** `UserController` untuk CRUD data dan Assignment (assign role).
- **Views:** Grid view list user, form create/update (menggunakan Bootstrap 5 dan HTMX).

## 2. Switch Role

- **State:** Role aktif disimpan di `Yiisoft\Session\SessionInterface` (`active_admin_role`).
- **AccessChecker:** Buat `Agusedyc\Admin\Access\ActiveRoleAccessChecker` (decorator untuk `AccessCheckerInterface` bawaan) yang memeriksa permission hanya dari `active_admin_role`. Jika tidak ada `active_admin_role`, gunakan semua role, atau tolak akses (configurable).
- **UI:** Dropdown / tombol di layout admin untuk mengganti role aktif (di-render dengan Bootstrap 5, interaksi via HTMX). Route khusus `SwitchRoleController` untuk memproses perubahan tanpa full page reload.

## 3. Auto Generate Menu

- **Generator:** `Agusedyc\Admin\Menu\MenuGenerator`
- **Logic:**
  1. Ambil role aktif.
  2. Ambil semua child permissions (routes dan groups) yang di-assign ke role tersebut.
  3. Strukturkan berdasarkan hierarchy: Group (sebagai root menu) -> Routes (sebagai submenu).
  4. Ambil metadata label/icon (dari konfigurasi/tabel jika ada).
- **Output:** Array yang kompatibel dengan komponen Menu Yii3 (`yiisoft/yii-bootstrap5`), dioptimalkan agar link navigasi bekerja dengan HTMX (hx-get, hx-target).
