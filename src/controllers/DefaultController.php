<?php
/**
 * Entries Loader And Filter plugin for Craft CMS 3.x
 *
 * Entries Loader And Filter
 *
 * @author    Hestabit Technologies <technology@hestabit.com>
 * @copyright Copyright (c) 2019 Hestabit Technologies
 * @license   [<url>] [name]
 * @since     1.0.0
 * @link      https://hestabit.com
 * @category  [description]
 */

namespace hestabit\craftajaxinate\controllers;

use hestabit\craftajaxinate\CraftAjaxinate;

use Craft;
use craft\web\Controller;

/**
 * Default Controller
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author  Hestabit Technologies <technology@hestabit.com>
 * @package CraftAjaxinate
 * @since   1.0.0
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index', 'load-more', 'plugin-init'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/craft-ajaxinate/default
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $result = 'Welcome to the DefaultController actionIndex() method';

        return $result;
    }

    /**
     * e.g.: actions/craft-ajaxinate/default/plugin-init
     *
     * @return json
     */
    public function actionPluginInit()
    {
        $this->requireSiteRequest();
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $totalEntries = CraftAjaxinate::$plugin->craftAjaxinateService->getCount();

        $limit = CraftAjaxinate::$plugin->getSettings()->limitEntries;
        $offset = CraftAjaxinate::$plugin->getSettings()->offsetEntries;
        
        if ($request->getAcceptsJson()) {
            return $this->asJson(
                [
                 'success' => true,
                 'limit' => $limit,
                 'offset' => $offset,
                 'entries' => 'init',
                 'totalEntries' => $totalEntries,
                 'message' => 'Button updated',
                 ]
            );
        }
    }

    /**
     * Load more data
     * e.g.: actions/craft-ajaxinate/default/load-more
     *
     * @return json
     */
    
    public function actionLoadMore()
    {
        $this->requireSiteRequest();
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $currentpage = $request->getBodyParam('currentpage');
        $sorting = $request->getBodyParam('sorting');
        $catfilter = $request->getBodyParam('catfilter') ?  $request->getBodyParam('catfilter') : []; // array
        $extrafilter = $request->getBodyParam('extrafilter') ?  $request->getBodyParam('extrafilter') : []; // array
        


        $totalEntries = CraftAjaxinate::$plugin->craftAjaxinateService->getCount();
        // Default number of entries
        $defaultOffset = CraftAjaxinate::$plugin->getSettings()->offsetEntries;
        
        $entriesData = CraftAjaxinate::$plugin->craftAjaxinateService
                        ->prepareData($currentpage, $sorting, $catfilter, $extrafilter);

        $message =  $entriesData ?  'Data loaded' : CraftAjaxinate::$plugin->getSettings()->noMoreData;

        if ($request->getAcceptsJson()) {
            return $this->asJson(
                [
                'success' => true,
                'totalEntries'=> $totalEntries,
                'entries' => $entriesData,
                'message' => $message,
                ]
            );
        }
    }
}
