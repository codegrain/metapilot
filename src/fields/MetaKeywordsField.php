<?php
namespace metapilot\fields;

use Craft;
use craft\base\ElementInterface;
use craft\fields\PlainText;
use craft\helpers\Html;
use metapilot\Metapilot;

class MetaKeywordsField extends PlainText
{
    public static function displayName(): string
    {
        return Craft::t('app', 'Meta Keywords (AI)');
    }

    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        $id = Html::id($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);
        
        $html = parent::getInputHtml($value, $element);
        
        if ($element && $element->id) {
            $generateUrl = Craft::$app->getUrlManager()->createUrl('metapilot/generate/keywords', [
                'elementId' => $element->id,
                'fieldHandle' => $this->handle
            ]);
            
            $html .= Html::button('Generate AI Keywords', [
                'class' => 'btn secondary',
                'style' => 'margin-top: 5px;',
                'onclick' => "generateMetaKeywords('$namespacedId', '$generateUrl')"
            ]);
            
            $html .= Html::script("
                function generateMetaKeywords(fieldId, url) {
                    const field = document.getElementById(fieldId);
                    const btn = event.target;
                    btn.disabled = true;
                    btn.textContent = 'Generating...';
                    
                    fetch(url, {method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}})
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            field.value = data.keywords;
                        } else {
                            alert('Error: ' + (data.error || 'Failed to generate keywords'));
                        }
                    })
                    .catch(error => alert('Error: ' + error))
                    .finally(() => {
                        btn.disabled = false;
                        btn.textContent = 'Generate AI Keywords';
                    });
                }
            ");
        }
        
        return $html;
    }
}