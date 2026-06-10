Add-Type -AssemblyName System.IO.Compression.FileSystem
$zipPath = "$pwd\deployment-build.zip"
$missing = @(
    "public/icons/icon-72.png",
    "public/icons/icon-96.png",
    "public/icons/icon-128.png",
    "public/icons/icon-144.png",
    "public/icons/icon-152.png",
    "public/icons/icon-192.png",
    "public/icons/icon-384.png",
    "public/icons/icon-512.png",
    "public/sw.js",
    "public/workbox-9d79bed4.js"
)
try {
    $zip = [System.IO.Compression.ZipFile]::Open($zipPath, [System.IO.Compression.ZipArchiveMode]::Update)
    $count = 0
    foreach ($rel in $missing) {
        $fullPath = "$pwd\$($rel -replace '/', '\')"
        if (Test-Path $fullPath) {
            Write-Output "Adding: $rel"
            [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $fullPath, $rel, [System.IO.Compression.CompressionLevel]::Optimal)
            $count++
        }
    }
    $zip.Dispose()
    Write-Output "Done: added $count files"
} catch {
    Write-Output "ERROR: $_"
}
