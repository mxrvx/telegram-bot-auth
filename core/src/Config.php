<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth;

use MXRVX\Schema\System\Settings;
use MXRVX\Schema\System\Settings\SchemaConfig;

class Config extends SchemaConfig
{
    public static function make(array $config): SchemaConfig
    {
        $schema = Settings\Schema::define(App::NAMESPACE)
            ->withSettings(
                [
                    Settings\Setting::define(
                        key: 'ably_private_api_key',
                        value: 'Fw6w4A.6Ns7rQ:CqqOq5WxP33-UKIjFmBiQiwH77yr_jFHZ7ADBoUVP-A',
                        xtype: 'textfield',
                        area: 'ably',
                        typecast: Settings\TypeCaster::STRING,
                    ),
                    Settings\Setting::define(
                        key: 'ably_public_api_key',
                        value: '',
                        xtype: 'textfield',
                        area: 'ably',
                        typecast: Settings\TypeCaster::STRING,
                    ),
                    Settings\Setting::define(
                        key: 'fullname_required',
                        value: true,
                        xtype: 'combo-boolean',
                        area: 'main',
                        typecast: Settings\TypeCaster::BOOLEAN,
                    ),
                ],
            );
        return SchemaConfig::define($schema)->withConfig($config);
    }
}
