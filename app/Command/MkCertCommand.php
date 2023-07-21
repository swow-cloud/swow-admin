<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace App\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class MkCertCommand extends HyperfCommand
{
    protected string $command = 'which mkcert';

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('mkcert:command');
    }

    public function configure(): void
    {
        parent::configure();
        $this->setDescription('调用mkcert生成ssl证书,https://github.com/FiloSottile/mkcert 命令:php bin/hyperf.php mkcert:command -d 127.0.0.1 -c ./ssl/localhost.pem -k ./ssl/localhost-key.pem');
        $this->addOption('domain-name', 'd', InputOption::VALUE_REQUIRED, 'set domain name');
        $this->addOption('cert-file', 'c', InputOption::VALUE_REQUIRED, 'set cert-file path');
        $this->addOption('key-file', 'k', InputOption::VALUE_REQUIRED, 'set key-file path');
    }

    public function handle(): void
    {
        if (empty(shell_exec($this->command))) {
            $this->error('请先安装mkcert工具,下载地址:https://github.com/FiloSottile/mkcert');

            return;
        }

        @unlink($this->input->getOption('cert-file'));
        @unlink($this->input->getOption('key-file'));

        $command = sprintf(
            'mkcert  -cert-file %s -key-file %s %s',
            $this->input->getOption('cert-file'),
            $this->input->getOption('key-file'),
            $this->input->getOption('domain-name')
        );
        $output = shell_exec($command);
        $this->line((string) $output);
    }
}
