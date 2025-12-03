<?php

namespace Oxygen\Controllers;

use Oxygen\Core\Controller;
use Oxygen\Core\OxygenSession;

class LanguageController extends Controller
{
    public function switch($locale)
    {
        $availableLocales = [
            'en',
            'fr',
            'ar'
        ];

        if (in_array($locale, $availableLocales)) {
            OxygenSession::put('locale', $locale);
        }

        return back();
    }
}
