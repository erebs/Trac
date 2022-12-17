<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shop;
use App\Models\Fraud;
use App\Models\Transaction;
use App\Models\Subscription;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    //==--==--==--==-- User Register --==--==--==--==--==
    public function register(Request $request)
    {
        // Assigning Arguments to Variables 
        $name = $request->input('name');
        $mobile_number = $request->input('mobile_number');
        $member_id = $request->input('member_id');
        $password = $request->input('password');
        $constituency = $request->input('constituency');
        $pincode = $request->input('pincode');

        $isField = isset($name) && isset($mobile_number) && isset($member_id) && isset($password) && isset($pincode) && isset($constituency);

        // Check if any of the required fields are empty!
        if (!$isField) {
            echo "Some fields are required!";
            return;
        }
        if (strlen($pincode) != 6) {
            echo "Pincode must be 6 digits!";
            return;
        }

        if (strlen($password) < 8) {
            echo "password must be atleast 8 characters!";
            return;
        }

        // Check if details are already existed!
        $isMobile = User::where('mobile_number', $mobile_number)->count();
        $isMember = User::where('member_id', $member_id)->count();

        if ($isMobile != 0) {

            return Response()->json(["status" => false, "message" => "Mobile Number Already Exists!"]);
        }
        if ($isMember != 0) {

            return Response()->json(["status" => false, "message" => "Member Id Already Exists!"]);
        }

        $user = new User();
        $user->name = $name ?? '';
        $user->member_id = $member_id ?? '';
        $user->password = bcrypt($password) ?? '';
        $user->mobile_number = $mobile_number ?? '';
        $user->constituency = $constituency ?? '';
        $user->pincode = $pincode ?? '';
        $user->save();

        $data = [];
        $data['status'] = true;
        $data['message'] = 'Success';
        $data['user'] = $user;

        return Response()->json($data);
        return;
    }

    //==--==--==--==-- User Login --==--==--==--==--==
    public function userLogin(Request $request)
    {

        // Assigning Arguments to Variables
        $username = $request->input('username');
        $password = $request->input('password');

        // Checking if fields are empty
        $isField = isset($username) ?? isset($password);
        if (!$isField) {
            echo "Some fields are required!";
            return;
        }

        // Fetching User data from Database
        $user = User::where('mobile_number', $username)->get();


        // Checking if user exist in Database
        if (blank($user)) {
            echo "User not found!";
            return;
        }


        // Checking Hashed Password from database
        if (Hash::check($password, $user->password)) {

            return Response()->json(["status" => true, "message" => "Logged in successfully", "user" => $user]);
        } else {
            return Response()->json(["status" => false, "message" => "Incorrect username or password"]);
        }

        return;
    }

    //==--==--==--==-- Shop Register --==--==--==--==--==
    public function shopRegister (Request $request)
    {
        // Assigning Arguments to Variables 
        $shop_name = $request->input('shop_name');
        $user_id = $request->input('user_id');
        $mobile_number = $request->input('mobile_number');
        $gst = $request->input('gst');
        $shop_address = $request->input('shop_address');
        $status = $request->input('status');
        $constituency = $request->input('constituency');
        $pincode = $request->input('pincode');

        $isField = isset($shop_name) && isset($mobile_number) && ($pincode) && ($gst) && ($shop_address) && ($constituency) && ($user_id);

        // Check if any of the required fields are empty!
        if (!$isField) {
            echo "Some fields are required!";
            return;
        }
        if (strlen($pincode) != 6) {
            echo "Pincode must be 6 digits!";
            return;
        }
        if (strlen($mobile_number) < 10) {
            echo " Mobile Number must be atleast 10 digits!";
            return;
        }

        // Check if details are already existed!
        $isMobile = Shop::where('mobile_number', $mobile_number)->count();
        $isUser_id = Shop::where('user_id', $user_id)->count();

        if ($isMobile != 0) {

            return Response()->json(["status" => false, "message" => "Mobile Number Already Exists!"]);
        }

        if ($isUser_id != 0) {

            return Response()->json(["status" => false, "message" => "User Id Already Exists!"]);
        }

        $shop = new Shop();
        $shop->shop_name = $shop_name ?? '';
        $shop->user_id = $user_id ?? '';
        $shop->gst = $gst ?? '';
        $shop->mobile_number = $mobile_number ?? '';
        $shop->constituency = $constituency ?? '';
        $shop->pincode = $pincode ?? '';
        $shop->shop_address = $shop_address ?? '';
        $shop->status = $status ?? '';
        $shop->save();

        $data = [];
        $data['status'] = true;
        $data['message'] = 'Success';
        $data['shop'] = $shop;

        return Response()->json($data);
        return;
    }

    //==--==--==--==-- Shop Login --==--==--==--==--==

    public function shopLogin(Request $request)
    {
        $shop_username = $request->input('username');

        // Checking if fields are empty
        $isField = isset($shop_username) && isset($member_id);

        if (!$isField) {
            echo " Mobile number is required! ";
            return;
        }

        // Fetching shop data from Database
        $shop = Shop::where('mobile_number', $shop_username)->first();

        // Checking if user exist in Database
        if (blank($shop_username)) {
            echo " User not found!";
            return;
        }
    }

    //==--==--==--==-- Fraud Register --==--==--==--==--==

    public function fraudRegister(Request $request)
    {

        // Assigning Arguments to Variables 
        $shop_id = $request->input('shop_id');
        $name = $request->input('name');
        $mobile_number = $request->input('mobile_number');
        $address = $request->input('address');
        $type = $request->input('type');
        $proof_number = $request->input('proof_number');
        $profile_photo = $request->input('profile_photo');
        $description = $request->input('description');
        $approved_by = $request->input('approved_by');

        $isField = isset($shop_id) && isset($name) && isset($mobile_number) &&  isset($address)  &&  isset($type) && isset($proof_number)  && isset($profile_photo) && isset($description);

        // Check if any of the required fields are empty!
        if (!$isField) {
            echo "Some fields are required!";
            return;
        }

        if (strlen($mobile_number) < 10) {
            echo " Mobile Number must be atleast 10 digits!";
            return;
        }

        // Check if details are already existed!
        $isMobile = Fraud::where('mobile_number', $mobile_number)->count();
        $isProof = Fraud::where('proof_number', $proof_number)->count();

        if ($isMobile != 0) {
            return Response()->json(["status" => false, "message" => "Mobile Number Already Exists!"]);
        }
        if ($isProof != 0) {
            return Response()->json(["status" => false, "message" => "Proof Number Already Exists!"]);
        }

        $fraud = new Fraud();
        $fraud->name = $name ?? '';
        $fraud->shop_id = $shop_id ?? '';
        $fraud->mobile_number = $mobile_number ?? '';
        $fraud->address = $address ?? '';
        $fraud->type = $type ?? '';
        $fraud->proof_number = $proof_number ?? '';
        $fraud->description = $description ?? '';
        $fraud->approved_by = $approved_by ?? '';
        $fraud->profile_photo = $profile_photo ?? '';
        $fraud->save();


        $data = [];
        $data['status'] = true;
        $data['message'] = 'Success';
        $data['Fraud'] = $fraud;

        return Response()->json($data);
        return;
    }

    //==--==--==--==-- Fraud Search --==--==--==--==--==

    public function searchFraud(Request $request)

    {
        $query = $request->input('query');

        return Fraud::where('name', 'like', "%" . $query . "%")
            ->orWhere('mobile_number', 'like', '%' . $query . '%')->get();
    
        return;

        
    }

    //==--==--==--==-- Frauds By Shop --==--==--==--==--==

    public function fraudsByShop(Request $request)

    {
        $shop_id = $request->input('shop_id');
        return  Fraud::where('shop_id', $shop_id)->get();
    }

    //==--==--==--==-- Transaction Register --==--==--==--==--==

    public function transactionRegister(Request $request)

    {
        // Assigning Arguments to Variables 
        $name = $request->input('name');
        $transaction_id = $request->input('transaction_id');
        $dump = $request->input('dump');
        $shop_id = $request->input('shop_id');
        $subscription_id = $request->input('subscription_id');
        $status = $request->input('status');


        $isField = isset($name) && isset($transaction_id) && isset($dump) && isset($shop_id) && isset($subscription_id) && isset($status);

        // Check if any of the required fields are empty!
        if (!$isField) {
            echo "Some fields are required !";
            return;
        }

        // Check if details are already existed!
        $isSubscription = Transaction::where('subscription_id', $subscription_id)->count();
        $isTransaction = Transaction::where('transaction_id', $transaction_id)->count();


        // if ($isTransaction != 0) {

        //     return Response()->json(["status" => false, "message" => "Transaction Id Already Exists!"]);
        // }
        if ($isSubscription != 0) {

            return Response()->json(["status" => false, "message" => "Subscription Id Already Exists!"]);
        }

        $transaction = new Transaction();
        $transaction->name = $name ?? '';
        $transaction->transaction_id = $transaction_id ?? '';
        $transaction->dump = $dump ?? '';
        $transaction->shop_id = $shop_id ?? '';
        $transaction->subscription_id = $subscription_id ?? '';
        $transaction->status = $status ?? '';
        $transaction->save();

        $data = [];
        $data['status'] = true;
        $data['message'] = 'Success';
        $data['Transaction'] = $transaction;

        return Response()->json($data);
        return;
    }

    //==--==--==--==-- Transaction by subscription_id  --==--==--==--==--==

    public function getTransaction(Request $request)

    {
        $subscription_id = $request->input('subscription_id');
        return  Transaction::where('subscription_id', $subscription_id)->get();
    }

    //==--==--==--==-- Subscription Register  --==--==--==--==--==

    public function subscriptionRegister(Request $request)

    {
        // Assigning Arguments to Variables 
        $name = $request->input('name');
        $transaction_id = $request->input('transaction_id');
        $shop_id = $request->input('shop_id');
        $subscription_StartDate = $request->input('subscription_StartDate');
        $subscription_EndDate = $request->input('subscription_EndDate');
        $subscription_status = $request->input('subscription_status');


        $isField = isset($name) && isset($transaction_id) && isset($shop_id) && isset($subscription_StartDate) && isset($subscription_EndDate) && isset($subscription_status);

        // Check if any of the required fields are empty!
        if (!$isField) {
            echo "Some fields are required!";
            return;
        }

        // Check if details are already existed!

        $isTransaction = Subscription::where('transaction_id', $transaction_id)->count();
        // $isShop = Subscription::where('shop_id', $shop_id)->count();

        if ($isTransaction != 0) {

            return Response()->json(["status" => false, "message" => "Transaction Id Already Exists!"]);
        }
        // if ($isShop != 0) {

        //     return Response()->json(["status" => false, "message" => "Shop Id Already Exists!"]);
        // }

        $subscription = new Subscription();
        $subscription->name = $name ?? '';
        $subscription->transaction_id = $transaction_id ?? '';
        $subscription->shop_id = $shop_id ?? '';
        $subscription->subscription_StartDate = $subscription_StartDate ?? '';
        $subscription->subscription_EndDate = $subscription_EndDate ?? '';
        $subscription->subscription_status = $subscription_status ?? '';
        $subscription->save();

        $data = [];
        $data['status'] = true;
        $data['message'] = 'Success';
        $data['Subscription'] = $subscription;

        return Response()->json($data);
        return;
    }

    //==--==--==--==-- Subscription List  --==--==--==--==--==

    public function getSubscription(Request $request)

    {
        $shop_id = $request->input('shop_id');
        return  Subscription::where('shop_id', $shop_id)->get();
    }

}


