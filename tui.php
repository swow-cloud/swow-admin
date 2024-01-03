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
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Text\Title;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\Direction;
use Swow\Coroutine;
use Swow\Sync\WaitReference;

require 'vendor/autoload.php';

$greeting = 'Hello';

$sayHello = serialize(static function (string $name) use ($greeting): void {
    echo "{$greeting} {$name}!\n";
});
$sayHello = unserialize($sayHello);
$sayHello(Swow::class);
exit;
$socket = new \Swow\Socket(Swow\Socket::TYPE_TCP);
$server = \stream_socket_server('tls://127.0.0.1:9501', context: \stream_context_create([
    'ssl' => [
        'alpn_protocols' => 'h2,http/1.1',
        'local_cert' => __DIR__ . '/ssl/server.crt',
        'local_pk' => __DIR__ . '/ssl/server.key',
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
    ],
]));
$wr = new WaitReference();
Coroutine::run(static function () use ($server): void {
    $conn = \stream_socket_accept($server);
    $payload = \fread($conn, 1024);
    if (\str_contains($payload, 'PRI * HTTP/2.0')) {
        echo "ALPN uses HTTP/2.0\n";
    } else {
        echo "ALPN uses HTTP/1.1\n";
    }
});

$wr::wait($wr);
// echo PHP_EOL;
// $display = DisplayBuilder::default()->build();
// $total = 10;
// for ($done = 0; $done <= $total; ++$done) {
//    // 重置光标位置
//    echo "\r\033[K";
//    $display->clear();
//    $display->draw(
//        GridWidget::default()
//            ->direction(Direction::Horizontal)
//            ->constraints(
//                Constraint::percentage(50),
//                Constraint::percentage(50),
//            )
//            ->widgets(
//                BlockWidget::default()->borders(Borders::ALL)->titles(Title::fromString('Left')),
//                GridWidget::default()
//                    ->direction(Direction::Vertical)
//                    ->constraints(
//                        Constraint::percentage(50),
//                        Constraint::percentage(50),
//                    )
//                    ->widgets(
//                        BlockWidget::default()->borders(Borders::ALL)->titles(Title::fromString('Top Right'))->widget(
//                            \PhpTui\Tui\Extension\Core\Widget\ParagraphWidget::fromText(
//                                \PhpTui\Tui\Text\Text::parse(
//                                    <<<EOT
//                            The <fg=green>{$done}</> is the totality of <options=bold>entities</>,
//                            the whole of reality, or everything that is.[1] The nature of the
//                            world has been <fg=red>conceptualized</> differently in different fields. Some
//                            conceptions see the world as unique while others talk of a
//                            plurality of <bg=green>worlds</>.
//                            EOT
//                                )
//                            )
//                        ),
//                        BlockWidget::default()->borders(Borders::ALL)->titles(Title::fromString('Bottom Right')),
//                    )
//            )
//    );
//
//    // 等待一段时间以模拟进程
//    \usleep(100000);
// }
// echo PHP_EOL;
