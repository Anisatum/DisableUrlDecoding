<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DisableUrlDecoding;

use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

/**
 * Defines Settings for DisableUrlDecoding.
 *
 * Usage like this:
 * $settings = new SystemSettings();
 * $settings->metric->getValue();
 * $settings->description->getValue();
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    const TYPE_ALL = 'all';
    const TYPE_REGEXP = 'regexp';
    const TYPE_PARAMS = 'params';

    /** @var Setting */
    public $disableUrlDecoding;

    /** @var Setting */
    public $disableFor;

    /** @var Setting */
    public $disableRegexp;

    /** @var Setting */
    public $disableParams;

    protected function init()
    {
        $this->disableUrlDecoding = $this->makeDisableUrlDecoding();
        $this->disableFor = $this->makeDisableFor();
        $this->disableRegexp = $this->makeDisableRegex();
        $this->disableParams = $this->makeDisableParams();
    }
    private function makeDisableUrlDecoding()
    {
        $defaultValue = false;
        $type = FieldConfig::TYPE_BOOL;

        return $this->makeSetting('disable_decoding', $defaultValue, $type, function (FieldConfig $field) {
            $field->title = Piwik::translate('DisableUrlDecoding_DisableTitle');
            $field->inlineHelp = Piwik::translate('DisableUrlDecoding_DisableInlineHelp');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
        });
    }

    private function makeDisableFor()
    {
        $defaultValue = self::TYPE_ALL;
        $type = FieldConfig::TYPE_STRING;

        return $this->makeSetting('disable_for', $defaultValue, $type, function (FieldConfig $field) {
            $field->title = Piwik::translate('DisableUrlDecoding_ForTitle');
            $field->condition = 'disable_decoding';
            $field->inlineHelp = Piwik::translate('DisableUrlDecoding_ForInlineHelp') . '<br /><br /><ul>'
                . '<li><strong>' . Piwik::translate('DisableUrlDecoding_ForValueAll')
                . '</strong>' . Piwik::translate('DisableUrlDecoding_ForInlineHelpAll') . '</li><br />'
                . '<li><strong>' . Piwik::translate('DisableUrlDecoding_ForValueRegexp')
                . '</strong>' . Piwik::translate('DisableUrlDecoding_ForInlineHelpRegexp') . '</li><br />'
                . '<li><strong>' . Piwik::translate('DisableUrlDecoding_ForValueParams')
                . '</strong>' . Piwik::translate('DisableUrlDecoding_ForInlineHelpParams') . '</li><br />'
                . '</ul>';
            $field->uiControl = FieldConfig::UI_CONTROL_SINGLE_SELECT;
            $field->availableValues = [
                self::TYPE_ALL => Piwik::translate('DisableUrlDecoding_ForValueAll'),
                self::TYPE_REGEXP => Piwik::translate('DisableUrlDecoding_ForValueRegexp'),
                self::TYPE_PARAMS => Piwik::translate('DisableUrlDecoding_ForValueParams'),
            ];
        });
    }

    private function makeDisableRegex()
    {
        $defaultValue = '';
        $type = FieldConfig::TYPE_STRING;

        return $this->makeSetting('disable_regex', $defaultValue, $type, function (FieldConfig $field) {
            $field->title = Piwik::translate('DisableUrlDecoding_RegexpTitle');
            $field->condition = 'disable_decoding && disable_for == "regexp"';
            $field->inlineHelp = Piwik::translate('DisableUrlDecoding_RegexpInlineHelp');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
        });
    }

    private function makeDisableParams()
    {
        $defaultValue = '';
        $type = FieldConfig::TYPE_ARRAY;

        return $this->makeSetting('disable_params', $defaultValue, $type, function (FieldConfig $field) {
            $field->title = Piwik::translate('DisableUrlDecoding_ParamsTitle');
            $field->condition = 'disable_decoding && disable_for == "params"';
            $field->inlineHelp = Piwik::translate('DisableUrlDecoding_ParamsInlineHelp');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
        });
    }
}
