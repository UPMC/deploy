@echo off

mkdir "%SystemRoot%\AdminTools"

xcopy /E /Q /H /R /Y netdom.exe "%WINDIR%\SysWOW64"
xcopy /E /Q /H /R /Y netdom.exe.mui "%WINDIR%\SysWOW64\fr-FR"

xcopy /E /Q /H /R /Y deploy.exe "%SystemRoot%\AdminTools"
xcopy /E /Q /H /R /Y progress "%SystemRoot%\AdminTools"
xcopy /E /Q /H /R /Y schedule.cmd "%SystemRoot%\AdminTools"
xcopy /E /Q /H /R /Y unschedule.cmd "%SystemRoot%\AdminTools"
