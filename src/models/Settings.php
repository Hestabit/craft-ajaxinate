<?php
/**
 * Entries Loader And Filter plugin for Craft CMS 3.x
 *
 * Entries Loader And Filter
 *
 * @link      https://hestabit.com
 * @copyright Copyright (c) 2019 Hestabit Technologies
 */

namespace hestabit\craftajaxinate\models;

use hestabit\craftajaxinate\CraftAjaxinate;

use Craft;
use craft\base\Model;

/**
 * CraftAjaxinate Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author  Hestabit Technologies <technology@hestabit.com>
 * @package CraftAjaxinate
 * @since   1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some field model attribute
     *
     * @var string
     */
    public $noMoreData = 'No more data found.';
    public $sectionListAttribute = '';
    public $sectionsSelected = '';
    public $extraFieldSelected = '';
    public $offsetEntries = 0;
    public $limitEntries = 0;
    public $outputTemplate='';
    public $customFieldData = '';
    public $catList;
    public $sortBydate;
    public $sortByPrice;
    public $priceFilterState;
    public $sortingFilterState;
    public $catFilterState;
    public $resetBtnState;
    public $extraFieldState;
    
    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['limitEntries', 'number'],
            ['noMoreData', 'string'],
        ];
    }
}
