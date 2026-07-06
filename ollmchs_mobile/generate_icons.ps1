Add-Type -AssemblyName System.Drawing

$srcPath = "C:\Users\Lab IX\Documents\proj\ollmchs-library\ollmchs_mobile"
$source = [System.Drawing.Image]::FromFile("$srcPath\assets\icons\app_icon.png")

function Resize-Icon($size, $outPath) {
    $bmp = New-Object System.Drawing.Bitmap($size, $size)
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.DrawImage($source, 0, 0, $size, $size)
    $g.Dispose()
    $bmp.Save($outPath, [System.Drawing.Imaging.ImageFormat]::Png)
    $bmp.Dispose()
    Write-Host "  Created: $outPath"
}

# ---- Android mipmap ----
Write-Host "=== Android Icons ==="
Resize-Icon 48 "$srcPath\android\app\src\main\res\mipmap-mdpi\ic_launcher.png"
Resize-Icon 72 "$srcPath\android\app\src\main\res\mipmap-hdpi\ic_launcher.png"
Resize-Icon 96 "$srcPath\android\app\src\main\res\mipmap-xhdpi\ic_launcher.png"
Resize-Icon 144 "$srcPath\android\app\src\main\res\mipmap-xxhdpi\ic_launcher.png"
Resize-Icon 192 "$srcPath\android\app\src\main\res\mipmap-xxxhdpi\ic_launcher.png"

# ---- iOS AppIcon ----
Write-Host "=== iOS Icons ==="
$iosPath = "$srcPath\ios\Runner\Assets.xcassets\AppIcon.appiconset"
Resize-Icon 20 "$iosPath\Icon-App-20x20@1x.png"
Resize-Icon 40 "$iosPath\Icon-App-20x20@2x.png"
Resize-Icon 60 "$iosPath\Icon-App-20x20@3x.png"
Resize-Icon 29 "$iosPath\Icon-App-29x29@1x.png"
Resize-Icon 58 "$iosPath\Icon-App-29x29@2x.png"
Resize-Icon 87 "$iosPath\Icon-App-29x29@3x.png"
Resize-Icon 40 "$iosPath\Icon-App-40x40@1x.png"
Resize-Icon 80 "$iosPath\Icon-App-40x40@2x.png"
Resize-Icon 120 "$iosPath\Icon-App-40x40@3x.png"
Resize-Icon 120 "$iosPath\Icon-App-60x60@2x.png"
Resize-Icon 180 "$iosPath\Icon-App-60x60@3x.png"
Resize-Icon 76 "$iosPath\Icon-App-76x76@1x.png"
Resize-Icon 152 "$iosPath\Icon-App-76x76@2x.png"
Resize-Icon 167 "$iosPath\Icon-App-83.5x83.5@2x.png"
# Copy original 1024x1024 for App Store
Copy-Item "$srcPath\assets\icons\app_icon.png" "$iosPath\Icon-App-1024x1024@1x.png" -Force
Write-Host "  Created: $iosPath\Icon-App-1024x1024@1x.png"

# ---- Web Icons ----
Write-Host "=== Web Icons ==="
$webPath = "$srcPath\web\icons"
Resize-Icon 192 "$webPath\Icon-192.png"
Resize-Icon 512 "$webPath\Icon-512.png"
Resize-Icon 192 "$webPath\Icon-maskable-192.png"
Resize-Icon 512 "$webPath\Icon-maskable-512.png"

# ---- Favicon ----
Write-Host "=== Favicon ==="
Resize-Icon 48 "$srcPath\web\favicon.png"

# ---- Notification Icon ----
Write-Host "=== Notification Icon ==="
Resize-Icon 96 "$srcPath\android\app\src\main\res\drawable\notification_icon.png"

$source.Dispose()
Write-Host "`nAll icons generated successfully!"
