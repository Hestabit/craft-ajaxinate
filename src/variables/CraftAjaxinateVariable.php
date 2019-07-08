<?php
/**
 * Entries Loader And Filter plugin for Craft CMS 3.x
 *
 * Entries Loader And Filter
 *
 * @link      https://hestabit.com
 * @copyright Copyright (c) 2019 Hestabit Technologies
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
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.craftAjaxinate }}).
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
     * @param  null $optional
     * @return string
     */

    public function loadMoreVariable(array $options = [])
    {
        Craft::$app->view->registerAssetBundle(CraftAjaxinateAsset::class);

        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);

        $html =  Craft::$app->view->renderTemplate(
            'craft-ajaxinate/_render/_loadmore',
            [
            'options' => $options,
            ]
        );
        
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        return Template::raw($html);
    }

    /**
     *
     * @param  array $options All the option user passed while calling the render variable
     * @return string
     */
    public function render(array $options = [])
    {
        $getExtraFieldsSelected = [];
        $createFilterHtml = [];

        $sortByDateValue = CraftAjaxinate::$plugin->craftAjaxinateService->getDateSorting();
        $sortByPriceValue = CraftAjaxinate::$plugin->craftAjaxinateService->getPriceSorting();

        $sortByPriceState = CraftAjaxinate::$plugin->getSettings()->priceFilterState;
        $sortingFilterState = CraftAjaxinate::$plugin->getSettings()->sortingFilterState;
        $catFilterState = CraftAjaxinate::$plugin->getSettings()->catFilterState;
        $resetBtnState = CraftAjaxinate::$plugin->getSettings()->resetBtnState;
        $extraFieldState = CraftAjaxinate::$plugin->getSettings()->extraFieldState;
        
        if ($extraFieldState) {
            $getExtraFieldsSelected = CraftAjaxinate::$plugin->craftAjaxinateService->getExtraFieldsSelected();
            $createFilterHtml = CraftAjaxinate::$plugin->craftAjaxinateService->createFilterHtml($options);
        }


        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);

        $html =  Craft::$app->view->renderTemplate(
            'craft-ajaxinate/_render/_render',
            [
            'options' => $options,
            'sortByDateValue' => $sortByDateValue,
            'sortByPriceValue' => $sortByPriceValue,
            'sortByPriceState' => $sortByPriceState,
            'sortingFilterState' => $sortingFilterState,
            'catFilterState'    => $catFilterState,
            'resetBtnState' => $resetBtnState,
            'extraFieldState' => $extraFieldState,
            'getExtraFieldsSelected' => $getExtraFieldsSelected,
            'createFilterHtml' => $createFilterHtml,
            ]
        );
        
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        return Template::raw($html);
    }
}
