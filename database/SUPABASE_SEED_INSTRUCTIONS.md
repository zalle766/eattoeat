# دليل إضافة البيانات الافتراضية إلى Supabase
# Guide for Adding Seed Data to Supabase

## 📋 نظرة عامة / Overview

هذا الملف يشرح كيفية إضافة المطاعم والمنتجات الافتراضية إلى قاعدة بيانات Supabase.

This file explains how to add sample restaurants and products to your Supabase database.

---

## 🚀 الطريقة الأولى: استخدام Supabase Dashboard (الأسهل)
## Method 1: Using Supabase Dashboard (Easiest)

### الخطوات / Steps:

1. **افتح Supabase Dashboard**
   - اذهب إلى [supabase.com](https://supabase.com)
   - سجل الدخول إلى مشروعك
   - Go to [supabase.com](https://supabase.com)
   - Log in to your project

2. **افتح SQL Editor**
   - من القائمة الجانبية، اختر **SQL Editor**
   - From the sidebar, select **SQL Editor**

3. **انسخ محتوى الملف**
   - افتح ملف `database/supabase_seed_data.sql`
   - انسخ كل المحتوى
   - Open the file `database/supabase_seed_data.sql`
   - Copy all the content

4. **الصق في SQL Editor**
   - الصق المحتوى في SQL Editor
   - اضغط **Run** أو **Execute**
   - Paste the content in SQL Editor
   - Click **Run** or **Execute**

5. **تحقق من النتائج**
   - يجب أن ترى رسالة نجاح
   - تحقق من الجداول في **Table Editor**
   - You should see a success message
   - Check the tables in **Table Editor**

---

## 🔧 الطريقة الثانية: استخدام Supabase Client (للمطورين)
## Method 2: Using Supabase Client (For Developers)

### ملاحظة مهمة / Important Note:
قد تحتاج إلى تعطيل RLS مؤقتاً أو استخدام `service_role` key لإدراج البيانات.

You may need to temporarily disable RLS or use `service_role` key to insert data.

### مثال على الكود / Code Example:

```javascript
import { createClient } from '@supabase/supabase-js'

// استخدم service_role key (احذر: لا تستخدمه في frontend!)
// Use service_role key (Warning: Don't use it in frontend!)
const supabase = createClient(
  'YOUR_SUPABASE_URL',
  'YOUR_SERVICE_ROLE_KEY' // service_role key من Supabase Dashboard
)

// قراءة ملف SQL وتنفيذه
// Read SQL file and execute
async function seedDatabase() {
  // يمكنك استخدام مكتبة لتنفيذ SQL
  // You can use a library to execute SQL
  // أو تقسيم البيانات إلى insert statements
  // Or split data into insert statements
}
```

---

## 🛠️ الطريقة الثالثة: استخدام Supabase CLI
## Method 3: Using Supabase CLI

### التثبيت / Installation:

```bash
npm install -g supabase
```

### التنفيذ / Execution:

```bash
# تسجيل الدخول
# Login
supabase login

# ربط المشروع
# Link project
supabase link --project-ref YOUR_PROJECT_REF

# تنفيذ ملف SQL
# Execute SQL file
supabase db execute -f database/supabase_seed_data.sql
```

---

## ⚠️ حل المشاكل الشائعة / Troubleshooting

### مشكلة: خطأ في RLS (Row Level Security)
### Problem: RLS Error

**الحل / Solution:**

1. **تعطيل RLS مؤقتاً:**
   ```sql
   -- في Supabase Dashboard > SQL Editor
   ALTER TABLE restaurants DISABLE ROW LEVEL SECURITY;
   ALTER TABLE categories DISABLE ROW LEVEL SECURITY;
   ALTER TABLE menu_items DISABLE ROW LEVEL SECURITY;
   
   -- بعد إدراج البيانات، فعّل RLS مرة أخرى
   -- After inserting data, enable RLS again
   ALTER TABLE restaurants ENABLE ROW LEVEL SECURITY;
   ALTER TABLE categories ENABLE ROW LEVEL SECURITY;
   ALTER TABLE menu_items ENABLE ROW LEVEL SECURITY;
   ```

2. **أو تعديل السياسات:**
   ```sql
   -- السماح بإدراج البيانات بدون مصادقة
   -- Allow inserting data without authentication
   CREATE POLICY "Allow public insert on restaurants"
   ON restaurants FOR INSERT
   WITH CHECK (true);
   ```

### مشكلة: UUIDs مكررة
### Problem: Duplicate UUIDs

**الحل / Solution:**
- إذا كانت البيانات موجودة بالفعل، استخدم `ON CONFLICT`:
  ```sql
  INSERT INTO restaurants (id, name, ...)
  VALUES (...)
  ON CONFLICT (id) DO NOTHING;
  ```

### مشكلة: خطأ في Foreign Keys
### Problem: Foreign Key Error

**الحل / Solution:**
- تأكد من إدراج المطاعم قبل الفئات
- تأكد من إدراج الفئات قبل المنتجات
- Make sure to insert restaurants before categories
- Make sure to insert categories before menu items

---

## 📊 البيانات المضافة / Added Data

### المطاعم / Restaurants (8 مطاعم):
1. مطعم الأصالة (عربي)
2. بيتزا إيطاليا (إيطالي)
3. سوشي طوكيو (ياباني)
4. برجر هاوس (وجبات سريعة)
5. مطعم الصين الذهبي (صيني)
6. مطعم الأطلس (مغربي)
7. مطعم البحر الأبيض (مأكولات بحرية)
8. مطعم الحلويات الشرقية (حلويات)

### الفئات / Categories:
- كل مطعم يحتوي على 4 فئات تقريباً
- Each restaurant has approximately 4 categories

### المنتجات / Menu Items:
- أكثر من 60 منتج متنوع
- More than 60 diverse products

---

## ✅ التحقق من البيانات / Verify Data

بعد إضافة البيانات، تحقق من:

After adding data, verify:

```sql
-- عدد المطاعم
-- Number of restaurants
SELECT COUNT(*) FROM restaurants;

-- عدد الفئات
-- Number of categories
SELECT COUNT(*) FROM categories;

-- عدد المنتجات
-- Number of menu items
SELECT COUNT(*) FROM menu_items;

-- عرض مطعم مع منتجاته
-- Show restaurant with its products
SELECT 
  r.name as restaurant_name,
  c.name as category_name,
  m.name as menu_item_name,
  m.price
FROM restaurants r
LEFT JOIN categories c ON c.restaurant_id = r.id
LEFT JOIN menu_items m ON m.restaurant_id = r.id
WHERE r.id = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
ORDER BY c.display_order, m.name;
```

---

## 🔄 تحديث البيانات / Update Data

إذا أردت تحديث البيانات:

If you want to update data:

```sql
-- مثال: تحديث سعر منتج
-- Example: Update product price
UPDATE menu_items
SET price = 50.00
WHERE id = 'item1-1111-1111-1111-111111111111';

-- مثال: تعطيل منتج
-- Example: Disable a product
UPDATE menu_items
SET is_available = false
WHERE id = 'item1-1111-1111-1111-111111111111';
```

---

## 🗑️ حذف البيانات / Delete Data

إذا أردت حذف البيانات الافتراضية:

If you want to delete the seed data:

```sql
-- حذف المنتجات
-- Delete menu items
DELETE FROM menu_items 
WHERE restaurant_id IN (
  SELECT id FROM restaurants 
  WHERE name IN (
    'مطعم الأصالة', 'بيتزا إيطاليا', 'سوشي طوكيو', 
    'برجر هاوس', 'مطعم الصين الذهبي', 'مطعم الأطلس',
    'مطعم البحر الأبيض', 'مطعم الحلويات الشرقية'
  )
);

-- حذف الفئات
-- Delete categories
DELETE FROM categories 
WHERE restaurant_id IN (
  SELECT id FROM restaurants 
  WHERE name IN (
    'مطعم الأصالة', 'بيتزا إيطاليا', 'سوشي طوكيو', 
    'برجر هاوس', 'مطعم الصين الذهبي', 'مطعم الأطلس',
    'مطعم البحر الأبيض', 'مطعم الحلويات الشرقية'
  )
);

-- حذف المطاعم
-- Delete restaurants
DELETE FROM restaurants 
WHERE name IN (
  'مطعم الأصالة', 'بيتزا إيطاليا', 'سوشي طوكيو', 
  'برجر هاوس', 'مطعم الصين الذهبي', 'مطعم الأطلس',
  'مطعم البحر الأبيض', 'مطعم الحلويات الشرقية'
);
```

---

## 📝 ملاحظات إضافية / Additional Notes

1. **الصور / Images:**
   - يمكنك إضافة صور للمطاعم والمنتجات لاحقاً
   - يمكنك رفع الصور إلى Supabase Storage
   - You can add images for restaurants and products later
   - You can upload images to Supabase Storage

2. **التخصيص / Customization:**
   - يمكنك تعديل الأسعار والأسماء والوصف حسب احتياجاتك
   - You can modify prices, names, and descriptions according to your needs

3. **إضافة المزيد / Add More:**
   - يمكنك إضافة المزيد من المطاعم والمنتجات بنفس الطريقة
   - You can add more restaurants and products in the same way

---

## 🆘 الدعم / Support

إذا واجهت أي مشاكل، تحقق من:
- [Supabase Documentation](https://supabase.com/docs)
- [Supabase Discord](https://discord.supabase.com)

If you encounter any issues, check:
- [Supabase Documentation](https://supabase.com/docs)
- [Supabase Discord](https://discord.supabase.com)

