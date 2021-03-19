(function($) {
    'use strict';
    $(function() {
        var body = $('body');
        var footer = $('.footer');

        var current = location.pathname.split("/").slice(-1)[0].replace(/^\/|\/$/g, '');
        $('.navbar.horizontal-layout .nav-bottom .page-navigation .nav-item').each(function() {
            var $this = $(this);
            if (current === "") {
                //for root url
                if ($this.find(".nav-link").attr('href').indexOf("index.html") !== -1) {
                    $(this).find(".nav-link").parents('.nav-item').last().addClass('active');
                    $(this).addClass("active");
                }
            } else {
                //for other url
                if ($this.find(".nav-link").attr('href').indexOf(current) !== -1) {
                    $(this).find(".nav-link").parents('.nav-item').last().addClass('active');
                    $(this).addClass("active");
                }
            }
        })

        $(window).scroll(function() {
            var headerBottom = '.navbar.horizontal-layout .nav-bottom';
            if ($(window).scrollTop() >= 70) {
                $(headerBottom).addClass('fixed-top');
            } else {
                $(headerBottom).removeClass('fixed-top');
            }
        });

        $(".navbar.horizontal-layout .navbar-menu-wrapper .navbar-toggler").on("click", function() {
            $(".navbar.horizontal-layout .nav-bottom").toggleClass("header-toggled");
        });

        //checkbox and radios
        $(".form-check .form-check-label,.form-radio .form-check-label").not(".todo-form-check .form-check-label").append('<i class="input-helper"></i>');
    });

})(jQuery);
$(document).ready(function() {
    var mainBodycontainer = $(this).height();
    /*console.log(mainBodycontainer)*/
    /*alert(pullitheight)*/
    $("#mainBodycontainer").css({
        'min-height': (mainBodycontainer - 177) + 'px'
    });
    $("div#myId").dropzone({ url: "/file/post" });

});
$(window).bind('resize', function() {
    var mainBodycontainer = $(this).height();
    $("#mainBodycontainer").css({
        'min-height': (mainBodycontainer - 177) + 'px'
    });
});
$(function() {
    $("#Startdatepicker").datepicker();
    $("#Enddatepicker").datepicker();
    $('#timepicker').timepicker({
        //timeFormat: 'h:mm p',
        dynamic: false,
        dropdown: true,
        scrollbar: true
    });
    $('.timezoneselect').select2();

});
$(function(){
    var current = location.pathname;
    $('nav#itinerary-tabs .nav-item a').each(function(){
        var $this = $(this);
        //console.log($this.attr('href').indexOf(current));
        if(current.indexOf($this.attr('href')) !== -1){
            // if the current path is like this link, make it active
            $this.addClass('active');
        }
        else {
            $this.removeClass('active');
        }
    })
})
$(document).ready(function () {


    uploadImage()


    var ID
    var way = 0
    var queue = []
    var fullStock = 10
    var speedCloseNoti = 1000

    function generateID() {
        var text = $('header span')
        var newID = ''

        for(var i = 0; i < 3; i++) {
            newID += Math.floor(Math.random() * 3)
        }

        ID = 'ID: 5988' + newID
        text.html(ID)
    }



    function selectionOption() {
        var select = $('.select-option .head')
        var option = $('.select-option .option div')

        select.on('click', function (event) {
            event.stopPropagation()
            $('.select-option').addClass('active')
        })

        option.on('click', function () {
            var value = $(this).attr('rel')
            $('.select-option').removeClass('active')
            select.html(value)

            $('select#category').val(value)
        })
    }

    function removeClass() {
        $('body').on('click', function () {
            $('.select-option').removeClass('active')
        })
    }

    function uploadImage() {
        var button = $('.images .pic')
        var uploader = $('<input type="file" accept="image/*" />')
        var images = $('.images')

        button.on('click', function () {
            uploader.click()
        })

        uploader.on('change', function () {
            var reader = new FileReader()
            reader.onload = function(event) {
                images.prepend('<div class="img" style="background-image: url(\'' + event.target.result + '\');" rel="'+ event.target.result  +'"><span>remove</span></div>')
            }
            reader.readAsDataURL(uploader[0].files[0])

        })

        images.on('click', '.img', function () {
            $(this).remove()
        })

    }



})
// $(document).ready(function(){
//     /*$("#btn1").click(function(){
//      $("p").append(" <b>Appended text</b>.");
//      });*/
//     html = '<div class="days"><hr><div class="close-this" onclick="this.closest(\'.days\').remove();"><span>&times;</span></div><div class="row"><div class="col-md-6"><div class="floating-label form-group"><input class="floating-input form-control" type="text" placeholder="" value="Day 1" disabled="disabled"><label>Day Number </label></div></div><!--<div class="col-md-6"><div class="floating-label"><input class="floating-input form-control" type="text" placeholder=""><label>Day Name <sup>*</sup></label></div></div>--><div class="col-md-6"><div class="floating-label"><input class="floating-input form-control" type="text" placeholder=" " required><label>Day Name <sup>*</sup></label> </div></div></div><div class="row"><div class="col-md-6"><div class="floating-label form-group"> <textarea class="floating-input floating-textarea" placeholder=" " style="min-height: 100px"></textarea> <label>Day Description <sup>*</sup></label> </div></div><div class="col-md-6"><div class="floating-label form-group"><textarea class="floating-input floating-textarea" placeholder=" " style="min-height: 100px"></textarea> <label>Inclusion <sup>*</sup></label></div></div></div><div class="row"><div class="col-md-6"><div class="floating-label form-group"><select name="" id="" class="form-control floating-select" onclick="this.setAttribute(\'value\', this.value);" value=""><option value=""></option> <option>location 1</option><option>Location 2</option><option>Location 3</option></select><label>Select Location<sup>*</sup></label></div></div><div class="col-md-6"><div class="images"><div class="pic">add Image</div></div></div></div></div>'
//     $("#addDay").click(function(){
//         $('.itinerary-setup').append(html);
//     });
// });
/*
$(document).ready(function(){
    /!*$("#btn1").click(function(){
     $("p").append(" <b>Appended text</b>.");
     });*!/
    $("#addDay").click(function(){
        console.log('hi');
        $('.itinerary-setup').append('<div class="days"><div class="row"><div class="col-md-6"><div class="floating-label form-group"><select name="" id="" class="form-control floating-select" ><option value=""></option><option>Details</option><option>Summary</option><option>Summary + details</option></select><label>Home screen view on the app <sup>*</sup></label></div></div><div class="col-md-6"><div class="form-group form-check p-t-10"><input type="checkbox" class="form-check-input" id="Donotshowdays"><label class="form-check-label" for="Donotshowdays">Do not show days</label></div></div></div><div class="row"><div class="col-md-6"><div class="floating-label form-group"><input class="floating-input form-control" type="text" placeholder="" value="Day 1" disabled="disabled"><label>Day Number </label></div></div><!--<div class="col-md-6"><div class="floating-label"><input class="floating-input form-control" type="text" placeholder=""><label>Day Name <sup>*</sup></label></div></div>--><div class="col-md-6"><div class="floating-label"><input class="floating-input form-control" type="text" placeholder=" " required><label>Day Name <sup>*</sup></label> </div></div></div><div class="row"><div class="col-md-6"><div class="floating-label form-group"> <textarea class="floating-input floating-textarea" placeholder=" " style="min-height: 100px"></textarea> <label>Day Description <sup>*</sup></label> </div></div><div class="col-md-6"><div class="floating-label form-group"><textarea class="floating-input floating-textarea" placeholder=" " style="min-height: 100px"></textarea> <label>Inclusion <sup>*</sup></label></div></div></div><div class="row"><div class="col-md-6"><div class="floating-label form-group"><select name="" id="" class="form-control floating-select" ><option value=""></option> <option>location 1</option><option>Location 2</option><option>Location 3</option></select><label>Select Location<sup>*</sup></label></div></div><div class="col-md-6"><div class="images"><div class="pic">add</div></div></div></div></div>');
    });
});*/
