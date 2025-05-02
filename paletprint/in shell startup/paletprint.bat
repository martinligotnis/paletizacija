@echo off
echo Starting palletizing script... > C:\logs\startup_log.txt
cd "C:\xampp\htdocs\paletprint\"
:: Start the Python script using START command with /B flag (no new window)
start "" /B "C:\Users\Owner\AppData\Local\Programs\Python\Python313\pythonw.exe" "C:\xampp\htdocs\paletprint\main.pyw"
:: Exit the batch file
exit