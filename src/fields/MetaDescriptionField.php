<?php
namespace metapilot\fields;

use Craft;
use craft\base\ElementInterface;
use craft\fields\PlainText;
use craft\helpers\Html;
use craft\helpers\Json;
use metapilot\Metapilot;

class MetaDescriptionField extends PlainText
{
    public static function displayName(): string
    {
        return Craft::t('app', 'Meta Description (AI)');
    }

    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        $id = Html::id($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);
        
        $html = parent::getInputHtml($value, $element);
        
        if ($element && $element->id) {
            $generateUrl = Craft::$app->getUrlManager()->createUrl('metapilot/generate/description', [
                'elementId' => $element->id,
                'fieldHandle' => $this->handle
            ]);
            
            $html .= Html::button('Generate AI Description', [
                'class' => 'btn secondary',
                'style' => 'margin-top: 5px;',
                'onclick' => "generateMetaDescription('$namespacedId', '$generateUrl')"
            ]);
            
            $html .= Html::script("
                function generateMetaDescription(fieldId, url) {
                    const field = document.getElementById(fieldId);
                    const btn = event.target;
                    btn.disabled = true;
                    btn.textContent = 'Generating...';
                    
                    fetch(url, {method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}})
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            field.value = data.description;
                        } else {
                            alert('Error: ' + (data.error || 'Failed to generate description'));
                        }
                    })
                    .catch(error => alert('Error: ' + error))
                    .finally(() => {
                        btn.disabled = false;
                        btn.textContent = 'Generate AI Description';
                    });
                }
            ");
        }
        
        return $html;
    }
}