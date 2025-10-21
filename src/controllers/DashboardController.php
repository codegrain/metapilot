<?php
namespace metapilot\controllers;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use metapilot\Metapilot;
use yii\web\Response;

class DashboardController extends Controller
{
    public function actionIndex(): Response
    {
        $this->requirePermission('metapilot:generate');
        
        return $this->renderTemplate('metapilot/dashboard', [
            'title' => 'Metapilot Dashboard',
        ]);
    }


}