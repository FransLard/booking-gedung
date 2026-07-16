param(
    [string]$AndroidHome = "$env:LOCALAPPDATA\Android\Sdk"
)

$sdkmanager = "$AndroidHome\cmdline-tools\latest\bin\sdkmanager.bat"

if (-not (Test-Path $sdkmanager)) {
    Write-Host "sdkmanager not found. Downloading Android command-line tools..."
    $url = "https://dl.google.com/android/repository/commandlinetools-win-11076708_latest.zip"
    $zip = "$env:TEMP\cmdline-tools.zip"
    Invoke-WebRequest -Uri $url -OutFile $zip
    Expand-Archive -Path $zip -DestinationPath "$AndroidHome\cmdline-tools_temp" -Force
    New-Item -ItemType Directory -Path "$AndroidHome\cmdline-tools" -Force | Out-Null
    Move-Item -Path "$AndroidHome\cmdline-tools_temp\cmdline-tools\*" -Destination "$AndroidHome\cmdline-tools\latest\" -Force
    Remove-Item -Path "$AndroidHome\cmdline-tools_temp" -Recurse -Force
    Remove-Item -Path $zip -Force
}

Write-Host "Installing Android SDK Platform 36..."
& $sdkmanager "platforms;android-36"

Write-Host "Installing Android BuildTools 28.0.3..."
& $sdkmanager "build-tools;28.0.3"

Write-Host "`nSetting environment variables..."
[Environment]::SetEnvironmentVariable("ANDROID_HOME", $AndroidHome, "User")
[Environment]::SetEnvironmentVariable("ANDROID_SDK_ROOT", $AndroidHome, "User")

Write-Host "`nAccepting licenses..."
& $sdkmanager --licenses

Write-Host "`nDone! Restart terminal, then run: flutter doctor"
