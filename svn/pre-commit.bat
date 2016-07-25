rem 放到repositories对应的项目hooks目录下
@echo off
set SVN_BINDIR="D:\soft\visualSvn\bin"
set SVNLOOK="D:\soft\visualSvn\bin\svnlook.exe"
setlocal
set REPOS=%1
set TXN=%2
rem check that logmessage contains at least 4 characters
%SVN_BINDIR%\svnlook log "%REPOS%" -t "%TXN%" | findstr "...." > nul
if %errorlevel% gtr 0 goto err
exit 0
:err
echo "【必须添加大于4字符的注释】" 1>&2
exit 1
