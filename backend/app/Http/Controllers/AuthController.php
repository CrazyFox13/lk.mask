<?php

namespace App\Http\Controllers;

use App\Helpers\Mutator;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ConfirmEmail;
use App\Http\Requests\Auth\EmailReset;
use App\Http\Requests\Auth\Login;
use App\Http\Requests\Auth\PasswordLogin;
use App\Http\Requests\Auth\Profile;
use App\Http\Requests\Auth\Register;
use App\Http\Requests\Auth\SendEmailConfirmation;
use App\Http\Requests\Auth\SetAvatar;
use App\Http\Requests\Auth\SetDevice;
use App\Http\Requests\Auth\SetPassword;
use App\Http\Requests\Auth\SetSilence;
use App\Http\Requests\Auth\ValidateCode;
use App\Mail\EmailConfirmation;
use App\Mail\ResetPassword;
use App\Models\Company;
use App\Mail\SendTempPassword;
use App\Models\AppDevice;
use App\Models\City;
use App\Models\Order;
use App\Models\Report;
use App\Models\User;
use App\Models\UserLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    private function check_captcha($token): bool
    {
        if (config("app.env")== "local"){
            return true;
        }
        $ch = curl_init();
        $args = http_build_query([
            "secret" => config("services.captcha"),
            "token" => $token,
            "ip" => $_SERVER['REMOTE_ADDR'], // Нужно передать IP пользователя.
            // Как правильно получить IP зависит от вашего прокси.
        ]);
        curl_setopt($ch, CURLOPT_URL, "https://smartcaptcha.yandexcloud.net/validate?$args");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);

        $server_output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode !== 200) {
            echo "Allow access due to an error: code=$httpcode; message=$server_output\n";
            return true;
        }
        $resp = json_decode($server_output);
        return $resp->status === "ok";
    }

    public function register(Register $request): JsonResponse
    {
        if (!$this->check_captcha($request->get("captcha_token"))) {
            return response()->json([
                "message" => "Captcha is wrong",
            ], 422);
        }
        $plainPassword = Str::random();
        $user = new User();
        $user->phone = $request->get('phone');
        $user->password = Hash::make($plainPassword);
        $user->temp_password = true;
        $user->type = 'user'; // Устанавливаем тип пользователя явно
        $user->save();

        $user->sendPhoneCode();

        Cache::put("user_{$user->id}:plain-password", $plainPassword, 60 * 60 * 24 * 7); // На 7 дней кэшируем пароль в редисе?

        return $this->emptySuccessResponse();
    }

    public function loginByPassword(PasswordLogin $request): JsonResponse
    {
        if (!$this->check_captcha($request->get("captcha_token"))) {
            return response()->json([
                "message" => "Captcha is wrong",
            ], 422);
        }
        
        if ($request->has('email')) {
            return $this->emailPasswordLogin($request);
        } else {
            return $this->phonePasswordLogin($request);
        }
    }

    public function emailPasswordLogin(PasswordLogin $request): JsonResponse
    {
        $user = User::where("email", $request->get('email'))->first();

        if (!Hash::check($request->get('password'), $user->password)) {
            return response()->json([
                "message" => "Some fields are filled in incorrectly",
                "errors" => [
                    "password" => "Неверный пароль. Попробуйте еще раз."
                ]
            ], 422);
        }

        $token = $user->generateAccessToken("by_email_password");

        return $this->resourceItemResponse('access_token', $token);
    }

    public function phonePasswordLogin(PasswordLogin $request): JsonResponse
    {
        $cleanPhone = preg_replace('/\D/', '', $request->get('phone'));
        $user = User::query()->whereRaw("REGEXP_REPLACE(phone, '[^0-9]', '') = ?", [$cleanPhone])->first();

        if (!Hash::check($request->get('password'), $user->password)) {
            return response()->json([
                "message" => "Some fields are filled in incorrectly",
                "errors" => [
                    "password" => "Неверный пароль. Попробуйте еще раз."
                ]
            ], 422);
        }

        $token = $user->generateAccessToken("by_phone_password");

        return $this->resourceItemResponse('access_token', $token);
    }

    public function login(Login $request): JsonResponse
    {
        if (!$this->check_captcha($request->get("captcha_token"))) {
            return response()->json([
                "message" => "Captcha is wrong",
            ], 422);
        }

        $user = User::where("phone", $request->get('phone'))->first();

        if ($user->inSendingPhoneCodeTimeout()) {
            return response()->json([
                "message" => "Some fields are filled in incorrectly",
                "errors" => [
                    "phone" => "Мы недавно отправили сообщение. Подождите, прежде, чем отправлять следующее"
                ]
            ], 422);
        }
        list('success' => $success, 'message' => $message) = $user->sendPhoneCode();
        if (!$success) {
            return response()->json([
                "message" => "Ошибка при отправке СМС",
                "errors" => [
                    "phone" => $message
                ]
            ], 422);
        }
        return $this->emptySuccessResponse();
    }

    public function validatePhoneCode(ValidateCode $request): JsonResponse
    {
        $user = User::where("phone", $request->get('phone'))->first();
        if ($user->phone_code != $request->get('phone_code')) {
            return response()->json([
                "message" => "Some fields are filled in incorrectly",
                "errors" => [
                    "phone_code" => "Неправильный код. Попробуйте еще раз."
                ]
            ], 422);
        }

        $user->verifyPhone();

        if ($request->get('reset_password')) {
            $user->temp_password = true;
            $user->save();
        }

        $token = $user->generateAccessToken("by_phone");

        return $this->resourceItemResponse('access_token', $token);
    }

    public function me(Request $request): JsonResponse
    {
        $user = User::find(auth()->id());
        $user->markAsOnline();
        $user->load(['company' => function ($q) {
            $q->with('type', 'documents')->withCount(['approvedRecommendations', 'activeReports', 'activeOrders']);
        }, 'city']);
        $user->loadCount(['approvedRecommendations', 'activeReports', 'activeOrders']);

        if ($user->company) {
            $user->company->append("vehicle_types_id");
        }

        if ($request->get("is_web")) {
            if ($user->last_device !== "web") {
                $user->last_device = "web";
                $user->save();
            }
        }

        return $this->resourceItemResponse('user', $user);
    }

    public function checkToken(Request $request): JsonResponse
    {
        $check = !!$request->user('sanctum');
        if ($check) {
            $user = auth('sanctum')->user();
            $user->markAsOnline();
        }

        $token = request()->bearerToken();
        list($tokenId) = explode("|", $token);
        return $this->resourceItemResponse('check', $check, [
            'tokenId' => $tokenId
        ]);
    }

    public function profile(Profile $request): JsonResponse
    {
        $user = User::query()->findOrFail(auth()->id());
        $user->fill($request->all());
        $user->phone = $request->get("phone");
        $changes = [];

        if ($user->isDirty('name')) {
            $changes[] = "имя на $user->name";
        }
        if ($user->isDirty('surname')) {
            $changes[] = "фамилия на $user->surname";
        }

        if ($user->isDirty("email")) {
            $originalEmail = $user->getOriginal('email');
            $changes[] = "email c <s>$originalEmail</s> на $user->email";
            $user->resetEmailVerification();
        }

        if ($user->isDirty("phone")) {
            $originalPhone = $user->getOriginal('phone');
            $originalPhone = Mutator::digitsToRuPhoneNumber($originalPhone);
            $newPhone = Mutator::digitsToRuPhoneNumber($user->phone);
            $changes[] = "телефон с <s>$originalPhone</s> на $newPhone";
            $user->resetPhoneVerification();
        }

        if ($request->get("geo_city_id")) {
            $user->geo_city_id = $request->geo_city_id;
        }

        if (count($changes) > 0) {
            $originalName = $user->getOriginal('name');
            $originalSurname = $user->getOriginal('surname');
            $changesMessage = implode(", ", $changes);
            $logMessage = "У сотрудника $originalName $originalSurname изменено: $changesMessage";
            UserLog::write($user->id, $logMessage);
            if ($user->company_id && $user->isEmployee()) {
                UserLog::write($user->company->boss->id, $logMessage);
            }
        }

        $user->save();

        if (!$user->auto_subscribe_city) {
            if ($user->subscribedCities()->count() === 0) {
                $user->subscribedCities()->attach($request->geo_city_id);
                $user->auto_subscribe_city = true;
                $user->save();
            }
        }

        return $this->resourceItemResponse('user', $user);
    }

    public function setPassword(SetPassword $request): JsonResponse
    {
        $user = User::query()->find(auth()->id());
        if (!$user->temp_password && !Hash::check($request->get('old_password'), $user->password)) {
            return response()->json([
                "message" => "Some fields are filled in incorrectly",
                "errors" => [
                    "old_password" => "Неправильный пароль. Попробуйте еще раз."
                ]
            ], 422);
        }

        $user->password = Hash::make($request->get('password'));
        $user->temp_password = false;
        $user->save();

        return $this->emptySuccessResponse();
    }

    public function setDevice(SetDevice $request): JsonResponse
    {
        $user = auth("sanctum")->user();
        $device = AppDevice::query()
            ->where("user_id", $user->id)
            ->first();

        if (!$device) {
            $device = new AppDevice([
                'user_id' => auth()->id(),
            ]);
        }

        $device->fill([
            'type' => $request->get('type'),
            'token' => $request->get('token')
        ]);
        $device->save();

        $user->last_device = $request->get("type");
        $user->save();

        return $this->emptySuccessResponse();
    }

    public function sendEmailCode(SendEmailConfirmation $request): JsonResponse
    {
        $user = User::find(auth()->id());
        if (!$user->email) {
            return response()->json([
                "message" => "Some fields are filled in incorrectly",
                "errors" => [
                    "email" => "Fill email first"
                ]
            ], 422);
        }
        if ($user->email_code_sent_at && Carbon::now()->diffInSeconds(Carbon::parse($user->email_code_sent_at)) < 60) {
            return response()->json([
                "message" => "Some fields are filled in incorrectly",
                "errors" => [
                    "code" => "Вы уже запрашивали код. Подождите 1 минуту перед следующей попыткой."
                ]
            ], 422);
        }

        $user->email = $request->get("email");
        $user->email_code = random_int(1000, 9999);
        $user->email_code_sent_at = Carbon::now();
        $user->save();

        try {
            Mail::to($user)->send(new EmailConfirmation($user));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error while sending email',
                'errors' => $e->getMessage(),
            ], 400);
        }

        return $this->emptySuccessResponse();
    }

    public function validateEmailCode(ConfirmEmail $request): RedirectResponse
    {
        /** @var User $user */
        $user = User::find($request->get('user_id'));
        if (!$user->verifyEmailConfirmationHash($request->get('hash'))) {
            return redirect()->to(url("/profile"));
        }
        $user->email_verified_at = Carbon::now();
        $user->save();

        if ($user->temp_password) {
            $cachedPlainPassword = Cache::get("user_{$user->id}:plain-password");
            if ($cachedPlainPassword) {
                Mail::to($user->getRawOriginal('email'))->queue(new SendTempPassword($user, $cachedPlainPassword));
                Cache::forget("user_{$user->id}:plain-password");
            }
        }

        return redirect()->to(url("/auth/email-confirmed"));
    }

    public function resetEmail(EmailReset $request): JsonResponse
    {
        $user = User::query()
            ->where("email", $request->get("email"))
            ->first();

        $user->temp_password = true;
        $user->reset_hash = Str::random();
        $user->save();

        Mail::to($user->getRawOriginal('email'))->queue(new ResetPassword($user));

        return $this->emptySuccessResponse();
    }

    public function authByHash(Request $request)
    {
        $request->validate([
            'hash' => 'required',
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::query()
            ->where("email", $request->get("email"))
            ->first();

        if (!$user->validateEmailResetLink($request->get("hash"))) {
            abort(403);
        }

        $token = $user->generateAccessToken("by_reset");

        return $this->resourceItemResponse('access_token', $token);
    }

    public function setAvatar(SetAvatar $request): JsonResponse
    {

        $user = User::find(auth()->id());
        $user->avatar = $request->get("avatar");
        $user->save();

        return $this->emptySuccessResponse();
    }

    public function deleteAvatar(): JsonResponse
    {
        $user = User::find(auth()->id());
        $user->avatar = null;
        $user->save();
        return $this->emptySuccessResponse();
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = auth()->user();
        $user->password = Hash::make($request->get("password"));
        $user->save();

        return $this->emptySuccessResponse();
    }

    public function setSilence(SetSilence $request): JsonResponse
    {
        $user = auth()->user();
        $user->silence = $request->get('silence');
        $user->silence_from = $request->get('silence_from');
        $user->silence_from_m = $request->get('silence_from_m');
        $user->silence_to = $request->get('silence_to');
        $user->silence_to_m = $request->get('silence_to_m');
        $user->save();
        return $this->emptySuccessResponse();
    }

    public function logout(Request $request): JsonResponse
    {
        $user = User::query()->find(auth()->id());

        $token = request()->bearerToken();
        list($tokenId) = explode("|", $token);
        $user->tokens()->where('id', (int)$tokenId)->delete();

        #$user->device()->delete();

        return $this->emptySuccessResponse();
    }

    public function deleteAccount(): JsonResponse
    {
        $user = User::query()->find(auth('sanctum')->id());

        if ($user->isBoss()) {
            $company = $user->company;
            if ($company) {
                // delete all orders
                Order::query()->where("company_id", $company->id)->delete();

                // delete all staff
                User::query()->where("company_id", $company)
                    ->staff()->delete();

                // delete company
                $company->delete();
            }
        }
        Order::query()->where("user_id", $user->id)->delete();

        // delete out reports
        Report::query()->where("author_user_id", $user->id)->delete();

        $user->delete();

        return $this->emptySuccessResponse();
    }
}
