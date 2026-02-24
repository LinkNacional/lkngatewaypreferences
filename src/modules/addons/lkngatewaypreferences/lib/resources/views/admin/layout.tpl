<script type="text/javascript">
    if (!window.lkn) {
        window.lkn = {}
    }

    if (!window.lkn.gflang) {
        window.lkn.gflang = {}
    }

    window.lkn.gflang = {$jsonLang}
</script>

<hr>

{if isset($license_msg)}
    <div
        class="alert alert-danger"
        role="alert"
    >
        <a
            class="alert-link"
            href="?module=lkngatewaypreferences&c=general&r=create"
        >
            {$license_msg}
        </a>
    </div>
{/if}

<div
    class="container-fluid"
    style="padding-left: 0px; padding-right: 0px; min-height: 100vh;"
>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <button
                    type="button"
                    class="navbar-toggle collapsed"
                    data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1"
                    aria-expanded="false"
                >
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <div class="navbar-brand">
                    <img
                        style="height: 20px"
                        src="{$systemURL}/modules/addons/lkngatewaypreferences/logo.png"
                    >
                </div>
            </div>

            <div
                class="collapse navbar-collapse"
                id="bs-example-navbar-collapse-1"
            >
                <ul class="nav navbar-nav">
                    <li {($page=="general") ? "class='active'" : ''}>
                        <a href="?module=lkngatewaypreferences&c=general&r=create">{$lang['Settings']}</a>
                    </li>
                    <li {($page=="pref.byCountry") ? "class='active'" : ''}>
                        <a
                            href="?module=lkngatewaypreferences&c=by-country&r=create">{$lang['Preferences by country']}</a>
                    </li>
                    <li {($page=="pref.byClient") ? "class='active'" : ''}>
                        <a
                            href="?module=lkngatewaypreferences&c=by-client&r=create">{$lang['Preferences by client']}</a>
                    </li>
                    <li {($page=="pref.byFraud") ? "class='active'" : ''}>
                        <a href="?module=lkngatewaypreferences&c=by-fraud&r=create">{$lang['Preferences by fraud']}</a>
                    </li>
                    <li {($page=="diagnostic") ? "class='active'" : ''}>
                        <a href="?module=lkngatewaypreferences&c=diagnostic"
                            style="color: #F4912E; font-weight: bold;">Diagnostic</a>
                    </li>
                </ul>

                <p class="navbar-text navbar-right">v{$moduleVersion}</p>
            </div>
        </div>
    </nav>

    <div
        class="container-fluid"
        style="padding: 0px 140.95px;"
    >
        <div style="display: flex; justify-content: space-between; align-items: baseline;">
            <h2 style="font-weight: bold; font-size: 1.32em;">{$title}</h2>

            {block "title-right"}
            {/block}
        </div>
        <hr>

        <div
            class="container-fluid"
            style="padding: 0px;"
        >
            {block "content"}{/block}
        </div>
    </div>
</div>