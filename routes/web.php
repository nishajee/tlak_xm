<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['verify' => true]);

Route::get('/home', 'HomeController@index')->name('home');

//tlak Routes

Route::get('/', function (){
    return view('auth.login');
  });
  Route::get('/verifyandresetpassword/{token}', 'UserController@verifyAndResetPassword');
  Route::post('/verifyuser', 'UserController@verifyUser')->name('verify_user');
  
 
  Route::group(['middleware' => 'auth'], function () {
  Route::get('/dashboard', 'HomeController@index')->name('home');
  Route::post('/send_feedback', 'HomeController@send_feedback');
  //Tour PKG Route
      Route::get('departure','TourPackageController@indexTourPackage')->name('indexTour');
      Route::get('departure/create','TourPackageController@createTourPackage')->name('createTour');
      Route::post('departure/store','TourPackageController@storeTourPackage')->name('storeTour');
      Route::get('departure/{id}/edit','TourPackageController@editTourPackage')->name('editTour');
      Route::patch('departure/{id}/update','TourPackageController@updateTourPackage')->name('updateTour');
      Route::delete('departure/{id}','TourPackageController@deleteTourPackage')->name('deleteTour');
      Route::post('departure-disable/{id}','TourPackageController@disableDeparture')->name('disable_departure');
  
  //Location Tour PKG Route
      
      Route::get('location-poi/create/{id}','LocationPointOfInterestController@createLocation')->name('add_location');
      Route::post('location-poi/store/{id}','LocationPointOfInterestController@storeLocation')->name('store_location');
      Route::get('get-destination-location-ajax', 'LocationPointOfInterestController@getDestinationsAjax');
      Route::get('get-poi-ajax', 'LocationPointOfInterestController@getPoiAjax');
      Route::get('get-country-location-ajax', 'LocationPointOfInterestController@getCountryAjax');
  //Itineary Tour PKG Route
      
      //Route::get('itinerary/{id}','ItinearyController@indexItineary')->name('itineary');
      Route::get('itinerary/create/{id}','ItinearyController@createItineary')->name('add_itineary');
      Route::post('itinerary/store/{id}','ItinearyController@storeItineary')->name('store_itineary');
      Route::get('itinerary/{id}/edit/Pid/{tour_package_id}','ItinearyController@editItineary')->name('edit_itineary');
      // Route::patch('itinerary/{id}/update/Pid/{tour_package_id}','ItinearyController@updateItineary')->name('update_itineary');
      Route::post('itinerary/update/{id}','ItinearyController@updateItineary')->name('update_itineary');
      Route::delete('itinerary-delete/{id}','ItinearyController@deleteItineary')->name('delete_itineary');
  // Inclusion
     Route::get('inclusion/create/{id}','TourPackageController@createInclusion')->name('add_inclusion');
     Route::post('inclusion/store/{id}','TourPackageController@storeInclusion')->name('store_inclusion');
     Route::post('exclusion/store/{id}','TourPackageController@storeExclusion')->name('store_exclusion');
     Route::get('inclusion/{id}/edit','TourPackageController@editInclusion')->name('edit_inclusion');
     Route::post('inclusion/{id}/update','TourPackageController@updateInclusion')->name('update_inclusion');
     Route::post('exclusion/{id}/update','TourPackageController@updateExclusion')->name('update_exclusion');
  //People Tour PKG Route
      Route::get('people/create/{id}','PeopleController@createPeople')->name('add_people');
      Route::post('people/store/{id}','PeopleController@storePeople')->name('store_people');
      Route::delete('people-delete/{id}','PeopleController@deletePeople')->name('delete_people');
      Route::patch('people-update/{id}','PeopleController@updatePeople')->name('edit_people');
      Route::post('people-store-single/{id}','PeopleController@storePeopleSingle')->name('store_people_single');
      Route::post('get-details-people/{id}','PeopleController@getDetailsPeople')->name('get_details_people');
      Route::post('activate-traveler','PeopleController@activateTraveler')->name('activate_traveler');
      Route::post('deactivate-traveler/{id}','PeopleController@deactivateTraveler')->name('deactivate_traveler');
  
      Route::get('document-creation/{id}','DocumentAndCreationController@DocumentAndCreation')->name('document_creation');
      Route::post('travel-document/store/{id}','DocumentAndCreationController@storeTravelDocument')->name('store_travel_document');
      Route::patch('document-update/{id}','DocumentAndCreationController@updateTravelDocument')->name('update_travel_document');
      Route::delete('document-delete/{id}','DocumentAndCreationController@deleteTravelDocument')->name('delete_tdocuments');
      Route::post('pdf-creation/{id}','DocumentAndCreationController@pdfTravelDocument')->name('pdf_creation_store');
      Route::post('pdf-preview/{id}','DocumentAndCreationController@pdfPreview')->name('pdf_preview');
  
      Route::get('/download/{file_name}', function($file_name = null)
      {
          $path = storage_path().'/'.'app'.'/public/documents/pdf/itinerary/'.$file_name;
          if (file_exists($path)) {
              return Response::download($path);
          }
      });
        Route::post('pdf-delete/{id}','DocumentAndCreationController@delete_itinerary_pdf');
        //People Tour PKG Route
      Route::get('flights','FlightController@indexFlight')->name('flight');
      Route::get('flight/create/{id}','FlightController@createFlight')->name('add_flight');
      Route::post('flight/store/{id}','FlightController@storeFlight')->name('store_flight');
      Route::post('flight/update/{id}','FlightController@updateFlight')->name('update_flight');
      Route::delete('flight-delete/{id}','FlightController@deleteFlight')->name('delete_flight');
      Route::post('flight-search/{id}','FlightController@searchFlight')->name('search_flight');
      Route::get('get-people-ajax-edit-flight', 'FlightController@getPeopleAjaxEdit');
  
  //Hotel Tour PKG Route
      //Route::get('hotels','hotelController@indexHotel')->name('hotel');
      Route::get('hotel/{id}','HotelController@createHotel')->name('add_hotel');
      Route::post('hotel/store/{id}','HotelController@storeHotel')->name('store_hotel');
      Route::post('hotel/update/{id}','HotelController@updateHotel')->name('update_hotel');
      Route::delete('hotel-delete/{id}','HotelController@deleteHotel')->name('delete_hotel');	
  //terms and conditions
      Route::get('termandconditions/{id}','TermAndConditionController@index')->name('termandconditions_index');	
      Route::post('terms/update/{id}','TermAndConditionController@updateTerm')->name('terms_update');	
  //SOS
      Route::get('communication/{id}','CommunicationController@createCommunication')->name('createCommunication');
      Route::post('communication-manager/store/{id}','CommunicationController@storeCommunicationManager')->name('storeCommunicationManager');
      Route::post('communication-guide/store/{id}','CommunicationController@storeCommunicationGuide')->name('storeCommunicationGuide');
      Route::post('communication-scontact/store/{id}','CommunicationController@storeCommunicationScontact')->name('storeCommunicationScontact');
      Route::post('communication-placard/store/{id}','CommunicationController@storeCommunicationPlacard')->name('storeCommunicationPlacard');
  
      Route::patch('communication-update/{id}','CommunicationController@updateCommunication');
      Route::patch('departure-mng-update/{id}','CommunicationController@updateDepManager');
      Route::patch('departure-guide-update/{id}','CommunicationController@updateDepGuide');
      Route::patch('placard-update/{id}','CommunicationController@updatePlacard');
      //Delete
      Route::delete('communication-delete/{id}','CommunicationController@deleteCommunication');
      Route::delete('departure-mng-delete/{id}','CommunicationController@deleteDepManager');
      Route::delete('departure-guide-delete/{id}','CommunicationController@deleteDepGuide');
      Route::delete('placard-delete/{id}','CommunicationController@deletePlacard');
      Route::post('get-details-guide/{id}','CommunicationController@getDetailsGuide')->name('get_details_guide');
      Route::post('get-details-manager/{id}','CommunicationController@getDetailsManager')->name('get_details_manager');
      Route::post('activate-guide','CommunicationController@activateGuide')->name('activate_guide');
      Route::post('deactivate-guide/{id}','CommunicationController@deactivateGuide')->name('deactivate_guide');
      Route::post('activate-manager','CommunicationController@activateManager')->name('activate_manager');
      Route::post('deactivate-manager/{id}','CommunicationController@deactivateManager')->name('deactivate_manager');
  
  //Route::resource('day-wise-itineary','ItinearyController');
  // Route::resource('hotel','HotelController');
  Route::resource('optional-departure','UpcommingTourPackageController');
  Route::delete('destroyUcomingTours/{id}', 'UpcommingTourPackageController@destroyUcomingTour');
  Route::resource('poi','PointOfInterestController');
  Route::get('poi-edit/{id}/edit','PointOfInterestController@edit')->name('edit_poi');
  Route::patch('poiupdate/update/{id}','PointOfInterestController@update')->name('update_poi');
  Route::delete('poi-delete/{id}','PointOfInterestController@deletePoi')->name('delete_poi');
  Route::resource('poi-icon','PointOfInterestIconController');
  Route::resource('document-icon','DocumentIconController');
  
  Route::get('settings','SettingController@index')->name('company_info');
  Route::get('settings/{id}/edit','SettingController@companyInfoEdit')->name('company_info_edit');
  Route::patch('settings/{id}/update','SettingController@companyInfoUpdate')->name('company_info_update');
  Route::get('settings/logo/{id}/edit','SettingController@settingLogoEdit')->name('company_logo_edit');
  Route::patch('settings/logo/{id}/update','SettingController@settingLogoUpdate')->name('company_logo_update');
  Route::get('settings/email-password/{id}/edit','SettingController@settingsEmailPasswordEdit')->name('company_emailpwd_edit');
  Route::patch('settings/email-password/{id}/update','SettingController@settingsEmailPasswordUpdate')->name('company_emailpwd_update');
  Route::get('company-creation/{id}/edit','SettingController@settingsHeaderFooterEdit')->name('edit_company');
  Route::patch('company-creation/{id}/edit','SettingController@settingsHeaderFooterUpdate')->name('update_company');
  
  Route::get('menu-labels/create','MenuLabelController@create')->name('create_label');
  Route::post('menu-labels/store','MenuLabelController@store')->name('store_label');
  Route::get('menu-labels/edit','MenuLabelController@edit')->name('edit_label');
  Route::patch('menu-labels/update','MenuLabelController@update')->name('update_label');
  Route::get('menu-labels-icons','MenuLabelController@createLabelIcons');
  Route::post('menu-labels-icons','MenuLabelController@storeLabelIcons')->name('menu-labels-icons');
  
  //Route::get('get-poi-ajax', 'PointOfInterestController@getPoiDetails');
  
  //Ajax Routes
  Route::get('get-destination-ajax','LocationPointOfInterestController@getDestinationAjax');
  Route::get('get-location-ajax','LocationPointOfInterestController@getPois');
  Route::get('get-pois-ajax','LocationPointOfInterestController@getPoisAjax');
  Route::get('get-destination-itneary-ajax', 'ItinearyController@getDestinationsAjax')->name('loca');
  Route::patch('location-update/{id}','PointOfInterestController@locationUpdate');
  
  Route::get('get-location-poi-ajax','ItinearyController@getLocationPoiAjax');
  Route::get('get-hotel-itneary-ajax', 'ItinearyController@getHotelAjax');
  Route::get('get-people-ajax', 'HotelController@getPeopleAjax');
  Route::get('get-people-ajax-edit', 'HotelController@getPeopleAjaxEdit');
  Route::get('get-socket-ajax','HotelController@getSocketAjax');
  
  Route::get('changeStatusl', 'LocationPointOfInterestController@changeStatus');
  Route::get('deaprtureStatus', 'TourPackageController@changeStatus');
  Route::get('upcommingTourStatus', 'UpcommingTourPackageController@changeStatus');
  Route::get('get-country-list', 'TourPackageController@getCountryList');
  
  Route::get('/notifications/{id}','NotificationController@index')->name('notification');
  Route::post('add_scheduled_noticefication/{id}','NotificationController@addScheduledNotifications')->name('add_scheduled_notification');
  Route::delete('schedule-notification-delete/{id}','NotificationController@deleteScheduleNotification')->name('delete_schedule_notification');
  
  Route::post('add_instant_noticefication/{id}','NotificationController@addInstantNotifications')->name('add_instant_notification');
  Route::delete('instant-notification-delete/{id}','NotificationController@deleteInstantNotification')->name('delete_instant_notification');
  
  Route::post('add_location_noticefication/{id}','NotificationController@addLocationNotifications')->name('add_location_notification');
  Route::delete('location-notification-delete/{id}','NotificationController@deletelocationNotification')->name('delete_location_notification');
  
  Route::get('/roles', 'RoleController@index')->name('roles');
  Route::get('/roles/page', 'RoleController@page')->name('roles_page');
  Route::post('/add_role', 'RoleController@store')->name('add_role');
  Route::delete('role-delete/{id}','RoleController@deleteRole')->name('delete_role');
  Route::post('role-update/{id}','RoleController@update')->name('update_role');
  
  Route::get('/users', 'UserController@index')->name('users');
  Route::get('/users/create','UserController@createUser')->name('create_user');
  Route::post('add_user','UserController@addUser')->name('add_user');
  Route::post('activate-user/{id}','UserController@activateUser')->name('activate_user');
  Route::post('inactivate-user/{id}','UserController@inactivateUser')->name('inactivate_user');
  Route::get('/users/edit/{id}','UserController@editUser')->name('edit_user');
  Route::post('update_user/{id}','UserController@updateUser')->name('update_user');
  Route::post('resend-email/{id}','UserController@resendEmail')->name('resend_email');
  
  Route::get('/billing', 'BillingController@index')->name('billing');
  Route::get('/departure_biling/{id}', 'BillingController@departure_biling')->name('departure_biling');
  Route::post('/activate_departure', 'BillingController@activateDeparture')->name('activate_departure');
  Route::get('/add_credit', 'BillingController@addCredit')->name('add_credit');
  Route::post('/pay_credit', 'BillingController@payCredit')->name('pay_credit');
  Route::post('/payment_response', 'BillingController@paymentResponse')->name('payment_response');
  Route::get('/thank-you/{id}', 'BillingController@thank_you')->name('thank-you');
  Route::get('/payment-details', 'BillingController@paymentDetails')->name('payment-details');
  Route::get('/invoice/{id}', 'BillingController@invoice')->name('invoice');
  Route::get('get-utc','ItinearyController@getLocationUTC');
  Route::post('location-added','PointOfInterestController@addLocationPoi')->name('location_added');
  Route::post('departure-copy/{id}','TourPackageController@copyDeparture')->name('departure_copy');
  
  // Inclusion
  Route::get('upcoming-tours/create/{id}','UpcommingTourPackageController@createDepUpcoming')->name('add_dep_upcoming');
  Route::post('upcoming-tours/store/{id}','UpcommingTourPackageController@storeDepUpcoming')->name('store_dep_upcoming');
  Route::get('upcoming-tours/{id}/edit','UpcommingTourPackageController@editDepUpcoming')->name('edit_dep_upcoming');
  Route::post('upcoming-tours/{id}/update','UpcommingTourPackageController@updateDepUpcoming')->name('update_dep_upcoming');
  
  //dashboard instant notification
  Route::post('/dashboard-instant-notification', 'HomeController@instantNotification')->name('dashboard_instant_notification');
  Route::get('/dashboard-current-departure-ajax', 'HomeController@currentDeparture');
  
  // dashboard real timr tracking
  Route::post('/getLatLong/{id}', 'HomeController@getRealTimeTrackingData')->name('getLatLong');
  Route::post('/no_of_hits/{id}', 'HomeController@noOfHits')->name('no_of_hits');    
      
  Route::get('app-feedback/{id}','UpcommingTourPackageController@appFeedBack')->name('appFeedback');
  Route::get('/preview/{file_name}', function($file_name = null)
      {
          $path = storage_path().'/'.'app'.'/public/documents/pdf/itinerary/'.$file_name;
          if (file_exists($path)) {
              return Response::file($path);
          }
      });
  Route::post('position-reshifting/{id}','ItinearyController@positionShifting')->name('positionReshifting');
  Route::get('/web-version/{file_name}', function($file_name = null)
      {
          $path = storage_path().'/'.'app'.'/public/documents/pdf/itinerary/'.$file_name;
          if (file_exists($path)) {
              return Response::file($path);
          }
      });
  Route::get('dep-upcomming-status','UpcommingTourPackageController@depUpChangeStatus');
  Route::post('image-crop-poi-edit','PointOfInterestController@cropImageEditPage');
  
  Route::get('watpoi-search-onmap-ajax','PointOfInterestController@poiSearchOpenDb');
  Route::get('poi-image-ajax','PointOfInterestController@getWatPoiImages');
  Route::get('watpoi-random-ajax','PointOfInterestController@getRelatedWatPoi');
  Route::get('poi-random-tom-tom-ajax','PointOfInterestController@getRelatedWatPoiTomTomFun');
  Route::post('image-crop','PointOfInterestController@cropImage');
  Route::post('edit-image-crop','PointOfInterestController@editCropImage');
  Route::post('temp-poi-add','PointOfInterestController@tempPointOfInterest')->name('temp_poi');
  Route::post('temp-poi/update/{id}','PointOfInterestController@tempPointOfInterestUpdate');
  Route::post('delete-temp-poi/{id}','PointOfInterestController@tempPointOfInterestDelete');
  Route::post('submit-final-temp-pois','PointOfInterestController@finalPoiSubmitButtonPois')->name('final_poi_submit');
  Route::get('watpoi','poiWatController@createWat');
  Route::post('watpoi-store','poiWatController@storewat')->name('watpoi_store');
  Route::post('old-poi-image-crop','poiWatController@cropOldPoiImage');
  Route::post('image-crop-logo','SettingController@cropImageLogo');
  Route::get('departure-setting/{id}','ApiDepartureSettingController@apiDepartureSetting')->name('api_dep_setting');
  Route::post('country-guide/store/{id}','ApiDepartureSettingController@countryGuideIso')->name('store_iso');
  // Paypal Route
  Route::get('/product', function () {
      return view('product');
  });
  Route::post('handle-payment', 'PayPalPaymentController@handlePayment')->name('make.payment');
  Route::get('cancel-payment', 'PayPalPaymentController@paymentCancel')->name('cancel.payment');
  Route::get('payment-success', 'PayPalPaymentController@paymentSuccess')->name('success.payment');
  Route::get('transfer-poi', 'DataTransferController@transferPoi')->name('transfer-poi');
  });
  
