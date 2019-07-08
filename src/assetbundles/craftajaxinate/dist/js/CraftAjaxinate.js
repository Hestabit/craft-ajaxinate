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

        let settings = $.extend(
            {
                // These are the defaults.
                path: null,
                data: null,
                action: "updatebtn",
                containerClass: ".ajaxDataDump",
                messageClass: ".js_elMessage",
                [window.Craft.csrfTokenName]: window.Craft.csrfTokenValue
            },
            options
        );
        $(settings.messageClass).html("");

        if (settings.action == "reset") {
            $(settings.containerClass).html("");
            return true;
        }
        // let's call the ajax
        let request = $.post({
            url: settings.path,
            dataType: "json",
            data: settings,
            enctype: "multipart/form-data"
        });

        request.done(function(response) {
            if (settings.action == "updatebtn") {
                loadMoreBtn.attr("data-limit", Number(response.limit));
                loadMoreBtn.attr(
                    "data-totalPages",
                    Number(response.totalPages)
                );
            }

            if (response.entries && response.entries != "init") {
                $(settings.containerClass).append(response.entries);
            }

            if (
                settings.action == "sorting" ||
                settings.action == "catFilter"
            ) {
                if (response.entries && response.entries != "init") {
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

        $.fn.loadData({
            path: "/actions/craft-ajaxinate/default/plugin-init",
            data: csrf
        });

        // return this;
    };

    // call onload fn
    $.fn.onload();

    // load more data
    loadMoreBtn.on("click", function() {
        try {
            var currentpage = Number(loadMoreBtn.attr("data-currentpage"));

            loadMoreBtn.attr("data-currentpage", Number(++currentpage));

            // let currentpage = Number(loadMoreBtn.data('currentpage'));
            let sorting = Number(loadMoreBtn.attr("data-sorting"));
            let catfilter = Array(loadMoreBtn.attr("data-catfilter"));
            let extraFilter = JSON.parse(loadMoreBtn.attr("data-extrafilter"));

            $.fn.loadData({
                path: "/actions/craft-ajaxinate/default/load-more",
                action: "loadmore",
                currentpage: currentpage,
                sorting: sorting,
                catfilter: catfilter,
                extrafilter: extraFilter
            });
        } catch (e) {
            console.log(e);
        }
    });

    // sorting changed/selected
    $("#hb_sorting").on("change", function() {
        let e = $(this);
        let sorting = e.children("option:selected").val();
        loadMoreBtn.attr("data-sorting", Number(sorting));
        loadMoreBtn.attr("data-currentpage", Number(1));
        $.fn.loadData({
            path: "/actions/craft-ajaxinate/default/load-more",
            action: "sorting",
            sorting: Number(sorting),
            currentpage: 1
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

        loadMoreBtn.attr("data-currentpage", Number(1));
        // get data based on cat checked
        $.fn.loadData({
            path: "/actions/craft-ajaxinate/default/load-more",
            action: "catFilter",
            catfilter: favorite,
            sorting: sorting,
            extrafilter: extraFilter,
            currentpage: 1
        });
        // return this;
        loadMoreBtn.attr("data-catfilter", favorite);
    });

    // reset button
    $("#js_ResetBtn").on("click", function() {
        loadMoreBtn.attr("data-currentpage", Number(0));
        loadMoreBtn.attr("data-totalpages", Number(0));
        loadMoreBtn.attr("data-sorting", Number(0));
        loadMoreBtn.attr("data-catfilter", "");
        loadMoreBtn.attr("data-extrafilter", "[{}]");

        var response = $.fn.loadData({
            action: "reset"
        });

        if (response) {
            $(".js_eFilterO").prop("checked", false);
            $(".js_Cat").prop("checked", false);
        }
    });

    // on change of filters options
    $(".js_eFilterO").on("change", e => {
        let extraFilter = [];

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
            sorting: sorting
        });
    });
})(jQuery);
