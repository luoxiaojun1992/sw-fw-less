<?php

namespace SwFwLess\components\i18n;

use Symfony\Component\Translation\Loader\PhpFileLoader;

/**
 * Class Translator
 * @package SwFwLess\components\i18n
 */
class Translator
{
    /** @var \Symfony\Component\Translation\Translator */
    protected $translator;

    protected $resourcePath;

    protected $config = [
        'locale' => 'en_US',
    ];

    /** @var static */
    protected static $instance;

    public static function clearInstance()
    {
        static::$instance = null;
    }

    /**
     * @param string|null $resourcePath
     * @param array|null $config
     * @return static|null
     */
    public static function create($resourcePath = null, $config = null)
    {
        if (static::$instance instanceof self) {
            return static::$instance;
        }

        if ((!is_null($resourcePath)) && is_array($config)) {
            return static::$instance = new static($resourcePath, $config);
        } else {
            return null;
        }
    }

    public function __construct($resourcePath, $config = [])
    {
        $this->resourcePath = $resourcePath;
        $this->config = array_merge($this->config, $config);
        $this->setTranslator();
        $this->setResources();
    }

    protected function defaultLocale()
    {
        return $this->config['locale'];
    }

    protected function setTranslator()
    {
        $this->translator = new \Symfony\Component\Translation\Translator($this->defaultLocale());
    }

    protected function setResources()
    {
        $this->translator->addLoader('file', new PhpFileLoader());
        $resFd = opendir($this->resourcePath);
        while($localeResDir = readdir($resFd)) {
            if (!in_array($localeResDir, ['.', '..'])) {
                $locale = $localeResDir;
                $localeResourcePath = $this->resourcePath . '/' . $localeResDir;
                $localeResFd = opendir($localeResourcePath);
                while ($localeResFile = readdir($localeResFd)) {
                    if (!in_array($localeResFile, ['.', '..'])) {
                        $domain = substr($localeResFile, 0, -4);
                        $this->translator->addResource(
                            'file',
                            $localeResourcePath . '/' . $localeResFile,
                            $locale,
                            $domain
                        );

                    }
                }
                closedir($localeResFd);
            }
        }
        closedir($resFd);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->translator, $name], $arguments);
    }
}
