<?php

declare(strict_types=1);

const BRAND_DIR = __DIR__ . '/../public/assets/images/brand';
const SOURCE_PNG = __DIR__ . '/../public/assets/images/aorer6.png';
const SOURCE_SVG = BRAND_DIR . '/aorern-medalhao-aorer6.svg';
const UPSCALED_PNG = BRAND_DIR . '/aorer6-upscaled-2400.png';
const HQ_SVG = BRAND_DIR . '/aorern-medalhao-aorer6-hq.svg';
const FINAL_PNG = BRAND_DIR . '/aorern-medalhao-aorer6-3200.png';
const VECTOR_SILHOUETTE = BRAND_DIR . '/aorer6-silhueta.svg';
const FONT_FILE = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';

main();

function main(): void
{
    if (!extension_loaded('gd')) {
        fwrite(STDERR, "GD extension is required.\n");
        exit(1);
    }

    foreach ([SOURCE_PNG, SOURCE_SVG, FONT_FILE] as $path) {
        if (!is_file($path)) {
            fwrite(STDERR, "Missing required file: {$path}\n");
            exit(1);
        }
    }

    $upscaled = createUpscaledCentralImage(SOURCE_PNG, 2400);
    imagepng($upscaled, UPSCALED_PNG, 9);

    generateHighResSvg(HQ_SVG, SOURCE_SVG, UPSCALED_PNG);
    generateFinalPng(FINAL_PNG, $upscaled, FONT_FILE);
    generateSilhouetteSvg(VECTOR_SILHOUETTE, SOURCE_PNG);

    imagedestroy($upscaled);
}

function createUpscaledCentralImage(string $sourcePath, int $targetHeight): GdImage
{
    $source = imagecreatefrompng($sourcePath);
    if ($source === false) {
        throw new RuntimeException('Failed to open source PNG.');
    }

    imagealphablending($source, false);
    imagesavealpha($source, true);

    $srcWidth = imagesx($source);
    $srcHeight = imagesy($source);
    $targetWidth = (int) round($srcWidth * ($targetHeight / $srcHeight));

    $scaled = imagecreatetruecolor($targetWidth, $targetHeight);
    imagealphablending($scaled, false);
    imagesavealpha($scaled, true);
    $transparent = imagecolorallocatealpha($scaled, 0, 0, 0, 127);
    imagefilledrectangle($scaled, 0, 0, $targetWidth, $targetHeight, $transparent);

    imagecopyresampled(
        $scaled,
        $source,
        0,
        0,
        0,
        0,
        $targetWidth,
        $targetHeight,
        $srcWidth,
        $srcHeight
    );

    // Mild sharpen after scaling to keep edges from getting soft.
    imageconvolution($scaled, [
        [-1, -1, -1],
        [-1, 20, -1],
        [-1, -1, -1],
    ], 12, 0);

    imagedestroy($source);

    return $scaled;
}

function generateHighResSvg(string $targetSvg, string $sourceSvg, string $pngPath): void
{
    $svg = file_get_contents($sourceSvg);
    $base64 = base64_encode((string) file_get_contents($pngPath));
    $updated = preg_replace(
        '#href="data:image/png;base64,[^"]*"#s',
        'href="data:image/png;base64,' . $base64 . '"',
        $svg,
        1
    );

    if ($updated === null) {
        throw new RuntimeException('Failed to build high-resolution SVG.');
    }

    file_put_contents($targetSvg, $updated);
}

function generateFinalPng(string $targetPath, GdImage $central, string $fontPath): void
{
    $size = 3200;
    $canvas = imagecreatetruecolor($size, $size);
    imagealphablending($canvas, true);
    imagesavealpha($canvas, true);

    $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
    imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);

    $cx = (int) ($size / 2);
    $cy = (int) ($size / 2);

    $blue = color($canvas, '#020062');
    $white = color($canvas, '#fbfbfd');
    $shadow = imagecolorallocatealpha($canvas, 0, 0, 0, 96);
    $goldStops = ['#fff3c4', '#f4d97a', '#c79b2d', '#f2d470', '#fff1b6'];
    $goldText = color($canvas, '#efcf6b');

    imagefilledellipse($canvas, $cx, $cy + 85, 2850, 2850, $shadow);
    imagefilledellipse($canvas, $cx, $cy, 2832, 2832, $blue);
    drawGoldRing($canvas, $cx, $cy, 2832, 58, $goldStops);
    drawOutline($canvas, $cx, $cy, 2700, colorAlpha($canvas, '#fff3c4', 60), 4);

    imagefilledellipse($canvas, $cx, $cy, 2016, 2016, $white);
    drawOutline($canvas, $cx, $cy, 2172, colorAlpha($canvas, '#fff3c4', 60), 4);
    drawGoldRing($canvas, $cx, $cy, 2064, 48, $goldStops);

    drawArcText(
        $canvas,
        'ASSOCIAÇÃO DOS OFICIAIS DA RESERVA DO EXÉRCITO',
        $fontPath,
        96,
        $goldText,
        $cx,
        $cy,
        1160,
        205,
        335
    );
    drawArcText(
        $canvas,
        'RIO GRANDE DO NORTE',
        $fontPath,
        78,
        $goldText,
        $cx,
        $cy,
        1275,
        25,
        155
    );

    drawCenteredText($canvas, '★', $fontPath, 92, $goldText, 345, $cy + 35);
    drawCenteredText($canvas, '★', $fontPath, 92, $goldText, $size - 345, $cy + 35);

    imagealphablending($central, true);
    imagesavealpha($central, true);
    $centralWidth = (int) round($size * 0.63);
    $centralHeight = (int) round(imagesy($central) * ($centralWidth / imagesx($central)));
    $centralX = (int) round(($size - $centralWidth) / 2);
    $centralY = (int) round(($size - $centralHeight) / 2 + 70);

    imagecopyresampled(
        $canvas,
        $central,
        $centralX,
        $centralY,
        0,
        0,
        $centralWidth,
        $centralHeight,
        imagesx($central),
        imagesy($central)
    );

    imagepng($canvas, $targetPath, 9);
    imagedestroy($canvas);
}

function drawGoldRing(GdImage $image, int $cx, int $cy, int $diameter, int $thickness, array $hexStops): void
{
    $steps = max(12, $thickness);
    for ($i = 0; $i < $steps; $i++) {
        $t = $steps === 1 ? 0 : $i / ($steps - 1);
        $color = interpolatePalette($image, $hexStops, $t);
        imagesetthickness($image, 1);
        imageellipse($image, $cx, $cy, $diameter - $i, $diameter - $i, $color);
    }
}

function drawOutline(GdImage $image, int $cx, int $cy, int $diameter, int $color, int $thickness): void
{
    imagesetthickness($image, $thickness);
    imageellipse($image, $cx, $cy, $diameter, $diameter, $color);
    imagesetthickness($image, 1);
}

function drawArcText(
    GdImage $image,
    string $text,
    string $fontPath,
    int $fontSize,
    int $color,
    int $cx,
    int $cy,
    int $radius,
    float $startAngle,
    float $endAngle
): void {
    $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
    if ($chars === false || $chars === []) {
        return;
    }

    $weights = [];
    $total = 0.0;
    foreach ($chars as $char) {
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $char);
        $width = $bbox ? abs($bbox[2] - $bbox[0]) : $fontSize;
        $advance = $char === ' ' ? $fontSize * 0.72 : $width + $fontSize * 0.12;
        $weights[] = $advance;
        $total += $advance;
    }

    $angleSpan = $endAngle - $startAngle;
    $cursor = 0.0;
    foreach ($chars as $index => $char) {
        $mid = $cursor + ($weights[$index] / 2);
        $ratio = $total > 0 ? $mid / $total : 0;
        $angle = deg2rad($startAngle + ($angleSpan * $ratio));
        $x = (int) round($cx + cos($angle) * $radius);
        $y = (int) round($cy + sin($angle) * $radius);
        $rotation = rad2deg($angle) + 90;
        if ($char !== ' ') {
            drawCenteredText($image, $char, $fontPath, $fontSize, $color, $x, $y, $rotation);
        }
        $cursor += $weights[$index];
    }
}

function drawCenteredText(
    GdImage $image,
    string $text,
    string $fontPath,
    int $fontSize,
    int $color,
    int $x,
    int $y,
    float $angle = 0
): void {
    $bbox = imagettfbbox($fontSize, $angle, $fontPath, $text);
    if ($bbox === false) {
        return;
    }

    $minX = min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
    $maxX = max($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
    $minY = min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
    $maxY = max($bbox[1], $bbox[3], $bbox[5], $bbox[7]);

    $drawX = (int) round($x - (($maxX + $minX) / 2));
    $drawY = (int) round($y - (($maxY + $minY) / 2));

    imagettftext($image, $fontSize, $angle, $drawX, $drawY, $color, $fontPath, $text);
}

function generateSilhouetteSvg(string $targetPath, string $sourcePath): void
{
    $source = imagecreatefrompng($sourcePath);
    if ($source === false) {
        throw new RuntimeException('Failed to open source image for silhouette.');
    }

    $width = imagesx($source);
    $height = imagesy($source);
    [$minX, $minY, $maxX, $maxY] = alphaBounds($source);

    $points = [];
    for ($y = $minY; $y <= $maxY; $y += 4) {
        $left = null;
        $right = null;
        for ($x = $minX; $x <= $maxX; $x++) {
            if (isOpaquePixel($source, $x, $y)) {
                $left = $x;
                break;
            }
        }
        for ($x = $maxX; $x >= $minX; $x--) {
            if (isOpaquePixel($source, $x, $y)) {
                $right = $x;
                break;
            }
        }
        if ($left !== null && $right !== null) {
            $points[] = [$left, $y];
        }
    }
    for ($y = $maxY; $y >= $minY; $y -= 4) {
        $right = null;
        for ($x = $maxX; $x >= $minX; $x--) {
            if (isOpaquePixel($source, $x, $y)) {
                $right = $x;
                break;
            }
        }
        if ($right !== null) {
            $points[] = [$right, $y];
        }
    }

    imagedestroy($source);

    if (count($points) < 3) {
        throw new RuntimeException('Silhouette extraction failed.');
    }

    $path = [];
    foreach ($points as [$x, $y]) {
        $path[] = sprintf('%s %.1f %.1f', empty($path) ? 'M' : 'L', $x + 0.5, $y + 0.5);
    }

    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$width} {$height}" role="img" aria-label="Silhueta vetorial simplificada da arte central">
  <path d="{$path[0]}
SVG;

    for ($i = 1, $count = count($path); $i < $count; $i++) {
        $svg .= "\n    {$path[$i]}";
    }

    $svg .= "\n    Z\" fill=\"#d4af37\" stroke=\"#8f6b1b\" stroke-width=\"3\"/>\n</svg>\n";
    file_put_contents($targetPath, $svg);
}

function alphaBounds(GdImage $image): array
{
    $width = imagesx($image);
    $height = imagesy($image);
    $minX = $width - 1;
    $minY = $height - 1;
    $maxX = 0;
    $maxY = 0;

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            if (isOpaquePixel($image, $x, $y)) {
                $minX = min($minX, $x);
                $minY = min($minY, $y);
                $maxX = max($maxX, $x);
                $maxY = max($maxY, $y);
            }
        }
    }

    return [$minX, $minY, $maxX, $maxY];
}

function isOpaquePixel(GdImage $image, int $x, int $y): bool
{
    $rgba = imagecolorat($image, $x, $y);
    $alpha = ($rgba >> 24) & 0x7F;

    return $alpha < 118;
}

function interpolatePalette(GdImage $image, array $hexStops, float $t): int
{
    $count = count($hexStops);
    if ($count === 1) {
        return color($image, $hexStops[0]);
    }

    $segment = min($count - 2, (int) floor($t * ($count - 1)));
    $localT = ($t * ($count - 1)) - $segment;
    $a = hexToRgb($hexStops[$segment]);
    $b = hexToRgb($hexStops[$segment + 1]);

    $r = (int) round($a[0] + (($b[0] - $a[0]) * $localT));
    $g = (int) round($a[1] + (($b[1] - $a[1]) * $localT));
    $bl = (int) round($a[2] + (($b[2] - $a[2]) * $localT));

    return imagecolorallocatealpha($image, $r, $g, $bl, 0);
}

function color(GdImage $image, string $hex): int
{
    [$r, $g, $b] = hexToRgb($hex);
    return imagecolorallocatealpha($image, $r, $g, $b, 0);
}

function colorAlpha(GdImage $image, string $hex, int $alpha): int
{
    [$r, $g, $b] = hexToRgb($hex);
    return imagecolorallocatealpha($image, $r, $g, $b, $alpha);
}

function hexToRgb(string $hex): array
{
    $hex = ltrim($hex, '#');
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ];
}
