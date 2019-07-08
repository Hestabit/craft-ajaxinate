<?php
/**
 * Entries Loader And Filter plugin for Craft CMS 3.x
 *
 * Entries Loader And Filter
 *
 * @link      https://hestabit.com
 * @copyright Copyright (c) 2019 Hestabit Technologies
 */

namespace hestabit\craftajaxinate\controllers;

use hestabit\craftajaxinate\assetbundles\indexcpsection\IndexCPSectionAsset;

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
class FilterController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/craft-ajaxinate/filter
     *
     * @return mixed
     */
    public function actionIndex()
    {

        $this->view->registerAssetBundle(IndexCPSectionAsset::class);

        $data['menus'] = 'Welcome to the FilterController actionIndex() method';

        return $this->renderTemplate('craft-ajaxinate/index', $data);
    }
}
