<?php

namespace kirchevsky\ESignatures;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

class ESignatures
{
    private string $baseUrl = 'https://esignatures.com/api';
    private string $token;
    private Client $client;
    private ?LoggerInterface $logger;

    public function __construct(string $token, ?LoggerInterface $logger = null)
    {
        $this->token = $token;
        $this->client = new Client(['base_uri' => $this->baseUrl]);
        $this->logger = $logger;
    }

    public function sendContract(array $data): array
    {
        return $this->post('/contracts', $data);
    }

    public function getContract(string $contractId): array
    {
        return $this->get("/contracts/{$contractId}");
    }

    public function withdrawContract(string $contractId): array
    {
        return $this->post("/contracts/{$contractId}/withdraw", []);
    }

    public function addSigner(string $contractId, array $signerData): array
    {
        return $this->post("/contracts/{$contractId}/signers", $signerData);
    }

    public function updateSigner(string $contractId, string $signerId, array $signerData): array
    {
        return $this->post("/contracts/{$contractId}/signers/{$signerId}", $signerData);
    }

    public function resendContract(string $contractId, string $signerId): array
    {
        return $this->post("/contracts/{$contractId}/signers/{$signerId}/send_contract", []);
    }

    public function deleteSigner(string $contractId, string $signerId): array
    {
        return $this->post("/contracts/{$contractId}/signers/{$signerId}/delete", []);
    }

    public function listTemplates(): array
    {
        return $this->get('/templates');
    }

    public function getTemplate(string $templateId): array
    {
        return $this->get("/templates/{$templateId}");
    }

    public function createTemplate(array $data): array
    {
        return $this->post('/templates', $data);
    }

    public function copyTemplate(string $templateId, array $data): array
    {
        return $this->post("/templates/{$templateId}/copy", $data);
    }

    public function listContracts(array $queryParams = []): array
    {
        return $this->get('/contracts', $queryParams);
    }

    public function handleWebhook(callable $handler): void
    {
        $payload = json_decode(file_get_contents('php://input'), true);

        if (!$payload || !isset($payload['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid webhook payload']);
            return;
        }

        try {
            $handler($payload);
            http_response_code(200);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Webhook handling failed', ['exception' => $e]);
            }
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function get(string $endpoint, array $queryParams = []): array
    {
        return $this->request('GET', ltrim($endpoint, '/'), ['query' => array_merge(['token' => $this->token], $queryParams)]);
    }

    private function post(string $endpoint, array $data): array
    {
        return $this->request('POST', ltrim($endpoint, '/'), [
            'query' => ['token' => $this->token],
            'json' => $data,
        ]);
    }

    private function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            // Build the full URL manually
            $fullUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
            if ($this->logger) {
                $this->logger->info('Requesting URL', ['url' => $fullUrl]);
            }

            // Make the request with the full URL
            $response = $this->client->request($method, $fullUrl, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            if ($this->logger) {
                $this->logger->error('API request failed', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'options' => $options,
                    'error' => $e->getMessage(),
                ]);
            }

            throw new \RuntimeException('API request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
