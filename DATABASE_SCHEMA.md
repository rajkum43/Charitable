# Database Schema - Charitable Application

## 📊 Core Tables for Poll System

### 1. **death_aavedan** (मृत्यु आवेदन)
Death benefit application table

```sql
Columns:
├── id (int) - Primary Key
├── application_number (varchar(50)) - Unique ID like "DA20260401001"
├── member_id (varchar(20)) - Member's ID
├── member_name (varchar(100)) - Member name
├── member_dob (date) - Member's date of birth
├── member_mobile (varchar(10)) - Mobile number
├── member_address (text) - Address
├── applicant_name (varchar(100)) - Applicant/claimant name
├── applicant_dob (date) - Applicant date of birth
├── applicant_relation (varchar(50)) - Relation to deceased
├── applicant_parent_name (varchar(100)) - Applicant's father/mother/spouse name
├── deceased_name (varchar(100)) - Deceased person's name
├── deceased_member_id (varchar(20)) - Deceased member ID
├── deceased_dob (date) - Deceased DOB
├── deceased_age (int) - Age at death (18-60)
├── death_date (date) - Date of death
├── deceased_relationship (varchar(50)) - Relation to applicant
├── cause_of_death (text) - Cause
├── family_income (decimal(10,2)) - Annual income
├── family_members (int) - Family members count
├── bank_name (varchar(100)) - Bank name
├── branch_name (varchar(100)) - Branch
├── account_number (varchar(50)) - Account number
├── ifsc_code (varchar(20)) - IFSC code
├── account_holder_name (varchar(100)) - Account holder
├── upi_id (varchar(100)) - UPI address
├── remarks (text) - Admin remarks
├── status (enum) - 'submitted', 'under_review', 'approved', 'rejected', 'on_hold'
├── admin_remarks (text) - Admin notes
├── poll_status (tinyint) - 0=Not in poll, 1=In poll ⭐ (NEW)
├── deceased_aadhar (varchar(255)) - Aadhar copy file
├── death_certificate (varchar(255)) - Certificate file
├── post_mortem_report (varchar(255)) - PM report file
├── created_at (timestamp) - Submission time
└── updated_at (timestamp) - Last update time
```

---

### 2. **beti_vivah_aavedan** (बेटी विवाह आवेदन)
Girl marriage benefit application table

```sql
Columns:
├── id (int) - Primary Key
├── application_number (varchar(100)) - Unique ID like "BVA20260401001"
├── member_id (varchar(20)) - Member's ID
├── member_name (varchar(100)) - Member name
├── member_father (varchar(100)) - Member's father name
├── bride_name (varchar(100)) - Bride's name
├── bride_dob (date) - Bride's DOB
├── bride_aadhar (varchar(12)) - Bride's Aadhar
├── bride_education (varchar(100)) - Education level
├── bride_health (varchar(50)) - Health status
├── groom_name (varchar(100)) - Groom's name
├── groom_dob (date) - Groom's DOB
├── groom_age (int) - Groom's age
├── groom_father_name (varchar(100)) - Groom's father
├── groom_occupation (varchar(100)) - Groom's occupation
├── groom_education (varchar(100)) - Groom's education
├── wedding_date (date) - Marriage date
├── family_income (decimal(10,2)) - Annual family income
├── family_members (int) - Count of family members
├── address (text) - Address
├── district (varchar(100)) - District
├── block (varchar(100)) - Block
├── city (varchar(100)) - City
├── state (varchar(100)) - State
├── bank_name (varchar(100)) - Bank name
├── branch_name (varchar(100)) - Branch name
├── account_number (varchar(50)) - Account number
├── ifsc_code (varchar(20)) - IFSC code
├── account_holder_name (varchar(100)) - Account holder
├── upi_id (varchar(100)) - UPI address
├── status (enum) - 'Pending', 'Under Review', 'approved', 'rejected'
├── poll_status (tinyint) - 0=Not in poll, 1=In poll ⭐ (NEW)
├── aadhar_proof (varchar(255)) - File name
├── address_proof (varchar(255)) - File name
├── income_proof (varchar(255)) - File name
├── marriage_certificate (varchar(255)) - File name
├── created_at (timestamp) - Submission time
└── updated_at (timestamp) - Last update time
```

---

### 3. **poll** (पोल टेबल) ⭐ NEW
Stores all poll records with member assignments

```sql
Columns:
├── id (int) - Primary Key
├── claim_number (varchar(100)) - Application number (from death_aavedan or beti_vivah_aavedan)
├── user_id (int) - Member ID
├── poll (char(1)) - Poll option: 'A', 'B', 'C', 'D', etc.
├── application_type (enum) - 'Death' or 'Beti_Vivah'
├── alert (int) - Publish event number ⭐ (NEW) - tracks which publish batch the record belongs to
├── start_poll_date (date) - Poll start date (10th of month) ⭐
├── expire_poll_date (date) - Poll end date (20th of month) ⭐
├── created_at (timestamp) - Record creation time
└── updated_at (timestamp) - Last update time
```

**Indexes:**
- PRIMARY KEY: id
- INDEX: idx_user_id, idx_poll, idx_application_type, idx_claim_number
- INDEX: idx_poll_dates (start_poll_date, expire_poll_date)

---

### 4. **members** (सदस्य मास्टर)
Main member table

```sql
Columns:
├── id (int) - Primary Key
├── member_id (varchar(20)) - Unique member number
├── full_name (varchar(100)) - Full name
├── mobile (varchar(10)) - Mobile number
├── status (enum) - 'Active', 'Inactive', 'Suspended'
├── poll_option (char(1)) - Assigned poll option (A/B/C/...) ⭐ (NEW)
├── poll_assigned_at (timestamp) - When poll was assigned ⭐ (NEW)
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## ✅ Poll System SQL Queries

### Query: Get Records for Poll
```sql
SELECT 
    da.id,
    da.application_number as claim_number,
    da.member_id as user_id,
    da.member_name as user_name,
    'Death' as application_type
FROM death_aavedan da
WHERE da.poll_status = 0 AND da.status IN ('Pending', 'Under Review', 'approved')
ORDER BY da.created_at DESC;
```

### Query: Mark Records as In Poll
```sql
UPDATE death_aavedan 
SET poll_status = 1 
WHERE application_number IN ('DA20260401001', 'DA20260401002', ...);
```

### Query: Distribute Poll to Members
```sql
UPDATE members m
INNER JOIN temp_poll_distribution tpd ON m.id = tpd.member_id
SET m.poll_option = tpd.poll_option,
    m.poll_assigned_at = NOW()
WHERE m.status = 'Active';
```

---

## 🎯 Key Points

1. **Table Names:** Use `death_aavedan` (NOT death_claims) and `beti_vivah_aavedan`
2. **Application ID Column:** Use `application_number` not `claim_id` or `application_id`
3. **Member Name:** Use `member_name` directly from aavedan tables (no JOIN needed)
4. **Poll Status:** Both tables now have `poll_status` column (0=not in poll, 1=in poll)
5. **Poll Dates:** Auto-set to 10th-20th of current month
6. **Member Distribution:** Uses temporary table for bulk updates (high performance)
7. **Alert Column:** Tracks which publish batch records belong to (1=first publish, 2=second publish, etc.)

---

## 📊 Alert Column Logic (Poll Publishing)

The `alert` column in the `poll` table tracks which publish event a record belongs to:

**Example Timeline:**
- **First Publish:** Admin clicks "प्रकाशित करें" → `SELECT MAX(alert)` returns NULL/0 → Use alert = 1
  - All 5 selected rows get `alert = 1`
- **Second Publish:** Admin clicks "प्रकाशित करें" again → `SELECT MAX(alert)` returns 1 → Use alert = 2
  - All newly selected rows get `alert = 2`
- **Third Publish:** `SELECT MAX(alert)` returns 2 → Use alert = 3
  - All newly selected rows get `alert = 3`
- **And so on...** Each publish batch automatically increments

**Key Rule:** All rows inserted in a single publish action share the same alert number.

---

## 📋 Status Values

### death_aavedan status:
- `submitted` - Initial submission
- `under_review` - Being reviewed
- `approved` - Approved
- `rejected` - Rejected
- `on_hold` - On hold

### beti_vivah_aavedan status:
- `Pending` - Initial submission
- `Under Review` - Being reviewed
- `approved` - Approved
- `rejected` - Rejected

---

Generated: April 1, 2026
