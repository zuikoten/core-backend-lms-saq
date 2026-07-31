# Skema Tabel Relasional — Sistem Keuangan SPP TK (LMS)

```mermaid
erDiagram
    %% ========================
    %% MODUL AUTH (Shared Kernel)
    %% ========================
    users {
        bigint id PK
        string email "nullable, unique"
        string phone_number "unique"
        string password "nullable"
        boolean is_active "default true"
        remember_token "nullable"
        timestamp created_at
        timestamp updated_at
    }

    otp_codes {
        bigint id PK
        bigint user_id FK "nullable"
        string phone_number "nullable"
        string otp_code
        enum action_type "login | activation | reset_password"
        timestamp expires_at
        boolean is_used "default false"
        tinyint attempts "default 0"
        timestamp created_at
        timestamp updated_at
    }

    password_reset_tokens {
        string email PK
        string token
        timestamp created_at "nullable"
    }

    personal_access_tokens {
        bigint id PK
        morphs tokenable
        text name
        string token "unique"
        text abilities "nullable"
        timestamp last_used_at "nullable"
        timestamp expires_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    sessions {
        string id PK
        bigint user_id FK "nullable"
        string ip_address "nullable"
        text user_agent "nullable"
        longtext payload
        int last_activity
    }

    %% ========================
    %% MODUL CORE
    %% ========================
    academic_years {
        bigint id PK
        string year_name "contoh: 2026/2027"
        boolean is_active "default false"
        timestamp created_at
        timestamp updated_at
    }

    %% ========================
    %% MODUL STUDENT
    %% ========================
    parents {
        bigint id PK
        bigint user_id FK "nullable, unique"
        string phone_number
        string father_name "nullable"
        string mother_name "nullable"
        text address "nullable"
        timestamp created_at
        timestamp updated_at
    }

    students {
        bigint id PK
        bigint parent_id FK
        string nisn "nullable, unique"
        string full_name
        string nickname "nullable"
        enum gender "L | P"
        date birth_date
        enum status "aktif | mutasi | lulus"
        timestamp created_at
        timestamp updated_at
    }

    %% ========================
    %% MODUL FINANCE
    %% ========================
    billing_types {
        bigint id PK
        string name "SPP, Tabungan, Study Tour, dll"
        boolean is_recurring "default false"
        timestamp created_at
        timestamp updated_at
    }

    payment_channels {
        bigint id PK
        enum channel_type "bank_transfer | virtual_account | e_wallet | cash"
        string name "BCA, Mandiri, OVO, Cash, dll"
        string account_number "nullable"
        string account_holder_name "nullable"
        string provider "default manual | finpay"
        string provider_channel_code "nullable"
        boolean is_active "default true"
        timestamp created_at
        timestamp updated_at
    }

    billing_tariffs {
        bigint id PK
        bigint billing_type_id FK
        bigint academic_year_id FK
        string tariff_name
        decimal amount "12,2"
        timestamp created_at
        timestamp updated_at
    }

    student_tariff_mappings {
        bigint id PK
        bigint student_id FK
        bigint billing_tariff_id FK
        bigint academic_year_id FK "denormalisasi"
        bigint billing_type_id FK "denormalisasi"
        text note "nullable, alasan pemetaan tarif ini"
        bigint approved_by FK "nullable, users"
        timestamp created_at
        timestamp updated_at
        unique "student_id + academic_year_id + billing_type_id"
    }

    invoices {
        bigint id PK
        bigint student_id FK
        bigint academic_year_id FK
        bigint created_by FK "nullable, users"
        string invoice_number "unique"
        tinyint period_month "1-12"
        smallint period_year
        date due_date "nullable"
        decimal total_amount "12,2, default 0"
        enum status "unpaid | partial | paid | cancelled"
        timestamp created_at
        timestamp updated_at
        unique "student_id + academic_year_id + period_month"
    }

    invoice_items {
        bigint id PK
        bigint invoice_id FK
        bigint billing_type_id FK
        string item_name "snapshot nama saat invoice dibuat"
        decimal amount "12,2"
        timestamp created_at
        timestamp updated_at
    }

    payment_gateway_transactions {
        bigint id PK
        bigint invoice_id FK
        bigint payment_channel_id FK "nullable"
        string gateway_reference_id "unique"
        string gateway_trx_id "nullable"
        enum status "pending | paid | expired | failed | cancelled"
        decimal amount "12,2"
        timestamp expired_at "nullable"
        timestamp paid_at "nullable"
        json raw_request "nullable"
        json raw_response "nullable"
        timestamp created_at
        timestamp updated_at
    }

    invoice_payments {
        bigint id PK
        bigint invoice_id FK
        bigint payment_channel_id FK
        bigint payment_gateway_transaction_id FK "nullable"
        string reference_number "nullable"
        decimal amount_paid "12,2"
        datetime paid_at
        bigint handover_by FK "nullable, users"
        timestamp created_at
        timestamp updated_at
    }

    webhook_logs {
        bigint id PK
        string provider "default finpay"
        json payload
        json headers "nullable"
        boolean signature_valid "default false"
        boolean processed "default false"
        bigint payment_gateway_transaction_id FK "nullable"
        timestamp created_at "useCurrent, no updated_at"
    }

    %% ========================
    %% SPATIE PERMISSION TABLES
    %% ========================
    permissions {
        bigint id PK
        string name
        string guard_name
        timestamp created_at
        timestamp updated_at
        unique "name + guard_name"
    }

    roles {
        bigint id PK
        string name
        string guard_name
        timestamp created_at
        timestamp updated_at
        unique "name + guard_name"
    }

    model_has_permissions {
        bigint permission_id PK,FK
        string model_type
        bigint model_id
    }

    model_has_roles {
        bigint role_id PK,FK
        string model_type
        bigint model_id
    }

    role_has_permissions {
        bigint permission_id PK,FK
        bigint role_id PK,FK
    }

    %% ========================
    %% LARAVEL FRAMEWORK TABLES
    %% ========================
    cache {
        string key PK
        mediumtext value
        bigint expiration
    }

    cache_locks {
        string key PK
        string owner
        bigint expiration
    }

    jobs {
        bigint id PK
        string queue
        longtext payload
        smallint attempts
        int reserved_at "nullable"
        int available_at
        int created_at
    }

    job_batches {
        string id PK
        string name
        int total_jobs
        int pending_jobs
        int failed_jobs
        longtext failed_job_ids
        mediumtext options "nullable"
        int cancelled_at "nullable"
        int created_at
        int finished_at "nullable"
    }

    failed_jobs {
        bigint id PK
        string uuid "unique"
        string connection
        string queue
        longtext payload
        longtext exception
        timestamp failed_at
    }

    %% ========================
    %% RELATIONSHIPS
    %% ========================

    %% AUTH MODULE
    users ||--o{ otp_codes : "memiliki"
    users ||--o{ parents : "terkait"
    users ||--o{ sessions : "memiliki sesi"
    users ||--o{ personal_access_tokens : "memiliki token"
    users ||--o{ password_reset_tokens : "memiliki reset token"

    %% STUDENT MODULE
    parents ||--o{ students : "memiliki anak"
    parents }o--|| users : "terkait ke akun"

    %% FINANCE - TARIFF
    billing_types ||--o{ billing_tariffs : "memiliki tarif"
    academic_years ||--o{ billing_tariffs : "memiliki tarif"
    billing_tariffs ||--o{ student_tariff_mappings : "dipetakan ke"

    students ||--o{ student_tariff_mappings : "memiliki pemetaan tarif"
    academic_years ||--o{ student_tariff_mappings : "memiliki pemetaan"
    billing_types ||--o{ student_tariff_mappings : "memiliki pemetaan"

    %% FINANCE - INVOICE
    students ||--o{ invoices : "memiliki tagihan"
    academic_years ||--o{ invoices : "memiliki tagihan"
    users ||--o{ invoices : "membuat tagihan (created_by)"

    invoices ||--o{ invoice_items : "memiliki item"
    billing_types ||--o{ invoice_items : "direferensikan"

    %% FINANCE - PAYMENT
    invoices ||--o{ invoice_payments : "memiliki pembayaran"
    payment_channels ||--o{ invoice_payments : "digunakan"
    users ||--o{ invoice_payments : "menerima (handover_by)"

    invoices ||--o{ payment_gateway_transactions : "memiliki transaksi gateway"
    payment_channels ||--o{ payment_gateway_transactions : "digunakan"
    payment_gateway_transactions ||--o{ invoice_payments : "menghasilkan pembayaran"
    payment_gateway_transactions ||--o{ webhook_logs : "memiliki log webhook"

    %% SPATIE PERMISSION
    permissions ||--o{ role_has_permissions : "dimiliki oleh"
    roles ||--o{ role_has_permissions : "memiliki"
    roles ||--o{ model_has_roles : "diberikan ke"
    permissions ||--o{ model_has_permissions : "diberikan ke"
    users ||--o{ model_has_roles : "memiliki role"
    users ||--o{ model_has_permissions : "memiliki permission langsung"
```

## Catatan Penting

1. **user_id di parents** bersifat nullable & unique — diisi setelah orang tua aktivasi akun lewat OTP
2. **invoice_payments** mendukung partial payment — satu invoice bisa punya banyak baris pembayaran
3. **payment_gateway_transactions** opsional (tentatif untuk integrasi Finpay di masa depan)
4. **webhook_logs** hanya untuk notifikasi callback dari payment gateway (Finpay)
5. Tabel Spatie permission menggunakan polymorphic relationship (`model_has_roles`, `model_has_permissions`) sehingga bisa di-attach ke model apa pun, dalam kasus ini ke `users`
6. **personal_access_tokens** untuk API Sanctum (parent-facing React app)
7. **sessions** untuk admin panel (Blade views, session-based auth)
