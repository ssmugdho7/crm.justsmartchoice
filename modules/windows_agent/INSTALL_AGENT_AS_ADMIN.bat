@echo off
set /p TOKEN=Paste the Smart Network Manager agent token from CRM settings: 
powershell -ExecutionPolicy Bypass -Command "New-NetFirewallRule -DisplayName 'Smart Network Manager Agent 8099' -Direction Inbound -LocalPort 8099 -Protocol TCP -Action Allow -Profile Private"
echo Starting Smart Network Agent. Keep this window open for testing.
powershell -ExecutionPolicy Bypass -File "%~dp0SmartNetworkAgent.ps1" -Token "%TOKEN%"
pause
