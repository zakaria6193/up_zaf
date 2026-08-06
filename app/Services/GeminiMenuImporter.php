<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GeminiMenuImporter
{
    /**
     * Extract menu categories and items from a photo of a paper/digital menu.
     *
     * @return list<array{name: string, items: list<array{name: string, description: string|null, price: float|null}>}>
     */
    public function extract(UploadedFile $image): array
    {
        $apiKey = config('services.gemini.api_key');
        $model = (string) config('services.gemini.model', 'gemini-flash-latest');

        if (! filled($apiKey)) {
            throw new RuntimeException('Gemini API is not configured.');
        }

        $mime = $image->getMimeType() ?: 'image/jpeg';
        $base64 = base64_encode((string) file_get_contents($image->getRealPath()));

        $prompt = <<<'PROMPT'
You are extracting a restaurant menu from a photo.
Return ONLY valid JSON matching this shape:
{
  "categories": [
    {
      "name": "Category name",
      "items": [
        {
          "name": "Dish name",
          "description": "Optional short description or null",
          "price": 12.50
        }
      ]
    }
  ]
}

Rules:
- Group dishes under clear category names (create sensible categories if headings are missing).
- Prices must be numbers only (no currency symbols). Use null if price is unreadable.
- Ignore non-menu content (addresses, QR codes, social handles, watermarks).
- Prefer the restaurant's language as written on the menu.
- Do not invent dishes that are not visible.
PROMPT;

        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            rawurlencode($model),
        );

        try {
            $response = Http::timeout(60)
                ->acceptJson()
                ->asJson()
                ->post($url.'?key='.urlencode((string) $apiKey), [
                    'contents' => [[
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mime,
                                    'data' => $base64,
                                ],
                            ],
                        ],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'responseMimeType' => 'application/json',
                    ],
                ])
                ->throw();
        } catch (RequestException $e) {
            throw $this->mapRequestException($e);
        } catch (Throwable $e) {
            Log::warning('Gemini menu import failed', [
                'message' => $e->getMessage(),
            ]);

            throw new RuntimeException('Menu import failed. Please try again.', previous: $e);
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini returned an empty response.');
        }

        $decoded = json_decode($this->stripCodeFences($text), true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Could not parse menu data from Gemini.');
        }

        return $this->normalize($decoded);
    }

    public function configured(): bool
    {
        return filled(config('services.gemini.api_key'));
    }

    protected function mapRequestException(RequestException $e): RuntimeException
    {
        $response = $e->response;
        $status = $response?->status();
        $body = $response?->json() ?? [];
        $apiMessage = data_get($body, 'error.message');
        $apiMessage = is_string($apiMessage) ? trim($apiMessage) : '';

        Log::warning('Gemini menu import HTTP failure', [
            'status' => $status,
            'message' => $apiMessage !== '' ? $apiMessage : $e->getMessage(),
        ]);

        if ($status === 429) {
            return new RuntimeException(
                'Gemini quota exceeded. Check billing/quota or try again later.',
                previous: $e,
            );
        }

        $short = $apiMessage !== ''
            ? mb_substr($apiMessage, 0, 200)
            : 'HTTP '.($status ?? 'error');

        return new RuntimeException(
            'Gemini could not read this menu image ('.$short.'). Try a clearer photo.',
            previous: $e,
        );
    }

    protected function stripCodeFences(string $text): string
    {
        $trimmed = trim($text);

        if (Str::startsWith($trimmed, '```')) {
            $trimmed = preg_replace('/^```(?:json)?\s*/i', '', $trimmed) ?? $trimmed;
            $trimmed = preg_replace('/\s*```$/', '', $trimmed) ?? $trimmed;
        }

        return trim($trimmed);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{name: string, items: list<array{name: string, description: string|null, price: float|null}>}>
     */
    protected function normalize(array $payload): array
    {
        $categories = $payload['categories'] ?? $payload;

        if (! is_array($categories)) {
            return [];
        }

        $normalized = [];

        foreach ($categories as $category) {
            if (! is_array($category)) {
                continue;
            }

            $name = trim((string) ($category['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $items = [];
            foreach (($category['items'] ?? []) as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $itemName = trim((string) ($item['name'] ?? ''));
                if ($itemName === '') {
                    continue;
                }

                $description = isset($item['description']) ? trim((string) $item['description']) : null;
                $price = $item['price'] ?? null;
                $price = is_numeric($price) ? round((float) $price, 2) : null;

                $items[] = [
                    'name' => mb_substr($itemName, 0, 255),
                    'description' => $description !== null && $description !== ''
                        ? mb_substr($description, 0, 1000)
                        : null,
                    'price' => $price,
                ];
            }

            if ($items === []) {
                continue;
            }

            $normalized[] = [
                'name' => mb_substr($name, 0, 255),
                'items' => $items,
            ];
        }

        return $normalized;
    }
}
