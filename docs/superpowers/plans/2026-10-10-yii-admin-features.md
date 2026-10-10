# Yii Admin Features Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan antarmuka CRUD User dengan Assignment, fitur Switch Role via Session, dan Auto-generate Menu menggunakan HTMX dan Bootstrap 5.

**Architecture:** Loosely coupled User management via interface, Decorator based AccessChecker untuk Switch Role, dan Menu Generator yang membaca dari hierarki RBAC.

**Tech Stack:** PHP 8.1+, Yii3, Bootstrap 5, HTMX

**Spec:** `docs/superpowers/specs/2026-10-10-yii-admin-features-design.md`

## Global Constraints

- Kompatibel dengan PHP 8.1 - 8.5
- Mematuhi standar coding PSR-12 (via `composer cs-fix`)
- Views menggunakan Bootstrap 5 dan `htmx.org`
- Typehinting ketat dan lolos psalm static analysis

## Review Focus

- User mencoba switch ke role yang tidak mereka miliki -> ditolak.
- User tanpa `active_admin_role` di session -> kembali ke default Yii3 behavior (semua role digunakan).
- Menu generator menerima user tanpa assign apapun -> return empty array.

---

### Task 1: User Repository Interface & Default Model

**Files:**
- Create: `src/Repository/UserRepositoryInterface.php`
- Create: `src/Repository/DefaultUserRepository.php`
- Create: `src/Model/User.php`
- Create: `migrations/m231010_000000_create_admin_user_table.php`
- Test: `tests/Unit/Repository/DefaultUserRepositoryTest.php`

**Interfaces:**
- Produces: `UserRepositoryInterface` dengan method `findAll()`, `findById()`, `save()`, `delete()`
- Produces: `User` ActiveRecord dengan property `id`, `username`, `email`

- [ ] **Step 1: Write test for DefaultUserRepository**
```php
public function testFindAllReturnsArrayOfUsers(): void
{
    // setup mock db & user
    $repo = new DefaultUserRepository($db);
    $this->assertIsArray($repo->findAll());
}
```
- [ ] **Step 2: Run test to verify it fails**
Run: `vendor/bin/phpunit tests/Unit/Repository/DefaultUserRepositoryTest.php`
Expected: FAIL

- [ ] **Step 3: Implement UserRepositoryInterface and User ActiveRecord**
Buat class interface, class implementasi default (menggunakan `Yiisoft\ActiveRecord\ActiveRecord`), dan Model dasar. Buat migration Yii3 untuk tabel `yii_admin_user`.
- [ ] **Step 4: Run test to verify it passes**
- [ ] **Step 5: Commit**
```bash
git add src/Repository src/Model migrations tests
git commit -m "feat: user repository interface and default implementation"
```

### Task 2: User Controller & Views

**Files:**
- Create: `src/Controller/User/UserController.php`
- Create: `views/user/index.php`
- Create: `views/user/_form.php`
- Modify: `config/routes.php` (menambahkan route `/admin/user/*`)

**Interfaces:**
- Consumes: `UserRepositoryInterface`
- Consumes: `Yiisoft\Rbac\ManagerInterface` untuk assignment

- [ ] **Step 1: Create UserController with CRUD and Assignment**
Inject `UserRepositoryInterface` dan `ManagerInterface`. Buat action `index`, `create`, `update`, `delete`. Pada update, muat role assignment, dan proses form submit untuk memanggil `$manager->assign()` atau `$manager->revoke()`.
- [ ] **Step 2: Create Views using Bootstrap 5 and HTMX**
Dalam `_form.php`, gunakan atribut HTMX `hx-post`, `hx-target` untuk submit form assignment.
- [ ] **Step 3: Register routes**
Tambahkan grup route `/user` di `config/routes.php` (jika ada) atau di file controller routes.
- [ ] **Step 4: Verify views rendering manually or via functional test**
- [ ] **Step 5: Commit**
```bash
git add src/Controller/User views/user config
git commit -m "feat: user crud and assignment ui"
```

### Task 3: Switch Role Feature

**Files:**
- Create: `src/Access/ActiveRoleAccessChecker.php`
- Create: `src/Controller/Role/SwitchRoleController.php`
- Modify: `views/layout.php` (tambah dropdown)

**Interfaces:**
- Consumes: `Yiisoft\Access\AccessCheckerInterface`, `Yiisoft\Session\SessionInterface`
- Produces: `ActiveRoleAccessChecker`

- [ ] **Step 1: Write test for ActiveRoleAccessChecker**
Pastikan jika `active_admin_role` ada di session, checker membypass delegasi ke inner checker untuk role lain.
- [ ] **Step 2: Implement ActiveRoleAccessChecker**
Class ini mengimplementasikan `AccessCheckerInterface`. Pada constructor terima `$innerChecker` dan `$session`.
- [ ] **Step 3: Implement SwitchRoleController**
Terima POST via HTMX, set `$session->set('active_admin_role', $role)`, lalu return snippet atau trigger redirect htmx `HX-Redirect`.
- [ ] **Step 4: Update Layout**
Di `views/layout.php`, tambahkan dropdown switch role (Bootstrap 5) yang melakukan `hx-post="/admin/role/switch"`.
- [ ] **Step 5: Commit**
```bash
git add src/Access src/Controller/Role views
git commit -m "feat: switch role active state"
```

### Task 4: Auto Generate Menu

**Files:**
- Create: `src/Menu/MenuGenerator.php`
- Test: `tests/Unit/Menu/MenuGeneratorTest.php`

**Interfaces:**
- Consumes: `Yiisoft\Rbac\ManagerInterface`, `Yiisoft\Session\SessionInterface`
- Produces: `MenuGenerator::generate(): array`

- [ ] **Step 1: Write test for MenuGenerator**
Setup mock `ManagerInterface` yang mengembalikan hierarki group -> route. Pastikan output array sesuai format menu Yii3.
- [ ] **Step 2: Implement MenuGenerator**
Baca rule hirarki RBAC. Jika ada active role di session, query permission anak. Hasilkan struktur label/url array.
- [ ] **Step 3: Run test**
- [ ] **Step 4: Commit**
```bash
git add src/Menu tests
git commit -m "feat: menu generator"
```
