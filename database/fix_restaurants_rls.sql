-- =====================================================
-- إصلاح RLS Policies للمطاعم
-- Fix RLS Policies for Restaurants
-- =====================================================
-- هذا الملف يضمن أن RLS policies تسمح بالوصول للمطاعم
-- This file ensures RLS policies allow access to restaurants

-- 1. التحقق من أن RLS مفعل على جدول restaurants
-- Check if RLS is enabled on restaurants table
SELECT tablename, rowsecurity 
FROM pg_tables 
WHERE schemaname = 'public' AND tablename = 'restaurants';

-- 2. حذف السياسات القديمة إذا كانت موجودة (اختياري)
-- Drop old policies if they exist (optional)
DROP POLICY IF EXISTS "Anyone can view active restaurants" ON restaurants;
DROP POLICY IF EXISTS "Public can view active restaurants" ON restaurants;
DROP POLICY IF EXISTS "Restaurants are viewable by everyone" ON restaurants;

-- 3. إنشاء/إعادة إنشاء السياسة الصحيحة
-- Create/recreate the correct policy
CREATE POLICY "Anyone can view active restaurants"
  ON restaurants FOR SELECT
  USING (is_active = true);

-- 4. التحقق من أن السياسة تم إنشاؤها بنجاح
-- Verify the policy was created successfully
SELECT 
  schemaname,
  tablename,
  policyname,
  permissive,
  roles,
  cmd,
  qual
FROM pg_policies 
WHERE tablename = 'restaurants';

-- 5. اختبار الاستعلام (يجب أن يعيد المطاعم النشطة)
-- Test query (should return active restaurants)
SELECT COUNT(*) as active_restaurants_count
FROM restaurants
WHERE is_active = true;

-- 6. عرض جميع المطاعم النشطة
-- Show all active restaurants
SELECT 
  id,
  name,
  is_active,
  rating,
  city
FROM restaurants
WHERE is_active = true
ORDER BY rating DESC
LIMIT 10;

