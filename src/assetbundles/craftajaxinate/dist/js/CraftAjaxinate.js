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
    let processing = false;
    let loadMoreBtn = $("#js_Hb_LoadMore");
    let propFilter = $(".propFilter");
    // added
    let loader = $("#js_Hb_Loader");
    let scrollActive = Boolean(loadMoreBtn.attr("data-scrollActive")) ? Boolean(loadMoreBtn.attr("data-scrollActive")) : false;
    let timeOut;
    let pageLoaded = 0;
    const pagesToLoad = Number(loadMoreBtn.attr("data-pagesToLoad"));
    const bottomOffset = Number(loadMoreBtn.attr("data-bottomOffset"));;
    // end
    // fetch data and update the entries
    $.fn.loadData = function(options) {
        loader.show();
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
        let containerClass = settings.settings.containerClass;
        let messageClass = settings.settings.messageClass;

        if (containerClass && containerClass != null) {
            settings.containerClass = containerClass;
        }
        if (messageClass && messageClass != null) {
            settings.messageClass = messageClass;
        }


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
            processing = false;
            loader.hide();
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
                loadMoreBtn.attr("data-status", Number(0));
                $(settings.messageClass).html(response.message);
                loadMoreBtn.hide();
            } else {
                loadMoreBtn.attr("data-status", Number(1));
            }
        });

        request.fail(function(jqXHR, textStatus) {
            console.log("[Craft-ajaxinate] :  Request failed: " + textStatus);
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
        // reset counter of onscroll events
        pageLoaded = 0;
        loadMoreDataAjax();
    });

    // sorting changed/selected
    $("#hb_sorting").on("change", function() {
        // reset counter of onscroll events
        pageLoaded = 0;

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
        // reset counter of onscroll events
        pageLoaded = 0;

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
        // reset counter of onscroll events
        pageLoaded = 0;
        scrollToTop(100);

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
        // reset counter of onscroll events
        pageLoaded = 0;

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

    const loadMoreDataAjax = () => {
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
            console.log(`[Craft-ajaxinate] error : " ${e}`);
        }
    };

    // scroll events only 
    if (scrollActive) {
        $(window).scroll(function() {
            let dataAavailable = Number(loadMoreBtn.attr("data-status"));

            if (processing) {
                return false;
            }

            // if no data found in last ajax attempt
            if (!dataAavailable) {
                return false;
            }

            loadMoreBtn.hide();

            let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;

            let isReachingBottom = (document.body.offsetHeight - (window.innerHeight + scrollTop)) < bottomOffset;

            if (isReachingBottom) {
                processing = true;

                if (pageLoaded >= pagesToLoad) {
                    if (dataAavailable) {
                        loadMoreBtn.show();
                    }
                    return false;
                }

                pageLoaded++;
                requestAnimationFrame(callDebounce);
            }
        });
    }

    let callDebounce = debounce(loadMoreDataAjax, 350);

    // https://davidwalsh.name/javascript-debounce-function
    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this,
                args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    };



    function scrollToTop(wait = 10) {
        let scrollTop = document.body.scrollTop || document.documentElement.scrollTop;
        if (scrollTop != 0) {
            window.scrollBy(0, -20);
            timeOut = setTimeout(scrollToTop, wait);
        } else {
            clearTimeout(timeOut);
        }
    }
})(jQuery);