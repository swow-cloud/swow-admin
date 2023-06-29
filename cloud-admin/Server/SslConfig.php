<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\Server;

use Hyperf\Contract\Arrayable;
use InvalidArgumentException;

/**
 * @method SslConfig setCertificate(string $cert)
 * @method SslConfig setCertificateKey(string $key)
 * @method SslConfig setVerifyPeer(bool $bool)
 * @method SslConfig setVerifyPeerName(null|bool|string $name)
 * @method SslConfig setAllowSelfSigned(bool $bool)
 * @method string getCertificate()
 * @method string getCertificateKey()
 * @method bool getVerifyPeer()
 * @method null|bool|string getVerifyPeerName()
 * @method bool getAllowSelfSigned()
 */
class SslConfig implements Arrayable
{
    /**
     * @var array{certificate:string,certificate_key:string,verify_peer:bool,verify_peer_name:bool,allow_self_signed:bool}
     */
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->setCertificate($this->config['certificate'] ?? '')
            ->setCertificateKey($config['certificate_key'] ?? '')
            ->setVerifyPeer($this->config['verify_peer'] ?? false)
            ->setVerifyPeerName($this->config['verify_peer_name'] ?? false)
            ->setAllowSelfSigned($this->config['allow_self_signed'] ?? false);
    }

    /**
     * @param mixed $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->config[$name]);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @param mixed $name
     * @return null|bool|mixed|string
     */
    public function __get($name)
    {
        return $this->config[$name] ?? null;
    }

    /**
     * @param mixed $name
     * @param mixed $arguments
     * @return null|$this|bool|mixed|string
     */
    public function __call($name, $arguments)
    {
        $prefix = strtolower(substr($name, 0, 3));
        if (in_array($prefix, ['set', 'get'])) {
            $propertyName = strtolower(substr($name, 3));

            return $prefix === 'set' ? $this->set($propertyName, ...$arguments) : $this->__get($propertyName);
        }

        throw new InvalidArgumentException(sprintf('Invalid method %s', $name));
    }

    public function toArray(): array
    {
        return $this->config;
    }

    /**
     * @param mixed $name
     * @param mixed $value
     * @return $this
     */
    protected function set($name, $value): self
    {
        $this->config[$name] = $value;

        return $this;
    }
}
