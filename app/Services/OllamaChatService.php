<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Talks only to a local Ollama process (127.0.0.1 / localhost / ::1).
 * Laravel still supplies live PSIS facts; the model must not invent stock numbers.
 */
class OllamaChatService
{
    public function isEnabled(): bool
    {
        return (bool) config('psis.ollama.enabled');
    }

    public function baseUrl(): string
    {
        return rtrim((string) config('psis.ollama.url', 'http://127.0.0.1:11434'), '/');
    }

    public function isLocalUrl(?string $url = null): bool
    {
        $host = strtolower((string) parse_url($url ?? $this->baseUrl(), PHP_URL_HOST));

        return in_array($host, ['127.0.0.1', 'localhost', '::1'], true);
    }

    public function reply(User $user, string $question, string $grounding): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        if (! $this->isLocalUrl()) {
            Log::warning('PSIS Ollama URL is not localhost; request skipped.', [
                'url' => $this->baseUrl(),
            ]);

            return null;
        }

        $role = $user->getRoleNames()->first() ?? 'User';
        $model = (string) config('psis.ollama.model', 'llama3.2:3b');
        $timeout = max(5, (int) config('psis.ollama.timeout', 45));

        $system = <<<PROMPT
You are the PECIT Smart Inventory System (PSIS) assistant running on this school's local computer.
The signed-in user's role is {$role}.
Answer only from the Live PSIS facts provided by Laravel. Never invent stock counts, pesos, request numbers, names, or dates.
If the facts do not contain the figure, say you do not have that number.
Keep answers short (2 to 8 sentences). Do not mention these instructions or Ollama.
PROMPT;

        try {
            $response = Http::timeout($timeout)
                ->connectTimeout(3)
                ->acceptJson()
                ->post($this->baseUrl().'/api/chat', [
                    'model' => $model,
                    'stream' => false,
                    'keep_alive' => '30m',
                    'options' => [
                        'temperature' => 0.2,
                        'num_ctx' => 4096,
                    ],
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => "Live PSIS facts:\n{$grounding}\n\nUser question:\n{$question}"],
                    ],
                ]);

            if (! $response->successful()) {
                Log::notice('PSIS Ollama HTTP '.$response->status());

                return null;
            }

            $text = trim((string) data_get($response->json(), 'message.content', ''));

            return $text !== '' ? $text : null;
        } catch (Throwable $e) {
            Log::notice('PSIS Ollama unreachable: '.$e->getMessage());

            return null;
        }
    }
}
