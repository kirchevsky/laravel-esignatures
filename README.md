# Laravel eSignatures Module

This package provides an easy-to-use Laravel integration with [eSignatures.io](https://esignatures.io), allowing you to send, manage, and track contracts programmatically.

---

## Features

- Send contracts using eSignatures.io templates.
- Use custom PDFs for signing without templates.
- Manage signers (add, update, delete).
- Handle webhooks for real-time contract updates.
- Withdraw contracts.
- List and copy templates.
- Logging support for debugging and monitoring.

---

## Installation

1. Install the package via Composer:

    ```bash
    composer require kirchevsky/laravel-esignatures
    ```

2. Publish the configuration (optional):

    ```bash
    php artisan vendor:publish --tag=esignatures-config
    ```

3. Add your eSignatures.io API token to the `.env` file:

    ```env
    ESIGNATURES_TOKEN=your-secret-token
    ```

---

## Usage

### Basic Initialization

Create an instance of the module:

```php
use YourNamespace\ESignatures\ESignatures;

$eSignatures = new ESignatures(env('ESIGNATURES_TOKEN'));
```

### Sending a Contract with a Template

```php
$response = $eSignatures->sendContract([
    'template_id' => 'template-id',
    'signers' => [
        [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'mobile' => '+1234567890',
        ],
    ],
    'title' => 'Contract Title',
    'metadata' => 'custom-metadata',
]);

if ($response['status'] === 'queued') {
    echo "Contract sent successfully!";
}
```


### Fetching Contract Details

```php
$contract = $eSignatures->getContract('contract-id');
echo "Contract Title: " . $contract['data']['contract']['title'];
```

### Withdrawing a Contract

```php
$response = $eSignatures->withdrawContract('contract-id');
if ($response['status'] === 'queued') {
    echo "Contract withdrawn successfully!";
}
```

### Managing Signers

#### Add a Signer:

```php
$response = $eSignatures->addSigner('contract-id', [
    'name' => 'New Signer',
    'email' => 'new@example.com',
    'mobile' => '+1234567890',
]);
```

#### Update a Signer:

```php
$response = $eSignatures->updateSigner('contract-id', 'signer-id', [
    'name' => 'Updated Signer',
    'email' => 'updated@example.com',
]);
```

#### Delete a Signer:

```php
$response = $eSignatures->deleteSigner('contract-id', 'signer-id');
```

### Webhook Handling

Handle webhook notifications from eSignatures.io:

1. Add a route in your `web.php`:

    ```php
    Route::post('/webhook/esignatures', [WebhookController::class, 'handle']);
    ```

2. Create the `WebhookController`:

    ```php
    use Illuminate\Http\Request;
    use YourNamespace\ESignatures\ESignatures;

    class WebhookController extends Controller
    {
        public function handle(Request $request)
        {
            $eSignatures = new ESignatures(env('ESIGNATURES_TOKEN'));
            $eSignatures->handleWebhook(function ($payload) {
                // Process the webhook payload
                Log::info('Webhook received', $payload);
            });
        }
    }
    ```

---

## Advanced Usage

### List Templates

```php
$templates = $eSignatures->listTemplates();
foreach ($templates['data'] as $template) {
    echo "Template ID: {$template['template_id']}, Name: {$template['template_name']}\n";
}
```

### Copy a Template

```php
$response = $eSignatures->copyTemplate('template-id', [
    'title' => 'New Template',
    'placeholder_fields' => [
        [
            'api_key' => 'field-key',
            'value' => 'Field Value',
        ],
    ],
]);
```

---

## Debugging & Logging

If you want to log API requests and responses, pass a PSR-3 logger (like Laravel's Log facade) during initialization:

```php
use Illuminate\Support\Facades\Log;

$eSignatures = new ESignatures(env('ESIGNATURES_TOKEN'), Log::channel('stack'));
```

---

## Contributing

1. Fork the repository.
2. Create a new branch (`git checkout -b feature-name`).
3. Make your changes.
4. Commit your changes (`git commit -am 'Add new feature'`).
5. Push to the branch (`git push origin feature-name`).
6. Open a Pull Request.

---

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## Support

If you encounter any issues or have questions, feel free to open an issue on [GitHub](https://github.com/kirchevsky/laravel-esignatures).
