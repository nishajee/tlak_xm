<?php

namespace App\Http\Controllers\API2;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Access\AuthorizationException;
use App\User;
class VerificationApiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
     protected $redirectTo = '/dashboard';
    //protected $redirectTo = '/departure';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       //
    }
    /**
    * Mark the authenticated user’s email address as verified.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response
    */
    public function verify(Request $request) {

        if (!$request->hasValidSignature()) {
            return response()->json(["msg" => "Invalid/Expired url provided."], 401);
        }
    $userID = $request['id'];

    $user = User::findOrFail($userID);

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }
    return redirect()->to('/');
  
    }

        /**
        * Resend the email verification notification.
        *
        * @param \Illuminate\Http\Request $request
        * @return \Illuminate\Http\Response
        */
public function resend(Request $request)
{
if ($request->user()->hasVerifiedEmail()) {
return response()->json('User already have verified email!', 422);

}
$request->user()->sendEmailVerificationNotification();
return response()->json(["msg" => "Email verification link sent on your email id"]);

}
}
