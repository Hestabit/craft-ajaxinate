/**
 * Entries Loader And Filter plugin for Craft CMS
 *
 * Entries Loader And Filter JS
 *
 * @author    Hestabit Technologies <technology@hestabit.com>
 * @copyright Copyright (c) 2019 Hestabit Technologies
 * @link     https://www.hestabit.com/
 * @package   CraftAjaxinate
 * @since     1.0.0
 */
(function($) {
    "use strict";

    let loadMoreBtn = $("#js_Hb_LoadMore");
    let propFilter = $(".propFilter");

    // fetch data and update the entries
    $.fn.loadData = function(options) {
        loadMoreBtn.show();
        var currentpage = Number(loadMoreBtn.attr("data-currentpage"));

        let settings = $.extend({
                // These are the defaults.
                path: null,
                data: null,
                action: "init",
                containerClass: ".ajaxDataDump",
                messageClass: ".js_elMessage",
                [window.Craft.csrfTokenName]: window.Craft.csrfTokenValue
            },
            options
        );
        $(settings.messageClass).html("");

        if (settings.action == "reset") {
            $(settings.containerClass).html("");
            // return true;
        }
        // let's call the ajax
        let request = $.post({
            url: settings.path,
            dataType: "json",
            data: settings,
            enctype: "multipart/form-data"
        });

        request.done(function(response) {
            if (settings.action == "init") {
                loadMoreBtn.attr("data-limit", Number(response.limit));
                // loadMoreBtn.attr("data-totalPages", Number(response.totalPages));
            }

            //append
            if (response.entries && response.entries != "") {
                $(settings.containerClass).append(response.entries);
            }

            //replace
            if (settings.action == "sorting" || settings.action == "catFilter") {
                if (response.entries && response.entries != "") {
                    $(settings.containerClass).html(response.entries);
                }
            }
            // no data found
            if (response.entries == null) {
                $(settings.messageClass).html(response.message);
                loadMoreBtn.hide();
            }
        });

        request.fail(function(jqXHR, textStatus) {
            alert("Request failed: " + textStatus);
        });

        // return this;
    };
    // onload
    $.fn.onload = function() {
        let csrf = {
            [window.Craft.csrfTokenName]: window.Craft.csrfTokenValue
        };
        var currentpage = Number(loadMoreBtn.attr("data-currentpage"));
        let settings = $('#js_ajaxinateRender').data('settings');

        loadMoreBtn.attr("data-currentpage", Number(++currentpage));
        $.fn.loadData({
            path: "/actions/craft-ajaxinate/default/plugin-init",
            data: csrf,
            currentpage,
            settings
        });

        // return this;
    };

    // call onload fn
    $.fn.onload();

    // load more data
    loadMoreBtn.on("click", function() {
        try {
            let currentpage = Number(loadMoreBtn.attr("data-currentpage"));
            loadMoreBtn.attr("data-currentpage", Number(++currentpage));
            // let currentpage = Number(loadMoreBtn.data('currentpage'));
            let sorting = Number(loadMoreBtn.attr("data-sorting"));
            let catfilter = Array(loadMoreBtn.attr("data-catfilter"));
            let extraFilter = JSON.parse(loadMoreBtn.attr("data-extrafilter"));
            let settings = $('#js_ajaxinateRender').data('settings');
            $.fn.loadData({
                path: "/actions/craft-ajaxinate/default/load-more",
                action: "loadmore",
                currentpage: currentpage,
                sorting: sorting,
                catfilter: catfilter,
                extrafilter: extraFilter,
                settings
            });
        } catch (e) {
            console.log(`Error occured in craft-ajaxinate :: ${e}`);
        }
    });

    // sorting changed/selected
    $("#hb_sorting").on("change", function() {
        let e = $(this);
        let sorting = e.children("option:selected").val();
        let settings = $('#js_ajaxinateRender').data('settings');
        loadMoreBtn.attr("data-sorting", Number(sorting));
        loadMoreBtn.attr("data-currentpage", Number(1));

        $.fn.loadData({
            path: "/actions/craft-ajaxinate/default/load-more",
            action: "sorting",
            sorting: Number(sorting),
            currentpage: 1,
            settings
        });
    });

    // cat changed/selected
    $("input[name='cat']").on("change", function() {
        let favorite = [];
        $.each($("input[name='cat']:checked"), function() {
            favorite.push($(this).val());
        });

        let sorting = Number(loadMoreBtn.attr("data-sorting"));
        let catfilter = Array(loadMoreBtn.attr("data-catfilter"));
        let extraFilter = JSON.parse(loadMoreBtn.attr("data-extrafilter"));
        let settings = $('#js_ajaxinateRender').data('settings');
        loadMoreBtn.attr("data-currentpage", Number(1));
        // get data based on cat checked
        $.fn.loadData({
            path: "/actions/craft-ajaxinate/default/load-more",
            action: "catFilter",
            catfilter: favorite,
            sorting: sorting,
            extrafilter: extraFilter,
            currentpage: 1,
            settings
        });
        // return this;
        loadMoreBtn.attr("data-catfilter", favorite);
    });

    // reset button
    $("#js_ResetBtn").on("click", function() {
        loadMoreBtn.attr("data-currentpage", Number(1));
        // loadMoreBtn.attr("data-totalPages", Number(0));
        loadMoreBtn.attr("data-sorting", Number(0));
        loadMoreBtn.attr("data-catfilter", "");
        loadMoreBtn.attr("data-extrafilter", "[{}]");
        var currentpage = Number(loadMoreBtn.attr("data-currentpage"));
        let settings = $('#js_ajaxinateRender').data('settings');
        var response = $.fn.loadData({
            action: "reset",
            path: "/actions/craft-ajaxinate/default/plugin-init",
            currentpage,
            settings
        });
        $(".js_eFilterO").prop("checked", false);
        $(".js_Cat").prop("checked", false);
    });

    // on change of filters options
    $(".js_eFilterO").on("change", e => {
        let extraFilter = [];
        let settings = $('#js_ajaxinateRender').data('settings');
        $.each($(".js_eFilterO"), function() {
            let inputType = $(this).attr("type");
            let valueFilter = $(this).val();
            let handleName = $(this).attr("name");
            let ftype = $(this).attr("data-ftype");


            if (inputType == "range") {
                extraFilter.push({
                    handle: handleName,
                    value: valueFilter,
                    ftype: ftype
                });
            }

            if ($(this).prop("checked")) {
                extraFilter.push({
                    handle: handleName,
                    value: valueFilter,
                    ftype: ftype
                });
            }
        });

        loadMoreBtn.attr("data-currentpage", Number(1));
        loadMoreBtn.attr("data-extrafilter", JSON.stringify(extraFilter));
        let catfilter = Array(loadMoreBtn.attr("data-catfilter"));
        let sorting = Number(loadMoreBtn.attr("data-sorting"));

        // call ajax to load data
        $.fn.loadData({
            path: "/actions/craft-ajaxinate/default/load-more",
            action: "catFilter",
            extrafilter: extraFilter,
            catfilter: catfilter,
            currentpage: 1,
            sorting,
            settings
        });
    });
})(jQuery);