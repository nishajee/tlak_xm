<!DOCTYPE html> 
<html lang="en">
<head><!--insert all stylesheet links -->      
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="icon" href="{{ asset('media/logos/favicon.ico') }}" type="image/x-icon">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="author" content="TLAK">
<link rel="canonical" href="{{ url()->current() }}" />
@section('meta_title_des_keywords')
<meta name="description" content="">
<meta name="keywords" content="">
@endsection
<title>@yield('title')</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700">
<link href="{{ asset('plugins/custom/fullcalendar/fullcalendar.bundle.css') }}">
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet">
<link href="{{ asset('plugins/global/plugins.bundle.css') }}" rel="stylesheet">
<link href="{{ asset('css/style.bundle.css') }}" rel="stylesheet">
<link href="{{ asset('css/pages/invoices/invoice-2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('css/tlak.css') }}" rel="stylesheet">
     <!--  <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css"> -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="{{ asset('plugins/global/plugins.bundle.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/scripts.bundle.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/custom/fullcalendar/fullcalendar.bundle.js') }}" type="text/javascript"></script>
<!-- <script src="//maps.google.com/maps/api/js?key=AIzaSyBTGnKT7dt597vo9QgeQ7BFhvSRP4eiMSM" type="text/javascript"></script> -->
<!-- <script src="{{ asset('plugins/custom/gmaps/gmaps.js') }}" type="text/javascript"></script> -->
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="{{ asset('js/pages/dashboard.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/pages/crud/metronic-datatable/base/html-table.js') }}" type="text/javascript"></script>
<!-- <script src="{{ asset('js/pages/crud/file-upload/dropzonejs.js') }}" type="text/javascript"></script> -->
<script src="{{ asset('js/pages/crud/forms/widgets/bootstrap-timepicker.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/pages/crud/forms/widgets/select2.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/pages/crud/forms/widgets/bootstrap-select.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/pages/crud/forms/widgets/form-repeater.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/pages/components/calendar/basic.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/tlak.js') }}" type="text/javascript"></script>
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-161851691-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'UA-161851691-1');
</script>
</head>
     
      @include('layouts.header')
      
        
       @section('content')

       @show
       
     <div class="kt-footer  kt-footer--extended  kt-grid__item" id="kt_footer" style="background-image: url({{url('media/bg/bg-2.jpg')}});">
     <div class="kt-footer__bottom">
        <div class="kt-container ">
           <div class="kt-footer__wrapper">
              <div class="kt-footer__logo">
                 <a class="kt-header__brand-logo">
                    <img alt="Logo" src="{{ asset('media/logos/logo.png')}}" class="kt-header__brand-logo-sticky" width="130">
                 </a>
                 <div class="kt-footer__copyright">
                   2021&nbsp;&copy;
                    <a>TLAK</a> | All Rights Reserved
                 </div>
              </div>
              <div class="kt-footer__menu">
                 <a href="https://www.tlakapp.com/faqs.php" target="_blank">FAQ</a>
                 <a href="https://www.tlakapp.com/terms-and-conditions.php" target="_blank">Terms & Conditions</a>
                 <a href="https://www.tlakapp.com/contact-us.php" target="_blank">Contact Us</a>
              </div>
           </div>
        </div>
     </div>
</div>
</div>
</div>
</div>
<!-- feedback -->
       <ul class="kt-sticky-toolbar" style="margin-top: 30px;">
         <li class="kt-sticky-toolbar__item kt-sticky-toolbar__item--success" id="kt_demo_panel_toggle" data-toggle="kt-tooltip" data-placement="right">
            <a href="#" class="feadbacks" data-toggle="modal" data-target="#kt_modal_1">Feedback</a>
         </li>
       </ul>
       <!-- End feedback -->
<!--begin::Modal-->
<div class="modal fade" id="kt_modal_1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <form method="post" action="{{ action('HomeController@send_feedback') }}" onsubmit = "return(validate());" name="myForm">
    @csrf
    <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-body">
                <div class="form-group">
                    <label for="recipient_phone" class="form-control-label">Phone</label>
                    <input type="number" class="form-control" name="recipient_phone" id="recipient_phone" placeholder="Phone number">
                </div>
                <div class="form-group">
                    <label for="recipient_email" class="form-control-label">Email *</label>
                    <input type="email" class="form-control" name="recipient_email" id="recipient_email" placeholder="Email">
                </div>
                <div class="form-group">
                    <label for="recipient_module" class="form-control-label">Module *</label>
                    <select class="form-control kt-selectpicker" data-size="4" data-live-search="true" name="recipient_module" id="recipient_module">
                      <option value="">Select Module</option>
                      <option value="Inclusions">Inclusions</option>
                      <option value="Location">Locations</option>
                      <option value="Day Wise Itinerary">Itinerary</option>
                      <option value="Add People">Travelers</option>
                      <option value="Flight Search">Flight Info</option>
                      <option value="Flight Search">Notifications/Alert</option>
                      <option value="Document and Creation">Document and Creation</option>
                      <option value="Document and Creation">Operations Team</option>
                      <option value="Document and Creation">Upcoming Tours</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="message_text" class="form-control-label">Feedback *</label>
                    <textarea class="form-control" name="message_text" id="message_text" placeholder="Type your feedback.."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Send feedback</button>
            </div>
        </div>
    </div>
    </form>
</div>
<!--end::Modal-->

<script>
    var KTAppOptions = {
        "colors": {
            "state": {
                "brand": "#366cf3",
                "light": "#ffffff",
                "dark": "#282a3c",
                "primary": "#5867dd",
                "success": "#34bfa3",
                "info": "#36a3f7",
                "warning": "#ffb822",
                "danger": "#fd3995"
            },
            "base": {
                "label": ["#c5cbe3", "#a1a8c3", "#3d4465", "#3e4466"],
                "shape": ["#f0f3ff", "#d9dffa", "#afb4d4", "#646c9a"]
            }
        }
    };

    function validate() {

    if( document.myForm.recipient_email.value == "" ) {
        alert( "Please enter email!" );
        document.myForm.recipient_email.focus() ;
        return false;
    }

    if( document.myForm.recipient_module.value == "" ) {
        alert( "Please select a module!" );
        document.myForm.recipient_module.focus() ;
        return false;
    }

    if( document.myForm.message_text.value == "" ) {
        alert( "Please type your feedback!" );
        document.myForm.message_text.focus() ;
        return false;
    }

  }
</script>
<style type="text/css">
  .feadbacks
    {
        -webkit-transform: rotate(-90deg); 
        -moz-transform: rotate(-90deg);    
        width:100px;
    color: rgb(218, 165, 32);
    font-size: 18px;
    font-weight: 500;
    }
    .kt-footer__copyright a {
    color: #ffdf2e !important;
}

</style>
</body>
</html>

