<script type="text/javascript">
    if (!window.lkn) {
        window.lkn = {}
    }

    if (!window.lkn.gflang) {
        window.lkn.gflang = {}
    }

    window.lkn.gflang = {$jsonLang}

    var isPro = {$license->isProUser()}
</script>

{include file="../../modal.tpl"}

<a
    href="#"
    data-toggle="modal"
    data-target="#lkngatewayprefs-client-prefs-modal"
><img
        src="images/icons/addfunds.png"
        border="0"
        align="absmiddle"
    > {$lang['Edit gateway preferences']}</a>

<div
    id="lkngatewayprefs-client-prefs-modal"
    class="modal fade"
    tabindex="-1"
    role="dialog"
>
    <div
        class="modal-dialog"
        role="document"
    >
        <div class="modal-content">
            <div class="modal-header">
                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close"
                    style="transform: scale(1.6);"
                >
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title">{$lang['Gateway preference']}</h4>
            </div>
            <div class="modal-body">
                <div
                    class="row"
                    style="width: 90%; margin: 0 auto 0; margin-bottom: 1em;"
                >
                    <div
                        class="col-md-12"
                        style="margin-bottom: 1em; padding-left: 0px;"
                    >
                        <p>{$lang['Select permitted gateways for this client']}</p>
                        <small>{$lang['This setting overrides the global preference and country preference.']}</small>
                        <br><small>{$lang['Use CTRL + Click to select more than one option or disable an option.']}</small>
                    </div>
                    <div class="col">
                        <select
                            id="select-client-prefs"
                            class="form-control"
                            style="overflow: hidden;"
                            multiple
                            {if isset($license_msg)}
                                disabled
                            {/if}
                        >
                            {foreach from=$paymentMethodsList item=$method}
                                <option
                                    {if in_array($method['code'], $currentClientPrefs)}selected{/if}
                                    value="{$method['code']}"
                                >{$method['name']}</option>
                            {/foreach}
                        </select>
                    </div>

                    <div
                        class="col-md-12"
                        style="margin-top: 1em; padding-left: 0px;"
                    >
                        <p>
                            <strong
                                id="save-prefs-feedback"
                                style="display: none;"
                            ></strong>
                        </p>
                    </div>
                </div>
                {if isset($license_msg)}
                    <div class='alert alert-warning text-center'>
                        <p>
                            <strong>{$lang['premiumfunction_subtext_warning']}</strong>
                        </p>
                        <p>{$lang['premiumfunction_subtext_inlineexplanation']}<a
                                class="alert-link"
                                href="{$_SERVER[HTTP_HOST]}/admin/addonmodules.php?module=lkngatewaypreferences&c=general&r=create"
                            >{$lang['premiumfunction_buytext_inline']}</a>
                            {$lang['premiumfunction_subtext_inlineexplanation2']}
                            <a
                                class="alert-link"
                                href="{$_SERVER[HTTP_HOST]}/admin/addonmodules.php?module=lkngatewaypreferences&c=by-client&r=create"
                            >{$lang['premiumfunction_subtext_editclients']}</a>.
                        </p>
                    </div>
                {/if}
            </div>
            <div class="modal-footer">
                <button
                    id="btn-store-client-prefs"
                    class="btn btn-block btn-primary"
                    type="button"
                    {if isset($license_msg)}
                        disabled
                    {/if}
                >
                    {$lang['Save']}
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    const systemURL = "{$systemURL}"
</script>

<script
    src="{$systemURL}/modules/addons/lkngatewaypreferences/lib/resources/hooks/adm_client_summary_links/index.js"
    defer
></script>