<?php
namespace metapilot\services;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use GuzzleHttp\Client;
use metapilot\Metapilot;
use metapilot\traits\LoggingTrait;

class MetapilotService extends Component
{
    use LoggingTrait;

    private Client $client;

    public function init(): void
    {
        parent::init();
        $this->client = new Client(['timeout' => 30]);
    }

    public function generateMetaDescription(Entry $entry): ?string
    {
        $settings = Metapilot::$plugin->getSettings();
        if (!$settings->openAiApiKey) {
            $this->logError('No OpenAI API key configured');
            return null;
        }

        $content = $this->extractContent($entry);
        if (!$content || strlen(trim($content)) < 10) {
            $this->logError('No content found in entry: ' . $entry->title . ' (content: "' . $content . '")');
            return null;
        }

        try {
            $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $settings->openAiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $settings->openAiModel,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Generate a compelling meta description (max 160 characters) for SEO based on the content provided.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $content
                        ]
                    ],
                    'max_tokens' => 100,
                    'temperature' => 0.7,
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return trim($data['choices'][0]['message']['content'] ?? '');
        } catch (\Exception $e) {
            $this->logError('Description API error: ' . $e->getMessage());
            return null;
        }
    }

    public function generateMetaKeywords(Entry $entry): ?string
    {
        $settings = Metapilot::$plugin->getSettings();
        if (!$settings->openAiApiKey) {
            $this->logError('No OpenAI API key configured');
            return null;
        }

        $content = $this->extractContent($entry);
        if (!$content || strlen(trim($content)) < 10) {
            $this->logError('No content found in entry: ' . $entry->title . ' (content: "' . $content . '")');
            return null;
        }

        try {
            $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $settings->openAiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $settings->openAiModel,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Generate 5-10 relevant SEO keywords separated by commas based on the content provided. Focus on the main topics and themes.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $content
                        ]
                    ],
                    'max_tokens' => 100,
                    'temperature' => 0.7,
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return trim($data['choices'][0]['message']['content'] ?? '');
        } catch (\Exception $e) {
            $this->logError('Keywords API error: ' . $e->getMessage());
            return null;
        }
    }

    private function extractContent(Entry $entry): string
    {
        $content = [];
        
        if ($entry->title) {
            $content[] = $entry->title;
            $this->logInfo("Added title: " . $entry->title);
        }

        $fieldLayout = $entry->getFieldLayout();
        if ($fieldLayout) {
            foreach ($fieldLayout->getCustomFields() as $field) {
                $value = $entry->getFieldValue($field->handle);
                $this->logInfo("Field {$field->handle}: " . gettype($value) . " - " . (is_string($value) ? substr($value, 0, 100) : 'not string'));
                
                if (is_string($value) && trim($value)) {
                    $cleanValue = strip_tags($value);
                    if (strlen(trim($cleanValue)) > 0) {
                        $content[] = $cleanValue;
                    }
                } elseif (is_object($value) && method_exists($value, '__toString')) {
                    $stringValue = (string)$value;
                    $cleanValue = strip_tags($stringValue);
                    if (strlen(trim($cleanValue)) > 0) {
                        $content[] = $cleanValue;
                    }
                }
            }
        }

        $text = implode(' ', $content);
        $this->logInfo("Final content length: " . strlen($text) . " chars");
        return substr($text, 0, 3000);
    }
}