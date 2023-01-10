<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shop;
use App\Models\Fraud;
use App\Models\Transaction;
use App\Models\Subscription;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
	//==--==--==--==-- User Register --==--==--==--==--==

	public function register(Request $request)
	{
		// Assigning Arguments to variables 
		$name = $request->input('name');
		$mobile_number = $request->input('mobile_number');
		$member_id = $request->input('member_id');
		$password = $request->input('password');
		$constituency = $request->input('constituency');
		$pincode = $request->input('pincode');

		$isField = isset($name) && isset($mobile_number) && isset($member_id) && isset($password) && isset($pincode) && isset($constituency);

		// Check if any of the required fields are empty!
		if (!$isField) {
			return Response()->json(["status" => false, "message" => "Some Fields are Required"]);
		}

		if (strlen($pincode) != 6) {
			return Response()->json(["status" => false, "message" => "Pincode must be 6 Digits"]);
		}

		if (strlen($password) < 8) {
			return Response()->json(["status" => false, "message" => "Password must be atleast 8 Characters"]);
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
		$user->name = $name;
		$user->member_id = $member_id;
		$user->password = bcrypt($password);
		$user->mobile_number = $mobile_number;
		$user->constituency = $constituency;
		$user->pincode = $pincode;
		$user->save();

		$data = [];
		$data['status'] = true;
		$data['message'] = 'Success';
		$data['user'] = $user;

		return Response()->json($data);
	}

	//==--==--==--==-- User Login --==--==--==--==--==
	public function userLogin(Request $request)
	{

		// Assigning Arguments to Variables
		$username = $request->input('username');
		$password = $request->input('password');

		// Checking if fields are empty
		$isField = isset($username) && isset($password);
		if (!$isField) {
			return Response()->json(["status" => false, "message" => "Some Fields are Required"]);
		}

		// Fetching User data from Database
		$user = User::where('mobile_number', $username)->orWhere('member_id', $username)->first();

		// Checking if user exist in Database
		if (blank($user)) {

			return Response()->json(["status" => false, "message" => "Incorrect username or password"]);
		}

		// Checking Hashed Password from database
		if (Hash::check($password, $user->password)) {

			// Get User first shop
			$shop = Shop::where('user_id', $user->id)->first();

			// Check if User has any Shop
			if (blank($shop)) {
				return Response()->json(["status" => false, "message" => "Shop is required to Login"]);
			}

			return Response()->json(["status" => true, "message" => "Logged in successfully", "user" => $user, "shop" => $shop]);
		} else {
			return Response()->json(["status" => false, "message" => "Incorrect username or password"]);
		}
	}

	//==--==--==--==-- Shop Register --==--==--==--==--==

	public function shopRegister(Request $request)
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

		$isField = isset($shop_name) && isset($mobile_number) && ($pincode) && ($gst)  && ($constituency) && ($user_id);

		// Check if any of the required fields are empty!
		if (!$isField) {
			return Response()->json(["status" => false, "message" => "Some fields are required"]);
		}
		if (strlen($pincode) != 6) {
			return Response()->json(["status" => false, "message" => "Pincode should be 6 digits"]);
		}
		if (strlen($mobile_number) != 10) {
			return Response()->json(["status" => false, "message" => "Mobile Number should be atleast 10 digits"]);
		}

		// Check if details are already existed!
		$isMobile = Shop::where('mobile_number', $mobile_number)->count();
		$isUserId = User::where('id', $user_id)->count();

		if ($isMobile != 0) {

			return Response()->json(["status" => false, "message" => "Mobile Number already exist!"]);
		}

		if ($isUserId == 0) {

			return Response()->json(["status" => false, "message" => "User Id does not exist!"]);
		}

		$shop = new Shop();
		$shop->shop_name = $shop_name ?? '';
		$shop->user_id = $user_id ?? '';
		$shop->gst = $gst ?? '';
		$shop->mobile_number = $mobile_number ?? '';
		$shop->constituency = $constituency ?? '';
		$shop->pincode = $pincode ?? '';
		$shop->shop_address = $shop_address;
		$shop->status = $status ?? 'Pending';
		$shop->save();

		$data = [];
		$data['status'] = true;
		$data['message'] = 'Success';
		$data['shop'] = $shop;

		return Response()->json($data);
	}

	//==--==--==--==-- Shop Login --==--==--==--==--==

	public function shopLogin(Request $request)
	{
		$mobile_number = $request->input('mobile_number');

		// Checking if fields are empty
		$isField = isset($mobile_number);

		if (!$isField) {
			return Response()->json(["status" => false, "message" => "Mobile Number is required"]);
		}

		// Fetching shop data from Database
		$shop = Shop::where('mobile_number', $mobile_number)->first();

		// Checking if user exist in Database
		if (blank($shop)) {

			return Response()->json(["status" => false, "message" => "Shop  Mobile Number  does not exist!"]);
		} else {

			return Response()->json(["status" => true, "message" => "Logged in successfully", "shop" => $shop]);
		}
	}

	//==--==--==--===-- Shops by User ==--==--==--==--

	public function shopsByUser(Request $request)
	{
		$user_id = $request->input('user_id');


		$validator = Validator::make($request->all(), ['user_id' => 'required']);

		if ($validator->fails()) {

			return Response()->json(["status" => false, "message" => 'user_id is required']);
		}

		$shops = Shop::where('user_id', $user_id)->get();

		return Response()->json(["status" => true, "message" => 'success', "shops" => $shops]);
	}

	//==--==--==--==-- Fraud Create --==--==--==--==--==

	public function fraudCreate(Request $request)
	{

		// Assigning Arguments to Variables 
		$shop_id = $request->input('shop_id');
		$name = $request->input('name');
		$mobile_number = $request->input('mobile_number');
		$address = $request->input('address');
		$proof_type = $request->input('proof_type');
		$proof_number = $request->input('proof_number');
		$profile_photo = $request->input('profile_photo');
		$description = $request->input('description');
		$approved_by = $request->input('approved_by');

		$isField = isset($shop_id) && isset($name) && isset($mobile_number) &&  isset($address)  &&  isset($proof_type) && isset($proof_number)  && isset($profile_photo) && isset($description);

		// Check if any of the required fields are empty!
		if (!$isField) {
			return Response()->json(["status" => false, "message" => "Some fields are required!"]);
		}

		if (strlen($mobile_number) != 10) {

			return Response()->json(["status" => false, "message" => "Mobile Number should be atleast 10 Digits"]);
		}


		// Check if details are already existed!
		$isMobile = Fraud::where('mobile_number', $mobile_number)->count();
		$isProof = Fraud::where('proof_number', $proof_number)->count();
		$isShopId = Shop::where('id', $shop_id)->count();

		if ($isShopId == 0) {
			return Response()->json(["status" => false, "message" => "Shop Id does not exist!"]);
		}

		if ($isMobile != 0) {
			return Response()->json(["status" => false, "message" => "Mobile Number already exist!"]);
		}
		if ($isProof != 0) {
			return Response()->json(["status" => false, "message" => "Proof Number already exist!"]);
		}

		$fraud = new Fraud();
		$fraud->shop_id = $shop_id;
		$fraud->name = $name;
		$fraud->mobile_number = $mobile_number;
		$fraud->address = $address;
		$fraud->profile_photo = $profile_photo;
		$fraud->proof_type = $proof_type;
		$fraud->proof_number = $proof_number;
		$fraud->description = $description;
		$fraud->save();


		$data = [];
		$data['status'] = true;
		$data['message'] = 'Success';
		$data['fraud'] = $fraud;

		return Response()->json($data);
	}

	//==--==--==--==-- Fraud Search --==--==--==--==--==

	public function searchFraud(Request $request)

	{
		$query = $request->input('query');

		return Fraud::where('name', 'like', "%" . $query . "%")
			->orWhere('mobile_number', 'like', '%' . $query . '%')->get();
	}

	//==--==--==--==-- Frauds By Shop --==--==--==--==--==

	public function fraudsByShop(Request $request)

	{
		$shop_id = $request->input('shop_id');
		return  Fraud::where('shop_id', $shop_id)->get();
	}

	//==--==--==--==-- Transaction Register --==--==--==--==

	public function transactionRegister(Request $request)

	{
		// Assigning Arguments to Variables 
		$shop_id = $request->input('shop_id');
		$name = $request->input('name');
		$transaction_id = $request->input('transaction_id');
		$dump = $request->input('dump');
		$shop_id = $request->input('shop_id');
		$subscription_id = $request->input('subscription_id');
		$status = $request->input('status');


		$isField = isset($name) && isset($transaction_id) && isset($dump) && isset($shop_id) && isset($subscription_id) && isset($status);

		// Check if any of the required fields are empty!

		if (!$isField) {

			return Response()->json(["status" => false, "message" => "Some fields are required"]);
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
		$transaction->subscription_id = $subscription_id ?? 'c';
		$transaction->status = $status ?? '';
		$transaction->save();

		$data = [];
		$data['status'] = true;
		$data['message'] = 'Success';
		$data['Transaction'] = $transaction;

		return Response()->json($data);
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
			return Response()->json(["status" => false, "message" => "Some fields are Required "]);
		}

		// Check if details are already existed!

		$isTransaction = Subscription::where('transaction_id', $transaction_id)->count();
		// $isShop = Subscription::where('shop_id', $shop_id)->count();

		if ($isTransaction != 0) {

			return Response()->json(["status" => false, "message" => "Transaction Id Already Exists ! "]);
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
	}

	//==--==--==--==-- Subscription List  --==--==--==--==--==

	public function getSubscription(Request $request)

	{
		$shop_id = $request->input('shop_id');
		return  Subscription::where('shop_id', $shop_id)->get();
	}

	//==--===--==--==--==-- User Update  --==--==--==--==--==--==
	public function userUpdate(Request $request)

	{
		$validator = Validator::make($request->all(), [
			'name' => 'required|min:2| max:100',
		]);

		if ($validator->fails()) {
			return Response()->json(["status" => false, "message" => "Entered name is invalid"]);

			$user = new User();
			$user->update([
				'name' => $request->name,
			]);

			return Response()->json(["status" => true, "message" => "Profile updated successfully"]);
		}
	}


	// ==--==--==--== Shop Update ==--==--==--==--

	public function shopUpdate(Request $request)

	{
		$validator = Validator::make($request->all(), [
			'shop_name' => 'required|min:2| max:100',
			'shop_address' => 'required|min:2| max:100',

		]);

		if ($validator->fails()) {
			return Response()->json(["status" => false, "message" => "Invalid name and shop address"]);

			$shop = new Shop();
			$shop->update([
				'shop_name' => $request->shop_name,
				'shop_address' => $request->shop_address,
			]);

			return Response()->json(["status" => true, "message" => "Shop updated successfully"]);
		}
	}

	// ==--==--==--==--==- Change Password ==--==--==--==--

	public function updatePassword(Request $request)

	{
		$validator = Validator::make($request->all(), [
			'password' => 'required|min:6| max:100',
		]);

		if ($validator->fails()) {
			return Response()->json([
				'message' => 'validations fails',
				'errors' => $validator->errors()
			]);
		}

		$user = $request->user();
		if (Hash::check($request->old_password, $user->password)) {
			$user->update([
				'password' => Hash::make($request->password)
			]);

			return Response()->json(["status" => false, "message" => "Old password doesnt match"]);
		} else {
			return Response()->json(["status" => true, "message" => "Password updated successfully"]);
		}
	}
}
