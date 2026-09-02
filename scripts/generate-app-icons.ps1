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

    $src = [System.Drawing.Bitmap]::FromFile($SrcPath)
    $bmp = New-Object System.Drawing.Bitmap $Size, $Size
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality

    $black = [System.Drawing.Color]::FromArgb(255, 11, 11, 11)
    $mint = [System.Drawing.Color]::FromArgb(255, 94, 234, 212)
    $g.Clear($black)

    $pad = [Math]::Max(10, [int][Math]::Round($Size * 0.14))
    $w = $Size - (2 * $pad)
    $h = $Size - (2 * $pad)

    $temp = New-Object System.Drawing.Bitmap $src.Width, $src.Height, ([System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $tg = [System.Drawing.Graphics]::FromImage($temp)
    $tg.DrawImage($src, 0, 0, $src.Width, $src.Height)
    $tg.Dispose()

    for ($y = 0; $y -lt $h; $y++) {
        $sy = [int][Math]::Floor($y * $temp.Height / $h)
        for ($x = 0; $x -lt $w; $x++) {
            $sx = [int][Math]::Floor($x * $temp.Width / $w)
            $p = $temp.GetPixel($sx, $sy)

            $brightness = ($p.R + $p.G + $p.B) / 3.0
            if ($brightness -gt 140) {
                $strength = [Math]::Min(1.0, ($brightness - 140) / 80.0)
                $r = [int]($black.R + ($mint.R - $black.R) * $strength)
                $gr = [int]($black.G + ($mint.G - $black.G) * $strength)
                $b = [int]($black.B + ($mint.B - $black.B) * $strength)
                $bmp.SetPixel($pad + $x, $pad + $y, [System.Drawing.Color]::FromArgb(255, $r, $gr, $b))
            }
        }
    }

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
