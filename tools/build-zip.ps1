<#
  Arma los ZIP para subir a Hostinger (en la carpeta deploy/).

    powershell -ExecutionPolicy Bypass -File tools\build-zip.ps1

  - la-guardia-instalacion.zip  -> primera subida: todo public_html, sin datos de prueba.
  - la-guardia-actualizacion.zip -> actualizaciones: solo código. NO incluye content/
    ni uploads/, así no se pisan los textos, horarios, fotos y la contraseña que el
    cliente cargó desde el panel.

  Usa System.IO.Compression con rutas "/" (Compress-Archive de PowerShell 5.1 guarda
  "\" y en el servidor Linux de Hostinger los archivos quedan mal nombrados).
#>

$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

$project = Split-Path -Parent $PSScriptRoot
$source = Join-Path $project 'public_html'
$outDir = Join-Path $project 'deploy'
New-Item -ItemType Directory -Force -Path $outDir | Out-Null

# Nunca se suben: datos generados en local, backups, intentos de login, temporales.
$alwaysExclude = @(
  '^content/auth\.json$',
  '^content/login-attempts\.json$',
  '^content/backups/',
  '^content/\.lock$',
  '^uploads/(?!\.htaccess$)',
  '\.tmp$'
)
$updateExclude = $alwaysExclude + @('^content/', '^uploads/')

function New-Zip([string]$zipPath, [string[]]$exclude) {
  if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
  $zip = [System.IO.Compression.ZipFile]::Open($zipPath, [System.IO.Compression.ZipArchiveMode]::Create)
  $count = 0
  try {
    Get-ChildItem -Path $source -Recurse -File -Force | ForEach-Object {
      $rel = $_.FullName.Substring($source.Length + 1).Replace('\', '/')
      foreach ($pattern in $exclude) { if ($rel -match $pattern) { return } }
      [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $_.FullName, $rel, [System.IO.Compression.CompressionLevel]::Optimal) | Out-Null
      $count++
    }
  } finally {
    $zip.Dispose()
  }
  $kb = [math]::Round((Get-Item $zipPath).Length / 1KB)
  Write-Host ("{0}: {1} archivos, {2} KB" -f (Split-Path -Leaf $zipPath), $count, $kb)
}

# Aviso si el código de instalación sigue con el valor de fábrica.
$config = Get-Content (Join-Path $source 'admin/config.php') -Raw
if ($config -match "SETUP_CODE = 'CAMBIAR-ESTE-CODIGO'") {
  # Mensajes en ASCII: PowerShell 5.1 lee este archivo sin BOM y rompe los acentos.
  Write-Host "AVISO: SETUP_CODE sigue sin cambiar. Cambialo en admin/config.php (antes de armar el ZIP o despues, desde el Administrador de archivos)." -ForegroundColor Yellow
}

New-Zip (Join-Path $outDir 'la-guardia-instalacion.zip') $alwaysExclude
New-Zip (Join-Path $outDir 'la-guardia-actualizacion.zip') $updateExclude
Write-Host "Listo: $outDir"
