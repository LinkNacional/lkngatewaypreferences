<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Repository;

use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Abstract\AbstractRepository;

final class PrefByCountryRepository extends AbstractRepository
{
    protected string $table = 'mod_lkngatewaypreferences_by_country';

    public function getOne(string $countryCode): ?array
    {
        $gateways = $this->query()
            ->where('country', $countryCode)
            ->first('gateways')
            ->gateways;

        return $gateways ? $this->jsonDecode($gateways) : [];
    }

    public function index(): array
    {
        return $this->query->where('country', '!=', '**')->get()->toArray();
    }

    public function delete(string $countryCode): void
    {
        $this->query->where('country', $countryCode)->delete();
    }

    public function store(string $countryCode, array $gateways): void
    {
        $this->query->updateOrInsert(
            ['country' => $countryCode],
            ['gateways' => json_encode($gateways, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
    }

    public function getGlobalPref(): array
    {
        $gateways = $this->query()->where('country', '**')->first('gateways')->gateways;

        return $gateways ? $this->jsonDecode($gateways) : [];
    }

    public function storeGlobalPref(array $gateways): void
    {
        $this->query->updateOrInsert(
            ['country' => '**'],
            ['gateways' => json_encode($gateways, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
    }
}
