{extends "../layout.tpl"}

{$page="general"}
{$title={$lang['General settings']}}

{block "content"}
    <div style="width: 824.5px;">
        <h5
            class="help-block"
            style="margin-left: 0px; margin-top: -3em; margin-bottom: 2em;"
        >
            {$lang['pluginsettings_subtitle']}
        </h5>
    </div>

    {if isset($settingsSaved)}
        <div
            class="alert alert-success"
            role="alert"
        >
            {$lang['Settings updated!']}
        </div>
    {/if}

    <form
        class="form-horizontal"
        style="max-width: 850px;"
        method="POST"
        target="_self"
        action=""
    >

        <input
            type="hidden"
            name="update-general-settings"
        >

        <div class="form-group">
            <div
                class="col-sm-5"
                style="margin-right: 25px;"
            >
                <label class="control-label">{$lang['Link Nacional License']}</label>
            </div>

            <div class="col-sm-5">
                <input
                    class="form-control"
                    name="lkn-license"
                    type="password"
                    value="{$lkn_license}"
                />
                {if isset($license_msg)}
                    <div
                        class='alert alert-warning'
                        style='margin-top: 1em;'
                    >
                        <p><em>{$lang['license_blurb']}</em></p>
                        <a
                            class="alert-link"
                            href="https://cliente.linknacional.com.br/solicitar/whmcs-preferencia-gateway-pagamento"
                        >
                            {$lang['premiumfunction_buytext']}
                        </a>
                    </div>
                {/if}
            </div>
        </div>

        <hr>

        <div class="form-group">
            <div
                class="col-sm-5"
                style="margin-right: 25px;"
            >
                <label class="control-label">{$lang['fraudstatus_title']}</label>
                <span class="help-block">
                    <p>
                        {$lang['fraudstatus_subtitle']}
                    </p>
                </span>
            </div>

            <div class="col-sm-5">
                <div
                    class="col-sm-1"
                    style="margin-right: 8px;"
                >
                    <input
                        type="checkbox"
                        id="enable-fraudchange"
                        name="enable-fraudchange"
                        {if isset($license_msg)}
                            disabled
                        {/if}
                        {($enable_fraudchange) ? 'checked' : ''}
                    >
                </div>

                <label
                    for="enable-fraudchange"
                    style="font-weight: normal;"
                >{$lang['fraudstatus_option']}</label>
                {if isset($license_msg)}
                    <div
                        class='alert alert-warning'
                        style='margin-top: 1em; '
                    >
                        <p>
                            <strong>{$lang['premiumfunction_subtext_warning']}</strong>
                        </p>
                        <p>{$lang['premiumfunction_subtext_explanation']}</p>
                    </div>
                {/if}
            </div>
        </div>

        <hr>

        <div class="form-group">
            <div
                class="col-sm-5"
                style="margin-right: 25px;"
            >
                <label class="control-label">{$lang['notes_title']}</label>
                <span class="help-block">
                    <p>
                        {$lang['notes_subtitle']}
                    </p>
                </span>
            </div>

            <div class="col-sm-5">
                <div
                    class="col-sm-1"
                    style="margin-right: 8px;"
                >
                    <input
                        type="checkbox"
                        id="enable-notes"
                        name="enable-notes"
                        {if isset($license_msg)}
                            disabled
                        {/if}
                        {($enable_notes) ? 'checked' : ''}
                    >
                </div>

                <label
                    for="enable-notes"
                    style="font-weight: normal;"
                >{$lang['notes_option']}</label>
                {if isset($license_msg)}
                    <div
                        class='alert alert-warning'
                        style='margin-top: 1em; '
                    >
                        <p>
                            <strong>{$lang['premiumfunction_subtext_warning']}</strong>
                        </p>
                        <p>{$lang['premiumfunction_subtext_explanation']}</p>
                    </div>
                {/if}
            </div>
        </div>

        <hr>

        <div class="form-group">
            <div
                class="col-sm-5"
                style="margin-right: 25px;"
            >
                <label class="control-label">{$lang['fraud_cron_hook_title']}</label>
                <span class="help-block">
                    <p>
                        {$lang['fraud_cron_hook_subtitle']}
                    </p>
                </span>
            </div>

            <div class="col-sm-5">
                <select
                    class="form-control"
                    name="fraud-cron-hook"
                    id="fraud-cron-hook"
                >
                    <option
                        value="AfterCronJob"
                        {($fraud_cron_hook === 'AfterCronJob') ? 'selected' : ''}
                    >
                        {$lang['fraud_cron_hook_option_aftercron']}
                    </option>
                    <option
                        value="FraudOrder"
                        {($fraud_cron_hook === 'FraudOrder') ? 'selected' : ''}
                    >
                        {$lang['fraud_cron_hook_option_fraud']}
                    </option>
                    <option
                        value="AfterFraudCheck"
                        {($fraud_cron_hook === 'AfterFraudCheck') ? 'selected' : ''}
                    >
                        {$lang['fraud_cron_hook_option_afterfraudcheck']}
                    </option>
                </select>
            </div>
        </div>

        <hr>

        <div class="form-group">
            <div
                class="col-sm-5"
                style="margin-right: 25px;"
            >
                <label class="control-label">{$lang['skip_zero_amount_title']}</label>
                <span class="help-block">
                    <p>
                        {$lang['skip_zero_amount_subtitle']}
                    </p>
                </span>
            </div>

            <div class="col-sm-5">
                <div
                    class="col-sm-1"
                    style="margin-right: 8px;"
                >
                    <input
                        type="checkbox"
                        id="skip-zero-amount"
                        name="skip-zero-amount"
                        {if isset($license_msg)}
                            disabled
                        {/if}
                        {($skip_zero_amount) ? 'checked' : ''}
                    >
                </div>

                <label
                    for="skip-zero-amount"
                    style="font-weight: normal;"
                >{$lang['skip_zero_amount_option']}</label>
                {if isset($license_msg)}
                    <div
                        class='alert alert-warning'
                        style='margin-top: 1em; '
                    >
                        <p>
                            <strong>{$lang['premiumfunction_subtext_warning']}</strong>
                        </p>
                        <p>{$lang['premiumfunction_subtext_explanation']}</p>
                    </div>
                {/if}
            </div>
        </div>

        <hr>

        <div class="form-group">
            <div
                class="col-sm-5"
                style="margin-right: 25px;"
            >
                <label class="control-label">{$lang['enable_auto_cancel_title']}</label>
                <span class="help-block">
                    <p>
                        {$lang['enable_auto_cancel_subtitle']}
                    </p>
                </span>
            </div>

            <div class="col-sm-5">
                <div
                    class="col-sm-1"
                    style="margin-right: 8px;"
                >
                    <input
                        type="checkbox"
                        id="enable-auto-cancel"
                        name="enable-auto-cancel"
                        {($enable_auto_cancel) ? 'checked' : ''}
                        onchange="toggleAutoCancelDays()"
                    >
                </div>

                <label
                    for="enable-auto-cancel"
                    style="font-weight: normal;"
                >{$lang['enable_auto_cancel_option']}</label>
            </div>
        </div>

        <div class="form-group" id="auto-cancel-days-group" style="display: none;">
            <div
                class="col-sm-5"
                style="margin-right: 25px;"
            >
                <label class="control-label">{$lang['auto_cancel_days_title']}</label>
                <span class="help-block">
                    <p>
                        {$lang['auto_cancel_days_subtitle']}
                    </p>
                </span>
            </div>

            <div class="col-sm-5">
                <input
                    type="number"
                    class="form-control"
                    id="auto-cancel-days"
                    name="auto-cancel-days"
                    min="1"
                    max="365"
                    value="{$auto_cancel_days}"
                    placeholder="{$lang['auto_cancel_days_placeholder']}"
                />
            </div>
        </div>

        <div class="form-group" id="cancel-paid-group" style="display: none;">
            <div
                class="col-sm-5"
                style="margin-right: 25px;"
            >
                <label class="control-label">{$lang['cancel_paid_orders_title']}</label>
                <span class="help-block">
                    <p>
                        {$lang['cancel_paid_orders_subtitle']}
                    </p>
                </span>
            </div>

            <div class="col-sm-5">
                <div
                    class="col-sm-1"
                    style="margin-right: 8px;"
                >
                    <input
                        type="checkbox"
                        id="cancel-paid-orders"
                        name="cancel-paid-orders"
                        {($cancel_paid_orders) ? 'checked' : ''}
                    >
                </div>

                <label
                    for="cancel-paid-orders"
                    style="font-weight: normal;"
                >{$lang['cancel_paid_orders_option']}</label>
            </div>
        </div>

        <hr>

        <div class="form-group">
            <div
                class="col-sm-5"
                style="margin-right: 25px;"
            >
                <label class="control-label lknhn-control-label">{$lang['Enable debug logs']}</label>
                <span class="help-block lknhn-help-block">
                    <a
                        style="color: #777;"
                        href="logs/module-log"
                    >
                        {$lang['Access logs']}
                    </a>
                </span>
            </div>

            <div class="col-sm-5">
                <div
                    class="col-sm-1"
                    style="margin-right: 8px;"
                >
                    <input
                        type="checkbox"
                        id="enable-log"
                        name="enable-log"
                        {($enable_log) ? 'checked' : ''}
                    >
                </div>
                <label
                    for="enable-log"
                    style="font-weight: normal;"
                >{$lang['Enable logs']}</label>
            </div>
        </div>

        <div
            class="form-group"
            style="margin: 55px 0px;"
        >
            <button
                type="submit"
                class="btn btn-primary btn-sm btn-block"
                style="width: 200px;"
            >{$lang['Save']}</button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            toggleAutoCancelDays();
        });

        function toggleAutoCancelDays() {
            const enableAutoCancel = document.getElementById('enable-auto-cancel').checked;
            const autoCancelDaysGroup = document.getElementById('auto-cancel-days-group');
            const cancelPaidGroup = document.getElementById('cancel-paid-group');
            
            if (enableAutoCancel) {
                autoCancelDaysGroup.style.display = 'block';
                cancelPaidGroup.style.display = 'block';
            } else {
                autoCancelDaysGroup.style.display = 'none';
                cancelPaidGroup.style.display = 'none';
            }
        }
    </script>
{/block}