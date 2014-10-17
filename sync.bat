cd \backup\lgu_jws\backup
md %1
cd \xampp\htdocs\lgu_jws\
xcopy *.* d:\bt-sync\lgu_jws\backup\%1 /s /c /v
