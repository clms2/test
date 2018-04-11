@echo off
for /d /r "C:\Users\Administrator\Desktop\empty\" %%i in (*) do (Rd /q /s "%%i" 2>nul)
for /d /r "C:\Users\Administrator\Desktop\empty_houtai\" %%i in (*) do (Rd /q /s "%%i" 2>nul)
exit