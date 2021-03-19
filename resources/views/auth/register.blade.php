@extends('layouts.app')
@section('title', 'TLAK Sign Up - Mobile Solutions For Travel Industry')
@section('meta_title_des_keywords')
<meta name="description" content="Sign up for TLAK! TLAK Provides Mobile Solutions for Travel Agencies, Tour Operators, OTA, Travel Management Companies and Tourism Boards.">
<meta name="keywords" content="TLAK Signup, TLAK Registration, Create TLAK Account">
@endsection

@section('content')
<section class="register-block">
    <div class="container">
        <div class="row d-table-1">
            <div class="col-md-4 login-sec register-sec text-center d-table-cell-reg">
                 <div class="logo kt-mt-50">
                     <a href="https://www.tlakapp.com/"><img src="{{ asset('media/logos/logo.png') }}" alt="TLAK Logo" class="img-fluid mx-auto"></a>
                </div>
                <h2 class="text-center kt-mt-50">Register a Business Account</h2>
                <p class="m-t-50">Already have an account. <strong><a href="{{ route('login') }}">LOGIN</a></strong></p>
                <div class="copy-text register-copyright1">Copyright <strong>&#169;</strong>
                    <script>document.write(new Date().getFullYear())</script><br>
                    <a href="http://www.watconsultingservices.com" target="_blank"><strong>WAT Consulting Services Pvt. Ltd.</strong></a>
                </div>
            </div>
            <div class="col-md-8 register-form  d-table-cell-reg">
                <form method="POST" action="{{ route('register') }}" class="floating-form" id="registerCompany">
                    @csrf
                    <div id="registationPart1">
                        <div class="floating-label form-group">
                            <input id="name" class="floating-input form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" type="text" placeholder=" " onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode === 32)">
                            <label>Contact Person Name<sup>*</sup></label>
                            <span class="invalid-feedback" role="alert" style="display:none" id="name_error" >
                                <strong>Please enter contact person name</strong>
                            </span>
                            @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        <div class="floating-label form-group">
                            <input id="email" name="email" value="{{ old('email') }}" class="floating-input form-control @error('email') is-invalid @enderror" type="email" placeholder=" ">
                            <label>Email ID <sup>*</sup></label>
                            <span class="invalid-feedback" role="alert" style="display:none" id="email_error">
                                <strong>Please enter email.</strong>
                            </span>
                            @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        
                        <div class="floating-label form-group">
                            <input id="password" name="password" class="floating-input form-control @error('password') is-invalid @enderror" type="password" placeholder=" " autocomplete="off">
                            <label>Password <sup>*</sup></label>
                            <span class="invalid-feedback" role="alert" style="display:none" id="password_error">
                                <strong>Please enter password.</strong>
                            </span>
                            @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                    
                        <div class="floating-label form-group">
                            <input id="password-confirm" name="password_confirmation" class="floating-input form-control @error('password_confirmation') is-invalid @enderror" type="password" placeholder=" " >
                            <label>Confirm Password <sup>*</sup></label>
                            <span class="invalid-feedback" role="alert" style="display:none" id="con_password_error">
                                <strong>Please enter conform passowrd</strong>
                            </span>
                            @error('password_confirmation')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                            
                        
                        <div class="floating-label form-group">
                            <input id="company_name" name="company_name" value="{{ old('company_name') }}" class="floating-input form-control @error('company_name') is-invalid @enderror" type="text" placeholder=" ">
                            <label>Company Name <sup>*</sup></label>
                            <span class="invalid-feedback" role="alert" style="display:none" id="company_name_error">
                                <strong>Please enter company name</strong>
                            </span>
                            @error('company_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        <div class="floating-label form-group input-group">
                            <input onblur="checkLength(this)" id="company_id" onkeypress="return AvoidSpace(event)" name="company_id" value="{{ old('company_id') }}" class="floating-input form-control @error('company_id') is-invalid     @enderror form-control" type="text" placeholder=" " oninput="myFunction(this.value)" maxlength="6" minlength="3">
                            <div class="input-group-append" data-tip="Your Company ID is the commonly known name of your organization. It can be a short name, acronym or any word or term formed by the first few letters of the name of an organization. Example, the company ID for “ABC Demo Tour & Travels Services Limited” can be ADTTSL or just ‘adttsl’. Please note that your Company ID cannot be more than 6-charaters in length.">
                              <div class="input-group-text" style="color: #C9C9C9; border-left: 2px solid;"><i class="fas fa-question"></i></div>
                            </div>
                            <label>Company ID <sup>*</sup></label>
                            <div id="error" style="color: red; display: none;">Company Id already exist!!</div>
                            <div id="success" class="invalid-feedback" style="display: none;">Company Id is available!!</div>
                            <span style="color: red;" id="companyminlength"></span>
                            <span class="invalid-feedback" role="alert" style="display:none" id="company_id_error">
                                <strong>Please enter company Id</strong>
                            </span>
                            @error('company_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        <script type="text/javascript">
                          function checkLength(el) {
                              if (el.value.length >= 3 && el.value.length <= 6) {
                               return true;
                              }
                              else{
                                $("#companyminlength").html("Company Id must be min 3 & max 6 characters long!!");
                                return false;
                              }
                            }
                          function AvoidSpace(event) {
                              var k = event ? event.which : window.event.keyCode;
                              if (k == 32) return false;
                          }
                        </script>

                        <div class="floating-label form-group">
                            <input id="phone" name="phone" value="{{ old('phone') }}" class="floating-input form-control @error('phone') is-invalid @enderror" type="text" placeholder=" " minlength="8" maxlength="14">
                            <label>Phone Number <sup>*</sup></label>
                            <span class="invalid-feedback" role="alert" style="display:none" id="phone_error">
                                <strong>Please enter phone number</strong>
                            </span>
                             @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        <!-- <div class="floating-label form-group">
                            <input id="contact_person" name="contact_person" value="{{ old('contact_person') }}" class="floating-input form-control @error('contact_person') is-invalid @enderror" type="text" placeholder=" ">
                            <label>Contact Person <sup>*</sup></label>
                            @error('contact_person')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div> -->
                        <a class="btn-next float-right" id="nextpageRegistation" onclick="next()">Next <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div id="registationPart2" style="display: none">
                        <a class="btn-next btn" id="backpageRegistation" onclick="back()"><i class="fas fa-arrow-left"></i>Back</a>

                        <div class="floating-label form-group">
                            <input id="autocomplete" name="street_address" placeholder="Enter your address" type="text" class="floating-input form-control @error('street_address') is-invalid @enderror" value="{{ old('street_address') }}">
                            @error('street_address')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>


                        <div class="floating-label form-group mt-3">
                           <input id="locality" name="locality" value="{{ old('locality') }}" class="floating-input form-control @error('locality') is-invalid @enderror" type="text" placeholder=" ">
                            <label>City <sup>*</sup></label>
                            @error('locality')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="floating-label form-group mt-3">
                           <input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" class="floating-input form-control @error('postal_code') is-invalid @enderror" type="text" placeholder=" ">
                            <label>Zip Code <sup>*</sup></label>
                            @error('postal_code')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="floating-label form-group mt-3">
                           <!-- <input id="country" name="country" value="{{ old('country') }}" class="floating-input form-control @error('country') is-invalid @enderror" type="text" placeholder=" "> -->
                           <select class="floating-input form-control" id="country" name="country">
                               <option value="">Select Country</option>
                               @foreach($country as $country_list)
                                <option value="{{$country_list->country}}">{{$country_list->country}}</option>
                               @endforeach
                           </select>
                            <label>Country <sup>*</sup></label>
                            @error('country')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="floating-label form-group">
                             <input id="company_website" name="company_website" value="{{ old('company_website') }}" class="floating-input form-control @error('company_website') is-invalid @enderror" type="text" placeholder=" ">
                            <label>Company Website</label>
                            @error('company_website')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>

                        <div class="floating-label form-group">
                            <input id="referred_by" name="referred_by" value="{{ old('referred_by') }}" class="floating-input form-control @error('referred_by') is-invalid @enderror" type="text" placeholder=" ">
                            <label>Referred By </label>
                        </div>

                        <div class="floating-label form-group">
                            <input id="hear_about" name="hear_about" value="{{ old('hear_about') }}" class="floating-input form-control @error('hear_about') is-invalid @enderror" type="text" placeholder=" ">
                            <label>How Did You Hear About Us? </label>
                            @error('hear_about')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>

                        <div class="form-group form-check padding-0 d-inline-block">
                            <input type="checkbox" name="term_conditions" class="form-check-input" id="rememberme" value="1">
                             <label class="form-check-label" for="rememberme"><a href="javascript:void(0);">Terms And Conditions</a></label>
                             
                                 @if ($errors->has('term_conditions'))
                                 <span class="help-block">
                                   <strong style="font-size: 80%;color: #dc3545;">{{ $errors->first('term_conditions') }}</strong>
                                 </span>
                                @endif
                             
                        </div>
                        <button type="submit" onClick="ga('send', 'event', { eventCategory: 'Registration', eventAction: 'Button Clicked', eventLabel: 'New Registration', eventValue: 1});" class="btn btn-login float-right" id="myButton"><i class="fas fa-registered"></i>Register</button>
                    </div>
                </form>
            </div>
            <!-- <div class="col-md-12 text-center p-b-20 register-copyright2">
                <div class="copy-text ">Copyright <strong>&#169;</strong>
                    <script>document.write(new Date().getFullYear())</script>
                    <a href="javascript:void(0);"><strong>TlakApp</strong></a>
                </div>
            </div> -->

        </div>
    </div>
</section>

<style type="text/css">
    .navbar-expand-md{
        display: none;
}
 .btn-primary {
    background-color: white;
    border-color: #7d411e;
    transition: transform .5s;
}
.btn-primary:hover {
    background-color: white;
    border-color: #7d411e;
    transform: scale(1.01);
}
.btn-primary:not(:disabled):not(.disabled):active, .btn-primary:not(:disabled):not(.disabled).active, .show > .btn-primary.dropdown-toggle {
    background-color: white;
    border-color: #7d411e;
}
@media (min-width: 768px){
    .register-sec{width: 40%}
    .d-table-cell{vertical-align: top}
    .d-table-cell-reg{display: block}
}
@media (max-width: 767px) {
    .register-sec{padding: 20px 30px}
    .register-form{padding:10px}
    .register-form{padding-top:0}
}
</style>

<script src="https://maps.google.com/maps/api/js?key=AIzaSyDeaIvmws05Lghj6CUUMBvM68Y2qBftMVw&libraries=places&language=en" type="text/javascript"></script>
<script>
   google.maps.event.addDomListener(window, 'load', initialize);
   function initialize() {
       var input = document.getElementById('autocomplete');
       var autocomplete = new google.maps.places.Autocomplete(input);
       autocomplete.addListener('place_changed', function() {
           var place = autocomplete.getPlace();
            for (var i = 0; i < place.address_components.length; i++) {
                    if (place.address_components[i].types[0] == 'locality') {
                        $('#locality').val(place.address_components[i].long_name);
                    }
                    if (place.address_components[i].types[0] == 'country') {
                        $('#country option').each(function(){
                            var $this = $(this);
                            if ($this.val() == place.address_components[i].long_name) {
                                $this.prop('selected', true);
                                return false;
                            }
                        });
                    }
                    // if (place.address_components[i].types[0] == 'short_name') {
                    //     $('#iso_2').val(place.address_components[i].short_name);
                    // }
                    if (place.address_components[i].types[0] == 'postal_code') {
                        $('#postal_code').val(place.address_components[i].short_name);
                    }
                }
        $('#autocomplete').val(place.formatted_address);
       });
   }
</script>
<script>

function myFunction(val) {
  if (val.length > 2) {
    var company_id = val;
    $.ajax(
           {
            headers:{
              'X-CSRF-TOKEN':$("meta[name='csrf-token']").attr("content")
            },
           url: 'api/checkCompanyId',
           type: 'post',
           data: {
               "company_id": val,
           },
           success: function (result){
              if(result == 'true'){
                $('#company_id').addClass("is-invalid");
                $('#error').css("display", "block");
                $('#success').css("display", "none");
              }
              else{
                $('#company_id').addClass("is-valid");
                $('#success').css("display", "block");
                $('#error').css("display", "none");
              }
           }
       });
  }
  else{
      $('#company_id').addClass("is-invalid");
      $('#success').css("display", "none");
      $('#error').css("display", "none");
  }
}


</script>
<style type="text/css">
    [data-tip] {
    position:relative;
}
[data-tip]:before {
    content: '';
    display: none;
    content: '';
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #1A1A1A;
    position: absolute;
    top: -5px;
    right: 15px;
    z-index: 8;
    font-size: 0;
    line-height: 0;
    width: 0;
    height: 0;
}
[data-tip]:after {
    display: none;
    content: attr(data-tip);
    position: absolute;
    bottom: 43px;
    right: 0px;
    padding: 5px 8px;
    background: #1A1A1A;
    color: #fff;
    z-index: 1;
    font-size: 0.85em;
    width: calc(100vw - 60vw);
    /* height: 18px; */
    /* line-height: 18px; */
    -webkit-border-radius: 3px;
    -moz-border-radius: 3px;
    border-radius: 3px;
    /* white-space: nowrap; */
    word-wrap: normal;
    word-break: break-all;
}
[data-tip]:hover:before,
[data-tip]:hover:after {
    display:block;
}
</style>

@endsection
