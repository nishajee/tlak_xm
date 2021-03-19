@extends('layouts.app')
@section('title', 'Login')
@section('meta_title_des_keywords')
<meta name="description" content="">
<meta name="keywords" content="Forgot Password, Reset Password, Change Password">
@endsection
@section('tlakjs')
<script src="{{ asset('js/tlak.js') }}" defer></script>
@endsection
@section('content')

<section class="login-block">
    <div class="container">
        <div class="row">
            <div class="col-md-3 login-sec">
            </div>
            <div class="col-md-6 login-sec">
                <div class="logo">
                    <a href="https://www.tlakapp.com/"><img src="{{ asset('media/logos/logo.png') }}" alt="TLAK Logo" class="img-fluid mx-auto"></a>
                </div>
                <h2 class="text-center">Reset password</h2>
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus readonly="">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Reset Password') }}
                                </button>
                            </div>
                        </div>
                    </form>
                <div class="copy-text">Copyright <strong>&#169;</strong> <script>document.write(new Date().getFullYear())</script><br> <a href="http://www.watconsultingservices.com" target="_blank"><strong>WAT Consulting Services Pvt. Ltd.</strong></a>
                </div>
            </div>
            <div class="col-md-3 login-sec">
            </div>
        </div>
    </div>
</section>
<style type="text/css">
    .navbar-expand-md{
        display: none;
}
 
</style>

@endsection
