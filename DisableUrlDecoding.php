<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DisableUrlDecoding;

use Piwik\Piwik;

class DisableUrlDecoding extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return [
            'CustomJsTracker.manipulateJsTracker' => 'manipulateJsTracker',
            'SystemSettings.updated' => 'updated'
        ];
    }

    public function isTrackerPlugin()
    {
        return true;
    }

    public function updated(SystemSettings $settings)
    {
        if ($settings->getPluginName() === 'DisableUrlDecoding') {
            Piwik::postEvent('CustomJsTracker.updateTracker');
        }
    }

    public function manipulateJsTracker(&$content)
    {
        $systemSettings = new SystemSettings();
        $jsSettings = [];

        if ($systemSettings->disableFor->getValue())
            $jsSettings['disableFor'] = $systemSettings->disableFor->getValue();
        if ($jsSettings['disableFor'] == SystemSettings::TYPE_REGEXP && $systemSettings->disableRegexp->getValue())
            $jsSettings['regexp'] = $systemSettings->disableRegexp->getValue();
        if ($jsSettings['disableFor'] == SystemSettings::TYPE_PARAMS && $systemSettings->disableParams->getValue())
            $jsSettings['params'] = $systemSettings->disableParams->getValue();


        $jsSettings = json_encode($jsSettings);
        $content = preg_replace('/Matomo.DisableUrlDecoding\s*\=\s*\{\}\;/i',
            "Matomo.DisableUrlDecoding=$jsSettings;",
            $content
        );
    }
}
