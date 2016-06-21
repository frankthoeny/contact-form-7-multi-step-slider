/* Contact Form 7 Mult Step Slider jQuery Plugin */
(function($) {

    $.fn.cf7msslider = function( options ) {
        
        // CF7 multi step slider default settings:
        var settings = $.extend({
            // These are the defaults.
            slidesContainerId: "#cf7ms-slider", 
        }, options );
 
        
        this.each(function() {

            var slidesContainerId = "#"+($(this).attr("id"));            
            var len = $(slidesContainerId+" .wpcf7").length;            // get number of slides
            var slidesContainerWidth = len*100+"%";                     // get width of the slide container
            var slideWidth = (100/len)+"%";                             // get width of the slides

            // set slide container width
            $(slidesContainerId+" .cf7ms-slides-container").css({
                width : slidesContainerWidth,
                visibility : "visible"
            });

            // set slide width
            $(slidesContainerId+" .wpcf7").css({
                width : slideWidth
            });
            
            // set the first slide active
            $(slidesContainerId+" .wpcf7").first().addClass("cf7ms-first cf7ms-active");
            $(slidesContainerId+" .wpcf7").last().addClass("cf7ms-last");
            
            // set the next (submit) button class
            $(".stepsNav input[value='Next']").addClass("wpcf7-next");             
            
            // handle the previous clicking functionality
            $(slidesContainerId+" .wpcf7-previous, "+slidesContainerId+" .wpcf7-back").click(function(){
                var i = $(slidesContainerId+" .wpcf7.cf7ms-active").index();                                 
                var n = i-1;
                var slideRight = "-"+n*100+"%";
                $(slidesContainerId+" .wpcf7.cf7ms-active").removeClass("cf7ms-active").prev(".wpcf7").addClass("cf7ms-active");
                $(slidesContainerId+" .wpcf7.cf7ms-active fieldset").show();
                $(slidesContainerId+" .cf7ms-slides-container").animate({ 
                    marginLeft : slideRight
                },350);                 
            });
            
        });

        // return this for chainability
        return this;

    }

}(jQuery));