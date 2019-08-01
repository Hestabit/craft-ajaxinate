<?php
/**
 * Entries Loader And Filter plugin for Craft CMS 3.x
 *
 * Entries Loader And Filter
 *
 * @link      https://hestabit.com
 * @copyright Copyright (c) 2019 Hestabit Technologies
 */

namespace hestabit\craftajaxinate\services;

use hestabit\craftajaxinate\CraftAjaxinate;

use Craft;
use craft\base\Component;
use craft\elements\Entry;

use craft\web\View;
use craft\helpers\Template;
use craft\db\Query;
use craft\elements\db\ElementQuery;

/**
 * CraftAjaxinateService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author  Hestabit Technologies <technology@hestabit.com>
 * @package CraftAjaxinate
 * @since   1.0.0
 */
class CraftAjaxinateService extends Component
{
    // Public Methods
    // =========================================================================

  
    /**
     * Get Default seection name from plugin setting.
     *
     * From any other plugin file, call it like this:
     *
     *  CraftAjaxinate::$plugin->craftAjaxinateService->getDefaultSections()
     *
     * @return string
     */
    public function getDefaultSections(): array
    {
        $sectionName = [];

        if (CraftAjaxinate::$plugin->getSettings()->sectionsSelected) {
            $sectionName = CraftAjaxinate::$plugin->getSettings()->sectionsSelected;
        }
        return $sectionName;
    }

    /**
     * Get Default limit from plugin setting.
     *
     * From any other plugin file, call it like this:
     *
     *  CraftAjaxinate::$plugin->craftAjaxinateService->getDefaultLimit()
     *
     * @return int
     */
    public function getDefaultLimit(): int
    {
        $limit = 1;
        if (CraftAjaxinate::$plugin->getSettings()->limitEntries) {
            $limit = CraftAjaxinate::$plugin->getSettings()->limitEntries;
        }
        return $limit;
    }
     
     /**
      * Get Default offset from plugin setting.
      *
      * From any other plugin file, call it like this:
      *
      *  CraftAjaxinate::$plugin->craftAjaxinateService->getDefaultOffset()
      *
      * @return int
      */
    public function getDefaultOffset(): int
    {
        $offset = 0;
        if (CraftAjaxinate::$plugin->getSettings()->offsetEntries) {
            $offset = CraftAjaxinate::$plugin->getSettings()->offsetEntries;
        }
        return $offset;
    }

    /**
     * Get total active entries.
     * CraftAjaxinate::$plugin->craftAjaxinateService->getCount()
     *
     * @param  string $sectionName
     * @return int|null
     */
    public function getCount($sectionName = null)
    {
        $count = null;
        if ($sectionName == null) {
            $sectionName = $this->getDefaultSections();
        }

        $count = Entry::find()
            ->section($sectionName)
            ->count();

        if ($count) {
            return $count;
        }
        return null;
    }

    /**
     * Get selected extra fields name from plugin setting.
     *
     * From any other plugin file, call it like this:
     *
     *  CraftAjaxinate::$plugin->craftAjaxinateService->getExtraFieldsSelected()
     *
     * @return string
     */
    public function getExtraFieldsSelected(): array
    {
        $extraFieldSelected = [];

        if (CraftAjaxinate::$plugin->getSettings()->extraFieldSelected) {
            $extraFieldSelected = CraftAjaxinate::$plugin->getSettings()->extraFieldSelected;
        }
       
        
        return $extraFieldSelected;
    }

    /**
     * Prepare query and get data based on settings applied in backend or while calling
     * the variable in template.
     *
     * From any other plugin file, call it like this:
     *
     *  CraftAjaxinate::$plugin->craftAjaxinateService->prepareData()
     *
     * @return int
     */
    public function prepareData(array $settings = [], int $currentpage = null, array $extrafilter = [], int $sorting = null, array $catfilter = [])
    {
       
        $path =  CraftAjaxinate::$plugin->getSettings()->outputTemplate;

        // get current active site handle from CP
        $currentSite = Craft::$app->getSites()->getCurrentSite()->handle;
        $path = $path[$currentSite]['template'];

        // Path override
        if (isset($settings) && !empty($settings['template'])) {
            $path = $settings['template'];
        }
       
        // if $path is emtpy return false
        if (empty($path)) {
            return false;
        }

        $limit = $this->getDefaultLimit();
        if (isset($settings) && !empty($settings['limit'])) {
            $limit = $settings['limit'];
        }


        $defaultOffset = $this->getDefaultOffset();
        if (isset($settings) && !empty($settings['offset'])) {
            $defaultOffset = $settings['offset'];
        }

        $sectionName = $this->getDefaultSections();
        if (isset($settings) && !empty($settings['section'])) {
            $sectionName = $settings['section'];
        }
        
        $entryQuery = Entry::find();
        $sortByPrice = null;
        if (isset($settings['sortingFilters']['price'])) {
            $sortByPrice = $settings['sortingFilters']['price'];
        }

        // filter based on section name
        $entryQuery->section($sectionName);

    
        // get future events only if enabled in CP and 
        // query param is not passed in render call.
        $showFutureEntries = CraftAjaxinate::$plugin->getSettings()->showFutureEntries;
        if ( $showFutureEntries && (!isset($settings['query'])) ) {

            $dateFieldSelected = CraftAjaxinate::$plugin->getSettings()->dateFieldSelected;
            $clientTimeZone = new \DateTimeZone('UTC');
            $date = new \DateTime('', $clientTimeZone);
            $date =  $date->format('Y-m-d H:i:s');
            $entryQuery->$dateFieldSelected(">=$date");
        }

        // filter based on sorting options [sort by date, price if enabled]
        switch ($sorting) {
            case 1:
                $entryQuery->orderBy('postDate desc');
                break;

            case 2:
                $entryQuery->orderBy('postDate asc');
                break;

            case 3:
                $entryQuery->orderBy("$sortByPrice desc");
                break;

            case 4:
                $entryQuery->orderBy("$sortByPrice asc");
                break;
            default:
                $entryQuery->orderBy('postDate desc');
                break;
        }

         // filter based on category
        if ($catfilter && !empty($catfilter[0])) {
             $catSlug = $this->getCatSlug($catfilter);
              $entryQuery->relatedTo = array(
              'targetElement' => array_merge(['and'], $catSlug),
              );
        }

        // Query based on imput type user clicked on frontend
        if ($extrafilter && !empty($extrafilter[0])) {
            $relatedTo = [];
            $fieldList = [];
            $relationFilter = false;
            foreach ($extrafilter as $key => $value) {
                $handleName = $value['handle'];
                $inputValue = $value['value'];

                switch ($value['ftype']) {
                    case 'craft\\fields\\Tags':
                        $relatedTo[] = $value['value'];
                        $fieldList[] = $value['handle'];
                        $relationFilter = true;
                        break;
              
                    case 'craft\\fields\\Checkboxes':
                    case 'craft\\fields\\RadioButtons':
                        $valReturned = $this->_applyFieldFilter($entryQuery, $handleName, $inputValue);
                        break;

                    case 'craft\\fields\\Lightswitch':
                        $entryQuery->$handleName = 1;
                        break;
              
                    case 'craft\\fields\\Number':
                        $entryQuery->$handleName("<= {$inputValue}");
                        break;
                }
            }


            if ($relationFilter) {
                $entryQuery->relatedTo = array(
                'targetElement' => array_merge(['and'], $relatedTo),
                'field' => $fieldList,
                );
            }
        }

        if (isset($settings['query']) && !empty(array_filter($settings['query']))) {
            $query = $settings['query'];
            foreach ($query as $key => $value) {
                $entryQuery->$key($value);
            }
        }

        $entryQuery->limit($limit);
       
        // offset behave differently if showInitEntries activated (-_-)
        $showInitEntries = CraftAjaxinate::$plugin->getSettings()->showInitEntries;
        if (isset($settings['initLoad']) && !empty($settings['initLoad'])) {
            $showInitEntries = $settings['initLoad'];
        }

        // It's a init call or a reset call and currentpage will be 1.
        if ($showInitEntries && $currentpage === 1) {
            // No skip
            $offset = 0;
            // set limit to offset as we need to load the same number of entries.
            $entryQuery->limit($defaultOffset);
        } else {
            // In CP and in render varible call user have option to skip entries,
            $offset = $defaultOffset + (($currentpage-2)*$limit);
        }
       
        // filter based on offset
        $entryQuery->offset($offset);
        
        $entries = $entryQuery->all();
        // print_r($entryQuery->getRawSql());
        if ($entries) {
            return $this->renderData($offset, $path, $entries);
        }

        return null;
    }

    private function _applyRelationFilter(ElementQuery $query, $relatedTo, $fieldList) : array
    {
        $query->relatedTo = array(
                'targetElement' => array_merge(['and'], [$relatedTo]),
                'field' => $fieldList,
        );
    }


    /**
     * Add andWhere condition to main query.
     *
     * @param  ElementQuery $query
     * @param  string       $handle
     * @param  array        $value
     * @return query
     */
    private function _applyFieldFilter(ElementQuery $query, string $handle, $value)
    {
        $query->andWhere(
            [
            'like', ('content.field_' . $handle), $value
            ]
        );
    }

    /**
     * Get cat slug by cat name.
     *
     * @param  string $catName
     * @return object
     */
    public function getCatSlug($catName = null)
    {
        $categories = \craft\elements\Category::find();
        $categoriesSlug = $categories->slug($catName)->all();

        return $categoriesSlug;
    }

    /**
     * Call template selected in CP and pass entries object to it.
     *
     * @param  int    $offset
     * @param  string $path
     * @param  array  $entries
     * @return html
     */
    public function renderData($offset, $path = null, $entries = [])
    {
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        $html = Craft::$app->view->renderTemplate(
            $path,
            [
                    'pageInfo' => 1,
                    'count' => CraftAjaxinate::$plugin->craftAjaxinateService->getCount(),
                    'entries' => $entries,
                    'offset' =>$offset,
            ]
        );

        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);

        return Template::raw($html);
    }

    /**
     * Get all the selected sections in CP
     * @param  string $value
     * @return array
     */
    public function getSectionsSelectedList($value = '')
    {
        $sectionsSelected = CraftAjaxinate::$plugin->getSettings()->sectionsSelected;
        return $sectionsSelected;
    }

    /**
     * Get all the selected fields in CP
     * @param  string $value
     * @return array
     */
    public function getFieldSelectedList($value = '')
    {
        $fieldSelected = CraftAjaxinate::$plugin->getSettings()->fieldSelected;
        return $fieldSelected;
    }
        
     /**
     * Calculate range of any number field
     * @param  string $sectionName
     * @param  string $handle
     * @return array
     */
    public function getNumberRange($sectionName = null, $handle = null)
    {
        $numbeRange = [];

        $entryQuery = Entry::find();
        $entryQuery->$handle('>= 0');

        $min = $entryQuery->min("field_{$handle}");
       

        if (!is_numeric($min)) {
            return false;
        }
       
        $max = $entryQuery->max("field_{$handle}");
        
        $numbeRange['handle'] = (string)$handle;
        $numbeRange['min'] = (int)$min;
        $numbeRange['max'] = (int)$max;
       
        
        return $numbeRange;
    }

    /**
     * Calculate range of any number field
     * @param  string $sectionName
     * @param  string $handle     
     * @return array             
     */
    public function getExtraFiltersRange($sectionName = null, $handle = null)
    {
        $numbeRange = [];
        $extraFieldState = CraftAjaxinate::$plugin->getSettings()->extraFieldState;
        if ($handle != null) {
             $numbeRange[] = $this->getNumberRange('', $handle);
        }

        // remove empty rows, due to non numeric $handle
        return array_filter($numbeRange);
    }

    /**
     * Call templates based on type of input passed in $options
     *
     * @param  array $options Array of all the options user passed
     */
    public function createFilterHtml(array $options = [])
    {
        $isTagAdded = false;
        $handleIdList = [];
        $fields = [];

        if (isset($options['extraFilters'])) {
            $fields = $options['extraFilters'];
        } else {
            $fields = CraftAjaxinate::$plugin->craftAjaxinateService->getExtraFieldsSelected();
        }
       

        $html = '';
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);

        foreach ($fields as $field) {
            $field = $this->getFieldDetails($field);
            switch ($field['type']) {
                case 'craft\fields\Number':
                    $html .=  Craft::$app->view->renderTemplate(
                        'craft-ajaxinate/_render/_number',
                        [
                        'field' => $field,
                        'options' => $options,
                        'numberRange' => $this->getExtraFiltersRange('', $field['handle']),
                        ]
                    );
                    break;
                case 'craft\fields\Lightswitch':
                case 'craft\fields\Checkboxes':
                    $html .=  Craft::$app->view->renderTemplate(
                        'craft-ajaxinate/_render/_checkbox',
                        [
                        'field' => $field,
                        'options' => $options,
                        ]
                    );
                    break;
                case 'craft\fields\RadioButtons':
                    $html .=  Craft::$app->view->renderTemplate(
                        'craft-ajaxinate/_render/_radio',
                        [
                        'field' => $field,
                        'options' => $options,
                        ]
                    );
                    break;
                default:
                    // code...
                    break;
            }
        }

        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);
        return Template::raw($html);
    }

    public function createTagFilterHtml(array $options = [])
    {   
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $html = '';
        if (isset($options['tagGroup'])) {
            $tagGroup = $options['tagGroup'];
            $html .=  Craft::$app->view->renderTemplate(
                'craft-ajaxinate/_render/_tags',
                [
                    'options' => $options,
                ]
            );
        }

        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);
        return Template::raw($html);
    }
    
    /**
     * Field details by handle name
     * @param  string $fieldName handle name
     * @return array
     */
    public function getFieldDetails($fieldName = '')
    {
        if ($fieldName === '') {
            return null;
        }

        $field = (new Query())
             ->select(['type','name', 'handle'])
             ->from(['{{%fields}}'])
             ->where(['handle' => $fieldName])
             ->one();

        return $field;
    }
}
