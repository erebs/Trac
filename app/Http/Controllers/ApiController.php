<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shop;
use App\Models\Image;
use App\Models\Fraud;
use App\Models\Transaction;
use App\Models\Subscription;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use paytm\paytmchecksum\PaytmChecksum;


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

	//==--==--==--==-- User Validation --==--==--==--==--==

	public function userValidation(Request $request)
	{
		$username = $request->input('username');

		$user = User::where('mobile_number', $username)->orWhere('member_id', $username)->first();


		// Check if User exist
		if (!blank($user)) {
			// Get Shops by User
			$shops = Shop::where('user_id', $user->id)->get();

			// Check if User have shops
			if (!blank($shops)) {
				return response()->json(["status" => true, 'message' => "User validated successfully", 'user' => $user, 'shops' => $shops]);
			} else {
				return response()->json(["status" => false, 'message' => "You don't have any shops registered", 'user_id' => $user->id]);
			}
		} else {
			return response()->json(["status" => false, 'message' => 'Invalid mobile number or member id']);
		}
	}

	//==--==--==--==-- User Login --==--==--==--==--==

	public function userLogin(Request $request)
	{

		// Assigning Arguments to Variables
		$username = $request->input('username');
		$password = $request->input('password');
		$shopNumber = $request->input('shop_number');

		// Checking if fields are empty
		$isField = isset($username) && isset($password);
		if (!$isField) {
			return Response()->json(["status" => false, "message" => "Some fields are required"]);
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
			$shop = Shop::where('user_id', $user->id)->where('mobile_number', $shopNumber)->first();

			// Check if User has any Shop
			if (blank($shop)) {
				return Response()->json(["status" => false, "message" => "Incorrect shop number. Please try again"]);
			} else {

				if ($shop['status'] == 'Active') {

					return Response()->json(["status" => true, "is_active" => true, "message" => "Logged in successfully", "user" => $user, "shop" => $shop]);
				} else {

					return Response()->json(["status" => true, "is_active" => false, "message" => "Your shop doesn't have an active plan!", "user" => $user, "shop" => $shop]);
				}
			}
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


		$isField = isset($shop_name) && isset($mobile_number) && ($pincode) && ($constituency) && ($user_id) && ($district);

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
		$shop->shop_name = $shop_name;
		$shop->user_id = $user_id;
		$shop->gst = $gst;
		$shop->mobile_number = $mobile_number;
		$shop->constituency = $constituency;
		$shop->pincode = $pincode;
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

			if ($shop['status'] == 'Active') {

				return Response()->json(["status" => true, "is_active" => true, "message" => "Logged in successfully", "shop" => $shop]);
			} else {

				return Response()->json(["status" => true, "is_active" => false, "message" => "Your shop have no active plan!", "shop" => $shop]);
			}
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


		$imageData = base64_decode($profile_photo);
		$imageFileName = strtolower('img' . Carbon::now()->timestamp . Str::random(4) . '.jpg');
		file_put_contents(public_path() . '/uploads/frauds/' . $imageFileName, $imageData) or print_r(error_get_last());

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
			->orWhere('mobile_number', 'like', '%' . $query . '%')->orWhere('proof_number', 'like', '%' . $query . '%')->take(30)->get();
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


	//==--==--==--==-- Fraud Images --==--==--==--==--==

	public function fraudImages(Request $request)

	{
		$fraudId = $request->input('fraud_id');


		$validator = Validator::make($request->all(), ['fraud_id' => 'required|exists:frauds,id']);

		if ($validator->fails()) {

			return Response()->json(["status" => false, "message" => $validator->errors()]);
		}

		$fraudImages = Image::where('fraud_id', $fraudId)->get();

		$images = [];

		foreach ($fraudImages as $image) {

			$images[] = $image['image'];
		}

		return Response()->json(["status" => true, "message" => 'success', 'images' => $images]);
	}

	//==--==--==--==-- Transaction Register --==--==--==--==

	public function transactionRegister(Request $request)
	{
		$validator = Validator::make(
			$request->all(),
			[
				'user_id' => 'required|exists:users,id',
				'shop_id' => 'required|exists:shops,id',
				'dump' => 'required',
				'status' => 'required'
			]
		);

		if ($validator->fails()) {
			return Response()->json(["status" => false, "message" => $validator->errors()]);
		}

		// Assigning Arguments to Variables 
		$user_id = $request->input('user_id');
		$shop_id = $request->input('shop_id');
		$dump = $request->input('dump');
		$status = $request->input('status');

		$transaction = new Transaction();
		$transaction->shop_id = $shop_id;
		$transaction->user_id = $user_id;
		$transaction->dump = $dump;
		$transaction->status = $status;
		$transaction->save();

		$data = [];
		$data['status'] = true;
		$data['message'] = 'Success';
		$data['transaction'] = $transaction;

		return Response()->json($data);
	}

	//==--==--==--==-- Subscription Register  --==--==--==--==--==

	public function subscriptionRegister(Request $request)

	{

		$validator = Validator::make(
			$request->all(),
			[
				'user_id' => 'required|exists:users,id',
				'shop_id' => 'required|exists:shops,id',
				'transaction_id' => 'required|exists:transactions,id',
				'price' => 'required',
				'subscription_StartDate' => 'required',
				'subscription_EndDate' => 'required'
			]
		);

		if ($validator->fails()) {
			return Response()->json(["status" => false, "message" => $validator->errors()]);
		}

		// Assigning Arguments to Variables
		$user_id = $request->input('user_id');
		$shop_id = $request->input('shop_id');
		$transaction_id = $request->input('transaction_id');
		$price = $request->input('price');
		$subscription_StartDate = $request->input('subscription_StartDate');
		$subscription_EndDate = $request->input('subscription_EndDate');

		$subscription = new Subscription();
		$subscription->user_id = $user_id;
		$subscription->shop_id = $shop_id;
		$subscription->transaction_id = $transaction_id;
		$subscription->price = $price;
		$subscription->subscription_StartDate = $subscription_StartDate;
		$subscription->subscription_EndDate = $subscription_EndDate;
		$subscription->save();

		$data = [];
		$data['status']  = true;
		$data['message'] = 'Success';
		$data['subscription'] = $subscription;

		return Response()->json($data);
	}

	//==--==--==--==-- Subscription  --==--==--==--==--==

	public function subscription(Request $request)

	{

		$shopId = $request->input('shop_id');

		$validator = Validator::make(
			$request->all(),

			[
				'shop_id' => 'required|exists:shops,id',
			]
		);

		if ($validator->fails()) {

			return Response()->json(["status" => false, "message" => $validator->errors()]);
		}

		// Check does Shop have an active plan
		$subscription = Subscription::where('shop_id', $shopId)->where('subscription_EndDate', '>', Carbon::now())->latest('id')->first();

		if (!blank($subscription)) {
			return Response()->json(["status" => true, "message" => 'success', 'subscription' => $subscription]);
		} else {
			return Response()->json(["status" => false, "message" => "Your shop doesn't have an active plan!"]);
		}
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
			return Response()->json(["status" => false, "message" => "Invalid shop name and shop address"]);

			$shop = new Shop();
			$shop->update([
				'shop_name' => $request->shop_name,
				'shop_address' => $request->shop_address,
			]);

			return Response()->json(["status" => true, "message" => "Shop updated successfully"]);
		}
	}
	// ===--===---===---===- Shop Status Update ==--===--==---==

	public function shopStatusUpdate(Request $request)
	{
		$shopId = $request->input('shop_id');
		$userId = $request->input('user_id');
		$status = $request->input('status');


		$validator = Validator::make(
			$request->all(),
			[
				'shop_id' => 'required|exists:shops,id',
				'user_id' => 'required|exists:shops,user_id',
				'status' => ['required', Rule::in(['Active', 'Pending',  'Suspended', 'Deleted', 'Enable'])],
			],

		);


		if ($validator->fails()) {
			return Response()->json(["status" => false, "message" => $validator->errors()]);
		} else {
			$shop = Shop::find($shopId);

			if ($shop['user_id'] == $userId) {


				// Check does Shop have an active plan
				if ($status == 'Enable') {
					$subscription = Subscription::where('shop_id', $shopId)->where('user_id', $userId)->where('subscription_EndDate', '>', Carbon::now())->get();

					if (!blank($subscription)) {
						$status = 'Active';
					} else {
						$status = 'Pending';
					}
				}


				$shop->status = $status;
				$shop->save();
				return Response()->json(["status" => true, "message" => "Shop status updated successfully"]);
			} else {
				return Response()->json(["status" => false, "message" => "User and Shop doesn't match!"]);
			}
		}
	}

	// ==--==--==--==--==- Reset Password ==--==--==--==--

	public function resetPassword(Request $request)

	{

		$mobile_number = $request->input('mobile_number');
		$password = $request->input('password');


		$validator = Validator::make($request->all(), [

			'mobile_number' => 'required|exists:users,mobile_number',
			'password' => 'required|min:8| max:32'
		]);

		if ($validator->fails()) {
			return response()->json(["status" => false, 'message' => $validator->errors()]);
		}
		$user = User::where('mobile_number', $mobile_number)->first();


		$user->update([
			'password' => bcrypt($password)
		]);
		return Response()->json(["status" => true, "message" => "Password updated successfully"]);
	}


	// ==--==--==--==--==- Change Password ==--==--==--==--

	public function changePassword(Request $request)

	{

		$userId = $request->input('user_id');
		$password = $request->input('password');
		$newPassword = $request->input('new_password');


		$validator = Validator::make($request->all(), [

			'user_id' => 'required',
			'password' => 'required|min:8|max:32'
		]);

		if ($validator->fails()) {
			return response()->json(["status" => false, 'message' => $validator->errors()]);
		}
		$user = User::find($userId);
		if (Hash::check($password, $user->password)) {
			$user->update([
				'password' => bcrypt($newPassword)
			]);
			return Response()->json(["status" => true, "message" => "Password updated successfully"]);
		} else {
			return Response()->json(["status" => false, "message" => "Incorrect old password. Please try again"]);
		}
	}


	// ==--===--==-- Fraud Images Upload ===---=====--

	public function fraudImagesUpload(Request $request)
	{
		$fraudId = $request->input('fraud_id');
		$shopId = $request->input('shop_id');
		$images = $request->input('images');

		$validator = Validator::make(
			$request->all(),
			[
				'fraud_id' => 'required|exists:frauds,id',
				'shop_id' => 'required|exists:shops,id',
				'images' => 'required',
			]
		);

		if ($validator->fails()) {
			return Response()->json(["status" => false, "message" => $validator->errors()]);
		}

		$fraud = Fraud::where('id', $fraudId)->where('shop_id', $shopId)->get();

		if (!blank($fraud)) {

			// Loop images and insert one by one
			foreach ($images as $image) {

				$imageData = base64_decode($image);
				$imageFileName = strtolower('img' . Carbon::now()->timestamp . Str::random(4) . '.jpg');
				file_put_contents(public_path() . '/uploads/images/' . $imageFileName, $imageData) or print_r(error_get_last());


				$fraud = new Image();
				$fraud->fraud_id = $fraudId;
				$fraud->shop_id = $shopId;
				$fraud->append_url = '/uploads/images/';
				$fraud->image = $imageFileName;
				$fraud->save();
			}

			return Response()->json(["status" => true, "message" => "Images added successfully"]);
		} else {

			return Response()->json(["status" => false, "message" => "Fraud and Shop doesn't match!"]);
		}
	}

	// ==--==--==--==--==- Initiate Transaction ==--==--==--==--

	public function initiateTransaction(Request $request)
	{

		$merchantId = $request->input("merchant_id");
		$merchantKey = $request->input("merchant_key");
		$orderId = $request->input("order_id");
		$amount = $request->input("amount");
		$custId = $request->input("customer_id");
		$websiteName = $request->input("website");
		$callbackUrl = $request->input("callback_url");


		$paytmParams = array();

		$paytmParams["body"] = array(
			"requestType" => "Payment",
			"mid" => $merchantId,
			"websiteName" => $websiteName,
			"orderId"  => $orderId,
			"callbackUrl" => $callbackUrl,
			"txnAmount" => array(
				"value" => $amount,
				"currency" => "INR"
			),
			"userInfo" => array(
				"custId" => $custId
			)
		);

		$checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $merchantKey);
		$paytmParams["head"] = array(
			"signature" => $checksum
		);

		$post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);


		/* for Staging */
		$url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=$merchantId&orderId=$orderId";

		/* for Production */
		// $url = "https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid=$merchantId&orderId=$orderId";

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$response =  json_decode(curl_exec($ch), true);

		if ($response["body"]["resultInfo"]["resultCode"] == "0000") { // 0000 means success

			$txnToken = $response["body"]["txnToken"];
			return Response()->json(["status" => true, "message" => "TXN Token generated successfully", "txnToken" => $txnToken, "response" => $response]);
		} else {
			$message = $response["body"]["resultInfo"]["resultMsg"];
			return Response()->json(["status" => false, "message" => $message, "response" => $response]);
		}
	}



	// ==--==--==--==--==- Transaction Status ==--==--==--==--

	public function transactionStatus(Request $request)
	{

		$merchantId = $request->input("merchant_id");
		$merchantKey = $request->input("merchant_key");
		$orderId = $request->input("order_id");

		$paytmParams = array();


		$paytmParams["body"] = array(
			"mid" => $merchantId,
			"orderId" => $orderId,
		);

		$checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $merchantKey);


		$paytmParams["head"] = array(
			"signature"	=> $checksum
		);

		/* prepare JSON string for request */
		$post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

		/* for Staging */
		$url = "https://securegw-stage.paytm.in/v3/order/status";

		/* for Production */
		// $url = "https://securegw.paytm.in/v3/order/status";

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		$response = json_decode(curl_exec($ch), true);
		$txnId = $response["body"]["txnId"] ?? null;
		if ($response["body"]["resultInfo"]["resultCode"] == "01") { // 01 means success
			return Response()->json(["status" => true, "message" => "TXN completed successfully", "txnId" => $txnId, "response" => $response]);
		} else {

			$message = $response["body"]["resultInfo"]["resultMsg"];
			return Response()->json(["status" => false, "message" => $message, "txnId" => $txnId, "response" => $response]);
		}
	}
}
