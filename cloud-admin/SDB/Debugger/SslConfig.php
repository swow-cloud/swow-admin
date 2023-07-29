<?php

declare(strict_types=1);
/**
 * This file is part of Cloud-Admin.
 *
 * @link     https://www.cloud-admin.jayjay.cn
 * @document https://wiki.cloud-admin.jayjay.cn
 * @license  https://github.com/swow-cloud/swow-admin/blob/master/LICENSE
 */
namespace CloudAdmin\SDB\Debugger;

use RuntimeException;

class SslConfig
{
    final public function __construct(
        public bool $ssl,
        public string $certificate,
        public string $certificateKey,
        public bool $verifyPeer,
        public bool $verifyPeerName,
        public bool $allowSelfSigned
    ) {
    }

    /**
     * Retrieves the SSL flag for the object.
     *
     * @return bool the value of the SSL flag
     */
    public function getSsl(): bool
    {
        return $this->ssl ?? false;
    }

    /**
     * Retrieves the assigned certificate for the object.
     *
     * @return string the assigned certificate
     */
    public function getCertificate(): string
    {
        // Check if file exists
        if (! file_exists($this->certificate)) {
            throw new RuntimeException("File does not exist: {$this->certificate}");
        }

        // Fetch file content
        $key = file_get_contents($this->certificate);

        // Try parsing it as a certificate
        /* @noinspection PhpComposerExtensionStubsInspection */

        if (openssl_x509_read($key) === false) {
            throw new RuntimeException('Not a valid certificate');
        }

        return $key;
    }

    /**
     * Sets the certificate for the object.
     *
     * @param string $certificate the certificate to set
     *
     * @return self returns the updated object instance
     */
    public function setCertificate(string $certificate): self
    {
        $this->certificate = $certificate;
        return $this;
    }

    /**
     * Returns the certificate key.
     *
     * @return string the certificate key
     */
    public function getCertificateKey(): string
    {
        // Check if file exists
        if (! file_exists($this->certificateKey)) {
            throw new RuntimeException("File does not exist: {$this->certificateKey}");
        }

        // Fetch file content
        $key = file_get_contents($this->certificateKey);

        // Try parsing it as a certificate
        /* @noinspection PhpComposerExtensionStubsInspection */

        if (openssl_x509_read($key) === false) {
            throw new RuntimeException('Not a valid certificate key');
        }

        return $key;
    }

    /**
     * Sets the certificate key for the object.
     *
     * @param string $certificateKey the certificate key
     * @return self returns the modified object
     *
     * @phpstan-param string $certificateKey
     * @phpstan-return self
     */
    public function setCertificateKey(string $certificateKey): self
    {
        $this->certificateKey = $certificateKey;
        return $this;
    }

    /**
     * Gets the value of verifyPeer.
     *
     * @return bool the value of verifyPeer
     */
    public function getVerifyPeer(): bool
    {
        return $this->verifyPeer;
    }

    /**
     * Set the option to verify the peer certificate during SSL/TLS connection.
     *
     * @param bool $verifyPeer Whether to verify the peer certificate. True to verify, false to disable verification.
     *
     * @phpstan-return self
     */
    public function setVerifyPeer(bool $verifyPeer): self
    {
        $this->verifyPeer = $verifyPeer;
        return $this;
    }

    /**
     * Returns the value of verifyPeerName property.
     *
     * @return bool the value of verifyPeerName property
     */
    public function getVerifyPeerName(): bool
    {
        return $this->verifyPeerName;
    }

    /**
     * Sets whether to verify the peer's certificate name during SSL/TLS handshake.
     *
     * This method allows you to configure whether the peer's certificate name should be verified
     * during the SSL/TLS handshake. By default, this verification is enabled.
     *
     * @param bool $verifyPeerName Whether to verify the peer's certificate name. Set to true to enable
     *                             verification, or false to disable it.
     * @return self returns the current instance of the class
     */
    public function setVerifyPeerName(bool $verifyPeerName): self
    {
        $this->verifyPeerName = $verifyPeerName;
        return $this;
    }

    /**
     * Get the value of allowSelfSigned flag.
     *
     * @return bool the value of allowSelfSigned flag
     */
    public function getAllowSelfSigned(): bool
    {
        return $this->allowSelfSigned;
    }

    /**
     * Sets whether the certificate allows self-signed certificates.
     *
     * @param bool $allowSelfSigned whether to allow self-signed certificates
     */
    public function setAllowSelfSigned(bool $allowSelfSigned): self
    {
        $this->allowSelfSigned = $allowSelfSigned;
        return $this;
    }

    /**
     * Returns an array representation of the object.
     *
     * The returned array includes the following keys:
     * - 'certificate': The value of the 'certificate' property.
     * - 'certificate_key': The value of the 'certificate_key' property.
     * - 'verify_peer': The value of the 'verify_peer' property.
     * - 'verify_peer_name': The value of the 'verify_peer_name' property.
     * - 'allow_self_signed': The value of the 'allow_self_signed' property.
     *
     * @return array the array representation of the object
     * @phpstan-return  array{
     *      ssl: string,
     *      certificate: string,
     *      certificate_key: string,
     *      verify_peer: bool,
     *      verify_peer_name: bool,
     *      allow_self_signed: bool
     *  }
     */
    public function toArray(): array
    {
        return [
            'ssl' => $this->getSsl(),
            'certificate' => $this->getCertificate(),
            'certificate_key' => $this->getCertificateKey(),
            'verify_peer' => $this->getVerifyPeer(),
            'verify_peer_name' => $this->getVerifyPeerName(),
            'allow_self_signed' => $this->getAllowSelfSigned(),
        ];
    }
}
