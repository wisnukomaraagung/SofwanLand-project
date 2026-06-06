# Use Case Diagram

```mermaid
usecaseDiagram
    actor Admin
    actor Manager
    actor "User (Pekerja)" as User

    Admin --> (Login)
    Manager --> (Login)
    User --> (Login)

    Admin --> (Lihat Dashboard Utama)
    Manager --> (Lihat Dashboard Utama)
    User --> (Lihat Dashboard Utama)

    Admin --> (Pilih Proyek)
    Manager --> (Pilih Proyek)
    User --> (Pilih Proyek)

    Admin --> (Dashboard Proyek)
    Manager --> (Dashboard Proyek)
    User --> (Absensi di Proyek)

    Admin --> (Kelola Keuangan)
    Admin --> (Kelola Barang)
    Manager --> (Kelola Keuangan)
    Manager --> (Kelola Barang)

    Admin --> (Manajemen User)
```
