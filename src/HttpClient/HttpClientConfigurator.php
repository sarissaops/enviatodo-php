<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\HttpClient;

use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\UriFactoryInterface;
use SarissaOps\Enviatodo\Assert;
use SarissaOps\Enviatodo\HttpClient\Plugin\AddPathPlugin;
use SarissaOps\Enviatodo\HttpClient\Plugin\History;
use SarissaOps\Enviatodo\HttpClient\Plugin\ReplaceUriPlugin;

/**
 * Assembles the PluginClient: base URI + the three Enviatodo auth headers +
 * last-response tracking. The wrapped PSR-18 client is discovered by default
 * and injectable for tests and custom backends.
 */
final class HttpClientConfigurator
{
    public const DEFAULT_ENDPOINT = 'https://apiqav2.enviatodo.mx/index.php/';

    private string $endpoint = self::DEFAULT_ENDPOINT;

    private bool $debug = false;

    private string $token = '';

    private string $apiKey = 'enviatodo';

    private string $appName = 'custom';

    /**
     * Preferred request timeout in seconds. PSR-18 defines no standard
     * timeout, so this is a contract for the caller (e.g. a Symfony bundle
     * applying it to its backend client), not something this package enforces
     * itself. Null means no preference.
     */
    private ?float $timeout = null;

    private ?UriFactoryInterface $uriFactory = null;

    private ?ClientInterface $httpClient = null;

    private History $responseHistory;

    public function __construct()
    {
        $this->responseHistory = new History();
    }

    public function createConfiguredClient(): PluginClient
    {
        Assert::stringNotEmpty($this->token, 'A token is required. Provide it via setToken() (see ENVIATODO_TOKEN).');

        $endpointUri = $this->getUriFactory()->createUri($this->endpoint);
        $plugins = [
            new Plugin\AddHostPlugin($endpointUri),
            new AddPathPlugin($endpointUri->getPath()),
            new Plugin\HeaderDefaultsPlugin([
                'Authorization' => 'Bearer ' . $this->token,
                'x-api-key' => $this->apiKey,
                'x-enviatodo-app' => $this->appName,
            ]),
            new Plugin\HistoryPlugin($this->responseHistory),
        ];

        if ($this->debug) {
            $plugins[] = new ReplaceUriPlugin($this->getUriFactory()->createUri($this->endpoint));
        }

        return new PluginClient($this->getHttpClient(), $plugins);
    }

    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function setAppName(string $appName): self
    {
        $this->appName = $appName;

        return $this;
    }

    public function getTimeout(): ?float
    {
        return $this->timeout;
    }

    public function setTimeout(float $seconds): self
    {
        Assert::greaterThan($seconds, 0, 'Timeout must be a positive number of seconds.');
        $this->timeout = $seconds;

        return $this;
    }

    public function setUriFactory(UriFactoryInterface $uriFactory): self
    {
        $this->uriFactory = $uriFactory;

        return $this;
    }

    public function setHttpClient(ClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    public function getResponseHistory(): History
    {
        return $this->responseHistory;
    }

    private function getUriFactory(): UriFactoryInterface
    {
        if (null === $this->uriFactory) {
            $this->uriFactory = Psr17FactoryDiscovery::findUrlFactory();
        }

        return $this->uriFactory;
    }

    private function getHttpClient(): ClientInterface
    {
        if (null === $this->httpClient) {
            $this->httpClient = Psr18ClientDiscovery::find();
        }

        return $this->httpClient;
    }
}
