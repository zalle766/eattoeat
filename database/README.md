# 📦 بيانات افتراضية لـ Supabase
# Supabase Seed Data

## 📁 الملفات المتوفرة / Available Files

1. **`supabase_schema.sql`** - مخطط قاعدة البيانات (الجداول)
   - Database schema (tables)

2. **`supabase_seed_data.sql`** - البيانات الافتراضية (المطاعم والمنتجات)
   - Seed data (restaurants and products)

3. **`SUPABASE_SEED_INSTRUCTIONS.md`** - دليل مفصل خطوة بخطوة
   - Detailed step-by-step guide

---

## 🚀 البدء السريع / Quick Start

### الخطوة 1: إنشاء الجداول
### Step 1: Create Tables

1. افتح [Supabase Dashboard](https://app.supabase.com)
2. اختر مشروعك
3. اضغط على **SQL Editor**
4. انسخ محتوى `supabase_schema.sql`
5. الصق في SQL Editor واضغط **Run**

### الخطوة 2: إضافة البيانات
### Step 2: Add Data

1. في نفس SQL Editor
2. انسخ محتوى `supabase_seed_data.sql`
3. الصق واضغط **Run**

---

## 📊 البيانات المضافة / Added Data

- ✅ **8 مطاعم** / 8 Restaurants
- ✅ **32 فئة** / 32 Categories
- ✅ **60+ منتج** / 60+ Products

---

## ⚠️ ملاحظات مهمة / Important Notes

1. **RLS (Row Level Security):**
   - قد تحتاج إلى تعطيل RLS مؤقتاً لإدراج البيانات
   - You may need to temporarily disable RLS to insert data
   - راجع `SUPABASE_SEED_INSTRUCTIONS.md` للتفاصيل
   - See `SUPABASE_SEED_INSTRUCTIONS.md` for details

2. **Service Role Key:**
   - للاستخدام البرمجي، استخدم `service_role` key
   - For programmatic use, use `service_role` key
   - ⚠️ لا تستخدمه في frontend!
   - ⚠️ Don't use it in frontend!

---

## 📖 للمزيد من التفاصيل / For More Details

راجع ملف **`SUPABASE_SEED_INSTRUCTIONS.md`** للحصول على:
- طرق متعددة لإضافة البيانات
- حل المشاكل الشائعة
- أمثلة على الاستعلامات

See **`SUPABASE_SEED_INSTRUCTIONS.md`** for:
- Multiple methods to add data
- Troubleshooting common issues
- Query examples

---

## ✅ التحقق من البيانات / Verify Data

```sql
-- عدد المطاعم
SELECT COUNT(*) FROM restaurants;

-- عدد المنتجات
SELECT COUNT(*) FROM menu_items;

-- عرض مطعم مع منتجاته
SELECT r.name, m.name, m.price
FROM restaurants r
JOIN menu_items m ON m.restaurant_id = r.id
LIMIT 10;
```

---

## 🆘 الدعم / Support

إذا واجهت مشاكل:
- راجع `SUPABASE_SEED_INSTRUCTIONS.md`
- تحقق من [Supabase Docs](https://supabase.com/docs)

If you encounter issues:
- See `SUPABASE_SEED_INSTRUCTIONS.md`
- Check [Supabase Docs](https://supabase.com/docs)

