<?php

namespace SwFwLess\facades;

/**
 * Class Translator
 *
 * @method static string trans($id, array $parameters = array(), $domain = null, $locale = null)
 * @method static setLocale($locale)
 * @package SwFwLess\facades
 */
class Translator extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\i18n\Translator::create();
    }
}
