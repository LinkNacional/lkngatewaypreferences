<?php

namespace WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Controllers;

use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Abstract\AbstractController;
use WHMCS\Module\Addon\lkngatewaypreferences\App\Preferences\Repository\GeneralSettingsRepository;
use WHMCS\Module\Addon\lkngatewaypreferences\Helpers\View;

final class GeneralSettingsController extends AbstractController
{
    /**
     * @since 1.1.0
     * @var GeneralSettingsRepository
     */
    private readonly GeneralSettingsRepository $repository;

    /**
     * @since 1.1.0
     */
    public function __construct()
    {
        $this->repository = new GeneralSettingsRepository();
    }

    public function create(): string
    {
        $viewParams = [];

        if (isset($_POST['update-general-settings'])) {
            $enableFraudChange = strip_tags($_POST['enable-fraudchange']);
            $this->repository->storeOrUpdate('enable_fraudchange', $enableFraudChange);

            $enableNotes = strip_tags($_POST['enable-notes']);
            $this->repository->storeOrUpdate('enable_notes', $enableNotes);

            $enableLog = strip_tags($_POST['enable-log']);
            $this->repository->storeOrUpdate('enable_log', $enableLog);

            $lknLicense = strip_tags($_POST['lkn-license']);
            $this->repository->storeOrUpdate('lkn_license', $lknLicense);

            $viewParams['settingsSaved'] = true;
        }

        $enableLog = (bool) $this->repository->getOne('enable_log') ?? '';
        $enableFraudChange = (bool) $this->repository->getOne('enable_fraudchange') ?? '';
        $enableNotes = (bool) $this->repository->getOne('enable_notes') ?? '';
        $lknLicense = $this->repository->getOne('lkn_license') ?? '';

        $viewParams = array_merge($viewParams, [
            'enable_log' => $enableLog,
            'enable_fraudchange' => $enableFraudChange,
            'enable_notes' => $enableNotes,
            'lkn_license' => $lknLicense
        ]);

        return View::render('views.admin.pages.settings', $viewParams);
    }
}
