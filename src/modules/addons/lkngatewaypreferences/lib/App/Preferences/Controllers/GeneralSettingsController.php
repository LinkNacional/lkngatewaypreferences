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

            $fraudCronHook = strip_tags($_POST['fraud-cron-hook']);
            $this->repository->storeOrUpdate('fraud_cron_hook', $fraudCronHook);

            $skipZeroAmount = strip_tags($_POST['skip-zero-amount'] ?? '');
            $this->repository->storeOrUpdate('skip_zero_amount', $skipZeroAmount);

            $enableAutoCancel = strip_tags($_POST['enable-auto-cancel'] ?? '');
            $this->repository->storeOrUpdate('enable_auto_cancel', $enableAutoCancel);

            $autoCancelDays = strip_tags($_POST['auto-cancel-days'] ?? '0');
            $autoCancelDays = (int) $autoCancelDays;
            $autoCancelDays = $autoCancelDays > 0 ? $autoCancelDays : 0;
            $this->repository->storeOrUpdate('auto_cancel_days', (string) $autoCancelDays);

            $cancelPaidOrders = strip_tags($_POST['cancel-paid-orders'] ?? '');
            $this->repository->storeOrUpdate('cancel_paid_orders', $cancelPaidOrders);

            $viewParams['settingsSaved'] = true;
        }

        $enableLog = (bool) $this->repository->getOne('enable_log') ?? '';
        $enableFraudChange = (bool) $this->repository->getOne('enable_fraudchange') ?? '';
        $enableNotes = (bool) $this->repository->getOne('enable_notes') ?? '';
        $lknLicense = $this->repository->getOne('lkn_license') ?? '';
        $fraudCronHook = $this->repository->getOne('fraud_cron_hook') ?? 'AfterCronJob';
        $skipZeroAmount = (bool) $this->repository->getOne('skip_zero_amount') ?? '';
        $enableAutoCancel = (bool) $this->repository->getOne('enable_auto_cancel') ?? '';
        $autoCancelDays = (int) $this->repository->getOne('auto_cancel_days') ?? 0;
        $cancelPaidOrders = (bool) $this->repository->getOne('cancel_paid_orders') ?? '';

        $viewParams = array_merge($viewParams, [
            'enable_log' => $enableLog,
            'enable_fraudchange' => $enableFraudChange,
            'enable_notes' => $enableNotes,
            'lkn_license' => $lknLicense,
            'fraud_cron_hook' => $fraudCronHook,
            'skip_zero_amount' => $skipZeroAmount,
            'enable_auto_cancel' => $enableAutoCancel,
            'auto_cancel_days' => $autoCancelDays,
            'cancel_paid_orders' => $cancelPaidOrders
        ]);

        return View::render('views.admin.pages.settings', $viewParams);
    }
}
