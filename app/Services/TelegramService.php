<?php

namespace App\Services;

use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramResponseException;

class TelegramService
{
    protected $telegram;

    public function __construct()
    {
        // Inicializa a API do Telegram com o token da configuração
        $this->telegram = new Api(config('services.telegram.7930611389:AAFz0Rlj5oWCs8uQoDPz4TLk3yAKHnEZ-pM'));
    }

    public function sendMessage($chatId, $message)
    {
        try {
            return $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text'    => $message,
            ]);
        } catch (TelegramResponseException $e) {
            // Logar erro de resposta do Telegram para debug
            throw $e; // Re-lançar a exceção ou lidar com ela conforme necessário
        }
    }
}
