<?php

namespace App\Jobs;

use App\Models\AppDevice;
use App\Models\PushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class SendPush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public AppDevice $device;
    public string $title;
    public string $text;
    public string $action;
    public array $data;
    public int $badgesCount;

    public function __construct(AppDevice $device, string $title, string $text, string $action = '', array $data = [], int $badgesCount = 0)
    {
        $this->device = $device;
        $this->title = $title;
        $this->text = $text;
        $this->action = $action;
        $this->data = $data;
        $this->badgesCount = $badgesCount;
    }

    public function handle()
    {
        $device = $this->device;
        $serviceAccount = $this->loadServiceAccount();
        $accessToken = $this->getAccessToken($serviceAccount);
        $payload = $this->buildPayload();
        $response = $this->sendFcmMessage($serviceAccount['project_id'], $accessToken, $payload);

        Log::info("Debug push", [
            "user_id" => $device->user_id,
            "device_id" => $device->id,
            "title" => $this->title,
            "response" => $response,
        ]);
        $this->processResponse($response);
    }

    /**
     * Загружает данные сервисного аккаунта.
     *
     * @return array
     * @throws \Exception
     */
    private function loadServiceAccount(): array
    {
        $path = config("services.firebase.json"); // путь к файлу сервисного аккаунта
        $content = Storage::disk("local")->get($path);
        $serviceAccount = json_decode($content, true);
        if (!$serviceAccount) {
            throw new \Exception("Не удалось загрузить файл сервисного аккаунта.");
        }
        return $serviceAccount;
    }

    /**
     * Получает access token через OAuth2, используя JWT.
     *
     * @param array $serviceAccount
     * @return string
     * @throws \Exception
     */
    private function getAccessToken(array $serviceAccount): string
    {
        $privateKey = $serviceAccount['private_key'];
        $clientEmail = $serviceAccount['client_email'];

        $jwt = $this->createJwt($clientEmail, $privateKey);

        $postFields = http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $tokenResponse = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception('Ошибка cURL при запросе токена: ' . curl_error($ch));
        }
        curl_close($ch);

        $tokenData = json_decode($tokenResponse, true);
        if (!isset($tokenData['access_token'])) {
            throw new \Exception('Ошибка получения access token: ' . $tokenResponse);
        }
        return $tokenData['access_token'];
    }

    /**
     * Создает JWT для получения access token.
     *
     * @param string $clientEmail
     * @param string $privateKey
     * @return string
     * @throws \Exception
     */
    private function createJwt(string $clientEmail, string $privateKey): string
    {
        $now = time();
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];
        $payload = [
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600, // токен действителен 1 час
            'iat' => $now
        ];

        $encodedHeader = $this->base64UrlEncode(json_encode($header));
        $encodedPayload = $this->base64UrlEncode(json_encode($payload));
        $jwtUnsigned = $encodedHeader . '.' . $encodedPayload;

        $signature = '';
        if (!openssl_sign($jwtUnsigned, $signature, $privateKey, 'SHA256')) {
            throw new \Exception("Ошибка при подписании JWT.");
        }
        $encodedSignature = $this->base64UrlEncode($signature);
        return $jwtUnsigned . '.' . $encodedSignature;
    }

    /**
     * Безопасное Base64 URL-кодирование.
     *
     * @param string $data
     * @return string
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Формирует payload для нового FCM API, с разделением логики для Android и iOS.
     *
     * @return array
     */
    private function buildPayload(): array
    {
        $deviceToken = $this->device->token;

        if ($this->device->type === "android") {
            // Для Android отправляем данные в поле data
            $message = [
                "token" => $deviceToken,
                "data" => array_merge(
                    [
                        "action" => $this->action,
                        "title" => $this->title,
                        "body" => $this->text,
                        "sound" => "default",
                        "badge" => (string)$this->badgesCount,
                    ],
                    array_map('strval', $this->data)
                )
            ];
        } else {
            // Для iOS уведомление: в notification остаются только title и body,
            // а дополнительные настройки передаются через apns
            $message = [
                "token" => $deviceToken,
                "notification" => [
                    "title" => $this->title,
                    "body" => $this->text,
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "sound" => "default",
                            "badge" => $this->badgesCount,
                            "action" => $this->action, // если требуется
                        ]
                    ]
                ]
            ];
            if (!empty($this->data)) {
                $message["apns"]["payload"]["aps"]["data"] = array_map('strval', $this->data);
            }
        }

        return ["message" => $message];
    }

    /**
     * Отправляет сообщение через FCM HTTP v1 API.
     *
     * @param string $projectId
     * @param string $accessToken
     * @param array $payload
     * @return string
     * @throws \Exception
     */
    private function sendFcmMessage(string $projectId, string $accessToken, array $payload): string
    {
        $fcmUrl = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception('Ошибка cURL при отправке FCM сообщения: ' . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }

    /**
     * Обрабатывает ответ от FCM и удаляет устройство при ошибке доставки.
     *
     * @param string $response
     * @return void
     */
    private function processResponse(string $response): void
    {
        try {
            $json = json_decode($response);
            // Если FCM вернул ошибку, например, NotRegistered,
            // удаляем устройство
            if (isset($json->error) || (isset($json->message) && isset($json->message->error))) {
                $this->device->delete();
            }
        } catch (\Exception $e) {
            Log::info("Ошибка при обработке ответа FCM", [$e->getMessage()]);
        }
    }
}
