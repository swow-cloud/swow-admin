<?php

declare(strict_types=1);

use PhpTui\Tui\DisplayBuilder;
use PhpTui\Tui\Extension\Bdf\Shape\TextShape;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\CanvasWidget;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Model\Color\AnsiColor;
use PhpTui\Tui\Model\Direction;
use PhpTui\Tui\Model\Layout\Constraint;
use PhpTui\Tui\Model\Marker;
use PhpTui\Tui\Model\Position\FloatPosition;
use PhpTui\Tui\Model\Text\Text;
use PhpTui\Tui\Model\Text\Title;
use PhpTui\Tui\Model\Widget\Borders;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    GridWidget::default()
        ->direction(Direction::Horizontal)
        ->constraints(
            Constraint::percentage(50),
            Constraint::percentage(50),
        )
        ->widgets(
            BlockWidget::default()->borders(Borders::ALL)->titles(Title::fromString('Left')),
            GridWidget::default()
                ->direction(Direction::Vertical)
                ->constraints(
                    Constraint::percentage(50),
                    Constraint::percentage(50),
                )
                ->widgets(
                    BlockWidget::default()->borders(Borders::ALL)->titles(Title::fromString('Top Right'))->widget(
                        ParagraphWidget::fromText(
                            Text::parse(<<<'EOT'
                            The <fg=green>world</> is the totality of <options=bold>entities</>,
                            the whole of reality, or everything that is.[1] The nature of the
                            world has been <fg=red>conceptualized</> differently in different fields. Some
                            conceptions see the world as unique while others talk of a
                            plurality of <bg=green>worlds</>.
                            EOT)
                        )),
                    BlockWidget::default()->borders(Borders::ALL)->titles(Title::fromString('Bottom Right')),
                )
        )
);