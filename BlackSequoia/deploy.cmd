@echo off
del /F /S /Q inst > deploy.log
xcopy "black_sequoia" "inst" /E /H /R /Y > deploy.log
xcopy "bin\bs.zip" ".\" /E /H /R /Y >> deploy.log
cd inst 
..\bin\7z.exe a ..\bs.zip . >> deploy.log
cd ..
bin\nmisync.exe AKIAIM27OHKGAWZIP3HQ l7Uovv1ejucvU5KhqVpl8UuGHdvDhxVdqsOFAnv8 ..\bs.zip iridium2 bs.zip
bin\nmisync.exe AKIAIM27OHKGAWZIP3HQ l7Uovv1ejucvU5KhqVpl8UuGHdvDhxVdqsOFAnv8 ..\black_sequoia\installer\install.sh iridium2 install.sh
