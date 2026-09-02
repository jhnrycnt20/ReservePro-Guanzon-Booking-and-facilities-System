param(
    [string]$SourcePath = "",
    [string]$PublicIcons = "",
    [string]$AndroidRes = ""
)

$Root = Split-Path $PSScriptRoot -Parent
if (-not $SourcePath) { $SourcePath = Join-Path $Root "public\images\guanzon_logoW.png" }
if (-not $PublicIcons) { $PublicIcons = Join-Path $Root "public\icons" }
if (-not $AndroidRes) { $AndroidRes = Join-Path $Root "mobile\android\app\src\main\res" }

Add-Type -AssemblyName System.Drawing

function New-GuanzonIcon {
    param(
        [string]$SrcPath,
        [string]$OutPath,
        [int]$Size
    )

    if ($Size -lt 48) { throw "Invalid icon size: $Size" }

    $src = [System.Drawing.Bitmap]::FromFile($SrcPath)
    $bmp = New-Object System.Drawing.Bitmap $Size, $Size
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.Clear([System.Drawing.Color]::FromArgb(255, 11, 11, 11))

    $pad = [Math]::Max(8, [int][Math]::Round($Size * 0.12))
    $w = $Size - (2 * $pad)
    $h = $Size - (2 * $pad)

    $temp = New-Object System.Drawing.Bitmap $w, $h, ([System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $tg = [System.Drawing.Graphics]::FromImage($temp)
    $tg.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $tg.Clear([System.Drawing.Color]::Transparent)
    $tg.DrawImage($src, 0, 0, $w, $h)
    $tg.Dispose()

    $mint = [System.Drawing.Color]::FromArgb(255, 94, 234, 212)

    for ($y = 0; $y -lt $h; $y++) {
        for ($x = 0; $x -lt $w; $x++) {
            $p = $temp.GetPixel($x, $y)
            $lum = (0.299 * $p.R) + (0.587 * $p.G) + (0.114 * $p.B)

            if ($lum -gt 165) {
                $temp.SetPixel($x, $y, $mint)
            } elseif ($lum -gt 95) {
                $alpha = [Math]::Min(255, [Math]::Max(0, [int](($lum - 95) / 70 * 255)))
                $temp.SetPixel($x, $y, [System.Drawing.Color]::FromArgb($alpha, $mint.R, $mint.G, $mint.B))
            } else {
                $temp.SetPixel($x, $y, [System.Drawing.Color]::Transparent)
            }
        }
    }

    $g.DrawImage($temp, $pad, $pad, $w, $h)
    $dir = Split-Path $OutPath -Parent
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Force -Path $dir | Out-Null
    }
    $bmp.Save($OutPath, [System.Drawing.Imaging.ImageFormat]::Png)

    $g.Dispose()
    $bmp.Dispose()
    $temp.Dispose()
    $src.Dispose()
}

if (-not (Test-Path $SourcePath)) {
    throw "Source logo not found: $SourcePath"
}

New-Item -ItemType Directory -Force -Path $PublicIcons | Out-Null

@(
    @{ Size = 512; Path = Join-Path $PublicIcons "icon-512.png" },
    @{ Size = 192; Path = Join-Path $PublicIcons "icon-192.png" },
    @{ Size = 180; Path = Join-Path $PublicIcons "apple-touch-icon.png" }
) | ForEach-Object {
    New-GuanzonIcon -SrcPath $SourcePath -OutPath $_.Path -Size $_.Size
    Write-Output "wrote $($_.Path)"
}

@(
    @{ Dir = "mipmap-mdpi"; Size = 48 },
    @{ Dir = "mipmap-hdpi"; Size = 72 },
    @{ Dir = "mipmap-xhdpi"; Size = 96 },
    @{ Dir = "mipmap-xxhdpi"; Size = 144 },
    @{ Dir = "mipmap-xxxhdpi"; Size = 192 }
) | ForEach-Object {
    $dir = Join-Path $AndroidRes $_.Dir
    foreach ($name in @("ic_launcher.png", "ic_launcher_round.png", "ic_launcher_foreground.png")) {
        New-GuanzonIcon -SrcPath $SourcePath -OutPath (Join-Path $dir $name) -Size $_.Size
    }
    Write-Output "wrote android $($_.Dir)"
}

Write-Output "Done"
