<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="author" content="TLAK">
<link rel="canonical" href="{{ url()->current() }}" />
<title>@yield('title')</title>
@section('meta_title_des_keywords')


@show

<link rel="icon" href="{{ asset('media/logos/favicon.ico') }}" type="image/x-icon">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700">
<link href="{{ asset('plugins/global/plugins.bundle.css') }}" rel="stylesheet">
<link href="{{ asset('css/style.bundle.css') }}" rel="stylesheet">
<link href="{{ asset('css/tlak.css') }}" rel="stylesheet">
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-161851691-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'UA-161851691-1');
</script>
<!-- Global site tag (gtag.js) - Google Ads: 494643726 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-494643726"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-494643726');
</script>
<!-- Facebook Pixel Code -->
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '233675964392791');
  fbq('track', 'PageView');
</script>
