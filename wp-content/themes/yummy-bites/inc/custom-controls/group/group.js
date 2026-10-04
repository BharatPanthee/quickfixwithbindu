/**
 * File group.js.
 *
 * Theme Group customizer.
 * 
 * @package Yummy Bites
 */

(function ($) {
    "use strict";
    $(document).on("click", ".yummy-bites-customizer-group-collapsible .head-label", (function () {
        var container = $(this).closest(".yummy-bites-customizer-group");
        container.find(" > .group-content").slideToggle(200);
        container.toggleClass("is-active");
        return false;
    }));

})(jQuery);