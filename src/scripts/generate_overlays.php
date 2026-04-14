<?php
/**
 * Generates sample overlay PNG images with transparency for Camagru.
 * Run once: php src/scripts/generate_overlays.php
 */

$dir = dirname(__DIR__) . '/public/overlays/';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$size = 480;

// ---- Overlay 1: Photo Frame ----
function makeFrame(string $path, int $size): void
{
    $img = imagecreatetruecolor($size, $size);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);

    $border     = (int)($size * 0.05);
    $frameColor = imagecolorallocatealpha($img, 30, 30, 30, 10);
    $white      = imagecolorallocatealpha($img, 255, 255, 255, 20);

    // Outer rectangle
    for ($i = 0; $i < $border; $i++) {
        imagerectangle($img, $i, $i, $size - 1 - $i, $size - 1 - $i, $frameColor);
    }
    // Corner decorations
    $c = imagecolorallocatealpha($img, 200, 160, 50, 5);
    $cs = (int)($size * 0.08);
    imageline($img, 0, 0, $cs, 0, $c);
    imageline($img, 0, 0, 0, $cs, $c);
    imageline($img, $size - 1, 0, $size - 1 - $cs, 0, $c);
    imageline($img, $size - 1, 0, $size - 1, $cs, $c);
    imageline($img, 0, $size - 1, $cs, $size - 1, $c);
    imageline($img, 0, $size - 1, 0, $size - 1 - $cs, $c);
    imageline($img, $size - 1, $size - 1, $size - 1 - $cs, $size - 1, $c);
    imageline($img, $size - 1, $size - 1, $size - 1, $size - 1 - $cs, $c);

    imagepng($img, $path);
    imagedestroy($img);
}

// ---- Overlay 2: Sunglasses ----
function makeSunglasses(string $path, int $size): void
{
    $img = imagecreatetruecolor($size, $size);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    imagealphablending($img, true);

    $black  = imagecolorallocate($img, 20, 20, 20);
    $lens   = imagecolorallocatealpha($img, 0, 0, 0, 50);
    $frame  = imagecolorallocate($img, 30, 30, 30);

    $cy  = (int)($size * 0.40);
    $r   = (int)($size * 0.14);
    $gap = (int)($size * 0.04);
    $lx  = (int)($size / 2) - $gap - $r;
    $rx  = (int)($size / 2) + $gap + $r;

    // Lenses
    imagefilledellipse($img, $lx, $cy, $r * 2, $r * 2, $lens);
    imagefilledellipse($img, $rx, $cy, $r * 2, $r * 2, $lens);
    imageellipse($img, $lx, $cy, $r * 2, $r * 2, $frame);
    imageellipse($img, $rx, $cy, $r * 2, $r * 2, $frame);

    // Bridge
    imageline($img, $lx + $r, $cy, $rx - $r, $cy, $frame);

    // Arms
    imageline($img, $lx - $r, $cy, (int)($size * 0.05), $cy + (int)($size * 0.02), $frame);
    imageline($img, $rx + $r, $cy, (int)($size * 0.95), $cy + (int)($size * 0.02), $frame);

    imagepng($img, $path);
    imagedestroy($img);
}

// ---- Overlay 3: Party hat ----
function makePartyHat(string $path, int $size): void
{
    $img = imagecreatetruecolor($size, $size);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    imagealphablending($img, true);

    $red    = imagecolorallocate($img, 220, 50, 50);
    $yellow = imagecolorallocate($img, 240, 200, 30);
    $white  = imagecolorallocate($img, 255, 255, 255);

    $cx   = (int)($size / 2);
    $base = (int)($size * 0.55);
    $top  = (int)($size * 0.05);
    $hw   = (int)($size * 0.18);

    // Triangle hat
    $points = [$cx, $top, $cx - $hw, $base, $cx + $hw, $base];
    imagefilledpolygon($img, $points, $red);
    imagepolygon($img, $points, $yellow);

    // Stripes
    for ($i = 1; $i <= 3; $i++) {
        $y = $top + (int)(($base - $top) * $i / 4);
        $w = (int)($hw * $i / 4) + 2;
        imageline($img, $cx - $w, $y, $cx + $w, $y, $yellow);
    }

    // Pompom
    imagefilledellipse($img, $cx, $top, (int)($size * 0.06), (int)($size * 0.06), $yellow);

    imagepng($img, $path);
    imagedestroy($img);
}

// ---- Overlay 4: Star sparkles ----
function makeSparkles(string $path, int $size): void
{
    $img = imagecreatetruecolor($size, $size);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    imagealphablending($img, true);

    $yellow = imagecolorallocatealpha($img, 255, 220, 0, 10);
    $white  = imagecolorallocatealpha($img, 255, 255, 255, 20);

    $stars = [
        [0.15, 0.15, 20], [0.85, 0.12, 16], [0.08, 0.70, 14],
        [0.90, 0.75, 18], [0.50, 0.05, 12], [0.20, 0.88, 10],
        [0.78, 0.45, 15], [0.05, 0.40, 12],
    ];

    foreach ($stars as [$rx, $ry, $rs]) {
        $x = (int)($rx * $size);
        $y = (int)($ry * $size);
        drawStar($img, $x, $y, $rs, $yellow, $white);
    }

    imagepng($img, $path);
    imagedestroy($img);
}

function drawStar($img, int $cx, int $cy, int $r, $color, $inner): void
{
    $points = [];
    for ($i = 0; $i < 10; $i++) {
        $angle  = deg2rad($i * 36 - 90);
        $radius = ($i % 2 === 0) ? $r : (int)($r * 0.45);
        $points[] = (int)($cx + $radius * cos($angle));
        $points[] = (int)($cy + $radius * sin($angle));
    }
    imagefilledpolygon($img, $points, $color);
}

// ---- Overlay 5: Mustache ----
function makeMustache(string $path, int $size): void
{
    $img = imagecreatetruecolor($size, $size);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    imagealphablending($img, true);

    $black = imagecolorallocate($img, 20, 20, 20);

    $cy = (int)($size * 0.62);
    $r1 = (int)($size * 0.12);
    $r2 = (int)($size * 0.09);
    $cx = (int)($size / 2);

    imagefilledellipse($img, $cx - $r1, $cy, $r1 * 2, (int)($r1 * 1.3), $black);
    imagefilledellipse($img, $cx + $r1, $cy, $r1 * 2, (int)($r1 * 1.3), $black);

    $maskColor = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefilledellipse($img, $cx, $cy + (int)($r1 * 0.3), (int)($r1 * 1.2), (int)($r1 * 1.0), $maskColor);

    imagepng($img, $path);
    imagedestroy($img);
}

makeFrame($dir . 'frame.png', $size);
makeSunglasses($dir . 'sunglasses.png', $size);
makePartyHat($dir . 'party_hat.png', $size);
makeSparkles($dir . 'sparkles.png', $size);
makeMustache($dir . 'mustache.png', $size);

echo "Overlays generated in $dir\n";
