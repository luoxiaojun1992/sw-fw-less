<?php

class TranslatorTest extends \PHPUnit\Framework\TestCase
{
    public function testTransInDefaultEN()
    {
        \SwFwLess\components\i18n\Translator::create(
            __DIR__ . '/../../stubs/components/i18n',
            ['locale' => 'en_US']
        );

        $this->assertEquals(
            'test',
            \SwFwLess\facades\Translator::trans(
                'test',
                [],
                'app'
            )
        );
        $this->assertEquals(
            'test',
            \SwFwLess\facades\Translator::trans(
                'test',
                [],
                'app',
                'en_US'
            )
        );
        $this->assertEquals(
            '测试',
            \SwFwLess\facades\Translator::trans(
                'test',
                [],
                'app',
                'zh_CN'
            )
        );

        \SwFwLess\components\i18n\Translator::clearInstance();
    }

    public function testTransInDefaultCN()
    {
        \SwFwLess\components\i18n\Translator::create(
            __DIR__ . '/../../stubs/components/i18n',
            ['locale' => 'zh_CN']
        );

        $this->assertEquals(
            '测试',
            \SwFwLess\facades\Translator::trans(
                'test',
                [],
                'app'
            )
        );
        $this->assertEquals(
            'test',
            \SwFwLess\facades\Translator::trans(
                'test',
                [],
                'app',
                'en_US'
            )
        );
        $this->assertEquals(
            '测试',
            \SwFwLess\facades\Translator::trans(
                'test',
                [],
                'app',
                'zh_CN'
            )
        );

        \SwFwLess\components\i18n\Translator::clearInstance();
    }

    public function testTransInInternalDefaultLocale()
    {
        \SwFwLess\components\i18n\Translator::create(
            __DIR__ . '/../../stubs/components/i18n',
            []
        );

        $this->assertEquals(
            'test',
            \SwFwLess\facades\Translator::trans(
                'test',
                [],
                'app'
            )
        );
        $this->assertEquals(
            'test',
            \SwFwLess\facades\Translator::trans(
                'test',
                [],
                'app',
                'en_US'
            )
        );
        $this->assertEquals(
            '测试',
            \SwFwLess\facades\Translator::trans(
                'test',
                [],
                'app',
                'zh_CN'
            )
        );

        \SwFwLess\components\i18n\Translator::clearInstance();
    }
}
