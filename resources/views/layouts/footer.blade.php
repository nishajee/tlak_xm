<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- <script src="{{ asset('plugins/global/plugins.bundle.js') }}" type="text/javascript"></script> -->
<!-- <script src="{{ asset('js/scripts.bundle.js') }}" type="text/javascript"></script> -->
<script src="{{ asset('js/tlak.js') }}" type="text/javascript"></script>

<script src="{{ asset('js/jquery.validate.js') }}" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/additional-methods.min.js"></script>
    <style>
        .error {
            color: red;
        }
    </style>
    <script>

        $(document).ready(function () {
            if ($("#registerCompany").length > 0) {
              //alert('hh');
                $('#registerCompany').validate({
                    ignore: "",
                    // initialize the plugin
                    rules: {

                        street_address: {
                            required: true,
                        },
                        locality: {
                            required: true
                        },
                        postal_code: {
                            required: true
                        },
                        country: {
                            required: true,
                        },
                        
                    }

                });
            }
        });
    </script>
<script>
    // $("#nextpageRegistation").click(function () {
    //     /*alert('hi');*/
    //     $("#registationPart2").css('display', 'block');
    //     $("#registationPart1").css('display', 'none');
    // });
    // $("#backpageRegistation").click(function () {
    //     /*alert('hi');*/
    //     $("#registationPart1").css('display', 'block');
    //     $("#registationPart2").css('display', 'none');
    // });
    $("#phone").keypress(function (e) {
       //if the letter is not digit then display error and don't type anything
        var enumber=$("#phone").val();
        var ln=enumber.length;
        if(ln>14){
            $("#phone_error").html("Phone must be not more than 14 Digits ").show().fadeOut(5000);
                 return false;
        }
        if (e.which != 8 && e.which != 0 && (e.which != 43)  && e.which != 107 && (e.which < 48 || e.which > 57)) {
          //display error message
          $("#phone_error").html("Digits Only").
          show().fadeOut(5000);
          return false;
        }
    });

    function next() {
    var name = $('#name').val();
    if (name == "") {
       $("#name_error").show();
       $("#name").focus();
       return false;
    }
    else{
      $("#name_error").hide();
    }

    var email = $('#email').val();
    if (email == "") {
       $("#email_error").show();
       $("#email").focus();
       return false;
    }
    else{
      $("#email_error").hide();
    }

    var password = $('#password').val();
    if (password == "") {
       $("#password_error").show();
       $("#password").focus();
       return false;
    }
    else{
      $("#password_error").hide();
    }

    var password_confirmation = $('#password-confirm').val();
    if (password_confirmation == "") {
       $("#con_password_error").show();
       $("#password-confirm").focus();
       return false;
    }
    else{
      $("#con_password_error").hide();
    }

    var company_name = $('#company_name').val();
    if (company_name == "") {
       $("#company_name_error").show();
       $("#company_name").focus();
       return false;
    }
    else{
      $("#company_name_error").hide();
    }

    var company_id = $('#company_id').val();
    if (company_id == "") {
       $("#company_id_error").show();
       $("#company_id").focus();
       return false;
    }
    else{
      $("#company_id_error").hide();
    }

    var phone = $('#phone').val();
    if (phone == "") {
       $("#phone_error").show();
       $("#phone").focus();
       return false;
    }
    else{
      $("#phone_error").hide();
    }

    $("#registationPart2").css('display', 'block');
    $("#registationPart1").css('display', 'none');
};
function back() {
    /*alert('hi');*/
    $("#registationPart1").css('display', 'block');
    $("#registationPart2").css('display', 'none');
};

</script>

