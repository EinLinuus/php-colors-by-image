<?php
declare(strict_types=1);

require_once __DIR__ . "/src/ImageColorDetector.php";

$images = [];

foreach(glob("sample-images/*") as $image_src) {
    try {
        $colors = ImageColorDetector::getColors($image_src);
        $dominant_color = ImageColorDetector::getDominantColor($colors);
        $contrast_color = ImageColorDetector::getContrastColor($dominant_color);
        $gradient_color = [$dominant_color, 1];
        foreach($colors as $color) {
            $contrast_ratio = ImageColorDetector::getContrastRatio($color, $dominant_color);
            if($contrast_ratio <= 2 && $contrast_ratio > $gradient_color[1]) {
                $gradient_color = [$color, $contrast_ratio];
            }
        }
        $images[] = [
            "src" => $image_src,
            "colors" => $colors,
            "dominant_color" => $dominant_color,
            "contrast_color" => $contrast_color,
            "gradient_color" => $gradient_color[0],
        ];
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        continue;
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Image Colors Example</title>
    <style>
        * { box-sizing: border-box }

        body {
            padding: 0;
            margin: 0;
            font-family: sans-serif;
        }

        main {
            margin: 4rem auto;
            max-width: 800px;
            padding: 1rem;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #333;
        }

        .row img {
            width: 100%;
            height: auto;
        }

        .row .colors {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .row .colors > div {
            width: 100px;
            height: 100px;
            border: 1px solid #eee;
        }

        .row .gradient {
            height: 100px;
            width: 300px;
            background: linear-gradient(135deg, var(--from), var(--to));
        }

        .row .content,
        .row .content-gradient {
            height: 100px;
            width: 300px;
            display: grid;
            place-items: center;
            font-size: 1.5rem;
            background: var(--dominant);
            color: var(--contrast);
        }

        .row .content + .content-gradient {
            margin-top: 0.5rem;
        }

        .row .content-gradient {
            background: linear-gradient(135deg, var(--from), var(--to));
        }
    </style>
</head>
<body>

    <main>
        <h1>Image Color Examples</h1>

        <p>
            All images are from <a href="https://unsplash.com/">Unsplash</a>.
        </p>

        <?php foreach($images as $image): ?>
            <div class="row">
                <img src="<?= $image["src"] ?>" alt="">
                <div>
                    <h2>Dominant & Contrast Color</h2>
                    <div class="colors">
                        <div style="background-color: <?= $image["dominant_color"] ?>"></div>
                        <div style="background-color: <?= $image["contrast_color"] ?>"></div>
                    </div>

                    <h2>Main Colors</h2>
                    <div class="colors">
                        <?php foreach($image["colors"] as $color): ?>
                            <div style="background-color: <?= $color ?>"></div>
                        <?php endforeach; ?>
                    </div>

                    <h3>Gradient</h3>
                    <div class="gradient" style="--from: <?= $image["dominant_color"] ?>; --to: <?= $image["gradient_color"] ?>"></div>

                    <h3>Content</h3>
                    <div class="content" style="--dominant: <?= $image["dominant_color"] ?>; --contrast: <?= $image["contrast_color"] ?>;">
                        <span>Hello world</span>
                    </div>
                    <div class="content-gradient" style="--from: <?= $image["dominant_color"] ?>; --to: <?= $image["gradient_color"] ?>; --contrast: <?= $image["contrast_color"] ?>;">
                        <span>Hello world</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </main>

</body>
</html>
