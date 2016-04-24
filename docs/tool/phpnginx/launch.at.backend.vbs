Set WshShell = WScript.CreateObject("WScript.Shell")
obj = WshShell.Run("D:\www\start.bat", 0)
set WshShell = Nothing