# 📊 PLFIS Database Structure Documentation

## ✅ Migrations Created

Semua migration files telah dibuat dan berhasil dijalankan:

### Core Financial Tables
1. ✅ **expense_categories** - Kategori pengeluaran (sistem & custom)
2. ✅ **financial_transactions** - Transaksi keuangan (income & expense)
3. ✅ **emergency_funds** - Dana darurat
4. ✅ **recurring_expenses** - Pengeluaran rutin

### Security & Management Tables
5. ✅ **accounts_vault** - Password manager (encrypted)
6. ✅ **notes** - Catatan pribadi
7. ✅ **tasks** - Agenda & task management
8. ✅ **ai_logs** - Log interaksi dengan AI
9. ✅ **settings** - Pengaturan user

---

## 🌱 Seeders Created

### 1. ExpenseCategorySeeder
Membuat 8 kategori default:
- 🛒 Kebutuhan Pokok (#4CAF50)
- 🚗 Transport (#2196F3)
- 📱 Internet & Komunikasi (#9C27B0)
- 📚 Pendidikan (#FF9800)
- 🎮 Hiburan (#E91E63)
- 🏥 Kesehatan (#F44336)
- 🚨 Darurat (#FF5722)
- 📦 Lainnya (#607D8B)

### 2. DemoUserSeeder (Optional)
Membuat user demo dengan data sample:
- User: demo@plfis.local (password: password)
- Settings
- Emergency fund
- Sample transactions
- Sample notes
- Sample tasks

---

## 🚀 Cara Menggunakan

### Menjalankan Migrations
```bash
# Fresh migration (hapus semua data)
php artisan migrate:fresh

# Migration biasa
php artisan migrate
```

### Menjalankan Seeders
```bash
# Seed kategori default saja
php artisan db:seed

# Seed dengan demo user (uncomment di DatabaseSeeder.php)
php artisan db:seed --class=DemoUserSeeder
```

---

## 📋 Struktur Tabel Detail

### 1. expense_categories
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint (nullable) | Null untuk sistem, filled untuk custom |
| name | string | Nama kategori |
| icon | string | Emoji icon |
| color | string | Hex color |
| is_system | boolean | True untuk kategori default |

### 2. financial_transactions
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key |
| type | enum | 'income' atau 'expense' |
| category_id | bigint | Foreign key ke categories |
| amount | decimal(15,2) | Nominal |
| source | string | Sumber income/merchant |
| description | text | Keterangan |
| transaction_date | date | Tanggal transaksi |
| is_recurring | boolean | Apakah berulang |
| recurring_period | enum | daily/weekly/monthly/yearly |
| tags | json | Tags tambahan |

### 3. emergency_funds
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key (unique) |
| target_amount | decimal(15,2) | Target dana darurat |
| current_amount | decimal(15,2) | Dana saat ini |
| monthly_expense_base | decimal(15,2) | Rata-rata pengeluaran bulanan |
| target_months | integer | Target bulan (default: 6) |

### 4. recurring_expenses
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key |
| category_id | bigint | Foreign key |
| name | string | Nama pengeluaran |
| amount | decimal(15,2) | Nominal |
| period | enum | daily/weekly/monthly/yearly |
| next_due_date | date | Jatuh tempo berikutnya |
| is_active | boolean | Status aktif |
| reminder_days_before | integer | Reminder berapa hari sebelumnya |

### 5. accounts_vault
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key |
| account_type | enum | email/game/social_media/website/api/other |
| service_name | string | Nama layanan |
| username | text | **ENCRYPTED** |
| email | text | **ENCRYPTED** |
| password | text | **ENCRYPTED** |
| notes | text | **ENCRYPTED** |
| url | string | URL layanan |
| last_password_change | date | Terakhir ganti password |

> ⚠️ **PENTING**: Field username, email, password, dan notes harus dienkripsi menggunakan `Crypt::encryptString()` di Laravel

### 6. notes
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key |
| title | string | Judul catatan |
| content | text | Isi catatan |
| tags | json | Tags |
| is_pinned | boolean | Pin di atas |
| color | string | Warna catatan |
| deleted_at | timestamp | Soft delete |

### 7. tasks
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key |
| title | string | Judul task |
| description | text | Deskripsi |
| priority | enum | low/medium/high/urgent |
| status | enum | pending/in_progress/completed/cancelled |
| due_date | datetime | Deadline |
| reminder_at | datetime | Waktu reminder |
| completed_at | datetime | Waktu selesai |
| tags | json | Tags |

### 8. ai_logs
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key |
| prompt | text | Pertanyaan ke AI |
| response | text | Jawaban AI |
| context_data | json | Data konteks (ringkasan keuangan) |
| tokens_used | integer | Token yang digunakan |
| response_time | integer | Waktu response (ms) |

### 9. settings
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key (unique) |
| currency | string | Mata uang (default: IDR) |
| language | string | Bahasa (default: id) |
| timezone | string | Timezone (default: Asia/Jakarta) |
| theme | enum | light/dark/auto |
| notification_enabled | boolean | Notifikasi aktif |
| ai_enabled | boolean | AI aktif |
| vault_timeout_minutes | integer | Timeout vault (default: 15) |
| preferences | json | Preferensi tambahan |

---

## 🔐 Keamanan

### Password Vault Encryption
Gunakan Laravel Crypt untuk enkripsi:

```php
use Illuminate\Support\Facades\Crypt;

// Encrypt
$encrypted = Crypt::encryptString($password);

// Decrypt
$decrypted = Crypt::decryptString($encrypted);
```

### Best Practices
1. Selalu enkripsi data sensitif di `accounts_vault`
2. Gunakan HTTPS di production
3. Implement rate limiting
4. Session timeout untuk keamanan
5. Backup database secara terenkripsi

---

## 📝 Next Steps

### Phase 1: Models
Buat Eloquent models untuk semua tabel:
- User (extend default)
- ExpenseCategory
- FinancialTransaction
- EmergencyFund
- RecurringExpense
- AccountVault
- Note
- Task
- AiLog
- Setting

### Phase 2: Services
Buat service layer:
- FinanceService
- AIService (Gemini integration)
- VaultService (encryption)
- NotificationService

### Phase 3: Controllers & Views
Buat UI untuk semua modul

---

## 🎯 Database Relationships

```
User
├── hasMany → financial_transactions
├── hasMany → expense_categories (custom)
├── hasOne → emergency_fund
├── hasMany → recurring_expenses
├── hasMany → accounts_vault
├── hasMany → notes
├── hasMany → tasks
├── hasMany → ai_logs
└── hasOne → settings

FinancialTransaction
└── belongsTo → expense_category

RecurringExpense
└── belongsTo → expense_category
```

---

## ✅ Status

- ✅ Migrations: **DONE**
- ✅ Seeders: **DONE**
- ✅ Database: **READY**
- ⏳ Models: **TODO**
- ⏳ Services: **TODO**
- ⏳ Controllers: **TODO**
- ⏳ Views: **TODO**
