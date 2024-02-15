<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyRequest;
use App\Mail\SendMail;
use App\Models\RegisterVerify;
use App\Models\User;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * 取得登入token
     * 
     * @param Request $request
     * 
     * @return Response
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;
        Auth::login($user);

        return response()->json([
            'token' => $token
        ]);
    }

    /**
     * 登出 & 刪除token
     * 
     * @param Request $request
     * 
     * @return Response
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * 註冊
     * 
     * @param RegisterRequest $request
     * 
     * @return OK
     */
    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $user = new User();
            $user->setRawAttributes($data);
            if ($user->save() === false) {
                throw new BadRequestException('insert_error');
            }

            // 取得驗證資訊
            $verify = $this->getVerifyCode(6, $user);

            $register = new RegisterVerify();
            $register->setRawAttributes($verify);
            if ($register->save() === false) {
                throw new BadRequestException('insert_error');
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new BadRequestException($e->getMessage());
        }

        // 寄送郵件
        Mail::to($user->email, $user->name)
            ->send(new SendMail($user->name, $verify['verification_code'], $user->id));

        return 'OK';
    }

    /**
     * 驗證信箱
     * 
     * @param VerifyRequest $request
     * @param string $id
     * 
     * @return Response
     */
    public function verify(VerifyRequest $request, string $id)
    {
        // TODO: 測試+新開一個重新發送驗證碼的api
        DB::beginTransaction();
        $data = $request->validated();
        $register = RegisterVerify::where('user_id', $id)->firstOrFail();

        $return = [];
        try {
            // 當前時間戳
            $currentTime = new DateTime();
            $now = $currentTime->format('Y-m-d H:i:s');
            $timestamp = $currentTime->getTimestamp();

            // 取得使用者
            $user = User::findOrFail($id);

            // 驗證時間小於現在時間
            if ($register->expired_time < $timestamp) {
                $return['message'] = '驗證時間超過請重新驗證';
                // 刪除驗證碼
                RegisterVerify::where('user_id', $id)->delete();
            } elseif ($data['verification_code'] !== $register->verification_code) {
                // 驗證碼有誤
                $return['message'] = '驗證碼有誤請重新驗證';
            } else {
                // 驗證成功更新使用者啟用時間
                $user->email_verified_at = $now;
                if ($user->save() === false) {
                    throw new BadRequestException('insert_error');
                }
                // 刪除驗證碼
                RegisterVerify::where('user_id', $id)->delete();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new BadRequestException($e->getMessage());
        }

        return $return === [] ? 'OK' : response()->json($return);
    }

    /**
     * 重新發送驗證碼
     * 
     * @param string $id
     * 
     * @return Response
     */
    public function sendEmail(string $id): Response
    {
        $user = User::where('id', $id)
            ->whereNull('email_verified_at')
            ->first();

        // 刪除驗證碼
        RegisterVerify::where('user_id', $id)->delete();
        if (!$user) {
            return response()->json(['message' => '無可驗證的使用者']);
        }
        // 取得驗證資訊
        $verify = $this->getVerifyCode(6, $user);

        // 新增驗證碼
        $register = new RegisterVerify();
        $register->setRawAttributes($verify);
        if ($register->save() === false) {
            throw new BadRequestException('insert_error');
        }

        // 寄送郵件
        Mail::to($user->email, $user->name)
            ->send(new SendMail($user->name, $verify['verification_code'], $user->id));

        return response()->json(['message' => '驗證碼已發送，請查看郵件']);
    }

    /**
     * 隨機驗證碼
     * 
     * @param $length
     * @param User $user
     * 
     * @return array
     */
    private function getVerifyCode($length = 6, User $user): array
    {
        // 驗證碼生成
        $str = '123456789ABCDEFGHIJKLMOPQRSTUBWXYZ';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $str[mt_rand(0, strlen($str) - 1)];
        }

        $currentTimestamp = time();
        $expireTime = strtotime('+30 minutes', $currentTimestamp);

        $verify = [
            'user_id' => $user->id,
            'verification_code' => $code,
            'expired_time' => $expireTime
        ];

        return $verify;
    }
}
