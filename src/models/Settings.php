<?php
namespace metapilot\models;

use Craft;
use craft\base\Model;

class Settings extends Model
{
    public ?string $openAiApiKey = null;
    public ?string $openAiModel = 'gpt-4o-mini';
    public bool $overwriteExisting = false;

    public function rules(): array
    {
        return [
            [['openAiApiKey', 'openAiModel'], 'string'],
            [['overwriteExisting'], 'boolean'],
            [['openAiApiKey'], 'required'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'openAiApiKey' => Craft::t('app', 'OpenAI API Key'),
            'openAiModel' => Craft::t('app', 'OpenAI Model'),
            'overwriteExisting' => Craft::t('app', 'Overwrite existing values'),
        ];
    }
}