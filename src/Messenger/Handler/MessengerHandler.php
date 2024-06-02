<?php

namespace App\Messenger\Handler;

use App\Entity\CartProduct;
use App\Entity\Notification;
use App\Entity\Product;
use App\Messenger\Message\MessengerMessage;
use App\Model\ObjectModel;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class MessengerHandler
{

    public function __construct(private ObjectModel $objectModel){}

    public function __invoke(MessengerMessage $messengerMessage): void
    {
        $method = $messengerMessage->getMethod(); // on récupère la méthode
        $params = $messengerMessage->getParams(); // on récupère les params

        match($method){
            'productAdded' => $this->productAdded($params['message']),
        };
    }

    private function productAdded(string $message): void {
        sleep(5);

        $notification = new Notification();
        $notification->setMessage($message);

        $this->objectModel->save($notification);
    }
}
