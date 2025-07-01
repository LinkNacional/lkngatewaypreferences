<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Repository;

use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Abstract\AbstractRepository;

final class GeneralSettingsRepository extends AbstractRepository
{
    protected string $table = 'mod_lkngatewaypreferences_settings';

    public function getOne(string $setting): ?string
    {
        $value = $this->query()
            ->where('setting', $setting)
            ->first('value')
            ->value;

        return $value;
    }

    public function storeOrUpdate(string $setting, mixed $value): void
    {
        if (is_array($value)) {
            $value = $this->jsonEncode($value);
        }

        $this->query()->updateOrInsert(
            ['setting' => $setting],
            ['value' => $value]
        );
    }
}
