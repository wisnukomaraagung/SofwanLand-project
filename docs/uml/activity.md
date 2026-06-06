# Activity Diagram — Login to Project Dashboard

```mermaid
flowchart TD
  A[Start] --> B[Login]
  B --> C{Role}
  C -->|Admin/Manager| D[Show Dashboard Utama]
  C -->|User (Pekerja)| D
  D --> E[User clicks a Project]
  E --> F[Set selected_project in session]
  F --> G[Redirect to Dashboard Proyek (detail)]
  G --> H{Role-specific menus}
  H -->|Admin/Manager| I[Dashboard Proyek, Keuangan, Barang, Absensi, Logout]
  H -->|User| J[Absensi, Logout]
  I --> K[End]
  J --> K
```
