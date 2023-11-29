<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
use PhpTui\Tui\DisplayBuilder;
use PhpTui\Tui\Extension\Core\Shape\MapResolution;
use PhpTui\Tui\Extension\Core\Shape\MapShape;
use PhpTui\Tui\Extension\Core\Widget\CanvasWidget;

require 'vendor/autoload.php';
$display = DisplayBuilder::default()->build();
$display->clear();
$display->draw(
    CanvasWidget::fromIntBounds(-180, 180, -90, 90)
        ->draw(
            MapShape::default()->resolution(MapResolution::High)
        )
);
