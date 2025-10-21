<?php
namespace metapilot\controllers;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use metapilot\Metapilot;
use yii\web\Response;

class GenerateController extends Controller
{
    protected array|bool|int $allowAnonymous = false;
    public $enableCsrfValidation = false;

    public function actionDescription(): Response
    {
        $elementId = $this->request->getParam('elementId');
        $entry = Entry::find()->id($elementId)->one();
        
        if (!$entry) {
            return $this->asJson(['success' => false, 'error' => 'Entry not found']);
        }
        
        $settings = Metapilot::$plugin->getSettings();
        if (!$settings->openAiApiKey) {
            return $this->asJson(['success' => false, 'error' => 'OpenAI API key not configured']);
        }
        
        try {
            $description = Metapilot::$plugin->getMetapilotService()->generateMetaDescription($entry);
            
            if ($description) {
                return $this->asJson(['success' => true, 'description' => $description]);
            }
            
            return $this->asJson(['success' => false, 'error' => 'No content generated - check logs']);
        } catch (\Exception $e) {
            return $this->asJson(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function actionKeywords(): Response
    {
        $elementId = $this->request->getParam('elementId');
        $entry = Entry::find()->id($elementId)->one();
        
        if (!$entry) {
            return $this->asJson(['success' => false, 'error' => 'Entry not found']);
        }
        
        $settings = Metapilot::$plugin->getSettings();
        if (!$settings->openAiApiKey) {
            return $this->asJson(['success' => false, 'error' => 'OpenAI API key not configured']);
        }
        
        try {
            $keywords = Metapilot::$plugin->getMetapilotService()->generateMetaKeywords($entry);
            
            if ($keywords) {
                return $this->asJson(['success' => true, 'keywords' => $keywords]);
            }
            
            return $this->asJson(['success' => false, 'error' => 'No content generated - check logs']);
        } catch (\Exception $e) {
            return $this->asJson(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}