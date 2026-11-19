param([switch]$Catalogo, [string]$CarpetaProyecto)
$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing
$revision = Join-Path $PSScriptRoot 'revision'
$masterFolder = if ($CarpetaProyecto) { (Resolve-Path -LiteralPath $CarpetaProyecto).Path } elseif ($Catalogo) { Join-Path $PSScriptRoot 'juego_completo\REMEMBERMIND_DIGRAMAS' } else { $revision }
$artifactDir = if ($Catalogo) { Join-Path $masterFolder '01_Casos_de_uso' } else { Join-Path $revision 'entrega' }
$output = (New-Item -ItemType Directory -Path $artifactDir -Force).FullName
$modelName = if ($Catalogo) { 'DIAGRAMAS_REMEMBERMIND.mdj' } else { 'REMEMBERMIND_DIGRAMAS.mdj' }
$project = Get-Content -LiteralPath (Join-Path $masterFolder $modelName) -Encoding utf8 -Raw | ConvertFrom-Json -AsHashtable
$package = $project.ownedElements[0].ownedElements | Where-Object name -eq '01 Casos de uso'
$names = @{}
function ModelElements($item) {
    $item
    foreach ($child in $item.ownedElements) {
        ModelElements $child
    }
}
$models = @(ModelElements $package)
foreach ($o in $models) {
    if ($o.name) { $names[$o._id] = $o.name }
}
function Export-Name([string]$name) {
    if ($name -match '^(DCU|CU)-01\.') { return 'DCU-01_Acceso_al_sistema.png' }
    $plain = $name.Normalize([Text.NormalizationForm]::FormD) -replace '\p{Mn}', ''
    ($plain -replace '\. ', '_' -replace ' ', '_') + '.png'
}

function Brush([string]$color) {
    [Drawing.SolidBrush]::new([Drawing.ColorTranslator]::FromHtml($color))
}
function Pen([string]$color, [float]$width = 1.15) {
    [Drawing.Pen]::new([Drawing.ColorTranslator]::FromHtml($color), $width)
}
function Pt([double]$x, [double]$y) { [Drawing.PointF]::new($x, $y) }
function Box($v) { [Drawing.RectangleF]::new($v.left, $v.top, $v.width, $v.height) }
function Center($v) { Pt ($v.left + $v.width / 2.0) ($v.top + $v.height / 2.0) }
function Text([string]$label, [Drawing.RectangleF]$rect, [int]$size, [bool]$bold = $false,
              [bool]$left = $false) {
    $style = if ($bold) { [Drawing.FontStyle]::Bold } else { [Drawing.FontStyle]::Regular }
    $font = [Drawing.Font]::new('Arial', $size, $style, [Drawing.GraphicsUnit]::Pixel)
    $fmt = [Drawing.StringFormat]::new()
    try {
        $fmt.Alignment = if ($left) { [Drawing.StringAlignment]::Near } else { [Drawing.StringAlignment]::Center }
        $fmt.LineAlignment = [Drawing.StringAlignment]::Center
        $g.DrawString($label, $font, $ink, $rect, $fmt)
    } finally { $fmt.Dispose(); $font.Dispose() }
}
function Anchor($node, [Drawing.PointF]$other) {
    $mid = Center $node
    $dx = $other.X - $mid.X
    $dy = $other.Y - $mid.Y
    $rx = $node.width / 2.0
    $ry = $node.height / 2.0
    if ($node._type -eq 'UMLUseCaseView') {
        $scale = 1 / [Math]::Sqrt(($dx/$rx)*($dx/$rx) + ($dy/$ry)*($dy/$ry))
    } else {
        $scale = 1 / [Math]::Max([Math]::Abs($dx/$rx), [Math]::Abs($dy/$ry))
    }
    Pt ($mid.X + $dx*$scale) ($mid.Y + $dy*$scale)
}
function Actor-Arm($node, [Drawing.PointF]$other) {
    $mid = Center $node
    $side = if ($other.X -gt $mid.X) { 17 } else { -17 }
    Pt ($mid.X + $side) ($node.top + 38)
}
function Arrow([Drawing.PointF]$start, [Drawing.PointF]$tip, [bool]$triangle) {
    $dx = $tip.X - $start.X
    $dy = $tip.Y - $start.Y
    $length = [Math]::Sqrt($dx*$dx + $dy*$dy)
    $vx = $dx / $length
    $vy = $dy / $length
    $depth = if ($triangle) { 13.0 } else { 9.0 }
    $width = if ($triangle) { 6.0 } else { 4.0 }
    $a = Pt ($tip.X-$vx*$depth-$vy*$width) ($tip.Y-$vy*$depth+$vx*$width)
    $b = Pt ($tip.X-$vx*$depth+$vy*$width) ($tip.Y-$vy*$depth-$vx*$width)
    if ($triangle) {
        $path = [Drawing.PointF[]]@($tip, $a, $b)
        $g.FillPolygon($white, $path)
        $g.DrawPolygon($normal, $path)
    } else {
        $g.DrawLine($normal, $a, $tip)
        $g.DrawLine($normal, $b, $tip)
    }
}

function Export-Sheet($sheet) {
$nodes = @{}
$furthestX = 0
$furthestY = 0
foreach ($v in $sheet.ownedViews) {
    $nodes[$v._id] = $v
    if ($v.Contains('left')) {
        $furthestX = [Math]::Max($furthestX, $v.left + $v.width)
        $furthestY = [Math]::Max($furthestY, $v.top + $v.height)
    }
}
$scale = [Math]::Min(4.0, 8400.0 / [Math]::Max($furthestX+46, $furthestY+44))
$exportName = Export-Name $sheet.name
$img = [Drawing.Bitmap]::new([int](($furthestX+46)*$scale), [int](($furthestY+44)*$scale), [Drawing.Imaging.PixelFormat]::Format24bppRgb)
$img.SetResolution(300, 300)
$g = [Drawing.Graphics]::FromImage($img)
$ink = Brush '#182434'
$white = Brush '#FFFFFF'
$blue = Brush '#EEF5FC'
$paper = Brush '#F4F7FA'
$normal = Pen '#3B4858'
$framePen = Pen '#233247'
$dotted = Pen '#647386'
$dotted.DashStyle = [Drawing.Drawing2D.DashStyle]::Dot
$dashed = Pen '#3B4858'
$dashed.DashStyle = [Drawing.Drawing2D.DashStyle]::Dash
try {
    $g.Clear([Drawing.Color]::White)
    $g.SmoothingMode = [Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $g.TextRenderingHint = [Drawing.Text.TextRenderingHint]::AntiAliasGridFit
    $g.ScaleTransform($scale, $scale)
    $g.TranslateTransform(22, 16)

    $v = $sheet.ownedViews | Where-Object _type -eq 'UMLUseCaseSubjectView'
    $g.DrawRectangle($framePen, [float]$v.left, [float]$v.top, [float]$v.width, [float]$v.height)
    Text $names[$v.model.'$ref'] ([Drawing.RectangleF]::new($v.left, $v.top+4, $v.width, 27)) 17 $true
    $frame = Box $v
    $avoid = @($sheet.ownedViews | Where-Object _type -eq 'UMLUseCaseView' | ForEach-Object { Box $_ })

    $lines = @()
    foreach ($v in $sheet.ownedViews) {
        if (!$v.tail -or !$v.head) { continue }
        $source = $nodes[$v.tail.'$ref']
        $target = $nodes[$v.head.'$ref']
        $a = Anchor $source (Center $target)
        $b = Anchor $target (Center $source)
        if ($v._type -eq 'UMLAssociationView') {
            if ($source._type -eq 'UMLActorView') {
                $a = Actor-Arm $source (Center $target)
                $b = Anchor $target $a
            } elseif ($target._type -eq 'UMLActorView') {
                $b = Actor-Arm $target (Center $source)
                $a = Anchor $source $b
            }
        }
        $linePen = switch ($v._type) {
            'UMLIncludeView' { $dashed }
            'UMLExtendView' { $dashed }
            'UMLNoteLinkView' { $dotted }
            default { $normal }
        }
        $g.DrawLine($linePen, $a, $b)
        if ($v._type -eq 'UMLGeneralizationView') { Arrow $a $b $true }
        if ($v._type -in @('UMLIncludeView','UMLExtendView')) {
            Arrow $a $b $false
        }
        $lines += @{ view = $v; a = $a; b = $b }
    }

    $labels = @()
    foreach ($line in $lines) {
        $kind = $line.view._type
        if ($kind -notin @('UMLIncludeView','UMLExtendView')) { continue }
        $label = if ($kind -eq 'UMLIncludeView') { '«include»' } else { '«extend»' }
        $a = $line.a
        $b = $line.b
        $dx = $b.X - $a.X
        $dy = $b.Y - $a.Y
        $norm = [Math]::Sqrt($dx*$dx + $dy*$dy)
        $nx = $dy / $norm
        $ny = -$dx / $norm
        $dist = 33 * [Math]::Abs($nx) + 10 * [Math]::Abs($ny) + 5
        $chosen = $null
        :positions foreach ($gap in @($dist, ($dist+12), ($dist+25), ($dist+40), ($dist+55))) {
            foreach ($t in @(0.55, 0.43, 0.63, 0.30, 0.75, 0.20, 0.85)) {
                foreach ($sign in @(1, -1)) {
                    $x = $a.X + $dx*$t + $nx*$gap*$sign
                    $y = $a.Y + $dy*$t + $ny*$gap*$sign
                    $box = [Drawing.RectangleF]::new($x-32, $y-8, 64, 16)
                    if ($box.Left -lt $frame.Left+4 -or $box.Right -gt $frame.Right-4) { continue }
                    if ($avoid.Where({ $box.IntersectsWith($_) }).Count -or
                        $labels.Where({ $box.IntersectsWith($_) }).Count) { continue }
                    $interference = $false
                    foreach ($other in $lines) {
                        for ($sample = 0; $sample -le 32; $sample++) {
                            $f = $sample / 32.0
                            $px = $other.a.X + ($other.b.X-$other.a.X)*$f
                            $py = $other.a.Y + ($other.b.Y-$other.a.Y)*$f
                            if ($box.Contains([float]$px,[float]$py)) { $interference = $true; break }
                        }
                        if ($interference) { break }
                    }
                    if (!$interference) { $chosen = $box; break positions }
                }
            }
        }
        if ($null -eq $chosen) {
            throw "No hay espacio para $label en $($sheet.name)"
        }
        $labels += $chosen
        Text $label $chosen 13
    }

    foreach ($v in $sheet.ownedViews) {
        if ($v._type -eq 'UMLUseCaseView') {
            $label = $names[$v.model.'$ref']
            $isMain = $v.fillColor -eq '#EEF5FC'
            $r = Box $v
            $fill = if ($isMain) { $blue } else { $white }
            $g.FillEllipse($fill, $r)
            $g.DrawEllipse($normal, $r)
            Text $label $r $(if ($isMain) {19} else {16}) $true
        }
        if ($v._type -eq 'UMLActorView') {
            $c = Center $v
            $x = $c.X
            $y = $v.top + 5
            $g.DrawEllipse($normal, [float]($x-11), [float]$y, 22.0, 22.0)
            $g.DrawLine($normal, (Pt $x ($y+22)), (Pt $x ($y+55)))
            $g.DrawLine($normal, (Pt ($x-17) ($y+33)), (Pt ($x+17) ($y+33)))
            $g.DrawLine($normal, (Pt $x ($y+55)), (Pt ($x-18) ($y+77)))
            $g.DrawLine($normal, (Pt $x ($y+55)), (Pt ($x+18) ($y+77)))
            Text $names[$v.model.'$ref'] ([Drawing.RectangleF]::new($v.left, $y+85, $v.width, 44)) 12 $true
        }
        if ($v._type -eq 'UMLTextView') {
            $isTitle = $v.top -lt 50
            $captionSize = if ($isTitle) { [int]($v.font -split ';')[1] } else { 15 }
            Text $v.text (Box $v) $captionSize $isTitle
        }
        if ($v._type -eq 'UMLNoteView') {
            $r = Box $v
            $x2 = $r.Right
            $y2 = $r.Bottom
            $fold = 9
            $outline = [Drawing.PointF[]]@((Pt $r.Left $r.Top),(Pt ($x2-$fold) $r.Top),
                (Pt $x2 ($r.Top+$fold)),(Pt $x2 $y2),(Pt $r.Left $y2))
            $g.FillPolygon($paper, $outline)
            $g.DrawPolygon($normal, $outline)
            $g.DrawLine($normal, (Pt ($x2-$fold) $r.Top), (Pt ($x2-$fold) ($r.Top+$fold)))
            $g.DrawLine($normal, (Pt ($x2-$fold) ($r.Top+$fold)), (Pt $x2 ($r.Top+$fold)))
            Text $v.text ([Drawing.RectangleF]::new($r.Left+6, $r.Top+2, $r.Width-13, $r.Height-4)) 13 $false $true
        }
    }
    $img.Save((Join-Path $output $exportName), [Drawing.Imaging.ImageFormat]::Png)
    $thumbW = 740
    $thumbH = [int]($thumbW * $img.Height / $img.Width)
    $thumb = [Drawing.Bitmap]::new($thumbW, $thumbH)
    $tg = [Drawing.Graphics]::FromImage($thumb)
    try {
        $tg.InterpolationMode = [Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
        $tg.DrawImage($img, 0, 0, $thumbW, $thumbH)
        $thumb.Save((Join-Path $thumbDir $exportName), [Drawing.Imaging.ImageFormat]::Png)
    } finally { $tg.Dispose(); $thumb.Dispose() }
} finally {
    foreach ($object in @($g,$img,$ink,$white,$blue,$paper,$normal,$framePen,$dotted,$dashed)) {
      $object.Dispose()
    }
}
Write-Output $exportName
}

$thumbDir = (New-Item -ItemType Directory -Force -Path (Join-Path $masterFolder '..\revision_visual')).FullName
$sheets = @($models | Where-Object _type -eq 'UMLUseCaseDiagram')
foreach ($sheet in $sheets) { Export-Sheet $sheet }
if (!$Catalogo) {
    Copy-Item -LiteralPath (Join-Path $revision 'DCU-01_Especificacion_funcional.md') -Destination $output
}
