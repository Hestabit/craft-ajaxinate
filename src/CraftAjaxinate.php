<?php
/**
 * Entries Loader And Filter plugin for Craft CMS 3.x
 *
 * Entries Loader And Filter
 *
 * @author    Hestabit Technologies <technology@hestabit.com>
 * @copyright Copyright (c) 2019 Hestabit Technologies
 * @link     https://www.hestabit.com/
 */

namespace hestabit\craftajaxinate;

use hestabit\craftajaxinate\services\CraftAjaxinateService as CraftAjaxinateServiceService;
use hestabit\craftajaxinate\variables\CraftAjaxinateVariable;
use hestabit\craftajaxinate\twigextensions\CraftAjaxinateTwigExtension;
use hestabit\craftajaxinate\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;
use yii\base\Event;
use craft\db\Query;

/**
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author  Hestabit Technologies <technology@hestabit.com>
 * @package CraftAjaxinate
 * @since   1.0.0
 *
 * @property CraftAjaxinateServiceService $craftAjaxinateService
 */
class CraftAjaxinate extends Plugin
{
    // Public Properties
    // =========================================================================

    /**
     *
     * @var CraftAjaxinate
     */
    public static $plugin;

   
    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * CraftAjaxinate::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Add in our Twig extensions
        Craft::$app->view->registerTwigExtension(new CraftAjaxinateTwigExtension());

        // Register our site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'craft-ajaxinate/default';
            }
        );

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                // $event->rules['cpFilters'] = 'craft-ajaxinate/default/do-something';
                $event->rules['craft-ajaxinate'] = 'craft-ajaxinate/filter';
            }
        );

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
            /**
            * @var CraftVariable $variable
            */
                $variable = $event->sender;
                $variable->set('craftAjaxinate', CraftAjaxinateVariable::class);
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );

        Craft::info(
            Craft::t(
                'craft-ajaxinate',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        $fieldsData = [];

        // $fields = Craft::$app->fields->getAllFields();
         $fields = (new Query())
             ->select(['id','handle','name','instructions','type'])
             ->from(['{{%fields}}'])
             ->all();

   

        foreach ($fields as $field) {
            $fieldsData[$field['handle']] = $field['name'];
        }
       
        /**
         * @var FlysystemVolume $volume
         */
       
        $volumes = [];

        // get all sections
        $sections = [];

        foreach (\Craft::$app->sections->getAllSections() as $section) {
            $s = ["label" => $section->name, "sectionId" => $section->id, 'value' => $section->handle ];
            $sections[] = $s;
        }

        return Craft::$app->view->renderTemplate(
            'craft-ajaxinate/settings',
            [
                'settings' => $this->getSettings(),
                'sections' => $sections,
                'fields'   => $fieldsData,
            ]
        );
    }
}
