<?php

namespace App\Listeners;

use App\Entity\User;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

readonly class EntityTrackingListener
{
    public function __construct(private Security $security){
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void{
        $object = $args->getObject();

        if(method_exists($object, 'setCreatedBy') && is_null($object->getCreatedBy())){
            $object->setCreatedBy($this->security->getUser());
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void{
        $object = $args->getObject();

        if(method_exists($object, 'setUpdatedBy')){
            $object->setUpdatedBy($this->security->getUser());
        }
    }

}