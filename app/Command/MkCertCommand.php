<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin project.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */

namespace App\Command;

use /*
 * Annotation class for defining a Hyperf Command.
 *
 * @Annotation
 * @Target({"CLASS"})
 */
Hyperf\Command\Annotation\Command;
use /*
 * Class HyperfCommand
 *
 * Represents a command in the Hyperf framework.
 *
 * @package Hyperf\Command
 */
Hyperf\Command\Command as HyperfCommand;
use /*
 * The ContainerInterface represents a dependency injection container.
 *
 * It provides a minimal set of methods to define and retrieve services.
 *
 * @link https://www.php-fig.org/psr/psr-11/
 */
Psr\Container\ContainerInterface;
use /*
 * Class InputOption
 *
 * Represents a command line option.
 *
 * @package Symfony\Component\Console\Input
 */
Symfony\Component\Console\Input\InputOption;

/**
 * Class MkCertCommand
 *
 * This class represents a command for generating SSL certificates using the mkcert tool.
 * The command can be invoked by running `php bin/hyperf.php mkcert:command`.
 *
 * Available options:
 * - `--domain-name` or `-d`: Set the domain name for generating the SSL certificate.
 * - `--cert-file` or `-c`: Set the path for the generated certificate file.
 * - `--key-file` or `-k`: Set the path for the generated key file.
 *
 * Example usage: `php bin/hyperf.php mkcert:command -d example.com -c ./ssl/cert.pem -k ./ssl/key.pem`
 */
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
