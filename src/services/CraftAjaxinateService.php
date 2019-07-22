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
      * Get sorting from plugin setting.
      *
      * From any other plugin file, call it like this:
      *
      *  CraftAjaxinate::$plugin->craftAjaxinateService->getDateSorting()
      *
      * @return int
      */
    public function getDateSorting(): array
    {
        $sortBydate = [];
        if (CraftAjaxinate::$plugin->getSettings()->sortBydate) {
            $sortBydate = CraftAjaxinate::$plugin->getSettings()->sortBydate;
        }
        return $sortBydate;
    }

    /**
     * Get sorting from plugin setting.
     *
     * From any other plugin file, call it like this:
     *
     *  CraftAjaxinate::$plugin->craftAjaxinateService->getPriceSorting()
     *
     * @return int
     */
    public function getPriceSorting(): string
    {
        $sortByPrice = [];
        if (CraftAjaxinate::$plugin->getSettings()->sortByPrice) {
            $sortByPrice = CraftAjaxinate::$plugin->getSettings()->sortByPrice;
        }
        return $sortByPrice;
    }



    /**
     * Get total active entries.
     * CraftAjaxinate::$plugin->craftAjaxinateService->getCount()
     *
     * @param  string $sectionName
     * @return int
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
    public function prepareData(int $currentpage = null, int $sorting = null, array $catfilter = [], array $extrafilter = [])
    {

        $imageUrl = [];
        $path =  CraftAjaxinate::$plugin->getSettings()->outputTemplate;
        
        // get current active site handle
        $currentSite = Craft::$app->getSites()->getCurrentSite()->handle;
        $path = $path[$currentSite]['template'];

        // if $path is emtpy return false
        if (empty($path)) {
                  return false;
        }

        $limit = $this->getDefaultLimit();
        $offset = $this->getDefaultOffset();

        $sectionName = $this->getDefaultSections();

        $entryQuery = Entry::find();
         
        $sortByPrice = CraftAjaxinate::$plugin->getSettings()->sortByPrice;
       
        $sortByPrice = substr("$sortByPrice", 0, strrpos($sortByPrice, '_'));

        // filter based on sectionname
        $entryQuery->section($sectionName);

        // get future events only if enabled in CP
        $showFutureEntries = CraftAjaxinate::$plugin->getSettings()->showFutureEntries;
        if ($showFutureEntries) {
            $dateFieldSelected = CraftAjaxinate::$plugin->getSettings()->dateFieldSelected;
            $dateFieldSelected = substr("$dateFieldSelected", 0, strrpos($dateFieldSelected, '_'));
            $entryQuery->$dateFieldSelected(">=".date('Y-m-d'));
        }

        // filter based on sorting options [sort by date, price if enabled]
        if ($sorting) {
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
                     $entryQuery->orderBy('postDate asc');
                    break;
            }
        }

         // filter based on category
        if ($catfilter && !empty($catfilter[0])) {
             $catSlug = $this->getCatSlug($catfilter);
              $entryQuery->relatedTo = array(
              'targetElement' => array_merge(['and'], $catSlug),
              );
        }

        // call template based on imput type user clicked on frontend
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

        // filter based on limit [based on setting in cp]
        $entryQuery->limit($limit);

        // calculate offset,in CP user have option to skip entries,
        // we treat it as offset
        $offset = $offset + (($currentpage-1)*$limit);

        // filter based on offset
        $entryQuery->offset($offset);
        
        $entries = $entryQuery->all();

        if ($entries) {
            // return $entries;
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
     * @return string
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
     * @param  [type] $sectionName [description]
     * @param  [type] $handle      [description]
     * @return [type]              [description]
     */
    public function getExtraFiltersRange($sectionName = null, $handle = null)
    {
        $numbeRange = [];
        $extraFieldState = CraftAjaxinate::$plugin->getSettings()->extraFieldState;
        if ($handle != null) {
             $numbeRange[] = $this->getNumberRange('', $handle);
        } else if ($extraFieldState) {
            $getExtraFieldsSelected = CraftAjaxinate::$plugin->craftAjaxinateService->getExtraFieldsSelected();
            foreach ($getExtraFieldsSelected as $handle) {
                $handle = substr("$handle", 0, strrpos($handle, '_'));
                $numbeRange[] = $this->getNumberRange('', $handle);
            }
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
        $html = '';
        $fields = (new Query())
                    ->select(['id','handle','name','instructions','type'])
                    ->from(['{{%fields}}'])
                    ->all();

       
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);

         $ectraFields = CraftAjaxinate::$plugin->craftAjaxinateService->getExtraFieldsSelected();
        foreach ($ectraFields as $value) {
            $handleIdList[] = substr("$value", strrpos($value, '_') + 1);
        }

        foreach ($fields as $field) {
            $output[(int) $field['id']] = array(
                'id'            => (int) $field['id'],
                'handle'        => $field['handle'],
                'name'          => $field['name'],
                'instructions'  => $field['instructions'],
                'type'          => $field['type']
            );

            // call template based on input field type
            if (in_array($field['id'], $handleIdList)) {
                switch ($field['type']) {
                    case 'craft\fields\PlainText':
                        $html .=  Craft::$app->view->renderTemplate(
                            'craft-ajaxinate/_render/_text',
                            [
                            'field' => $field,
                            'options' => $options,
                            ]
                        );
                        break;
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
                        $html .=  Craft::$app->view->renderTemplate(
                            'craft-ajaxinate/_render/_checkbox',
                            [
                            'field' => $field,
                            'options' => $options,
                            ]
                        );
                        break;
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
                    case 'craft\fields\Tags':
                        if ($isTagAdded) {
                            break;
                        }
                        $html .=  Craft::$app->view->renderTemplate(
                            'craft-ajaxinate/_render/_tags',
                            [
                            'field' => $field,
                            'options' => $options,
                                ]
                        );
                        $isTagAdded= true;
                        break;
                    default:
                        // code...
                        break;
                }
            }
        }

        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);
        echo Template::raw($html);
    }
}
