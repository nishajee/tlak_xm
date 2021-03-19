<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});





//tlak apis routes
Route::group(['namespace'=>'API'],function(){
  

    Route::post('/login','ApiTravelerLoginController@login');
    Route::post('/logout','ApiTravelerLoginController@logoutTraveler');
    Route::post('/traveler','ApiTravelerLoginController@registerTraveler');
    Route::post('/profile','ApiTravelerProfileController@travelerProfile');
    Route::post('/updateprofile','ApiTravelerProfileController@updateTravelerProfile');
    Route::post('/itineary','ApiItinearyController@itineary');
    Route::post('/detailitineary/{id}','ApiItinearyController@detailItineary');
    Route::post('/poi','ApiPoiController@PointOfInterest');
    Route::post('/poilatlong','ApiPoiController@PointOfInterestLatLong');
    Route::post('/poidetail/{id}','ApiPoiController@detailPoi');
    Route::post('/poiimages/{id}','ApiPoiController@imagesPoi');
    Route::post('/flight','ApiFlightController@flight');
    Route::post('/flightdetail/{id}','ApiFlightController@flightDetails');
    Route::post('/flightstatus/{id}','ApiFlightController@flightStatus');
    Route::post('/flightdocuments/{id}','ApiFlightController@flightDocuments');
    Route::post('/hotel','ApiHotelController@Hotel');
    Route::post('/hoteldetail/{id}','ApiHotelController@hotelDetails');
    Route::post('/hoteldocuments/{id}','ApiHotelController@hotelDocuments');
    Route::post('/optional-departure','ApiOptionalDepartureController@optionalDeparture');
    Route::post('/travel-document','ApiDocumentController@travelDocument');
    Route::post('/travel-document-detail/{id}','ApiDocumentController@travelDocumentDetails');
    Route::post('/groupchange','ApiGroupChangeController@groupChange');
    Route::post('/menulabel','ApiMenuLabelController@menuLabel');
    Route::post('/support','ApiSupportController@contactSupport');
    Route::post('/inclusion','ApiInclusionController@Inclusion');
    Route::post('/weather','ApiWeatherController@weatherForecast');
    //Route::post('/itinearylocationpoi','ApiItinearyLocationController@itinearyLocation');
    // Route::post('/weather','ApiWeatherController@weatherForecast');
    Route::post('/optionalPoi','ApiOptionalPoiController@optionalPoi');
    Route::post('/poiOnMap','ApiPoiMapController@PointOfInterestMap');
    Route::post('/feedback','ApiFeedbackController@feedbackApp');
    Route::post('/placard','ApiSupportController@Placard');
    //Route::post('/checkCompanyId','ApiHomeController@checkCompanyId');
    
    Route::post('/chatboardgroupdata','ApiChatController@chatPeoplegroupList');
    Route::post('/storegroupdata','ApiChatController@postGroupData');
    Route::post('/getgroupdata','ApiChatController@getGroupData');
    Route::post('/storeindividualdata','ApiChatController@postIndividualData');
    Route::post('/getindividualdata','ApiChatController@getIndividualData');
    Route::post('/emergencyContacts','ApiEmergencyContactController@emergencyContact');
    Route::post('/tlakfeedbak','ApiTlakContactFeedbackController@tlakFeedbackApp');
    Route::post('/tlakcontact','ApiTlakContactFeedbackController@tlakContactApp');
    Route::post('/sos','ApiSOSController@sosApp');
    Route::post('/checkCompanyId','ApiHomeController@checkCompanyId');
    Route::post('/occupantidupdate','ApiTravelerLoginController@occupantIdUpdate');
    Route::post('/countryguide','ApiCountryGuideController@countryGuide');
    Route::post('/country-pax-credit','ApiGetCountryPaxCreditController@getCountryPaxCredit');


    

    
    });
    
    Route::group(['prefix'=>'v2','namespace'=>'API2'],function(){
  

    //Route::post('/login','ApiTravelerLoginController@login');
    Route::post('/avatar','ApiTravelerLoginController@Avatar');
    Route::get('/commanPasscode','ApiTravelerLoginController@commanPasscode');
    Route::post('/comman','ApiCommanController@comman');
    Route::post('/banner','ApiCommanController@banner');
    Route::post('/travelerList','ApiCommanController@travelerList');
    Route::post('/allDocument','ApiDocumentController@allDocument');
    Route::post('/logout','ApiTravelerLoginController@logoutTraveler');
    Route::post('/traveler','ApiTravelerLoginController@registerTraveler');
    Route::post('/profile','ApiTravelerProfileController@travelerProfile');
    Route::post('/updateprofile','ApiTravelerProfileController@updateTravelerProfile');
    Route::post('/itineary','ApiItinearyController@itineary');
    Route::post('/detailItineary/{id}','ApiItinearyController@detailItineary');
    Route::post('/itinearyList','ApiItinearyController@itinearyList');
    Route::post('/ongoingtrip','ApiItinearyController@onGoingtrip');
    Route::post('/poi','ApiPoiController@PointOfInterest');
    Route::post('/poiList','ApiPoiController@PointOfInterestList');
    Route::post('/topAttraction','ApiPoiController@topAttraction');
    Route::post('/poilatlong','ApiPoiController@PointOfInterestLatLong');
    Route::post('/poidetail/{id}','ApiPoiController@detailPoi');
    Route::post('/poiimages/{id}','ApiPoiController@imagesPoi');
    Route::post('/flight','ApiFlightController@flight');
    Route::post('/flightdetail/{id}','ApiFlightController@flightDetails');
    Route::post('/flightstatus/{id}','ApiFlightController@flightStatus');
    Route::post('/flightdocuments/{id}','ApiFlightController@flightDocuments');
    Route::post('/transports','ApiFlightController@transports');
    Route::post('/hotel','ApiHotelController@Hotel');
    Route::post('/hoteldetail/{id}','ApiHotelController@hotelDetails');
    Route::post('/hotelList','ApiHotelController@hotelList');
    Route::post('/restaurents','ApiHotelController@Restaurents');
    Route::post('/shoppings','ApiHotelController@Shoppings');
    Route::post('/hoteldocuments/{id}','ApiHotelController@hotelDocuments');
    Route::post('/restaurents','ApiHotelController@restaurents');
    Route::post('/optional-departure','ApiOptionalDepartureController@optionalDeparture');
    Route::post('/travel-document','ApiDocumentController@travelDocument');
    Route::post('/travel-document-detail/{id}','ApiDocumentController@travelDocumentDetails');
    Route::post('/groupchange','ApiGroupChangeController@groupChange');
    Route::post('/menulabel','ApiMenuLabelController@menuLabel');
    Route::post('/support','ApiSupportController@contactSupport');
    Route::post('/inclusion','ApiInclusionController@Inclusion');
    Route::post('/exclusion','ApiInclusionController@Exclusion');
    Route::post('/weather','ApiWeatherController@weatherForecast');
    //Route::post('/itinearylocationpoi','ApiItinearyLocationController@itinearyLocation');
    // Route::post('/weather','ApiWeatherController@weatherForecast');
    Route::post('/optionalPoi','ApiOptionalPoiController@optionalPoi');
    Route::post('/poiOnMap','ApiPoiMapController@PointOfInterestMap');
    Route::post('/feedback','ApiFeedbackController@feedbackApp');
    Route::post('/tlaksupport','ApiFeedbackController@tlakSupport');
    Route::post('/placard','ApiSupportController@Placard');
    //Route::post('/checkCompanyId','ApiHomeController@checkCompanyId');
    
    Route::post('/chatboardgroupdata','ApiChatController@chatPeoplegroupList');
    Route::post('/storegroupdata','ApiChatController@postGroupData');
    Route::post('/getgroupdata','ApiChatController@getGroupData');
    Route::post('/storeindividualdata','ApiChatController@postIndividualData');
    Route::post('/getindividualdata','ApiChatController@getIndividualData');
    Route::post('/emergencyContacts','ApiEmergencyContactController@emergencyContact');
    Route::post('/tlakfeedbak','ApiTlakContactFeedbackController@tlakFeedbackApp');
    Route::post('/tlakcontact','ApiTlakContactFeedbackController@tlakContactApp');
    Route::post('/sos','ApiSOSController@sosApp');
    Route::post('/all_alarm','ApiSOSController@allAlarm');
    Route::post('/add_alarm','ApiSOSController@addAlarm');
    Route::post('/alarm_status','ApiSOSController@alarmStatus');
    Route::post('/checkCompanyId','ApiHomeController@checkCompanyId');
    Route::post('/occupantidupdate','ApiTravelerLoginController@occupantIdUpdate');
    Route::post('/countryguide','ApiCountryGuideController@countryGuide');
    Route::post('/events','ApiEventController@events');
    Route::post('/placetovisit','ApiPlaceToVisitController@placeToVisit');
    Route::post('/setContribution','ApiFeedController@setContribution');
    Route::post('/getContribution','ApiFeedController@getContribution');
    Route::post('/getFeed','ApiFeedController@getFeed');
    Route::post('/feedActivity','ApiFeedController@feedActivity');
    Route::post('/getComment','ApiFeedController@getComment');
    Route::post('/mapDetail','ApiMapDetailController@mapDetail');
    Route::get('/versionControl','ApiCommanController@versionControl');
    Route::post('/real_time_data','ApiCommanController@realTimeData');
    Route::post('/version_code','ApiCommanController@versionCode');

   
    });
     //web.php routes 
   
     Route::group(['prefix'=>'v7','namespace'=>'API2'],function(){
        //passport login ****** nisha************
        Route::post('login', 'PassportUserController@login');
        Route::post('register', 'PassportUserController@register');
        Route::post('refreshtoken', 'PassportUserController@refreshToken');
        Route::get('email/verify/{id}', 'VerificationApiController@verify')->name('verificationapi.verify');
        Route::get('email/resend', 'VerificationApiController@resend')->name('verificationapi.resend');
       Route::post('forgotPassword','PassportUserController@forgotPassword');
        // ****** nisha************  
    });