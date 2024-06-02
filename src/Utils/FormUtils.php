<?php

namespace App\Utils;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

readonly class FormUtils
{
    public function __construct(){}

    public static function validateForm(FormInterface $form, array $params): ?RedirectResponse
    {
        $form->handleRequest($params['request']);

        if ($form->isSubmitted() && $form->isValid()) {
            $object = $form->getData();

            try {
                $params['modelClass']->save($object);
                $params['request']->getSession()->getFlashBag()->add('success', $params['flashbagSuccess']);
                return new RedirectResponse($params['redirectSuccess']);
            } catch (\RuntimeException $e) {
                $params['request']->getSession()->getFlashBag()->add('error', $e->getMessage());
                return new RedirectResponse($params['request']->getPathInfo()); // l'url de la route courante
            }
        }

        return null;
    }
}