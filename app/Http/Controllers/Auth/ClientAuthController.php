<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\TrustedDevice;
use App\Models\User;
use App\Support\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientAuthController extends Controller
{
  public function requestOtp(Request $request): JsonResponse
  {
    $request->validate([
      'phone' => ['required', 'string', 'min:10', 'max:11'],
      'name' => ['required', 'string', 'min:2', 'max:100'],
    ], [
      'phone.required' => 'O telefone é obrigatório.',
      'phone.min' => 'O telefone deve ter pelo menos 10 dígitos.',
      'phone.max' => 'O telefone deve ter no máximo 11 dígitos.',
      'name.required' => 'O nome é obrigatório.',
      'name.min' => 'O nome deve ter pelo menos 2 caracteres.',
    ]);

    $barbershopId = session('barbershop_id');
    $phone = preg_replace('/\D/', '', $request->phone);

    // Buscar ou criar usuário pelo telefone + barbearia
    $user = User::withoutGlobalScope('barbershop')
      ->where('phone', $phone)
      ->where('barbershop_id', $barbershopId)
      ->first();

    if (!$user) {
      $user = new User();
      $user->name = $request->name;
      $user->phone = $phone;
      $user->role = 'user';
      $user->barbershop_id = $barbershopId;
      $user->profile_photo = null;
      $user->save();
    } else {
      // Atualizar nome se diferente
      if ($user->name !== mb_strtolower($request->name)) {
        $user->name = $request->name;
        $user->save();
      }
    }

    // Invalidar OTPs anteriores do mesmo telefone
    OtpCode::forPhone($phone, $barbershopId)
      ->whereNull('verified_at')
      ->update(['verified_at' => now()]);

    // Gerar novo código OTP de 6 dígitos
    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    OtpCode::create([
      'phone' => $phone,
      'code' => $code,
      'barbershop_id' => $barbershopId,
      'expires_at' => now()->addMinutes(5),
    ]);

    // Enviar via WhatsApp
    $whatsapp = new WhatsAppService();
    $sent = $whatsapp->sendOtp($phone, $code);

    if (!$sent) {
      return response()->json([
        'success' => false,
        'message' => 'Erro ao enviar código. Tente novamente.'
      ], 500);
    }

    return response()->json([
      'success' => true,
      'message' => 'Código enviado para o seu WhatsApp.',
    ]);
  }

  public function verifyOtp(Request $request): JsonResponse
  {
    $request->validate([
      'phone' => ['required', 'string'],
      'code' => ['required', 'string', 'size:6'],
    ], [
      'code.required' => 'O código é obrigatório.',
      'code.size' => 'O código deve ter 6 dígitos.',
    ]);

    $barbershopId = session('barbershop_id');
    $phone = preg_replace('/\D/', '', $request->phone);

    $otp = OtpCode::valid($phone, $request->code, $barbershopId)->first();

    if (!$otp) {
      return response()->json([
        'success' => false,
        'message' => 'Código inválido ou expirado.',
      ], 422);
    }

    // Marcar OTP como verificado
    $otp->update(['verified_at' => now()]);

    // Buscar usuário
    $user = User::withoutGlobalScope('barbershop')
      ->where('phone', $phone)
      ->where('barbershop_id', $barbershopId)
      ->first();

    if (!$user) {
      return response()->json([
        'success' => false,
        'message' => 'Usuário não encontrado.',
      ], 404);
    }

    // Gerar device token (UUID v4 plaintext para o cliente, hash no DB)
    $deviceToken = Str::uuid()->toString();
    $deviceTokenHash = hash('sha256', $deviceToken);

    TrustedDevice::create([
      'user_id' => $user->id,
      'device_token_hash' => $deviceTokenHash,
      'barbershop_id' => $barbershopId,
      'expires_at' => now()->addDays(30),
      'last_used_at' => now(),
    ]);

    // Gerar Sanctum token
    $user->tokens()->delete();
    $accessToken = $user->createToken('client-auth')->plainTextToken;

    // Carregar barbershop com business hours
    $user->load(['barbershop.businessHours']);

    return response()->json([
      'success' => true,
      'data' => [
        'user' => $user,
        'barbershop' => $user->barbershop,
        'access_token' => $accessToken,
        'device_token' => $deviceToken,
      ],
    ]);
  }

  public function autoLogin(Request $request): JsonResponse
  {
    $request->validate([
      'device_token' => ['required', 'string'],
      'phone' => ['required', 'string'],
    ]);

    $barbershopId = session('barbershop_id');
    $phone = preg_replace('/\D/', '', $request->phone);
    $deviceTokenHash = hash('sha256', $request->device_token);

    $device = TrustedDevice::validToken($deviceTokenHash, $barbershopId)->first();

    if (!$device) {
      return response()->json([
        'success' => false,
        'message' => 'Dispositivo não confiável. Faça login novamente.',
      ], 401);
    }

    $user = $device->user;

    // Validar que o phone do user corresponde
    if ($user->phone !== $phone) {
      return response()->json([
        'success' => false,
        'message' => 'Dispositivo não confiável. Faça login novamente.',
      ], 401);
    }

    // Atualizar last_used_at
    $device->update(['last_used_at' => now()]);

    // Gerar novo Sanctum token
    $user->tokens()->delete();
    $accessToken = $user->createToken('client-auth')->plainTextToken;

    $user->load(['barbershop.businessHours']);

    return response()->json([
      'success' => true,
      'data' => [
        'user' => $user,
        'barbershop' => $user->barbershop,
        'access_token' => $accessToken,
      ],
    ]);
  }
}
