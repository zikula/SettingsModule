<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

class LocaleListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * LocaleListener constructor.
     * @param string $defaultLocale
     * @param CurrentUserApiInterface $currentUserApi
     * @param boolean $installed
     */
    public function __construct($defaultLocale = 'en', CurrentUserApiInterface $currentUserApi, $installed = false)
    {
        // compute default locale considering user preference
        $userSelectedLocale = $installed ? $currentUserApi->get('locale') : '';
        $this->defaultLocale = !empty($userSelectedLocale) ? $userSelectedLocale : $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session or default
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            // must be registered after the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 15]]
        ];
    }
}
