<?php
namespace metapilot;

use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\fields\PlainText;
use craft\services\Fields;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use yii\base\Event;

use metapilot\models\Settings;
use metapilot\services\MetapilotService;
use metapilot\fields\MetaDescriptionField;
use metapilot\fields\MetaKeywordsField;

class Metapilot extends Plugin
{
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;
    public static Metapilot $plugin;

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'metapilotService' => MetapilotService::class,
        ]);

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules['metapilot'] = 'metapilot/dashboard/index';
            $event->rules['metapilot/dashboard'] = 'metapilot/dashboard/index';
            $event->rules['metapilot/settings'] = 'metapilot/dashboard/settings';
            $event->rules['metapilot/generate/description'] = 'metapilot/generate/description';
            $event->rules['metapilot/generate/keywords'] = 'metapilot/generate/keywords';
        });

        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function (RegisterUserPermissionsEvent $event) {
            $event->permissions['Metapilot'] = [
                'metapilot:generate' => ['label' => Craft::t('app', 'Generate meta content')],
                'metapilot:settings' => ['label' => Craft::t('app', 'Manage Metapilot settings')],
            ];
        });

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = MetaDescriptionField::class;
            $event->types[] = MetaKeywordsField::class;
        });
    }

    public function getCpNavItem(): ?array
    {
        $item = parent::getCpNavItem();
        $item['label'] = 'Metapilot';
        $item['url'] = 'metapilot';
        $item['subnav'] = [
            'dashboard' => ['label' => 'Dashboard', 'url' => 'metapilot/dashboard'],
            'settings' => ['label' => 'Settings', 'url' => 'metapilot/settings'],
        ];
        return $item;
    }

    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }

    public function getSettingsResponse(): mixed
    {
        return Craft::$app->controller->renderTemplate('metapilot/_settings', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function requireAdminOrPermission(string $permission): void
    {
        $user = Craft::$app->getUser();
        if (!$user->getIsAdmin() && !$user->checkPermission($permission)) {
            throw new \yii\web\ForbiddenHttpException('Insufficient permissions.');
        }
    }

    public function getMetapilotService(): MetapilotService
    {
        return $this->get('metapilotService');
    }
}