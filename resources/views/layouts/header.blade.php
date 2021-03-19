<body style="background-image: url({{ asset('media/demos/demo4/header.jpg')}}); background-position: center top; background-size: 100% 350px;"
   class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-page--loading">
   <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed" style="width:100%">
      <div class="kt-header-mobile__logo">
         <a href="{{route('home')}}" style="width:20%">
         @if($tenant->company_logo != null) 
            <img alt="Logo" src="{{'https://s3-tlak-bucket.s3-us-west-2.amazonaws.com/company/'.$tenant->company_logo}}" style="width:100%">
         @else
            <img alt="Logo" src="{{asset('images/your-logo-png.png')}}" style="width:100%">
         @endif      
         </a>
      </div>
      <div class="kt-header-mobile__toolbar">
         <button class="kt-header-mobile__toolbar-toggler" id="kt_header_mobile_toggler"><span></span></button>
         <button class="kt-header-mobile__toolbar-topbar-toggler" id="kt_header_mobile_topbar_toggler"><i
            class="flaticon-more-1"></i></button>
      </div>
   </div>
   <!-- end:: Header Mobile -->
   <div class="kt-grid kt-grid--hor kt-grid--root">
   <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
   <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">
   <!-- begin:: Header -->
   <div id="kt_header" class="kt-header  kt-header--fixed " data-ktheader-minimize="on">
      <div class="kt-container">
         <!-- begin:: Brand -->
         <div class="kt-header__brand   kt-grid__item" id="kt_header_brand">
            <a class="kt-header__brand-logo" href="{{route('home')}}" style="width:100%">
            @if($tenant->company_logo != null)   
               <img alt="Logo" src="{{'https://s3-tlak-bucket.s3-us-west-2.amazonaws.com/company/'.$tenant->company_logo}}" class="kt-header__brand-logo-default" style="max-width: 100px;">
            @else
               <img alt="Logo" src="{{asset('images/your-logo-png.png')}}" class="kt-header__brand-logo-default" style="max-width: 100px;">
            @endif

            @if($tenant->company_logo != null) 
               <img alt="Logo" src="{{'https://s3-tlak-bucket.s3-us-west-2.amazonaws.com/company/'.$tenant->company_logo}}"
               class="kt-header__brand-logo-sticky" style="max-width: 100px;">
            @else
               <img alt="Logo" src="{{asset('images/your-logo-png.png')}}" class="kt-header__brand-logo-sticky" style="max-width: 100px;">
            @endif   
            </a>
         </div>
         <!-- end:: Brand -->
         <!-- begin: Header Menu -->
         <button class="kt-header-menu-wrapper-close" id="kt_header_menu_mobile_close_btn">
         <i class="la la-close"></i>
         </button>
         <div class="kt-header-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_header_menu_wrapper">
            <div id="kt_header_menu" class="kt-header-menu kt-header-menu-mobile ">
               <ul class="kt-menu__nav ">
                  <li class="kt-menu__item {{ (request()->is('dashboard')) ? 'kt-menu__item--active' : '' }}" aria-haspopup="true">
                     <a href="{{url('/dashboard')}}" class="kt-menu__link ">
                     <span class="kt-menu__link-text">Dashboard</span>
                     </a>
                  </li>
               
                  @can('departure_view', Auth::User()->getPermissions())
                  <li class="kt-menu__item {{ (request()->is(['departure', 'departure/*','inclusion/*','location-poi/*','itinerary/*','people/*','flight/*','hotel/*','notifications/*','document-creation/*','communication/*'])) ? 'kt-menu__item--active' : '' }}">
                     <a href="{{route('indexTour')}}" class="kt-menu__link ">
                     <span class="kt-menu__link-text">Departures</span>
                     </a>
                  </li>
                  @endcan
                  @can('poi_view', Auth::User()->getPermissions())
                  <li class="kt-menu__item {{ (request()->is(['poi','poi/*','poi-edit/*'])) ? 'kt-menu__item--active' : '' }}">
                     <a href="{{route('poi.index')}}" class="kt-menu__link ">
                     <span class="kt-menu__link-text">Points of Interest</span>
                     </a>
                  </li>
                  @endcan
                  <!--  <li class="kt-menu__item">
                     <a href="{{route('flight')}}" class="kt-menu__link ">
                        <span class="kt-menu__link-text">Flights</span>
                     </a>
                     </li> -->
                  @can('optional_departure_view', Auth::User()->getPermissions())
                  <li class="kt-menu__item {{ (request()->is(['optional-departure','optional-departure/*'])) ? 'kt-menu__item--active' : '' }}">
                     <a href="{{ route('optional-departure.index') }}" class="kt-menu__link ">
                     <span class="kt-menu__link-text">Upcoming Tours</span>
                     </a>
                  </li>
                  @endcan
                  <li class="kt-menu__item {{ (request()->is(['settings','settings/*','company-creation/*','menu-labels/*'])) ? 'kt-menu__item--active' : '' }}">
                     <a href="{{ url('settings') }}" class="kt-menu__link ">
                     <span class="kt-menu__link-text">Settings</span>
                     </a>
                  </li>
                  <!-- <li class="kt-menu__item  kt-menu__item--submenu kt-menu__item--rel"  data-ktmenu-submenu-toggle="click" aria-haspopup="true">
                     <a  href="javascript:;" class="kt-menu__link kt-menu__toggle"><span class="kt-menu__link-text">Notifications</span><i class="kt-menu__ver-arrow la la-angle-right"></i></a>
                     <div class="kt-menu__submenu kt-menu__submenu--classic kt-menu__submenu--left">
                        <ul class="kt-menu__subnav">
                           <li class="kt-menu__item "  aria-haspopup="true"><a  href="{{ url('scheduled-notifications') }}" class="kt-menu__link "><span class="kt-menu__link-text">Scheduled Notification</span></a></li>
                           <li class="kt-menu__item "  aria-haspopup="true"><a  href="{{ url('location-based-notifications') }}" class="kt-menu__link "><span class="kt-menu__link-text">Location-Based Notification</span></a></li>
                           <li class="kt-menu__item "  aria-haspopup="true"><a  href="{{ url('instant-notifications') }}" class="kt-menu__link "><span class="kt-menu__link-text">Instant Notification</span></a></li>
                        </ul>
                     </div>
                     </li> -->
                  <!-- <li class="kt-menu__item">
                     <a href="javascript:void(0);" class="kt-menu__link ">
                     <span class="kt-menu__link-text">Drive</span>
                     </a>
                  </li> -->
                  <!-- <li class="kt-menu__item">
                     <a href="javascript:void(0);" class="kt-menu__link ">
                     <span class="kt-menu__link-text">User Guide</span>
                     </a>
                  </li> -->
                  <li class="kt-menu__item">
                     <a href="{{ route('add_credit') }}" class="btn btn-warning kt-subheader__btn-options">
                     <span class="kt-menu__link-text">Recharge</span>
                     </a>
                  </li>
               </ul>
            </div>
         </div>
         <!-- end: Header Menu -->
         <!-- begin:: Header Topbar -->
         <div class="kt-header__topbar kt-grid__item">
            <!--begin: Search -->
            <div class="kt-header__topbar-item kt-header__topbar-item--search dropdown" id="kt_quick_search_toggle">
               <div class="kt-header__topbar-wrapper" data-toggle="dropdown" data-offset="10px,0px">
                  <span class="kt-header__topbar-icon">
                     <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                           <rect x="0" y="0" width="24" height="24"/>
                           <path d="M14.2928932,16.7071068 C13.9023689,16.3165825 13.9023689,15.6834175 14.2928932,15.2928932 C14.6834175,14.9023689 15.3165825,14.9023689 15.7071068,15.2928932 L19.7071068,19.2928932 C20.0976311,19.6834175 20.0976311,20.3165825 19.7071068,20.7071068 C19.3165825,21.0976311 18.6834175,21.0976311 18.2928932,20.7071068 L14.2928932,16.7071068 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                           <path d="M11,16 C13.7614237,16 16,13.7614237 16,11 C16,8.23857625 13.7614237,6 11,6 C8.23857625,6 6,8.23857625 6,11 C6,13.7614237 8.23857625,16 11,16 Z M11,18 C7.13400675,18 4,14.8659932 4,11 C4,7.13400675 7.13400675,4 11,4 C14.8659932,4 18,7.13400675 18,11 C18,14.8659932 14.8659932,18 11,18 Z"
                              fill="#000000" fill-rule="nonzero"/>
                        </g>
                     </svg>
                  </span>
               </div>
               <div class="dropdown-menu dropdown-menu-fit dropdown-menu-right dropdown-menu-anim dropdown-menu-lg">
                  <div class="kt-quick-search kt-quick-search--dropdown kt-quick-search--result-compact"
                     id="kt_quick_search_dropdown">
                     <form method="get" class="kt-quick-search__form">
                        <div class="input-group">
                           <div class="input-group-prepend">
                              <span class="input-group-text">
                              <i class="flaticon2-search-1"></i>
                              </span>
                           </div>
                           <input type="text" class="form-control kt-quick-search__input" placeholder="Search...">
                           <div class="input-group-append">
                              <span class="input-group-text">
                              <i class="la la-close kt-quick-search__close"></i>
                              </span>
                           </div>
                        </div>
                     </form>
                     <div class="kt-quick-search__wrapper kt-scroll" data-scroll="true" data-height="325" data-mobile-height="200">
                     </div>
                  </div>
               </div>
            </div>
            <!--end: Search -->
            <!--begin: Notifications -->
            <div class="kt-header__topbar-item dropdown">
               <div class="kt-header__topbar-wrapper" data-toggle="dropdown" data-offset="10px,0px">
                  <span class="kt-header__topbar-icon kt-pulse kt-pulse--light">
                     <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                           <rect x="0" y="0" width="24" height="24"/>
                           <path d="M2.56066017,10.6819805 L4.68198052,8.56066017 C5.26776695,7.97487373 6.21751442,7.97487373 6.80330086,8.56066017 L8.9246212,10.6819805 C9.51040764,11.267767 9.51040764,12.2175144 8.9246212,12.8033009 L6.80330086,14.9246212 C6.21751442,15.5104076 5.26776695,15.5104076 4.68198052,14.9246212 L2.56066017,12.8033009 C1.97487373,12.2175144 1.97487373,11.267767 2.56066017,10.6819805 Z M14.5606602,10.6819805 L16.6819805,8.56066017 C17.267767,7.97487373 18.2175144,7.97487373 18.8033009,8.56066017 L20.9246212,10.6819805 C21.5104076,11.267767 21.5104076,12.2175144 20.9246212,12.8033009 L18.8033009,14.9246212 C18.2175144,15.5104076 17.267767,15.5104076 16.6819805,14.9246212 L14.5606602,12.8033009 C13.9748737,12.2175144 13.9748737,11.267767 14.5606602,10.6819805 Z" fill="#000000" opacity="0.3"/>
                           <path d="M8.56066017,16.6819805 L10.6819805,14.5606602 C11.267767,13.9748737 12.2175144,13.9748737 12.8033009,14.5606602 L14.9246212,16.6819805 C15.5104076,17.267767 15.5104076,18.2175144 14.9246212,18.8033009 L12.8033009,20.9246212 C12.2175144,21.5104076 11.267767,21.5104076 10.6819805,20.9246212 L8.56066017,18.8033009 C7.97487373,18.2175144 7.97487373,17.267767 8.56066017,16.6819805 Z M8.56066017,4.68198052 L10.6819805,2.56066017 C11.267767,1.97487373 12.2175144,1.97487373 12.8033009,2.56066017 L14.9246212,4.68198052 C15.5104076,5.26776695 15.5104076,6.21751442 14.9246212,6.80330086 L12.8033009,8.9246212 C12.2175144,9.51040764 11.267767,9.51040764 10.6819805,8.9246212 L8.56066017,6.80330086 C7.97487373,6.21751442 7.97487373,5.26776695 8.56066017,4.68198052 Z" fill="#000000"/>
                        </g>
                     </svg>
                     <!--<i class="flaticon2-bell-alarm-symbol"></i>-->
                     <span class="kt-pulse__ring"></span>
                  </span>
                  <!--<span class="kt-badge kt-badge--light"></span>-->
               </div>
            </div>
            <!--end: Notifications -->
            <!--begin: User bar -->
            <div class="kt-header__topbar-item kt-header__topbar-item--user">
               @if(Auth::guest())
               <a href="{{route('login')}}" class="btn btn-default btn-flat">Login</a>
               @else
               <div class="kt-header__topbar-wrapper" data-toggle="dropdown" data-offset="10px,0px">
                  <span class="kt-header__topbar-welcome">Hi,</span>
                  <span class="kt-header__topbar-username">{{explode(' ',trim(Auth::User()->name))[0]}}</span>
                  <img alt="Pic" src="{{ asset('media/users/default.jpg')}}" class="kt-hidden"/>
               </div>
               <div class="dropdown-menu dropdown-menu-fit dropdown-menu-right dropdown-menu-anim dropdown-menu-xl">
                  <div class="kt-user-card kt-user-card--skin-dark kt-notification-item-padding-x" style="background-image: url(media/misc/bg-1.jpg)">
                     <div class="kt-user-card__avatar">
                        <img class="" alt="Pic" src="{{ asset('media/users/default.jpg')}}"/>
                        <span class="kt-badge kt-badge--lg kt-badge--rounded kt-badge--bold kt-font-success kt-hidden">S</span>
                     </div>
                     <div class="kt-user-card__name">{{Auth::User()->name}}
                        <br>
                        <span class="kt-badge kt-badge--brand kt-badge--inline">{{Auth::User()->getRoleName()}}</span>
                     </div>
                     <a href="{{route('logout')}}" class="btn btn-label btn-label-light btn-sm btn-bold" onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">Sign Out</a>
                     <form id="logout-form" action="{{route('logout')}}" method="POST" style="display: none;">
                        {{csrf_field()}}
                     </form>
                  </div>
                  @endif
                  <div class="kt-notification">
                     <a href="{{route('billing')}}" class="kt-notification__item">
                        <div class="kt-notification__item-icon">
                           <i class="fas fa-user kt-font-success"></i>
                        </div>
                        <div class="kt-notification__item-details">
                           <div class="kt-notification__item-title kt-font-bold">Billing</div>
                        </div>
                     </a>
                     <a href="{{route('users')}}" class="kt-notification__item">
                        <div class="kt-notification__item-icon">
                           <i class="fas fa-user kt-font-success"></i>
                        </div>
                        <div class="kt-notification__item-details">
                           <div class="kt-notification__item-title kt-font-bold">Users</div>
                        </div>
                     </a>
                     <a href="{{route('roles')}}" class="kt-notification__item">
                        <div class="kt-notification__item-icon">
                           <i class="fas fa-users kt-font-success"></i>
                        </div>
                        <div class="kt-notification__item-details">
                           <div class="kt-notification__item-title kt-font-bold">Roles</div>
                        </div>
                     </a>
                  </div>
                  <!--end: Navigation -->
               </div>
            </div>
            <!--end: User bar -->
         </div>
         <!-- end:: Header Topbar -->
      </div>
   </div>
   <style type="text/css">
      .btn.btn-label-light {
      color: #000 !important;
      }
      .kt-user-card.kt-user-card--skin-dark .kt-user-card__name {
      color: #000 !important;
      }
   </style>
