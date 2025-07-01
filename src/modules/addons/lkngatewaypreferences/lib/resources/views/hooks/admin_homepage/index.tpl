<div
    class="alert alert-success alert-dismissible fade in"
    role="alert"
>
    <button
        id="lkngatewaypreferences-dismiss-icon"
        type="button"
        class="close "
        data-dismiss="alert"
        aria-label="Close"
    >
        <span aria-hidden="true">&times;</span>
    </button>
    <h4 style="margin-bottom: 30px;">{$lang['new_version_available']}</h4>
    <p style="max-width: 750px;">{$lang['new_version_available_descrip']}</p>
    <div style="margin-top: 30px; display: flex; align-items: end; justify-content: space-between;">
        <p style="max-width: 750px;">
            <a
                class="btn btn-success"
                target="_blank"
                href="https://cliente.linknacional.com.br/dl.php?type=d&id=43"
                role="button"
            ><i class="fas fa-cloud-download"></i> {$lang['download_new_version']} v{$newVersion}</a>
        </p>
    </div>
</div>
<script>
    const systemURL = "{$systemURL}"
</script>

<script
    src="{$systemURL}/modules/addons/lkngatewaypreferences/lib/resources/views/hooks/admin_homepage/index.js"
    defer
></script>