<p align="center"><img src="resources/img/entry-loader.svg" width="381" height="148" alt="Entries Loader And Filter plugin"></p>

# Entries Loader And Filter plugin for Craft CMS 3.x

This plugin will give you an option to load more entries using ajax.

You have options for filters based on fields and categories.

You have the sorting options available.

Number fields are converted to `range` input type,you can apply css as per your need.

You can control the options from the plugin setting page. Just watch below animation for available backend options.

You can pass your custom query as well to get the entries as per your need.

You have the option to create `Multiple Load More` instances on different templates.

If you have already shown some entries (`desc` order) on a template, you can skip the number of entries by adjusting the offset in the Setting page or while calling the `render()`.We will skip those entries then.

# Setting Page

![Screenshot](resources/img/craft-entry-loader-settingpage.gif)

## Requirements

This plugin requires `Craft CMS 3.0.0` or later.

## Installation.

To install the plugin, follow these instructions.

1.  Open your terminal and go to your Craft project:

        cd /path/to/project

2.  Then tell Composer to load the plugin:

        composer require hestabit/craft-ajaxinate

3.  In the Control Panel, go to Settings → Plugins and click the “Install” button for Entries Loader And Filter.

### :warning: Please select these `Supported Field` type only in the plugin setting page or passing in `render()` call.

- Number
- Radio
- Checkbox
- Lightswitch

## Steps to add Load More functionality.

- Add `csrf token` to the template on which you want the Load More functionality. See a sample below.

- Add the below code to the template (on which you added the `csrf token`). This code will add the `Load More button`.


```twig

{{ craft.craftAjaxinate.loadMoreVariable() }}

```

- Add this class **"ajaxDataDump"** to any existing or create a new empty `<div class="ajaxDataDump"></div>`on your template, Plugin will append the new entries to this div.

- **Rendering Template** : Create a new **separate template**. In this template, you have access to `{{ entries }}` object. This object has all the entries based on settings. `Don't put any extra markup here like header or footer`. See an example below.

* Select **Rendering Template** in the plugin’s Setting page or while calling `render()`, that you just created in the above step.

* You are free to apply CSS and define HTML as per your need, on the entries in **Rendering Template**.

- **Options** Available options for **Load More** button:
  - btnWrapperClass : Class to be added on `<div>`.
  - loadMoreName : String to be used for **Load More** button. Default **Load More**

## Load More button example with options

    {{ craft.craftAjaxinate.loadMoreVariable({
          btnWrapperClass:'ajaxBtn',
          loadMoreName: 'Load More'})
    }}

## Steps to add sorting and filters

- All the above steps should be done.
- To render sorting you need to add the below code in your template on which your Load More button is available:

```twig

       {{ craft.craftAjaxinate.render() }}
```

- Adjust settings as per your needs from the plugin’s Setting page.

* **Options for filters and sorting** provide you a way to customize the html that is rendered through the plugin. Available options are:
  - template : Pass the **Rendering Templatet** path.
  - limit : Pass the limit.
  - offset : Entries to skip and load on page load.
  - initLoad : To show entries on page load like `initLoad:true` or `initLoad:false`
  - resetBtnState : To show reset button like `resetBtnState:true` or `resetBtnState:false`
  - extraFilters : To show filters pass fields handle like `extraFilters: ['price', 'featuredEntry', 'anyOtherhandleName']`
  - sortingFilters : To show sorting options `( date and price handle only )` like
                      `sortingFilters:{
                        'date':'dateHandleName',
                        'price':'priceHandleName'
                      },`
  - query : Advanced query options, just pass the parameters in craft format. See examples below.
  - section : Pass the sections name like `['news', 'services']`
  - selectClass : Class to be added on `<select>`.
  - optionClass : Class to be added on `<option>`.
  - nFirstName : String to be used for **Newest First**. Default **Newest First**
  - oFirstName : String to be used for **Oldest First**. Default **Oldest First**
  - lPriceName : String to be used for **Low To High**. Default **Low To High**
  - hPriceName : String to be used for **High To Low**. Default **High To Low**
  - dSortName : String to be used for **Default Soring**. Default **Default Soring**
  - ulClass : Class to be added on `<ul>`.
  - liClass : Class to be added on `<li>`.
  - resetWrapperClass : Class to be added on `<div>` of reset button.
  - catWrapperClass : Class to be added on `<div>` of category option.
  - checkFieldDiv : Class to be added on `<div>` of checkbox fields.
  - sortingWrapperClass : Class to be added on `<div>` of sorting option.
  - catGroup : Array of categories handle like `['cms','craftcms']` Required to show the categories filter.
  - catGroupLimit : Number of categories child to show, `default is 10`.
  - tagGroup : Name of tag group handle like `'blogtag'`.
  - noMoreDataMsg : Message to show when no entries is found as per the settings.



## Sorting Example with options

    {{ craft.craftAjaxinate.render({
      selectClass: 'selectClassWrapper',
      optionClass: 'optionClassWrapper',
      sortingWrapperClass: 'sortingWrapperClasss'
    }) }}

Load 3 entries on page load and show filters of `featuredEntry (lightsqitch), price (number)`.Also, show all child categories of `mensClothing and shoes` and tags of `technology`.

    {{ craft.craftAjaxinate.render({
      template: 'ajax/stories.twig',
      offset: 3,
      initLoad: true,
      resetBtnState: 1,
      extraFilters: ['featuredEntry', 'price'],
      catGroup: ['mensClothing', 'shoes'],
      sortingFilters:{
        'date': 'eventDate',
        'price': 'price',
      },
      tagGroup: ['technology'],      
    }) }}

Load 3 entries on page load and `shortDescription` should not be empty. As the limit is not passed CP settings will be used.

    {{ craft.craftAjaxinate.render({
      template: 'ajax/stories.twig',
      offset: 3,
      initLoad: true,
      resetBtnState: 1,
      query:{
        'shortDescription':':notempty:',
      },
      
    }) }} 


Load 3 entries on page load and `postDate` before `2019-07-31`.

    {{ craft.craftAjaxinate.render({
      template: 'ajax/stories.twig',
      offset: 3,
      initLoad: true,
      resetBtnState: 1,
      query:{
        'before': '2019-07-26'
      },
      
    }) }} 

Load 4 entries on page load and `postDate` before `2019-07-31` and `shortDescription` should not be empty. Each line in `query` has `and` relation between them.

    {{ craft.craftAjaxinate.render({
      template: 'ajax/stories.twig',
      offset: 4,
      limit: 5,
      initLoad: true,
      resetBtnState: 1,
      query:{
        'shortDescription':':notempty:',
        'before': '2019-07-26'
      }      
    }) }}

`title` either `foo` or `bar`. As `template` is not passed CP settings will be used.

    {{ craft.craftAjaxinate.render({
      limit: 5,
      resetBtnState: 1,
      query:{
        'where': ['or', ['like','title','foo'], ['like','title','bar']],
      }      
    }) }}

`title` either `foo` or `bar` or `field_featuredEntry` (`lightswitch`) is active.

Append **field_** before handleName.

    {{ craft.craftAjaxinate.render({
      limit: 5,
      resetBtnState: 1,
      query:{
        'where': ['or', ['like','title','foo'], ['like','title','bar']],
        'orWhere': ['and', ['=','field_featuredEntry',1]],
      }      
    }) }}

Between `2019-07-12` and `2019-07-31` dates.

Append **field_** before handleName.

    {{ craft.craftAjaxinate.render({
      limit: 5,
      resetBtnState: 1,
      query:{
        'postDate': ['and', '>= 2019-07-12', '<= 2019-07-31'],
        'orWhere': ['and', ['=','field_featuredEntry',1]],
      }      
    }) }}


<details>
<summary> Example of csrf  ( There is no need to declare csrf if its already declared in your site)</summary>

```js
# Example of csrf  ( There is no need to declare csrf if its already declared in your site)
{% set csrfToken = {
  csrfTokenName: craft.app.config.general.csrfTokenName,
  csrfTokenValue: craft.app.request.csrfToken,
} %}

<script type="text/javascript">
window.Craft = {{ csrfToken|json_encode|raw }};
</script>

```

</details>

---

<details>
<summary>Example of rendering template (You have access to {{entries}} in this template, You have to iterate on this object)</summary>

```twig

{# Access all the fields in the iteration. #}

{% for item in entries %}
  <a href="{{item.url}}">{{ item.title }}</a>
  <span>Price : {{ item.priceHanlde }} </span>
  .....
{% endfor %}
```

</details>

---

## Entries Loader And Filter Roadmap

Some things to do, and ideas for potential features:

- [x] Load more entries (Particular template )
- [x] Option to select the default template in backend
- [x] Sorting
- [x] Filters
- [x] Multiple Load More
- [x] Custom Queries
- [x] Option to load entries on onload
- [x] Filter based on future entries
- [ ] Search

## Support

Found any issue :confused: , [Create a Github Issue](https://github.com/Hestabit/craft-ajaxinate/issues/new)

## Credits

- Developed by [Saurabh Ranjan](http://maddyboy.github.io)
- Boilerplate by [pluginfactory](https://pluginfactory.io)

Brought to you by [HestaBit](https://github.com/Hestabit)
