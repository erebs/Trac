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
use Carbon\Carbon;
use Illuminate\Support\Str;

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
		$district = $request->input('district');

		$isField = isset($name) && isset($mobile_number) && isset($member_id) && isset($password) && isset($pincode) && isset($constituency) && isset($constituency);

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
		$user->district = $district;
		$user->save();

		$data = [];
		$data['status'] = true;
		$data['message'] = 'Success';
		$data['user'] = $user;

		return Response()->json($data);
	}

	//==--==--==--==-- User Register Validation --==--==--==--==--==

	public function registerValidation(Request $request)
	{
		$validator = Validator::make(
			$request->all(),
			[
				'mobile_number' => 'unique:users,mobile_number',
				'member_id' => 'unique:users,member_id'
			],
			[
				'mobile_number.unique' => 'Mobile number already exists',
				'member_id.unique' => 'Member id already exists',
			]
		);

		if ($validator->fails()) {
			return response()->json(["status" => false, 'message' => $validator->errors()]);
		} else {
			return response()->json(["status" => true, 'message' => "Validated successfully"]);
		}
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
		$district = $request->input('district');


		$isField = isset($shop_name) && isset($mobile_number) && ($pincode) && ($gst)  && ($constituency) && ($user_id) && ($district);

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
		$shop->district = $district;
		$shop->shop_address = $shop_address;
		$shop->status = $status ?? 'pending';
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

			return Response()->json(["status" => false, "message" => "Shop Mobile Number does not exist!"]);
		} else {

			return Response()->json(["status" => true, "message" => "Logged in successfully", "shop" => $shop]);
		}
	}

	//==--==--==--===-- Shops by User ==--==--==--==--

	public function shopsByUser(Request $request)
	{
		$user_id = $request->input('user_id');


		$validator = Validator::make($request->all(), ['user_id' => ['required', 'exists:shops,user_id']]);

		if ($validator->fails()) {

			return Response()->json(["status" => false, "message" => $validator->errors()]);
		}

		$shops = shop::where('user_id', $user_id)->get();

		return Response()->json(["status" => true, "message" => 'success', 'shops' => $shops]);
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
		$isProof  = Fraud::where('proof_number', $proof_number)->count();
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

		// $rawDataExploder = explode(";", $profile_photo);
		// $dataExploder = explode(",", $rawDataExploder[1]);
		$imageData = base64_decode($profile_photo);
		$imageFileName = strtolower('img' . Carbon::now()->timestamp . Str::random(4).'.jpg');
		file_put_contents(public_path() . '/uploads/' . $imageFileName, $imageData) or print_r(error_get_last());

		$fraud = new Fraud();
		$fraud->shop_id = $shop_id;
		$fraud->name = $name;
		$fraud->mobile_number = $mobile_number;
		$fraud->address = $address;
		$fraud->profile_photo = $imageFileName;
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


		$validator = Validator::make($request->all(), ['shop_id' => ['required']]);

		if ($validator->fails()) {

			return Response()->json(["status" => false, "message" => $validator->errors()]);
		}

		$frauds = Fraud::where('shop_id', $shop_id)->get();

		return Response()->json(["status" => true, "message" => 'success', 'frauds' => $frauds]);
	}

	//==--==--==--==-- Transaction Register --==--==--==--==

	public function transactionRegister(Request $request)
	{
		// Assigning Arguments to Variables 
		$shop_id = $request->input('shop_id');
		$user_id = $request->input('user_id');
		$dump = $request->input('dump');
		$status = $request->input('status');

		$isField = isset($name) && isset($shop_id) && isset($user_id) && isset($dump) && isset($status);

		// Check if any of the required fields are empty!

		if (!$isField) {

			return Response()->json(["status" => false, "message" => "Some fields are required"]);
		}

		// Check if details are already existed!

		$isShop = Transaction::where('shop_id', $shop_id)->count();
		$isUser = Transaction::where('user_id', $user_id)->count();

		if ($isShop != 0) {

			return Response()->json(["status" => false, "message" => "Shop Id Already Exists!"]);
		}
		if ($isUser != 0) {

			return Response()->json(["status" => false, "message" => "User Id Already Exists!"]);
		}

		$transaction = new Transaction();
		$transaction->shop_id = $shop_id ?? '';
		$transaction->user_id = $user_id ?? '';
		$transaction->dump = $dump ?? '';
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
		$user_id = $request->input('user_id');
		$shop_id = $request->input('shop_id');
		$transaction_id = $request->input('transaction_id');
		$price = $request->input('price');
		$plans = $request->input('plans');
		$subscription_StartDate = $request->input('subscription_StartDate');
		$subscription_EndDate = $request->input('subscription_EndDate');



		$isField = isset($name) && isset($user_id) && isset($shop_id) && isset($transaction_id) && isset($price) && isset($plans) && isset($subscription_StartDate) && isset($subscription_EndDate);

		// Check if any of the required fields are empty!
		if (!$isField) {
			return Response()->json(["status" => false, "message" => "Some fields are Required "]);
		}

		// Check if details are already existed!

		$isTransaction = Subscription::where('transaction_id', $transaction_id)->count();
		$isShop = Subscription::where('shop_id', $shop_id)->count();
		$isUser = Subscription::where('user_id', $user_id)->count();

		if ($isTransaction != 0) {

			return Response()->json(["status" => false, "message" => "Transaction Id Already Exists ! "]);
		}
		if ($isShop != 0) {

			return Response()->json(["status" => false, "message" => "Shop Id Already Exists!"]);
		}
		if ($isUser != 0) {

			return Response()->json(["status" => false, "message" => "User Id Already Exists!"]);
		}

		$subscription = new Subscription();
		$subscription->name = $name;
		$subscription->user_id = $user_id;
		$subscription->shop_id = $shop_id;
		$subscription->transaction_id = $transaction_id;
		$subscription->price = $price;
		$subscription->plans = $plans;
		$subscription->subscription_StartDate = $subscription_StartDate;
		$subscription->subscription_EndDate = $subscription_EndDate;
		$subscription->save();

		$data = [];
		$data['status']  = true;
		$data['message'] = 'Success';
		$data['Subscription'] = $subscription;

		return Response()->json($data);
	}

	//==--==--==--==-- Subscription List  --==--==--==--==--==

	public function getSubscription(Request $request)

	{

		$shop_id = $request->input('shop_id');
		$user_id = $request->input('user_id');

		$validator = Validator::make(
			$request->all(),

			[
				'shop_id' => 'required|exists:subscriptions,shop_id',
				'user_id' => 'required|exists:subscriptions,user_id'
			],
			[
				'shop_id.unique' => 'shop Id already exists',
				'user_id.unique' => 'User Id already exists',
			]
		);

		if ($validator->fails()) {

			return Response()->json(["status" => false, "message" => $validator->errors()]);
		}

		$subscription = Subscription::where('Shop_id', $shop_id)->get();

		return Response()->json(["status" => true, "message" => 'success', 'Subscription' => $subscription]);
	}

	//==--===--==--==--==-- User Update  --==--==--==--==--==--==
	public function updateProfile(Request $request)

	{
		$validator = Validator::make($request->all(), [
			'name' => 'required|min:2| max:100',
		]);

		if ($validator->fails()) {
			return Response()->json(["status" => false, "message" => "Invalid name "]);

			$shop = new Shop();
			$shop->update([
				'name' => $request->name,
			]);

			return Response()->json(["status" => true, "message" => "Name updated successfully"]);
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
			return Response()->json(["status" => false, "message" => "Invalid  shop name and shop address"]);

			$shop = new Shop();
			$shop->update([
				'shop_name' => $request->shop_name,
				'shop_address' => $request->shop_address,
			]);

			return Response()->json(["status" => true, "message" => "Shop updated successfully"]);
		}
	}
	// ===--===---===---===- change Shop status ==--===--==---==

	public function shopStatusUpdate(Request $request)
	{
		$status = $request->input('status') ?? 'Pending';
		$id = $request->input('id') ?? 0;
		if ($id > 0) {
			$shops = Shop::find($id);
			$shops->status = $status;
			$shops->save();

			return Response()->json(["status" => true, "message" => "Status updated"]);
		} else {
			return Response()->json(["status" => false, "message" => "Something went wrong"]);
		}
	}

	// ==--==--==--==--==- Change Password ==--==--==--==--

	public function updatePassword(Request $request)

	{
		$validator = Validator::make($request->all(), [
			'old password' => 'required|min:6| max:32',
			'password' => 'required|min:6| max:32'
		]);

		if ($validator->fails()) {
			return response()->json(["status" => false, 'errors' => $validator->errors()]);
		} else {
			return response()->json(["status" => true, "message" => "Validation Success"]);
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

	// ==--===---===--  Image Upload  ==--===---===---===--===

	public function imageUpload(Request $request)
	{
		$imageData = $request->input('image_data');
		$rawDataExploder = explode(";", $imageData);
		$dataExploder = explode(",", $rawDataExploder[1]);
		$imageData = base64_decode($dataExploder[1]);
		$imageFileName = strtolower('simg' . Carbon::now()->timestamp . Str::random(4));
		file_put_contents(public_path() . $imageFileName . '.jpg', $imageData) or print_r(error_get_last());

		$response['status'] = '01';
		$response['message'] = 'Success';
		$response['photo_id'] = $imageFileName;

		return response($response, 200)
			->header('Content-Type', 'application/json');
	}
}
