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

    public function actionDescription(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('metapilot:generate');
        
        $elementId = $this->request->getRequiredParam('elementId');
        $entry = Entry::find()->id($elementId)->one();
        
        if (!$entry) {
            return $this->asJson(['success' => false, 'error' => 'Entry not found']);
        }
        
        $description = Metapilot::$plugin->getMetapilotService()->generateMetaDescription($entry);
        
        if ($description) {
            return $this->asJson(['success' => true, 'description' => $description]);
        }
        
        return $this->asJson(['success' => false, 'error' => 'Failed to generate description']);
    }

    public function actionKeywords(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('metapilot:generate');
        
        $elementId = $this->request->getRequiredParam('elementId');
        $entry = Entry::find()->id($elementId)->one();
        
        if (!$entry) {
            return $this->asJson(['success' => false, 'error' => 'Entry not found']);
        }
        
        $keywords = Metapilot::$plugin->getMetapilotService()->generateMetaKeywords($entry);
        
        if ($keywords) {
            return $this->asJson(['success' => true, 'keywords' => $keywords]);
        }
        
        return $this->asJson(['success' => false, 'error' => 'Failed to generate keywords']);
    }
}