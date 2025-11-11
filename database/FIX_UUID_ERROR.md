# إصلاح خطأ UUID في Supabase
# Fix UUID Error in Supabase

## 🔴 المشكلة / Problem

عند تنفيذ ملف `supabase_seed_data.sql` في Supabase، قد تواجه هذا الخطأ:
When executing `supabase_seed_data.sql` in Supabase, you may encounter this error:

```
ERROR: 22P02: invalid input syntax for type uuid: "cat1-1111-1111-1111-111111111111"
```

## ✅ الحل السريع / Quick Solution

### الطريقة 1: استخدام uuid_generate_v4() (الأسهل)
### Method 1: Use uuid_generate_v4() (Easiest)

في Supabase SQL Editor، استبدل جميع UUIDs الثابتة بـ `uuid_generate_v4()`:
In Supabase SQL Editor, replace all fixed UUIDs with `uuid_generate_v4()`:

**مثال / Example:**

```sql
-- بدلاً من / Instead of:
('cat1-1111-1111-1111-111111111111', ...)

-- استخدم / Use:
(uuid_generate_v4(), ...)
```

### الطريقة 2: استخدام UUIDs صالحة يدوياً
### Method 2: Use valid UUIDs manually

استبدل جميع UUIDs غير الصالحة بـ UUIDs صالحة بتنسيق hex:
Replace all invalid UUIDs with valid UUIDs in hex format:

**مثال / Example:**

```sql
-- UUID صالح / Valid UUID:
'550e8400-e29b-41d4-a716-446655440000'

-- UUID غير صالح / Invalid UUID:
'cat1-1111-1111-1111-111111111111'
```

## 🔧 الحل الكامل / Complete Solution

### الخطوة 1: استبدال UUIDs في الفئات
### Step 1: Replace UUIDs in Categories

استبدل جميع `'catX-...'` بـ UUIDs صالحة:
Replace all `'catX-...'` with valid UUIDs:

```sql
-- استبدل / Replace:
'cat1-1111-1111-1111-111111111111' → '550e8400-e29b-41d4-a716-446655440001'
'cat1-2222-2222-2222-222222222222' → '550e8400-e29b-41d4-a716-446655440002'
'cat2-1111-1111-1111-111111111111' → '550e8400-e29b-41d4-a716-446655440011'
-- وهكذا...
```

### الخطوة 2: استبدال UUIDs في المنتجات
### Step 2: Replace UUIDs in Menu Items

استبدل جميع `'itemX-...'` بـ UUIDs صالحة:
Replace all `'itemX-...'` with valid UUIDs:

```sql
-- استبدل / Replace:
'item1-1111-1111-1111-111111111111' → '660e8400-e29b-41d4-a716-446655440001'
'item1-2222-2222-2222-222222222222' → '660e8400-e29b-41d4-a716-446655440002'
-- وهكذا...
```

## 🚀 الحل التلقائي (مستقبلاً)
## Automatic Solution (Future)

يمكنك استخدام سكريبت PowerShell `scripts/fix_uuids.ps1` لإصلاح جميع UUIDs تلقائياً:
You can use PowerShell script `scripts/fix_uuids.ps1` to fix all UUIDs automatically:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\fix_uuids.ps1
```

## 📝 ملاحظات / Notes

1. **UUIDs صالحة** يجب أن تكون بتنسيق: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
   **Valid UUIDs** must be in format: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
   حيث x هي hex digits (0-9, a-f)
   where x are hex digits (0-9, a-f)

2. **UUIDs غير صالحة** مثل `cat1-1111-...` أو `item1-1111-...` لن تعمل
   **Invalid UUIDs** like `cat1-1111-...` or `item1-1111-...` won't work

3. **الحل الأفضل** هو استخدام `uuid_generate_v4()` في SQL
   **Best solution** is to use `uuid_generate_v4()` in SQL

## ✅ بعد الإصلاح / After Fixing

بعد إصلاح جميع UUIDs، يجب أن يعمل الملف بدون أخطاء:
After fixing all UUIDs, the file should work without errors:

```sql
-- تحقق من النجاح / Check for success:
SELECT COUNT(*) FROM restaurants;  -- يجب أن يعرض 15
SELECT COUNT(*) FROM categories;  -- يجب أن يعرض 64
SELECT COUNT(*) FROM menu_items;  -- يجب أن يعرض 100+
```

---

**ملاحظة:** تم إصلاح بعض UUIDs بالفعل في الملف. إذا واجهت أخطاء، استخدم `uuid_generate_v4()`.
**Note:** Some UUIDs have already been fixed in the file. If you encounter errors, use `uuid_generate_v4()`.

