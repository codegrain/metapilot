<?php
namespace metapilot\services;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use GuzzleHttp\Client;
use metapilot\Metapilot;

class MetapilotService extends Component
{
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
            return null;
        }

        $content = $this->extractContent($entry);
        if (!$content) {
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
            Craft::error('Metapilot API error: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    public function generateMetaKeywords(Entry $entry): ?string
    {
        $settings = Metapilot::$plugin->getSettings();
        if (!$settings->openAiApiKey) {
            return null;
        }

        $content = $this->extractContent($entry);
        if (!$content) {
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
            Craft::error('Metapilot API error: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    private function extractContent(Entry $entry): string
    {
        $content = [];
        
        if ($entry->title) {
            $content[] = $entry->title;
        }

        foreach ($entry->getFieldLayout()->getCustomFields() as $field) {
            $value = $entry->getFieldValue($field->handle);
            
            if (is_string($value) && trim($value)) {
                $content[] = strip_tags($value);
            }
        }

        $text = implode(' ', $content);
        return substr($text, 0, 3000);
    }
}