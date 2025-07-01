<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Repository;

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Abstract\AbstractRepository;

final class PrefByFraudRepository extends AbstractRepository
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

    public function index(): array
    {
        return Capsule::table('mod_lkngatewaypreferences_for_fraud')->get()->toArray();
    }

    public function delete(string $countryCode): void
    {
        Capsule::table('mod_lkngatewaypreferences_for_fraud')->where('country', $countryCode)->delete();
    }

    public function store(string $countryCode, array $gateways): void
    {
        Capsule::table('mod_lkngatewaypreferences_for_fraud')->updateOrInsert(
            ['country' => $countryCode],
            ['gateways' => json_encode($gateways, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
    }
}
