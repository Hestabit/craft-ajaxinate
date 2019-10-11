<?php
/**
 * Entries Loader And Filter plugin for Craft CMS 3.x
 *
 * Entries Loader And Filter
 *
 * @link      https://hestabit.com
 * @copyright Copyright (c) 2019 Hestabit Technologies
 * @author    Hestabit Technologies
 */

namespace hestabit\craftajaxinate\variables;

use hestabit\craftajaxinate\CraftAjaxinate;
use hestabit\craftajaxinate\assetbundles\CraftAjaxinate\CraftAjaxinateAsset;
use Craft;
use craft\web\View;
use craft\helpers\Template;

/**
 * Entries Loader And Filter Variable
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author  Hestabit Technologies
 * @package CraftAjaxinate
 * @since   1.0.0
 */
class CraftAjaxinateVariable
{
    // Public Methods
    // =========================================================================

    /**
     * {{ craft.craftAjaxinate.loadMoreVariable }}
     *
     * @param  array $options
     * @return string
     */

    public function loadMoreVariable(array $options = [])
    {
        Craft::$app->view->registerAssetBundle(CraftAjaxinateAsset::class);

        //CP setting for scrollActive value
        $scrollActive = CraftAjaxinate::$plugin->getSettings()->scrollActive;
        
        // override CP setting for scrollActive value
        if (isset($options['scrollActive'])) {
            $scrollActive = $options['scrollActive'];
        }

        //CP setting for pagesToLoad value
        $pagesToLoad = CraftAjaxinate::$plugin->getSettings()->pagesToLoad;
        
        // override CP setting for pagesToLoad value
        if (isset($options['pagesToLoad'])) {
            $pagesToLoad = $options['pagesToLoad'];
        }

        //CP setting for bottomOffset value
        $bottomOffset = CraftAjaxinate::$plugin->getSettings()->bottomOffset;
        
        // override CP setting for bottomOffset value
        if (isset($options['bottomOffset'])) {
            $bottomOffset = $options['bottomOffset'];
        }
      
        if (isset($options) && !empty($options['loaderTemplate'])) {
            // user defined loader
            $loaderHtml = $this->getLoaderTemplate($options['loaderTemplate']);
        } else {
            // default loader
            $loaderHtml = $this->getdefaultLoaderTemplate();
        }

        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);

        $html =  Craft::$app->view->renderTemplate(
            'craft-ajaxinate/_render/_loadmore',
            [
            'options' => $options,
            'pagesToLoad' => $pagesToLoad,
            'bottomOffset' => $bottomOffset,
            'loaderHtml' => $loaderHtml,
            'scrollActive' => $scrollActive
            ]
        );
        
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        return Template::raw($html);
    }

    /**
     *
     * @param  array $options All the option user passed
     * @return string
     */
    public function render(array $options = [])
    {
        $createFilterHtml = '';

        //CP setting for reset button
        $resetBtnState = CraftAjaxinate::$plugin->getSettings()->resetBtnState;
        
        // override CP setting for reset button
        if (isset($options['resetBtnState'])) {
            $resetBtnState = $options['resetBtnState'];
        }

        //CP setting for filters only if user dont used `filters` option
        $extraFieldState = CraftAjaxinate::$plugin->getSettings()->extraFieldState;
        if (!isset($options['extraFilters']) && $extraFieldState) {
            $createFilterHtml = CraftAjaxinate::$plugin->craftAjaxinateService->createFilterHtml($options);
        }

        // override CP setting for filters
        if (isset($options['extraFilters']) && !empty($options['extraFilters'])) {
            $createFilterHtml = CraftAjaxinate::$plugin->craftAjaxinateService->createFilterHtml($options);
        }
        
        $catFilterState = false;
        if (isset($options['catGroup']) && !empty(array_filter($options['catGroup']))) {
            $catFilterState = true;
        }
       
        $tagFilterHtml = '';
        if (isset($options['tagGroup']) && !empty($options['tagGroup'])) {
            $tagFilterHtml = CraftAjaxinate::$plugin->craftAjaxinateService->createTagFilterHtml($options);
        }
        

       
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);

        $html =  Craft::$app->view->renderTemplate(
            'craft-ajaxinate/_render/_render',
            [
            'options' => $options,
            'resetBtnState' => $resetBtnState,
            'catFilterState'    => $catFilterState,
            'createFilterHtml' => $createFilterHtml,
            'tagFilterHtml' => $tagFilterHtml,
            ]
        );
        
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        return Template::raw($html);
    }

    public function getLoaderTemplate($path = '')
    {
     
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);
        $html = Craft::$app->view->renderTemplate($path);
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        return Template::raw($html);
    }

    public function getdefaultLoaderTemplate()
    {
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $html = Craft::$app->view->renderTemplate('craft-ajaxinate/_defaultloader');
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);
        return Template::raw($html);
    }
}
