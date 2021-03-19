<ul class="kt-nav kt-nav--bold kt-nav--md-space kt-nav--v4" id="setting-tabs">
    <li class="kt-nav__item active">
        <a class="kt-nav__link" href="{{route('company_info')}}">
            <span class="kt-nav__link-text Overview">Company Overview</span>
        </a>
    </li>
    <li class="kt-nav__item">
        <a class="kt-nav__link" href="{{route('company_info_edit',Auth::User())}}">
            <span class="kt-nav__link-text Company">Company</span>
        </a>
    </li>
    <li class="kt-nav__item">
        <a class="kt-nav__link" href="{{route('company_logo_edit',Auth::User())}}">
            <span class="kt-nav__link-text Logo">Logo</span>
        </a>
    </li>
    <li class="kt-nav__item">
        <a class="kt-nav__link" href="{{route('edit_company',Auth::User())}}">
            <span class="kt-nav__link-text Creations">Creations</span>
        </a>
    </li>
    <li class="kt-nav__item">
        <a class="kt-nav__link" href="{{route('create_label')}}">
            <span class="kt-nav__link-text Menu-Labels">Menu and Labels</span>
        </a>
    </li>
    <li class="kt-nav__item">
        <a class="kt-nav__link" href="{{route('company_emailpwd_edit',Auth::User())}}">
            <span class="kt-nav__link-text Email-Password">Password</span>
        </a>
    </li>
</ul>
