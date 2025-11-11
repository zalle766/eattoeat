# سكريبت لإضافة مجلد frontend إلى Git
Write-Host "إضافة مجلد frontend إلى Git..." -ForegroundColor Green

# إضافة جميع ملفات frontend (باستثناء node_modules و dist)
git add frontend/package.json
git add frontend/vite.config.js
git add frontend/tailwind.config.js
git add frontend/postcss.config.js
git add frontend/index.html
git add frontend/src/
git add frontend/public/
git add frontend/Dockerfile
git add frontend/nginx.conf
git add frontend/.gitignore

# إضافة ملفات README إذا كانت موجودة
if (Test-Path "frontend/README.md") {
    git add frontend/README.md
}

Write-Host "تم إضافة ملفات frontend إلى Git" -ForegroundColor Green
Write-Host "الآن قم بتنفيذ: git commit -m 'Add frontend directory'" -ForegroundColor Yellow
Write-Host "ثم: git push" -ForegroundColor Yellow

