@echo off
set "LOG=C:\logs\startup_report_log.txt"

rem 1) write a timestamped startup line (append)
echo %DATE% %TIME% - Starting reporting script... >> "%LOG%"

cd "C:\xampp\htdocs\paletprint"

rem 2) launch python.exe, redirect stdout+stderr into the same log
start "" /MIN cmd /C ^
  ""C:\Users\Owner\AppData\Local\Programs\Python\Python313\python.exe" report.pyw" ^
     >> "%LOG%" 2>&1
exit