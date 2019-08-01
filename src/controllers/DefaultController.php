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
    protected $allowAnonymous = ['load-more', 'plugin-init'];

    // Public Methods
    // =========================================================================

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
        $currentpage = $request->getBodyParam('currentpage');
        $settings =  $request->getBodyParam('settings');
        $settings =  $settings ?  $settings : [];
        $defaultMsg =  CraftAjaxinate::$plugin->getSettings()->noMoreData;
        $noMoreDataMsg =  isset($settings['noMoreDataMsg']) ? trim($settings['noMoreDataMsg']) : $defaultMsg;

        $initLoad = isset($settings['initLoad']) ? $settings['initLoad'] : [];

        $totalEntries = CraftAjaxinate::$plugin->craftAjaxinateService->getCount();
        $limit = CraftAjaxinate::$plugin->getSettings()->limitEntries;
        $offset = CraftAjaxinate::$plugin->getSettings()->offsetEntries;
        // check entries onLoad status CP
        $showInitEntries = CraftAjaxinate::$plugin->getSettings()->showInitEntries; 

        $entriesData =  null;

        // (initLoad passed as true ) || (Enabled in CP and not false on template)
        if ( ($settings && $initLoad === 'true') || ( $initLoad !== 'false' && $showInitEntries) ) {
           
            $entriesData = CraftAjaxinate::$plugin->craftAjaxinateService
                           ->prepareData($settings, $currentpage);

        }

        $message =  $entriesData ?  'Data loaded' : $noMoreDataMsg;
        if ($request->getAcceptsJson()) {
            return $this->asJson(
                [
                 'success' => true,
                 'limit' => $limit,
                 'offset' => $offset,
                 'entries' => $entriesData,
                 'totalEntries' => $totalEntries,
                 'message' => $message,
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
       
        $settings = $request->getBodyParam('settings');
        $settings = $settings ? $settings : [];
        $defaultMsg =  CraftAjaxinate::$plugin->getSettings()->noMoreData;
        $noMoreDataMsg =  isset($settings['noMoreDataMsg']) ? trim($settings['noMoreDataMsg']) : $defaultMsg;
        
        $catfilter = $request->getBodyParam('catfilter');
        $catfilter =  $catfilter ?  $catfilter : [];

        $extrafilter = $request->getBodyParam('extrafilter'); 
        $extrafilter = $extrafilter ? $extrafilter : []; // array

        $totalEntries = CraftAjaxinate::$plugin->craftAjaxinateService->getCount();
        
        $entriesData = CraftAjaxinate::$plugin->craftAjaxinateService
                        ->prepareData($settings, $currentpage, $extrafilter, $sorting, $catfilter);

        $message =  $entriesData ?  'Data loaded' : $noMoreDataMsg;

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
